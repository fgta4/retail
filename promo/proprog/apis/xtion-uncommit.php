<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}


require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/core/debug.php';
require_once __ROOT_DIR.'/core/approval.php';
require_once __DIR__ . '/xapi.base.php';

use \FGTA4\exceptions\WebException;
use FGTA4StandartApproval;
use \FGTA4\StandartApproval;



/**
 * retail/promo/proprog/apis/xtion-uncommit.php
 *
 * ========
 * UnCommit
 * ========
 * UnCommit dokumen, mengembalikan status dokumen ke draft 
 *
 * Agung Nugroho <agung@fgta.net> http://www.fgta.net
 * Tangerang, 26 Maret 2021
 *
 * digenerate dengan FGTA4 generator
 * tanggal 08/07/2023
 */
$API = new class extends proprogBase {

	public function execute($id, $param) {
		$tablename = 'mst_proprog';
		$primarykey = 'proprog_id';
		$userdata = $this->auth->session_get_user();

		try {
			$currentdata = (object)[
				'header' => $this->get_header_row($id),
				'user' => $userdata
			];

			$this->pre_action_check($currentdata, 'uncommit');


			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();
			
			try {

				$this->remove_approval($currentdata);
				$this->save_and_set_uncommit_flag($id, $currentdata);


				$record = []; $row = $this->get_header_row($id);
				foreach ($row as $key => $value) { $record[$key] = $value; }
				$dataresponse = (object) array_merge($record, [
					//  untuk lookup atau modify response ditaruh disini
					'prorule_name' => \FGTA4\utils\SqlUtility::Lookup($record['prorule_id'], $this->db, 'mst_prorule', 'prorule_id', 'prorule_name'),
					'dept_name' => \FGTA4\utils\SqlUtility::Lookup($record['dept_id'], $this->db, 'mst_dept', 'dept_id', 'dept_name'),
					'promodel_name' => \FGTA4\utils\SqlUtility::Lookup($record['promodel_id'], $this->db, 'mst_promodel', 'promodel_id', 'promodel_name'),
					'problock_name' => \FGTA4\utils\SqlUtility::Lookup($record['problock_id'], $this->db, 'mst_problock', 'problock_id', 'problock_name'),
					'proprog_dtstart' => date("d/m/Y", strtotime($record['proprog_dtstart'])),
					'proprog_dtend' => date("d/m/Y", strtotime($record['proprog_dtend'])),
					'a_prospot_name' => \FGTA4\utils\SqlUtility::Lookup($record['a_prospot_id'], $this->db, 'mst_prospot', 'prospot_id', 'prospot_name'),
					'a_prostep_name' => \FGTA4\utils\SqlUtility::Lookup($record['a_prostep_id'], $this->db, 'mst_prostep', 'prostep_id', 'prostep_name'),
					'a_progrouping_name' => \FGTA4\utils\SqlUtility::Lookup($record['a_progrouping_id'], $this->db, 'mst_progrouping', 'progrouping_id', 'progrouping_name'),
					'b_prospot_name' => \FGTA4\utils\SqlUtility::Lookup($record['b_prospot_id'], $this->db, 'mst_prospot', 'prospot_id', 'prospot_name'),
					'proprog_commitby' => \FGTA4\utils\SqlUtility::Lookup($record['proprog_commitby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
					'proprog_approveby' => \FGTA4\utils\SqlUtility::Lookup($record['proprog_approveby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
					'proprog_declineby' => \FGTA4\utils\SqlUtility::Lookup($record['proprog_declineby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
					'doc_name' => \FGTA4\utils\SqlUtility::Lookup($record['doc_id'], $this->db, 'mst_doc', 'doc_id', 'doc_name'),

					'_createby' => \FGTA4\utils\SqlUtility::Lookup($record['_createby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
					'_modifyby' => \FGTA4\utils\SqlUtility::Lookup($record['_modifyby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
				]);


				\FGTA4\utils\SqlUtility::WriteLog($this->db, $this->reqinfo->modulefullname, $tablename, $id, 'UNCOMMIT', $userdata->username, (object)[]);

				$this->db->commit();
				return (object)[
					'success' => true,
					'version' => $currentdata->header->{$this->main_field_version},
					'dataresponse' => $dataresponse
				];
				
			} catch (\Exception $ex) {
				$this->db->rollBack();
				throw $ex;
			} finally {
				$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function remove_approval($currentdata) {
		try {
			StandartApproval::remove(
				$this->db, 
				$currentdata,
				$this->approval_tablename, 
				["$this->main_primarykey"=> $currentdata->header->{$this->main_primarykey}, "$this->approval_primarykey"=>null],
				$currentdata->header->doc_id,
				(object)[
					'tablename_head' => $this->main_tablename,
					'flag_head_decline' => $this->field_isdecline,
					'flag_head_declineby' => $this->field_declineby,
					'flag_head_declinedate' => $this->field_declinedate,
					'flag_head_isapprovalprogress' => $this->fields_isapprovalprogress,					
				]				
			);

		} catch (Exception $ex) {
			throw $ex;
		}		
	}			
			

	public function save_and_set_uncommit_flag($id, $currentdata) {
		$currentdata->header->{$this->main_field_version}++;
		try {
			$sql = " 
				update $this->main_tablename
				set 
				$this->field_iscommit = 0,
				$this->field_commitby = null,
				$this->field_commitdate = null,
				$this->main_field_version = :version
				where
				$this->main_primarykey = :id
			";

			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				":id" => $currentdata->header->{$this->main_primarykey},
				":version" => $currentdata->header->{$this->main_field_version}
			]);

		} catch (\Exception $ex) {
			throw $ex;
		}	
	}	
};



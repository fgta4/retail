<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}


require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/core/debug.php';

require_once __DIR__ . '/xapi.base.php';

if (is_file(__DIR__ .'/data-header-handler.php')) {
	require_once __DIR__ .'/data-header-handler.php';
}

use \FGTA4\exceptions\WebException;

use \FGTA4\StandartApproval;



/**
 * retail/sales/merchorderin/apis/xtion-uncommit.php
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
 * tanggal 19/09/2023
 */
$API = new class extends merchorderinBase {

	public function execute($id, $options) {
		$event = 'uncommit';
		$tablename = 'trn_merchorderin';
		$primarykey = 'merchorderin_id';
		$userdata = $this->auth->session_get_user();

		$handlerclassname = "\\FGTA4\\apis\\merchorderin_headerHandler";
		$hnd = null;
		if (class_exists($handlerclassname)) {
			$hnd = new merchorderin_headerHandler($options);
			$hnd->caller = &$this;
			$hnd->db = &$this->db;
			$hnd->auth = $this->auth;
			$hnd->reqinfo = $this->reqinfo;
			$hnd->event = $event;
		} else {
			$hnd = new \stdClass;
		}

		try {
			$currentdata = (object)[
				'header' => $this->get_header_row($id),
				'user' => $userdata
			];

			if (method_exists(get_class($hnd), 'XtionActionExecuting')) {
				// XtionActionExecuting(string $id, $action, object &$currentdata) : void
				$hnd->XtionActionExecuting($id, 'uncommit', $currentdata);
			}


			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();
			
			try {

				if (method_exists(get_class($hnd), 'XtionUnCommitting')) {
					// XtionUnCommitting(string $id, object &$currentdata) : void
					$hnd->XtionUnCommitting($id, $currentdata);
				}

	
				$this->save_and_set_uncommit_flag($id, $currentdata);
				if (method_exists(get_class($hnd), 'XtionUnCommitted')) {
					// XtionUnCommitted(string $id) : void
					$hnd->XtionUnCommitted($id);
				}

				$record = []; $row = $this->get_header_row($id);
				foreach ($row as $key => $value) { $record[$key] = $value; }
				$dataresponse = array_merge($record, [
					//  untuk lookup atau modify response ditaruh disini
					'merchorderin_date' => date("d/m/Y", strtotime($record['merchorderin_date'])),
					'merchorderin_datedue' => date("d/m/Y", strtotime($record['merchorderin_datedue'])),
					'merchsea_name' => \FGTA4\utils\SqlUtility::Lookup($record['merchsea_id'], $this->db, 'mst_merchsea', 'merchsea_id', 'merchsea_name'),
					'unit_name' => \FGTA4\utils\SqlUtility::Lookup($record['unit_id'], $this->db, 'mst_unit', 'unit_id', 'unit_name'),
					'dept_name' => \FGTA4\utils\SqlUtility::Lookup($record['dept_id'], $this->db, 'mst_dept', 'dept_id', 'dept_name'),
					'partner_name' => \FGTA4\utils\SqlUtility::Lookup($record['partner_id'], $this->db, 'mst_partner', 'partner_id', 'partner_name'),
					'project_name' => \FGTA4\utils\SqlUtility::Lookup($record['project_id'], $this->db, 'mst_project', 'project_id', 'project_name'),
					'merchorderin_commitby' => \FGTA4\utils\SqlUtility::Lookup($record['merchorderin_commitby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
					'merchorderin_postby' => \FGTA4\utils\SqlUtility::Lookup($record['merchorderin_postby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),

					'_createby' => \FGTA4\utils\SqlUtility::Lookup($record['_createby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
					'_modifyby' => \FGTA4\utils\SqlUtility::Lookup($record['_modifyby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
				]);


				\FGTA4\utils\SqlUtility::WriteLog($this->db, $this->reqinfo->modulefullname, $tablename, $id, 'UNCOMMIT', $userdata->username, (object)[]);

				if (method_exists(get_class($hnd), 'DataOpen')) {
					//  DataOpen(array &$record) : void 
					$hnd->DataOpen($dataresponse);
				}

				$this->db->commit();
				return (object)[
					'success' => true,
					'version' => $currentdata->header->{$this->main_field_version},
					'dataresponse' => (object) $dataresponse
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



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
 * retail/sales/promoab/apis/xtion-commit.php
 *
 * =======
 * Commit
 * =======
 * Commit dokumen, menandakan dokumen yang selesai dsunting
 * dan telah siap untuk diproses lebih lanjut
 * Pada status tercommit, dokumen akan menjadi readonly. 
 *
 * Agung Nugroho <agung@fgta.net> http://www.fgta.net
 * Tangerang, 26 Maret 2021
 *
 * digenerate dengan FGTA4 generator
 * tanggal 20/08/2024
 */
$API = new class extends promoabBase {

	public function execute($id, $options) {
		$event = 'commit';
		$tablename = 'mst_promoab';
		$primarykey = 'promoab_id';
		$userdata = $this->auth->session_get_user();

		$handlerclassname = "\\FGTA4\\apis\\promoab_headerHandler";
		$hnd = null;
		if (class_exists($handlerclassname)) {
			$hnd = new promoab_headerHandler($options);
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
				$hnd->XtionActionExecuting($id, 'commit', $currentdata);
			}

			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();

			try {

				if (method_exists(get_class($hnd), 'XtionCommitting')) {
					// XtionCommitting(string $id, object &$currentdata) : void
					$hnd->XtionCommitting($id, $currentdata);
				}

	
				$this->save_and_set_commit_flag($id, $currentdata);
				if (method_exists(get_class($hnd), 'XtionCommitted')) {
					// XtionCommitted(string $id) : void
					$hnd->XtionCommitted($id);
				}
				
				$record = []; $row = $this->get_header_row($id);
				foreach ($row as $key => $value) { $record[$key] = $value; }
				$dataresponse = array_merge($record, [
					//  untuk lookup atau modify response ditaruh disini
					'brand_name' => \FGTA4\utils\SqlUtility::Lookup($record['brand_id'], $this->db, 'mst_brand', 'brand_id', 'brand_name'),
					'promoabmodel_descr' => \FGTA4\utils\SqlUtility::Lookup($record['promoabmodel_id'], $this->db, 'mst_promoabmodel', 'promoabmodel_id', 'promoabmodel_descr'),
					'promoabrule_dtstart' => date("d/m/Y", strtotime($record['promoabrule_dtstart'])),
					'promoabrule_dtend' => date("d/m/Y", strtotime($record['promoabrule_dtend'])),
					'a_promoabrulesection_name' => \FGTA4\utils\SqlUtility::Lookup($record['a_promoabrulesection_id'], $this->db, 'mst_promoabrulesection', 'promoabrulesection_id', 'promoabrulesection_name'),
					'b_promoabrulesection_name' => \FGTA4\utils\SqlUtility::Lookup($record['b_promoabrulesection_id'], $this->db, 'mst_promoabrulesection', 'promoabrulesection_id', 'promoabrulesection_name'),
					'promoabrule_name' => \FGTA4\utils\SqlUtility::Lookup($record['promoabrule_id'], $this->db, 'mst_promoabrule', 'promoabrule_id', 'promoabrule_name'),
					'promoab_commitby' => \FGTA4\utils\SqlUtility::Lookup($record['promoab_commitby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),

					'_createby' => \FGTA4\utils\SqlUtility::Lookup($record['_createby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
					'_modifyby' => \FGTA4\utils\SqlUtility::Lookup($record['_modifyby'], $this->db, $GLOBALS['MAIN_USERTABLE'], 'user_id', 'user_fullname'),
				]);

				\FGTA4\utils\SqlUtility::WriteLog($this->db, $this->reqinfo->modulefullname, $tablename, $id, 'COMMIT', $userdata->username, (object)[]);

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



	public function save_and_set_commit_flag($id, $currentdata) {
		try {
			$sql = " 
				update $this->main_tablename
				set 
				$this->field_iscommit = 1,
				$this->field_commitby = :username,
				$this->field_commitdate = :date
				where
				$this->main_primarykey = :id
			";

			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				":id" => $id,
				":username" => $currentdata->user->username,
				":date" => date("Y-m-d H:i:s")
			]);

		} catch (\Exception $ex) {
			throw $ex;
		}	
	}	
};



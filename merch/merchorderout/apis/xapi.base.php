<?php namespace FGTA4\apis;

if (!defined('FGTA4')) {
	die('Forbiden');
}

require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/core/couchdbclient.php';
// /* Enable Debugging */
// require_once __ROOT_DIR.'/core/debug.php';

use \FGTA4\exceptions\WebException;
// use \FGTA4\debug;
use \FGTA4\CouchDbClient;



/**
 * retail/merch/merchorderout/apis/xapi.base.php
 *
 * merchorderoutBase
 * Kelas dasar untuk keperluan-keperluan api
 * kelas ini harus di-inherit untuk semua api pada modul merchorderout
 *
 * Agung Nugroho <agung@fgta.net> http://www.fgta.net
 * Tangerang, 26 Maret 2021
 *
 * digenerate dengan FGTA4 generator
 * tanggal 01/11/2023
 */
class merchorderoutBase extends WebAPI {

	protected $main_tablename = "trn_merchorderout";
	protected $main_primarykey = "merchorderout_id";
	protected $main_field_version = "merchorderout_version";	
	
	protected $field_iscommit = "merchorderout_iscommit";
	protected $field_commitby = "merchorderout_commitby";
	protected $field_commitdate = "merchorderout_commitdate";		
			
	



	function __construct() {

		// $logfilepath = __LOCALDB_DIR . "/output//*merchorderout*/.txt";
		// debug::disable();
		// debug::start($logfilepath, "w");

		$DB_CONFIG = DB_CONFIG[$GLOBALS['MAINDB']];
		$DB_CONFIG['param'] = DB_CONFIG_PARAM[$GLOBALS['MAINDBTYPE']];		
		$this->db = new \PDO(
					$DB_CONFIG['DSN'], 
					$DB_CONFIG['user'], 
					$DB_CONFIG['pass'], 
					$DB_CONFIG['param']
		);

		$FSCONFIGNAME = $GLOBALS['MAINFS'];
		$this->cdb = new CouchDbClient((object)DB_CONFIG[$FSCONFIGNAME]);
	}


	public function get_header_row($id) {
		try {
			$sql = "
				select 
				A.*
				from 
				$this->main_tablename A 
				where 
				A.$this->main_primarykey = :id 
			";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([":id" => $id]);
			$rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
			if (!count($rows)) { throw new \Exception("Data '$id' tidak ditemukan"); }
			return (object)$rows[0];
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

}
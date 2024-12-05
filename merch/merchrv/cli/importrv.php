<?php namespace FGTA4;


require_once __ROOT_DIR.'/core/sqlutil.php';
require_once __ROOT_DIR.'/core/webapi.php';	

require_once __DIR__ . '/syncworkerbase.php';


use  \FGTA4\utils\SqlUtility;

class SCENARIO {
	public static $username;
	public static $id;
	public static $param;
};




/*
 * Syncronisasi RV dari TB ke kalista
 * 
 */
console::class(new class($args) extends syncWorkerBase {
	private array $params;
	private bool $isDevMode = true;


	function __construct($args) {
		parent::__construct($args);

		// get executing parameter
		$this->params = $args->params;
	}

	function execute() {

		$name = $this->params['--name'];
		$pid = $this->params['--pid'];
		$username = $this->params['--username'];
		$data = $this->params['--data'];
		$this->logfile = array_key_exists('--log', $this->params) ? $this->params['--log'] : null;


		SCENARIO::$username = $username;
		echo "Execute long process\r\n";
		
		try {
			$this->registerProcess($name, $pid, $username);

			// ekstract data parameter
			$args = json_decode($data);
			if (!property_exists($args, 'region_id')) {
				throw new \Exception('parameter region_id belum didefinisikan di parameter cli --data');
			}

			if (!property_exists($args, 'hemoving_id')) {
				throw new \Exception('parameter hemoving_id belum didefinisikan di parameter cli --data');
			}

			if (!property_exists($args, 'merchrv_id')) {
				throw new \Exception('parameter merchrv_id belum didefinisikan di parameter cli --data');
			}

			$region_id = $args->region_id;
			$hemoving_id = $args->hemoving_id;
			$merchrv_id = $args->merchrv_id;

			

			SCENARIO::$id = $hemoving_id;
			SCENARIO::$param = [];

			$progress = 1;
			$this->updateProcess($progress, "cek region mapping for $region_id");
			$brand_id = $this->getBrandId($region_id);
			$refdata = $this->getUnitRef($region_id);

			$unit_id = $refdata['unit_id'];
			$dept_id = $refdata['dept_id'];

			// Jalankan persiapan disini
			$url = 'http://ws.transfashionindonesia.com/crossroads/apis';
			$endpoint = "$url/getrv.php?id=$hemoving_id&region=$region_id";

			$progress = 2;
			$this->updateProcess($progress, "getting data from $endpoint");
			
			$ch = curl_init($endpoint);
			curl_setopt_array($ch, self::CurlOPTIONS);
			$jsoncontent  = curl_exec($ch);
			curl_close($ch);
			

			/*
			$tempdata = __DIR__ . '/dummyrvdata.json';
			$fp = fopen($tempdata, "w");
			fwrite($fp, $jsoncontent);
			fclose($fp);

			$tempdata = __DIR__ . '/dummyrvdata.json';
			$fp = fopen($tempdata, "r");
			$jsoncontent = fread($fp, filesize($tempdata));
			fclose($fp);
			*/			


			$res = json_decode($jsoncontent);
			if (json_last_error()!=0) {
				throw new \Exception($jsoncontent);
			}

			if ($res->code!=0) {
				throw new \Exception($res->message);
			}

			$kurs = $res->data->kurs;



			$header = $res->data->rv->header;
			$header->unit_id = $unit_id;
			$header->dept_id = $dept_id;
			$header->brand_id = $brand_id;
			$header->username = $username;
			
			
			if (!property_exists($kurs, $header->currency_id)) {
				throw new \Exception("kurs '$header->currency_id' belum ada di data");
			}
			$curr_id = $header->currency_id;
			$header->currency_rate = $kurs->{$curr_id};
			

			$progress = 3;
			$this->updateProcess($progress, "cek season mapping for $header->season_id");
			$merchsea_id = $this->getMerchseaId($header->season_id);
			$header->merchsea_id = $merchsea_id;


			$progress = 4;
			$this->updateProcess($progress, "cek rekanan mapping for $header->rekanan_id");
			$partner_id = $this->getPartnerId($header->rekanan_id);
			$header->partner_id = $partner_id;



			/* Development Only: Hapus dulu data lama */
			if ($this->isDevMode) {
				$this->db->query("SET FOREIGN_KEY_CHECKS=0");
				$this->db->query("delete from trn_merchrvitem where merchrv_id='$merchrv_id'");
				$this->db->query("SET FOREIGN_KEY_CHECKS=1");
			}
			/* End Dev Only */	


			// get data header RV
			$sql = "select * from trn_merchrv where merchrv_id = :merchrv_id";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':merchrv_id'=>$merchrv_id ]);
			$merchrvheader = $stmt->fetch();

		

			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();

			try {
				$progress = 6;
				$this->updateProcess($progress, "creating document");

				$this->filldataRV_item($res->data, $merchrv_id, $merchrvheader, 10, 36);
				// $merchorderin_id = $this->createSO($res->data, 37, 73);
				// $this->createDO($merchorderin_id, 73, 90);


				$this->db->commit();
			} catch (\Exception $ex) {
				$this->db->rollBack();
				throw $ex;
			} finally {
				$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,1);
			}


			$progress = 91;
			$taskdescr = "registering items";
			$this->updateProcess($progress, $taskdescr);


			$this->updateProcess(100, 'done.');
			$this->commitProcess();
		} catch (\Exception $ex) {
			$this->haltProcess($ex->getMessage());
			echo "\r\n\x1b[31m"."ERROR"."\x1b[0m"."\r\n";
			echo "\x1b[1m".$ex->getMessage()."\x1b[0m"."\r\n";
			echo "\r\n";
			echo $ex->getTraceAsString();
		}
	}



	function filldataRV_item(object $data, string $merchrv_id, array $merchrvheader,  int $progress_start, int $progress_end) : void {
		$merchorderout_id  = $merchrvheader['merchorderout_id'];
		$merchship_id  = $merchrvheader['merchship_id'];

		
		try {

			// get shipment info
			$sql = "select * from trn_merchship where merchship_id = :merchship_id";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':merchship_id'=>$merchship_id]);
			$row = $stmt->fetch();
			$merchship_rate = (float)$row['merchship_rate'];


			// get item from PO
			$sql = "
				select orderoutitem_value  
				from trn_merchorderoutitem 
				where merchitem_id = :merchitem_id and merchorderout_id = :merchorderout_id
			";
			$stmtpo = $this->db->prepare($sql);


			$i=0;
			$max = count($data->rv->items);
			$taskportion = $progress_end - $progress_start;			
			$totalRV = 0;
			foreach ($data->rv->items as $item) {
				$i++;

				$cancel = $this->isRequestingCancel();
				if ($cancel) {
					$this->cancelProcess();
					break;
				}

				$qtyRV = (int)$item->qtyRV;
				$totalRV += $qtyRV;

				//print_r($item);
				$merchitem_id = $item->heinvitem_id;
				$qtyPL = (int)$item->qtyPL;
				$qtyRV = (int)$item->qtyRV;

				// ambil valuepo
				$stmtpo->execute([':merchitem_id'=>$merchitem_id, ':merchorderout_id'=>$merchorderout_id]);
				$rowpo = $stmtpo->fetch();

				$merchrvitem_valuepo = 0;
				if ($rowpo!=null) {
					$merchrvitem_valuepo = (float)$rowpo['orderoutitem_value'];
				}

				$obj = new \stdClass;
				$obj->merchrv_id = $merchrv_id;
				$obj->merchrvitem_id = uniqid();
				$obj->merchitem_id = $merchitem_id;
				$obj->merchitem_combo = '';
				$obj->merchitem_name = '';
				$obj->merchrvitem_valuepo = $merchrvitem_valuepo;
				$obj->merchrvitem_value = $merchrvitem_valuepo;
				$obj->merchrvitem_qtyinit = $qtyPL;
				$obj->merchrvitem_qty = $qtyRV;
				$obj->merchrvitem_rate = $merchship_rate;
				$obj->merchrvitem_subtotalvaluefrg = $obj->merchrvitem_valuepo * $obj->merchrvitem_qty;
				$obj->merchrvitem_subtotalvalueidr = $obj->merchrvitem_rate * $obj->merchrvitem_subtotalvaluefrg;
				$obj->_createby = $merchrvheader['_createby'];
				$obj->_createdate = $merchrvheader['_createdate'];

				$progress = $progress_start + ((int)($taskportion*((float)$i/(float)$max)));
				$taskdescr = "processing item:  $merchitem_id " . ($i+1) . " of $max";
				$this->updateProcess($progress, $taskdescr);
	

				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_merchrvitem", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

				
				//$this->updateProcess($progress, $taskdescr);


			}

			$n = $totalRV;

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	


});
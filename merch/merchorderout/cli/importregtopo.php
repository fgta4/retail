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
			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \Exception("Error on parse JSON: " . json_last_error_msg() . " - " . $data);
			}

			if (!property_exists($args, 'region_id')) {
				throw new \Exception("parameter 'region_id' belum didefinisikan");
			}

			if (!property_exists($args, 'heinvregister_id')) {
				throw new \Exception("parameter 'heinvregister_id' belum didefinisikan");
			}


			if (!property_exists($args, 'merchorderout_id')) {
				throw new \Exception("parameter 'merchorderout_id' belum didefinisikan");
			}

			$region_id = $args->region_id;
			$heinvregister_id = $args->heinvregister_id;
			$merchorderout_id = $args->merchorderout_id;

			

			SCENARIO::$id = $heinvregister_id;
			SCENARIO::$param = [];

			$progress = 1;
			$this->updateProcess($progress, "cek region mapping for $region_id");
			$brand_id = $this->getBrandId($region_id);
			$refdata = $this->getUnitRef($region_id);

			$unit_id = $refdata['unit_id'];
			$dept_id = $refdata['dept_id'];

			if ($dept_id==null) {
				throw new \Exception('Cek master data unit, kode dept belum di set di unitref_otherdata');
			}


			// Jalankan persiapan disini
			$url = 'http://ws.transfashionindonesia.com/crossroads/apis';
			$endpoint = "$url/getregister.php?id=$heinvregister_id&region=$region_id";

			$progress = 2;
			$this->updateProcess($progress, "getting data from $endpoint");
			
			
			$ch = curl_init($endpoint);
			curl_setopt_array($ch, self::CurlOPTIONS);
			$jsoncontent  = curl_exec($ch);
			if(curl_errno($ch)){
				throw new \Exception(curl_error($ch));
			}
			curl_close($ch);
			
			/*
			$tempdata = __DIR__ . '/dummyregister.json';
			$fp = fopen($tempdata, "w");
			fwrite($fp, $jsoncontent);
			fclose($fp);
			
			$tempdata = __DIR__ . '/dummyregister.json';
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



			$header = $res->data->header;
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


			$progress = 5;
			$this->updateProcess($progress, "get category mapping");
			$ctglist = &$res->data->ctg;
			$ctgmaps = $this->getCategoryMaps($brand_id, $ctglist);



			/* Development Only: Hapus dulu data lama */
			if ($this->isDevMode) {
				/*	
				$this->db->query("
					delete from mst_merchitem where merchregitem_id in (
						select merchregitem_id from trn_merchregitem where merchreg_id = '$header->heinvregister_id'
					);
				");
				*/
				$this->db->query("SET FOREIGN_KEY_CHECKS=0");
				$this->db->query("delete from trn_merchregitem where merchreg_id='$header->heinvregister_id'");
				$this->db->query("delete from trn_merchreg where merchreg_id='$header->heinvregister_id'");
				$this->db->query("SET FOREIGN_KEY_CHECKS=1");
			}
			/* End Dev Only */	

			
			$this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT,0);
			$this->db->beginTransaction();

			try {
				$progress = 6;
				$this->updateProcess($progress, "creating register header document");
				$regheader = $this->createRegisterHeader($header);


				$items = $res->data->items;
				$max = count($items);
				for ($i=0; $i<$max; $i++) {
					$cancel = $this->isRequestingCancel();
					if ($cancel) {
						$this->cancelProcess();
						break;
					}

					// print_r($ctgmaps[$header->ctg_id]);

					$item = $items[$i];
					$item->unit_id = $header->unit_id;
					$item->dept_id = $header->dept_id;
					$item->brand_id = $header->brand_id;
					$item->merchsea_id = $header->merchsea_id;
					$item->merchitemctg_id = $ctgmaps[$item->ctg_id]['merchitemctg_id'];
					$item->itemctg_id = $ctgmaps[$item->ctg_id]['itemctg_id'];
					$item->itemclass_id = $ctgmaps[$item->ctg_id]['itemclass_id'];
					$item->username = $header->username;

					$line = $item->line;

					$progress = 10 + ((int)(80*((float)$i/(float)$max)));
					$taskdescr = "processing item " . ($i+1) . " of $max (line $line)";
					
					$this->updateProcess($progress, $taskdescr);
					$regitem = $this->createRegisterItem($item);

				}


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

			require __ROOT_DIR . '/apps/retail/merch/merchreg/apis/xtion-generate.php';
			$API->reqinfo = (object)[
				'modulefullname' => 'retail/merch/merchreg'
			];

			$API->auth = new class {
				public function session_get_user() {
					return (object) [
						'username' => SCENARIO::$username
					];
				}			
			};
			$API->useotp = false;
			$result = $API->execute(SCENARIO::$id, SCENARIO::$param);



			// import register to PO
			$this->importRegisterToMerchorderout($heinvregister_id, $merchorderout_id);
			



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


	function createRegisterItem(object $item) : object {
		
		$obj = new \stdClass;
		$obj->merchregitem_id = $item->id . str_pad($item->line, 7, '0', STR_PAD_LEFT);
		// 16926797070000010

		$obj->merchitem_refcode = $item->heinv_id; 
		$obj->merchitem_refitem = substr($item->heinv_id, 0, 11) . $item->colnum; 
		$obj->merchitem_barcode = $item->barcode;
		$obj->merchitem_art = $item->art;
		$obj->merchitem_mat = $item->mat;
		$obj->merchitem_col = $item->col;
		$obj->merchitem_size = $item->size;

		$comboorderdata = 'merchitem_art,merchitem_mat,merchitem_col,merchitem_size';
		$comboorder = explode(',', $comboorderdata);
		$combos = [];
		foreach ($comboorder as $propname) {
			if (!property_exists($obj, $propname)) {
				throw new \Exception("field $propname tidak ada di trn_merchregitem");
			}
			$combos[] = $obj->{$propname};
		}

		$obj->merchitem_combo = implode(' ', $combos);
		$obj->merchitem_name = $item->name;
		$obj->merchitem_descr = $item->descr;
		$obj->merchitem_colnum = $item->colnum;
		$obj->merchitem_pcpline = $item->pcpline;
		$obj->merchitem_pcpgroup = $item->pcpgro;
		$obj->merchitem_pcpcategory = $item->pcpcat;
		$obj->merchitem_colorcode = $item->col;
		$obj->merchitem_colordescr = $item->coldescr;
		$obj->merchitem_gender = $item->gender;
		$obj->merchitem_fit = $item->fit;
		$obj->merchitem_hscodeship = $item->hscodeship;
		$obj->merchitem_hscodeina = $item->hscodeina;
		$obj->merchitem_gtype = $item->gtype;
		$obj->merchitem_labelname = $item->labelname;
		$obj->merchitem_labelproduct = $item->produk;
		$obj->merchitem_bahan = $item->bahan;
		$obj->merchitem_pemeliharaan = $item->pemeliharaan;
		$obj->merchitem_logo = $item->logo;
		$obj->merchitem_dibuatdi = $item->dibuatdi;
		$obj->merchitem_width = $item->width;
		$obj->merchitem_length = $item->length;
		$obj->merchitem_height = $item->height;
		$obj->merchitem_weight = $item->weight;
		$obj->merchitem_fob = $item->price;
		$obj->merchitem_initqty = $item->qty;
		$obj->merchitemctg_id = $item->merchitemctg_id;
		$obj->merchreg_id = $item->id;
		$obj->unit_id = $item->unit_id;
		$obj->dept_id = $item->dept_id;
		$obj->brand_id = $item->brand_id;


		try {
			$mercharticle_id = null;
			$sqlcekartmat = "
				select mercharticle_id
				from mst_mercharticle
				where
				brand_id=:brand_id and mercharticle_art=:art and mercharticle_mat=:mat
			";
			$stmtart = $this->db->prepare($sqlcekartmat);
			$stmtart->execute([
				':brand_id' => $item->brand_id,
				':art' => $item->art,
				':mat' => $item->mat
			]);
			$rowart = $stmtart->fetch();
			if ($rowart!=null) {
				$mercharticle_id = $rowart['mercharticle_id'];
				//$obj->mercharticle_id = $mercharticle_id;
				$obj->mercharticle_paircode = $mercharticle_id;
			}



			$sqlcek = "
				select * from trn_merchregitem where merchregitem_id = :merchregitem_id
			";
			$stmt = $this->db->prepare($sqlcek);
			$stmt->execute([
				':merchregitem_id' => $obj->merchregitem_id
			]);
			$row = $stmt->fetch();
			if ($row==null) {
				if ($mercharticle_id!=null) {
					$obj->mercharticle_id = $mercharticle_id;
				}

				$obj->_createby = $item->username;
				$obj->_createdate = date('Y-m-d H:i:s');
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_merchregitem", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);
			}

			return $obj;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function createRegisterHeader(object $header) : object {
		$obj = new \stdClass;
		$obj->merchreg_id = $header->heinvregister_id;
		$obj->merchreg_date = $header->heinvregister_date;
		$obj->merchreg_descr = $header->heinvregister_descr;
		$obj->curr_id = $header->currency_id;
		$obj->curr_rate = $header->currency_rate;
		$obj->partner_id = $header->partner_id;
		$obj->merchsea_id = $header->merchsea_id;
		$obj->unit_id = $header->unit_id;
		$obj->dept_id = $header->dept_id;
		$obj->brand_id = $header->brand_id;
		$obj->merchregtype_id = 'R01';
		$obj->interface_id = 'TB';
		$obj->merchregtype_iscangenerate = 1;



		try {

			// cek apakah data sudah ada
			$sqlcek = "
				select * from trn_merchreg where merchreg_id = :merchreg_id
			";
			$stmt = $this->db->prepare($sqlcek);
			$stmt->execute([
				':merchreg_id' => $header->heinvregister_id
			]);
			$row = $stmt->fetch();
			if ($row==null) {
				// data belum di sync, buat baru
				$obj->_createby = $header->username;
				$obj->_createdate = date('Y-m-d H:i:s');
	
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_merchreg", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

			} else {
				// perlu ada perubahan ? (not implemented)
			}			

			return $obj;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function importRegisterToMerchorderout(string $heinvregister_id, string $merchorderout_id) : void {
		try {
			/* Development Only: Hapus dulu data lama */
			if ($this->isDevMode) {
				$this->db->query("delete from trn_merchorderoutitem where merchorderout_id='$merchorderout_id'");
			}
			/* End Dev Only */	


			// insert data dari register ke 
			$sql = "
				select A.curr_id, A.curr_rate, B.* 
				from trn_merchreg A inner join trn_merchregitem B on B.merchreg_id = A.merchreg_id
				where A.merchreg_id = :merchreg_id
			";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':merchreg_id'=>$heinvregister_id]);
			$rows = $stmt->fetchall();
			foreach ($rows as $row) {
				$rate = (float)$row['curr_rate'];
				$fob = (float)$row['merchitem_fob'];
				$qty = (int)$row['merchitem_initqty'];
				$idr = $rate * ($fob * $qty);
				
				$obj = new \stdClass;
				$obj->merchorderoutitem_id = uniqid();
				$obj->merchitem_id = $row['merchitem_id'];
				$obj->merchitem_combo = $row['merchitem_combo'];
				$obj->orderoutitem_descr = $row['merchitem_descr'];
				$obj->orderoutitem_qty = (int)$row['merchitem_initqty'];
				$obj->orderoutitem_value = (float)$row['merchitem_fob'];
				$obj->orderoutitem_subtotalvaluefrg = $obj->orderoutitem_value * $obj->orderoutitem_qty; 
				$obj->curr_id = $row['curr_id'];
				$obj->curr_rate = $row['curr_rate'];
				$obj->orderoutitem_subtotalvalueidr = $idr;

				$obj->merchorderout_id = $merchorderout_id;
				$obj->_createby = $row['_createby'];
				$obj->_createdate = date('Y-m-d H:i:s');
				
				$cmd = \FGTA4\utils\SqlUtility::CreateSQLInsert("trn_merchorderoutitem", $obj);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);

			}

			
			// end registration
			$sql = "
				select 
				sum(orderoutitem_qty) as qty,
				sum(orderoutitem_subtotalvaluefrg) as valuefrg,
				sum(orderoutitem_subtotalvalueidr) as valueidr
				from trn_merchorderoutitem where merchorderout_id = :merchorderout_id		
			";

			
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':merchorderout_id' => $merchorderout_id]);
			$row = $stmt->fetch();

			if ($row!=null) {
				$obj = new \stdClass;
				$obj->merchorderout_id = $merchorderout_id;
		
				$obj->orderout_qty = (int)$row['qty'];
				$obj->orderout_totalvaluefrg = (float)$row['valuefrg'];
				$obj->orderout_totalvalueidr = (float)$row['valueidr'];

				$obj->orderout_outstdqty = $obj->orderout_qty;
				$obj->orderout_outstdvaluefrg = $obj->orderout_totalvaluefrg;
				$obj->orderout_outstdvalueidr = $obj->orderout_totalvalueidr;

				$key = new \stdClass;
				$key->merchorderout_id = $obj->merchorderout_id;

				$cmd = \FGTA4\utils\SqlUtility::CreateSQLUpdate("trn_merchorderout", $obj, $key);
				$stmt = $this->db->prepare($cmd->sql);
				$stmt->execute($cmd->params);
			}
			


		} catch (\Exception $ex) {
			throw $ex;
		}
	}




});
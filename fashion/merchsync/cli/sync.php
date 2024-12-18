<?php namespace FGTA4;


require_once __ROOT_DIR . '/core/sqlutil.php';
require_once __ROOT_DIR . '/core/webapi.php';	
require_once __ROOT_DIR . '/core/cliworker.php';	


require_once dirname(__FILE__) . '/tbdatamap.php';	

require_once dirname(__FILE__) . '/sync--base.php';	

require_once dirname(__FILE__) . '/sync-sales.php';	
require_once dirname(__FILE__) . '/sync-register.php';
require_once dirname(__FILE__) . '/sync-do.php';	
require_once dirname(__FILE__) . '/sync-aj.php';	
require_once dirname(__FILE__) . '/sync-tr.php';	
require_once dirname(__FILE__) . '/sync-rv.php';	

// require_once dirname(__FILE__) . '/registertmp.php';	
// require_once dirname(__FILE__) . '/registersync.php';	



use  \FGTA4\utils\SqlUtility;

class SCENARIO {
	public static $username;
	public static $id;
	public static $param;
};


console::class(new class($args) extends cliworker {
	const _DELAY_BETWEEN_LOOP = 1; // seconds
	
	private array $params;
	private bool $isDevMode = true;
	private object $rptdb;


	private $stmt_get_fail_number;


	function __construct($args) {
		// get executing parameter
		$this->params = $args->params;

		parent::__construct($args);
	}

	function execute() {

		$name = $this->params['--name'];
		$pid = $this->params['--pid'];
		$username = $this->params['--username'];
		$data = $this->params['--data'];
		$this->logfile = array_key_exists('--log', $this->params) ? $this->params['--log'] : null;

		SCENARIO::$username = $username;
		

		// set mapping
		TbDataMap::$db = $this->db;


		

		$cfg = [
			'url' => 'http://ws.transfashion.id/crossroads/frontend',
		];


		
		$syncSales = new SyncSales($cfg);
		$syncReg = new SyncRegister($cfg);
		$syncDO = new SyncDO($cfg);
		$syncAJ = new SyncAJ($cfg);
		$syncTR = new SyncTR($cfg);
		$syncRV = new SyncRV($cfg);

		$syncPricing = new \stdClass;
		$syncClosing = new \stdClass;
		
		
		
		

		$progs = [
			'SL' => ['instance'=>$syncSales, 'method'=>'Sync', 'skip'=>false],

			'REG' => ['instance'=>$syncReg, 'method'=>'Sync', 'skip'=>false],

			'DO-POSTALL'  => ['instance'=>$syncDO, 'method'=>'PostAll', 'skip'=>false],

			'AJ-POSTALL' => ['instance'=>$syncAJ, 'method'=>'PostAll', 'skip'=>false],

			'TR-PROP' => ['instance'=>$syncTR, 'method'=>'Prop', 'skip'=>false],
			'TR-SEND' => ['instance'=>$syncTR, 'method'=>'Send', 'skip'=>false],
			'TR-RECV' => ['instance'=>$syncTR, 'method'=>'Recv', 'skip'=>false],
			'TR-UNPROP' => ['instance'=>$syncTR, 'method'=>'UnProp', 'skip'=>false],
			'TR-UNSEND' => ['instance'=>$syncTR, 'method'=>'UnSend', 'skip'=>false],
			'TR-UNRECV' => ['instance'=>$syncTR, 'method'=>'UnRecv', 'skip'=>false],
			
			'RV-SEND' => ['instance'=>$syncRV, 'method'=>'Send', 'skip'=>false],
			'RV-RECV' => ['instance'=>$syncRV, 'method'=>'Recv', 'skip'=>false],
			'RV-POST' => ['instance'=>$syncRV, 'method'=>'Post', 'skip'=>false],
			'RV-UNSEND' => ['instance'=>$syncRV, 'method'=>'UnSend', 'skip'=>false],
			'RV-UNRECV' => ['instance'=>$syncRV, 'method'=>'UnRecv', 'skip'=>false],
			'RV-UNPOST' => ['instance'=>$syncRV, 'method'=>'UnPost', 'skip'=>false],

			'PRC' => ['instance'=>$syncPricing, 'method'=>'Sync', 'skip'=>true],

			'INV-CLOsE' => ['instance'=>$syncClosing, 'method'=>'Close', 'skip'=>true],
			'INV-OPEN' => ['instance'=>$syncClosing, 'method'=>'Open', 'skip'=>true],

		];
		
		try {		
			echo "Registering long process\r\n";
			$this->registerProcess($name, $pid, $username);


			$this->updateProcess(0, "Syncing start");
			$this->removeQueCompleted();
			$this->resetQueTimeout();
			$this->resetSkipped();
			
			
			$batch_id = uniqid();
			$txcount = $this->createSyncBatch($batch_id, 10);
			while ($txcount>0) {
				$rows = $this->getQueues($batch_id);

				foreach ($rows as $row) {
					$merchsync_id = $row['merchsync_id'];
					$merchsync_doc = $row['merchsync_doc'];
					$merchsync_type = $row['merchsync_type'];

					$prog = $progs[$merchsync_type];
					$instance = $prog['instance'];
					$methodname = $prog['method'];
					$skip = $prog['skip'];

					if ($skip) {
						$this->setSkipped($merchsync_id);
						$this->updateProcess(0, "Syncing $merchsync_id $merchsync_type $merchsync_doc ...SKIP");
						continue;
					} else {
						$this->updateProcess(0, "Syncing $merchsync_id $merchsync_type $merchsync_doc");
						$this->setProcessingFlag($merchsync_id, 1);
					}
				
				
					if (!array_key_exists($merchsync_type, $progs)) {
						throw new \Exception("Sync untuk '$merchsync_type' belum didefinisikan");
					}

					if (!method_exists($instance, $methodname)) {
						$msg = "Method '$methodname' belum didefinisikan untuk '$merchsync_type'";
						$this->setResult($merchsync_id, 1, $msg);
						throw new \Exception($msg);
					}

					try {
						$instance->{$methodname}($merchsync_id, $merchsync_doc, $merchsync_type);
						$this->setCompleted($merchsync_id);
						$this->setResult($merchsync_id, 0, "");
					} catch (\Exception $ex) {
						$this->setResult($merchsync_id, 1, $ex->getMessage());
					}
					
				}

				// hapus queue yang telah selesai diproses
				$this->removeQueCompleted();
			
				// ambil lagi queue untuk diproses pada loop berikutnya
				$batch_id = uniqid();
				$txcount = $this->createSyncBatch($batch_id, 10);


				if (self::_DELAY_BETWEEN_LOOP > 0) {
					sleep(self::_DELAY_BETWEEN_LOOP);
				}
				

			}

			$this->updateProcess(100, "done.");
			$this->commitProcess();

		} catch (\Exception $ex) {
			$this->haltProcess($ex->getMessage());
			echo "\r\n\x1b[31m"."ERROR"."\x1b[0m"."\r\n";
			echo "\x1b[1m".$ex->getMessage()."\x1b[0m"."\r\n";
			echo "\r\n";
			echo $ex->getTraceAsString();
		}
	}			


	function removeQueCompleted() : void {
		$this->updateProcess(0, "Remove Completed Queue");
		/*
		hapus queue yang sudah selesai (cek field merchsync_iscompleted=1)
		agar tidak perlu diexekusi lagi pada proses berikutnya 
		*/
		try {
			$sql = "delete from fsn_merchsync where merchsync_iscompleted=1";
			$this->db->exec($sql);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function resetQueTimeout() : void {
		$this->updateProcess(0, "Reset Timeout Queue");
		/*
		reset merchsync_batch dan isprocessing queue yang telah timeout
		*/

		try {
			$sql = "
				update fsn_merchsync
				set 
				merchsync_batch=null
				where
				merchsync_timeout > now()
			";
			$this->db->exec($sql);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	
	function resetSkipped() : void {
		$this->updateProcess(0, "Reset Skipped Queue");
		/*
		reset merchsync_batch dan isprocessing queue yang telah timeout
		*/

		try {
			$sql = "
				update fsn_merchsync
				set 
				merchsync_timeout=null,
				merchsync_batch=null,
				merchsync_result=null
				where
				    merchsync_result='SKIP'
				or (merchsync_isprocessing=0 and merchsync_batch is not null and merchsync_iscompleted=0)
			";
			$this->db->exec($sql);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function createSyncBatch(string $batch_id, int $maxtx) : int {
		$this->updateProcess(0, "Get UnProcessed Queue ...");

		/*
		ambil transaksi sejumlah $maxtx yang belum di synkronisasi, 
		kembalikan jumlah yang ditemukan 
		*/
		try {
			$sqlupd = "
				update fsn_merchsync
				set 
					merchsync_batch = :batch
				where
					merchsync_id = :merchsync_id
			";
			$stmtupd = $this->db->prepare($sqlupd);


			$sql = "
				select *
				from fsn_merchsync 
				where  
				(merchsync_isfail<3 or merchsync_batch is null) and merchsync_type LIKE 'RV%' 
				order by _createby asc limit $maxtx
			";
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$rows = $stmt->fetchAll();
			$count = count($rows);

			foreach ($rows as $row) {
				$merchsync_id = $row['merchsync_id'];
				$stmtupd->execute([
					':batch' => $batch_id,
					':merchsync_id' => $merchsync_id
				]);
			}

			return $count;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function getQueues(string $batch_id) : array {
		try {
			$sql = "
				select * from fsn_merchsync where merchsync_batch = :batch order by _createby asc
			";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':batch' => $batch_id]);
			return $stmt->fetchAll();
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function setProcessingFlag(string $merchsync_id, int $isprocessing) : void {
		try {
			$sql = "
				update fsn_merchsync
				set 
					merchsync_isprocessing = :isprocessing,
					merchsync_timeout = now() + INTERVAL 10 minute
				where
					merchsync_id = :merchsync_id
			";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				':merchsync_id' => $merchsync_id,
				':isprocessing' => $isprocessing
			]);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function setSkipped(string $merchsync_id) : void {
		try {
			$sql = "
				update fsn_merchsync
				set 
					merchsync_isprocessing = 0,
					merchsync_timeout = now(),
					merchsync_result = 'SKIP'
				where
					merchsync_id = :merchsync_id
			";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				':merchsync_id' => $merchsync_id
			]);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	function setResult(string $merchsync_id, int $isfail, string $msg) : void {
		try {
			
			$fail_attempt = 0;
			if ($isfail == 1) {
				$fail_attempt = 1;
				if ($this->stmt_get_fail_number==null) {
					$sqlgetfail = "select merchsync_isfail from fsn_merchsync where merchsync_id = :merchsync_id";
					$this->stmt_get_fail_number = $this->db->prepare($sqlgetfail);
				}
				$this->stmt_get_fail_number->execute([':merchsync_id' => $merchsync_id]);
				$row = $this->stmt_get_fail_number->fetch();
				$fail_attempt = $row['merchsync_isfail'] + 1;
			}


			$sql = "
				update fsn_merchsync
				set 
					merchsync_isfail = :isfail,
					merchsync_result = :msg
				where
					merchsync_id = :merchsync_id
			";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				':merchsync_id' => $merchsync_id,
				':isfail' => $fail_attempt,
				':msg' => substr($msg, 0, 255), // $msg
			]);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	function setCompleted(string $merchsync_id) : void {
		try {
			$sql = "
				update fsn_merchsync
				set 
					merchsync_isprocessing = 0,
					merchsync_iscompleted = 1
				where
					merchsync_id = :merchsync_id
			";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				':merchsync_id' => $merchsync_id
			]);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}




});	
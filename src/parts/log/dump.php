<?php
namespace nx\parts\log;

use nx\parts\log;

trait dump{
	use log;
	protected function nx_parts_log_dump(): void{
		if(null ===$this->log) $this->log = new \nx\helpers\log();
		$this->log->addWriter($this->log_dump_writer(...));
	}
	protected function log_dump_writer($log, $is_logs =false): void{
		if($is_logs){
			foreach($log as $item){
				var_dump($item);
			}
		} else {
			var_dump($log);
		}
	}
}
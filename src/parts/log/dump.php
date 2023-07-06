<?php

namespace nx\parts\log;

use nx\parts\log;

/**
 * @property string $log_dump_name     logger的名称，允许覆盖（只保留最后一个），或并存（不同名称）
 * @property bool   $log_dump_deferred 是否为延迟输出 true 为结束时输出 false 立刻
 */
trait dump{
	use log;

	protected function nx_parts_log_dump(): void{
		if(PHP_SAPI !== 'cli') return;
		if(null === $this->log){
			$this->log = new \nx\helpers\log();
		}
		$this->log->addWriter($this->log_dump_writer(...),
			$this->log_dump_name ?? 'default',
			$this->log_dump_deferred ?? false
		);
	}
	protected function log_dump_writer($log): void{
		if($this->log_dump_deferred ?? false){
			foreach($log as $item){
				var_dump($item);
			}
		}
		else{
			var_dump($log);
		}
	}
}
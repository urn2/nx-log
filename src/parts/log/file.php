<?php

namespace nx\parts\log;

use nx\parts\log;
use nx\parts\path;

/**
 * @method string getPath(string $sub_path)
 * @property string                  $log_file_name     logger的名称，允许覆盖（只保留最后一个），或并存（不同名称）
 * @property bool                    $log_file_deferred 是否为延迟输出 true 为结束时输出 false 立刻
 */
trait file{
	use log, path;

	protected function nx_parts_log_file(): \Generator{
		$this->nx_parts_log();
		$deferred = $this->log_file_deferred ?? true;
		$this->log->addWriter($this->log_file_writer(...),
			$this->log_file_name ?? 'default',
			$deferred
		);
		$setup = $this['log/file'] ?? [];
		$path = $setup['path'] ?? $this->getPath('./logs/');
		$name = date($setup['name'] ?? 'Y-m-d');
		$this['log/file:list']=[];
		if(!$deferred){
			$this['log/file:handle'] = @fopen($path . $name . '.log', 'ab');
		}
		yield;
		if(!$deferred){
			if($this['log/file:handle']) fclose($this['log/file:handle']);
		}
		else{
			unset($this->log);//触发writer
			$handle = @fopen($path . $name . '.log', 'ab');
			if($handle){
				fwrite($handle, implode("\n", $this['log/file:list']) . "\n");
				fclose($handle);
			}
		}
	}
	private function log_file_line($log): string{
		//$step =sprintf("%06.2fms", $log['ms']);
		$time = date("Y-m-d H:i:s", $log['timestamp']);
		$v = \nx\helpers\log::interpolate($log['message'], $log['context']);
		$r ="$time {$this['app:uuid']} [{$log['level']}] $v";
		if('runtime' !==$log['level'] && !empty($log['trace'])) $r .="  <- {$log['trace']['file']}:{$log['trace']['line']}";
		return $r;
	}
	protected function log_file_writer($log): void{
		if($this->log_file_deferred ?? true){
			$this['log/file:list'] = array_map(fn($l) => $this->log_file_line($l), $log);
		}
		else{
			if($this['log/file:handle']) fwrite($this['log/file:list'], $this->log_file_line($log) . "\n");
		}
	}
}
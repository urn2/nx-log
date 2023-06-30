<?php

namespace nx\parts\log;

use nx\parts\log;

/**
 * @property-read                    $uuid
 * @property-read \nx\helpers\buffer $buffer
 * @method string getPath
 */
trait file{
	use log, \nx\parts\path;

	protected function nx_parts_log_file(): \Generator{
		if(null === $this->log) $this->log = new \nx\helpers\log();
		$this->log->addWriter($this->log_file_writer(...), 'file');
		$setup = $this->setup['log/file'] ?? [];
		$path = $setup['path'] ?? $this->getPath('./logs/');
		$name = date($setup['name'] ?? 'Y-m-d');
		$this->buffer['log/file'] = ['list' => []];
		yield;
		unset($this->log);//触发writer
		$handle = @fopen($path . $name . '.log', 'ab');
		if($handle){
			fwrite($handle, implode("\n", $this->buffer['log/file']['list']) . "\n");
			fclose($handle);
		}
	}
	private function log_file_line($log):string{
		//$step =sprintf("%06.2fms", $log['ms']);
		$time =date("Y-m-d H:i:s", $log['timestamp']);
		$v =\nx\helpers\log::interpolate($log['message'], $log['context']);
		return "$time $this->uuid [{$log['level']}] $v";
	}
	protected function log_file_writer($log, $is_logs = false): void{
		if($is_logs){
			$this->buffer['log/file']['list'] = array_map(fn($l)=>$this->log_file_line($l), $log);
		}
		else{
			$this->buffer['log/file']['list'][] = $this->log_file_line($log);
		}
	}
}
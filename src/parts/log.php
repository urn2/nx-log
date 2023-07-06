<?php

namespace nx\parts;
trait log{
	public ?\nx\helpers\log $log = null;
	protected function nx_parts_log(): void{
		if(null === $this->log){
			$this->log = new \nx\helpers\log();
		}
	}
	/**
	 * info级别log
	 *
	 * @param        $message
	 * @param array  $context
	 * @param string $level
	 * @return void
	 */
	public function log($message, array $context = [], string $level='info'): void{
		$this->log->log($level, $message, $context);
	}
}
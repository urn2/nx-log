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
	 * @param mixed ...$args
	 * @return void
	 */
	public function log(...$args): void{
		$this->log->log('info', null, $args);
	}
}
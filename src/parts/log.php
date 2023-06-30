<?php
/** @noinspection PhpUndefinedClassInspection */

namespace nx\parts;
trait log{
	public ?\nx\helpers\log $log = null;
	protected function nx_parts_log(): void{
		if(null ===$this->log) $this->log = new \nx\helpers\log();
	}
	/**
	 * info级别log
	 *
	 * @param \Stringable|string $message
	 * @param array              $context
	 * @return void
	 */
	public function log(\Stringable|string $message, array $context = []): void{
		$this->log->info($message, $context);
	}
}
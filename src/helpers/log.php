<?php
/** @noinspection PhpUndefinedClassInspection */

namespace nx\helpers;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class log extends NullLogger{
	protected bool $deferred = false;
	protected array $logs = [];
	protected array $writers = [];
	protected float $start =0;
	public function __construct($deferred = false){
		$this->deferred = $deferred;
		$this->start =microtime(true);
	}
	public function __destruct(){
		if($this->deferred && !empty($this->logs)){
			foreach($this->writers as $logger){
				$logger($this->logs, true);
			}
		}
	}
	public function addWriter(callable $writer, string $name = 'default'): void{
		$this->writers[$name] = $writer;
		var_dump('log writers: ', count($this->writers));
	}
	/**
	 * 用上下文信息替换记录信息中的占位符
	 */
	static public function interpolate($message, array $context = []): string{
		$replaces = [];
		foreach($context as $key => $val){
			if(is_bool($val)){
				$val = $val ? "TRUE" : "FALSE";
			}
			elseif(is_null($val) || is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))){
				$val = (string)$val;
			}
			elseif(is_array($val) || is_object($val)){
				$val = @json_encode($val);
			}
			else{
				$val = '[type: ' . gettype($val) . ']';
			}
			$replaces['{' . $key . '}'] = $val;
		}
		return strtr($message, $replaces);
	}
	/**
	 * 可任意级别记录日志。
	 *
	 * @param mixed $level
	 * @param       $message
	 * @param array $context
	 */
	public function log($level, $message, array $context = []): void{
		$log = [
			'ms'=>microtime(true) -$this->start,
			'timestamp' => time(),
			'level' => $level,
			'message' => $message,
			'context' => $context,
		];
		if($this->deferred){
			$this->logs[] = $log;
		}
		else{
			foreach($this->writers as $logger){
				$logger($log);
			}
		}
	}
}
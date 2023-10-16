<?php

namespace nx\helpers;

use nx\parts\output\rest;
use Psr\Log\NullLogger;

class log extends NullLogger{
	protected array $writers = [];
	protected array $deferredLogs = [];
	protected array $deferredWriters = [];
	protected float $start = 0;
	public function __construct($start = 0){
		$this->start = 0 === $start ? microtime(true) : $start;
	}
	public function __destruct(){
		if(!empty($this->deferredWriters)){
			foreach($this->deferredWriters as $logger){
				$logger($this->deferredLogs);
			}
		}
	}
	/**
	 * 添加多个日志处理方法
	 *
	 * @param callable $writer   回调方法
	 * @param string   $name     名称，默认为 default 可覆盖
	 * @param bool     $deferred 是否延期输出 true 为解构时， false 立刻
	 * @return void
	 */
	public function addWriter(callable $writer, string $name = 'default', bool $deferred = false): void{
		unset($this->deferredWriters[$name], $this->writers[$name]);
		if($deferred) $this->deferredWriters[$name] = $writer;
		else $this->writers[$name] = $writer;
	}
	/**
	 * 用上下文信息替换记录信息中的占位符
	 */
	public static function interpolate($message, array $context = []): string{
		if(null ===$message){
			$r =[];
			foreach($context as $key => $value){
				$r[] ='{'.$key.'}';
			}
			return static::interpolate(implode(" ", $r), $context);
		}
		$replaces = [];
		foreach($context as $key => $val){
			if(is_bool($val)){
				$val = $val ? "TRUE" : "FALSE";
			}
			elseif(is_null($val) || is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))){
				$val = (string)$val;
			}
			elseif(is_array($val) || is_object($val)){
				$val = @json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
		if(!empty($this->deferredWriters)){
			$this->deferredLogs[] = [
				'ms' => microtime(true) - $this->start,
				'timestamp' => time(),
				'level' => $level,
				'message' => $message,
				'context' => $context,
				'trace' =>$this->backtrace(),
			];
		}
		if(!empty($this->writers)){
			$log = [
				'ms' => microtime(true) - $this->start,
				'timestamp' => time(),
				'level' => $level,
				'message' => $message,
				'context' => $context,
				'trace' =>$this->backtrace(),
			];
			foreach($this->writers as $logger){
				$logger($log);
			}
		}
	}
	protected function backtrace($max=10):array{
		$dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $max);
		$start = 0;//$this->log()
		$count =count($dbt)-1;
		//定位到 __class__->log()
		while(!('log' === $dbt[$start]['function'] && __CLASS__ ===$dbt[$start]['class'])){
			$start += 1;
			if($start >$count) break;
		}
		if($start >$count){//$this->log->debug() <-- 未定位成功，重置
			$start =0;
			while("Psr\Log\AbstractLogger" !==$dbt[$start]['class']){
				$start += 1;
				if($start >$count) break;
			}
			if($start >$count){//$this->log->log() <-- 未定位成功，重置
				$start =0;
				while(__CLASS__ !==$dbt[$start]['class']){
					$start += 1;
					if($start >$count) break;
				}
			}
		}
		if($start<$count){//跳过 trait 或 自行封装的方法
			while('log' === $dbt[$start]['function'] || '__call' === $dbt[$start]['function']){
				$start += 1;
				if($start >$count) break;
			}
		}
		//array_walk($dbt, function($t, $k){
		//	printf("%s %s:%s %s%s%s()\n", $k, $t['file'] ?? '', $t['line'] ?? '', $t['class'] ?? '', $t['type'] ?? '', $t['function'] ?? '');
		//
		//});
		$r= [
			'file'=>$dbt[$start-1]['file'] ?? '',
			'line'=>$dbt[$start-1]['line'] ?? 0,
			'class'=>$dbt[$start]['class'] ?? '',
			'type'=>$dbt[$start]['type'] ?? '',
			'function'=>$dbt[$start]['function'] ?? '',
		];
		//var_dump($r);
		return $r;
		//var_dump($start);
		//
		//return $dbt;
	}
}
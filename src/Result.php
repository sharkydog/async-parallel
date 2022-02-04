<?php
namespace SharkyDog\Async\Parallel;
use SharkyDog\Async;
use parallel;

class Result extends Async\Result {
	private $result;
	private $worker;
	
	public function __construct(parallel\Future $result, Worker $worker) {
		$this->result = $result;
		$this->worker = $worker;
	}
	public function __destruct() {
		ppn('destruct: '.static::class);
	}
	
	protected function _done(): bool {
		return $this->result->done();
	}
	
	protected function _value() {
		return $this->result->value();
	}
}
<?php
namespace SharkyDog\Async\Parallel;
use SharkyDog\Async;
use parallel;

class Worker extends Async\Worker {
	protected static $resultClass = Result::class;
	private static $defaultBootstrap;
	private $thread;
	
	public static function defaultBootstrap($bootstrap) {
		self::$defaultBootstrap = $bootstrap;
	}
	
	public function __construct($bootstrap=null) {
		if($bootstrap === null) $bootstrap = self::$defaultBootstrap;
		if($bootstrap) {
			$this->thread = new parallel\Runtime($bootstrap);
		} else {
			$this->thread = new parallel\Runtime();
		}
	}
	
	public function __destruct() {
		if($this->thread) $this->thread->kill();
		parent::__destruct();
	}
	
	protected function _run(\Closure $task, array $argv=[]): Async\Result {
		return new static::$resultClass($this->thread->run($task, $argv), $this);
	}
	
	protected function _kill(): void {
		if(!$this->thread) return;
		$this->thread->kill();
		$this->thread = null;
	}
}
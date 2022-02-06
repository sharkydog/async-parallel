<?php
use SharkyDog\Async\Parallel\Worker;
//SharkyDog\Async\Debug::init(3);

// results can be retrieved either with Result::onDone() callback
// or after Result::done() returns true

$t = microtime(true);

$task = function($p){
	sleep(1);
	print 'out: '.$p;
	return 'ret: '.$p;
};

$resultDone = function($r){
	print_r([
		'ret' => $r->ret(),
		'out' => $r->out()
	]);
};

$w1 = new Worker;
$r1 = $w1->run($task, 'Task 1');
$r1->onDone($resultDone);

$w2 = new Worker;
$r2 = $w2->run($task, 'Task 2');
$r2->onDone($resultDone);

// wait for tasks to finish
while(!$r1->done()) usleep(100);
while(!$r2->done()) usleep(100);

print_r([
	'ret' => [$r1->ret(),$r2->ret()],
	'out' => [$r1->out(),$r2->out()]
]);

unset($w1,$w2,$r1,$r2);
echo 'time: '.round(microtime(true)-$t,3).'s'."\n";
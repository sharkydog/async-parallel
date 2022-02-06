<?php
use SharkyDog\Async\Parallel\Worker;
use SharkyDog\Async\Results;

// use Results to track several tasks
// multiple Results can be used to track different sets of the same tasks
//
// 'id' property of Results is used here only to identify the particular Results object
// it's not defined in the class

$t = microtime(true);

$task = function($p){
	sleep(1);
	print 'out: '.$p;
	return 'ret: '.$p;
};

$resultsDone = function($arrRs,$rs){
	print_r([
		'src' => $rs->id,
		'ret' => array_map(fn($r)=>$r->ret(), $arrRs),
		'out' => array_map(fn($r)=>$r->out(), $arrRs)
	]);
};

$w1 = new Worker;
$w2 = new Worker;

$r1 = $w1->run($task, 'Task 1');
$r2 = $w2->run($task, 'Task 2');

// result keys can be numeric or something more meaningful

$rs1 = new Results([$r1,$r2]);
$rs1->id = 'rs1';
$rs1->onDone($resultsDone);

$rs2 = new Results(['t1'=>$r1,'t2'=>$r2]);
$rs2->id = 'rs2';
$rs2->onDone($resultsDone);

$rs3 = new Results;
$rs3->id = 'rs3';
$rs3->add($r1, 'task1');
$rs3->add($r2, 'task2');
$rs3->onDone($resultsDone);

// wait for tasks to finish
// since all of the Results here hold the same tasks only one need to be waited
// when all tasks a given Results object holds are finished
// onDone() will be called and done() will return true
while(!$rs1->done()) usleep(100);

// this can be called once per Results object
// once pulled, results will no longer be in the Results object
$arrRs = $rs1->pull();

// cleanup
// Results will hold the Result objects even after onDone()
// in turn Result will hold Worker
// this is to allow results to be retrieved later with Results::pull() and not in onDone()
// this call is not needed if pull() was used
// you can also pull() or clearDone() in onDone() callback
$rs1->clearDone();
$rs2->clearDone();
$rs3->clearDone();

print_r([
	'ret' => [$r1->ret(),$r2->ret()],
	'out' => [$r1->out(),$r2->out()]
]);

unset($w1,$w2,$r1,$r2,$rs1,$rs2,$rs3,$arrRs);
echo 'time: '.round(microtime(true)-$t,3).'s'."\n";
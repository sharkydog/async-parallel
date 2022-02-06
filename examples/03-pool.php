<?php
use SharkyDog\Async\Parallel\Worker;
use SharkyDog\Async\Results;
use SharkyDog\Async\Pool;

$t = microtime(true);

// create new Pool for Worker with max two workers
// tasks will be send to a free worker (no running tasks)
// if none, new will be created up to the pool size (2 in this example)
// if pool size has been reached,
// new task will be send to a busy worker with least tasks
$p = new Pool(Worker::class, 2);

// the pool may be resized later
// even if the new size is lower, workers will not be closed if they have running tasks
$p->setSize(3);

// minimum workers can be set
// this will immediately create new workers if the total count is lower than minimum
$p->setSize(2,2);

// reduce minimum workers to one
// if currently we have two workers, one will be closed if it's free
// if it's not, it will be closed when all tasks finish
// but one will remain, even if it's free
$p->setSize(2,1);

// close all free workers when finished
// * now, as in this example all the workers do not have any tasks yet
$p->setSize(2);

$task = function($p){
	sleep(1);
	print 'out: '.$p;
	return 'ret: '.$p;
};

$resultsDone = function($arrRs,$rs){
	print_r([
		'ret' => array_map(fn($r)=>$r->ret(), $arrRs),
		'out' => array_map(fn($r)=>$r->out(), $arrRs)
	]);
};

// here we are running three tasks with pool size 2
// the first two will be executed in parallel
// the third will wait for one worker to finish

$rs = new Results([
	$p->worker()->run($task, 'Task 1'),
	$p->worker()->run($task, 'Task 2'),
	$p->worker()->run($task, 'Task 3')
]);
$rs->onDone($resultsDone);

// wait for tasks to finish
while(!$rs->done()) usleep(100);

$rs->clearDone();

unset($rs,$p);
echo 'time: '.round(microtime(true)-$t,3).'s'."\n";
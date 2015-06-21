<?php

include_once 'Position.php';
include_once 'RequestManager.php';
include_once 'MemoryData.php';

date_default_timezone_set('Asia/Jerusalem');

$pid = getmypid();

echo 'start :' . date('YmdHis') . 'pid: ' . $pid . "        ";

include_once 'Position.php';
include_once 'RequestManager.php';

//$_req = new ReqestsManager();
//$_pos = new Position();
//$_req->getPositionInLine($_pos);
//zzzWhileObjectFunctionFalse($_req, "okToSendRequest", $_pos);

//apc_store('test123', 'testing1234');
echo '########### ' . MemoryData::getNumRequestsToday() . ' ############';
echo 'end :' . date('YmdHis') . 'pid: ' . $pid . "\n";

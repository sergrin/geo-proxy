<?php

//date_default_timezone_set('Asia/Jerusalem');

$_googUrl = $_POST['url'];
//$_googUrl  = 'http://www.google.com/search?q=blah';

if (!$_googUrl){
        die ("error in request");
}

include_once 'Reporting.php';
Reporting::LogIncommingRequest($_googUrl);


include_once 'Position.php';
//include_once 'RequestManager.php';
include_once 'GoogleProxyMongo.php';

$_cacheLookup = GoogleProxyMongo::findUrl($_googUrl);

if ( $_cacheLookup ){
	
	Reporting::LogCacheHit($_googUrl);
	exit( $_cacheLookup );
	
} else {
	//$_req = new ReqestsManager();
	$_pos = new Position();
	//$_req->getPositionInLine($_pos);
	//zzzWhileObjectFunctionFalse($_req, 'getPositionInLine',$_pos);
	//zzzWhileObjectFunctionFalse($_req, "okToSendRequest", $_pos);

	$_googleResponse = file_get_contents($_googUrl,0,null,null);
	GoogleProxyMongo::storeUrl($_googUrl, $_googleResponse);
	exit( $_googleResponse );
}


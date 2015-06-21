<?php



$_reqUrl = $_POST['url'];
//$_reqUrl  = 'http://gis.eyezeek.atlasct.com/gis/geocoder?q=;city|תaaaל אביב;neighborhood|לב העיר;street|לונץ;&lang=heb&countrycode=isr&key=Gu5rdksS86zC39Sa&output=KML&api=3.0&projection=latlong&ie=utf-8';

if (!$_reqUrl){
        die ("error in request");
}

include_once 'Reporting.php';
Reporting::LogIncommingRequest($_reqUrl);

function cleanUrlAddress($gq){
	$params = array();
	parse_str($gq, $params);
	return http_build_query($params);
}

$_fixedUrlFragments = array(
	parse_url($_reqUrl, PHP_URL_SCHEME),
	'://',
	 parse_url($_reqUrl, PHP_URL_HOST),
	 parse_url($_reqUrl, PHP_URL_PATH),
	'?',
	str_replace('%5C%5C%5C%5C%5C%5C%5C','',cleanUrlAddress(parse_url($_reqUrl, PHP_URL_QUERY)))
);

$_reqUrl = implode('', $_fixedUrlFragments);

include_once 'GoogleProxyMongo.php';


$_cacheLookup = GoogleProxyMongo::findUrl($_reqUrl);

if ( $_cacheLookup ){
	Reporting::LogCacheHit($_reqUrl);
	die( $_cacheLookup );
} else {
	$_googleResponse = file_get_contents($_reqUrl,0,null,null);
	GoogleProxyMongo::storeUrl($_reqUrl, $_googleResponse);
	die( $_googleResponse );
}


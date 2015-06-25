<?php
/**
 * Access layer to google proxy cache stored in local Mongo DB
 *
 * Usage:
 * $_testUrl = "http://www.google.com";
 * GoogleProxyMongo::storeUrl($_testUrl, "this is what I want muhahaha");
 * var_dump( GoogleProxyMongo::findUrl($_testUrl) ); // string(28) "this is what I want muhahaha" 
 *
 * @author romansky
 */
class GoogleProxyMongo {
    
	/**
	 *
	 * @param string $url
	 * @return mixed google response as srting, otherwise return false
	 */
	public static function findUrl($url){
		self::_setup();

		$_filter = array('key' => self::_signUrl($url));
		$success = false;
                while(!$success){
                        try{
				$_findResult = self::$cached->findOne($_filter);
				if( (!$_findResult) && (strpos($url,'google') !== FALSE) ){
					$old_url = str_replace('sensor=false&','sensor=false&client=gme-pingola&',$url);
					$old_url = self::signUrl($old_url);
					$_filter = array('key' => self::_signUrl($old_url));
					$_findResult = self::$cached->findOne($_filter);
				}
				$success = true;
			}
			catch(MongoCursorException $e){
                                //echo "\nMongoCursorException: ".$e->getMessage();
                                sleep(1);
                        }
		}

//		if ($_findResult)
//			self::_incrementCacheCounter();

		return ($_findResult) ? $_findResult['value'] : false;
	}
	
	// Sign a URL with a given crypto key
	// Note that this URL must be properly URL-encoded
	public static function signUrl($myUrlToSign){
		$primary_key = "UJIk8JWkw832MQb2xiiYQ70W_1g=";
		// parse the url
		$url = parse_url($myUrlToSign);

		$urlPartToSign = $url['path'] . "?" . $url['query'];

		// Decode the private key into its binary format
		$decodedKey = self::decodeBase64UrlSafe($primary_key);

		// Create a signature using the private key and the URL-encoded
		// string using HMAC SHA1. This signature will be binary.
		$signature = hash_hmac("sha1",$urlPartToSign, $decodedKey, true);

		$encodedSignature = self::encodeBase64UrlSafe($signature);

		return $myUrlToSign."&signature=".$encodedSignature;
	}
	
	// Encode a string to URL-safe base64
	public static function encodeBase64UrlSafe($value){
		return str_replace(array('+', '/'), array('-', '_'),
		base64_encode($value));
	}
	
	// Decode a string from URL-safe base64
	public static function decodeBase64UrlSafe($value){
		return base64_decode(str_replace(array('-', '_'), array('+', '/'),
		$value));
	}

	/**
	 *
	 * @param string $url the requested google url
	 * @param sring $googleResponse response from google
	 */
	public static function storeUrl($url, $googleResponse){
		if(strpos($googleResponse, 'OVER_QUERY_LIMIT') !== FALSE){
			return;
		}
		self::_setup();

		self::$cached->insert(array(
			'key' => self::_signUrl($url),
			'url' => $url,
			'date' => new MongoDate(),
			'value' => $googleResponse,
			'site' => parse_url($url, PHP_URL_HOST)
		));
	}

	private static function _signUrl($url){
		return md5($url);
	}
	
	private static $cached;
	private static $setup;

	private static function _setup(){

		if ( self::$setup )
				return;
		self::$setup = true;
	
		$connected = false;
		while(!$connected){	
			try
			{

				$_mdb = new MongoClient('mongodb://heroku_3d9m4g8d:eb26lj3oqrt114shqne2ljpgfs@ds053978.mongolab.com:53978/heroku_3d9m4g8d');
				self::$cached = $_mdb->selectDB("googleproxy")->cached;
				self::$cached->ensureIndex(
						array('key' => 1),
						array('unique' => true, 'background' => true)
				);
				$connected = true;

			} catch ( MongoConnectionException $e ) {
				//echo $e->getMessage();				
				sleep(1);
			}
		}
	}

	/**
	 * Depricated, using Reporting.php now
	 */
	private static function _incrementCacheCounter(){

		$_mdb = new Mongo();
		$_reports = $_mdb->selectDB("googleproxy")->reports;
		
		$_reports->ensureIndex(
				array('report' => 1),
				array('unique' => true, 'background' => true)
		);

		if (!$_reports->findOne(array('report' => 'cachehit') )){
			$_reports->insert(array(
				'report' => 'cachehit',
				'count' => 0
			));
		}

		$_filter = array('report' => 'cachehit' );
		$_update = array('$inc' => array('count' => 1));
		$_options['multiple'] = false;
		$_reports->update($_filter, $_update, $_options );

	}
}

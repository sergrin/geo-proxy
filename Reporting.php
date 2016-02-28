<?php
/**
 * Repoting to Mongo DB
 *
 * @author romansky
 */
class Reporting {

	public static function LogCacheHit($url){
		self::_UpdateReport($url, ReportType::CacheHitCount);
	}

	public static function LogIncommingRequest($url){
		self::_UpdateReport($url, ReportType::RequestCount);
	}


	private static function _UpdateReport($url, $reportName){
		$_host = parse_url($url, PHP_URL_HOST);
		$_today = new MongoDate(strtotime('today'));
		$connected = false;
		while(!$connected){
			try{
				$_mdb = new MongoClient('mongodb://heroku:xIklhEn9rZ3qtVglutELuDdNZm4fSZgv-1T_G46FrSvuYIxTbTW4y8uePGq567AITTx0cxTjpknb2Et3Ts59Kw@lamppost.18.mongolayer.com:10228,lamppost.19.mongolayer.com:10205/app38066177');
				$connected = true;
			}
			catch(MongoConnectionException $e){
				//echo "\nMongoConnectionException: ".$e->getMessage();
				sleep(1);
			}
		}
		
		$_reports = $_mdb->selectDB("app38066177")->reports;

		$_rowFilter = array('host' => $_host, 'date' => $_today);
		
		$success = false;
		while(!$success){
			try{
				if (!$_reports->findOne( $_rowFilter )){
					$_reports->insert(array_merge($_rowFilter,
						array(
							ReportType::CacheHitCount => array('count' => 0),
							ReportType::RequestCount => array('count' => 0)
						)
					));
				}
				$success = true;
			}
			catch(MongoCursorException $e){
				//echo "\nMongoCursorException: ".$e->getMessage();
				sleep(1);
			}
		}

		$_reports->ensureIndex(
				array('report' => 1, 'date' => 1),
				array('background' => true)
		);
		
		$_update = array('$inc' => array( $reportName . '.count' => 1 ));
		$_options['multiple'] = false;
		$_reports->update($_rowFilter, $_update, $_options );
		
	}
	
}

abstract class ReportType {
	const RequestCount = 'requestcount';
	const CacheHitCount = 'cachehit';
}

<?php

include_once 'Position.php';

/**
 * @author romansky
 */
class MemoryData {

	private static $_curPosKey = 'curPos';
	private static $_lastPosKey = 'lastPos';
	private static $_reqPerSecKey = 'reqpersec';
	private static $_dayReqKey = 'dayreqs';
	private static $_resettableCounter = 'counter';

	public static function getCurrentPosition(){
		return self::_getCurPosition();
	}

	public static function getLastPosition(){
		return self::_getLastPosition();
	}

	/**
	 * !!Needs to be written Syncronously!
	 */
	public static function getNewPosition(){
		
		self::_incrementRequestsToday();
		$_maxReqNumToday = self::_getRequestsToday();
		$_day = date('Ymd');
		$_newReqId = $_day . $_maxReqNumToday;
		self::_setLastPosition( $_newReqId );
		
		if ( !self::_getCurPosition() ){
			self::_setCurPosition( $_newReqId );
		}

		return $_newReqId;
		
	}

	public static function getSecondsSinceLastRequest(){
		return time() - self::_getLastRequestTime();
	}

	public static function getNumRequestsThisSecond(){
		return self::_getRequestsPerSecond();
	}


	public static function registerSendingRequest(Position $pos){
		// update the this second count
		self::_incrementRequestsInSeconds();
		// finally update the current last position
		self::_setCurPosition($pos->getMyPositionId());
	}

	public static function getNumRequestsResetCounter($dontreset = false) {
		$_numRequests = apc_fetch(self::$_resettableCounter);
		// reset the counter
		if (!$dontreset)
			apc_store(self::$_resettableCounter, 0);
		return $_numRequests;
	}

	public static function getNumRequestsToday(){
		return self::_getRequestsToday();
	}

	private static function _getCurPosition(){
		return apc_fetch(self::$_curPosKey);
	}

	private static function _setCurPosition($curPos){
		apc_store(self::$_curPosKey, $curPos);
	}

	private static function _getLastPosition(){
		return apc_fetch(self::$_lastPosKey);
	}

	private static function _setLastPosition($lastPos){
		apc_store(self::$_lastPosKey, $lastPos);
	}

	private static function _getRequestsPerSecond(){
		$_second = time();
		$_reqSecData = apc_fetch(self::$_reqPerSecKey);
		if (preg_match("/^{$_second}\|[0-9]+/", $_reqSecData)){
			$_tmp = explode('|', $_reqSecData);
			return $_tmp[1];
		} else {
			return 0;
		}
	}

	private static function _getLastRequestTime(){
		$_reqSecData = apc_fetch(self::$_reqPerSecKey);
		$_tmp = explode('|', $_reqSecData);
		return $_tmp[0];
	}

	private static function _incrementRequestsInSeconds(){
		$_second = time();
		$_reqNum = self::_getRequestsPerSecond() +1;
		$_reqInSec = $_second . '|' . $_reqNum;
		apc_store(self::$_reqPerSecKey, $_reqInSec);
		$_reqSoFar = apc_fetch(self::$_resettableCounter);
		apc_store(self::$_resettableCounter, $_reqSoFar * 1 +1);
	}

	private static function _getRequestsToday(){
		$_todayReqData = apc_fetch(self::$_dayReqKey);
		$_day = date('Ymd');
		if (preg_match("/^{$_day}[0-9]+/", $_todayReqData)){
			return substr($_todayReqData, 8);
		} else {
			return 0;
		}
	}

	private static function _incrementRequestsToday(){
		date_default_timezone_set('Asia/Jerusalem');
		$_day = date('Ymd');
		$_reqsToday = self::_getRequestsToday() +1;
		apc_store(self::$_dayReqKey, $_day . $_reqsToday);
	}

}
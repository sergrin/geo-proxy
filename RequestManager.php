<?php

include_once 'tools.php';
include_once 'Mutex.php';
include_once 'Position.php';
include_once 'MemoryData.php';

/**
 * Description of RequestManager
 *
 * @author romansky
 */
class ReqestsManager{

	// queue ids
	private $_posQId = '123';
	private $_reqQid = '124';

	// semaphores
	/** @var Mutex  */
	private $_positionQueue;
	/** @var Mutex  */
	private $_requestQueue;

	public function ReqestsManager(){

		// position queue semaphore
		$this->_positionQueue = new Mutex();
		$this->_positionQueue->init( $this->_posQId );

		// request sending queue semaphore
		$this->_requestQueue = new Mutex();
		$this->_requestQueue->init( $this->_reqQid );

	}

	/**
	 * Come here to get your number in the requests line!
	 *
	 * @param Position $pos position object to populate with prev position and new position
	 * @return bool sucess of getting the position in the queue
	 */
	public function getPositionInLine(Position &$pos){
		if ($this->_positionQueue->acquire()){
			$pos->setup(MemoryData::getLastPosition(), MemoryData::getNewPosition());
			$this->_positionQueue->release();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Wait for your number to come up so you could send a request
	 *
	 * @return bool OK to send request, otherwise wait
	 */
	public function okToSendRequest(Position $pos){
		if (
				( MemoryData::getNumRequestsThisSecond() <= 9 &&
				MemoryData::getNumRequestsToday() < 100000 ) && (
				$pos->getPrevPositionId() == MemoryData::getCurrentPosition() ||
				MemoryData::getSecondsSinceLastRequest() > 30 )
				) {
			MemoryData::registerSendingRequest($pos);
			return true;
		} else {
			return false;
		}
	}

}
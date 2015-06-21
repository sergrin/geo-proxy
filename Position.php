<?php
/**
 * Holds infomration about the position of an item
 * to be used in conjuntion with RequestManager
 *
 * @author romansky
 */
class Position {

	private $_prevPosId;
	private $_myPosId;

	public function setup($prevPosId, $myPosId){
		$this->_prevPosId = $prevPosId;
		$this->_myPosId = $myPosId;
	}

	public function getMyPositionId(){
		return $this->_myPosId;
	}

	public function getPrevPositionId(){
		return $this->_prevPosId;
	}

}
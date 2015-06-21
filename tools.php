<?php
/* 
 * Some tools to be used 
 */

function zzz(){
	usleep(50000);
}

/**
 *
 * @param object $obj
 * @param string $func
 * @param array $params
 */
function zzzWhileObjectFunctionFalse($obj, $func, $params){

	if ( !is_array($params)){
		$params = array ($params);
	}

	while (true){
		if (! call_user_func_array( array($obj, $func), $params) ){
			zzz();
		} else {
			return;
		}
	}
}


function get_time_difference( $start, $end )
{
    date_default_timezone_set('Europe/Skopje'); 
    $uts['start']      =    strtotime( $start );
    $uts['end']        =    strtotime( $end );
    if( $uts['start']!==-1 && $uts['end']!==-1 )
    {
        if( $uts['end'] >= $uts['start'] )
        {
            $diff    =    $uts['end'] - $uts['start'];
            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;
            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;
            $diff    =    intval( $diff );
            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
        }
        else
        {
            trigger_error( "Ending date/time is earlier than the start date/time", E_USER_WARNING );
        }
    }
    else
    {
        trigger_error( "Invalid date/time data detected", E_USER_WARNING );
    }
    return( false );
}

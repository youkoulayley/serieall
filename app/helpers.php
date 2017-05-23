<?php

use App\Models\Log;

/**
 * @param $logID
 * @param $logMessage
 * @return bool
 * @internal param $logJob
 * @internal param $logObjet
 * @internal param $logName
 */
function saveLogMessage($logID, $logMessage){
    $log = new Log();
    $log->list_log_id = $logID;
    $log->message = $logMessage;
    $log->save();

    return true;
}

function noteToCircle($note) {
    $noteMax = config('param.noteMax');
    $radiusCircle = config('param.radiusCircleNote');

    $dashArray = 2 * pi() * $radiusCircle;
//    565.48

    return $dashArray * (1 - $note / $noteMax);

}

//@func: postConfirm:  Changes state of several entities in BD. Sends notifications and returns.
//@param: null
//@returns: Object()
public function postConfirm(){
    $id = Input::get('service_id');
    $servicio = Service::find($id);
    $driver_id = Input::get('driver_id');
    if( $servicio != NULL){
        if($servicio->status_id == '6'){
            return returnResponseWithCode('2')
        }
        if($servicio->driver_id == NULL && $servicio->status_id == '1'){
            Driver::update($driver_id, array(
                'available' => '0'
            ));
            $driverTmp = Driver::find($driver_id);
            Service::update($id, array(
                'driver_id' => $driver_id
                'status_id' => '2',
                'car_id' => $driverTmp->car_id
            ));
            //Notificar a usuario!!
            $pushMessage = 'Tu servicio ha sido confirmado!';
            $servicio = Service :: find($id);
            $push = Push::make();
            if($servicio->user->uuid == ''){
                return returnResponseWithCode('0')
            }
            return notifyUser($servicio, $push ,$pushMessage)
        } else {
            return returnResponseWithCode('1')
        }
    } else {
        return returnResponseWithCode('3')
    }
}
//@func: notifiyUser: Handles users push notification service.
//@param: servicio Object
//@param: push Object
//@param: pushMessage String
//@returns: Object()
private function notifyUser($servicio, $push, $pushMessage){
    if ($servicio->user->type == '1'){//iPhone
        $result = $push->ios($servicio->user->uuid, $pushMessage, 1, 'honk.wav', 'Open', array('serviceId' => $servicio->id));
    } else {
        $result = $push->android2($servicio->user->uuid, $pushMessage, 1, 'default', 'Open', array('serviceId' =>servicio->id));
    }
    return returnResponseWithCode('0')
}

//@func: returnResponseWithCode: Returns an error given a code
//@param: code String
//@returns: Object()
private function returnResponseWithCode($code){
    return Response::json(array('error'=>$code));
}
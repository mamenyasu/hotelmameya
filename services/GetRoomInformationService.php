<?php

require_once __DIR__.'/../models/RoomModel.php';

class GetRoomInformationService{
//!!--プロパティ--
    private $roomModel;
//!!--コンストラクタ--    
    public function __construct($pdo){
        $this->roomModel=new RoomModel($pdo);
    }

////部屋情報を取得する操作。
    public function getRoomInformation($room_id){
        try{
            $room_information=$this->roomModel->getRoomInformation($room_id);
            return $room_information;
        }catch(Exception $e){
            throw $e;
        }
    }


}
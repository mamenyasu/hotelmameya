<?php

require_once __DIR__.'/../models/ReservationsModel.php';
require_once __DIR__.'/../models/RoomAvailabilityModel.php';

class MasterService{
    private $reservationsModel;
    private $RoomAvailabilityModel;
    
    public function __construct($pdo){
        $this->reservationsModel=new ReservationsModel($pdo);
        $this->RoomAvailabilityModel=new RoomAvailabilityModel($pdo);
    }

    //指定した種類の部屋の、その月の予約状況一覧を返す操作。
    public function getReservationRoomMonthAll($room_id,$year,$month){
        try{
            $reservationRoomMonthAll=$this->reservationsModel->getReservationRoomMonthAll($room_id,$year,$month);
            if(!$reservationRoomMonthAll){
            return ['success'=>false,'Message'=>'該当する予約がありません。'];
            }
            return ['success'=>true,'reservationMonthAll'=>$reservationRoomMonthAll];
        }catch(Exception $e){
            throw $e;
        }
    }


}
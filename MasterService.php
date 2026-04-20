<?php
class MasterService{
    private $reservationsModel;
    private $RoomAvailabilityModel;
    
    public function __construct($pdo){
        $this->reservationsModel=new ReservationsModel($pdo);
        $this->RoomAvailabilityModel=new RoomAvailabilityModel($pdo);
    }

    //その月の予約状況一覧を返す操作。
    public function getReservationMonthAll($year,$month){
    $reservationMonthAll=$this->reservationsModel->getReservationMonthAll($year,$month);
    if(!$reservationMonthAll){
        return ['success'=>false,'Message'=>'該当する予約がありません。'];
    }
    return ['success'=>true,'reservationMonthAll'=>$reservationMonthAll];
    }


}
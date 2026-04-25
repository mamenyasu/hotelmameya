<?php

require_once __DIR__.'/../models/RoomAvailabilityModel.php';

class FinalPriceService{
    private $pdo;
    private $roomAvailabilityModel;

    public function __construct($pdo){
        $this->pdo=$pdo;
        $this->roomAvailabilityModel=new RoomAvailabilityModel($pdo);
    }

    public function getFinalPrice($request){
        try{
            $total_price=0;
            $rows=$this->roomAvailabilityModel->getRoomBetweenPriceData($request);
            foreach($rows as $row){
            $total_price=$total_price+intval($row['price']);
            }
            return ['success'=>true, 'total_price'=>$total_price];
  
        }catch(Exception $e){
            throw $e;
        }

    }
}
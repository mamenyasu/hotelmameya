<?php

require_once __DIR__.'/../models/RoomAvailabilityModel.php';

class MaxCheckoutService{
    private $pdo;
    private $roomAvailabilityModel;
    
    public function __construct($pdo){
        $this->pdo=$pdo;
        $this->roomAvailabilityModel=new RoomAvailabilityModel($pdo);
    }

    //部屋在庫の最後尾の年と月を連想配列で返す操作。
    public function getMaxCheckout(){
        try{
            $maxDate=$this->roomAvailabilityModel->getMaxCheckout();
            $maxYear = date('Y', strtotime($maxDate));
            $maxMonth = date('n', strtotime($maxDate));
            return ['maxYear' => $maxYear, 'maxMonth' => $maxMonth];            
        }catch(Exception $e){
            throw $e;
        }
    }


}
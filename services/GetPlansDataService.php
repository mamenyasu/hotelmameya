<?php

require_once __DIR__.'/../models/PlanModel.php';

class GetPlansDataService{
//!!--プロパティ--
    private $planModel;
//!!--コンストラクタ--    
    public function __construct($pdo){
        $this->planModel=new PlanModel($pdo);
    }

////プランデータを連想配列で取得する操作。
    public function getPlansData($room_id){
        try{
            $plansData=$this->planModel->getPlansData($room_id);
            return $plansData;
        }catch(Exception $e){
            throw $e;
        }
    }


}
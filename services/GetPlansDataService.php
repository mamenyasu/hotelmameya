<?php

require_once __DIR__.'/../models/PlanModel.php';

class GetPlansDataService{
//!!--プロパティ--
    private $planModel;
//!!--コンストラクタ--    
    public function __construct($pdo){
        $this->planModel=new PlanModel($pdo);
    }

////プランデータを配列で取得する操作。
// [
    //  0 => [ ['plan_name']=>..., ['plan_title']=>... ,]
    //  1 => [ ['plan_name']=>..., ['plan_title']=>... ,]
    // ]みたいな感じ。
    public function getPlansData(){
        try{
            $plansData=$this->planModel->getPlansData();
            return $plansData;
        }catch(Exception $e){
            throw $e;
        }
    }

//プラン名からプランタイトルを取得する操作。
public function getPlanTitle($plan_name){
    try{
            $plansTitle=$this->planModel->getPlanTitle($plan_name);
            return $plansTitle;
        }catch(Exception $e){
            throw $e;
        }
    }
}


<?php

require_once __DIR__.'/../models/PricesCalendarModel.php';
require_once __DIR__.'/../models/PlanModel.php';
require_once __DIR__.'/../services/RoomMOnthPriceService.php';

class PricesCalendarService{
//--プロパティ--
    private $pricesCalendarModel;
    private $planModel;
    private $roomMonthPriceService;
//コンストラクタ--
    public function __construct($pdo){
     $this->pricesCalendarModel = new PricesCalendarModel($pdo);
     $this->planModel = new PlanModel($pdo);
     $this->roomMonthPriceService = new RoomMonthPriceService();

    }
//指定された部屋、指定された月という条件で、プランごとの料金表を返す。結果($prices)はプランごとの多重配列。
    public function getPricesAllPlan($room_id,$year,$month){
        try{
            $prices=[];

            //プランを配列で取得。
            $plans=$this->planModel->getPlan();

            foreach($plans as $plan){
            $pricesRecord=$this->pricesCalendarModel->getPricesRecord($room_id,$plan,$year,$month);
            $pricesArray=$this->roomMonthPriceService->getRoomMonthPrice($pricesRecord);  // [1=>3000,2=>3500 ...]みたいな感じに変換されて返ってくる。
            $prices[$plan]=$pricesArray; // ['bed_only'=>[1=>3000, 2=>3500 ...], 'standard'=>[1=>4000, 2=>4500 ...] ... ]みたいな感じになる。
            }

            return $prices;

        }catch(Exception $e){
            throw $e;
        }
    }
}
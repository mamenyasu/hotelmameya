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

            //プラン名を配列で取得。
            $plans=$this->planModel->getPlanName();

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

    //最終料金計算
    public function getFinalPrice($request){
        try{
            $checkin_date=$request['checkin_date'];
            $checkout_date=$request['checkout_date'];
            $plan=$request['plan'];
            $person=intval($request['person']);
            $room_id=$request['room_id'];
            $prices=$this->pricesCalendarModel->getPricesBetween($room_id,$plan,$checkin_date,$checkout_date);
            $totalPrice=0;
            foreach($prices as $price){
                $totalPrice += $price*$person;
            }
            return $totalPrice;
        }catch(Exception $e){
            throw $e;
        }

    }
}
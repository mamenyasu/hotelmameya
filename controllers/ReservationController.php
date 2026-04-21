<?php

require_once __DIR__.'/../services/FormValidationService.php';
require_once __DIR__.'/../services/ReservationService.php';
require_once __DIR__.'/../services/CalendarMarkArrayService.php';

class ReservationController{

    private $pdo;
    public function __construct($pdo){
        $this->pdo=$pdo;
    }


    ////初期ページ表示メソッド。
    //予約カレンダーページへ移動する場合に、初期設定として、room_id=1(シングル)、当日の年、月、日をデータとして与える。
    public function index(){
        $room_id=1;
        $year=date('Y');
        $month=date('m');
        include __DIR__.'/../views/index.php';
        exit();
    }


    ////予約カレンダービュー表示メソッド。
    public function date_list($room_id,$year,$month){

        //指定された種類の部屋の、指定月のデータを取得。各日それぞれの値段も、この配列に入っている。
        try{
        $reservationService=new ReservationService($this->pdo);
        $availabilityRoomMonth=$reservationService->getAvailabilityRoomMonth($room_id,$year,$month);
            if($availabilityRoomMonth['success']==false){
                $message=$availabilityRoomMonth['message'];
                include __DIR__.'/../views/false.php';
                exit();
            }

        //指定された種類の部屋の、指定月の各日の空き具合（〇△×）のデータ。
        $calendarMarkService=new CalendarMarkArrayService();
        $calendarMark=$calendarMarkService->getCalendarMarkArray($availabilityRoomMonth);

        //月初～月末（例：１～３１）のprice配列とmark配列をビューに与える。
        $price=[];
            foreach($availabilityRoomMonth as $row => $data){
            $day=(int)date('j',strtotime($row['stay_date']));   //ASC、DESC、両対応。
            $price[$day]=$data['price'];
            }
        $mark=$calendarMark;
        include __DIR__.'/../views/reservationCalendar.php';
        exit();

        //例外処理
        }catch(Exception $e){
        $message=$e->getMessage();
        include __DIR__.'/../views/false.php';
        exit();
        }
    }




}
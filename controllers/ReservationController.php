<?php

require_once __DIR__.'/../services/FormValidationService.php';
require_once __DIR__.'/../services/ReservationService.php';
require_once __DIR__.'/../services/CalendarMarkArrayService.php';
require_once __DIR__.'/../services/RoomMonthPriceService.php';

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
    public function reservationCalendar($room_id,$year,$month){
        try{
        //指定された種類の部屋の、指定月のデータを取得。各日それぞれの値段も、この配列に入っている。
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

        //月初～月末（例：１～３１）のprice配列とmark配列をビューに与えて表示。
        $roomMonthPriceService=new RoomMonthPriceService();
        $price=$roomMonthPriceService->getRoomMonthPrice($availabilityRoomMonth);
        $mark=$calendarMark;
        include __DIR__.'/../views/reservationCalendar.php';
        exit();

        //例外処理。
        }catch(Exception $e){
        $message=$e->getMessage();
        include __DIR__.'/../views/false.php';
        exit();
        }
    }

    ////予約フォームビュー表示メソッド。
    public function reserve_form($room_id,$year,$month,$day){
        try{
        //カレンダーで選択した日が、最低でも当日一泊出来るか再確認。
        $reservationService=new ReservationService($this->pdo);
        $hasStockOne=$reservationService->hasStockOne($room_id,$year,$month,$day);
        if($hasStockOne['success']==false){
            $message=$hasStockOne['message'];
            include __DIR__.'/../views/false.php';
        }

        //予約フォームを表示。
        $room_id=$room_id;
        $user_name="";
        $user_telphone="";
        $checkin_date=sprintf('%04d-%02d-%02d',$year,$month,$day);
        $checkout_date="";
        $total_price="";
        include __DIR__.'/../views/reserveForm.php';
        exit();

        //例外処理。
        }catch(Exception $e){
        $messeage=$e->getMessage();
        include __DIR__.'/../views/false.php';
        exit();
        }
    }

    ////予約内容確認ビュー表示メソッド。
    public function reserve_reconfirm($request){
        //バリデーション。
        $formvalidation=new FormValidationService();
        $error=$formvalidation->formValidate($request);
        if($error){
            $error=$error;
            $room_id=$request['room_id'];
            $user_name=$request['user_name'];
            $user_telphone=['user_telphone'];
            $checkin_date=$request['checkin_date'];
            $checkout_date=$request['checkout_date'];
            $total_price=$request['total_price'];
            include __DIR__.'/../views/reserveForm.php';
        }
        $room_id=$request['room_id'];
        $user_name=$request['user_name'];
        $uset_telphone=$request['user_telphone'];
        $checkin_date=$request['checkin_date'];
        $checkout_date=$request['checkout_date'];
        $total_price=$request['total_price'];
        include __DIR__.'/../views/reserveReconfirm.php';
    }

}
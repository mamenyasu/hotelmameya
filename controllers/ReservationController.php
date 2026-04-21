<?php

require_once __DIR__.'/../services/FormValidationService.php';
require_once __DIR__.'/../services/ReservationService.php';
require_once __DIR__.'/../services/CalendarMarkArrayService.php';
require_once __DIR__.'/../requests/RoomMonthPriceService.php';

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
        $checkin_date=htmlspecialchars(sprintf('%04d-%02d-%02d',$year,$month,$day));
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

    ////予約内容最終確認用ビュー表示メソッド。
    public function reserve_reconfirm($request){
        //バリデーション。
        $formrequest=new FormRequest();
        $error=$formrequest->formValidate($request);
        if($error){
            $error=$error;
            $room_id=htmlspecialchars($request['room_id']);
            $user_name=htmlspecialchars($request['user_name']);
            $user_telphone=htmlspecialchars($request['user_telphone']);
            $user_address=htmlspecialchars($request['user_address']);
            $email=htmlspecialchars($request['email']);
            $checkin_date=htmlspecialchars($request['checkin_date']);
            $checkout_date=htmlspecialchars($request['checkout_date']);
            $total_price=htmlspecialchars($request['total_price']);
            include __DIR__.'/../views/reserveForm.php';
            exit();
        }
        //セッション変数を使って、ビューをまたいで保持可能にする。
        $_SESSION['reserve']=[
            'room_id' => $request['room_id'],
            'user_name' => $request['user_name'],
            'user_telphone' => mb_convert_kana($request['user_telphone'], 'n', 'UTF-8'), //電話番号、全角なら半角へ。
            'user_address' => $request['user_address'],
            'email' => $request['email'],
            'checkin_date' => $request['checkin_date'],
            'checkout_date' =>$request['checkout_date'],
            'total_price' =>$request['total_price']
        ];
        //ビュー表示用。
        $room_id=htmlspecialchars($request['room_id']);
        $user_name=htmlspecialchars($request['user_name']);
        $user_telphone=htmlspecialchars(mb_convert_kana($request['user_telphone'], 'n', 'UTF-8'));
        $user_address=htmlspecialchars($request['user_address']);
        $email=htmlspecialchars($request['email']);
        $checkin_date=htmlspecialchars($request['checkin_date']);
        $checkout_date=htmlspecialchars($request['checkout_date']);
        $total_price=htmlspecialchars($request['total_price']);
        include __DIR__.'/../views/reserveReconfirm.php';
        exit();
    }

    ////予約確定メソッド。
    public function reserve_confirm(){
        try{
        $reservationService=new ReservationService($this->pdo);
        //最終的に、セッション変数を使って予約テーブルと在庫テーブルの２つに保存。
        $result=$reservationService->reserve($_SESSION['reserve']);
        if($result['success']==false){
            unset($_SESSION['reserve']);
            $message=$result['message'];
            include __DIR__.'/../views/false.php';
            exit();
        }
        unset($_SESSION['reserve']);
        include __DIR__.'/../views/reserveSuccess.php';
        exit();
        //例外処理。
        }catch(Exception $e){
        unset($_SESSION['reserve']);
        $message=$e->getMessage();
        include __DIR__.'/../views/false.php';
        }
    }

}
<?php

require_once __DIR__.'/../requests/FromRequest.php';
require_once __DIR__.'/../requests/CancelFormRequest.php';
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



//--新規予約--

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
        $calendarMark=$calendarMarkService->getCalendarMarkArray($availabilityRoomMonth['availabilityRoomMonth']);

        //月初～月末（例：１～３１）のprice配列とmark配列をビューに与えて表示。
        $roomMonthPriceService=new RoomMonthPriceService();
        $price=$roomMonthPriceService->getRoomMonthPrice($availabilityRoomMonth['availabilityRoomMonth']);
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


    ////予約フォームビュー表示。
    public function reserve_form($room_id,$year,$month,$day){
        try{
        //カレンダーで選択した日が、最低でも当日一泊出来るか再確認。
        $reservationService=new ReservationService($this->pdo);
        $hasStockOne=$reservationService->hasStockOne($room_id,$year,$month,$day);
        if($hasStockOne['success']==false){
            $message=$hasStockOne['message'];
            include __DIR__.'/../views/false.php';
            exit();
        }

        //予約フォームを表示。カレンダーで選択した月日がチェックイン日となる。
        $message="";
        $errors="";
        $room_id=$room_id;
        $user_name="";
        $user_telphone="";
        $user_address="";
        $email="";
        $checkin_date=htmlspecialchars(sprintf('%04d-%02d-%02d',$year,$month,$day));
        $checkout_date="";
        $total_price="";
        include __DIR__.'/../views/reserveForm.php';
        exit();

        //例外処理。
        }catch(Exception $e){
        $message=$e->getMessage();
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
            $message="";
            $errors=$error;
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

        //選択した期間で、部屋が確保できるか確認。確保できなければ、入力内容を持って差し戻し。
        try{
            $reservationSerivice=new ReservationService($this->pdo);
            $hasStockBetween=$reservationService->hasStockBetween($request);
            if($hasStockBetween['success']==false){
                $message=$hasStockBetween['message'];
                $errors="";
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

            //最終確認ビュー表示用。
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
        
        //例外処理
        }catch(Exception $e){
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();
        }
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
        exit();
        }
    }



//--予約キャンセル--    

    ////キャンセルフォーム表示。予約IDとメールアドレスを入力してもらう予定。
    public function reserve_cancel_form(){
        $errors="";
        $id="";
        $email="";
        include __DIR__.'/../views/reserveCancelForm.php';
    }


    ////キャンセルリクエスト照会メソッド。成功だとキャンセル最終確認ビューへ。
    public function reserve_cancel_reconfirm($request){
        //予約IDとメールアドレスをバリデーション。
        $cancelFormRequest=new CancelFormRequest();
        $error=$cancelFormRequest->cancelFormValidate($request);
        if($error){
            $errors=$error;
            $id=htmlspecialchars($request['id']);
            $email=htmlspecialchars($request['email']);
            include __DIR__.'/../views/reserveCancelForm.php';
            exit();
        }

        //入力バリデーション後、予約IDが全角だった場合、照会前に半角へ変換。
        $request['id']=mb_convert_kana($request['id'],'n','utf-8');

        //既予約が存在するか、また入力内容と一致するか照合。
        try{
            $reservationService=new ReservationService($this->pdo);
            $result=$reservationService->showReservation($request);
            if($result['success']==false){
                $message=$result['message'];
                include __DIR__.'/../views/false.php';
                exit();
            }

            //セッション変数を使って、ビューをまたいで保持可能にする。
            $_SESSION['reserve_cancel']=[
                'id' => $result['reservation']['id'],
                'room_id' => $result['reservation']['room_id'],
                'user_name' => $result['reservation']['user_name'],
                'user_telphone' => $result['reservation']['user_telphone'],
                'user_address' => $result['reservation']['user_address'],
                'email' => $result['reservation']['email'],
                'checkin_date' => $result['reservation']['checkin_date'],
                'checkout_date' =>$result['reservation']['checkout_date'],
                'total_price' =>$result['reservation']['total_price']
                ];

            //ビュー表示用。
            $id=htmlspecialchars($result['reservation']['id']);
            $room_id=htmlspecialchars($result['reservation']['room_id']);
            $user_name=htmlspecialchars($result['reservation']['user_name']);
            $user_telphone=htmlspecialchars($result['reservation']['user_telphone']);
            $user_address=htmlspecialchars($result['reservation']['user_address']);
            $email=htmlspecialchars($result['reservation']['email']);
            $checkin_date=htmlspecialchars($result['reservation']['checkin_date']);
            $checkout_date=htmlspecialchars($result['reservation']['checkout_date']);
            $total_price=htmlspecialchars($result['reservation']['total_price']);
            include __DIR__.'/../views/reserveCancelReconfirm.php';
            exit();
        }catch(Exception $e){
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();
        }
    }


    ////キャンセル確定メソッド。セッション変数[reserve_cancel]を使ってキャンセルする。
    public function reserve_cancel_confirm(){
        try{
            $reservationService=new ReservationService($this->pdo);
            $result=$reservationService->cancel($_SESSION['reserve_cancel']);
            $message=$result['message'];
            include __DIR__.'/../views/reserveCancelSuccess.php';
            exit();
        }catch(Exception $e){
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();
        }
    }



//--予約変更--

    ////予約変更フォーム表示。既予約の予約IDとメールアドレスを入力してもらう予定。
    public function reserve_updateVerify_form(){
        $message="";
        $errors="";
        $id="";
        $email="";
        include __DIR__.'/../views/reserveUpdateVerifyForm.php';
    }


    ////リクエスト照会し、存在しなければIDとメールアドレスを持って差し戻し。
    ////存在すれば、旧予約情報を保持しつつ、変更内容入力フォームを表示。
    ////部屋ごとのカレンダー用のデータもビューに渡す。在庫は、予約済みのものを一時的に戻して計算。
    public function reserve_update_form($request){
        //予約IDとメールアドレスをバリデーション。キャンセルバリデーションを再利用。
        $cancelFormRequest=new CancelFormRequest();
        $error=$cancelFormRequest->cancelFormValidate($request);
        if($error){
            $errors=$error;
            $id=htmlspecialchars($request['id']);
            $email=htmlspecialchars($request['email']);
            include __DIR__.'/../views/reserveUpdateVerifyForm.php';
            exit();
        }

        //入力バリデーション後、予約IDが全角だった場合、照会前に半角へ変換。
        $request['id']=mb_convert_kana($request['id'],'n','utf-8');

         //既予約が存在するか、また入力内容と一致するか照合。一致しなければメッセージを持って差し戻し。
        try{
            $reservationService=new ReservationService($this->pdo);
            $oldresult=$reservationService->showReservation($request);
            if($oldresult['success']==false){
                $message=$oldresult['message'];
                include __DIR__.'/../views/reserveUpdateVerifyForm.php';
                exit();
            }

            //セッション変数を使って、旧予約内容をビューをまたいで保持可能にする。個人情報は不要。
            $_SESSION['reserve_update_old']=[
                'id' => $oldresult['reservation']['id'],
                'room_id' => $oldresult['reservation']['room_id'],
                'checkin_date' => $oldresult['reservation']['checkin_date'],
                'checkout_date' =>$oldresult['reservation']['checkout_date'],
                'total_price' =>$oldresult['reservation']['total_price']
                ];

            //初期表示用にカレンダーのデータをつくる。初期設定は既予約のものを使う。
            $oldroom_id=$_SESSION['reserve_update_old']['room_id'];
            $oldcheckin_date=$_SESSION['reserve_update_old']['checkin_date'];
            $oldyear=date('Y',strtotime($oldcheckin_date));
            $oldmonth=date('m',strtotime($oldcheckin_date));
            $availabilityRoomMonth=$reservationService->getAvailabilityRoomMonth($oldroom_id,$oldyear,$oldmonth);
            if($availabilityRoomMonth['success']==false){
                $message=$availabilityRoomMonth['message'];
                include __DIR__.'/../views/false.php';
                exit();
            }

            //カレンダーの在庫を一時的に戻す。
            


            

            //ビュー表示用。
            $calendarData=$availabilityRoomMonth['availabilityRoomMonth'];
            $message="";
            $errors="";
            $old_id=htmlspecialchars($oldresult['reservation']['id']);
            $old_room_id=htmlspecialchars($oldresult['reservation']['room_id']);
            $old_checkin_date=htmlspecialchars($oldresult['reservation']['checkin_date']);
            $old_checkout_date=htmlspecialchars($oldresult['reservation']['checkout_date']);
            $old_total_price=htmlspecialchars($oldresult['reservation']['total_price']);
            include __DIR__.'/../views/reserveUpdateForm.php';
            exit();
        }catch(Exception $e){
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();
        }
    }

    
    ////変更内容最終確認ビューを表示。旧予約情報と新予約情報を表示。
    public function reserve_update_reconfirm(){

    }

}
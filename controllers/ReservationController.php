<?php

require_once __DIR__.'/../requests/FormRequest.php';
require_once __DIR__.'/../requests/CancelFormRequest.php';
require_once __DIR__.'/../services/ReservationService.php';
require_once __DIR__.'/../services/CalendarMarkArrayService.php';
require_once __DIR__.'/../services/RoomMonthPriceService.php';
require_once __DIR__.'/../services/RestockService.php';


class ReservationController{
//!!--プロパティ--
    private $pdo;
    private $formrequest;
    private $cancelFormRequest;
    private $updateFormRequest;
    private $reservationService;
    private $calendarMarkArrayService;
    private $roomMonthPriceService;
    private $restockService;
//!!--コンストラクタ--
    public function __construct($pdo){
        $this->pdo=$pdo;
        $this->formrequest = new FormRequest();
        $this->cancelFormRequest = new CancelFormRequest();
        $this->updateFormRequest = new UpdateFormRequest();
        $this->reservationService = new ReservationService($pdo);
        $this->calendarMarkArrayService = new CalendarMarkArrayService();
        $this->roomMonthPriceService = new RoomMonthPriceService();
        $this->restockService = new RestockService();
    }


//--初期ページ表示メソッド--
    public function index(){
        include __DIR__.'/../views/index.php';
        exit();
    }


//--新規予約--

    ////予約カレンダービュー表示メソッド。初期表示ではroom=1(シングル)、当日。
        //ただし、--新規予約--プロセスだけでなく、後述の--予約変更--プロセスでAJAXで呼ばれる場合もある。
    public function reservationCalendar($room_id=null, $year=null, $month=null){
        if($room_id===null){
            $room_id=1;
        }
        if($year===null){
            $year=date('Y');
        }
        if($month===null){
            $month=date('m');
        }

        try{
        //指定された種類の部屋の、指定月のデータを取得。各日それぞれの値段も、この配列に入っている。
        $availabilityRoomMonth=$this->reservationService->getAvailabilityRoomMonth($room_id,$year,$month);
            if($availabilityRoomMonth['success']==false){
                $message=$availabilityRoomMonth['message'];
                include __DIR__.'/../views/false.php';
                exit();
            }

        //指定された種類の部屋の、指定月の各日の空き具合（〇△×）のデータ。
        $calendarMark=$this->calendarMarkArrayService->getCalendarMarkArray($availabilityRoomMonth['availabilityRoomMonth']);

        //月初～月末（例：１～３１）のprice配列とmark配列をビューに与えて表示。
        $price=$this->roomMonthPriceService->getRoomMonthPrice($availabilityRoomMonth['availabilityRoomMonth']);
        $mark=$calendarMark;
        //後述の--予約変更--プロセスの中でAJAXとして呼ぶ場合は、マウスで各日をクリック選択できないカレンダーを表示。
        if(isset($_SESSION['reserve_update_old'])){
            include __DIR__.'/../views/reserveUpdateCalendar.php';
        }else{
        include __DIR__.'/../views/reservationCalendar.php';
        }
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
        $hasStockOne=$this->reservationService->hasStockOne($room_id,$year,$month,$day);
        if($hasStockOne['success']==false){
            $message=$hasStockOne['message'];
            include __DIR__.'/../views/false.php';
            exit();
        }

        //予約フォームを表示。カレンダーで選択した月日がチェックイン日となる。
        $checkin_date=htmlspecialchars(sprintf('%04d-%02d-%02d',$year,$month,$day));
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
        //バリデーション。通らなかったら差し戻し。
        $error=$this->formrequest->formValidate($request);
        if($error){
            $room_id=htmlspecialchars($request['room_id']);
            $user_name=htmlspecialchars($request['user_name']);
            $user_telphone=htmlspecialchars($request['user_telphone']);
            $user_address=htmlspecialchars($request['user_address']);
            $email=htmlspecialchars($request['email']);
            $comment=htmlspecialchars($request['comment']);
            $checkin_date=htmlspecialchars($request['checkin_date']);
            $checkout_date=htmlspecialchars($request['checkout_date']);
            $total_price=htmlspecialchars($request['total_price']);
            include __DIR__.'/../views/reserveForm.php';
            exit();
        }

        //選択した期間で、部屋が確保できるか確認。確保できなければ、入力内容を持って差し戻し。
        try{
            $hasStockBetween=$this->reservationService->hasStockBetween($request);
            if($hasStockBetween['success']==false){
                $message=$hasStockBetween['message'];
                $room_id=htmlspecialchars($request['room_id']);
                $user_name=htmlspecialchars($request['user_name']);
                $user_telphone=htmlspecialchars($request['user_telphone']);
                $user_address=htmlspecialchars($request['user_address']);
                $email=htmlspecialchars($request['email']);
                $comment=htmlspecialchars($request['comment']);
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
                'comment' => $request['comment'],
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
            $comment=htmlspecialchars($request['comment']);
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
            if(!$_SESSION['reserve']){
            echo "不正なリクエストです。";
            exit();
        }

        try{
        //最終的に、セッション変数を使って予約テーブルと在庫テーブルの２つに保存。
        $result=$this->reservationService->reserve($_SESSION['reserve']);
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
        include __DIR__.'/../views/reserveCancelForm.php';
    }


    ////キャンセルリクエスト照会メソッド。成功だとキャンセル最終確認ビューへ。
    public function reserve_cancel_reconfirm($request){
        //予約IDとメールアドレスをバリデーション。通らなかったら差し戻し。
            $error=$this->cancelFormRequest->cancelFormValidate($request);
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
            $result=$this->reservationService->showReservation($request);
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
                'comment' => $result['reservation']['comment'],
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
            $comment=htmlspecialchars($result['reservation']['comment']);
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
        if(!$_SESSION['reserve_cancel']){
            echo "不正なリクエストです。";
            exit();
        }

        try{
            $result=$this->reservationService->cancel($_SESSION['reserve_cancel']);
            $message=$result['message'];
            unset($_SESSION['reserva_cancel']);
            include __DIR__.'/../views/reserveCancelSuccess.php';
            exit();
        }catch(Exception $e){
            $message=$e->getMessage();
            unset($_SESSION['reserva_cancel']);
            include __DIR__.'/../views/false.php';
            exit();
        }
    }



//--予約変更--

    ////予約変更フォーム表示。既予約の予約IDとメールアドレスを入力してもらう予定。
    public function reserve_updateVerify_form(){
        include __DIR__.'/../views/reserveUpdateVerifyForm.php';
    }


    ////リクエスト照会し、存在しなければIDとメールアドレスを持って差し戻し。
    ////存在すれば、旧予約情報を保持しつつ、変更内容入力フォームを表示。
    ////部屋ごとのカレンダー用のデータもビューに渡す。在庫は、予約済みのものを一時的に戻して計算。
    public function reserve_update_form($request){

        //予約IDとメールアドレスをバリデーション。キャンセルバリデーションを再利用。
        $error=$this->cancelFormRequest->cancelFormValidate($request);
        if($error){
            $id=htmlspecialchars($request['id']);
            $email=htmlspecialchars($request['email']);
            include __DIR__.'/../views/reserveUpdateVerifyForm.php';
            exit();
        }

        //入力バリデーション後、予約IDが全角だった場合、照会前に半角へ変換。
        $request['id']=mb_convert_kana($request['id'],'n','utf-8');

         //既予約が存在するか、入力内容と一致するか照合。戻り値は結果と該当レコード。一致しなければメッセージを持って差し戻し。
        try{
            $oldresult=$this->reservationService->showReservation($request);
            if($oldresult['success']==false){
                $message=$oldresult['message'];
                include __DIR__.'/../views/reserveUpdateVerifyForm.php';
                exit();
            }

            //セッション変数を使って、旧予約内容(該当レコード)をビューをまたいで保持可能にする。個人情報は不要。
            $_SESSION['reserve_update_old']=[
                'id' => $oldresult['reservation']['id'],
                'room_id' => $oldresult['reservation']['room_id'],
                'comment' => $oldresult['resrvation']['comment'],
                'checkin_date' => $oldresult['reservation']['checkin_date'],
                'checkout_date' =>$oldresult['reservation']['checkout_date'],
                'total_price' =>$oldresult['reservation']['total_price']
                ];

            //初期表示用に一か月分の在庫データを取得。初期設定は既予約のものを使う。
            $oldroom_id=$_SESSION['reserve_update_old']['room_id'];
            $oldcheckin_date=$_SESSION['reserve_update_old']['checkin_date'];
            $oldyear=date('Y',strtotime($oldcheckin_date));
            $oldmonth=date('m',strtotime($oldcheckin_date));
            $availabilityRoomMonth=$this->reservationService->getAvailabilityRoomMonth($oldroom_id,$oldyear,$oldmonth);
            if($availabilityRoomMonth['success']==false){
                $message=$availabilityRoomMonth['message'];
                include __DIR__.'/../views/false.php';
                exit();
            }

            //在庫を一時的に戻す。
            $restocked_availabilityRoomMonth=$this->restockService->restock($availabilityRoomMonth);
            //１～月末日まで、〇△×に変換。在庫戻しと関係あるので、リストック後の配列を使用。
            $markArrayMonth=$this->calendarMarkArrayService->getCalendarMarkArray($restocked_availabilityRoomMonth);
            //値段データも取得。 １～月末日まで。価格は在庫戻しと関係ないので、修正前の配列を使う。
            $pricesMonth=$this->roomMonthPriceService->getRoomMonthPrice($availabilityRoomMonth['availabilityRoomMonth']);
            

            //ビュー表示用。
            $markArray=$markArrayMonth;
            $prices=$pricesMonth;
            $old_checkin_year=(int)date('Y',strtotime($oldresult['reservation']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の年。
            $old_checkin_month=(int)date('n',strtotime($oldresult['reservation']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の月。
            $old_id=htmlspecialchars($oldresult['reservation']['id']);
            $old_room_id=htmlspecialchars($oldresult['reservation']['room_id']);
            $old_comment=htmlspecialchars($oldresult['resrvation']['comment']);
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
    public function reserve_update_reconfirm($request){
    //バリデーション。通らなかったら差し戻し。
        $error=$this->updateFormRequest->updateFormValidate($request);
        if($error){
            $new_room_id=htmlspecialchars($request['room_id']);
            $new_comment=htmlspecialchars($request['comment']);
            $new_checkin_date=htmlspecialchars($request['checkin_date']);
            $new_checkout_date=htmlspecialchars($request['checkout_date']);
            $new_total_price=htmlspecialchars($request['total_price']);
            include __DIR__.'/../views/reserveUpdateForm.php';
            exit();
        }

    //変更後の予約の部屋と期間が本当に空いているか再度チェック。空いていなければ差し戻し。
        try{
            $result=$this->reservationService->hasStock($request);
            if(!$result){
                $message=$result['message'];
                $new_room_id=htmlspecialchars($request['room_id']);
                $new_comment=htmlspecialchars($request['comment']);
                $new_checkin_date=htmlspecialchars($request['checkin_date']);
                $new_checkout_date=htmlspecialchars($request['checkout_date']);
                $new_total_price=htmlspecialchars($request['total_price']);
                include __DIR__.'/../views/reserveUpdateForm.php';
            }

    //セッション変数を使って、旧予約内容をビューをまたいで保持可能にする。個人情報は不要。
            $_SESSION['reserve_update_new']=[
                'room_id' => $request['room_id'],
                'comment' => $request['comment'],
                'checkin_date' => $request['checkin_date'],
                'checkout_date' =>$request['checkout_date'],
                'total_price' =>$request['total_price']
                ];

    //ビュー表示用。
            $id=htmlspecialchars($_SESSION['reserve_update_old']['id']);
            $old_room_id=htmlspecialchars($_SESSION['reserve_update_old']['room_id']);
            $old_comment=htmlspecialchars($_SESSION['reserve_update_old']['comment']);
            $old_checkin_date=htmlspecialchars($_SESSION['reserve_update_old']['checkin_date']);
            $old_checkout_date=htmlspecialchars($_SESSION['reserve_update_old']['checkout_date']);
            $old_total_price=htmlspecialchars($_SESSION['reserve_update_old']['total_price']);
            $new_room_id=htmlspecialchars($_SESSION['reserve_update_new']['room_id']);
            $bew_comment=htmlspecialchars($_SESSION['reserve_update_new']['comment']);
            $new_checkin_date=htmlspecialchars($_SESSION['reserve_update_new']['checkin_date']);
            $new_checkout_date=htmlspecialchars($_SESSION['reserve_update_new']['checkout_date']);
            $new_total_price=htmlspecialchars($_SESSION['reserve_update_new']['total_price']);
            include __DIR__.'/../views/reserveUpdateConfirm.php';
            exit();
        }catch(Exception $e){
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();
        }    
    }


    ////変更内容のDBへの書き込み。
    public function reserve_update_confirm(){
        if(!$_SESSION['reserve_update_old'] || $_SESSION['reserve_update_new']){
            echo "不正なリクエストです。";
            exit();
        }

        $updateRequest['id']=$_SESSION['reserve_update_old']['id'];
        $updateRequest['room_id']=$_SESSION['reserve_update_new']['room_id'];
        $updateRequest['comment']=$_SESSION['reserve_update_new']['comment'];
        $updateRequest['checkin_date']=$_SESSION['reserve_update_new']['checkin_date'];
        $updateRequest['checkout_date']=$_SESSION['reserve_update_new']['checkout_date'];
        $updateRequest['total_price']=$_SESSION['reserve_update_new']['total_price'];

        try{
                $result=$this->reservationService->update($updateRequest);
                //最終チェック。通らなければ差し戻し。
                    if(!$result){
                    $message=$result['message'];
                    $id=$_SESSION['reserve_update_old']['id'];
                    $old_room_id=htmlspecialchars($_SESSION['reserve_update_old']['room_id']);
                    $old_comment=htmlspecialchars($_SESSION['reserve_update_old']['comment']);
                    $old_checkin_date=htmlspecialchars($_SESSION['reserve_update_old']['checkin_date']);
                    $old_checkout_date=htmlspecialchars($_SESSION['reserve_update_old']['checkout_date']);
                    $old_total_price=htmlspecialchars($_SESSION['reserve_update_old']['total_price']);
                    $new_room_id=htmlspecialchars($_SESSION['reserve_update_new']['room_id']);
                    $new_comment=htmlspecialchars($_SESSION['reserve_update_new']['comment']);
                    $new_checkin_date=htmlspecialchars($_SESSION['reserve_update_new']['checkin_date']);
                    $new_checkout_date=htmlspecialchars($_SESSION['reserve_update_new']['checkout_date']);
                    $new_total_price=htmlspecialchars($_SESSION['reserve_update_new']['total_price']);
                    include __DIR__.'/../views/reserveUpdateForm.php';
                    exit();
                    } 
                $message=$result['message'];
                include __DIR__.'/../views/updateSuccess.php';
                exit();
        }catch(Exception $e){
                $message=$e->getMessage();
                include __DIR__.'/../views/false.php';
                exit();    
        }
        
    }


}
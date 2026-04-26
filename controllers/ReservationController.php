<?php

require_once __DIR__.'/../requests/FormRequest.php';
require_once __DIR__.'/../requests/UpdateFormRequest';
require_once __DIR__.'/../requests/CancelFormRequest.php';
require_once __DIR__.'/../services/ReservationService.php';
require_once __DIR__.'/../services/CalendarMarkArrayService.php';
require_once __DIR__.'/../services/RoomMonthPriceService.php';
require_once __DIR__.'/../services/RestockService.php';
require_once __DIR__.'/../services/YearMonthToDaysService.php';
require_once __DIR__.'/../services/FinalPriceService.php';
require_once __DIR__.'/../services/PricesCalendarService.php';
require_once __DIR__.'/../services/GetPlansDataService.php';


class ReservationController{
//!!--プロパティ----------
    private $pdo;
    private $formrequest;
    private $cancelFormRequest;
    private $updateFormRequest;
    private $reservationService;
    private $calendarMarkArrayService;
    private $roomMonthPriceService;
    private $restockService;
    private $yearMonthToDaysService;
    private $finalPriceService;
    private $pricesCalendarService;
    private $getPlansDataService;
//!!--コンストラクタ---------
    public function __construct($pdo){
        $this->pdo=$pdo;
        $this->formrequest = new FormRequest();
        $this->cancelFormRequest = new CancelFormRequest();
        $this->updateFormRequest = new UpdateFormRequest();
        $this->reservationService = new ReservationService($pdo);
        $this->calendarMarkArrayService = new CalendarMarkArrayService();
        $this->roomMonthPriceService = new RoomMonthPriceService();
        $this->restockService = new RestockService();
        $this->yearMonthToDaysService = new YearMonthToDaysService();
        $this->finalPriceService = new FinalPriceService($pdo);
        $this->pricesCalendarService = new PricesCalendarService($pdo);
        $this->getPlansDataService = new GetPlansDataService($pdo);
    }

///ルータースイッチデフォルト用。----------
    public function index(){
        include __DIR__.'/../views/index.php';
    }


//--初期ページ表示メソッド----------------
    public function reserve_index(){
        include __DIR__.'/../views/reservationIndex.php';
        exit();
    }



//--新規予約------------------------------------------

    ////予約カレンダービュー表示メソッド。
     //初期表示ではroom=1(シングル)、当日。
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
        //指定された種類の部屋の、指定月のデータを取得。
            $availabilityRoomMonth=$this->reservationService->getAvailabilityRoomMonth($room_id,$year,$month);
            if($availabilityRoomMonth['success']==false){
                $message=$availabilityRoomMonth['message'];
                include __DIR__.'/../views/false.php';
                exit();
            }

        //mark配列生成。(指定された種類の部屋の、指定月の各日の空き具合（〇△×）)
            $mark=$this->calendarMarkArrayService->getCalendarMarkArray($availabilityRoomMonth['availabilityRoomMonth']);
        //指定された種類の部屋の、指定月の値段表を取得。$pricesはプランごとの多重配列。
            $prices=$this->pricesCalendarService->getPricesAllPlan($room_id,$year,$month);
        //月初～月末（例：１～３１）のdays配列。
            $days=$this->yearMonthToDaysService->getDays($year,$month);     
        //指定された部屋のプランデータを取得。見出しや内容など。
            $plansData=$this->getPlansDataService->getPlansData($room_id);  
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
    public function reserve_form($room_id,$year,$month,$day,$plan){
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
        //$planはhiddenで仕込む。
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
            $plan=htmlspecialchars($request['plan']);
            include __DIR__.'/../views/reserveForm.php';
            exit();
        }

        //選択した期間で、部屋が確保できるか確認。確保できなければ、入力内容を持って差し戻し。
        try{
            $hasStock=$this->reservationService->hasStock($request);
            if($hasStock['success']==false){
                $message=$hasStock['message'];
                $room_id=htmlspecialchars($request['room_id']);
                $user_name=htmlspecialchars($request['user_name']);
                $user_telphone=htmlspecialchars($request['user_telphone']);
                $user_address=htmlspecialchars($request['user_address']);
                $email=htmlspecialchars($request['email']);
                $comment=htmlspecialchars($request['comment']);
                $checkin_date=htmlspecialchars($request['checkin_date']);
                $checkout_date=htmlspecialchars($request['checkout_date']);
                $total_price=htmlspecialchars($request['total_price']);
                $plan=htmlspecialchars($request['plan']);
                include __DIR__.'/../views/reserveForm.php';
                exit();
            }
            //バックエンドで料金を再計算。
            $result_finalprice=$this->finalPriceService->getFinalPrice($request);
            $final_price=$result_finalprice['total_price'];

            //セッション変数に保持。
            $_SESSION['reserve']=[
                'room_id' => $request['room_id'],
                'user_name' => $request['user_name'],
                'user_telphone' => mb_convert_kana($request['user_telphone'], 'n', 'UTF-8'), //電話番号、全角なら半角へ。
                'user_address' => $request['user_address'],
                'email' => $request['email'],
                'comment' => $request['comment'],
                'checkin_date' => $request['checkin_date'],
                'checkout_date' =>$request['checkout_date'],
                'total_price' =>$final_price,
                'plan' =>$request['plan']
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
            $total_price=htmlspecialchars($final_price);
            $plan=htmlspecialchars($request['plan']);
            include __DIR__.'/../views/reserveReconfirm.php';
            exit();
        
        //例外処理
        }catch(Exception $e){
            if(isset($_SESSION['reserve'])){
                unset($_SESSION['reserve']);
            }
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();
        }
    }



    ////予約確定メソッド。
    public function reserve_confirm(){
            if(!isset($_SESSION['reserve'])){
            echo "不正なリクエストです。";
            exit();
        }

        try{
        //最終的に、セッション変数を使って予約テーブルと在庫テーブルの２つに保存。できなかったら差し戻し。
        $result=$this->reservationService->reserve($_SESSION['reserve']);
        if($result['success']==false){
            $message=htmlspecialchars($result['message']);
            $room_id=htmlspecialchars($_SESSION['reserve']['room_id']);
            $user_name=htmlspecialchars($_SESSION['reserve']['user_name']);
            $user_telphone=htmlspecialchars($_SESSION['reserve']['user_telphone']);
            $user_address=htmlspecialchars($_SESSION['reserve']['user_address']);
            $email=htmlspecialchars($_SESSION['reserve']['email']);
            $comment=htmlspecialchars($_SESSION['reserve']['comment']);
            $checkin_date=htmlspecialchars($_SESSION['reserve']['checkin_date']);
            $checkout_date=htmlspecialchars($_SESSION['reserve']['checkout_date']);
            $total_price=htmlspecialchars($_SESSION['reserve']['total_price']);
            $plan=htmlspecialchars($_SESSION['reserve']['plan']);
            unset($_SESSION['reserve']);
            include __DIR__.'/../views/reserveForm.php';
            exit();
        }
        $message=$result['message'];
        unset($_SESSION['reserve']);
        include __DIR__.'/../views/success.php';
        exit();
        //例外処理。
        }catch(Exception $e){
        unset($_SESSION['reserve']);
        $message=$e->getMessage();
        include __DIR__.'/../views/false.php';
        exit();
        }
    }




//--予約キャンセル-------------------------------------

    ////キャンセルフォーム表示。予約IDとメールアドレスを入力してもらう予定。
    public function reserve_cancel_form(){
        include __DIR__.'/../views/reserveCancelForm.php';
        exit();
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
            $plan=htmlspecialchars($result['reservation']['plan']);
            include __DIR__.'/../views/reserveCancelReconfirm.php';
            exit();
        }catch(Exception $e){
            if(isset($_SESSION['reserve_cancel'])){
                unset($_SESSION['reserve_cancel']);
            }
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();
        }
    }


    ////キャンセル確定メソッド。セッション変数[reserve_cancel]を使ってキャンセルする。
    public function reserve_cancel_confirm(){
        if(!isset($_SESSION['reserve_cancel'])){
            echo "不正なリクエストです。";
            exit();
        }

        try{
            $result=$this->reservationService->cancel($_SESSION['reserve_cancel']);
            $message=$result['message'];
            unset($_SESSION['reserve_cancel']);
            include __DIR__.'/../views/success.php';
            exit();
        }catch(Exception $e){
            $message=$e->getMessage();
            unset($_SESSION['reserve_cancel']);
            include __DIR__.'/../views/false.php';
            exit();
        }
    }



//--予約変更-------------------------------------------------

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
                'comment' => $oldresult['reservation']['comment'],
                'checkin_date' => $oldresult['reservation']['checkin_date'],
                'checkout_date' =>$oldresult['reservation']['checkout_date'],
                'total_price' =>$oldresult['reservation']['total_price'],
                'plan' =>$oldresult['reservation']['plan']
                ];

            //初期表示用に一か月分の在庫データを取得。初期設定は既予約のものを使う。
            $oldroom_id=$_SESSION['reserve_update_old']['room_id'];
            $oldcheckin_date=$_SESSION['reserve_update_old']['checkin_date'];
            $oldyear=date('Y',strtotime($oldcheckin_date));
            $oldmonth=date('m',strtotime($oldcheckin_date));
            $availabilityRoomMonth=$this->reservationService->getAvailabilityRoomMonth($oldroom_id,$oldyear,$oldmonth);
            if($availabilityRoomMonth['success']==false){
                unset($_SESSION['reserve_update_old']);
                $message=$availabilityRoomMonth['message'];
                include __DIR__.'/../views/false.php';
                exit();
            }

            //在庫を一時的に戻す。リストック後は'success'判定がなくなり、普通の配列に。
            $restocked_availabilityRoomMonth=$this->restockService->restock($availabilityRoomMonth);
            //１～月末日まで、〇△×に変換。在庫戻しと関係あるので、リストック後の配列を使用。
            $markArrayMonth=$this->calendarMarkArrayService->getCalendarMarkArray($restocked_availabilityRoomMonth);

            //値段データも取得。 １～月末日まで。価格は在庫戻しと関係ないので、修正前の配列を使う。
            $oldresult_year=date('Y',$oldresult['reservation']['checkin_date']);
            $oldresult_month=date('m',$oldresult['reservation']['checkin_date']);
            $pricesAllPlan=$this->pricesCalendarService->getPricesAllPlan($oldresult['reservation']['room_id'],$oldresult_year,$oldresult_month);
            $pricesMonth=$pricesAllPlan[$oldresult['reservation']['plan']];

            //旧予約の部屋種類をもとに、プラン一覧データを取得。
            $roomPlansdata=$getPlansDataService->getPlansDataService($oldresult['reservation']['room_id']);
            
            //差し戻し用に、リストック状態のカレンダー表示用データをセッション変数で保持。
            $_SESSION['reserve_update_calendar'] = [
                'markArray' => $markArrayMonth,
                'prices'    => $pricesMonth
                ];

            //ビュー表示用。
            $days=$this->yearMonthToDaysService->getDays($oldyear,$oldmonth);
            $markArray=$markArrayMonth;
            $prices=$pricesMonth;
            $plansdata=$roomPlansData;
            $old_checkin_year=(int)date('Y',strtotime($oldresult['reservation']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の年。
            $old_checkin_month=(int)date('n',strtotime($oldresult['reservation']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の月。
            $old_id=htmlspecialchars($oldresult['reservation']['id']);
            $old_room_id=htmlspecialchars($oldresult['reservation']['room_id']);
            $old_comment=htmlspecialchars($oldresult['reservation']['comment']);
            $old_checkin_date=htmlspecialchars($oldresult['reservation']['checkin_date']);
            $old_checkout_date=htmlspecialchars($oldresult['reservation']['checkout_date']);
            $old_total_price=htmlspecialchars($oldresult['reservation']['total_price']);
            $old_plan=htmlspecialchars($oldresult['reservation']['plan']);
            include __DIR__.'/../views/reserveUpdateForm.php';
            exit();
        }catch(Exception $e){
            if(isset($_SESSION['reserve_update_old'])){
                unset($_SESSION['reserve_update_old']);
            }
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();
        }
    }

    
    ////変更内容最終確認ビューを表示。旧予約情報と新予約情報を表示。
    public function reserve_update_reconfirm($request){
    if(!isset($_SESSION['reserve_update_old'])){
        echo '不正なリクエストです。';
        exit();
    }

    //バリデーション。通らなかったら差し戻し。
        $error=$this->updateFormRequest->updateFormValidate($request);
        if($error){
            $markArray=$_SESSION['reserve_update_calendar']['markArray'];
            $prices=$_SESSION['reserve_update_calendar']['prices'];
            $old_checkin_year=(int)date('Y',strtotime($_SESSION['reserve_update_old']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の年。
            $old_checkin_month=(int)date('n',strtotime($_SESSION['reserve_update_old']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の月。
            $old_id=htmlspecialchars($_SESSION['reserve_update_old']['id']);
            $old_room_id=htmlspecialchars($_SESSION['reserve_update_old']['room_id']);
            $old_comment=htmlspecialchars($_SESSION['reserve_update_old']['comment']);
            $old_checkin_date=htmlspecialchars($_SESSION['reserve_update_old']['checkin_date']);
            $old_checkout_date=htmlspecialchars($_SESSION['reserve_update_old']['checkout_date']);
            $old_total_price=htmlspecialchars($_SESSION['reserve_update_old']['total_price']);
            $old_plan=htmlspecialchars($_SESSION['reserve_update_old']['plan']);
            $new_room_id=htmlspecialchars($request['room_id']);
            $new_comment=htmlspecialchars($request['comment']);
            $new_checkin_date=htmlspecialchars($request['checkin_date']);
            $new_checkout_date=htmlspecialchars($request['checkout_date']);
            $new_total_price=htmlspecialchars($request['total_price']);
            $new_plan=htmlspecialchars($request['plan']);
            include __DIR__.'/../views/reserveUpdateForm.php';
            exit();
        }

    //変更後の予約の部屋と期間が本当に空いているか再度チェック。空いていなければ差し戻し。
        try{
            $result=$this->reservationService->hasStock($request);
            if($result['success']==false){
                $message=$result['message'];
                $markArray=$_SESSION['reserve_update_calendar']['markArray'];
                $prices=$_SESSION['reserve_update_calendar']['prices'];
                $old_checkin_year=(int)date('Y',strtotime($_SESSION['reserve_update_old']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の年。
                $old_checkin_month=(int)date('n',strtotime($_SESSION['reserve_update_old']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の月。
                $old_id=htmlspecialchars($_SESSION['reserve_update_old']['id']);
                $old_room_id=htmlspecialchars($_SESSION['reserve_update_old']['room_id']);
                $old_comment=htmlspecialchars($_SESSION['reserve_update_old']['comment']);
                $old_checkin_date=htmlspecialchars($_SESSION['reserve_update_old']['checkin_date']);
                $old_checkout_date=htmlspecialchars($_SESSION['reserve_update_old']['checkout_date']);
                $old_total_price=htmlspecialchars($_SESSION['reserve_update_old']['total_price']);
                $old_plan=htmlspecialchars($_SESSION['reserve_update_old']['plan']);
                $new_room_id=htmlspecialchars($request['room_id']);
                $new_comment=htmlspecialchars($request['comment']);
                $new_checkin_date=htmlspecialchars($request['checkin_date']);
                $new_checkout_date=htmlspecialchars($request['checkout_date']);
                $new_total_price=htmlspecialchars($request['total_price']);
                $new_plan=htmlspecialchars($request['plan']);
                include __DIR__.'/../views/reserveUpdateForm.php';
                exit();
            }

    //バックエンドで料金を再計算。
                $result_finalprice=$this->finalPriceService->getFinalPrice($request);
                $final_price=$result_finalprice['total_price'];

    //セッション変数を使って、新たな予約内容をビューをまたいで保持可能にする。個人情報は不要。
            $_SESSION['reserve_update_new']=[
                'id' => $_SESSION['reserve_update_old']['id'],
                'room_id' => $request['room_id'],
                'comment' => $request['comment'],
                'checkin_date' => $request['checkin_date'],
                'checkout_date' =>$request['checkout_date'],
                'total_price' =>$final_price,
                'plan' =>$request['plan']
                ];

    //ビュー表示用。
            $id=htmlspecialchars($_SESSION['reserve_update_old']['id']);
            $old_room_id=htmlspecialchars($_SESSION['reserve_update_old']['room_id']);
            $old_comment=htmlspecialchars($_SESSION['reserve_update_old']['comment']);
            $old_checkin_date=htmlspecialchars($_SESSION['reserve_update_old']['checkin_date']);
            $old_checkout_date=htmlspecialchars($_SESSION['reserve_update_old']['checkout_date']);
            $old_total_price=htmlspecialchars($_SESSION['reserve_update_old']['total_price']);
            $old_plan=htmlspecialchars($_SESSION['reserve_update_old']['plan']);
            $new_room_id=htmlspecialchars($_SESSION['reserve_update_new']['room_id']);
            $new_comment=htmlspecialchars($_SESSION['reserve_update_new']['comment']);
            $new_checkin_date=htmlspecialchars($_SESSION['reserve_update_new']['checkin_date']);
            $new_checkout_date=htmlspecialchars($_SESSION['reserve_update_new']['checkout_date']);
            $new_total_price=htmlspecialchars($_SESSION['reserve_update_new']['total_price']);
            $new_plan=htmlspecialchars($_SESSION['reserve_update_new']['plan']);
            include __DIR__.'/../views/reserveUpdateConfirm.php';
            exit();
        //例外処理。    
        }catch(Exception $e){
            if(isset($_SESSION['reserve_update_old'])){
            unset($_SESSION['reserve_update_old']);
            }
            if(isset($_SESSION['reserve_update_new'])){
            unset($_SESSION['reserve_update_new']);
            }
            if(isset($_SESSION['reserve_update_calendar'])){
            unset($_SESSION['reserve_update_calendar']);
            }
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();
        }    
    }


    ////変更内容のDBへの書き込み。
    public function reserve_update_confirm(){
        if(!isset($_SESSION['reserve_update_old']) || !isset($_SESSION['reserve_update_new'])){
            echo "不正なリクエストです。";
            exit();
        }

        try{
                $result=$this->reservationService->update($_SESSION['reserve_update_new']);
                //最終チェック。通らなければ差し戻し。
                if($result['success']==false){
                    $message=$result['message'];
                    $id=$_SESSION['reserve_update_old']['id'];
                    $old_room_id=htmlspecialchars($_SESSION['reserve_update_old']['room_id']);
                    $old_comment=htmlspecialchars($_SESSION['reserve_update_old']['comment']);
                    $old_checkin_date=htmlspecialchars($_SESSION['reserve_update_old']['checkin_date']);
                    $old_checkout_date=htmlspecialchars($_SESSION['reserve_update_old']['checkout_date']);
                    $old_total_price=htmlspecialchars($_SESSION['reserve_update_old']['total_price']);
                    $old_plan=htmlspecialchars($_SESSION['reserve_update_old']['plan']);
                    $new_room_id=htmlspecialchars($_SESSION['reserve_update_new']['room_id']);
                    $new_comment=htmlspecialchars($_SESSION['reserve_update_new']['comment']);
                    $new_checkin_date=htmlspecialchars($_SESSION['reserve_update_new']['checkin_date']);
                    $new_checkout_date=htmlspecialchars($_SESSION['reserve_update_new']['checkout_date']);
                    $new_total_price=htmlspecialchars($_SESSION['reserve_update_new']['total_price']);
                    $new_plan=htmlspecialchars($_SESSION['reserve_update_new']['plan']);
                    unset($_SESSION['reserve_update_new']);
                    include __DIR__.'/../views/reserveUpdateForm.php';
                    exit();
                } 
                //成功時。
                unset($_SESSION['reserve_update_old']);
                unset($_SESSION['reserve_update_new']);
                unset($_SESSION['reserve_update_calendar']);
                $message=$result['message'];
                include __DIR__.'/../views/success.php';
                exit();
        //例外処理。        
        }catch(Exception $e){
            unset($_SESSION['reserve_update_old']);
            unset($_SESSION['reserve_update_new']);
            unset($_SESSION['reserve_update_calendar']);
            $message=$e->getMessage();
            include __DIR__.'/../views/false.php';
            exit();    
        }
        
    }


}
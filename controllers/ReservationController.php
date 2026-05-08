<?php

require_once __DIR__ . '/../requests/FormRequest.php';
require_once __DIR__ . '/../requests/UpdateFormRequest.php';
require_once __DIR__ . '/../requests/CancelFormRequest.php';
require_once __DIR__ . '/../services/ReservationService.php';
require_once __DIR__ . '/../services/CalendarMarkArrayService.php';
require_once __DIR__ . '/../services/RoomMonthPriceService.php';
require_once __DIR__ . '/../services/RestockService.php';
require_once __DIR__ . '/../services/YearMonthToDaysService.php';
require_once __DIR__ . '/../services/PricesCalendarService.php';
require_once __DIR__ . '/../services/GetPlansDataService.php';
require_once __DIR__ . '/../services/MaxGuest_OfRoomService.php';
require_once __DIR__ . '/../services/GetRoomInformationService.php';
require_once __DIR__ . '/../services/WeekDayService.php';
require_once __DIR__ . '/../services/MaxCheckoutService.php';


class ReservationController
{
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
    private $pricesCalendarService;
    private $getPlansDataService;
    private $maxGuest_OfRoomService;
    private $getRoomInformationService;
    private $weekDayService;
    private $maxCheckoutService;

    //!!--コンストラクタ---------
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->formrequest = new FormRequest();
        $this->cancelFormRequest = new CancelFormRequest();
        $this->updateFormRequest = new UpdateFormRequest();
        $this->reservationService = new ReservationService($pdo);
        $this->calendarMarkArrayService = new CalendarMarkArrayService();
        $this->roomMonthPriceService = new RoomMonthPriceService();
        $this->restockService = new RestockService();
        $this->yearMonthToDaysService = new YearMonthToDaysService();
        $this->pricesCalendarService = new PricesCalendarService($pdo);
        $this->getPlansDataService = new GetPlansDataService($pdo);
        $this->maxGuest_OfRoomService = new MaxGuest_OfRoomService($pdo);
        $this->getRoomInformationService = new GetRoomInformationService($pdo);
        $this->weekDayService = new WeekDayService();
        $this->maxCheckoutService = new MaxCheckoutService($pdo);
    }


    //--初期ページ表示メソッド----------------
    public function reserve_index()
    {
        include __DIR__ . '/../views/reservationIndex.php';
        exit();
    }



    //--新規予約------------------------------------------

    ////予約カレンダービュー表示メソッド。
    //初期表示ではroom=1(シングル)、当日。
    public function reservationCalendar($room_id = null, $year = null, $month = null)
    {
        if ($room_id === null) {
            $room_id = 1;
        }
        if ($year === null) {
            $year = date('Y');
        }
        if ($month === null) {
            $month = date('m');
        }

        try {
            //指定された種類の部屋の、指定月のデータを取得。
            $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);
            if ($availabilityRoomMonth['success'] == false) {
                $message = $availabilityRoomMonth['message'];
                include __DIR__ . '/../views/false.php';
                exit();
            }
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($room_id);
            $room_name = $room_information['room_name'];
            //mark配列生成。(指定された種類の部屋の、指定月の各日の空き具合（〇△×）)
            $marks = $this->calendarMarkArrayService->getCalendarMarkArray($availabilityRoomMonth['availabilityRoomMonth']);
            //指定された種類の部屋の、指定月の値段表を取得。プランごとの多重配列。
            $pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($room_id, $year, $month);
            //月初～月末（例：１～３１）のdays配列。
            $days = $this->yearMonthToDaysService->getDays($year, $month);
            //指定された部屋のプランデータを取得。見出しや内容など。
            $plansData = $this->getPlansDataService->getPlansData();
            //初期表示用の、最初のプラン（0=１泊２食付きプラン）
            $selectedPlan = $_SESSION['reserve_form']['selectedPlan'] ?? $plansData[0]['plan_name'];
            // 初期表示用の価格配列
            $prices = $pricesAllPlan[$selectedPlan];
            //指定された部屋の人数制限。
            $maxGuest_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($room_id);
            //カレンダーで、指定月１日が何曜日から始まるか。0=日曜日～
            $start_weekDay = $this->weekDayService->getStartWeekDay_From_Ym($year, $month);
            //カレンダー生成用、在庫カレンダー最後尾の日付
            $maxDate = $this->maxCheckoutService->getMaxCheckout();
            $maxYear = $maxDate['maxYear'];
            $maxMonth = $maxDate['maxMonth'];
            include __DIR__ . '/../views/reservationCalendar.php';
            exit();

            //例外処理。
        } catch (Exception $e) {
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }


    ////予約フォームビュー表示。
    public function reserve_form($room_id, $year, $month, $day, $plan)
    {
        try {
            //ブラウザの戻るボタン対策。
            $_SESSION['reserve_form']['selectedPlan'] = $plan;

            //カレンダーで選択した日が、最低でも当日一泊出来るか再確認。
            $hasStockOne = $this->reservationService->hasStockOne($room_id, $year, $month, $day);
            if ($hasStockOne['success'] == false) {
                $message = $hasStockOne['message'];
                include __DIR__ . '/../views/false.php';
                exit();
            }


            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($room_id);
            $room_name = $room_information['room_name'];
            //プラン名をプランタイトルに変換する。
            $plan_title = $this->getPlansDataService->getPlanTitle($plan);
            //予約フォームを表示。カレンダーで選択した月日がチェックイン日となる。
            $checkin_date = htmlspecialchars(sprintf('%04d-%02d-%02d', $year, $month, $day));

            //$planはhiddenで仕込む!

            //部屋タイプにより制限人数。
            $number_OfRoom = $this->maxGuest_OfRoomService->getMaxguest_OfRoom($room_id);

            //宿泊日数のセレクトボックスの値を生成。
            $maxStayNights = $this->reservationService->makeNumStayNights($room_id, $year, $month, $day);


            //在庫カレンダー最後尾の日付
            $maxDate = $this->maxCheckoutService->getMaxCheckout();
            $maxYear = $maxDate['maxYear'];
            $maxMonth = $maxDate['maxMonth'];

            //戻るボタンで戻った時のセッション変数優先での、ビューへ与える変数。
            $stay_nights = intval($_SESSION['reserve_form']['stay_nights'] ?? 1);
            //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
            if ($stay_nights > count($maxStayNights)) {
                $stay_nights = count($maxStayNights);
            }
            $stay_nights = htmlspecialchars($stay_nights);
            $person = intval($_SESSION['reserve_form']['person'] ?? 1); //見積用と兼用
            $user_name = $_SESSION['reserve_form']['user_name'] ?? '';
            $user_telphone = $_SESSION['reserve_form']['user_telphone'] ?? '';
            $user_address = $_SESSION['reserve_form']['user_address'] ?? '';
            $email = $_SESSION['reserve_form']['email'] ?? '';
            $comment = $_SESSION['reserve_form']['comment'] ?? '';
            $checkout_date = date('Y-m-d', strtotime("{$checkin_date} +{$stay_nights} day"));

            //見積り計算
            $estimate = $this->pricesCalendarService->getEstimate(
                $room_id,
                $plan,
                $person,
                $checkin_date,
                $checkout_date
            );
            //二重送信防止トークン
            $token = bin2hex(random_bytes(32));
            $_SESSION['reserve_token'] = $token;

            include __DIR__ . '/../views/reserveForm.php';
            exit();

            //例外処理。
        } catch (Exception $e) {
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }


    ////予約内容最終確認用ビュー表示メソッド。
    public function reserve_reconfirm($request)
    {
        //二重送信防止トークンチェック
        if (
            empty($_POST['reserve_token']) ||
            empty($_SESSION['reserve_token']) ||
            $_POST['reserve_token'] !== $_SESSION['reserve_token']
        ) {
            unset($_SESSION['reserve_token']);
            $message="不正なリクエストです。3秒後にTOPページに戻ります。";
            include __DIR__.'/../views/false.php';
            exit;
        }

        //バリデーション。通らなかったら差し戻し。
        $error = $this->formrequest->formValidate($request);
        if ($error) {
            //指定された種類の部屋の、指定月のデータを取得。
            $estimate = $request['total_price'];
            $number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($request['room_id']);
            $room_id = htmlspecialchars($request['room_id'], ENT_QUOTES, 'UTF-8');
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($request['room_id']);
            $room_name = $room_information['room_name'];
            $user_name = htmlspecialchars($request['user_name'], ENT_QUOTES, 'UTF-8');
            $user_telphone = htmlspecialchars($request['user_telphone'], ENT_QUOTES, 'UTF-8');
            $user_address = htmlspecialchars($request['user_address'], ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8');
            $comment = htmlspecialchars($request['comment'], ENT_QUOTES, 'UTF-8');
            $checkin_date = htmlspecialchars($request['checkin_date'], ENT_QUOTES, 'UTF-8');
            $checkout_date = htmlspecialchars($request['checkout_date'], ENT_QUOTES, 'UTF-8');
            $total_price = htmlspecialchars($request['total_price'], ENT_QUOTES, 'UTF-8');
            $plan = htmlspecialchars($request['plan'], ENT_QUOTES, 'UTF-8');
            //プラン名をプランタイトルに変換する。
            $plan_title = $this->getPlansDataService->getPlanTitle($plan);
            $person = htmlspecialchars($request['person'], ENT_QUOTES, 'UTF-8');
            //宿泊日数のセレクトボックスの値を生成。
            $year = date('Y', strtotime($request['checkin_date']));
            $month = date('m', strtotime($request['checkin_date']));
            $day = date('d', strtotime($request['checkin_date']));
            $stay_nights = $request['stay_nights'];
            $maxStayNights = $this->reservationService->makeNumStayNights($room_id, $year, $month, $day);
            //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
            if ($stay_nights > count($maxStayNights)) {
                $stay_nights = count($maxStayNights);
            }
            $stay_nights = htmlspecialchars($stay_nights);
            $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);
            $marks = $this->calendarMarkArrayService->getCalendarMarkArray($availabilityRoomMonth['availabilityRoomMonth']);
            //指定された種類の部屋の、指定月の値段表を取得。プランごとの多重配列。
            $pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($room_id, $year, $month);
            //月初～月末（例：１～３１）のdays配列。
            $days = $this->yearMonthToDaysService->getDays($year, $month);
            //指定された部屋のプランデータを取得。見出しや内容など。
            $plansData = $this->getPlansDataService->getPlansData();
            //初期表示用の、最初のプラン（0=１泊２食付きプラン）
            $selectedPlan = $request['plan'];
            // 初期表示用の価格配列
            $prices = $pricesAllPlan[$selectedPlan];
            //指定された部屋の人数制限。
            $maxGuest_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($room_id);
            //カレンダーで、指定月１日が何曜日から始まるか。0=日曜日～
            $start_weekDay = $this->weekDayService->getStartWeekDay_From_Ym($year, $month);
            //カレンダー生成用、在庫カレンダー最後尾の日付
            $maxDate = $this->maxCheckoutService->getMaxCheckout();
            $maxYear = $maxDate['maxYear'];
            $maxMonth = $maxDate['maxMonth'];
            include __DIR__ . '/../views/reserveForm.php';
            exit();
        }

        //選択した期間で、部屋が確保できるか確認。確保できなければ、入力内容を持って差し戻し。
        try {
            $hasStock = $this->reservationService->hasStock($request);
            if ($hasStock['success'] == false) {
                $estimate = $request['total_price'];
                $message = $hasStock['message'];
                $number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($request['room_id']);
                $room_id = htmlspecialchars($request['room_id'], ENT_QUOTES, 'UTF-8');
                //room_idを部屋の名前（日本語）に変換する。
                $room_information = $this->getRoomInformationService->getRoomInformation($request['room_id']);
                $room_name = $room_information['room_name'];
                $user_name = htmlspecialchars($request['user_name'], ENT_QUOTES, 'UTF-8');
                $user_telphone = htmlspecialchars($request['user_telphone'], ENT_QUOTES, 'UTF-8');
                $user_address = htmlspecialchars($request['user_address'], ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8');
                $comment = htmlspecialchars($request['comment'], ENT_QUOTES, 'UTF-8');
                $checkin_date = htmlspecialchars($request['checkin_date'], ENT_QUOTES, 'UTF-8');
                $checkout_date = htmlspecialchars($request['checkout_date'], ENT_QUOTES, 'UTF-8');
                $total_price = htmlspecialchars($request['total_price'], ENT_QUOTES, 'UTF-8');
                $plan = htmlspecialchars($request['plan'], ENT_QUOTES, 'UTF-8');
                //プラン名をプランタイトルに変換する。
                $plan_title = $this->getPlansDataService->getPlanTitle($plan);
                $person = htmlspecialchars($request['person'], ENT_QUOTES, 'UTF-8');
                //宿泊日数のセレクトボックスの値を生成。
                $year = date('Y', strtotime($request['checkin_date']));
                $month = date('m', strtotime($request['checkin_date']));
                $day = date('d', strtotime($request['checkin_date']));
                $stay_nights = $request['stay_nights'];
                $maxStayNights = $this->reservationService->makeNumStayNights($room_id, $year, $month, $day);
                //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
                if ($stay_nights > count($maxStayNights)) {
                    $stay_nights = count($maxStayNights);
                }
                $stay_nights = htmlspecialchars($stay_nights);
                $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);
                $marks = $this->calendarMarkArrayService->getCalendarMarkArray($availabilityRoomMonth['availabilityRoomMonth']);
                //指定された種類の部屋の、指定月の値段表を取得。プランごとの多重配列。
                $pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($room_id, $year, $month);
                //月初～月末（例：１～３１）のdays配列。
                $days = $this->yearMonthToDaysService->getDays($year, $month);
                //指定された部屋のプランデータを取得。見出しや内容など。
                $plansData = $this->getPlansDataService->getPlansData();
                //初期表示用の、最初のプラン（0=１泊２食付きプラン）
                $selectedPlan = $request['plan'];
                // 初期表示用の価格配列
                $prices = $pricesAllPlan[$selectedPlan];
                //指定された部屋の人数制限。
                $maxGuest_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($room_id);
                //カレンダーで、指定月１日が何曜日から始まるか。0=日曜日～
                $start_weekDay = $this->weekDayService->getStartWeekDay_From_Ym($year, $month);
                //カレンダー生成用、在庫カレンダー最後尾の日付
                $maxDate = $this->maxCheckoutService->getMaxCheckout();
                $maxYear = $maxDate['maxYear'];
                $maxMonth = $maxDate['maxMonth'];
                include __DIR__ . '/../views/reserveForm.php';
                exit();
            }
            //バックエンドで料金を再計算。
            $final_price = $this->pricesCalendarService->getFinalPrice($request);

            //セッション変数に保持(「戻る」ボタン用)
            $_SESSION['reserve_form'] = [
                'room_id' => $request['room_id'],
                'plan' => $request['plan'],
                'stay_nights' => $request['stay_nights'],
                'person' => $request['person'],
                'user_name' => $request['user_name'],
                'user_telphone' => mb_convert_kana($request['user_telphone'], 'n', 'UTF-8'),
                'user_address' => $request['user_address'],
                'email' => $request['email'],
                'comment' => $request['comment'],
                'checkin_date' => $request['checkin_date'],
                'checkout_date' => $request['checkout_date'],
                'total_price' => $request['total_price']
            ];

            //セッション変数に保持。(予約完了用)
            $_SESSION['reserve'] = [
                'room_id' => $request['room_id'],
                'user_name' => $request['user_name'],
                'user_telphone' => mb_convert_kana($request['user_telphone'], 'n', 'UTF-8'), //電話番号、全角なら半角へ。
                'user_address' => $request['user_address'],
                'email' => $request['email'],
                'comment' => $request['comment'],
                'checkin_date' => $request['checkin_date'],
                'checkout_date' => $request['checkout_date'],
                'total_price' => $final_price,
                'plan' => $request['plan'],
                'person' => $request['person'],
                'stay_nights' => $request['stay_nights']
            ];

            //最終確認ビュー表示用。
            $room_id = htmlspecialchars($request['room_id'], ENT_QUOTES, 'UTF-8');
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($request['room_id']);
            $room_name = $room_information['room_name'];
            $user_name = htmlspecialchars($request['user_name'], ENT_QUOTES, 'UTF-8');
            $user_telphone = htmlspecialchars(mb_convert_kana($request['user_telphone'], 'n', 'UTF-8'), ENT_QUOTES, 'UTF-8');
            $user_address = htmlspecialchars($request['user_address'], ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8');
            $comment = htmlspecialchars($request['comment'], ENT_QUOTES, 'UTF-8');
            $checkin_date = htmlspecialchars($request['checkin_date'], ENT_QUOTES, 'UTF-8');
            $checkout_date = htmlspecialchars($request['checkout_date'], ENT_QUOTES, 'UTF-8');
            $total_price = htmlspecialchars($final_price, ENT_QUOTES, 'UTF-8');
            $plan = htmlspecialchars($request['plan'], ENT_QUOTES, 'UTF-8');
            $stay_nights = htmlspecialchars($request['stay_nights'], ENT_QUOTES, 'UTF-8');
            //戻るボタン用
            $year = date('Y', strtotime($checkin_date));
            $month = date('m', strtotime($checkin_date));
            $day = date('d', strtotime($checkin_date));
            //プラン名をプランタイトルに変換する。
            $plan_title = $this->getPlansDataService->getPlanTitle($plan);
            $person = htmlspecialchars($request['person'], ENT_QUOTES, 'UTF-8');
            $token=$_SESSION['reserve_token'];
            include __DIR__ . '/../views/reserveReconfirm.php';
            exit();

            //例外処理
        } catch (Exception $e) {
            if (isset($_SESSION['reserve'])) {
                unset($_SESSION['reserve']);
            }
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }



    ////予約確定メソッド。
    public function reserve_confirm()
    {
        if (!isset($_SESSION['reserve'])) {
            unset($_SESSION['reserve_form']);
            $message="不正なリクエストです。3秒後にTOPページに戻ります。";
            include __DIR__.'/../views/false.php';
            exit();
        }

        //二重送信防止トークンチェック
        if (
            empty($_POST['reserve_token']) ||
            empty($_SESSION['reserve_token']) ||
            $_POST['reserve_token'] !== $_SESSION['reserve_token']
        ) {
            unset($_SESSION['reserve_token']);
            $message="不正なリクエストです。3秒後にTOPページに戻ります。";
            include __DIR__.'/../views/false.php';
            exit;
        }
        unset($_SESSION['reserve_token']);


        try {
            //最終的に、セッション変数を使って予約テーブルと在庫テーブルの２つに保存。できなかったら差し戻し。
            $result = $this->reservationService->reserve($_SESSION['reserve']);

            if ($result['success'] == false) {
                unset($_SESSION['reserve']);
                $message = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
                $estimate = $_SESSION['reserve_form']['total_price'];
                $number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($_SESSION['reserve_form']['room_id']);
                $room_id = htmlspecialchars($_SESSION['reserve_form']['room_id'], ENT_QUOTES, 'UTF-8');
                //room_idを部屋の名前（日本語）に変換する。
                $room_information = $this->getRoomInformationService->getRoomInformation($_SESSION['reserve_form']['room_id']);
                $room_name = $room_information['room_name'];
                $user_name = htmlspecialchars($_SESSION['reserve_form']['user_name'], ENT_QUOTES, 'UTF-8');
                $user_telphone = htmlspecialchars($_SESSION['reserve_form']['user_telphone'], ENT_QUOTES, 'UTF-8');
                $user_address = htmlspecialchars($_SESSION['reserve_form']['user_address'], ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars($_SESSION['reserve_form']['email'], ENT_QUOTES, 'UTF-8');
                $comment = htmlspecialchars($_SESSION['reserve_form']['comment'], ENT_QUOTES, 'UTF-8');
                $checkin_date = htmlspecialchars($_SESSION['reserve_form']['checkin_date'], ENT_QUOTES, 'UTF-8');
                $checkout_date = htmlspecialchars($_SESSION['reserve_form']['checkout_date'], ENT_QUOTES, 'UTF-8');
                $total_price = htmlspecialchars($_SESSION['reserve_form']['total_price'], ENT_QUOTES, 'UTF-8');
                $plan = htmlspecialchars($_SESSION['reserve_form']['plan'], ENT_QUOTES, 'UTF-8');
                //プラン名をプランタイトルに変換する。
                $plan_title = $this->getPlansDataService->getPlanTitle($plan);
                $person = htmlspecialchars($_SESSION['reserve_form']['person'], ENT_QUOTES, 'UTF-8');
                //宿泊日数のセレクトボックスの値を生成。
                $year = date('Y', strtotime($_SESSION['reserve_form']['checkin_date']));
                $month = date('m', strtotime($_SESSION['reserve_form']['checkin_date']));
                $day = date('d', strtotime($_SESSION['reserve_form']['checkin_date']));
                $stay_nights = $_SESSION['reserve_form']['stay_nights'];
                $maxStayNights = $this->reservationService->makeNumStayNights($room_id, $year, $month, $day);
                //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
                if ($stay_nights > count($maxStayNights)) {
                    $stay_nights = count($maxStayNights);
                }
                $stay_nights = htmlspecialchars($stay_nights);
                $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);
                $marks = $this->calendarMarkArrayService->getCalendarMarkArray($availabilityRoomMonth['availabilityRoomMonth']);
                //指定された種類の部屋の、指定月の値段表を取得。プランごとの多重配列。
                $pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($room_id, $year, $month);
                //月初～月末（例：１～３１）のdays配列。
                $days = $this->yearMonthToDaysService->getDays($year, $month);
                //指定された部屋のプランデータを取得。見出しや内容など。
                $plansData = $this->getPlansDataService->getPlansData();
                //初期表示用の、最初のプラン（0=１泊２食付きプラン）
                $selectedPlan = $_SESSION['reserve_form']['plan'];
                // 初期表示用の価格配列
                $prices = $pricesAllPlan[$selectedPlan];
                //指定された部屋の人数制限。
                $maxGuest_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($room_id);
                //カレンダーで、指定月１日が何曜日から始まるか。0=日曜日～
                $start_weekDay = $this->weekDayService->getStartWeekDay_From_Ym($year, $month);
                //カレンダー生成用、在庫カレンダー最後尾の日付
                $maxDate = $this->maxCheckoutService->getMaxCheckout();
                $maxYear = $maxDate['maxYear'];
                $maxMonth = $maxDate['maxMonth'];
                include __DIR__ . '/../views/reserveForm.php';
                exit();
            }

            $message = htmlspecialchars($result['message']);
            unset($_SESSION['reserve']);
            unset($_SESSION['reserve_form']);
            unset($_SESSION['reserve_token']);
            include __DIR__ . '/../views/success.php';
            exit();

            //例外処理。
        } catch (Exception $e) {
            unset($_SESSION['reserve']);
            unset($_SESSION['reserve_form']);
            unset($_SESSION['reserve_token']);
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }



    //--予約キャンセル-------------------------------------

    ////キャンセルフォーム表示。予約IDとメールアドレスを入力してもらう予定。
    public function reserve_cancel_form()
    {
        include __DIR__ . '/../views/reserveCancelForm.php';
        exit();
    }


    ////キャンセルリクエスト照会メソッド。成功だとキャンセル最終確認ビューへ。
    public function reserve_cancel_reconfirm($request)
    {
        //予約IDとメールアドレスをバリデーション。通らなかったら差し戻し。
        $error = $this->cancelFormRequest->cancelFormValidate($request);
        if ($error) {
            $errors = $error;
            $id = htmlspecialchars($request['id'], ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8');
            include __DIR__ . '/../views/reserveCancelForm.php';
            exit();
        }

        //入力バリデーション後、予約IDが全角だった場合、照会前に半角へ変換。
        $request['id'] = mb_convert_kana($request['id'], 'n', 'utf-8');

        //既予約が存在するか、また入力内容と一致するか照合。
        try {
            $result = $this->reservationService->showReservation($request);
            if ($result['success'] == false) {
                $message = $result['message'];
                include __DIR__ . '/../views/false.php';
                exit();
            }

            //セッション変数を使って、ビューをまたいで保持可能にする。
            $_SESSION['reserve_cancel'] = [
                'id' => $result['reservation']['id'],
                'room_id' => $result['reservation']['room_id'],
                'user_name' => $result['reservation']['user_name'],
                'user_telphone' => $result['reservation']['user_telphone'],
                'user_address' => $result['reservation']['user_address'],
                'email' => $result['reservation']['email'],
                'comment' => $result['reservation']['comment'],
                'checkin_date' => $result['reservation']['checkin_date'],
                'checkout_date' => $result['reservation']['checkout_date'],
                'total_price' => $result['reservation']['total_price'],
                'plan' => $result['reservation']['plan'],
                'person' => $result['reservation']['person']
            ];

            //ビュー表示用。
            $id = htmlspecialchars($result['reservation']['id'], ENT_QUOTES, 'UTF-8');
            $room_id = htmlspecialchars($result['reservation']['room_id'], ENT_QUOTES, 'UTF-8');
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($result['reservation']['room_id']);
            $room_name = $room_information['room_name'];
            $user_name = htmlspecialchars($result['reservation']['user_name'], ENT_QUOTES, 'UTF-8');
            $user_telphone = htmlspecialchars($result['reservation']['user_telphone'], ENT_QUOTES, 'UTF-8');
            $user_address = htmlspecialchars($result['reservation']['user_address'], ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($result['reservation']['email'], ENT_QUOTES, 'UTF-8');
            $comment = htmlspecialchars($result['reservation']['comment'], ENT_QUOTES, 'UTF-8');
            $checkin_date = htmlspecialchars($result['reservation']['checkin_date'], ENT_QUOTES, 'UTF-8');
            $checkout_date = htmlspecialchars($result['reservation']['checkout_date'], ENT_QUOTES, 'UTF-8');
            $total_price = htmlspecialchars($result['reservation']['total_price'], ENT_QUOTES, 'UTF-8');
            $plan = htmlspecialchars($result['reservation']['plan'], ENT_QUOTES, 'UTF-8');
            //プラン名をプランタイトルに変換する。
            $plan_title = $this->getPlansDataService->getPlanTitle($plan);
            $person = htmlspecialchars($result['reservation']['person'], ENT_QUOTES, 'UTF-8');
            include __DIR__ . '/../views/reserveCancelReconfirm.php';
            exit();
        } catch (Exception $e) {
            if (isset($_SESSION['reserve_cancel'])) {
                unset($_SESSION['reserve_cancel']);
            }
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }


    ////キャンセル確定メソッド。セッション変数[reserve_cancel]を使ってキャンセルする。
    public function reserve_cancel_confirm()
    {
        if (!isset($_SESSION['reserve_cancel'])) {
            $message="不正なリクエストです。3秒後にTOPページに戻ります。";
            include __DIR__.'/../views/false.php';
            exit();
        }


        try {
            $result = $this->reservationService->cancel($_SESSION['reserve_cancel']);
            $message = $result['message'];
            unset($_SESSION['reserve_cancel']);
            include __DIR__ . '/../views/success.php';
            exit();
        } catch (Exception $e) {
            $message = $e->getMessage();
            unset($_SESSION['reserve_cancel']);
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }



    //--予約変更-------------------------------------------------

    ////予約変更フォーム表示。既予約の予約IDとメールアドレスを入力してもらう予定。
    public function reserve_updateVerify_form()
    {
        include __DIR__ . '/../views/reserveUpdateVerifyForm.php';
    }


    ////リクエスト照会し、存在しなければIDとメールアドレスを持って差し戻し。
    ////存在すれば、旧予約情報を保持しつつ、変更内容入力フォームを表示。
    ////部屋ごとのカレンダー用のデータもビューに渡す。在庫は、予約済みのものを一時的に戻して計算。
    public function reserve_update_form($request)
    {
        //戻るボタンで戻った時に、verifyのバリデーションを再度通過する為に必要。
        $_SESSION['reserve_update_verify']['id'] = $request['id'];
        $_SESSION['reserve_update_verify']['email'] = $request['email'];


        //予約IDとメールアドレスをバリデーション。キャンセルバリデーションを再利用。
        $error = $this->cancelFormRequest->cancelFormValidate($request);
        if ($error) {
            $id = htmlspecialchars($request['id'] ?? $_SESSION['reserve_update_verify']['id'], ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($request['email'] ?? $_SESSION['reserve_update_verify']['email'], ENT_QUOTES, 'UTF-8');
            include __DIR__ . '/../views/reserveUpdateVerifyForm.php';
            exit();
        }

        //入力バリデーション後、予約IDが全角だった場合、照会前に半角へ変換。
        $request['id'] = mb_convert_kana($request['id'], 'n', 'utf-8');

        //既予約が存在するか、入力内容と一致するか照合。戻り値は結果と該当レコード。一致しなければメッセージを持って差し戻し。
        try {
            $oldresult = $this->reservationService->showReservation($request);
            if ($oldresult['success'] == false) {
                $message = $oldresult['message'];
                include __DIR__ . '/../views/reserveUpdateVerifyForm.php';
                exit();
            }

            //セッション変数を使って、旧予約内容(該当レコード)をビューをまたいで保持可能にする。個人情報は不要。
            $_SESSION['reserve_update_old'] = [
                'id' => $oldresult['reservation']['id'],
                'room_id' => $oldresult['reservation']['room_id'],
                'comment' => $oldresult['reservation']['comment'],
                'checkin_date' => $oldresult['reservation']['checkin_date'],
                'checkout_date' => $oldresult['reservation']['checkout_date'],
                'total_price' => $oldresult['reservation']['total_price'],
                'plan' => $oldresult['reservation']['plan'],
                'person' => $oldresult['reservation']['person']
            ];

            //プランデータを取得。plan_name(英字)やplan_title(日本語)や内容など。
            $plansData = $this->getPlansDataService->getPlansData();
            //初期表示用の、最初のプラン。戻るボタンで戻ってきらセッション変数を優先し、なければ旧予約情報のもので。
            $selectedPlanName = $_SESSION['reserve_update_new']['plan'] ?? $oldresult['reservation']['plan'];
            //初期表示用のプラン名をプランタイトルに変換する。
            $selected_plan_title = $this->getPlansDataService->getPlanTitle($selectedPlanName);


            //初期表示用に一か月分の在庫データを取得。初期設定は、戻るボタンで戻ってきた場合はセッション変数を優先し、なければ既予約のものを使う。
            $room_id = $_SESSION['reserve_update_new']['room_id'] ?? $_SESSION['reserve_update_old']['room_id'];
            $checkin_date = $_SESSION['reserve_update_new']['checkin_date'] ?? $_SESSION['reserve_update_old']['checkin_date'];
            $year = date('Y', strtotime($checkin_date));
            $month = date('m', strtotime($checkin_date));
            $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);
            if ($availabilityRoomMonth['success'] == false) {       //空配列が返ってきた場合。
                unset($_SESSION['reserve_update_old']);
                unset($_SESSION['reserve_update_new']);
                unset($_SESSION['reserve_update_calendar']);
                unset($_SESSION['reserve_update_verify']);
                $message = $availabilityRoomMonth['message'];
                include __DIR__ . '/../views/false.php';
                exit();
            }

            //初期表示用に、戻るボタンで戻ってきた時はセッション変数をもとに、そうでなければ既予約情報をもとに、チェックイン月の１日が何曜日かを返す（カレンダー生成用）。
            $start_weekDay = $this->weekDayService->getStartWeekDay_From_checkinDate($_SESSION['reserve_update_new']['checkin_date'] ?? $oldresult['reservation']['checkin_date']);

            //在庫を一時的に戻す。リストック後は'success'判定がなくなり、普通の配列に。
            $restocked_availabilityRoomMonth = $this->restockService->restock($availabilityRoomMonth);
            //１～月末日まで、〇△×に変換。在庫戻しと関係あるので、リストック後の配列を使用。
            $markArrayMonth = $this->calendarMarkArrayService->getCalendarMarkArray($restocked_availabilityRoomMonth);

            //値段データも取得。 １～月末日まで。価格は在庫戻しと関係ないので、修正前の配列を使う。
            $result_year = date('Y', strtotime($_SESSION['reserve_update_new']['checkin_date'] ?? $oldresult['reservation']['checkin_date']));
            $result_month = date('m', strtotime($_SESSION['reserve_update_new']['checkin_date'] ?? $oldresult['reservation']['checkin_date']));
            $pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($_SESSION['reserve_update_new']['room_id'] ?? $oldresult['reservation']['room_id'], $result_year, $result_month);
            $pricesMonth = $pricesAllPlan[$_SESSION['reserve_update_new']['plan'] ?? $oldresult['reservation']['plan']];


            //カレンダー生成用、在庫カレンダー最後尾の日付
            $maxDate = $this->maxCheckoutService->getMaxCheckout();
            $maxYear = $maxDate['maxYear'];
            $maxMonth = $maxDate['maxMonth'];


            //見積初期表示用の$estimateをビューに与える。戻るボタンでもどった時はセッション変数を優先、そうでなければ旧予約情報をもとに。
            $estimate = $_SESSION['reserve_update_new']['total_price'] ?? $this->pricesCalendarService->getEstimate($oldresult['reservation']['room_id'], $oldresult['reservation']['plan'], $oldresult['reservation']['person'], $oldresult['reservation']['checkin_date'], $oldresult['reservation']['checkout_date']);



            //差し戻し用に、リストック状態のカレンダー表示用データ&カレンダー１日の曜日をセッション変数で保持。次の最終確認のバリデーション不合格後のincludeページに与える。
            $_SESSION['reserve_update_calendar'] = [
                'marks' => $markArrayMonth,
                'prices'    => $pricesMonth,
                'start_weekDay' => $start_weekDay,
                'selectedPlanName' => $selectedPlanName
            ];
            //すべてのルーム情報一覧。
            $rooms_information_all = $this->getRoomInformationService->getRoomsinformation_all();
            //ルームの名前一覧。
            $rooms_name = $this->getRoomInformationService->getRoomsName();

            //ビュー表示用。number_OfRoomは（分かりにくくてごめんなさい）部屋の制限人数。
            $number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($_SESSION['reserve_update_new']['room_id'] ?? $oldresult['reservation']['room_id']);
            $days = $this->yearMonthToDaysService->getDays($year, $month); //上記の、一か月分の在庫データ関連から変数を引用。戻るボタンで戻っている場合はセッション変数から、そうでなければ旧予約情報から算出。
            $marks = $markArrayMonth;
            $prices = $pricesMonth;
            $checkin_year = (int)date('Y', strtotime($_SESSION['reserve_update_new']['checkin_date'] ?? $oldresult['reservation']['checkin_date'])); //AJAXカレンダー初期表示用。
            $checkin_month = (int)date('n', strtotime($_SESSION['reserve_update_new']['checkin_date'] ?? $oldresult['reservation']['checkin_date'])); //AJAXカレンダー初期表示用。
            $old_id = htmlspecialchars($oldresult['reservation']['id'], ENT_QUOTES, 'UTF-8');
            $old_room_id = htmlspecialchars($oldresult['reservation']['room_id'], ENT_QUOTES, 'UTF-8');
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($oldresult['reservation']['room_id']);
            $old_room_name = $room_information['room_name'];
            $old_comment = htmlspecialchars($oldresult['reservation']['comment'], ENT_QUOTES, 'UTF-8');
            $old_checkin_date = htmlspecialchars($oldresult['reservation']['checkin_date'], ENT_QUOTES, 'UTF-8');
            $old_checkout_date = htmlspecialchars($oldresult['reservation']['checkout_date'], ENT_QUOTES, 'UTF-8');
            $old_total_price = htmlspecialchars($oldresult['reservation']['total_price'], ENT_QUOTES, 'UTF-8');
            $old_plan = htmlspecialchars($oldresult['reservation']['plan'], ENT_QUOTES, 'UTF-8');
            //プラン名をプランタイトルに変換する。
            $old_plan_title = $this->getPlansDataService->getPlanTitle($old_plan);
            $old_person = htmlspecialchars($oldresult['reservation']['person'], ENT_QUOTES, 'UTF-8');

            $new_room_id = htmlspecialchars($_SESSION['reserve_update_new']['room_id'] ?? $oldresult['reservation']['room_id'], ENT_QUOTES, 'UTF-8');
            //room_idを部屋の名前（日本語）に変換する。
            $new_room_information = $this->getRoomInformationService->getRoomInformation($_SESSION['reserve_update_new']['room_id'] ?? $oldresult['reservation']['room_id']);
            $new_room_name = $new_room_information['room_name'];
            $new_comment = htmlspecialchars($_SESSION['reserve_update_new']['comment'] ?? $oldresult['reservation']['comment'], ENT_QUOTES, 'UTF-8');
            $new_checkin_date = htmlspecialchars($_SESSION['reserve_update_new']['checkin_date'] ?? $oldresult['reservation']['checkin_date'], ENT_QUOTES, 'UTF-8');
            $new_checkout_date = htmlspecialchars($_SESSION['reserve_update_new']['checkout_date'] ?? $oldresult['reservation']['checkout_date'], ENT_QUOTES, 'UTF-8');
            $new_total_price = htmlspecialchars($_SESSION['reserve_update_new']['total_price'] ?? $oldresult['reservation']['total_price'], ENT_QUOTES, 'UTF-8');
            $new_plan = htmlspecialchars($_SESSION['reserve_update_new']['plan'] ?? $oldresult['reservation']['plan'], ENT_QUOTES, 'UTF-8');
            //プラン名をプランタイトルに変換する。
            $new_plan_title = $this->getPlansDataService->getPlanTitle($new_plan);
            $new_person = htmlspecialchars($_SESSION['reserve_update_new']['person'] ?? $oldresult['reservation']['person'], ENT_QUOTES, 'UTF-8');
            $stay_nights = $_SESSION['reserve_update_new']['stay_nights'] ?? intval((strtotime($oldresult['reservation']['checkout_date']) - strtotime($oldresult['reservation']['checkin_date'])) / 86400);

            $room_id_forNights = $_SESSION['reserve_update_new']['room_id'] ?? $oldresult['reservation']['room_id'];
            $checkin_date_forNights = $_SESSION['reserve_update_new']['checkin_date'] ?? $oldresult['reservation']['checkin_date'];
            $maxStayNights = $this->reservationService->makeNumStayNights(
                $room_id_forNights,
                date('Y', strtotime($checkin_date_forNights)),
                date('m', strtotime($checkin_date_forNights)),
                date('d', strtotime($checkin_date_forNights))
            );
            //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
            if ($stay_nights > count($maxStayNights)) {
                $stay_nights = count($maxStayNights);
            }
            $stay_nights = htmlspecialchars($stay_nights);


            include __DIR__ . '/../views/reserveUpdateForm.php';
            exit();
        } catch (Exception $e) {
            unset($_SESSION['reserve_update_old']);
            unset($_SESSION['reserve_update_new']);
            unset($_SESSION['reserve_update_calendar']);
            unset($_SESSION['reserve_update_verify']);
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }


    ////変更内容最終確認ビューを表示。旧予約情報と新予約情報を表示。
    public function reserve_update_reconfirm($request)
    {
        if (!isset($_SESSION['reserve_update_old'])) {
            $message="不正なリクエストです。3秒後にTOPページに戻ります。";
            include __DIR__.'/../views/false.php';
            exit();
        }

        //バックエンドで料金を再計算。
        $final_price = $this->pricesCalendarService->getFinalPrice($request);

        //セッション変数を使って、新たな予約内容をビューをまたいで保持可能にする。個人情報は不要。
        $_SESSION['reserve_update_new'] = [
            'id' => $_SESSION['reserve_update_old']['id'],
            'room_id' => $request['room_id'],
            'comment' => $request['comment'],
            'checkin_date' => $request['checkin_date'],
            'checkout_date' => $request['checkout_date'],
            'total_price' => $final_price,
            'plan' => $request['plan'],
            'person' => $request['person'],
            'stay_nights' => $request['stay_nights']
        ];


        //バリデーション。通らなかったら差し戻し。
        $error = $this->updateFormRequest->updateFormValidate($request);
        if ($error) {
            $number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($_SESSION['reserve_update_new']['room_id']);
            $plansData = $this->getPlansDataService->getPlansData();
            $selectedPlanName = $_SESSION['reserve_update_new']['plan'];
            $selected_plan_title = $this->getPlansDataService->getPlanTitle($selectedPlanName);
            $room_id = $_SESSION['reserve_update_new']['room_id'];
            $checkin_date = $_SESSION['reserve_update_new']['checkin_date'];
            $year = date('Y', strtotime($checkin_date));
            $month = date('m', strtotime($checkin_date));
            $checkin_year = $year;
            $checkin_month = $month;
            $days = $this->yearMonthToDaysService->getDays($year, $month);
            $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);
            $restocked = $this->restockService->restock($availabilityRoomMonth);
            $marks = $this->calendarMarkArrayService->getCalendarMarkArray($restocked);
            $pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($room_id, $year, $month);
            $prices = $pricesAllPlan[$selectedPlanName];
            $start_weekDay = $this->weekDayService->getStartWeekDay_From_checkinDate($checkin_date);

            $old_checkin_year = (int)date('Y', strtotime($_SESSION['reserve_update_old']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の年。
            $old_checkin_month = (int)date('n', strtotime($_SESSION['reserve_update_old']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の月。
            $old_id = htmlspecialchars($_SESSION['reserve_update_old']['id'], ENT_QUOTES, 'UTF-8');
            $old_room_id = htmlspecialchars($_SESSION['reserve_update_old']['room_id'], ENT_QUOTES, 'UTF-8');
            //すべてのルーム情報一覧。
            $rooms_information_all = $this->getRoomInformationService->getRoomsinformation_all();
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($_SESSION['reserve_update_old']['room_id']);
            $old_room_name = $room_information['room_name'];
            $old_comment = htmlspecialchars($_SESSION['reserve_update_old']['comment'], ENT_QUOTES, 'UTF-8');
            $old_checkin_date = htmlspecialchars($_SESSION['reserve_update_old']['checkin_date'], ENT_QUOTES, 'UTF-8');
            $old_checkout_date = htmlspecialchars($_SESSION['reserve_update_old']['checkout_date'], ENT_QUOTES, 'UTF-8');
            $old_total_price = htmlspecialchars($_SESSION['reserve_update_old']['total_price'], ENT_QUOTES, 'UTF-8');
            $old_plan = htmlspecialchars($_SESSION['reserve_update_old']['plan'], ENT_QUOTES, 'UTF-8');
            //プラン名をプランタイトルに変換する。
            $old_plan_title = $this->getPlansDataService->getPlanTitle($old_plan);
            $old_person = htmlspecialchars($_SESSION['reserve_update_old']['person'], ENT_QUOTES, 'UTF-8');
            $new_room_id = htmlspecialchars($request['room_id'], ENT_QUOTES, 'UTF-8');
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($request['room_id']);
            $new_room_name = $room_information['room_name'];
            $new_comment = htmlspecialchars($request['comment'], ENT_QUOTES, 'UTF-8');
            $new_checkin_date = htmlspecialchars($request['checkin_date'], ENT_QUOTES, 'UTF-8');
            $new_checkout_date = htmlspecialchars($request['checkout_date'], ENT_QUOTES, 'UTF-8');
            $new_plan = htmlspecialchars($request['plan'], ENT_QUOTES, 'UTF-8');
            //プラン名をプランタイトルに変換する。
            $new_plan_title = $this->getPlansDataService->getPlanTitle($new_plan);
            $new_person = htmlspecialchars($request['person'], ENT_QUOTES, 'UTF-8');
            $number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($request['room_id']);

            $stay_nights = $request['stay_nights'];
            $room_id_forNights = $request['room_id'];
            $checkin_date_forNights = $request['checkin_date'];
            $maxStayNights = $this->reservationService->makeNumStayNights(
                $room_id_forNights,
                date('Y', strtotime($checkin_date_forNights)),
                date('m', strtotime($checkin_date_forNights)),
                date('d', strtotime($checkin_date_forNights))
            );
            //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
            if ($stay_nights > count($maxStayNights)) {
                $stay_nights = count($maxStayNights);
            }
            $stay_nights = htmlspecialchars($stay_nights);
            $estimate = $_SESSION['reserve_update_new']['total_price'];

            include __DIR__ . '/../views/reserveUpdateForm.php';
            exit();
        }

        //変更後の予約の部屋と期間が本当に空いているか再度チェック。空いていなければ差し戻し。
        try {
            $result = $this->reservationService->hasStock($request);
            if ($result['success'] == false) {
                $plansData = $this->getPlansDataService->getPlansData();
                $selectedPlanName = $_SESSION['reserve_update_new']['plan'];
                $selected_plan_title = $this->getPlansDataService->getPlanTitle($selectedPlanName);
                $room_id = $_SESSION['reserve_update_new']['room_id'];
                $checkin_date = $_SESSION['reserve_update_new']['checkin_date'];
                $year = date('Y', strtotime($checkin_date));
                $month = date('m', strtotime($checkin_date));
                $checkin_year = $year;
                $checkin_month = $month;
                $days = $this->yearMonthToDaysService->getDays($year, $month);
                $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);
                $restocked = $this->restockService->restock($availabilityRoomMonth);
                $marks = $this->calendarMarkArrayService->getCalendarMarkArray($restocked);
                $pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($room_id, $year, $month);
                $prices = $pricesAllPlan[$selectedPlanName];
                $start_weekDay = $this->weekDayService->getStartWeekDay_From_checkinDate($checkin_date);

                $message = $result['message'];
                $old_checkin_year = (int)date('Y', strtotime($_SESSION['reserve_update_old']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の年。
                $old_checkin_month = (int)date('n', strtotime($_SESSION['reserve_update_old']['checkin_date'])); //AJAXカレンダー初期表示用。旧予約の月。
                $old_id = htmlspecialchars($_SESSION['reserve_update_old']['id'], ENT_QUOTES, 'UTF-8');
                $old_room_id = htmlspecialchars($_SESSION['reserve_update_old']['room_id'], ENT_QUOTES, 'UTF-8');
                //すべてのルーム情報一覧。
                $rooms_information_all = $this->getRoomInformationService->getRoomsinformation_all();
                //room_idを部屋の名前（日本語）に変換する。
                $room_information = $this->getRoomInformationService->getRoomInformation($_SESSION['reserve_update_old']['room_id']);
                $old_room_name = $room_information['room_name'];
                $old_comment = htmlspecialchars($_SESSION['reserve_update_old']['comment'], ENT_QUOTES, 'UTF-8');
                $old_checkin_date = htmlspecialchars($_SESSION['reserve_update_old']['checkin_date'], ENT_QUOTES, 'UTF-8');
                $old_checkout_date = htmlspecialchars($_SESSION['reserve_update_old']['checkout_date'], ENT_QUOTES, 'UTF-8');
                $old_total_price = htmlspecialchars($_SESSION['reserve_update_old']['total_price'], ENT_QUOTES, 'UTF-8');
                $old_plan = htmlspecialchars($_SESSION['reserve_update_old']['plan'], ENT_QUOTES, 'UTF-8');
                //プラン名をプランタイトルに変換する。
                $old_plan_title = $this->getPlansDataService->getPlanTitle($old_plan);
                $old_person = htmlspecialchars($_SESSION['reserve_update_old']['person'], ENT_QUOTES, 'UTF-8');
                $new_room_id = htmlspecialchars($request['room_id'], ENT_QUOTES, 'UTF-8');
                //room_idを部屋の名前（日本語）に変換する。
                $room_information = $this->getRoomInformationService->getRoomInformation($request['room_id']);
                $new_room_name = $room_information['room_name'];
                $new_comment = htmlspecialchars($request['comment'], ENT_QUOTES, 'UTF-8');
                $new_checkin_date = htmlspecialchars($request['checkin_date'], ENT_QUOTES, 'UTF-8');
                $new_checkout_date = htmlspecialchars($request['checkout_date'], ENT_QUOTES, 'UTF-8');
                $new_plan = htmlspecialchars($request['plan'], ENT_QUOTES, 'UTF-8');
                //プラン名をプランタイトルに変換する。
                $new_plan_title = $this->getPlansDataService->getPlanTitle($new_plan);
                $new_person = htmlspecialchars($request['person'], ENT_QUOTES, 'UTF-8');
                $number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($request['room_id']);

                $stay_nights = $request['stay_nights'];
                $room_id_forNights = $request['room_id'];
                $checkin_date_forNights = $request['checkin_date'];
                $maxStayNights = $this->reservationService->makeNumStayNights(
                    $room_id_forNights,
                    date('Y', strtotime($checkin_date_forNights)),
                    date('m', strtotime($checkin_date_forNights)),
                    date('d', strtotime($checkin_date_forNights))
                );
                //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
                if ($stay_nights > count($maxStayNights)) {
                    $stay_nights = count($maxStayNights);
                }
                $stay_nights = htmlspecialchars($stay_nights);
                $estimate = $_SESSION['reserve_update_new']['total_price'];
                include __DIR__ . '/../views/reserveUpdateForm.php';
                exit();
            }



            //ビュー表示用。
            $id = htmlspecialchars($_SESSION['reserve_update_old']['id'], ENT_QUOTES, 'UTF-8');
            $old_room_id = htmlspecialchars($_SESSION['reserve_update_old']['room_id'], ENT_QUOTES, 'UTF-8');
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($_SESSION['reserve_update_old']['room_id']);
            $old_room_name = $room_information['room_name'];
            $old_comment = htmlspecialchars($_SESSION['reserve_update_old']['comment'], ENT_QUOTES, 'UTF-8');
            $old_checkin_date = htmlspecialchars($_SESSION['reserve_update_old']['checkin_date'], ENT_QUOTES, 'UTF-8');
            $old_checkout_date = htmlspecialchars($_SESSION['reserve_update_old']['checkout_date'], ENT_QUOTES, 'UTF-8');
            $old_total_price = htmlspecialchars($_SESSION['reserve_update_old']['total_price'], ENT_QUOTES, 'UTF-8');
            $old_plan = htmlspecialchars($_SESSION['reserve_update_old']['plan'], ENT_QUOTES, 'UTF-8');
            //プラン名をプランタイトルに変換する。
            $old_plan_title = $this->getPlansDataService->getPlanTitle($old_plan);
            $old_person = htmlspecialchars($_SESSION['reserve_update_old']['person'], ENT_QUOTES, 'UTF-8');
            $new_room_id = htmlspecialchars($_SESSION['reserve_update_new']['room_id'], ENT_QUOTES, 'UTF-8');
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($_SESSION['reserve_update_new']['room_id']);
            $new_room_name = $room_information['room_name'];
            $new_comment = htmlspecialchars($_SESSION['reserve_update_new']['comment'], ENT_QUOTES, 'UTF-8');
            $new_checkin_date = htmlspecialchars($_SESSION['reserve_update_new']['checkin_date'], ENT_QUOTES, 'UTF-8');
            $new_checkout_date = htmlspecialchars($_SESSION['reserve_update_new']['checkout_date'], ENT_QUOTES, 'UTF-8');
            $new_total_price = htmlspecialchars($_SESSION['reserve_update_new']['total_price'], ENT_QUOTES, 'UTF-8');
            $new_plan = htmlspecialchars($_SESSION['reserve_update_new']['plan'], ENT_QUOTES, 'UTF-8');
            //プラン名をプランタイトルに変換する。
            $new_plan_title = $this->getPlansDataService->getPlanTitle($new_plan);
            $new_person = htmlspecialchars($_SESSION['reserve_update_new']['person'], ENT_QUOTES, 'UTF-8');
            //二重送信防止トークン
            $token = bin2hex(random_bytes(32));
            $_SESSION['reserve_token'] = $token;
            include __DIR__ . '/../views/reserveUpdateReconfirm.php';
            exit();
            //例外処理。    
        } catch (Exception $e) {
            unset($_SESSION['reserve_update_old']);
            unset($_SESSION['reserve_update_new']);
            unset($_SESSION['reserve_update_calendar']);
            unset($_SESSION['reserve_update_verify']);
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }


    ////変更内容のDBへの書き込み。
    public function reserve_update_confirm()
    {
        if (!isset($_SESSION['reserve_update_old']) || !isset($_SESSION['reserve_update_new'])) {
            $message="不正なリクエストです。3秒後にTOPページに戻ります。";
            include __DIR__.'/../views/false.php';
            exit();
        }
        //二重送信防止トークンチェック
        if (
            empty($_POST['reserve_token']) ||
            empty($_SESSION['reserve_token']) ||
            $_POST['reserve_token'] !== $_SESSION['reserve_token']
        ) {
            unset($_SESSION['reserve_token']);
            $message="不正なリクエストです。3秒後にTOPページに戻ります。";
            include __DIR__.'/../views/false.php';
            exit;
        }
        unset($_SESSION['reserve_token']);


        try {
            $result = $this->reservationService->update($_SESSION['reserve_update_new']);
            //最終チェック。通らなければ差し戻し。
            if ($result['success'] == false) {
                $message = $result['message'];
                $id = $_SESSION['reserve_update_old']['id'];

                $plansData = $this->getPlansDataService->getPlansData();
                $selectedPlanName = $_SESSION['reserve_update_new']['plan'];
                $selected_plan_title = $this->getPlansDataService->getPlanTitle($selectedPlanName);
                $room_id = $_SESSION['reserve_update_new']['room_id'];
                $checkin_date = $_SESSION['reserve_update_new']['checkin_date'];
                $year = date('Y', strtotime($checkin_date));
                $month = date('m', strtotime($checkin_date));
                $checkin_year = $year;
                $checkin_month = $month;
                $days = $this->yearMonthToDaysService->getDays($year, $month);
                $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);
                $restocked = $this->restockService->restock($availabilityRoomMonth);
                $marks = $this->calendarMarkArrayService->getCalendarMarkArray($restocked);
                $pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($room_id, $year, $month);
                $prices = $pricesAllPlan[$selectedPlanName];
                $start_weekDay = $this->weekDayService->getStartWeekDay_From_checkinDate($checkin_date);

                $old_room_id = htmlspecialchars($_SESSION['reserve_update_old']['room_id'], ENT_QUOTES, 'UTF-8');
                //room_idを部屋の名前（日本語）に変換する。
                $room_information = $this->getRoomInformationService->getRoomInformation($_SESSION['reserve_update_old']['room_id']);
                $old_room_name = $room_information['room_name'];
                $old_comment = htmlspecialchars($_SESSION['reserve_update_old']['comment'], ENT_QUOTES, 'UTF-8');
                $old_checkin_date = htmlspecialchars($_SESSION['reserve_update_old']['checkin_date'], ENT_QUOTES, 'UTF-8');
                $old_checkout_date = htmlspecialchars($_SESSION['reserve_update_old']['checkout_date'], ENT_QUOTES, 'UTF-8');
                $old_total_price = htmlspecialchars($_SESSION['reserve_update_old']['total_price'], ENT_QUOTES, 'UTF-8');
                $old_plan = htmlspecialchars($_SESSION['reserve_update_old']['plan'], ENT_QUOTES, 'UTF-8');
                //プラン名をプランタイトルに変換する。
                $old_plan_title = $this->getPlansDataService->getPlanTitle($old_plan);
                $old_person = htmlspecialchars($_SESSION['reserve_update_old']['person'], ENT_QUOTES, 'UTF-8');
                $new_room_id = htmlspecialchars($_SESSION['reserve_update_new']['room_id'], ENT_QUOTES, 'UTF-8');
                //room_idを部屋の名前（日本語）に変換する。
                $room_information = $this->getRoomInformationService->getRoomInformation($_SESSION['reserve_update_new']['room_id']);
                $new_room_name = $room_information['room_name'];
                $new_comment = htmlspecialchars($_SESSION['reserve_update_new']['comment'], ENT_QUOTES, 'UTF-8');
                $new_checkin_date = htmlspecialchars($_SESSION['reserve_update_new']['checkin_date'], ENT_QUOTES, 'UTF-8');
                $new_checkout_date = htmlspecialchars($_SESSION['reserve_update_new']['checkout_date'], ENT_QUOTES, 'UTF-8');
                $new_total_price = htmlspecialchars($_SESSION['reserve_update_new']['total_price'], ENT_QUOTES, 'UTF-8');
                $new_plan = htmlspecialchars($_SESSION['reserve_update_new']['plan'], ENT_QUOTES, 'UTF-8');
                //プラン名をプランタイトルに変換する。
                $new_plan_title = $this->getPlansDataService->getPlanTitle($new_plan);
                $new_person = htmlspecialchars($_SESSION['reserve_update_new']['person'], ENT_QUOTES, 'UTF-8');
                $number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($_SESSION['reserve_update_new']['room_id']);

                $stay_nights = $_SESSION['reserve_update_new']['stay_nights'];
                $room_id_forNights = $_SESSION['reserve_update_new']['room_id'];
                $checkin_date_forNights = $_SESSION['reserve_update_new']['checkin_date'];
                $maxStayNights = $this->reservationService->makeNumStayNights(
                    $room_id_forNights,
                    date('Y', strtotime($checkin_date_forNights)),
                    date('m', strtotime($checkin_date_forNights)),
                    date('d', strtotime($checkin_date_forNights))
                );
                //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
                if ($stay_nights > count($maxStayNights)) {
                    $stay_nights = count($maxStayNights);
                }
                $stay_nights = htmlspecialchars($stay_nights);
                $estimate = $_SESSION['reserve_update_new']['total_price'];

                include __DIR__ . '/../views/reserveUpdateForm.php';
                exit();
            }
            //成功時。
            unset($_SESSION['reserve_update_old']);
            unset($_SESSION['reserve_update_new']);
            unset($_SESSION['reserve_update_calendar']);
            unset($_SESSION['reserve_update_verify']);
            unset($_SESSION['reserve_token']);
            $message = $result['message'];
            include __DIR__ . '/../views/success.php';
            exit();
            //例外処理。        
        } catch (Exception $e) {
            unset($_SESSION['reserve_update_old']);
            unset($_SESSION['reserve_update_new']);
            unset($_SESSION['reserve_update_calendar']);
            unset($_SESSION['reserve_update_verify']);
            unset($_SESSION['reserve_token']);
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }

    
}

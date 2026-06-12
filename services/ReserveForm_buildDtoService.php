<?php
require_once __DIR__ . '/../services/ReservationService.php';
require_once __DIR__ . '/../services/CalendarMarkArrayService.php';
require_once __DIR__ . '/../services/RoomMonthPriceService.php';
require_once __DIR__ . '/../services/YearMonthToDaysService.php';
require_once __DIR__ . '/../services/PricesCalendarService.php';
require_once __DIR__ . '/../services/GetPlansDataService.php';
require_once __DIR__ . '/../services/MaxGuest_OfRoomService.php';
require_once __DIR__ . '/../services/GetRoomInformationService.php';
require_once __DIR__ . '/../services/WeekDayService.php';
require_once __DIR__ . '/../services/MaxCheckoutService.php';

class ReserveForm_buildDtoService{

    private $pdo;
    private $reservationService;
    private $calendarMarkArrayService;
    private $roomMonthPriceService;
    private $yearMonthToDaysService;
    private $pricesCalendarService;
    private $getPlansDataService;
    private $maxGuest_OfRoomService;
    private $getRoomInformationService;
    private $weekDayService;
    private $maxCheckoutService;


 public function __construct($pdo){
        $this->pdo = $pdo;
        $this->reservationService = new ReservationService($pdo);
        $this->calendarMarkArrayService = new CalendarMarkArrayService();
        $this->roomMonthPriceService = new RoomMonthPriceService();
        $this->yearMonthToDaysService = new YearMonthToDaysService();
        $this->pricesCalendarService = new PricesCalendarService($pdo);
        $this->getPlansDataService = new GetPlansDataService($pdo);
        $this->maxGuest_OfRoomService = new MaxGuest_OfRoomService($pdo);
        $this->getRoomInformationService = new GetRoomInformationService($pdo);
        $this->weekDayService = new WeekDayService();
        $this->maxCheckoutService = new MaxCheckoutService($pdo);

 } 

 public function build(ReserveFormDto $dto):void{
        try{
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($dto->room_id);
            $dto->room_name = $room_information['room_name'];

            //プラン名をプランタイトルに変換する。
            $dto->plan_title = $this->getPlansDataService->getPlanTitle($dto->plan);
            //予約フォームを表示。カレンダーで選択した月日がチェックイン日となる。
            $dto->checkin_date = htmlspecialchars(sprintf('%04d-%02d-%02d', $dto->year, $dto->month, $dto->day));

            //部屋タイプにより制限人数。
            $dto->number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($dto->room_id);

            //宿泊日数のセレクトボックスの値を生成。
            $dto->maxStayNights = $this->reservationService->makeNumStayNights($dto->room_id, $dto->year, $dto->month, $dto->day);


            //在庫カレンダー最後尾の日付
            $dto->maxDate = $this->maxCheckoutService->getMaxCheckout();
            $dto->maxYear = $dto->maxDate['maxYear'];
            $dto->maxMonth = $dto->maxDate['maxMonth'];

            //戻るボタンで戻った時のセッション変数優先での、ビューへ与える変数。
            $stay_nights = intval($_SESSION['reserve_form']['stay_nights'] ?? 1);
            //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
            if ($stay_nights > count($dto->maxStayNights)) {
                $stay_nights = count($dto->maxStayNights);
            }
            $dto->stay_nights = htmlspecialchars($stay_nights);
            
            $dto->person = intval($_SESSION['reserve_form']['person'] ?? 1); //見積用と兼用
            $dto->user_name = $_SESSION['reserve_form']['user_name'] ?? '';
            $dto->user_telphone = $_SESSION['reserve_form']['user_telphone'] ?? '';
            $dto->user_address = $_SESSION['reserve_form']['user_address'] ?? '';
            $dto->email = $_SESSION['reserve_form']['email'] ?? '';
            $dto->comment = $_SESSION['reserve_form']['comment'] ?? '';
            $dto->checkout_date = date('Y-m-d', strtotime("{$dto->checkin_date} +{$dto->stay_nights} day"));

            //見積り計算
            $dto->estimate = $this->pricesCalendarService->getEstimate(
                $dto->room_id,
                $dto->plan,
                $dto->person,
                $dto->checkin_date,
                $dto->checkout_date
            );

        }catch (Exception $e) {
            unset($_SESSION['reserve_token']);
            unset($_SESSION['reserve_form']);
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
}
}
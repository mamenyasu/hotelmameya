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
require_once __DIR__ . '/../dto/ReserveDto.php';


class ReservationReconfirm_buildDtoService{

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

    public function build(ReserveDto $dto):void{

    try {
            //指定された種類の部屋の、指定月のデータを取得。
            $dto->estimate = $dto->total_price;
            $dto->number_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($dto->room_id);
            $dto->room_id = htmlspecialchars($dto->room_id, ENT_QUOTES, 'UTF-8');
            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($dto->room_id);
            $dto->room_name = $room_information['room_name'];
            $dto->user_name = htmlspecialchars($dto->user_name, ENT_QUOTES, 'UTF-8');
            $dto->user_telphone = htmlspecialchars($dto->user_telphone, ENT_QUOTES, 'UTF-8');
            $dto->user_address = htmlspecialchars($dto->user_address, ENT_QUOTES, 'UTF-8');
            $dto->email = htmlspecialchars($dto->email, ENT_QUOTES, 'UTF-8');
            $dto->comment = htmlspecialchars($dto->comment, ENT_QUOTES, 'UTF-8');
            $dto->checkin_date = htmlspecialchars($dto->checkin_date, ENT_QUOTES, 'UTF-8');
            $dto->checkout_date = htmlspecialchars($dto->checkout_date, ENT_QUOTES, 'UTF-8');
            $dto->total_price = htmlspecialchars($dto->total_price, ENT_QUOTES, 'UTF-8');
            $dto->plan = htmlspecialchars($dto->plan, ENT_QUOTES, 'UTF-8');
            //プラン名をプランタイトルに変換する。
            $dto->plan_title = $this->getPlansDataService->getPlanTitle($dto->plan);
            $dto->person = htmlspecialchars($dto->person, ENT_QUOTES, 'UTF-8');
            //宿泊日数のセレクトボックスの値を生成。
            $dto->year = date('Y', strtotime($dto->checkin_date));
            $dto->month = date('m', strtotime($dto->checkin_date));
            $dto->day = date('d', strtotime($dto->checkin_date));
            $dto->stay_nights = $dto->stay_nights;
            $dto->maxStayNights = $this->reservationService->makeNumStayNights($dto->room_id, $dto->year, $dto->month, $dto->day);
            //もし最新のmaxstayNightsから飛び出ていた場合にstayNightsを補正。
            if ($dto->stay_nights > count($dto->maxStayNights)) {
                $dto->stay_nights = count($dto->maxStayNights);
            }
            $dto->stay_nights = htmlspecialchars($dto->stay_nights);
            $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($dto->room_id, $dto->year, $dto->month);
            $dto->marks = $this->calendarMarkArrayService->getCalendarMarkArray($availabilityRoomMonth['availabilityRoomMonth']);
            //指定された種類の部屋の、指定月の値段表を取得。プランごとの多重配列。
            $dto->pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($dto->room_id, $dto->year, $dto->month);
            //月初～月末（例：１～３１）のdays配列。
            $dto->days = $this->yearMonthToDaysService->getDays($dto->year, $dto->month);
            //指定された部屋のプランデータを取得。見出しや内容など。
            $dto->plansData = $this->getPlansDataService->getPlansData();
            //初期表示用の、最初のプラン（0=１泊２食付きプラン）
            $dto->selectedPlan = $dto->plan;
            // 初期表示用の価格配列
            $dto->prices = $dto->pricesAllPlan[$dto->selectedPlan];
            //指定された部屋の人数制限。
            $dto->maxGuest_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($dto->room_id);
            //カレンダーで、指定月１日が何曜日から始まるか。0=日曜日～
            $dto->start_weekDay = $this->weekDayService->getStartWeekDay_From_Ym($dto->year, $dto->month);
            //カレンダー生成用、在庫カレンダー最後尾の日付
            $dto->maxDate = $this->maxCheckoutService->getMaxCheckout();
            $dto->maxYear = $dto->maxDate['maxYear'];
            $dto->maxMonth = $dto->maxDate['maxMonth'];

        } catch (Exception $e) {
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }

}
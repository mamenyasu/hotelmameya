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


class ReservationCalendar_buildDtoService{

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
            $availabilityRoomMonth = $this->reservationService->getAvailabilityRoomMonth($dto->room_id, $dto->year, $dto->month);
            if ($availabilityRoomMonth['success'] == false) {
                $message = $availabilityRoomMonth['message'];
                include __DIR__ . '/../views/false.php';
                exit();
            }


            //room_idを部屋の名前（日本語）に変換する。
            $room_information = $this->getRoomInformationService->getRoomInformation($dto->room_id);
            $dto->room_name = $room_information['room_name'];

            //mark配列生成。(指定された種類の部屋の、指定月の各日の空き具合（〇△×）)
            $dto->marks = $this->calendarMarkArrayService->getCalendarMarkArray($availabilityRoomMonth['availabilityRoomMonth']);

            //指定された種類の部屋の、指定月の値段表を取得。プランごとの多重配列。
            $dto->pricesAllPlan = $this->pricesCalendarService->getPricesAllPlan($dto->room_id, $dto->year, $dto->month);

            //月初～月末（例：１～３１）のdays配列。
            $dto->days= $this->yearMonthToDaysService->getDays($dto->year, $dto->month);

            //指定された部屋のプランデータを取得。見出しや内容など。
            $dto->plansData = $this->getPlansDataService->getPlansData();

            //初期表示用の、最初のプラン（0=１泊２食付きプラン）
            $dto->selectedPlan = $_SESSION['reserve_form']['selectedPlan'] ?? $dto->plansData[0]['plan_name'];

            // 初期表示用の価格配列
            $dto->prices = $dto->pricesAllPlan[$dto->selectedPlan];

            //指定された部屋の人数制限。
            $dto->maxGuest_OfRoom = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($dto->room_id);

            //カレンダーで、指定月１日が何曜日から始まるか。0=日曜日～
            $dto->start_weekDay = $this->weekDayService->getStartWeekDay_From_Ym($dto->year, $dto->month);

            //カレンダー生成用、在庫カレンダー最後尾の日付
            $maxDate = $this->maxCheckoutService->getMaxCheckout();
            $dto->maxDate = $maxDate;
            $dto->maxYear = $maxDate['maxYear'];
            $dto->maxMonth = $maxDate['maxMonth'];



        } catch (Exception $e) {
            $message = $e->getMessage();
            include __DIR__ . '/../views/false.php';
            exit();
        }
    }

}
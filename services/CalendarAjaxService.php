<?php

    require_once __DIR__.'/../services/GetPlansDataService.php';
    require_once __DIR__.'/../services/CalendarMarkArrayService.php';
    require_once __DIR__.'/../services/PricesCalendarService.php';
    require_once __DIR__.'/../services/YearMonthToDaysService.php';
    require_once __DIR__.'/../services/ReservationService.php';
    require_once __DIR__.'/../services/RestockService.php';
    require_once __DIR__.'/../services/WeekDayService.php';

    class CalendarAjaxService {

    private $getPlansDataService;
    private $calendarMarkArrayService;
    private $pricesCalendarService;
    private $yearMonthToDaysService;
    private $reservationService;
    private $restockService;
    private $weekDayService;

    public function __construct($pdo){
        $this->getPlansDataService = new GetPlansDataService($pdo);
        $this->calendarMarkArrayService = new CalendarMarkArrayService();
        $this->pricesCalendarService = new PricesCalendarService($pdo);
        $this->yearMonthToDaysService = new YearMonthToDaysService();
        $this->reservationService = new ReservationService($pdo);
        $this->restockService = new RestockService();
        $this->weekDayService = new WeekDayService();
    }

    public function getCalendarData($room_id, $plan, $year, $month){
        // ① プラン一覧 ＜修正；不要
        //$plans = $this->getPlansDataService->getPlansData();

        // ② 生の在庫データ（多重配列）
        $availability = $this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);

        if(!$availability['success']){
            return [
                'success' => false,
                'message' => '在庫データを取得できませんでした'
            ];
        }

        // ③ SESSION の旧予約情報を使って restock
        $restockedAvailability = $this->restockService->restock($availability);

        // ④ mark（〇△×）
        $marks = $this->calendarMarkArrayService
                 ->getCalendarMarkArray($restockedAvailability);

        // ⑤ 指定されたプランの価格カレンダー
        $prices = $this->pricesCalendarService
                   ->getPricesPlan($room_id, $plan, $year, $month);

        // ⑥ days（1〜月末）
        $days = $this->yearMonthToDaysService->getDays($year, $month);

        //⑦　start_weekDay(0が日曜日～6が土曜日)
        $start_weekDay = $this->weekDayService->getStartWeekDay_From_Ym($year,$month);

        return [
            'success' => true,
            'marks'    => $marks,
            'prices'  => $prices,
            'days'    => $days,
            'start_weekDay' => $start_weekDay
        ];

    }
}
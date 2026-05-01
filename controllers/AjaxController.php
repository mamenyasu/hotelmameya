<?php

require_once __DIR__ . '/../services/CalendarAjaxService.php';
require_once __DIR__ . '/../services/MaxGuest_OfRoomService.php';
require_once __DIR__ . '/../services/MaxCheckoutService.php';


    class AjaxController {

    private $calendarAjaxService;
    private $maxGuest_OfRoomService;
    private $maxCheckoutService;

    public function __construct($pdo){
        $this->calendarAjaxService = new CalendarAjaxService($pdo);
        $this->maxGuest_OfRoomService = new MaxGuest_OfRoomService();
        $this->maxCheckoutService = new MaxCheckoutService($pdo);
    }

    public function calendar($room_id, $plan, $year, $month){
        //在庫カレンダー最後尾の日付
        $maxDate=$this->maxCheckoutService->getMaxCheckout();
        $maxYear=$maxDate['maxYear'];
        $maxMonth=$maxDate['maxMonth'];
        $current = strtotime("$year-$month-01");
        $max     = strtotime($maxYear . '-' . $maxMonth . '-01');
        if ($current > $max) {
        echo json_encode(['error' => 'out_of_range']);
        exit;
        }

        $result = $this->calendarAjaxService->getCalendarData($room_id, $plan, $year, $month);
        echo json_encode($result);
        exit();
    }

    public function maxguest($room_id){
        $max = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($room_id);

        echo json_encode([
            'success' => true,
            'maxGuest' => $max,
        ]);
        exit();
    }
}
<?php
    class AjaxController {

    private $calendarAjaxService;
    private $maxGuest_OfRoomService;
    private $maxCheckoutService;

    public function __construct($pdo){
        $this->calendarAjaxService = new CalendarAjaxService($pdo);
        $this->maxGuest_OfRoomService = new MaxGuest_OfRoomService();
        $this->maxCheckoutService = new MaxCheckoutService($pdo);
    }

    public function calendar($room_id, $year, $month){
        //在庫カレンダー最後尾の日付
        $maxDate=$this->maxCheckoutService->getMaxCheckout();
        $maxYear=$maxDate['maxYear'];
        $maxMonth=$maxDate['maxMonth'];
        if ($year > $maxYear || ($year == $maxYear && $month > $maxMonth)) {
        echo json_encode(['error' => 'out_of_range']);
        exit;
        }

        $result = $this->calendarAjaxService->getCalendarData($room_id, $year, $month);
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
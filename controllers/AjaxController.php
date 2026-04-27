<?php
    class AjaxController {

    private $calendarAjaxService;
    private $maxGuest_OfRoomService;

    public function __construct($pdo){
        $this->calendarAjaxService = new CalendarAjaxService($pdo);
        $this->maxGuest_OfRoomService = new MaxGuest_OfRoomService();
    }

    public function calendar($room_id, $year, $month){
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
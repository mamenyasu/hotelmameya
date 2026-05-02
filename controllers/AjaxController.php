<?php

require_once __DIR__ . '/../services/CalendarAjaxService.php';
require_once __DIR__ . '/../services/MaxGuest_OfRoomService.php';
require_once __DIR__ . '/../services/MaxCheckoutService.php';
require_once __DIR__ . '/../services/PricesCalendarService.php';


class AjaxController
{

    private $calendarAjaxService;
    private $maxGuest_OfRoomService;
    private $maxCheckoutService;
    private $pricesCalendarService;

    public function __construct($pdo)
    {
        $this->calendarAjaxService = new CalendarAjaxService($pdo);
        $this->maxGuest_OfRoomService = new MaxGuest_OfRoomService();
        $this->maxCheckoutService = new MaxCheckoutService($pdo);
        $this->pricesCalendarService = new PricesCalendarService($pdo);
    }

    public function calendar($room_id, $plan, $year, $month)
    {
        //在庫カレンダー最後尾の日付
        $maxDate = $this->maxCheckoutService->getMaxCheckout();
        $maxYear = $maxDate['maxYear'];
        $maxMonth = $maxDate['maxMonth'];
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

    public function maxguest($room_id)
    {
        $max = $this->maxGuest_OfRoomService->getMaxGuest_OfRoom($room_id);

        echo json_encode([
            'success' => true,
            'maxGuest' => $max,
        ]);
        exit();
    }

    //AJAX用見積計算。GETを使う。新規予約フォームとか。
    public function estimate()
    {

        $room_id = $_GET['room_id'] ?? null;
        $plan = $_GET['plan'] ?? null;
        $person = $_GET['person'] ?? null;
        $checkin_date = $_GET['checkin_date'] ?? null;
        $checkout_date = $_GET['checkout_date'] ?? null;

        if (!$room_id || !$plan || !$person || !$checkin_date || !$checkout_date) {
            echo json_encode(['success' => false, 'message' => 'パラメータ不足']);
            exit;
        }

        $estimate = $this->pricesCalendarService->getEstimate(
            $room_id,
            $plan,
            $person,
            $checkin_date,
            $checkout_date
        );

        echo json_encode([
            'success' => true,
            'estimate' => $estimate
        ]);
        exit;
    }
    
}

<?php

require_once __DIR__.'/../services/CalendarMarkArrayService';
require_once __DIR__.'/../services/ReservationService.php';
require_once __DIR__.'/../services/RestockService.php';
require_once __DIR__.'/../services/RoomMonthPriceService.php';


class CalendarController{
//!!--プロパティ---------
    private $pdo;
    private $reservationService;
    private $calendarMarkArrayService;
    private $roomMonthPriceService;
    private $restockService;
    private $yearMonthToDaysService;

//!!--コンストラクタ---------
    public function __construct($pdo){
        $this->pdo=$pdo;
        $this->reservationService = new ReservationService($pdo);
        $this->calendarMarkArrayService = new CalendarMarkArrayService();
        $this->roomMonthPriceService = new RoomMonthPriceService();
        $this->restockService = new RestockService();
        $this->yearMonthToDaysService= new YearMonthToDaysService();
    }

///ルータースイッチデフォルト用。----------
    public function index(){
        include __DIR__.'/../views/index.php';
    }

////カレンダー生成に必要なデータを返すメソッド。AJAX。予約変更ロジック専用。
    public function getCalendarData($room_id, $year, $month){
        try{
            //指定された種類の部屋の、指定年月のデータを取得。各日それぞれの値段も、この配列に入っている。
            $availabilityRoomMonth=$this->reservationService->getAvailabilityRoomMonth($room_id, $year, $month);
            if($availabilityRoomMonth['success']==false){
                $message=$availabilityRoomMonth['message'];
                include __DIR__.'/../views/false.php';
                exit();
            }

            if($room_id==$_SESSION['reserve_update_old']['room_id']){
            //セレクトボックスで選んだ部屋の種類が旧予約の部屋種と一致している場合のみ、在庫を一時的に戻す。戻り値は、'success'判定がなくなり普通配列に。
            $availabilityRoomMonth=$this->restockService->restock($availabilityRoomMonth);
            }

            //１～月末日まで、〇△×に変換。
            $markArray=$this->calendarMarkArrayService->getCalendarMarkArray($availabilityRoomMonth);
            //値段データも取得。 １～月末日まで。
            $prices=$this->roomMonthPriceService->getRoomMonthPrice($availabilityRoomMonth['availabilityRoomMonth']);
            $days=$this->yearMonthToDaysService->getDays($year,$month);

            //JSONは連想配列で。
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['days'=>$days, 'markArray'=>$markArray, 'prices'=>$prices]);
            exit();

        }catch(Exception $e){
            echo $e->getMessage();
            exit();
        }
    }



}
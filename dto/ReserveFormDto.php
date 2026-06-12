<?php
class ReserveFormDto {

    // --- 基本情報 ---
    public $room_id;
    public $year;
    public $month;
    public $day;
    public $plan;

    // --- 部屋・プラン情報 ---
    public $room_name;
    public $plan_title;
    public $number_OfRoom;      // 最大人数
    public $maxGuest_OfRoom;    // 部屋タイプの最大人数

    // --- 日付関連 ---
    public $checkin_date;
    public $checkout_date;
    public $stay_nights;
    public $maxStayNights;
    public $maxDate;
    public $maxYear;
    public $maxMonth;
    public $start_weekDay;

    // --- カレンダー関連 ---
    public $marks;
    public $days;
    public $pricesAllPlan;
    public $plansData;
    public $selectedPlan;
    public $prices;

    // --- ユーザー入力 ---
    public $person;
    public $user_name;
    public $user_telphone;
    public $user_address;
    public $email;
    public $comment;

    // --- 見積り ---
    public $estimate;
    public $total_price;

    // --- エラー（差し戻し用） ---
    public $error;

    public function __construct($room_id = null, $year =null , $month = null, $day = null, $plan = null)
    {
        $this->room_id = $room_id;
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->plan = $plan;
    }

    public function toViewData(): array
    {
        return [
            'room_id' => $this->room_id,
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'plan' => $this->plan,

            'room_name' => $this->room_name,
            'plan_title' => $this->plan_title,
            'number_OfRoom' => $this->number_OfRoom,
            'maxGuest_OfRoom' => $this->maxGuest_OfRoom,

            'checkin_date' => $this->checkin_date,
            'checkout_date' => $this->checkout_date,
            'stay_nights' => $this->stay_nights,
            'maxStayNights' => $this->maxStayNights,
            'maxDate' => $this->maxDate,
            'maxYear' => $this->maxYear,
            'maxMonth' => $this->maxMonth,
            'start_weekDay' => $this->start_weekDay,

            'marks' => $this->marks,
            'days' => $this->days,
            'pricesAllPlan' => $this->pricesAllPlan,
            'plansData' => $this->plansData,
            'selectedPlan' => $this->selectedPlan,
            'prices' => $this->prices,

            'person' => $this->person,
            'user_name' => $this->user_name,
            'user_telphone' => $this->user_telphone,
            'user_address' => $this->user_address,
            'email' => $this->email,
            'comment' => $this->comment,

            'estimate' => $this->estimate,
            'total_price' => $this->total_price,

            'error' => $this->error,
        ];
    }
}

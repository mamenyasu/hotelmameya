<?php
class ReserveFormDto{
    
            public $room_id; 
            public $year;
            public $month;
            public $day;
            public $plan;

            public $room_name;
            public $plan_title;
            public $checkin_date;
            public $number_OfRoom;
            public $maxStayNights;

            public $maxDate;
            public $maxYear;
            public $maxMonth;

            public $stay_nights;
            public $person;
            public $user_name;
            public $user_telphone;
            public $user_address;
            public $email;
            public $comment;
            public $checkout_date;

            public $estimate;


            public function __construct($room_id,$year,$month,$day,$plan){
                $this->room_id = $room_id;
                $this->year = $year;
                $this->month = $month;
                $this->day = $day;
                $this->plan = $plan;
            }

            public function toViewData():array{
                return[
                'room_id' => $this->room_id,
                'year' => $this->year,
                'month' => $this->month,
                'day' => $this->day,
                'plan' => $this->plan,

                'room_name' => $this->room_name,
                'plan_title'=> $this->plan_title,
                '$checkin_date' => $this->checkin_date,
                'number_OfRoom' => $this->number_OfRoom,
                'maxStayNights' => $this->maxStayNights,
                'maxDate' => $this->maxDate,
                'maxYear' => $this ->maxYear,
                'maxMonth' => $this ->maxMonth,

                'stay_nights' => $this->stay_nights,
                'person' => $this->person,
                'user_name' => $this->user_name,
                'user_telphone' => $this->user_telphone,
                'user_address' => $this->user_address,
                'email' => $this->email,
                'comment' => $this->comment,
                'checkout_date' => $this->checkout_date,

                'estimate' => $this->estimate,
                ];
            }

}
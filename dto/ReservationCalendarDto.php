<?php
class ReservationCalendarDto{

            public $room_id; 
            public $year;
            public $month;

            //部屋の名前（日本語）
            public $room_name;
            //mark配列。(指定された種類の部屋の、指定月の各日の空き具合（〇△×）)
            public $marks;
            //指定された種類の部屋の、指定月の値段表。プランごとの多重配列。
            public $pricesAllPlan;
            //月初～月末（例：１～３１）のdays配列。
            public $days;
            //指定された部屋のプランデータ。見出しや内容など。
            public $plansData;
            //初期表示用の、最初のプラン（0=１泊２食付きプラン）
            public $selectedPlan;
            // 初期表示用の価格配列
            public $prices;
            //指定された部屋の人数制限。
            public $maxGuest_OfRoom;
            //カレンダーで、指定月１日が何曜日から始まるか。0=日曜日～
            public $start_weekDay;
            //カレンダー生成用、在庫カレンダー最後尾の日付
            public $maxDate;
            public $maxYear;
            public $maxMonth;

 public function __construct($room_id,$year,$month){
            $this->room_id = $room_id ?? 1;
            $this->year = $year ?? date('Y');
            $this->month = $month ?? date('m');
 }

 public function toViewData():array{
    return [
      'room_id' => $this->room_id,
      'year' => $this->year,
      'month' => $this->month,

      'room_name' => $this->room_name,     
      'marks' => $this->marks ?? null,
      'pricesAllPlan' => $this->pricesAllPlan ?? null,
      'days' => $this->days ?? null,
      'plansData' => $this->plansData ?? null,
      'selectedPlan' => $this->selectedPlan ?? null,
      'prices' => $this->prices ?? null,
      'maxGuest_OfRoom' => $this->maxGuest_OfRoom ?? null,
      'start_weekDay' => $this->start_weekDay,
      'maxDate' => $this->maxDate,
      'maxYear' => $this->maxYear,
      'maxMonth' => $this->maxMonth        
    ];
 }

}
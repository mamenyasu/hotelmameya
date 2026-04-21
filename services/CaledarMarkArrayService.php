<?php
class CalendarMarkArray{

    public function getCalendarMarkArray($availability){

        $days=count($availability);
        $calendarMarkArray=[];

        for($i=0; $i<$days; $i++){
            $row=$availability[$i];
            $booked=$row['booked_rooms']; //予約済みの部屋の数
            $remaining=10-$booked; //残り部屋数

            if($remaining > 3){
                $mark='〇';
            }else if($remaining >= 1){
                $mark='△';
            }else{
                $mark='×';
            }

            $calendarMarkArray[$i+1]=$mark;

        }
        return $calendarMarkArray; //指定した一か月分の〇△×を配列で返す。[1]日～[月末]日まで。

    }
}
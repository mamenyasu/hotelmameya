<?php
class RoomMonthPriceService{
    public function getRoomMonthPrice($availabilityRoomMonth){
        $price=[];
            foreach($availabilityRoomMonth as $data){
            $day=(int)date('j',strtotime($data['stay_date']));   //ASC、DESC、両対応。
            $price[$day]=$data['price'];
            }
        return $price;
    }
}
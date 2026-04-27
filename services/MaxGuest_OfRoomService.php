<?php
    class MaxGuest_OfRoomService{

    public function getMaxGuest_OfRoom($room_id){
        switch (intval($room_id)){
            case 1 : $maxGuest_OfRoom=2; break;
            case 2 : $maxGuest_OfRoom=3; break;
            case 3 : $maxGuest_OfRoom=3; break;
            default : $maxGuest_OfRoom=3; break;
        }

        return $maxGuest_OfRoom;

    }
}
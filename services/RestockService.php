<?php
class RestockService{
    public function restock($availabilityRoomMonth){
        $old_checkindate=$_SESSION['reserve_update_old']['checkin_date'];
        $old_checkoutdate=$_SESSION['reserve_update_old']['checkout_date'];

        $startunix=strtotime($old_checkindate);
        $endunix=strtotime($old_checkoutdate);

        for($d=$startunix; $d<$endunix; $d=strtotime('+1 day',$d)){    // $dはunix。unix単位で回す。チェックアウト日は含まれない。
            $date=date('Y-m-d', $d);   // 照合のため、$dをY-m-dに戻す。
            foreach($availabilityRoomMonth['availabilityRoomMonth'] as &$dayData){ // 一か月分、日を回して配列をリファレンス渡し。
                if($dayData['stay_date'] == $date){
                    $dayData['booked_rooms']--;
                }
            }
        }
        return $availabilityRoomMonth['availabilityRoomMonth']; //旧予約日の在庫を戻し、success判定を除いた配列だけを戻す。
    }

}
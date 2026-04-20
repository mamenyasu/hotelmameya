<?php
class ReservationService{
    private $reservationsModel;
    private $roomAvailabilityModel;

    public function __construct($pdo){
        $this->reservationsModel=new ReservationsModel($pdo);
        $this->roomAvailabilityModel=new RoomAvailabilityModel($pdo);
    }

    //予約操作。戻り値として結果の連想配列を受け取る（もしくは$eを受け取る)。
    public function reserve($request){
        try{
             //操作直前に本当に空きがあるか再確認。
            $stock=$this->roomAvailabilityModel->hasStock($request);
            if(!$stock){
                return ['success'=>false,'messeage'=>'空きがありません。'];
            }
            //予約テーブルに登録。
            $this->reservationsModel->createReservation($request);
            //部屋（在庫）を減らす。
            $this->roomAvailabilityModel->decreaseBookedRooms($request);
            //結果をコントローラーに返す。
            return ['success'=>true,'message'=>'予約が完了しました。'];
        }catch(Exception $e){
            throw $e;
        }
    }

    //予約キャンセル操作。戻り値として結果の連想配列を受けとる（もしくは$eを受け取る）。
    public function cancel($request){
        try{
            //キャンセル予定の予約が実在するか確認する。予約IDから既予約情報(DBの連想配列)が返ってくる。
            $reservation=$this->reservationsModel->getReservationById($request);
            if(!$reservation){
                return ['success'=>false, 'message'=>'予約が存在しません。'];
            }
            //予約取り消し操作。
            $this->reservationsModel->deleteReservation($reservation);
            //在庫を復活させる。
            $this->roomAvailabilityModel->increaseBookedRooms($reservation);
            //結果をコントローラーに返す。
            return ['success'=>true,'messeage'=>'予約がキャンセルされました。'];            
        }catch(Exception $e){
            throw $e;
        }
    }

    //予約変更操作。戻り値として結果の連想配列を受け取る（もしくは$eを受け取る）。
    //注意。順番が大事。(IDは新旧共通なので)まず古いデータを引っ張っておく（有無も確認する）。
    //DB書き込み直前に空きがあるか再確認しておく。その後、在庫を回復させてから、予約データを上書きし、在庫をあらたに減らす。
    public function update($request){
        try{
        $old=$this->reservationsModel->getReservationById($request);
        if(!$old){
            return ['success'=>false, 'message'=>'予約が存在しません。'];
        }
        $stock=$this->roomAvailabilityModel->hasStock($request);
        if(!$stock){
            return ['success'=>false,'messeage'=>'空きがありません。'];
        }
        $this->roomAvailabilityModel->increaseBookedRooms($old);
        $this->reservationsModel->updateReservation($request);
        $this->roomAvailabilityModel->decreaseBookedRooms($request);
        return ['success'=>true,'message'=>'予約が変更されました。'];
    }catch(Exception $e){
        throw $e;
    }
    }

    //照合して既予約情報を返す操作。予約IDと電話番号を元に照合。戻り値として結果の連想配列を受け取る（もしくは$eを受け取る）。
    public function showReservation($request){
        try{
            $reservation=$this->reservationsModel->getReservationById($request);
            if(!$reservation){
                return ['success'=>false, 'message'=>'予約が存在しません。'];
            }
            if($reservation['user_telphone']==$request['user_telphone']){
                return ['success'=>true, 'reservation'=>$reservation];
            }else{
                return ['success'=>false, 'message'=>'予約IDと電話番号が一致しません'];
            }
        }catch(Exception $e){
            throw $e;
        }
    }


}
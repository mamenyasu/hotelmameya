<?php

require_once __DIR__.'/../models/ReservationsModel.php';
require_once __DIR__.'/../models/RoomAvailabilityModel.php';

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
            //予約取り消し操作。
            $this->reservationsModel->deleteReservation($request);
            //在庫を復活させる。
            $this->roomAvailabilityModel->increaseBookedRooms($request);
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

    //照合して既予約情報を返す操作。予約IDとメールアドレスを元に照合。戻り値として結果の連想配列を返す（もしくは$eを返す）。
    public function showReservation($request){
        try{
            $reservation=$this->reservationsModel->getReservationById($request);
            if(!$reservation){
                return ['success'=>false, 'message'=>'予約が存在しません。'];
            }elseif($reservation['email']==$request['email']){
                return ['success'=>true, 'reservation'=>$reservation];
            }else{
                return ['success'=>false, 'message'=>'予約IDとメールアドレスが一致しません'];
            }
        }catch(Exception $e){
            throw $e;
        }
    }

    //指定した種類の部屋の、一か月の在庫状況を返す操作。
    public function getAvailabilityRoomMonth($room_id,$year,$month){
        try{
            $availabilityRoomMonth=$this->roomAvailabilityModel->getAvailabilityMonth($room_id,$year,$month);
            if(!$availabilityRoomMonth){
                return ['success'=>false, 'message'=>'指定された部屋の月在庫を取得できませんでした。'];
            }
            return ['success'=>true, 'availabilityRoomMonth'=>$availabilityRoomMonth];
        }catch(Exception $e){
            throw $e;
        }
    }

    //指定の種類の部屋に、指定の期間で、空きがあるかの情報をコントローラーに渡すメソッド。
    public function hasStock($request){
        try{
        $result=$this->roomAvailabilityModel->hasStock($request);
            if($result){
                return ['success'=>true];
            }else{
                return ['success'=>false,'message'=>'空きがありません。'];
            }
        }catch(Exception $e){
            throw $e;
        }
    }

    //カレンダーで選択した日が、最低でも当日一泊出来るか確認するメソッド。
    public function hasStockOne($room_id,$year,$month,$day){
        try{
            $request['room_id']=$room_id;
            $request['checkin_date']=sprintf('%04d-%02d-%02d',$year,$month,$day);
            $request['checkput_date']=date('Y-m-d',strtotime('+1 days',strtotime(sprintf('%04d-%02d-%02d',$year,$month,$day))));
            $result=$this->roomAvailabilityModel->hasStock($request);
            if($result){
                return ['success'=>true];
            }else{
                return ['success'=>false,'message'=>'空きがありません。'];
            }
        }catch(Exception $e){
            throw $e;
        }
    }


}
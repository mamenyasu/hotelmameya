<?php

require_once __DIR__.'/../models/ReservationsModel.php';
require_once __DIR__.'/../models/RoomAvailabilityModel.php';
require_once __DIR__.'/../models/RoomModel.php';
require_once __DIR__.'/../services/RestockService.php';

class ReservationService{
    private $pdo;
    private $reservationsModel;
    private $roomAvailabilityModel;
    private $roomModel;
    private $restockService;

    public function __construct($pdo){
        $this->pdo=$pdo;
        $this->reservationsModel=new ReservationsModel($pdo);
        $this->roomAvailabilityModel=new RoomAvailabilityModel($pdo);
        $this->roomModel = new RoomModel($pdo);
        $this->restockService = new RestockService();
    }



    //予約操作。戻り値として結果の連想配列を受け取る（もしくは$eを受け取る)。
    public function reserve($request){
        try{
            //操作直前に本当に空きがあるか再確認。
            $rows=$this->roomAvailabilityModel->getRoomBetweenData($request);
            $room_information=$this->roomModel->getRoomInformation($request['room_id']);
            if(!$rows){
                return ['success'=>false,'message'=>'指定された期間の空き状況が確認できませんでした。'];
            }
            foreach($rows as $row){
                if($room_information['total_inventory'] <= $row['booked_rooms']){
                return ['success'=>false,'message'=>'空きがありません。'];
                }
            }

            //予約テーブルに登録。
            $this->reservationsModel->createReservation($request);
            //予約IDを取得。
            $reservationId=$this->pdo->lastInsertId();
            //部屋（在庫）を減らす。=予約数を＋１する。
            $this->roomAvailabilityModel->increaseBookedRooms($request);
            //結果をコントローラーに返す。
            return ['success'=>true,'message'=>"予約が完了しました。\n予約IDは".$reservationId.'です。'];
        }catch(Exception $e){
            throw $e;
        }
    }

    //予約キャンセル操作。戻り値として結果の連想配列を受けとる（もしくは$eを受け取る）。
    public function cancel($request){
        try{
            //予約取り消し操作。
            $this->reservationsModel->deleteReservation($request);
            //在庫を復活させる。＝予約数を減らす
            $this->roomAvailabilityModel->decreaseBookedRooms($request);
            //結果をコントローラーに返す。
            return ['success'=>true,'message'=>'予約がキャンセルされました。'];            
        }catch(Exception $e){
            throw $e;
        }
    }

    //予約変更操作。戻り値として結果の連想配列を受け取る（もしくは$eを受け取る）。
    //注意。順番が大事。(IDは新旧共通なので)まず古いデータを引っ張っておく（有無も確認する）。
    //DB書き込み直前に空きがあるか再確認しておく。その後、在庫を回復させてから、予約データを上書きし、在庫をあらたに減らす。
    public function update($request){
        try{
            //古いデータを取得。有無も確認。
            $old=$this->reservationsModel->getReservationById($request);
            if(!$old){
                return ['success'=>false, 'message'=>'予約が存在しません。'];
            }
            
            //新たに指定された部屋の種類と期間で、空きがあるか確認。
            $rows=$this->roomAvailabilityModel->getRoomBetweenData($request);
            $room_information=$this->roomModel->getRoomInformation($request['room_id']);
            if(!$rows){
                return ['success'=>false,'message'=>'指定された期間の空き状況が確認できませんでした。'];
            }
            foreach($rows as $row){
                if($room_information['total_inventory'] <= $row['booked_rooms']){
                return ['success'=>false,'message'=>'空きがありません。'];
                }
            }

            //トランザクション処理開始。
            $this->pdo->beginTransaction();
            $this->roomAvailabilityModel->decreaseBookedRooms($old);
            $this->reservationsModel->updateReservation($request);
            $this->roomAvailabilityModel->increaseBookedRooms($request);
            $this->pdo->commit(); //成功時はコミット。
                return ['success'=>true,'message'=>'予約が変更されました。'];
        }catch(Exception $e){
            $this->pdo->rollBack(); //失敗時はロールバック。
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
                return ['success'=>false, 'message'=>'指定された部屋の月在庫を取得できませんでした。']; //SUCCESS判定は、空配列が返ってきた場合の処理用。
            }
            return ['success'=>true, 'availabilityRoomMonth'=>$availabilityRoomMonth];
        }catch(Exception $e){
            throw $e;
        }
    }

    //指定の種類の部屋に、指定の期間で、空きがあるかをコントローラーに渡すメソッド。
    public function hasStock($request){
        try{
        $room_information=$this->roomModel->getRoomInformation($request['room_id']);
        $rows=$this->roomAvailabilityModel->getRoomBetweenData($request);
        if(!$rows){
            return ['success'=>false,'message'=>'指定された期間の空き状況が確認できませんでした。'];
        }
        foreach($rows as $row){
            if($room_information['total_inventory'] <= $row['booked_rooms']){
                return ['success'=>false,'message'=>'空きがありません。'];
            }
        }
        return ['success'=>true];
        }catch(Exception $e){
            throw $e;
        }
    }



    //カレンダーで選択した日が、最低でも当日一泊出来るか確認するメソッド。
    public function hasStockOne($room_id,$year,$month,$day){
        try{
            $room_information=$this->roomModel->getRoomInformation($room_id);
            $request['room_id']=$room_id;
            $request['checkin_date']=sprintf('%04d-%02d-%02d',$year,$month,$day);
            $request['checkout_date']=date('Y-m-d',strtotime('+1 days',strtotime(sprintf('%04d-%02d-%02d',$year,$month,$day))));
            $rows=$this->roomAvailabilityModel->getRoomBetweenData($request);
            if(!$rows){
                return ['success'=>false,'message'=>'空き状況が確認できませんでした。'];
            }
            foreach($rows as $row){
                if($room_information['total_inventory'] <= $row['booked_rooms']){
                return ['success'=>false,'message'=>'空きがありません。'];
                }
            }
            return ['success'=>true];
        }catch(Exception $e){
            throw $e;
        }
    }

//指定したチェックイン日から宿泊可能な日数を返す操作。宿泊日数セレクトボックス用。maxStaiNights=[１泊,２泊,...]みたいな配列が返る。
public function makeNumStayNights($room_id,$year,$month,$day){
    
    $maxStayNights=[];
    $room_information=$this->roomModel->getRoomInformation($room_id);
    $availabilityTwoWeek=$this->roomAvailabilityModel->getAvailabilityTwoWeek($room_id,$year,$month,$day);
    //サービスを挟んでいないため、いったんSUCESSキーとavailabilityRoomMonthキーを追加。
    $availability=['SUCCESS' => true,'availabilityRoomMonth' => $availabilityTwoWeek];

    //予約変更の場合はリストックされ、そうではない場合（新規予約の場合）は何もなく、結果としてSUCCESS判定を除いて返ってくる。
    $availability=$this->restockService->restock($availability);

    for($i=0; $i<14; $i++){
        if (!isset($availability[$i])) {
            break; // データがない＝泊まれない
        } 
        $total_inventory=$room_information['total_inventory'];
        $booked_rooms=$availability[$i]['booked_rooms'];
        if($booked_rooms >= $total_inventory){
            break;
        }else{
            $maxStayNights[]=$i+1;
        }
    }
    return $maxStayNights; //もしmaxStayNightsが空である（一泊もできない）場合、ビュー側で判定し、「部屋の空きがありません」みたいなメッセージを出す予定。
}


}
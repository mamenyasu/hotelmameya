<?php
class RoomAvailabilityModel{

    //コンストラクタ（PDOをもらう）
    private PDO $pdo;
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }


    //指定した種類の部屋の、一か月分の在庫データの配列を、昇順で返すメソッド。
    public function getAvailabilityMonth($room_id,$year,$month){
    try{
        $startYearMonth=sprintf('%04d-%02d-01',$year,$month);
        $endYearMonth=date('Y-m-d',strtotime("$startYearMonth +1 Month")); //シングルクォートで囲まないように。変数展開されません。

        $stmt=$this->pdo->prepare("SELECT * FROM room_availability WHERE room_id=:room_id AND stay_date >= :startYearMonth AND stay_date < :endYearMonth ORDER BY stay_date ASC");
        $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
        $stmt->bindValue(':startYearMonth',$startYearMonth,PDO::PARAM_STR);
        $stmt->bindValue(':endYearMonth',$endYearMonth,PDO::PARAM_STR);
        $stmt->execute();
        }catch(Exception $e){
            throw new Exception('データベースエラー：指定の部屋の月在庫データを取得できませんでした');
        }

        $availablity=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $availablity; //指定した種類の部屋の、一か月分の在庫データを配列で返す。

    }

    //指定の種類の部屋の、指定の期間について、booked_rooms（予約数）を＋１するメソッド。=在庫を減らす
    public function increaseBookedRooms($request){
        try{
        $checkin_date=$request['checkin_date'];
        $checkout_date=$request['checkout_date'];

        $stmt=$this->pdo->prepare('UPDATE room_availability SET booked_rooms = booked_rooms +1 WHERE room_id=:room_id AND stay_date >= :checkin_date AND stay_date < :checkout_date');
        $stmt->bindValue(':room_id',$request['room_id'],PDO::PARAM_INT);
        $stmt->bindValue(':checkin_date',$checkin_date,PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$checkout_date,PDO::PARAM_STR);
        $stmt->execute();
        }catch(Exception $e){
            throw new Exception('データベースエラー：在庫を加算できませんでした');    
        }
    }

    //指定の種類の部屋の、指定の期間について、booked_rooms(予約数)をー１するメソッド。=在庫を増やす
    public function decreaseBookedRooms($request){
        try{
        $stmt=$this->pdo->prepare('UPDATE room_availability SET booked_rooms = booked_rooms -1 WHERE room_id=:room_id AND stay_date >= :checkin_date AND stay_date < :checkout_date');
        $stmt->bindValue(':room_id',$request['room_id'],PDO::PARAM_INT);
        $stmt->bindValue(':checkin_date',$request['checkin_date'],PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$request['checkout_date'],PDO::PARAM_STR);
        $stmt->execute();
        }catch(Exception $e){
            throw new Exception('データベースエラー：在庫を減算できませんでした');
        }
    }
    
    //指定の種類の部屋に、指定の期間のデータを返すメソッド。
    public function getRoomBetweenData($request){
        try{
        $stmt=$this->pdo->prepare('SELECT booked_rooms,total_rooms FROM room_availability WHERE room_id=:room_id AND stay_date >=:checkin_date AND stay_date < :checkout_date');
        $stmt->bindValue(':room_id',$request['room_id'],PDO::PARAM_INT);
        $stmt->bindValue(':checkin_date',$request['checkin_date'],PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$request['checkout_date'],PDO::PARAM_STR);
        $stmt->execute();
        $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
        }catch(Exception $e){
            throw new Exception('データベースエラー：データベース確認に失敗しました');
        }
    }

    //指定の種類の部屋について、指定の期間の料金データを返すメソッド。
    public function getRoomBetweenPriceData($request){
        try{
        $stmt=$this->pdo->prepare('SELECT price FROM room_availability WHERE room_id=:room_id AND stay_date >=:checkin_date AND stay_date < :checkout_date');
        $stmt->bindValue(':room_id',$request['room_id'],PDO::PARAM_INT);
        $stmt->bindValue(':checkin_date',$request['checkin_date'],PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$request['checkout_date'],PDO::PARAM_STR);
        $stmt->execute();
        $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
        }catch(Exception $e){
            throw new Exception('データベースエラー：データベース確認に失敗しました');
        }
    }


    //最後尾のチェックアウト日を返すメソッド。
    public function getMaxCheckout(){
        try{
        $stmt=$this->pdo->prepare('SELECT MAX(stay_date) FROM room_availability');
        $stmt->execute();
        $maxCheckoutDate=$stmt->fetch(PDO::FETCH_COLUMN);
        return $maxCheckoutDate;
        }catch(Exception $e){
            throw new Exception('データベースエラー：部屋在庫カレンダーのデータ取得に失敗しました');
        }
    }

}
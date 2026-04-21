<?php
class RoomAvailabilityModel{

    //コンストラクタ（PDOをもらう）
    private PDO $pdo;
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }


    //指定した種類の部屋の、一か月分の在庫データの配列を、昇順で返すメソッド。
    public function getAvailabilityMonth(int $room_id,int $year,int $month){
        //せっかくインデックスを設定しているので、速度改善のためSQLでLIKEは使わない。
    try{
        $startYearMonth=sprintf('%04d-%02d-01',$year,$month);
        $endYearMonth=date('Y-m-d',strtotime("$startYearMonth +1 Month")); //シングルクォートで囲まないように。変数展開されません。

        $stmt=$this->pdo->prepare("SELECT * FROM room_availability WHERE room_id=:room_id AND stay_date >= :startYearMonth AND stay_date <:endYearMonth ORDER BY stay_date ASC");
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

    //指定の種類の部屋の、指定の期間について、booked_rooms（予約数）を＋１するメソッド。
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

    //指定の種類の部屋の、指定の期間について、booked_rooms(予約数)をー１するメソッド。
    public function decreaseBookedRooms($request){
        try{
        $checkin_date=$request['checkin_date'];
        $checkout_date=$request['checkout_date'];

        $stmt=$this->pdo->prepare('UPDATE room_availability SET booked_rooms = booked_rooms -1 WHERE room_id=:room_id AND stay_date >= :checkin_date AND stay_date < :checkout_date');
        $stmt->bindValue(':room_id',$request['room_id'],PDO::PARAM_INT);
        $stmt->bindValue(':checkin_date',$checkin_date,PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$checkout_date,PDO::PARAM_STR);
        $stmt->execute();
        }catch(Exception $e){
            throw new Exception('データベースエラー：在庫を減算できませんでした');
        }
    }
    
    //指定の種類の部屋に、指定の期間で、空きがあるか確認するメソッド。trueかfalseを返す。
    public function hasStock($request){
        try{
        $stmt=$this->pdo->prepare('SELECT booked_rooms,total_rooms FROM room_availability WHERE room_id=:room_id AND stay_date >=:checkin_date AND stay_date < :checkout_date');
        $stmt->bindValue(':room_id',$request['room_id'],PDO::PARAM_INT);
        $stmt->bindValue(':checkin_date',$request['checkin_date'],PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$request['checkout_date'],PDO::PARAM_STR);
        $stmt->execute();

        $found=false; //万が一、該当データが無く(10年先とか変なPOSTされた場合)、whileに入れなかった場合はfalseを返す為の変数。
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $found=true;
            if($row['total_rooms']<=$row['booked_rooms']){
                return false;
            }
        }
        return $found; //trueかfalseが返る。
        }catch(Exception $e){
            throw new Exception('データベースエラー：空き室状況の確認に失敗しました');
        }
    }
}
<?php
class ReservationsModel{
    private $pdo;

    //コンストラクタ（PDOをもらう）
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    //予約IDから予約情報を取得するメソッド。
    public function getReservationById($request){
        $reservation_id=$request['id'];
        try{
        $stmt=$this->pdo->prepare('SELECT * FROM reservations WHERE id=:id');
        $stmt->bindValue(':id',$reservation_id,PDO::PARAM_INT);
        $stmt->execute();
        $reservation=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!$reservation){
            throw new Exception('予約が見つかりません');
        }
        return $reservation;
        }catch(Exception $e){
            throw new Exception('データベースエラー：予約情報を取得できませんでした');
        }
    }

    //予約追加メソッド。個人の予約データをreservationsテーブルにINSERTする。
    public function createReservation($request){
        try{
        $stmt=$this->pdo->prepare('INSERT INTO reservations (room_id,user_name,user_telphone,comment,checkin_date,checkout_date) VALUES (:room_id,:user_name,:user_telphone,:comment,:checkin_date,:checkout_date)');
        $stmt->bindValue(':room_id',$request['room_id'],PDO::PARAM_INT);
        $stmt->bindValue('user_name',$request['user_name'],PDO::PARAM_STR);
        $stmt->bindValue(':user_telphone',$request['user_telphone'],PDO::PARAM_STR);
        $stmt->bindValue(':comment',$request['comment'],PDO::PARAM_STR);
        $stmt->bindValue(':checkin_date',$request['checkin_date'],PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$request['checkout_date'],PDO::PARAM_STR);
        $stmt->execute();
        }catch(Exception $e){
            throw new Exception('データベースエラー：予約の登録に失敗しました');
        }
    }

    //予約取り消しメソッド。
    public function deleteReservation($request){
        try{
        $stmt=$this->pdo->prepare('DELETE FROM reservations WHERE id=:id');
        $stmt->bindValue(':id',$request['id']);
        $stmt->execute();
        }catch(Exception $e){
            throw new Exception('データベースエラー：予約の取り消しに失敗しました');
        }
    }

    //予約変更（上書き）メソッド。
    public function updateReservation($request){
        try{
        $stmt=$this->pdo->prepare('UPDATE reservations SET room_id=:room_id, user_name=:user_name, user_telphone=:user_telphone, comment=:comment, checkin_date=:checkin_date, checkout_date=:checkout_date WHERE id=:id');
        $stmt->bindValue(':room_id',$request['room_id'],PDO::PARAM_INT);
        $stmt->bindValue(':user_name',$request['user_name'],PDO::PARAM_STR);
        $stmt->bindValue(':user_telphone',$request['user_telphone'],PDO::PARAM_STR);
        $stmt->bindValue(':comment',$request['comment'],PDO::PARAM_STR);
        $stmt->bindValue(':checkin_date',$request['checkin_date'],PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$request['checkout_date'],PDO::PARAM_STR);
        $stmt->bindValue(':id',$request['id'],PDO::PARAM_INT);
        $stmt->execute();
        }catch(Exception $e){
            throw new Exception('データベースエラー：予約の変更に失敗しました');
        }
    }

    
}
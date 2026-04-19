<?php
class ReservationsModel{
    private $pdo;

    //コンストラクタ（PDOをもらう）
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    //予約にて、個人の予約データをreservationsテーブルにINSERTするメソッド。
    public function insert($request){
        $room_id=$request['room_id'];
        $user_name=$request['user_name'];
        $user_telphone=$request['user_telphone'];
        $checkin_date=$request['checkin_date'];
        $checkout_date=$request['ceckout_date'];
        try{
        $stmt=$this->pdo->prepare('INSERT INTO reservations (room_id,user_name,user_telphone,checkin_date,checkout_date) VALUES (:room_id,:user_name,:user_telphone,:checkin_date,:checkout_date)');
        $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
        $stmt->bindValue('user_name',$user_name,PDO::PARAM_STR);
        $stmt->bindValue(':user_telphone',$user_telphone,PDO::PARAM_STR);
        $stmt->bindValue(':checkin_date',$checkin_date,PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$checkout_date,PDO::PARAM_STR);
        $stmt->execute();
        }catch(Exception $e){
        exit('エラー：'.$e->getMessage());
        }
    }
}
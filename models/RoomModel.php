<?php
    class RoomModel{
    private $pdo;

    //コンストラクタ（PDOをもらう）
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    //部屋ごとのルーム情報を取得するメソッド。連想配列で返ってくる。
    public function getRoomInformation($room_id){
        try{
        $stmt=$this->pdo->prepare('SELECT * FROM rooms WHERE id=:room_id');
        $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
        $stmt->execute();
        $room_information=$stmt->fetch(PDO::FETCH_ASSOC);
        return $room_information;
        }catch(Exception $e){
            throw new Exception('データベースエラー：部屋情報を取得できませんでした');
        }
    }

    //すべての部屋の名前を取得するメソッド。配列で名前が返ってくる。
    public function getRoomsName(){
         try{
        $stmt=$this->pdo->prepare('SELECT room_name FROM rooms');
        $stmt->execute();
        $rooms_name=$stmt->fetch(PDO::FETCH_COLUMN);
        return $rooms_name;
        }catch(Exception $e){
            throw new Exception('データベースエラー：部屋情報を取得できませんでした');
        }
    }
}
<?php
    class RoomModel{
    private $pdo;

    //コンストラクタ（PDOをもらう）
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    //ルーム情報を取得するメソッド。
    public function getRoomInformation($room_id){
        try{
        $stmt=$this->pdo->prepare('SELECT * FROM rooms WHERE id=:room_id');
        $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
        $stmt->execute();
        $room_information=$stmt->fetch(PDO::FETCH_COLUMN);
        return $room_information;
        }catch(Exception $e){
            throw new Exception('データベースエラー：部屋情報を取得できませんでした');
        }
    }
}
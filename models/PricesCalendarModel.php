<?php
    class PricesCalendarModel{
    private $pdo;

    //コンストラクタ（PDOをもらう）
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

 //指定した種類の部屋の、指定されたプランの、一か月の値段レコードを取得するメソッド。
    public function getPricesRecord($room_id,$plan,$year,$month){
        $startdate=sprintf('%04d-%02d-01',$year,$month);
        $enddate=date('Y-m-d',strtotime("$startdate +1 Month"));
        try{
        $stmt=$this->pdo->prepare('SELECT * FROM pricesCalendar WHERE room_id=:room_id AND plan=:plan AND checkin_date >= :startdate AND checkin_date < :enddate');
        $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
        $stmt->bindValue(':plan',$plan,PDO::PARAM_STR);
        $stmt->bindValue(':startdate',$startdate,PDO::PARAM_STR);
        $stmt->bindValue(':enddate',$enddate,PDO::PARAM_STR);
        $stmt->execute();
        $pricesRecord=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $pricesRecord;
        }catch(Exception $e){
            throw new Exception('データベースエラー：値段情報を取得できませんでした');
        }
    }



}
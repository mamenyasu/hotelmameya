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
        $stmt=$this->pdo->prepare('SELECT * FROM pricescalendar WHERE room_id=:room_id AND plan_name=:plan_name AND stay_date >= :startdate AND stay_date < :enddate');
        $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
        $stmt->bindValue(':plan_name',$plan,PDO::PARAM_STR);
        $stmt->bindValue(':startdate',$startdate,PDO::PARAM_STR);
        $stmt->bindValue(':enddate',$enddate,PDO::PARAM_STR);
        $stmt->execute();
        $pricesRecord=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $pricesRecord;
        }catch(Exception $e){
            throw new Exception('データベースエラー：値段情報を取得できませんでした');
        }
    }

//指定した種類の部屋の、指定されたプランの、指定された期間の値段レコードを取得するメソッド。
    public function getPricesBetween($room_id,$plan,$checkin_date,$checkout_date){
        try{
        $stmt=$this->pdo->prepare('SELECT price FROM pricescalendar WHERE room_id=:room_id AND plan_name=:plan_name AND stay_date >= :checkin_date AND stay_date < :checkout_date');
        $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
        $stmt->bindValue(':plan_name',$plan,PDO::PARAM_STR);
        $stmt->bindValue(':checkin_date',$checkin_date,PDO::PARAM_STR);
        $stmt->bindValue(':checkout_date',$checkout_date,PDO::PARAM_STR);
        $stmt->execute();
        $pricesRecord=$stmt->fetchAll(PDO::FETCH_COLUMN);
        return $pricesRecord;
        }catch(Exception $e){
            throw new Exception('データベースエラー：値段情報を取得できませんでした');
        }
    }

}
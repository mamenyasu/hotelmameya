<?php
class RoomAvailabilityRepository{

    private PDO $pdo;

    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    public function getAvailability(int $year,int $month){
        //せっかくインデックスを設定しているので、速度改善のためSQLでLIKEは使わない。

        $startYearMonth=sprintf('%04d-%02d-01',$year,$month);
        $endYearMonth=date('Y-m-d',strtotime('$startYearMonth +1 Month'));

        $stmt=$this->pdo->prepare("SELECT * FROM room_availablity WHERE stay_date >= :startYearMonth AND stay_date <:endYearMonth");
        $stmt->bindValue(':startYearMonth',$startYearMonth,PDO::PARAM_STR);
        $stmt->bindValue(':endYearMonth',$endYearMonth,PDO::PARAM_STR);
        $stmt->execute();

        $availablity=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $availablity; //指定した一か月分のデータを配列で返す。

    }
}
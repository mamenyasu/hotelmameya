<?php
    class PlanModel{
    private $pdo;

    //コンストラクタ（PDOをもらう）
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    //プラン名を単純配列で取得するメソッド。
    public function getPlanName(){
        try{
        $stmt=$this->pdo->prepare('SELECT plan_name FROM plans');
        $stmt->execute();
        $plans=$stmt->fetchAll(PDO::FETCH_COLUMN);
        return $plans;
        }catch(Exception $e){
            throw new Exception('データベースエラー：情報を取得できませんでした');
        }
    }

    //指定された部屋の、プランデータを連想配列で取得するメソッド。
    // [
    //  [['room_id']=>..., ['plan_name']=>..., ['plan_title']=>... ,]
    //  [['room_id']=>..., ['plan_name']=>..., ['plan_title']=>... ,]
    // ]みたいな感じ。
    public function getPlansData($room_id){
        try{
        $stmt=$this->pdo->prepare('SELECT * FROM plans WHERE room_id=:room_id');
        $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
        $stmt->execute();
        $plansData=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $plansData;
        }catch(Exception $e){
            throw new Exception('データベースエラー：情報を取得できませんでした');
        }
    }

}


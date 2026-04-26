<?php
    class PlanModel{
    private $pdo;

    //コンストラクタ（PDOをもらう）
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    //プランを取得するメソッド。
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



}
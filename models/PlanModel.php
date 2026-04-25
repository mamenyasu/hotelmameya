<?php
    class PlanModel{
    private $pdo;

    //コンストラクタ（PDOをもらう）
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }

    //プランを取得するメソッド。
    public function getPlan(){
        try{
        $stmt=$this->pdo->prepare('SELECT plan FROM plans');
        $stmt->execute();
        $plans=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $plans;
        }catch(Exception $e){
            throw new Exception('データベースエラー：値段情報を取得できませんでした');
        }
    }



}
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

    //プランデータを配列で取得するメソッド。
    // [
    //  0 => [ ['plan_name']=>..., ['plan_title']=>... ,]
    //  1 => [ ['plan_name']=>..., ['plan_title']=>... ,]
    // ]みたいな感じ。
    public function getPlansData(){
        try{
        $stmt=$this->pdo->prepare('SELECT * FROM plans');
        $stmt->execute();
        $plansData=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $plansData;
        }catch(Exception $e){
            throw new Exception('データベースエラー：情報を取得できませんでした');
        }
    }

//プラン名からプランタイトルを取得するメソッド。
    public function getPlanTitle($plan_name){
        try{
        $stmt=$this->pdo->prepare('SELECT plan_title FROM plans WHERE plan_name=:plan_name');
        $stmt->bindValue(':plan_name',$plan_name,PDO::PARAM_STR);
        $stmt->execute();
        $planTitle=$stmt->fetch(PDO::FETCH_COLUMN);
        return $planTitle;
        }catch(Exception $e){
            throw new Exception('データベースエラー：情報を取得できませんでした');
        }
    }




}


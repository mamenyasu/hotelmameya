<?php
$room_id=1;
$plans=[];      //$plans=['bed_only, standard'] みたいになる。
$plansbase=[];     //$plansbase=['bed_only'=> 0, 'standard' => 2000]みたいになる。数値はint。
$start_price=0;
$plan_price=0;
$start_date=date('Y-m-d');

echo "room_id=1:シングル、room_id=2:ツイン、room_id=3:ダブル";
echo "本日より365日分の、値段カレンダーテーブル(pricesCalendar)を作成します。";

try{
    $dsn='mysql:host=localhost;dbname=hotelmameya;charset=utf8';
    $user='root';
    //dbパスワードは無し。
    $db=new PDO($dsn,$user);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//プラン配列(plans)とプラン上乗せ(plansbase)配列をデータベースから取得し自動で作成。
    $stmt=$db->prepare('SELECT * FROM plans');
    $stmt->execute();
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $plans[]=$row['plan_name'];
        $plansbase[$row['plan_name']] = (int)$row['plan_base'];
    }


    for($i=0; $i<3; $i++){  //部屋種類で回す。

        $room_id = $i + 1;
        
        switch ($i) {  //部屋種類ごとに基準料金を設定。
            case 0: $start_price = 5000; break; // シングル
            case 1: $start_price = 6000; break; // ツイン
            case 2: $start_price = 6000; break; // ダブル
        }
            foreach($plans as $plan){
                $date=$start_date; //日にちを今日にリセット。

                $plan_price = $start_price + $plansbase[$plan]; //プランに応じて料金を上乗せ。

                for($j=0; $j<365; $j++){                   
                    $w=date('w',strtotime($date)); //曜日ごとに料金調整。
                    if($w == 5){
                        $price=$plan_price+1000;
                    }else if($w  == 6){
                        $price=$plan_price+2000;
                    }else{
                        $price=$plan_price;
                    }

                    $stmt=$db->prepare('INSERT IGNORE INTO pricesCalendar (room_id,plan,stay_date,price) VALUES (:room_id,:plan,:stay_date,:price)');
                    $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
                    $stmt->bindValue(':plan',$plan,PDO::PARAM_STR);
                    $stmt->bindValue(':stay_date',$date,PDO::PARAM_STR);
                    $stmt->bindValue(':price',$price,PDO::PARAM_INT);
                    $stmt->execute();
                    $date=date('Y-m-d',strtotime("$date +1 day")); //シングルクォートで囲まないように。変数展開されません。
                }
            }
        
    }

}catch(Exception $e){
exit('エラー：'.$e->getMessage());
}

echo "値段カレンダー(pricesCalendar)を作成しました。";



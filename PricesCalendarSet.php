<?php
$room_id=1;
$plans=['bed_only', 'standard'];
$plansbase=['bed_only'=>0, 'standard'=>2000,];
$start_price;
$plan_price;
$start_date=date('Y-m-d');

echo "room_id=1:シングル、room_id=2:ツイン、room_id=3:ダブル";
echo "本日より365日分の、値段カレンダーテーブル(pricesCalendar)を作成します。";

try{
    $dsn='mysql:host=localhost;dbname=hotelmameya;charset=utf8';
    $user='root';
    //dbパスワードは無し。
    $db=new PDO($dsn,$user);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);

    for($i=0; $i<3; $i++){  //部屋種類で回す。
        
        switch ($i) {  //部屋種類ごとに基準料金を設定。
            case 0: $start_price = 5000; break; // シングル
            case 1: $start_price = 6000; break; // ツイン
            case 2: $start_price = 6000; break; // ダブル
        }
            foreach($plans as $plan){
                $date=$start_date; //日にちを今日にリセット。

                switch($plan){ //プランごとに基準料金に上乗せ。
                    case 'bed_only' : $plan_price = $start_price + $planbase[$plan]; break;
                    case 'standard' : $plan_price = $start_price + $planbase[$plan]; break;
                }

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

        $room_id++;
        
    }

}catch(Exception $e){
exit('エラー：'.$e->getMessage());
}

echo "値段カレンダー(pricesCalendar)を作成しました。";



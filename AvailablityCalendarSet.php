<?php
$room_id=1;
$total_rooms=5;
$start_date=date('Y-m-d');

echo "room_id=1:シングル(5部屋)、room_id=2:ツイン(4部屋)、room_id=3:ダブル(3部屋)";
echo "本日より365日分の、予約状況カレンダーテーブル(room_availablity)を作成します。";

try{
    $dsn='mysql:host=localhost;dbname=hotelmameya;charset=utf8';
    $user='root';
    //dbパスワードは無し。
    $db=new PDO($dsn,$user);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    for($i=0; $i<3; $i++){
        $date=$start_date;
        for($j=0; $j<365; $j++){
            $stmt=$db->prepare('INSERT IGNORE INTO room_availability (room_id,stay_date,booked_rooms,total_rooms) VALUES (:room_id,:stay_date,:booked_rooms,:total_rooms)');
            $stmt->bindValue(':room_id',$room_id,PDO::PARAM_INT);
            $stmt->bindValue(':stay_date',$date,PDO::PARAM_STR);
            $stmt->bindValue(':booked_rooms',0,PDO::PARAM_INT);
            $stmt->bindValue(':total_rooms',$total_rooms,PDO::PARAM_INT);
            $stmt->execute();
            $date=date('Y-m-d',strtotime("$date +1 day"));
        }
        $room_id++;
        if($i==0){
            $total_rooms=4;
        }else if($i==1){
            $total_rooms=3;
        }
    }

}catch(Exception $e){
exit('エラー：'.$e->getMessage());
}

echo "予約状況カレンダー(room_availability)を作成しました。";


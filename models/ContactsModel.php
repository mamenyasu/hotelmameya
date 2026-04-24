<?php
class ContactsModel{
//!!--プロパティ--
private $pdo;
//!!--コンストラクタ--
    public function __construct(PDO $pdo){
        $this->pdo=$pdo;
    }


////予約内容をデータベースに登録。
    public function confirm($request){
        if($request['id']){
            $request['id']=(int)mb_convert_kana($request['id'],'n','urf-8');
        }

        try{
        $stmt=$this->pdo->prepare('INSERT INTO contacts (id, user_name, user_telphone, email, comment) VALUES (:id, :user_name, :user_telphone, :email, :comment');
        $stmt->bindValue(':id',$request['id'],PDO::PARAM_INT);
        $stmt->bindValue('user_name',$request['user_name'],PDO::PARAM_STR);
        $stmt->bindValue(':user_telphone',$request['user_telphone'],PDO::PARAM_STR);
        $stmt->bindValue(':email',$request['email'],PDO::PARAM_STR);
        $stmt->bindValue(':comment',$request['comment'],PDO::PARAM_STR);
        $stmt->execute();
        }catch(Exception $e){
            throw new Exception('データベースエラー：問い合わせの登録に失敗しました');
        }
    }


}


<?php

require __DIR__.'/../requests/ContactFormRequest.php';
require __DIR__.'/../services/ContactService.php';

class ContactController{
//!!--プロパティ--
    private $pdo;
    private $contactFormRequest;
    private $contactService;

//!!--コンストラクタ--
    public function __construct($pdo){
        $this->pdo=$pdo;
        $this->contactFormRequest= new ContactFormRequest();
        $this->contactService= new ContactService($pdo);
    }


////コンタクトフォーム表示。
    public function contact_form(){
        include __DIR__.'/../views/contactForm.php';
        exit();
    }


////コンタクトフォーム最終確認画面表示。
    public function contact_reconfirm($request){

    //電話番号を正規化（全角→半角、ハイフン除去）
    $tel = mb_convert_kana($request['user_telphone'], 'n', 'UTF-8'); // 全角数字→半角
    $request['user_telphone'] = preg_replace('/[^0-9]/', '', $tel); // 数字以外を除去

    //バリデーション。通らなかったら差し戻し。
    $error=$this->contactFormRequest->contactFormValidate($request);
        if($error){
            $reservation_id=htmlspecialchars($request['reservation_id'], ENT_QUOTES, 'UTF-8');
            $user_name=htmlspecialchars($request['user_name'], ENT_QUOTES, 'UTF-8');
            $user_telphone=htmlspecialchars($request['user_telphone'], ENT_QUOTES, 'UTF-8');
            $email=htmlspecialchars($request['email'], ENT_QUOTES, 'UTF-8');
            $comment=htmlspecialchars($request['comment'], ENT_QUOTES, 'UTF-8');
            include __DIR__.'/../views/contactForm.php';
            exit();
        }

    //セッション変数に保持。
    $_SESSION['contact']=[
        'reservation_id' => $request['reservation_id'],
        'user_name' => $request['user_name'],
        'user_telphone' => $request['user_telphone'],
        'email' => $request['email'],
        'comment' => $request['comment']
    ];

    //ビュー表示用
    $user_name=$request['user_name'];
    $user_telphone=$request['user_telphone'];
    $email=$request['email'];
    $reservation_id=$request['reservation_id'] ?? null;
    $comment=$request['comment'];
    include __DIR__.'/../views/contactReconfirm.php';
    }

////コンタクト内容をデータベースに書き込み確定。
    public function contact_confirm(){
        if(!isset($_SESSION['contact'])){
            echo '不正なリクエストです。';
            exit();
        }

        try{
            $result=$this->contactService->confirm($_SESSION['contact']);
            unset($_SESSION['contact']);
            $message=$result['message'];
            include __DIR__.'/../view/success.php';
            exit();
        //例外処理。
        }catch(Exception $e){
            unset($_SESSION['contact']);
            $message=$e->getMessage();
            include __DIR__.'/../view/false.php';
        }
    }
}
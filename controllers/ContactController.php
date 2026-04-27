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
    //バリデーション。通らなかったら差し戻し。
    $error=$this->contactFormRequest->contactFormValidate($request);
        if($error){
            $reservaiton_id=$request['reservation_id'];
            $user_name=$request['user_name'];
            $user_telphone=$request['user_telphone'];
            $email=$request['email'];
            $comment=$request['comment'];
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
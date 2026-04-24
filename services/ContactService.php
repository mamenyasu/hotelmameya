<?php

require_once __DIR__.'/../models/ContactsModel.php';

class ContactService{
//!!--プロパティ--
    private $contactsModel;
//!!--コンストラクタ--    
    public function __construct($pdo){
        $this->contactsModel=new ContactsModel($pdo);
    }

////問い合わせをデータベースに書き込む操作。
    public function confirm($request){
        try{
            if($request['reservation_id']){
                $request['reservation_id']=(int)mb_convert_kana($request['reservation_id'],'n','urf-8');
            }
            $result=$this->contactsModel->confirm($request);
            return ['success'=>true, 'message'=>'問い合わせを受け付けました。'];
        }catch(Exception $e){
            throw $e;
        }
    }


}
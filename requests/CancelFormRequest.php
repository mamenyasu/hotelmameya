<?php
class CancelFormRequest{
    //キャンセルフォームのバリデーションをするメソッド。error配列を返す。
    public function cancelFormValidate($request){
        $error=[];
        $id=trim(mb_convert_kana($request['id'], 'n', 'UTF-8'));
        $email=$request['email'];

        //予約IDについて
        if($id==null || trim($id)==""){
            $error['id']='予約IDを入力してください。';
        }elseif(strlen($id) > 11){
            $error['id_length']='予約IDは１１桁以内で入力してください。';
        }elseif(!preg_match('/^\d+$/', $id)){
            $error['id_format']='予約IDは数字のみで入力してください。';
        }

        //メールアドレスについて
        if($email==null || trim($email)==""){
            $error['email']='メールアドレスが入力されていません。';
        }elseif(mb_strlen($email) > 255){
            $error['email_length']='メールアドレスは２５５文字以内で入力してください。';
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error['email_format'] = 'メールアドレスの形式が正しくありません。';
        }

        return $error;

    }
}
<?php
class ContactFormRequest{
    //Contactformのバリデーションをするメソッド。error配列を返す。
    public function contactFormvalidate($request){
        $error=[];
        $reservation_id=$request['reservation_id'];
        $user_name=$request['user_name'];
        $user_telphone=$request['user_telphone'];
        $email=$request['email'];
        $comment=$request['comment'];


        //名前について
        if($user_name==null || trim($user_name)==""){
            $error['user_name']='名前が入力されていません。';
        }elseif(mb_strlen($user_name,'UTF-8') > 100){
            $error['user_name_length']='名前は１００文字以内で入力してください。';
        }

        //電話番号について。問い合わせは空欄でも構わないので、NULLチェックはない。
        if(!preg_match('/^[0-9]+$/',$user_telphone)){
            $error['user_telphone_format']='電話番号は半角数字で入力してください。';
        }elseif(mb_strlen($user_telphone) > 100){
            $error['user_telphone_length']='電話番号は１００文字以内で入力してください。';
        }

        //メールアドレスについて
        if($email==null || trim($email)==""){
            $error['email']='メールアドレスが入力されていません。';
        }elseif(mb_strlen($email) > 255){
            $error['email_length']='メールアドレスは２５５文字以内で入力してください。';
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error['email_format'] = 'メールアドレスの形式が正しくありません。';
        }

        //コメントについて
        if(mb_strlen($comment) > 1000){
            $error['comment_length']='コメントは１０００文字以内で入力してください。';
        }


        return $error;

    }
}
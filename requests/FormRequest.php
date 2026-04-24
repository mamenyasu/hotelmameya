<?php
class FormRequest{
    //formのバリデーションをするメソッド。error配列を返す。
    public function formvalidate($request){
        $error=[];
        $user_name=$request['user_name'];
        $user_telphone=$request['user_telphone'];
        $user_address=$request['user_address'];
        $email=$request['email'];
        $comment=$request['comment'];
        $checkin_date=$request['checkin_date'];
        $checkout_date=$request['checkout_date'];
        $total_price=$request['total_price'];

        //名前について
        if($user_name==null || trim($user_name)==""){
            $error['user_name']='名前が入力されていません。';
        }elseif(mb_strlen($user_name,'UTF-8') > 100){
            $error['user_name_length']='名前は１００文字以内で入力してください。';
        }

        //電話番号について
        if($user_telphone==null || trim($user_telphone)==""){
            $error['user_telphone']='電話番号が入力されていません';
        }elseif(!preg_match('/^[0-9]+$/',$user_telphone)){
            $error['user_telphone_format']='電話番号は数字のみで入力してください。';
        }elseif(mb_strlen($user_telphone) > 100){
            $error['user_telphone_length']='電話番号は１００文字以内で入力してください。';
        }

        //住所について
        if($user_address==null || trim($user_address)==""){
            $error['user_address']='住所が入力されていません。';
        }elseif(mb_strlen($user_address,'utf-8') > 255){
            $error['user_address_length']='住所は２５５文字以内で入力してください。';
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

        //チェックイン、アウトについて
        if($checkin_date==null || $checkin_date==""){
            $error['checkin_date']='チェックイン日が未定です。';
        }
        if($checkout_date==null || $checkout_date=""){
            $error['checkout_date']='チェックアウト日が未定です。';
        }
        if(strtotime($checkin_date) >= strtotime($checkout_date)){
            $error['date_order']='チェックイン日はチェックアウト日より前にしてください。';
        }

        //合計金額について
        if($total_price==null || $total_price==""){
            $error['total_price']='合計金額が未入力です。';
        }elseif (!is_numeric($total_price)) {
            $error['total_price_format'] = '合計金額は数値で入力してください。';
        }

        return $error;

    }
}
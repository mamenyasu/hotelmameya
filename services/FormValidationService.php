<?php
class FormValidationService{
    //formのバリデーションをするメソッド。error[]配列を返す。

    public function formvalidate($request){
        $error=[];
        $user_name=$request['user_name'];
        $user_telphone=$request['user_telphone'];
        $checkin_date=$request['checkin_date'];
        $checkout_date=$request['checkout_date'];
        $total_price=$request['total_price'];

        //名前について
        if($user_name==null || trim($user_name)==""){
            $error['user_name']='名前が入力されていません。';
        }
        if(mb_strlen($user_name,'UTF-8') > 33){
            $error['user_name_length']='名前は３３文字以内で入力してください。';
        }

        //電話番号について。まずは全角だったら半角に。
        $user_telphone=mb_convert_kana($user_telphone,'n','UTF-8');
        if($user_telphone==null || $user_telphone==""){
            $error['user_telphone']='電話番号が入力されていません';
        }
        if(!preg_match('/^[0-9]+$/',$user_telphone)){
            $error['user_telphone_format']='電話番号は数字のみで入力してください。';
        }
        if(strlen($user_telphone) > 100){
            $error['user_telphone_length']='電話番号は１００文字以内で入力してください。';
        }

        //チェックイン、アウトについて
        if($checkin_date==null || $checkin_date==""){
            $error['checkin_date']='チェックイン日が未定です。';
        }
        if($checkout_date==null || $checkout_date=""){
            $error['checkout_date']='チェックアウト日が未定です。';
        }
        if(strtotime($checkin_date) >= strtotime($checkout_date)){
            $eeror['date_order']='チェックイン日はチェックアウト日より前にしてください。';
        }

        //合計金額について
        if($total_price==null || $total_price==""){
            $error['total_price']='合計金額が未入力です。';
        }

        return $error;

    }
}
<?php
class UpdateFormRequest{
    public function updateFormValidate($request){
        $error=[];
        $checkin_date=$request['checkin_date'];
        $checkout_date=$request['checkout_date'];
        $total_price=$request['total_price'];

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
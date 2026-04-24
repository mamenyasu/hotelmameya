<?php
class YearMonthToDaysService{

//指定された年月の、１～月末日の値を配列で返す。
    public function getDays($year, $month){
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year); //28とか31とか。
    $days = range(1, $daysInMonth);
    return $days;
    }
}
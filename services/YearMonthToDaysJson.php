<?php
////このphpにGET ?year=${year}&month=${month}でアクセスしただけで、JSONが返ります。
//指定された年月の、１～月末日の値を配列で返す。

$year  = $_GET['year'];
$month = $_GET['month'];

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year); //28とか31とか。
$days = range(1, $daysInMonth);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($days);
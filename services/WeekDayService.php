<?php
class WeekDayService{
    public function getStartWeekDay_From_Ym($year,$month){
        $start_weekDay=date('w',strtotime(sprintf('%04d-%02d-1',$year,$month)));
        return $start_weekDay;
    }

    public function getStartWeekDay_From_checkinDate($checkin_date){
        $year=date('Y',strtotime($checkin_date));
        $month=date('m',strtotime($checkin_date));
        $start_weekDay=date('w',strtotime(sprintf('%04d-%02d-1',$year,$month)));
        return $start_weekDay;
    }

}
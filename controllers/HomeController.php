<?php
class HomeController{

private function clearReservationSessions(){
        unset($_SESSION['reserve']);
        unset($_SESSION['reserve_cancel']);
        unset($_SESSION['reserve_update_old']);
        unset($_SESSION['reserve_update_new']);
        unset($_SESSION['reserve_update_calendar']);
        unset($_SESSION['contact']);
        unset($_SESSION['reserve_form_selectedPlan']);
        unset($_SESSION['reserve_form']);
        unset($_SESSION['reserve_update_verify']);
        }



    public function index(){
          $this->clearReservationSessions();
        include __DIR__.'/../views/index.php';
        exit();
    }

    public function rooms(){
        $this->clearReservationSessions();
        include __DIR__.'/../views/rooms.php';
        exit();
    }

    public function foods(){
        $this->clearReservationSessions();
        include __DIR__.'/../views/foods.php';
        exit();
    }

    public function facility(){
        $this->clearReservationSessions();
        include __DIR__.'/../views/facility.php';
        exit();
    }

}
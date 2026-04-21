<?php
class HomeController{
    public function index(){
        include __DIR__.'/../views/index.php';
        exit();
    }

    public function rooms(){
        include __DIR__.'/../views/rooms.php';
        exit();
    }

    public function foods(){
        include __DIR__.'/../views/foods.php';
        exit();
    }

    public function amenity(){
        include __DIR__.'/../views/amenity.php';
        exit();
    }
}
<?php
session_start();

$requestUri=$_SERVER['REQUEST_URI'];
$path=parse_url($requestUri,PHP_URL_PATH);
$path=trim($path,'/');
$segments=explode('/',$path);
$controller=$segments[0] ?? 'home';
$action=$segments[1] ?? 'index';
$room_id=$segments[2];
$year=$segments[3];
$month=$segments[4];
$day=$segments[5];

$dsn='mysql:host=localhost;dbname=hotelmameya;charset=utf8';
$user='root';
$pdo=new PDO($dsn,$user);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);

switch($controller){
    case 'home' :
        require_once 'controllers/HomeController.php';
        $ctrl=new HomeController();
        switch($action){
            case 'index' : $ctrl->index(); break;
            case 'rooms' : $ctrl->rooms(); break;
            case 'foods' : $ctrl->foods(); break;
            case 'amenity' : $ctrl->amenity(); break;
            default : $ctrl->index(); break;
        }
    break;

    case 'reserve' :
        require_once 'controllers/ReservationController.php';
        $ctrl=new ReservationController($pdo);
        switch($action){
            case 'index' : $ctrl->index(); break;
            case 'reservationCalendar' : $ctrl->reservationCalendar($room_id,$year,$month); break;
            case 'reserve_form' : $ctrl->reserve_form($room_id,$year,$month,$day); break;
            case 'reserve_reconfirm' : $ctrl->reserve_reconfirm($_POST); break;
            case 'reserve_confirm' : $ctrl->reserve_confirm(); break;
            case 'cancel_form' : $ctrl->reserve_cancel_form(); break;
            case 'cancel_reconfirm' : $ctrl->reserve_cancel_reconfirm($_POST); break;
            case 'cancel_confirm' : $ctrl->reserve_cancel_confirm(); break;
            case 'update_verify_form' : $ctrl->reserve_updateVerify_form(); break;
            case 'update_form' : $ctrl->reserve_update_form($_POST); break;
            case 'update_reconfirm' : $ctrl->reserve_update_reconfirm($_POST); break;
            case 'update_confirm' : $ctrl->reserve_update_confirm(); break;
            default : $ctrl->index(); break;
        }
    break;

    case 'contact' :
        require_once 'controllers/ContactController.php';
        $ctrl=new ContactController($pdo);
        switch($action){
            case 'index' : $ctrl->index(); break;
            case 'contact_form' : $ctrl->contact_form(); break;
            case 'contact_form_confirm' : $ctrl->contact_confirm(); break;
            default : $ctrl->index(); break;
        }
    break;


    case 'master' :
        require_once 'controllers/MasterController.php';
        $ctrl=new MasterController($pdo);
        switch($action){
            case 'index' : $ctrl->index(); break;
            case 'login_form' : $ctrl->master_login_form(); break;
            case 'login_confirm' : $ctrl->master_login_confirm(); break;
            case 'master_home' : $ctrl->master_home(); break;
            case 'master_listSelect' : $ctrl->master_listSelect(); break;
            case 'master_list' : $ctrl->master_list(); break;
            default : $ctrl->index(); break;
        }
    break;

    default : 
        echo '404 Not Found';
    break; 
}
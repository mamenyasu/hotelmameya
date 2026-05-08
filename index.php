<?php
session_start();

$requestUri=$_SERVER['REQUEST_URI'];
$path=parse_url($requestUri,PHP_URL_PATH);
//サブディレクトリ名を削除。
$path = str_replace('/hotelmameya/', '', $path);
$path=trim($path,'/');
if ($path === '') {
    $segments=[];  //explodeしてしまうとnullではなく空文字が返ってきてしまい、後述の「??」が効かなくなる。
}else{
    $segments=explode('/',$path);
}
$controller=$segments[0] ?? 'home';
$action=$segments[1] ?? 'index';
$room_id=$segments[2] ?? null;
$year=$segments[3] ?? null; 
$month=$segments[4] ?? null;
$day=$segments[5] ?? null;
$plan=$segments[6] ?? null;

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
            case 'facility' : $ctrl->facility(); break;
            default : $ctrl->index(); break;
        }
    break;

    case 'reserve' :
        require_once 'controllers/ReservationController.php';
        $ctrl=new ReservationController($pdo);
        switch($action){
            case 'reserve_index' : $ctrl->reserve_index(); break;
            case 'reserve_calendar' : $ctrl->reservationCalendar($room_id,$year,$month); break;
            case 'reserve_form' : $ctrl->reserve_form($room_id,$year,$month,$day,$plan); break;
            case 'reserve_reconfirm' : $ctrl->reserve_reconfirm($_POST); break;
            case 'reserve_confirm' : $ctrl->reserve_confirm(); break;
            case 'cancel_form' : $ctrl->reserve_cancel_form(); break;
            case 'cancel_reconfirm' : $ctrl->reserve_cancel_reconfirm($_POST); break;
            case 'cancel_confirm' : $ctrl->reserve_cancel_confirm(); break;
            case 'update_verify_form' : $ctrl->reserve_updateVerify_form(); break;
            case 'update_form' : $ctrl->reserve_update_form($_POST); break;
            case 'update_reconfirm' : $ctrl->reserve_update_reconfirm($_POST); break;
            case 'update_confirm' : $ctrl->reserve_update_confirm(); break;
            
            default : echo "404 Not Found"; break;
        }
    break;

    case 'contact' :
        require_once 'controllers/ContactController.php';
        $ctrl=new ContactController($pdo);
        switch($action){
            case 'contact_form' : $ctrl->contact_form(); break;
            case 'contact_reconfirm' : $ctrl->contact_reconfirm($_POST); break;
            case 'contact_confirm' : $ctrl->contact_confirm(); break;
            default : echo "404 Not Found"; break;
        }
    break;


    case 'master' :
        require_once 'controllers/MasterController.php';
        $ctrl=new MasterController($pdo);
        switch($action){
            case 'master_index' : $ctrl->index(); break;
            case 'login_form' : $ctrl->master_login_form(); break;
            case 'login_confirm' : $ctrl->master_login_confirm(); break;
            case 'master_listSelect' : $ctrl->master_listSelect(); break;
            case 'master_list' : $ctrl->master_list(); break;
            default : echo "404 Not Found"; break;
        }
    break;

    
    case 'ajax':
        require_once 'controllers/AjaxController.php';
        $ctrl = new AjaxController($pdo);

        switch($action){
        case 'calendar' : $ctrl->calendar($room_id, $plan,$year, $month); break;
        case 'maxguest' : $ctrl->maxguest($room_id); break;
        case 'estimate' : $ctrl->estimate(); break;
        default : echo "404 Not Found"; break;
        }
    break;


    default : 
        echo '404 Not Found';
    break; 
}
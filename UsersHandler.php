<?php
require_once dirname(__FILE__).'\Users.php';

try {

    if(isset( $_GET['function2call'] ) || isset( $_POST['function2call'] )) {

        $usersObj = new Users();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $function2call = $_POST['function2call'];
        } elseif($_SERVER['REQUEST_METHOD'] === 'GET') {
            $function2call = $_GET['function2call'];
        }

        // getUsers
        if($function2call === 'getUsers') {
            $result = $usersObj->getUsers($_GET['activationStatus']);
        }

        // getUser
        if($function2call === 'getUser') {
            $result = $usersObj->getUser($_GET['user_id']);
        }

        // getUserCol
        if($function2call === 'getUserPermissions') {
            $result = $usersObj->getUserPermissions($_GET['logon_name'],$_GET['logon_user'],$_GET['user_id']);
        }

        // removeUserCol
        if($function2call === 'removeUserCol') {
            $result = $usersObj->removeUserCol($_POST['logon_user'],$_POST['user_id']);
        }

        echo $result;
    }

} catch(Exception $e) {
    mail("dev@tomfafard.com","PermissionsTable Demo Error: UsersHandler.php",$e);
}

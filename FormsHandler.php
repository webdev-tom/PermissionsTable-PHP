<?php
require_once dirname(__FILE__).'\Forms.php';

if(isset( $_GET['function2call'] ) || isset( $_POST['function2call'] )) {

    $formsObj = new Forms();

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $function2call = $_POST['function2call'];
    } elseif($_SERVER['REQUEST_METHOD'] === 'GET') {
        $function2call = $_GET['function2call'];
    }

    // getForms
    if($function2call === 'getForms') {
        $result = $formsObj->getForms();
    }

    echo $result;
}

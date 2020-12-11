<?php

declare(strict_types=1);
require_once("../shared/Mysql.php");

class Forms {


    // ***** getForms *****
    // Returns a 2-dimensional array indicating forms (permission categories) from the ERP.
    // Each inner array has two values: the form ID followed by the form name.
    function getForms()
    {
        $func_status = 'pass';
        $func_error = '';
        $forms = '';

        try {
            $tableName = "permissions_forms";

            $getForms = new Mysql;
            $getForms->dbConnect();
            $resultSet = $getForms->selectAll($tableName);
            $getForms->dbDisconnect();

            if($resultSet) {
                if(mysqli_num_rows($resultSet) > 0){
                    $forms = '[';
                    while($row = mysqli_fetch_array($resultSet)){
                        $newFormTitle = preg_replace("/\"/"," ",$row['FORMTITLE']);
                        $forms .= '['.$row['FORMID'].',"'.$newFormTitle.'"],';
                    }
                    $forms = substr($forms, 0, -1);
                    $forms .= ']';
                } else {
                    $func_status = 'fail';
                    $func_error = 'getForms: Query returned no results..';
                }
            } else {
                $func_status = 'fail';
                $func_error = 'getForms: Error performing query..';
            }

        } catch (Exception $e) {
            $func_status = 'fail';
            $func_error = 'getForms: Caught exception: '.$e->getMessage();
            mail("dev@tomfafard.com", "PermissionsTable Demo Error: Users getForms", $e->getMessage());
        }

        return '{"STATUS":"'.$func_status.'","ERROR":"'.$func_error.'","RESULT":'.$forms.'}';

    }

}

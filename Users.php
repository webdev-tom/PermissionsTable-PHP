<?php

    declare(strict_types=1);
    require_once("../shared/Mysql.php");

try {

    class Users
    {

        /*
		***** getUsers *****

		Returns a JSON string of users from ERP based on input criteria.

		Arguments:
			$activationStatus
				Type: String
				Possible values: "ALL", "1", "0"
				Purpose: Controls whether the function should filter users based on activation status

	    */
        function getUsers(string $activationStatus = "ALL")
        {
            $func_status = 'pass';
            $func_error = '';
            $users = '""';

            try {

                $tableName = "permissions_logon";
                $colName = "Active";
                $operator = "=";
                $value = $activationStatus;
                $valueType = "char";

                $getUsers = new Mysql;
                $getUsers->dbConnect();
                if ($value != "ALL") {
                    $resultSet = $getUsers->selectWhere($tableName, $colName, $operator, $value, $valueType);
                } else {
                    $resultSet = $getUsers->selectAll($tableName);
                }

                if($resultSet) {
                    if(mysqli_num_rows($resultSet) > 0){
                        $users = '[';
                        while($row = mysqli_fetch_array($resultSet)){
                            $newLogonName = preg_replace("/\"/"," ",$row['LOGON_NAME']);
                            $users .= '{"LOGON_ID":"'.$row['LOGON_ID'].'","LOGON_USER":"'.$row['LOGON_USER'].'","LOGON_NAME":"'.$newLogonName.'"},';
                        }
                        $users = substr($users, 0, -1);
                        $users .= ']';
                    } else {
                        $func_status = 'fail';
                        $func_error = 'getUsers: Query returned no results..';
                    }
                } else {
                    $func_status = 'fail';
                    $func_error = 'getUsers: Error performing query..';
                }

                $getUsers->dbDisconnect();

            } catch (Exception $e) {
                $func_status = 'fail';
                $func_error = 'getUsers: Caught exception: '.$e->getMessage();
                mail("dev@tomfafard.com", "PermissionsTable Demo Error: Users getUsers", $e->getMessage());
            }


            return '{"STATUS":"'.$func_status.'","ERROR":"'.$func_error.'","RESULT":'.$users.'}';

        }

        /*
        ***** getUser *****

		Returns a JSON string of a single user from ERP based on ID.

		Arguments:
            $user_id
				Type: Int
				Possible values: Valid LOGON_ID value from Permissions_Logon table
				Purpose: Allows us to pull information for a specific user.
         */
        function getUser(int $user_id = -1)
        {
            $func_status = 'pass';
            $func_error = '';
            $user = '""';

            try {

                if($user_id !== -1) {

                    $query = new Mysql;
                    $query->dbConnect();


                    $tableName = "permissions_logon";
                    $colName = "LOGON_ID";
                    $operator = "=";
                    $value = $user_id;
                    $valueType = "int";
                    $limit = 1;

                    $resultSet = $query->selectWhere($tableName, $colName, $operator, $value, $valueType, $limit);

                    if($resultSet) {
                        if(mysqli_num_rows($resultSet) > 0){
                            $row = mysqli_fetch_array($resultSet);
                            $newLogonName = preg_replace("/\"/"," ",$row['LOGON_NAME']);
                            $user = '{"LOGON_ID":"'.$row['LOGON_ID'].'","LOGON_USER":"'.$row['LOGON_USER'].'","LOGON_NAME":"'.$newLogonName.'"}';
                        } else {
                            $func_status = 'fail';
                            $func_error = 'getUser: Query returned no results..';
                        }
                    } else {
                        $func_status = 'fail';
                        $func_error = 'getUser: Error performing query..';
                    }

                    $query->dbDisconnect();

                } else {
                    $func_status = 'fail';
                    $func_error = 'You must pass a valid user id..';
                }


            } catch (Exception $e) {
                $func_status = 'fail';
                $func_error = 'getUser: Caught exception: '.$e->getMessage();
                mail("dev@tomfafard.com", "PermissionsTable Demo Error: Users getUser", $e->getMessage());
            }

            return '{"STATUS":"'.$func_status.'","ERROR":"'.$func_error.'","RESULT":'.$user.'}';
        }

        /*
        ***** getUserPermissions *****

		Returns a JSON string of a user's permissions based on a LOGON_ID value.

		Arguments:
			$logon_user
				Type: String
				Possible values: Valid LOGON_USER value from Permissions_Logon table
				Purpose: Simply passed through the function for JavaScript to reference later on.
            $user_id
				Type: Int
				Possible values: Valid LOGON_ID value from Permissions_Logon table
				Purpose: Allows us to pull permissions for a specific user.
			$web_user
				Type: String
				Possible values: The current user making this request ( based on session )
				Purpose: Used as a reference point to track which web users are accessing which permissions.
         */
        function getUserPermissions(string $logon_name = "x", string $logon_user = "x", int $user_id = -1, string $web_user = "Guest")
        {
            $func_status = 'pass';
            $func_error = '';
            $permissions = '""';

            try {

                if($user_id !== -1) {

                    $query = new Mysql;
                    $query->dbConnect();

                    // Query to check if a web user is currently editing this logon_user

                    $tableName = "permissions_editing";
                    $colName = "LOGON_ID";
                    $operator = "=";
                    $value = $user_id;
                    $valueType = "int";

                    $resultSet = $query->selectWhere($tableName, $colName, $operator, $value, $valueType);

                    $alreadyEditing = false;
                    if($resultSet) {
                        if(mysqli_num_rows($resultSet) > 0) {
                            $row = mysqli_fetch_array($resultSet);
                            if($web_user !== $row['WEB_USER']){
                                $func_status = 'fail';
                                $func_error = $logon_user.' is currently being edited by '.$row['WEB_USER'].'.\nYou may 
                                make changes once they are finished.';
                                return '{"STATUS":"'.$func_status.'","ERROR":"'.$func_error.'","RESULT":'.$permissions.'}';
                            } else {
                                $alreadyEditing = true;
                            }
                        }
                    } else {
                        $func_status = 'fail';
                        $func_error = 'getUserCol: Error performing query..';
                    }

                    // Query to select the current permission settings for the passed logon_user

                    $tableName = "permissions_levels";
                    $colName = "LOGON_ID";
                    $operator = "=";
                    $value = $user_id;
                    $valueType = "int";

                    $resultSet = $query->selectWhere($tableName, $colName, $operator, $value, $valueType);

                    if($resultSet) {
                        if(mysqli_num_rows($resultSet) > 0){
                            $permissions = '{"LOGON_NAME":"'.$logon_name.'","LOGON_USER":"'.$logon_user.'","USER_ID":"'
                                .$user_id.'","PERMISSIONS":[';
                            while($row = mysqli_fetch_array($resultSet)){
                                // Make sure to set NULLs to -1 (No Access)
                                $row['LOGON_LEVEL'] = is_null($row['LOGON_LEVEL']) ? -1 : $row['LOGON_LEVEL'];
                                $permissions .= '{"FORMID":"'.$row['FORMID'].'","LOGON_LEVEL":"'.$row['LOGON_LEVEL'].
                                    '"},';
                            }
                            $permissions = substr($permissions, 0, -1);
                            $permissions .= ']}';
                        } else {
                            $func_status = 'fail';
                            $func_error = 'getUserCol: Query returned no results..';
                        }
                    } else {
                        $func_status = 'fail';
                        $func_error = 'getUserCol: Error performing query..';
                    }

                    // Query to insert this $web_user as the current editor if records do not already exist
                    if(!$alreadyEditing){
                        $result = $query->freeRun("INSERT INTO Permissions_Editing (LOGON_ID,WEB_USER) VALUES ($user_id,'$web_user')");

                        if(!$result) {
                            $func_status = 'fail';
                            $func_error = 'addUserCol: Error adding current user to editing table..';
                        }
                    }

                    $query->dbDisconnect();

                } else {
                    $func_status = 'fail';
                    $func_error = 'You must pass a valid user id..';
                }

            } catch (Exception $e) {
                $func_status = 'fail';
                $func_error = 'getUserCol: Caught exception: '.$e->getMessage();
                mail("dev@tomfafard.com", "PermissionsTable Demo Error: Users addUserCol", $e->getMessage());
            }

            return '{"STATUS":"'.$func_status.'","ERROR":"'.$func_error.'","RESULT":'.$permissions.'}';
        }


        /*
        ***** removeUserCol *****

		Removes the passed $web_user from the Permissions_Editing table.
        Other users will now have the opportunity to make changes to this LOGON_ID.

		Arguments:
			$logon_user
				Type: String
				Possible values: Valid LOGON_USER value from Permissions_Logon table
				Purpose: Simply passed through the function for JavaScript to reference later on.
            $user_id
				Type: Int
				Possible values: Valid LOGON_ID value from Permissions_Logon table
				Purpose: The user we'd like to remove from the Permissions_Editing table.
			$web_user
				Type: String
				Possible values: The current user making this request ( based on session )
				Purpose: The web user we'd like to remove from the Permissions_Editing table.
         */
        function removeUserCol(string $logon_user = "x", int $user_id = -1, string $web_user = "Guest")
        {
            $func_status = 'pass';
            $func_error = '';

            try {

                if($user_id !== -1) {

                    $query = new Mysql;
                    $query->dbConnect();

                    // Query to remove the current web user from the Permissions_Editing table
                    $result = $query->freeRun("DELETE FROM Permissions_Editing WHERE LOGON_ID = $user_id AND WEB_USER = '$web_user'");

                    if(!$result) {
                        $func_status = 'fail';
                        $func_error = 'removeUserCol: Error removing the editing user..';
                    }

                } else {
                    $func_status = 'fail';
                    $func_error = 'You must pass a valid user id..';
                }

                $query->dbDisconnect();

            } catch (Exception $e) {
                $func_status = 'fail';
                $func_error = 'removeUserCol: Caught exception: '.$e->getMessage();
                mail("dev@tomfafard.com", "PermissionsTable Demo Error: Users removeUserCol", $e->getMessage());
            }

            return '{"STATUS":"'.$func_status.'","ERROR":"'.$func_error.'","RESULT":"'.$logon_user.'"}';

        }

    }

} catch(Exception $e) {
    mail("dev@tomfafard.com","PermissionsTable Demo Error: Users.php",$e);
}

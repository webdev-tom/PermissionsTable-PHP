<?php
try{
require("../shared/Mysql.php");

// Check db to see if user was in the middle of editing permissions
// If they were, restore session by passing the $selectedUsers array to JavaScript
$Mysql = new Mysql;
$Mysql->dbConnect();

$tableName = "permissions_editing";
$colName = "WEB_USER";
$operator = "=";
$value = "Guest";
$valueType = "char";

$resultSet = $Mysql->selectWhere($tableName,$colName,$operator,$value,$valueType);
$Mysql->dbDisconnect();

$selectedUsers = [];
if($resultSet) {
    if(mysqli_num_rows($resultSet) > 0){
        // Returned rows... Restore current user's session with the selected users they were editing
        while($row = mysqli_fetch_array($resultSet)){
            array_push($selectedUsers,$row['LOGON_ID']);
        }
    }
} else {
    throw new Exception('Error performing the query...');
}

} catch(Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
<!doctype html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Permission Table Demo | tomfafard.com</title>

<link href="/includes/plugins/bootstrap3/css/bootstrap.css" rel="stylesheet">
<link href="/includes/plugins/sumoselect/sumoselect.min.css" rel="stylesheet">
<link href="/includes/plugins/fontawesome/css/all.min.css" rel="stylesheet">
<style>
	
	body{
		background-color: #fefefe;
	}
	
	table#permissionTable{
		margin: 0 auto;
		width: 100%;
		clear: both;
		border-collapse: collapse;
		table-layout: fixed; 
		word-wrap:break-word; 
	}

    .hidden {
        opacity: 0 !important;
    }
	
	.nopadding{
		padding: 0 !important;
	}
	
	.blockRow {
		display: block !important;
	}
	
	
	.loader{
		margin-top: 50px;
		text-align: center;
		font-size: 1.5em;
		transition: 500ms ease;
		opacity: 0;
		display: none;
		vertical-align: middle;
		user-select: none;
		cursor: default;
	}
	
	.smoothFade{
		transition: opacity 500ms ease;
	}
	
	.smoothSlide{
		transition: 500ms ease;
	}
	
	
	.statusNav {
		display: inline-flex;
		justify-content: center;
	}

	.pPill{
		padding: 0 3px;
		width: calc(100% / 3) !important;
	}
	.pPill a{
		border: 1px solid #448AC8 !important;
	}
	
	.pTable {
		width: 99%;
	}
	
	.pTitle {
		background-color: #F5FBFF;
		font-weight: bold;
        opacity: 1;
	}
	
	.list-group-item{
		transition: 150ms ease;
	}

    .userColHeadCell{
        opacity: 1;
    }
	
	.userColBodyCell{
		text-align: center;
        opacity: 1;
	}
	
	.submitChangesBtn{
		display: inline-block;
		color: #78B99A;
		margin-left: 5px;
		position: absolute;
		right: 0;
		top: 0;
		height: 100%;
		padding: 0 12px !important;
		background-color: rgba(255,255,255,0) !important;
	}
	
	.discardChangesBtn{
		display: inline-block;
		color: #E95671;
		margin-right: 5px;
		position: absolute;
		left: 0;
		top: 0;
		height: 100%;
		padding: 0 12px !important;
		background-color: rgba(255,255,255,0) !important;
	}
	
	.submitChangesBtn:hover, 
	.discardChangesBtn:hover{
/*		border: 1px solid #A9A9A9;*/
		border: none;
		color: #AFC3D1;
	}
	
	.colDisplayName{
		width: 80%;
		margin: 0 auto !important;
	}


	
	
	#userSelectContainer{
		height: 100vh;
		background-color: #ebebeb;
		padding: 25px;
	}
	
	#usl-container{
		background-color: #fff;
		border: 1px solid #448AC8;
		border-radius: 4px;
		margin-top: 5px;
		height: 80%;
		overflow: scroll;
	}
	
	#user-select-list{
		margin-bottom: 0 !important;
		transition: 500ms ease;
		opacity: 0;
		display: none;
	}
	
	#deselectBtn{
		font-weight: bold;
		width: 100%;
		margin-top: 4px;
		opacity: 0.3;
		transition: opacity 200ms ease-in;
	}
	
	#appHint{
		width: 100%;
		text-align: center;
		position: absolute;
		top: 40%;
		left: 0;
		transition: opacity 150ms ease;
		z-index: 1;
	}
	
	#appHint h2{
		color: #4D4D4D;
		user-select: none;
		-webkit-user-select: none;
		cursor: default;
	}

	#permissionTableContainer {
		padding: 14px 35px;
	}
	
	#permissionTable tbody{
		display:block;
		overflow:auto;
		height:80vh;
		width:100%;
		border-bottom: 2px solid #BEBEBE;
	}
	
	#permissionTable thead{
		border-bottom: 2px solid #BEBEBE;
	}
	
	#permissionTable thead tr{
		display:block;
		transition: height 500ms ease;
		height: 0;
		/*opacity: 0;*/
	}
	
	#permissionTable thead th,
	#permissionTable thead td{
		padding: 10px 18px;
	}
	
	#permissionTable tbody td{
		padding: 8px 10px;
		vertical-align: middle;
	}

	#toggleLeftHandView {
		 position: absolute;
		 font-size: 2em;
		 top: 0;
		 left: 10px;
		 cursor: pointer;
		 z-index: -1;
	 }


	/* SumoSelect Overrides */

	.SumoSelect > .CaptionCont > span {
		padding-right: 0 !important;
	}



	@media only screen and (max-width: 992px) {
		.leftHandView {
			transform: translate3d(-100%,0,0);
			position: absolute;
			transition: 400ms ease;
			z-index: 10;
		}

		.rightHandView {
			margin-top: 30px;
		}

		#toggleLeftHandView {
			z-index: 2;
		}

		#userSelectContainer {
			padding: 0 25px 25px 25px;
		}

		#usl-container{
			height: 60%;
		}

		#permissionTableContainer {
			padding: 14px 0px;
		}

		#permissionTable tbody{
			height:65vh;
		}
	}

	
</style>
</head>
<body>

	<div id="toggleLeftHandView"><i class="fas fa-bars"></i></div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-3 nopadding leftHandView">
				
				<div id="userSelectContainer">
					<ul class="nav nav-pills nav-justified statusNav">
						<li role="presentation" class="pPill">
                            <a href="#" onClick="checkRefreshList(this)" data-flag="ALL">All</a>
                        </li>
						<li role="presentation" class="pPill active">
                            <a href="#" onClick="checkRefreshList(this)" data-flag="1">Active</a>
                        </li>
						<li role="presentation" class="pPill">
                            <a href="#" onClick="checkRefreshList(this)" data-flag="0">Inactive</a>
                        </li>
					</ul>
					<div id="usl-container">
						<div class="loader"><i class="fas fa-sync-alt fa-spin"></i><br>Updating user list...</div>
						<div class="list-group" id="user-select-list"></div>
					</div>
					<button id="deselectBtn" class="btn btn-primary" onClick="removeSelection()" disabled>
                        Clear Selection
                    </button>

					<!---<br><br><br>--->
					<!---<strong style="font-size: 20px;">User Array = <span id="testArrayValue">[]</span></strong>--->

				</div>
			</div>
			<div class="col-md-9 nopadding rightHandView">
				<div id="permissionTableContainer">
					<div id="appHint"><h2>Select a user from the list to edit their permissions.</h2></div>
					<table border="0" class="pTable" id="permissionTable">
						<thead>
							<tr></tr>
						</thead>
						<tbody></tbody>
					</table>
				</div><!--- table container --->

			</div><!--- col-md-9 --->
		</div><!--- row --->
	</div>
	


<script src="/includes/plugins/jquery_3.4.1/jquery-3.4.1.min.js" type="text/javascript"></script>
<script src="/includes/plugins/bootstrap3/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/includes/plugins/fontawesome/js/all.min.js" type="text/javascript"></script>
<script src="/includes/plugins/sumoselect/jquery.sumoselect.min.js" type="text/javascript"></script>
<script type="text/javascript">
// Define changes array, which records the permission changes we'll need to make
let changes = [];

// Define global selectedUsers array, which holds the users we'll go and grab
let selectedUsers = <?php echo json_encode($selectedUsers) ?> || [];

// On Ready
$(function(){

	// Init user-select-list
	let initUserList = refreshUserList(1);

	// If there are already users selected, restore them
    if(selectedUsers.length > 0) {
        for(let i = 0;i < selectedUsers.length;i++){
            fillSessionCols(selectedUsers[i]);
        }
    }

	// handle panel for mobile
	$('#toggleLeftHandView').on('click',function(){
		$('.leftHandView').css('transform','translate3d(0,0,0)');
		$('.rightHandView').one('click',function(){
			$('.leftHandView').css('transform','translate3d(-100%,0,0)');
		});
	});
});


function checkRefreshList(e) {
    let $thisPillLink = $(e);
    let $thisPill = $(e).parent('li');
    let $thisPillGroup = $(e).parent('li').parent('ul');

    if(!$thisPill.hasClass('active')){
        $thisPillGroup.children('li').each(function(){
            $(this).removeClass('active');
        });
        $thisPill.addClass('active');
        refreshUserList($thisPillLink.data('flag'));
    }
}

function refreshUserList(activeFlag = 'ALL') {
    if(typeof activeFlag == 'undefined'){
        activeFlag = 'ALL'
    }

    let $usl = $('#user-select-list');
    let $loader = $('.loader');

    $usl.css('opacity','0');
    setTimeout(function(){
        $loader.show();
        $usl.hide();
        $loader.css('opacity','1');
        $usl.html('');

        const maxselectedusers = 3;
        $.ajax({
            type: 'get',
            url: 'UsersHandler.php',
            dataType: 'html',
            data: {
                function2call: 'getUsers',
                activationStatus: activeFlag
            },
            cache: false,
            success: function( data ){
                let json = data.trim();
                let parsedJSON = JSON.parse(json);

                if(parsedJSON['STATUS'] === 'pass'){
                    let users = parsedJSON['RESULT'];
                    let $deselectBtn = $('#deselectBtn');

                    for(let i = 0; i < users.length; i++){
                        $usl.append('<a href="#" class="list-group-item' +
                            ((selectedUsers.indexOf(parseInt(users[i]['LOGON_ID'])) > -1) ? ' active' : '') +
                            '" title="' + users[i]['LOGON_USER'] + '" data-userid="' + users[i]['LOGON_ID'] +
                            '" data-logonuser="' + users[i]['LOGON_USER'] + '">' + users[i]['LOGON_NAME'] + '</a>');
                    }

                    $usl.children('a').each(function(){
                        $(this).on('click',function(){
                            if($(this).hasClass('active')){
                                selectedUsers = spliceArray(selectedUsers,$(this).data('userid'));
                                removeUserCol($(this).data('logonuser'),$(this).data('userid'));
                                $(this).removeClass('active');
                            } else {

                                if(selectedUsers.length < maxselectedusers){
                                    $(this).addClass('active');
                                    selectedUsers.push($(this).data('userid'));
                                    getUserPermissions($(this).data('logonuser'),$(this).html(),$(this).data('userid'));
                                } else {
                                    //selected user limit reached, do nothing...
                                }
                            }

                            if(selectedUsers.length > 0){
                                $deselectBtn.prop('disabled',false);
                                $deselectBtn.css('opacity','1');
                            } else {
                                $deselectBtn.prop('disabled',true);
                                $deselectBtn.css('opacity','0.3');
                            }
                        });
                    });

                    $usl.show();
                    $loader.css('opacity','0');
                    setTimeout(function(){
                        $loader.hide();
                        $usl.css('opacity','1');
                    },500);

                } else {
                    console.log('Caught Exception in refreshUserList => ',parsedJSON['ERROR']);
                    return false;
                }
            }
        });
    },500);
}

function fillSessionCols(user_id) {
    try {
        $.ajax({
            type: 'get',
            url: 'UsersHandler.php',
            dataType: 'html',
            data: {
                function2call: 'getUser',
                user_id: user_id
            },
            cache: false,
            success: function (data) {
                let json = data.trim();
                let parsedJSON = JSON.parse(json);

                if (parsedJSON['STATUS'] === 'pass') {
                    let user = parsedJSON['RESULT'];
                    getUserPermissions(user.LOGON_USER, user.LOGON_NAME, user.LOGON_ID);
                } else {
                    console.log('Caught Exception in fillSessionCols => ',parsedJSON['ERROR']);
                    return false;
                }
            }
        });
    } catch(error) {
        console.log('Caught Exception in fillSessionCols => ' + error);
    }
}

// ***** getForms *****
// Returns a promise containing
// Arguments:
//  fresh <bool>: Determines whether we need to add the labels column.
//                True = Fresh table, yes we do
//                False = Table already has columns, no need
//  userPermissions <arr>: Array of objects that hold a single user's permissions.
function getForms() {
	return new Promise((resolve,reject) => {
		try {
			$.ajax({
				type: 'get',
				url: 'FormsHandler.php',
				dataType: 'html',
                data: {
                    function2call: 'getForms'
                },
				cache: false,
				success: function( data ){
					let json = data.trim();
					let parsedJSON = JSON.parse(json);
					if(parsedJSON["STATUS"] === 'pass'){
						const formsArr = parsedJSON["RESULT"];
						resolve(formsArr);
					} else {
						reject(parsedJSON["ERROR"]);
					}
				}
			});	
		}
		catch(error) {
			reject(error);
		}
	});
}

function addForms(formsArr) {
    return new Promise((resolve,reject) => {
        try {
            for(let i = 0; i < formsArr.length; i++){
                $('#permissionTable > tbody').append('<tr class="smoothFade"><td class="pTitle smoothFade hidden" ' +
                    'data-formid="' + formsArr[i][0] + '">' + formsArr[i][1] + '</td></tr>');
            }
            resolve(true);
        } catch(error) {
            reject(error);
        }
    });
}

// ***** getUserPermissions *****
// Gets an object containing user info and their permissions.
// Passes the object to the addColumns function.
// Arguments:
//  logon_user <string>: The user's username
//  logon_name <string>: Full name of the user
//  user_id <int>: The ID of the user
function getUserPermissions(logon_user, logon_name, user_id) {
    let orig_permissions = {}; // Object to save original permissions object, in case user wishes to revert changes made

    try {
        $.ajax({
            type: 'get',
            url: 'UsersHandler.php',
            dataType: 'html',
            data: {
                function2call: 'getUserPermissions',
                logon_user: logon_user,
                logon_name: logon_name,
                user_id: user_id
            },
            cache: false,
            success: async function( data ){
                let json = data.trim();
                let parsedJSON = JSON.parse(json);

                if(parsedJSON['STATUS'] === 'pass'){
                    let userPermissions = parsedJSON['RESULT'];
                    let fresh = false
                    orig_permissions[user_id] = userPermissions;

                    const currentColWidth = await getColWidth();

                    if(currentColWidth == 0) {
                        fresh = true;
                    }
                    const addColumns_result = await addColumns(fresh,userPermissions);
                    return true;
                } else {
                    console.log('Exception in getUserPermissions =>', parsedJSON['ERROR']);
                    let $userToDeselect = $('#user-select-list').find('[data-logonuser="' + logon_user + '"]');
                    selectedUsers = spliceArray(selectedUsers,$userToDeselect.data('userid'));
                    $userToDeselect.removeClass('active');
                    return false;
                }
            }
        });
    } catch(error) {
        console.log('Caught Exception in getUserPermissions => ',error);
    }
}

// ***** addColumns <async> *****
// Handles adding columns to the table.
// Arguments:
//  fresh <bool>: Determines whether we need to add the labels column
//                True = Fresh table, yes we do
//                False = Table already has columns, no need
//  userPermissions <obj>: Object that holds user data and their permissions
async function addColumns(fresh, userPermissions) {
    try {
        if(fresh) {
            $('#appHint').css({'opacity':'0','z-index':'-1'});
            $('#permissionTable > thead > tr').css({'height':'42px'});
            $('#permissionTable > thead').css('border-bottom','2px solid #448AC8');
            $('#permissionTable > tbody').css('border-bottom','2px solid #448AC8');
            $('#permissionTable > thead > tr').append('<th class="userColHeadCell smoothSlide hidden">&nbsp;</th>');

            const getForms_result = await getForms();
            const addForms_result = await addForms(getForms_result);
        }
        const addUserCol_result = await addUserCol(userPermissions);
        const getColWidth_result = await getColWidth();
        const adjColWidth_result = await adjColWidth(getColWidth_result);

        const showNewCol_result = await showNewCol();


        // getForms()
        //     .then(result => addForms(Object.entries(result)))
        //     .then(result => addUserCol(logon_user,permissions,orig_permissions,logon_name,logon_id))
        //     .then(result => adjColWidth(colWidth))
        //     .then(function(){
        //         $('#permissionTable > thead > tr').find('th:last-child').css('opacity',1);
        //         $('#permissionTable > tbody > tr').find('td:last-child').css('opacity',1);
        //         setTimeout(function(){
        //             $('#permissionTable > thead').find('tr').css('opacity',1);
        //             $('#permissionTable > tbody').find('tr').css('opacity',1);
        //         },500)
        //     }).catch(error => {
        //     alert('Caught Exception => ' + error);
        // });

        return true;

    } catch(error) {
        console.log('Caught Exception => ',error);
        return false;
    }
}

// ***** addUserCol *****
// Adds user column to the table.
// Arguments:
//  userPermissions <obj>: Object that holds user data and their permissions
function addUserCol(userPermissions){
	return new Promise((resolve,reject) => {
		try {
			let $appendedHeader = $('<th data-logonuser="' + userPermissions['LOGON_USER'] + '" class="userColHeadCell smoothFade smoothSlide hidden">' +
                '<div style="position:relative;width:100%;text-align:center">' +
                '<p class="colDisplayName">' + userPermissions['LOGON_NAME'] + '</p><button class="btn discardChangesBtn">' +
                '<i class="fas fa-times"></i></button><button class="btn submitChangesBtn"><i class="fas fa-check"></i>'
                + '</button></div></th>').appendTo('#permissionTable > thead > tr');

			// Set click handlers for discard / submit buttons
			$appendedHeader.find('.discardChangesBtn').on('click',function(){
				console.log('discard changes clicked');
			});
			$appendedHeader.find('.submitChangesBtn').on('click',function(){
				//pass orig_permissions to compare and make sure submitting a change
				//doUpdatePermissions(orig_permissions[logon_user])
				console.log('XML goes here');
			});
				
			$('#permissionTable > tbody').find('tr').each(function(){
				let $trow = $(this);
				let thisFormID = $trow.children('.pTitle').data('formid');
				let thisPermissionCategory = userPermissions['PERMISSIONS'].filter(obj => {
					return obj['FORMID'] == thisFormID;
				});

				$trow.append('<td data-logonuser="' + userPermissions['LOGON_USER'] + '" data-logonid="' + userPermissions['USER_ID'] + '" ' +
                    'class="userColBodyCell smoothFade hidden"><select id="' + userPermissions['LOGON_USER'] + '_' +
                    thisPermissionCategory[0]['FORMID'] + '_permissionSelect" class="pSelect" name="' + userPermissions['LOGON_USER'] + '_' +
                    thisPermissionCategory[0]['FORMID'] + '_permissionSelect"><option value="-1" ' +
                    ((thisPermissionCategory[0]['LOGON_LEVEL'] == -1) ? 'selected' : '') + '>No Access</option><option value="1" ' +
                    ((thisPermissionCategory[0]['LOGON_LEVEL'] == 1) ? 'selected' : '') + '>Read-Only</option><option value="0" ' +
                    ((thisPermissionCategory[0]['LOGON_LEVEL'] == 0) ? 'selected' : '') + '>Full Access</option></select></td>');
			});

			$('.pSelect').SumoSelect();
            $('.pSelect').on('change',function(){
                $(this).next('p').css({"font-weight":"bold","background-color":"#E8C648"});
            });
			resolve(true);
		}
		catch(error) {
			reject(error);
		}
	});
}

function showNewCol(){
    return new Promise((resolve, reject) => {
        try {
            setTimeout(function(){
                $('#permissionTable > thead').find('tr').each(function(){
                    $(this).children().each(function(){
                        $(this).removeClass('hidden');
                    })
                });
                $('#permissionTable > tbody').find('tr').each(function(){
                    $(this).children().each(function(){
                        $(this).removeClass('hidden');
                    })
                });
                resolve(true);
            },500);
        } catch(error) {
            reject(error);
        }
    });
}

function discardChanges(logon_id){

}


function removeUserCol(logon_user, user_id) {
	$.ajax({
		type: "post",
		url: "UsersHandler.php",
		dataType: "html",
		data: {
		    function2call: 'removeUserCol',
            logon_user: logon_user,
			user_id: user_id
		},
		cache: false,
		success: function( data ){
			let json = data.trim();
			let objRes = JSON.parse(json);

			if(objRes.status === 'pass'){
				let logon_user = objRes.logon_user;
				
				let $userCol = $('#permissionTable').find('[data-logonuser="' + logon_user + '"]');
				let $labelCol = $('#permissionTable').find('th,td');
				let $tableRows = $('#permissionTable > tbody').find('tr');
				let checkTHead = $('#permissionTable > thead > tr').children();
					
				let removeLabelCol = false;
				if(checkTHead.length <= 2){
					//remove form labels
					removeLabelCol = true;
					$tableRows.css('opacity',0);
				}

				$userCol.css('opacity',0);
				setTimeout(function(){
					$userCol.remove();
					
					if(removeLabelCol){
						$labelCol.remove();
						$tableRows.remove();
						$('#permissionTable > thead > tr').css({'height':'0','opacity':'0'});
						$('#permissionTable > thead').css('border-bottom','2px solid #BEBEBE');
						$('#permissionTable > tbody').css('border-bottom','2px solid #BEBEBE');
						setTimeout(function(){
							$('#appHint').css({'opacity':'1','z-index':'1'});
						},500);
					} else {
						let checkTHead = $('#permissionTable > thead > tr').children();
						let tableW = $('#permissionTable').outerWidth();
						let colWidth = getColWidth(tableW,checkTHead.length);
						adjColWidth(colWidth);
					}
					
				},500);

			} else {
				alert(objRes.error);
				return false;
			}
		
		}
	});	

}

	
function removeAllUserCol(logon_user_array, user_id_array) {
	for(let i = 0; i < logon_user_array.length; i++){
		$.ajax({
			type: "post",
			url: "UsersHandler.php",
			dataType: "html",
			data: {
			    function2call: 'removeUserCol',
				logon_user: logon_user_array[i],
                user_id: user_id_array[i]
			},
			cache: false,
			success: function( data ){
				let json = data.trim();
				let objRes = JSON.parse(json);

				if(objRes.status !== 'pass'){
					alert(objRes.error);
					return false;
				}
			}
		});
	}

	let $labelCol = $('#permissionTable').find('th,td');
	let $tableRows = $('#permissionTable > tbody').find('tr');
	
	$labelCol.css('opacity',0);
	$tableRows.css('opacity',0);
	setTimeout(function(){
		$labelCol.remove();
		$tableRows.remove();
		$('#permissionTable > thead > tr').css({'height':'0','opacity':'0'});
		$('#permissionTable > thead').css('border-bottom','2px solid #BEBEBE');
		$('#permissionTable > tbody').css('border-bottom','2px solid #BEBEBE');
		setTimeout(function(){
			$('#appHint').css({'opacity':'1','z-index':'1'});
		},500);
	},500);
}

// ***** getColWidth *****
// Gets new column width based on the number of column and overall table width.
// Returns 0 when there are no columns in the table.
function getColWidth() {
    return new Promise((resolve,reject) => {
        try {
            const tableWidth = $('#permissionTable').outerWidth();
            const numberOfCols = $('#permissionTable > thead > tr').children().length;
            if (numberOfCols > 0) {
                let colWidth = Math.floor(tableWidth / numberOfCols);
                colWidth = colWidth + 'px';
                resolve(colWidth);
            } else {
                resolve(0);
            }
        } catch(error) {
            reject(error);
        }
    });
}

function adjColWidth(colWidth) {
	return new Promise((resolve,reject) => {
		try {
		    console.log(colWidth);
			$('#permissionTable > thead > tr').children('th').each(function(){
				$(this).css('width',colWidth);
			});
			$('#permissionTable > tbody > tr').children('td').each(function(){
				$(this).css('width',colWidth);
			});
			resolve(true);
		}
		catch(error) {
			reject(error);
		}
	});
}
	
function removeSelection() {
	selectedUsers = [];
	
	let usersToRemove = [];
    let idsToRemove = [];
	
	$('#user-select-list').children('a').each(function(){
		if($(this).hasClass('active')){
			$(this).removeClass('active');
			usersToRemove.push($(this).data('logonuser'));
            idsToRemove.push($(this).data('userid'));
		}
	});
	removeAllUserCol(usersToRemove,idsToRemove);
	$('#deselectBtn').prop('disabled',true);
	$('#deselectBtn').css("opacity","0.3");
}
	
function spliceArray(array,value) {
	let index = array.indexOf(value);
	if (index > -1) {
		array.splice(index, 1);
		return array;
	} else {
		alert('Value does not exist in array.');
		return false;
	}
}
</script>
</body>
</html>

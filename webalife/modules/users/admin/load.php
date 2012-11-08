<?php
require_once CMS_CLASSES."admin.class.php";

$admin=new admin('USERS');

if ($_REQUEST['cmd'] == 'viewUsers') {
	$sort = "status DESC, name ASC";
	if ($_REQUEST['sort'] != "")
		$sort = $_REQUEST['sort']." ".$_REQUEST['dir'];

	$start = (isset($_POST['start']) ? $_POST['start'] : 0);
	$limit = (isset($_POST['limit']) ? $_POST['limit'] : 1000);	
	
	$where = "";
	
	if (isset($_POST['filter']) && strlen($_POST['filter']) > 0) {
		$strPattern = mysql_escape_string($_POST['filter']);
		$whereSQL = array();
		$whereSQL[] = "us.LOGIN LIKE '%".$strPattern."%'";
		$where = "AND (".implode(' OR ', $whereSQL).")";
	}

	$users = $db->sql("SELECT id, status, us.name, login, pswrd, ug.title group_name FROM ".PREFIX."users us, ".PREFIX."users_groups ug WHERE status > -2 ".$where." AND us.group_id = ug.group_id ORDER BY ".$sort." LIMIT ".$start.", ".$limit, 2);

	$nUsers = array();
	foreach($users as $us) {
		//$orders = $db->sql("SELECT count(*) cnt FROM ".PREFIX."goods_orders WHERE user_id = ".$us['ID'], 1);
		$us['ISORDERS'] = 0; //($orders['CNT'] > 0);
	    $us['ISPASSWORD'] = ($us['PSWRD'] != '');
	    $nUsers[] = $us;
    }
	exit(json_encode(array("totalCount"=>$coUsers['CNT'], "users"=>$nUsers)));
}

if ($_REQUEST['cmd'] == 'getUser' && intval($_REQUEST['uid']) > 0) {
	$user = $db->sql("SELECT ID,
			LOGIN,
			PSWRD,
			DATEREG,
			DATEONLINE,
			IP_ADDR,
			EMAIL,
			CODE_ACTIVE,
			STATUS,
			COMPANY,
			OKPO,
			INN,
			UADDRESS,
			FADDRESS,
			PHONE,
			FAX,
			TELEX,
			DIRECTOR,
			BUCH,
			APPENDIX FROM ".PREFIX."users WHERE id = ".intval($_REQUEST['uid'])." LIMIT 1", 1);
	$user['COMPANY'] = html_entity_decode($user['COMPANY']);		
	$user['DATEREG'] = date('d.m.Y H:i', $user['DATEREG']);
	if ($user['DATEONLINE'] != '')
		$user['DATEONLINE'] = date('d.m.Y H:i', $user['DATEONLINE']);	
	exit(json_encode(array("success"=>"true", "data"=>$user)));
}

if ($_REQUEST['cmd'] == 'saveUser' && isset($_REQUEST['uid'])) {
	if ($_POST['LOGIN'] == "сгенерируется, при активации") 
		$_POST['LOGIN'] = "";
		
	if ($_POST['NEWPWD'] == "заполните, если хотите сменить")
		$_POST['NEWPWD'] = "";
	
	$uid = intval($_REQUEST['uid']);	
		
	if ($uid > 0) 	
		$prevData = $db->sql("SELECT * FROM ".PREFIX."users WHERE id = ".intval($_REQUEST['uid']), 1);
	else 
		$prevData = array(
			'STATUS' => -1
		);
		
	// generate login	
	if (intval($_POST['STATUS']) != -1 && intval($prevData['STATUS']) == -1) {
		$_POST['LOGIN'] = 'user'.($uid > 0 ? $uid : mktime());
		$_POST['NEWPWD'] = $admin->CreatePassword();
	} elseif ($_POST['STATUS'] == -1 && $prevData['STATUS'] != -1) {
		$_POST['STATUS'] = 0;
	} elseif ($_POST['STATUS'] == 1) {
		// send email with credentials
		
		/*
		require_once(CMS_CLASSES.'mailer.class.php');
		$mail = new mailer("ЕГАИС <info@egais.ru>");
		$mail->setPlaceHolders(array('USER_ACTIVATION_LINK' => '<a href="'.$link.'">'.$link.'</a>'));
		$mail->sendTemplate('AfterRegLink', $FIELD_EMAIL);
		*/
		// TODO: should we send an email to user?
	}

	$db->sql(($uid > 0 ? "UPDATE ".PREFIX."users SET " : "INSERT INTO ".PREFIX."users SET ")."
					LOGIN = ".($_POST['LOGIN'] != "" ? "'".$_POST['LOGIN']."'" : "NULL").",
					".($_POST['NEWPWD'] != "" && $_POST['LOGIN'] != "" ? "PSWRD = '".md5($_POST['NEWPWD'])."', " : "")."
					".($uid == 0 ? "
						EMAIL = '".mysql_escape_string($_REQUEST['EMAIL'])."', 
						SITE_ID = 1,
						GROUP_ID = 3,
						DATEREG = ".mktime().",
						IP_ADDR = '".$_SERVER['REMOTE_ADDR']."', 	
						SITE_ACCESS_ID = 1,
					" : "")."
					STATUS = ".$_POST['STATUS'].",
					COMPANY = '".mysql_escape_string($_POST['COMPANY'])."',
					OKPO = '".mysql_escape_string($_POST['OKPO'])."',
					INN = '".mysql_escape_string($_POST['INN'])."',
					UADDRESS = '".mysql_escape_string($_POST['UADDRESS'])."',
					FADDRESS = '".mysql_escape_string($_POST['FADDRESS'])."',
					PHONE = '".mysql_escape_string($_POST['PHONE'])."',
					FAX = '".mysql_escape_string($_POST['FAX'])."',
					TELEX = '".mysql_escape_string($_POST['TELEX'])."',
					DIRECTOR = '".mysql_escape_string($_POST['DIRECTOR'])."',
					BUCH = '".mysql_escape_string($_POST['BUCH'])."',
					APPENDIX = '".mysql_escape_string($_POST['APPENDIX'])."'
				".($uid > 0 ? "WHERE id = ".$uid : ""));	

	exit(json_encode(array("success"=>(mysql_error() == ""))));
}

if ($_REQUEST['cmd'] == 'actionDelete' && isset($_POST['userIds'])) {
	$userIds = split(';', $_POST['userIds']);
	$rowsAffected = 0; $usersAffected = array();
	foreach($userIds as $uid) {
		$uData = $db->sql("SELECT id, email FROM ".PREFIX."users WHERE id = ".intval($uid), 1);
		$usersAffected[] = $uData;
		$db->sql("DELETE FROM ".PREFIX."users WHERE id = ".intval($uid)." LIMIT 1");
		$rowsAffected++;
	}
	exit(json_encode(array("success"=>($rowsAffected == count($userIds)), "rows"=>$rowsAffected, "records"=>$usersAffected)));
}

if ($_REQUEST['cmd'] == 'actionDisable' && isset($_POST['userIds'])) {
	$userIds = split(';', $_POST['userIds']);
	$rowsAffected = 0; $usersAffected = array();
	foreach($userIds as $uid) {
		$db->sql("UPDATE ".PREFIX."users SET status = 0 WHERE id = ".intval($uid)." LIMIT 1");
		if (mysql_error() == "") {
			$uData = $db->sql("SELECT id, email FROM ".PREFIX."users WHERE id = ".intval($uid), 1);
			$usersAffected[] = $uData;
			$rowsAffected++;
		}	
	}
	exit(json_encode(array("success"=>($rowsAffected == count($userIds)), "rows"=>$rowsAffected, "records"=>$usersAffected)));
}

if ($_REQUEST['cmd'] == 'actionEnable' && isset($_POST['userIds'])) {
	$userIds = split(';', $_POST['userIds']);
	$rowsAffected = 0; $usersAffected = array();
	foreach($userIds as $uid) {
		$db->sql("UPDATE ".PREFIX."users SET status = 1 WHERE id = ".intval($uid)." LIMIT 1");
		if (mysql_error() == "") {
			$uData = $db->sql("SELECT id, email FROM ".PREFIX."users WHERE id = ".intval($uid), 1);
			$usersAffected[] = $uData;
			$rowsAffected++;
		}	
	}
	exit(json_encode(array("success"=>($rowsAffected == count($userIds)), "rows"=>$rowsAffected, "records"=>$usersAffected)));
}

if ($_REQUEST['cmd'] == 'saveUserPassword' && isset($_POST['PASSWORD'])) {
	$db->sql("UPDATE ".PREFIX."users SET pswrd = '".md5($_POST['PASSWORD'])."' WHERE id = ".intval($_REQUEST['uid']));	
	exit(json_encode(array("success"=>(mysql_error() == ''))));
}

if ($_REQUEST['cmd'] == 'getOrders') {
	$orders = $db->sql("SELECT max(order_id) order_id, 
						   max(datetime) datetime, max(status) status, sum(gcnt) cnt, 
						   sum(amount) amount 
						   FROM ".PREFIX."goods_orders 
						   WHERE user_id = ".intval($_REQUEST['uid'])."
						   GROUP BY order_id 
						   ORDER BY datetime DESC", 2); 
	$newOrders = array();
	foreach($orders as $order) {
		$newOrders[] = array(
					  'NAME' 	=> 'Заказ № '.$order['ORDER_ID'].' от '.date('d.m.Y H:i', $order['DATETIME']),
					  'GCNT' 	=> $order['CNT'],
					  'AMOUNT' 	=> $admin->parsePrice($order['AMOUNT']). " р.",
					  'STATUS' 	=> $order['STATUS'],
					  'ORDER_ID'=> $order['ORDER_ID']);
	}		
	exit(json_encode(array("orders"=>$newOrders)));
}

if ($_REQUEST['cmd'] == 'setStatus' && isset($_POST['order_id'])) {
	$res = $db->sql("UPDATE ".PREFIX."goods_orders SET status = ".intval($_POST['status'])." WHERE order_id = ".intval($_POST['order_id']));	
	exit(json_encode(array("success"=>($res), "rows"=>$res, "records"=>null)));
}

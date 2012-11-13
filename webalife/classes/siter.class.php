<?php
/*
Powered by MKS Engine (c) 2006
Created: Ivan S. Soloviev, ivan@mk-studio.ru
Description: Virtual main constructor of site


------- YOU MAY MODIFY THIS FILE AS YOU WANT -------
*/

/* Include main class of site constructor */
include_once(CMS_CLASSES.'site.class.php');

class siter extends site {
	function HeaderHandler()
	{
		global $mysql;
		// �������� ���������
		if($_POST['BOOK_SEND']) {
			if(!$_POST['BOOK_NICK']) $err[] = '������� ���� ���!';
			if($_POST['BOOK_EMAIL'] && !$this->is_email($_POST['BOOK_EMAIL'])) $err[] = 'E-Mail ������ �� �����!';
			if(!$_POST['BOOK_MESSAGE']) $err[] = '������� ���� ���������!';
			if(is_array($err)) define('BOOK_ERROR',implode('<br/>',$err));
			else {
				$_POST['BOOK_NICK'] = $this->my_str_replace($_POST['BOOK_NICK']);
				$_POST['BOOK_MESSAGE'] = $this->my_str_replace($_POST['BOOK_MESSAGE']);		
				$mysql->sql("INSERT INTO ".PREFIX."guestbook SET lang = '".SITELANG."', site_id = ".$_SESSION['CMS_SITE_ID'].", datetm = ".time().", nick = '".$_POST['BOOK_NICK']."', email = '".$_POST['BOOK_EMAIL']."', message = '".$_POST['BOOK_MESSAGE']."', status = 0, ip_addr = '".$_SERVER['REMOTE_ADDR']."'");
				mail($GLOBALS['CMS_ADMIN_EMAIL'], '��������� � ��������	�����', '���: '.$_POST['BOOK_NICK']."\r\n<br/>E-Mail:".$_POST['BOOK_EMAIL']."\r\n<br/>���������:\r\n<br/>".$_POST['BOOK_MESSAGE'],"From: robot@".str_replace('www.','',$_SERVER['HTTP_HOST'])."\r\nContent-Type: text/html; charset=windows-1251");
				header("Location: ".$_SERVER['CMS_ROOT_URL'].HTMLLANG.(strpos($_REQUEST['url'],'.') ? substr($_REQUEST['url'],0,strpos($_REQUEST['url'],'.')) : $_REQUEST['url']).'.SendOK.html');
				exit;
			}
		}	
		// �������� ������ ������ ��������� � �������� �����
		if($_POST['BOOK_SEND_REPLY']) {
			if(!$_POST['BOOK_NICK']) $err[] = '������� ���� ���!';
			if($_POST['BOOK_EMAIL'] && !$this->is_email($_POST['BOOK_EMAIL'])) $err[] = 'E-Mail ������ �� �����!';
			if(!$_POST['BOOK_MESSAGE']) $err[] = '������� ���� ���������!';
			if(is_array($err)) define('BOOK_ERROR',implode('<br/>',$err));
			else {
				$res = $mysql->sql("SELECT email FROM ".PREFIX."guestbook WHERE id = ".$_GET['replyto'], 1);
				$_POST['BOOK_NICK'] = $this->my_str_replace($_POST['BOOK_NICK']);
				$_POST['BOOK_MESSAGE'] = $this->my_str_replace($_POST['BOOK_MESSAGE']);		
				//$mysql->sql("INSERT INTO ".PREFIX."guestbook SET lang = '".SITELANG."', site_id = ".$_SESSION['CMS_SITE_ID'].", datetm = ".time().", nick = '".$_POST['BOOK_NICK']."', email = '".$_POST['BOOK_EMAIL']."', message = '".$_POST['BOOK_MESSAGE']."', status = 0, ip_addr = '".$_SERVER['REMOTE_ADDR']."'");
				mail($res['EMAIL'], '����� �� ���� ��������� � �������� ����� �� ����� http://'.$_SERVER['HTTP_HOST'].'/', '���: '.$_POST['BOOK_NICK']."\r\n<br/>E-Mail:".$_POST['BOOK_EMAIL']."\r\n<br/>���������:\r\n<br/>".$_POST['BOOK_MESSAGE'],"From: robot@".str_replace('www.','',$_SERVER['HTTP_HOST'])."\r\nContent-Type: text/html; charset=windows-1251");
				header("Location: ".$_SERVER['CMS_ROOT_URL'].HTMLLANG.(strpos($_REQUEST['url'],'.') ? substr($_REQUEST['url'],0,strpos($_REQUEST['url'],'.')) : $_REQUEST['url']).'.SendOK.html?replyto='.$_GET['replyto']);
				exit;
			}
		}
		
		// ������� ������� �����������
		if($_POST['POLL_SEND']) {
			$vote = $_POST['VOTES'];
			require(CMS_MODULES.'/polls/admin/languages.php');
			$SLANG = $SLANG[SITELANG];
			$ctime = mktime(0,0,0,date('m'),date('d'),date('Y'));
			// ���� �����
			if($vote['POLL_TYPE'] == 0) {
				if($vote['ANSWER'] == '') $err[] = $SLANG['error 2'];
				if(is_array($err)) {
					define('VOTE_ERROR', implode('<br/>',$err));
				} else {
					// �������, ������� �� ����� ��� ������ �� ���� ������
					if(!empty($_COOKIE['CMS_POLL_QUESTION_'.$vote['QUEST_ID']])) {$allready = true;}
					if(!$allready) {
						$mysql->sql("INSERT INTO ".PREFIX."polls_votes SET poll_id = ".$vote['POLL_ID'].", quest_id = ".$vote['QUEST_ID'].", answer_id = ".$vote['ANSWER'].", ip_addr = '".$_SERVER['REMOTE_ADDR']."', datetm = ".$ctime);
						$mysql->sql("UPDATE ".PREFIX."polls_answers SET answers = answers + 1 WHERE answer_id = ".$vote['ANSWER']);
						setcookie('CMS_POLL_QUESTION_'.$vote['QUEST_ID'],$vote['ANSWER'],time()+60*60*24,'/');
						setcookie('CMS_POLL_'.$vote['POLL_ID'],(intval($_COOKIE['CMS_POLL_'.$vote['POLL_ID']]) + 1),time()+60*60*24,'/');
						header("Location: ".$_SESSION['HTML_URL'].".".$vote['POLL_ID'].".html");
						exit;
					} else {
						define('VOTE_ERROR', $SLANG['error 3']);
					}
				}
			}
			// ����� �������
			if($vote['POLL_TYPE'] == 1) {
				if(!is_array($vote['ANSWER'])) $err[] = $SLANG['error 2'];
				if(is_array($err)) {
					define('VOTE_ERROR', implode('<br/>',$err));
				} else {
					// �������, ������� �� ����� ��� ������ �� ���� ������
					if(!empty($_COOKIE['CMS_POLL_QUESTION_'.$vote['QUEST_ID']])) {$allready = true;}
					if(!$allready) {
						foreach($vote['ANSWER'] as $an) {
							$mysql->sql("INSERT INTO ".PREFIX."polls_votes SET poll_id = ".$vote['POLL_ID'].", quest_id = ".$vote['QUEST_ID'].", answer_id = ".$an.", ip_addr = '".$_SERVER['REMOTE_ADDR']."', datetm = ".$ctime);
							$mysql->sql("UPDATE ".PREFIX."polls_answers SET answers = answers + 1 WHERE answer_id = ".$an);
							setcookie('CMS_POLL_QUESTION_'.$vote['QUEST_ID'],$an,time()+60*60*24,'/');
						}
						setcookie('CMS_POLL_'.$vote['POLL_ID'],(intval($_COOKIE['CMS_POLL_'.$vote['POLL_ID']]) + 1),time()+60*60*24,'/');
						header("Location: ".$_SESSION['HTML_URL'].".".$vote['POLL_ID'].".html");
						exit;
					} else {
						define('VOTE_ERROR', $SLANG['error 3']);
					}
				}
			}
		}
	}
} 


?>
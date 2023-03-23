<?php
/*******************************************************************************
* Login - Klasse
/*******************************************************************************
* Version 0.9.1
* Author:  IT-Master
* www.it-master.ch / info@it-master.ch
* Copyright (c), IT-Master, All rights reserved
*******************************************************************************/
require_once ('./include/class_settings.php');
require_once('./include/class_ldap.php');

class time_login{
	public $_id		= "";
	public $_datenpfad	= "";
	public $_username 	= "";
	public $_passwort	= "";
	public $_login 		= false;
	public $_admins	= false; 	//nur Admins einloggen, dann true setzten nach erstellen einer Instanz
	private $_settings;
	private $_ldap;
	
	function __construct(){	
		$_settings = new time_settings();
		$_ldap     = new time_ldap($_settings->_array);
	}
	
	function login($POST,$userlist){
		if($this->_admins){
			if(isset($_POST['_n'])){
				$this->_username 	= trim($_POST['_n']);
			}
			if(isset($_POST['_p'])){
				$this->_passwort 	= sha1(trim($_POST['_p']));	
			}	
		}else{
			if($_POST['_n'] and $_POST['_p']){
				//  Anmeldung über Loginformular
			if(isset($_POST['_n'])){
				$this->_username 	= trim($_POST['_n']);
			}
			if(isset($_POST['_p'])){
				$this->_passwort 	= sha1(trim($_POST['_p']));	
			}	
			}else{
				// automatische Anmeldung über Cookies
				$this->_username 	= $_COOKIE["lname"];
				$this->_passwort 	= $_COOKIE["lpass"];
			}
		}
		
		// if ldap enabled then check there first
		if ($_ldap->enabled) {
			if (!$_ldap->login($this->_username, trim($_POST['_p']))) {
				// unset, login to ldap failed;
				$this->_passwort = "";
				$this->rapport($this->_username, "Fehler bei LDAP Authentifizierung", "Fehler");
			}
		}
		$this->check($userlist);
	}
	
	function rapport($U,$P,$typ){
		// Rapport - INFOS
		$rapport= simplexml_load_file("./include/Settings/rapport.xml");
		if($rapport->login==true){
			if (strpos($_SERVER['REQUEST_URI'],'admin.php')) {
    				$_datei = "adminlogin.txt";
			}else{
				$_datei = "userlogin.txt";
			}
			$_log = new time_filehandle("./debug/login/",$_datei,";");
			$_jahr = date("Y", time());
			$_monat = date("n", time());
			$_tag = date("j", time());
			$_stunde= date("H", time());
			$_minute = date("i", time());
			$_sekunde = date("s", time());		
			$_text = $_tag.".". $_monat. "." .$_jahr. "-" . $_stunde.":".$_minute.":".$_sekunde ;
			$_text .= ";";
			//Name
			$_text .= $U;
			$_text .= ";";
			//Passwort
			$_text .=$P;
			$_text .= ";";
			// Anmelde - Variante
			$_text .= $typ;
			$_text .= ";";	
			// Server - Pfad und Adresse der Webseite
			// $_text .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$_text .= $_SERVER['REQUEST_URI'];
			$_log->insert_line_top($_text);
		}
	}
	
	private function check($userlist){
		$u=0;
		if($this->_admins){
			// Im Admin - Bereich darf sich nur der erste Benutzer einloggen
			// oder alle in der ersten Gruppe(Administratoren)
			$_file		= file("./Data/group.txt");
			$_admins 	= $_file[0];
			$_admins 	= explode(";", $_admins);
			$_berechtigt 	= $_admins[2];
			$_berechtigt 	= explode(",", $_berechtigt);	
			foreach($_berechtigt as $u){
				$_name = trim($userlist[trim($u)][1]);
				$_passwort = trim($userlist[trim($u)][2]);
				if($_name == $this->_username && $_passwort == $this->_passwort){
					$this->_id 		= trim($u);
					$this->_datenpfad 	= $userlist[trim($u)][0];
					$this->_username 	= $userlist[trim($u)][1];
					$this->_passwort 	= $userlist[trim($u)][2];
					$this->_login		= true;
					$this->setSession(trim($u));
					$this->setcookie();	
					$_SESSION['showpdf'] = 1;
				}
			}
		}else{	
			foreach($userlist as $zeile){	
				if ($this->_username == trim($zeile[1]) AND $this->_passwort == trim($zeile[2])){
					$this->_id 		= $u;
					$this->_datenpfad 	= $zeile[0];
					$this->_username 	= $zeile[1];
					$this->_passwort 	= $zeile[2];
					$this->_login		= true;
					$this->setSession($u);
					$this->setcookie();		
				}
				$u++;	
			}
		}
		if($this->_login){
			$this->rapport($this->_username, "korrekt", "Login-Form");
		}else{
			if(isset($_POST['_p'])){
				$this->rapport($this->_username, $_POST['_p'], "Fehler");
			}else{
				$this->rapport($this->_username, 'Passwort fehler', "Fehler");
			}
		}
	}
	private function setSession($u){
		$_SESSION['admin'] 	= $this->_datenpfad;
		$_SESSION['id'] 		= $u;
		$_SESSION['datenpfad'] 	= $this->_datenpfad;
		$_SESSION['username'] 	= $this->_username;
		$_SESSION['passwort'] 	= $this->_passwort;
		$_SESSION['login'] 		= $this->_login;
		$_SESSION['showpdf'] 	= 0;
	}
	private function setcookie(){
		setcookie("lname",$this->_username,time()+2952000);
		setcookie("lpass",$this->_passwort,time()+2952000);	
	}	
	function checkadmin($userlist){
		$u=0;
		$_secure = false;
		if($this->_admins){			
			$_file		= file("./Data/group.txt");
			$_admins 	= $_file[0];
			$_admins 	= explode(";", $_admins);
			$_berechtigt 	= $_admins[2];
			$_berechtigt 	= explode(",", $_berechtigt);	
			foreach($_berechtigt as $u){
				$_name 		= trim($userlist[trim($u)][0]);
				$_passwort 	= trim($userlist[trim($u)][2]);
				if($_name == @$_SESSION['admin']){
					$_secure = true;
				}
			}
		}
		if(!$_secure) $this->logout();	
	}
	function logout(){
		$_SESSION['admin']		="";
		$_SESSION['id']			="";
		$_SESSION['datenpfad']	="";
		$_SESSION['username']	="";
		$_SESSION['passwort']	="";
		$_SESSION['login']		="";
		$_SESSION = array();
		//session_destroy();
		setcookie("lname","",time()-3600);
		setcookie("lpass","",time()-3600);
	}
}
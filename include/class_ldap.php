<?php
/*******************************************************************************
* LDAP - Klasse
/*******************************************************************************
* Version 0.0.1
* Author:  x-m-nc
* www.x-michael.com / info@x-michael.com
* Copyright (c), IT-Master, All rights reserved
*******************************************************************************/
class fake_filehandle {
	public $_filename 	= ""; 
	public $_filepfad 	= "";
	public $_array		= NULL;
	public $_file		= NULL;
	
	function __construct($_input = array(), $_trennzeichen) {
		$this->_file = $_input;
		$this->_array = $_input;
		$i=0;
		foreach($this->_array as $zeile){
			if(strpos($zeile, $_trennzeichen)){
				$this->_array[$i] = explode($_trennzeichen, $this->_array[$i]);
				$z=0;
				foreach($this->_array[$i] as $spalte){
					$this->_array[$i][$z] = trim($spalte);
					$z++;
				}
			}
			$i++;
		}
	}
}

class time_ldap {
	private $_settings = array();
	public $_enabled = false;
	public $_users;
	public $_groups;
	
	function __construct($arr_settings = array()) {
		$_settings = $arr_settings;
		$this->_enabled = !empty($_settings[29][1]); // ldap ist deaktiviert, wenn kein Server angegeben ist
	}
	
	function bind() {
	}
	
	function unbind() {
	}
	
	function check($POST) {
	}
}
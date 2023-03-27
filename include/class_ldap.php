<?php
/*******************************************************************************
* LDAP - Klasse
/*******************************************************************************
* Version 0.0.1
* Author:  x-m-nc
* www.x-michael.com / info@x-michael.com
* Copyright (c), IT-Master, All rights reserved
*******************************************************************************/
class time_ldap {
	private array $_settings;
	private $_ds;
	public $_enabled = false;
	
	function __construct(array $arr_settings) {
		$this->_settings = $arr_settings;
		$this->_enabled = !empty($this->_settings[29][1]); // ldap ist deaktiviert, wenn kein Server angegeben ist
		
		// für ldaps ist das Root-Zertifikat des Domänkontrollers erforderlich (base64 encoded)
		// für Testzwecke kann es "local" abgelegt werden, ansonsten im Zertifikatsspeicher
		// putenv('LDAPTLS_CACERT=./AD-CA-CERT.pem');
		//ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7); // Einkommentieren für detailierte Debug-Meldungen im error.log
		$this->_ds = ldap_connect($this->_settings[29][1]);
		
		// Prüfen ob (annonyme) Verbindung zum DC möglich ist
		if ($this->_ds) {
			$this->_enabled = ldap_bind($this->_ds);
			
			ldap_set_option($this->_ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		} else {
			$this->_enabled = false;
		}
	}
	
	function __destruct() {
		ldap_close($this->_ds);
	}
	
	function login(string $_username, string $_password) {
		// melde dich mit den Anmeldedaten aus Benutzername und Passwort an und gib zurück ob es erfolgreich war
		$_bind_result = false;
		if ($this->_enabled) {
			$_bind_result = ldap_bind($this->_ds, "CN=" . $_username . "," . $this->_settings[31][1], $_password);
		}
		return $_bind_result;
	}
	
	function change_password(string $_username, string $_oldpassword, string $_newpassword) {
		// ändere das Passwort von Benutzer
		$_ldap_modify_result = false;
		if ($this->_enabled) {
			$_bind_result = @ldap_bind($this->_ds, "CN=" . $_username . "," . $this->_settings[31][1], $_oldpassword);
			if($_bind_result) {
				// Construct the new password
				$_newpassword_enc = "{SHA}" . base64_encode(sha1($_newpassword, true));
    
				// Set the new password in the LDAP directory
				$_ldap_entry = array("userPassword" => $_newpassword_enc);
				$_ldap_modify_result = ldap_modify($this->_ds, "CN=" . $_username . "," . $this->_settings[31][1], $_ldap_entry);
			}
		}
		return $_ldap_modify_result;
	}
	
	function get_userpassword($_username) {
		if ($this->_enabled) {
			// Search for the user entry and retrieve the userPassword attribute
			$_search_filter = "(cn={$_username})";
			$_search_attributes = array("userPassword");
			$_ldap_search = ldap_search($this->_ds, $this->_settings[31][1], $_search_filter, $_search_attributes);
			if (!$_ldap_search) {
				return false;
			} else {   
				$_ldap_entries = ldap_get_entries($this->_ds, $_ldap_search);
				if ($_ldap_entries['count'] !== 1) {
					return false;
				} else {
					return $_ldap_entries[0]['userpassword'][0];
				}
			}
		} else {
			return false;
		}
	}
}
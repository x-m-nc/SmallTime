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
		ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7); // Einkommentieren für detailierte Debug-Meldungen im error.log
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
		if ($this->enabled) {
			@ldap_unbind($his->ds);
			@ldap_bind($this->ds, "CN=" . $_username . "," . $this->_settings[31][1], $_password);
		} else {
			return(false);
		}
	}
	
	function change_password(string $_username, string $_oldpassword, string $_newpassword) {
		// ändere das Passwort von Benutzer
		if ($this->enabled) {		
			if(@ldap_bind($this->ds, "CN=" . $_username . "," . $this->_settings[31][1], $_oldpassword)) {
				// Construct the new password
				$_newpassword_enc = "{SHA}" . base64_encode(sha1($_newpassword, true));
    
				// Set the new password in the LDAP directory
				$ldap_entry = array("userPassword" => $_newpassword_enc);
				return(!ldap_modify($this->_ds, "CN=" . $_username . "," . $this->_settings[31][1], $ldap_entry));
			} else {
				return(false);
			}
		} else {
			return(false);
		}
	}
	
	function get_userpassword() {
		if ($this->enabled) {
	// Search for the user entry and retrieve the userPassword attribute
    $search_filter = "(cn={$username})";
    $search_attributes = array("userPassword");
    $ldap_search = ldap_search($ldap_conn, $ldap_base_dn, $search_filter, $search_attributes);
    if (!$ldap_search) {
        return false;
    }
    
    $ldap_entries = ldap_get_entries($ldap_conn, $ldap_search);
    if ($ldap_entries['count'] !== 1) {
        return false;
    }
    
    $password_hash = $ldap_entries[0]['userpassword'][0];
	}
}
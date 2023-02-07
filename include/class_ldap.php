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
	public array $_array;
	public $_file		= NULL;
	
	function __construct(array $_input, $_trennzeichen = ";") {
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
	private array $_settings;
	private $_ds;
	public $_enabled = false;
	public $_users;
	public $_groups;
	
	function __construct(array $arr_settings) {
		$this->_settings = $arr_settings;
		$this->_enabled = !empty($this->_settings[29][1]); // ldap ist deaktiviert, wenn kein Server angegeben ist
		
		// für ldaps ist das Root-Zertifikat des Domänkontrollers erforderlich (base64 encoded)
		// für Testzwecke kann es "local" abgelegt werden, ansonsten im Zertifikatsspeicher
		// putenv('LDAPTLS_CACERT=./AD-CA-CERT.pem');
		// ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7); // Einkommentieren für detailierte Debug-Meldungen im error.log
		$this->_ds = ldap_connect($this->_settings[29][1]);
		
		// Prüfen ob (annonyme) Verbindung zum DC möglich ist
		if ($this->_ds) {
			$this->_enabled = ldap_bind($this->_ds);
			
			// create user & group arrays
		} else {
			$this->_enabled = false;
		}
	}
	
	public static function NTLMHash($_input) {
		// NTLM / AD Hash
		// Quelle: Kommentar von CraquePipe auf https://www.php.net/manual/de/ref.hash.php
		
		// Convert the password from UTF8 to UTF16 (little endian)
		$_input=iconv('UTF-8','UTF-16LE',$_input);

		// You could use this instead, but mhash works on PHP 4 and 5 or above
		// The hash function only works on 5 or above
		$MD4Hash=hash('md4',$_input);

		// Make it uppercase, not necessary, but it's common to do so with NTLM hashes
		$NTLMHash=strtoupper($MD4Hash);

		// Return the result
		return($NTLMHash);
	}
	
	function check($_input) {
	}
}
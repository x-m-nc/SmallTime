# Small - Time - LDAP-Erweiterung
Die kleine Zeiterfassung für Privatpersonen und kleine Firmen.
Infos zu Installation und Bedienung: [http://www.small.li/](http://www.small.li/)

## LDAPS - SSL-Verbindung zur Domäne

1. öffentliches Zertifikat vom Server herunterladen
	eine Möglichkeit: openssl s_client -showcerts -verify 5 -connect ldpa-server.example:636  < /dev/null | awk '/BEGIN/,/END/{ if(/BEGIN/)    {a++}; out="ldap-cert"a".pem"; print >out}'
2. in openssl/certs ablegen
	-> (linux: /usr/local/ssl/certs/AD-CA-CERT.pem)
	-> (Windows: ./AD-CA-CERT.pem (SmallTime Home))
3. im Script aktivieren (wenn anderer Dateiname verwendet)
	-> ./include/class_ldap.php
4. Einstellungen entsprechend im Admin-Interface setzen
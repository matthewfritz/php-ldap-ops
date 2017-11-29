<?php

/**
 * Author: Matthew Fritz <mattf@burbankparanormal.com>
 * Date: November 28, 2017
 *
 * Proof-of-concept to perform ldapadd operations against an LDAP server
 */

$config = require_once("common.php");

// ADDING DATA TO AN LDAP SERVER

// new person uid, overlay DN, and DN
$personuid = "oc4fanmatt4";
$overlaydn = $config['base_dn'] . "," . $config['overlay_dn'];
$persondn = "uid={$personuid}," . $config['base_dn'];

// new person data
$personmail = "matthew.fritz.2@example.com";
$personname = "Matthew Fritz";
$personarr = explode(" ", $personname);
$personpassword = "{SSHA}CWdRQEZD6jkRfmH0p7SD6dUxvKSYV6zW"; // 1234 (SSHA algorithm)

/*
objectClass: inetOrgPerson
uid: oc4fanmatt2
mail: matthew.fritz@example.com
displayName: Matthew Fritz
cn: Matthew Fritz
sn: Fritz
givenName: Matthew
userPassword:: e1NTSEF9dEVZaVUrMVFKQVU0VDNMN1M3R2VIczdRT2I5ajcvTUQ=
 */
$persondata = [
	'objectClass' => 'inetOrgPerson',
	'uid' => $personuid,
	'mail' => $personmail,
	'displayName' => $personname,
	'cn' => $personname,
	'sn' => $personarr[1],
	'givenName' => $personarr[0],
	'userPassword' => $personpassword,
];

// connect, set proper protocol, and bind
$conn = ldap_connect($config['host']) or die("Could not connect to LDAP server");
if(ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, $config['version'])) {
	echo "Set protocol to LDAPv" . $config['version'] . "<br />";
}

$bind = ldap_bind($conn, $config['add_dn'], $config['add_dn_pw']);

// add the data to the directory
if($bind) {
	$success = ldap_add($conn, $persondn, $persondata);
	if($success) {
		echo "LDAP add succeeded<br />";

		// search for and display the new data
		$search = ldap_search($conn, $overlaydn, "uid={$personuid}");
		$info = ldap_get_entries($conn, $search);
		echo "<pre>";
		print_r($info);
		echo "</pre>";
	}
	else
	{
		// display the existing data
		$search = ldap_search($conn, $overlaydn, "uid={$personuid}");
		$info = ldap_get_entries($conn, $search);
		echo "<pre>";
		print_r($info);
		echo "</pre>";

		ldap_get_option($conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
		die("LDAP add failed: " . ldap_error($conn) . " - $err");
	}
}
else
{
	ldap_get_option($conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
	die("LDAP bind failed: " . ldap_error($conn) . " - $err");
}

?>
<?php

/**
 * Author: Matthew Fritz <mattf@burbankparanormal.com>
 * Date: November 29, 2017
 *
 * Proof-of-concept to perform ldapmodify operations against an LDAP server
 */

$config = require_once("common.php");

// UPDATING USER PW AS THE USER HIMSELF

/**
 * Generates and returns a new password as a SSHA hash.
 *
 * @param string $password The plaintext password to hash
 * @param string $salt Salt for the algorithm (defaults to "salt")
 * @return string
 *
 * @see https://www.linuxquestions.org/questions/linux-newbie-8/ldap-and-php-generate-ssha-userpassword-4175451613/
 */
function generatePassword($password, $salt="salt") {
	return "{SSHA}" . base64_encode(sha1($password . $salt, true) . $salt);
}

// user uid, overlay DN (bind DN for user), and user DN
$personuid = "oc4fanmatt4";
$overlaydn = $config['base_dn'] .
	(!empty($config['overlay_dn']) ? "," . $config['overlay_dn'] : "");
$persondn = "uid={$personuid}," . $overlaydn;

// old password and new password
$oldpw = "1234";
$newpw = "1234";
$newpwhash = generatePassword($newpw);

// userPassword:: {SSHA}QMlUZLfqzdtVcq9UaP+xzbWxPzVzYWx0 (1234 as SSHA)
$persondata = [
	'userPassword' => $newpwhash,
];

// connect, set proper protocol, and bind
$conn = ldap_connect($config['host']) or die("Could not connect to LDAP server");
if(ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, $config['version'])) {
	echo "Set protocol to LDAPv" . $config['version'] . "<br />";
}

$bind = ldap_bind($conn, $persondn, $oldpw);

// add the data to the directory
if($bind) {
	$success = ldap_mod_replace($conn, $persondn, $persondata);
	if($success) {
		echo "LDAP modify user password succeeded<br />";

		// search for and display the new data
		$search = ldap_search($conn, $overlaydn, "uid={$personuid}");
		$info = ldap_get_entries($conn, $search);
		echo "<pre>";
		print_r($info);
		echo "</pre>";
	}
	else
	{
		ldap_get_option($conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
		die("LDAP modify failed: " . ldap_error($conn) . " - $err");
	}
}
else
{
	ldap_get_option($conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
	die("LDAP bind failed: " . ldap_error($conn) . " - $err");
}

?>
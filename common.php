<?php

/**
 * Author: Matthew Fritz <mattf@burbankparanormal.com>
 * Date: November 29, 2017
 *
 * Contains configuration values for the proof-of-concept operations
 */

return [

	// LDAP connection and version information
	'host' => 'ldaps://meta-dap.sandbox.csun.edu:636',
	'version' => 3,

	// base DN for searching and optional overlay DN for the subtree
	'base_dn' => 'ou=People,ou=Auth,o=METALAB',
	'overlay_dn' => 'dc=METALABproxy',

	// DN and PW for adding users
	'add_dn' => 'cn=admin,dc=METALABproxy',
	'add_dn_pw' => '4aK63dcpSak7',

	// users can bind as themselves and change their own password so there is
	// no need to have a separate modification DN and PW

];

?>
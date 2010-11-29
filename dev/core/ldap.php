<?php

class LdapException extends Exception {}

class Ldap {

	var $ldapconn = null;

	/*
	 * Anonymously binds to LDAP server
	 */
	function __construct() {
	  if(USE_LDAP) {
		  $this->ldapconn = ldap_connect("ldap.stuba.sk");

		  if($this->ldapconn) {
			  $dn = "dc=stuba,dc=sk";

			  $ldapbind = ldap_bind($this->ldapconn);

			  if(!$ldapbind) {
				  throw new LdapException("login_failed");
			  }
		  } else {
			  throw new LdapException("ldap_connect_failed");
		  }	
		}

	}

  /*
	 * Creates LDAP filter from associative array. Example:
	 * array("cn" => "Kram*", sn="Tom*") -- (cn=Kram*)(sn=Tom*)
	 */
	private function __toSearchString($criteria) {
		$filter = "";

		foreach(array_keys($criteria) as $key) {
			$filter .= "($key=" . $criteria[$key] . ")";	
		}

		return $filter;
	}

	/*
	 * Creates a two dimensional field from the ldap search array returned
	 * from ldap search.
	 */
	private function __parseSearchResult($results, $what) {
		foreach($results as $result) {
			$tmp = array();

			foreach($what as $key) {
				$tmp[$key] = $result[$key][0];
			}

			$formattedResult[] = $tmp;
		}

		// remove the first empty value that [] operator seems to create
		// Matej: prvy je [] lebo results pri iterovani narazi na count:
		/*
      return_value["count"] = number of entries in the result
      return_value[0] : refers to the details of first entry
  
      return_value[i]["dn"] =  DN of the ith entry in the result
  
      return_value[i]["count"] = number of attributes in ith entry
      return_value[i][j] = jth attribute in the ith entry in the result
      
      return_value[i]["attribute"]["count"] = number of values for
                                              attribute in ith entry
      return_value[i]["attribute"][j] = jth value of attribute in ith entry
    */
		array_shift($formattedResult); 

		return $formattedResult;
	}

	/*
	 * Performs an ldap search using the $criteria and fetching only 
	 * $what attributes.
	 */
	public function find($criteria, $what) {
		if(empty($criteria) || empty($what)) {
			throw new LdapException("missing_param");
		}

		$filter = $this->__toSearchString($criteria);

		$lsrch = @ldap_search($this->ldapconn, "ou=People,dc=stuba,dc=sk", $filter, $what, 0, 10);

		if(!$lsrch) {
			throw new LdapException("ldap_search_failed");
		}

		$userdata = ldap_get_entries($this->ldapconn, $lsrch);

		return $this->__parseSearchResult($userdata, $what);

	}

	public function autentificate($user, $password) {
		if(empty($user) || empty($password)) {
			throw new LdapException("missing_param");
		}

		$ldapconn = ldap_connect("ldap.stuba.sk");
    if($ldapconn) {
			$dn = 'uid=' . $user . ',ou=People,dc=stuba,dc=sk';
			$ldapbind = @ldap_bind($ldapconn, $dn, $password);
			if($ldapbind) {
				return true; 
			} else {
				throw new LdapException("login_failed");
			}
		} else {
			throw new LdapException("ldap_connect_failed");
		}

	}
}

?>

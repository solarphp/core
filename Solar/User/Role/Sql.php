<?php

/**
* 
* Get user roles from an SQL database table.
*
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Role
* 
* @author Paul M. Jones <pmjones@solarphp.net>
* 
* @license LGPL
* 
* @version $Id: Sql.php,v 1.5 2005/02/08 03:02:58 pmjones Exp $
* 
*/

/**
* 
* Get user roles from an SQL database table.
*
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Role
* 
*/

class Solar_User_Role_Sql extends Solar_Base {
	
	/**
	* @todo CONVERT TO $CONFIG ARRAY!!!
	*/
	
	/**
	* 
	* How to get the SQL object.
	* 
	* If a string, is treated as a Solar::shared() object name.
	* 
	* Otherwise, is treated as the config for a new Solar_Sql object.
	* 
	* @var mixed
	* 
	*/
	
	protected $sql = null;
	
	
	/**
	* 
	* Name of the table holding role data.
	* 
	* @var string
	* 
	*/
	
	protected $table = 'sc_user_role';
	
	
	/**
	* 
	* Name of the column with the username.
	* 
	* @var string
	* 
	*/
	
	protected $usernameCol = 'user_id';
	
	
	/**
	* 
	* Name of the column with the role value.
	* 
	* @var string
	* 
	*/
	
	protected $rolenameCol = 'role';
	
	
	/**
	* 
	* Get the roles for a user.
	* 
	*/
	
	public function fetch($username)
	{
		// get the SQL object
		if (is_string($this->sql)) {
			// use a shared object.
			$obj = Solar::shared($this->sql);
		} else {
			// instantiate a new object.
			$obj = Solar::object('Solar_Sql', $this->sql);
		}
		
		// if there were errors, return.
		if (! $obj || Solar::isError($obj)) {
			return $obj;
		}
		
		// build the SQL statement
		$stmt =  "SELECT $this->rolenameCol";
		$stmt .= " FROM $this->table";
		$stmt .= " WHERE $this->usernameCol = :username";
		
		// build the placeholder data
		$data = array(
			'username' => $username,
		);
		
		// get the results (a column of rows)
		$result = $obj->fetchCol($stmt, $data);
		
		// done!
		return $result;
	}
}

?>
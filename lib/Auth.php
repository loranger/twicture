<?php

class Auth
{

	private $user;
	private $user_id;
	private $cookie_validity = 3600;

	function __construct()
	{

	}

	function getLoginForm()
	{
		$out = '<fieldset><legend>' . _('Twitter account') . '</legend>'."\n";
		$out .= '<form action="" method="post" id="signin">'."\n";
		$out .= '	<label>' . _('Username') . ' : <input type="text" name="username" value=""/></label><br />'."\n";
		$out .= '	<label>' . _('Password') . ' : <input type="password" name="password" value=""/></label><br />'."\n";
		$out .= '	<input type="submit" id="submit" value="Sign in"/>'."\n";
		$out .= '</form>'."\n";
		$out .= '</fieldset>'."\n";
		return $out;
	}

	function getUser($id = false)
	{
		if( $id )
		{
			$statement = DB()->prepare( 'select * from users where id = :id;' );
			$statement->bindParam( ':id', $id );
			$statement->execute();
			if( $result = $statement->fetch() )
			{
				return $result;
			}
			return false;
		}
		else
		{
			return $this->user;
		}
	}

	function getUserId()
	{
		return $this->user_id;
	}

	function getUsers()
	{
		$statement = DB()->prepare( 'select * from users;' );
		$statement->execute();
		if( $result = $statement->fetchAll() )
		{
			return $result;
		}
		return false;
	}

	function addUser( $username, $password )
	{
		$statement = DB()->prepare( 'insert into users (username, password) values (:username, :password);' );
		$statement->bindParam( ':username', trim( $username ) );
		$statement->bindParam( ':password', sha1( trim( $password ) ) );
		return $statement->execute();
	}

	function findUser( $username )
	{
		$statement = DB()->prepare( 'select id from users where username = :username;' );
		$statement->bindParam( ':username', trim( $username ) );
		$statement->execute();
		if( $result = $statement->fetch() )
		{
			return $result['id'];
		}
		return false;
	}

	function updateUser( $id, $username, $password )
	{
		$statement = DB()->prepare( 'update username set username = :username, password = :password where id = :id;' );
		$datas = array();
		$statement->bindParam( ':id', intval( $id ) );
		$statement->bindParam( ':username', trim( $username ) );
		$statement->bindParam( ':password', sha1( trim( $password ) ) );
		return $statement->execute();
	}

	function checkUser( $username, $password, $useSha1 = true )
	{
		$password = $useSha1 ? sha1( trim( $password ) ) : trim( $password );
		$statement = DB()->prepare( 'select id from users where username = :username AND password = :password;' );
		$statement->bindParam( ':username', trim( $username ) );
		$statement->bindParam( ':password', $password );
		$statement->execute();
		if( $result = $statement->fetch() )
		{
			return $result['id'];
		}
		return false;
	}

	function check()
	{
		$username = ( getParam('username') ? trim( getParam('username') ) : false );
		$password = ( getParam('password') ? sha1( trim( getParam('password') ) ) : false );

		if ( !$password || empty($password) ) {
			$username = ( array_key_exists('user', $_COOKIE) ) ? trim( $_COOKIE['user'] ) : $username;
			$password = ( array_key_exists('auth', $_COOKIE) ) ? trim( $_COOKIE['auth'] ) : $password;
		}

		if ( $id = $this->checkUser( $username, $password, false ) ) {
			$time = time() + $this->cookie_validity;
			setcookie("user", $username, $time, '/');
			setcookie("auth", $password, $time, '/');
			$this->user = $username;
			$this->user_id = $id;
			return true;
		}
		return false;
	}

}

//##### Singleton shortcut function #####
function Auth()
{
	static $auth;
	if ( !$auth )
	{
		$auth = new Auth();
	}
	return $auth;
}

?>
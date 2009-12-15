<?php

class Database extends PDO
{
	function __construct( $sqlite_path )
	{
		try
		{
			parent::__construct( $sqlite_path );
		}
		catch(PDOException $e)
		{
			throw new Exception('<b>'.$e->getMessage().'</b>');
		}
	}

	private function logQuery($method, $args)
	{
		//Logs()->add($args[0]);
		return call_user_func_array(array('parent', $method), $args);
	}

	function query()
	{
		$args = func_get_args();
		$statement = $this->logQuery(__FUNCTION__, $args);
		if (!$statement) {
			$err = $this->errorInfo();
			throw new Exception(get_parent_class($this).': <b>'.$err[2].'</b>');
		}
		return $statement;
	}

	function prepare($dummy_statement, $dummy_array = false)
	{
		$args = func_get_args();
		$statement = $this->logQuery(__FUNCTION__, $args);
		if (!$statement) {
			$err = $this->errorInfo();
			throw new Exception(get_parent_class($this).': <b>'.$err[2].'</b>');
		}
		return $statement;
	}

	function import($file)
	{
		$file = file_get_contents($file);
		$queries = array_map('trim', explode(';', $file));
		foreach($queries as $query)
		{
			if(trim($query) != '')
			{
				$statement = $this->prepare($query.';');
				$statement->execute();
			}
		}
	}

}

//##### Singleton shortcut function #####
function DB()
{
	static $db;
	if ( !$db )
	{
		$db = new Database( PDO_DSN );
	}
	return $db;
}

?>
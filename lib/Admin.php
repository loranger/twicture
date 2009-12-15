<?php

class Admin extends Twicture
{

}

//##### Singleton shortcut function #####
function Admin()
{
	static $admin;
	if ( !$admin )
	{
		$admin = new Admin();
	}
	return $admin;
}

?>
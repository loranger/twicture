<?php

class Install
{

	private $log;
	private $username;
	private $password;

	function __construct($bypass = false)
	{
		$this->log = new InstallLog();
		$this->checkExtensions();
		$this->checkPermissions();
		if (!$bypass) {
			if( !$this->hasError() && $this->checkAuth() )
			{
				$this->initDB( realpath(dirname(__FILE__)).'/schema.sql' );
				if( !Auth()->findUser( $this->username ) )
				{
					if( Auth()->addUser( $this->username, $this->password ) )
					{
						if( !array_key_exists('media', $_FILES) )
						{
							redirect( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
						}
					}
				}
			}
		}
	}

	function checkExtension($ext, $url = false)
	{
		if (!extension_loaded($ext)) {
			if ( !function_exists('dl') || !@dl($ext.'.so')) {
				if($url)
				{
					$msg = sprintf(_('<a href="%s">%s extension</a> is missing'), $url, $ext);
				}
				else
				{
					$msg = sprintf(_('%s extension is missing'), $ext);
				}
				return $this->log->error($msg);
			}
		}
		return $this->log->success( sprintf(_('%s extension found'), $ext) );
	}

	function checkPDO()
	{
		return $this->checkExtension('pdo_sqlite', 'http://php.net/manual/book.pdo.php');
	}

	function checkImagick()
	{
		return $this->checkExtension('imagick', 'http://php.net/manual/book.imagick.php');
	}

	function checkGettext()
	{
		return $this->checkExtension('gettext', 'http://php.net/manual/book.gettext.php');
	}

	function checkCurl()
	{
		return $this->checkExtension('curl', 'http://php.net/manual/book.curl.php');
	}

	function checkXMLWriter()
	{
		return $this->checkExtension('xmlwriter', 'http://php.net/manual/book.xmlwriter.php');
	}

	function checkZip()
	{
		return $this->checkExtension('zip', 'http://php.net/manual/book.zip.php');
	}

	function checkExtensions()
	{
		$this->checkPDO();
		$this->checkImagick();
		$this->checkGettext();
		$this->checkXMLWriter();
		$this->checkZip();
	}

	function getPerms($path)
	{
		return substr(sprintf('%o', fileperms($path)), -4);
	}

	function checkPermissions()
	{
		if (@touch(DATAPATH . DIRECTORY_SEPARATOR . 'write_test')) {
			unlink(DATAPATH . DIRECTORY_SEPARATOR . 'write_test');
			return $this->log->success(_('"data" folder is writable'));
		} else {
			return $this->log->error(sprintf(_('cannot write in "data" folder. Check write permissions (currently %s)'), $this->getPerms(DATAPATH)));
		}
	}

	function hasError()
	{
		return ( count($this->log->getErrors()) > 0 || count($this->log->getWarns()) > 0);
	}

	function checkAuth()
	{
		$this->username = ( getParam('username') ? getParam('username') : '' );
		$this->password = ( getParam('password') ? getParam('password') : '' );
		if ( !empty( $this->username ) && !empty( $this->password ) )
		{
			return true;
		}

		$this->log->warn( Auth()->getLoginForm() );
		return false;
	}

	function initDB($schema_path)
	{
		$infos = pathinfo(DBPATH);

		if (file_exists(DBPATH)) {
			return $this->log->warn(sprintf(_('"%s" database already exists'), $infos['basename']));
		}
		else
		{
			DB()->import($schema_path);
			return $this->log->success(sprintf(_('"%s" database created'), $infos['basename']));
		}
	}

	function initUser($login, $password, $email)
	{
		if( User()->create($login, sha1($password), $email) )
		{
			$this->log->info(sprintf(_('user "%s" added (password is "%s")'), $login, $password));
		}
	}

	function getLogs()
	{
		return $this->log->get();
	}

	function getErrors()
	{
		return $this->log->getErrors();
	}

	function getWarns()
	{
		return $this->log->getWarns();
	}
}

?>
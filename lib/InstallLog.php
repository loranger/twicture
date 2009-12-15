<?php

class InstallLog
{

	private $errors = array();
	private $output = array();

	function __construct()
	{
	}

	function error($msg)
	{
		array_push($this->errors, $msg);
		return $this->log($msg, 'error');
	}

	function success($msg)
	{
		return $this->log($msg, 'success');
	}

	function warn($msg)
	{
		return $this->log($msg, 'warn');
	}

	function info($msg)
	{
		return $this->log($msg, 'info');
	}

	function log($msg, $type = 'log')
	{
		if(!array_key_exists($type, $this->output))
		{
			$this->output[$type] = array();
		}
		array_push($this->output[$type], ucfirst($msg));
		return ($type == 'error') ? false : true;
	}

	function getErrors()
	{
		$out = array();
		if( array_key_exists('error', $this->output) )
		{
			foreach($this->output['error'] as $msg)
			{
				$mask = '<div class="%s">%s</div>';
				$div = sprintf($mask, 'error', $msg);
				array_push($out, $div);
			}
		}
		return $out;
	}

	function getWarns()
	{
		return $this->get('warn');
		$out = array();
		if( array_key_exists('warn', $this->output) )
		{
			foreach($this->output['warn'] as $msg)
			{
				$mask = '<div class="%s">%s</div>';
				$div = sprintf($mask, 'warn', $msg);
				array_push($out, $div);
			}
		}
		return $out;
	}

	function get( $type = false )
	{
		$out = array();
		if( $type && array_key_exists($type, $this->output) )
		{
			foreach($this->output[$type] as $msg)
			{
				$div = sprintf('<div class="%s">%s</div>', $type, $msg);
				array_push($out, $div);
			}
		}
		elseif(!$type)
		{
			foreach($this->output as $type => $array)
			{
				foreach($array as $msg)
				{
					$div = sprintf('<div class="%s">%s</div>', $type, $msg);
					array_push($out, $div);
				}
			}
		}
		return $out;
	}

}

?>
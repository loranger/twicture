<?php

class Page
{

	private $meta = array();
	private $title = 'Twicture';
	private $css = array();
	private $js = array();
	private $scripts = array();
	private $contents = array();

	function __construct()
	{
		$this->addHttpEquiv('Content-Type', 'text/html; charset=utf-8');
	}

	function addHttpEquiv($name, $value)
	{
		$meta = array();
		$meta['http-equiv'] = $name;
		$meta['content'] = $value;
		array_push($this->meta, $meta);
		return $this;
	}

	function addMeta($name, $value)
	{
		$meta = array();
		$meta['name'] = $name;
		$meta['content'] = $value;
		array_push($this->meta, $meta);
		return $this;
	}

	function browserIsIPhone()
	{
		return ( strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== FALSE );
	}

	function addIPhoneHeaders()
	{
		$this->addMeta('viewport', 'width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;');
		$this->addMeta('apple-mobile-web-app-capable', 'yes');
		$this->addMeta('apple-mobile-web-app-status-bar-style', 'black-translucent');
		return $this;
	}

	function setTitle( $title )
	{
		$this->title = trim( $title );
		return $this;
	}

	function addScript( $script )
	{
		array_push( $this->scripts, $script );
		return $this;
	}

	function addJS( $url )
	{
		array_push( $this->js, trim( $url ) );
		return $this;
	}

	function addCSS( $url )
	{
		array_push( $this->css, trim( $url ) );
		return $this;
	}

	function addContent( $content )
	{
		if( is_array( $content ) )
		{
			foreach ($content as $iter) {
				$this->addContent( $iter );
			}
		}
		else
		{
			array_push( $this->contents, trim( $content ) );
		}
		return $this;
	}

	function __destruct()
	{
		echo $this;
	}

	function __toString()
	{
		$out = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'."\n";
		$out .= '	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n\n";
		$out .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">'."\n";
		$out .= '<head>'."\n";
		if ( $this->browserIsIPhone() )
		{
			$this->addIPhoneHeaders();
			$this->addCSS( Twicture()->getURL(true) . 'css/iphone.css' );
		}
		foreach ($this->meta as $meta) {
			$out .= '	<meta';
			foreach ($meta as $attribute => $value) {
				$out .= sprintf(' %s="%s"', $attribute, $value);
			}
			$out .= '/>'."\n\n";
		}
		$out .= '	<title>' . $this->title . '</title>'."\n\n";
		foreach ($this->js as $js) {
			$out .= '	<script type="text/javascript" src="' . $js . '"></script>'."\n";
		}
		foreach ($this->css as $css) {
			$out .= '	<link rel="stylesheet" type="text/css" href="' . $css . '" media="all"/>'."\n";
		}
		if( count( $this->scripts ) )
		{
			$out .= '	<script type="text/javascript">'."\n";
			foreach ($this->scripts as $script) {
				$out .= "\t\t".$script."\n";
			}
			$out .= '	</script>'."\n";
		}
		$out .= '</head>'."\n\n";
		$out .= '<body>'."\n";
		$out .= '<div id="content">'."\n";
		foreach ($this->contents as $content) {
			$out .= $content."\n";
		}
		$out .= '</div>'."\n";
		if ( !$this->browserIsIPhone() && Twicture()->isInstalled() )
		{
			$footer[_('list')] = Twicture()->getURL(true);
			if( Twicture()->isInstalled() )
			{
				foreach (Auth()->getUsers() as $user) {
					$footer['@'.$user['username']] = sprintf('http://twitter.com/%s', $user['username']);
				}
			}
			$footer[_('help')] = Twicture()->getURL(true).'help/';
			$out .= '<div id="footer">'."\n";
			foreach ($footer as $label => $url) {
				$out .= sprintf('<a href="%s">%s</a>', $url, $label)."\n";
			}
			$out .= '</div>'."\n";
		}
		$out .= '</body>'."\n";
		$out .= '</html>';
		return $out;
	}

}

//##### Singleton shortcut function #####
function Page()
{
	static $page;
	if ( !$page )
	{
		$page = new Page();
	}
	return $page;
}

?>
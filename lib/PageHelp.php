<?php

class PageHelp extends Page
{

	function __construct()
	{
		parent::__construct();

		$this->setTitle( _('Help') );

		$help[_('Image Service API Endpoint')] = Twicture()->getURL() . "<br /><br />\n";
		$help[_('Image Service API Endpoint')] .= _('API endpoint requires at least "username", "password" and "media" datas to store the uploaded image. "message" data is optional.')  . "<br />\n";
		$help[_('Image Service API Endpoint')] .= _('A valid endpoint post will return a "mediaurl" node containing the location of the uploaded image. Shortened or not, depending on the current Twicture settings.')  . "<br />\n";

		$help[_('View image')] = sprintf( _('%s[IMAGE ID]<br /><br />Message sent with the image will be shown as a tweet under the picture.'), Twicture()->getViewURL() );

		$help[_('Control Panel')] = Twicture()->getAdminURL();
		$dummyInstall = new Install(true);
		$logs = '';
		foreach($dummyInstall->getLogs() as $log)
		{
			$logs .= $log;
		}

		$help[_('Requirements')] = $logs;
		$out = '';
		foreach ($help as $label => $text) {
			$out .= sprintf( '<h3>%s</h3><p>%s</p>', $label, $text);
		}
		$this->addContent( '<div id="help">'.$out.'</div>' );

	}




}

//##### Singleton shortcut function #####
function PageHelp()
{
	static $page;
	if ( !$page )
	{
		$page = new PageHelp();
	}
	return $page;
}

?>
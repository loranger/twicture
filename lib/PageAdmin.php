<?php

class PageAdmin extends Page
{

	function __construct()
	{
		parent::__construct();

		$this->setTitle( _('Control Panel') );
		$this->addJS('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js');

		if( !Auth()->check() )
		{
			$this->addContent( Auth()->getLoginForm() );
		}
		else
		{

			$this->addScript('$(document).ready(function(){');
			$this->addScript('	$("a.delete").click(function(){');
			$this->addScript('		return confirm("' . _('Sure you want to delete this picture?') . '");');
			$this->addScript('	});');
			$this->addScript('	$("a.empty").click(function(){');
			$this->addScript('		return confirm("' . _('Sure you want to delete all those pictures?') . '");');
			$this->addScript('	});');
			$this->addScript('});');

			$this->addContent( '<h3>' . _('Control Panel') . '</h3>' );

			$pictures = Twicture()->getPictures();

			foreach ($pictures as $picture) {
				$link = '<a href="'.$picture->getDeleteURL().'" class="pic delete">'."\n";
				$link .= '	<img src="'.Twicture()->getDataURL().$picture->getThumbFilename().'.png" alt="'.$picture->getThumbFilename().'"/>'."\n";
				$link .= '	<div>'._('Delete').'</div>'."\n";
				$link .= '</a>'."\n";
				$this->addContent($link);
			}

			if( count($pictures) )
			{
				$out = '<div id="more">'."\n";
				$out .= sprintf('	<a href="%s" class="%s">%s</a>', Twicture()->getBackupURL(true), 'backup', _('Backup all pictures') )."\n";
				$out .= sprintf('	<a href="%s" class="%s">%s</a>', Twicture()->getEmptyURL(true), 'empty', _('Delete all pictures') )."\n";
				$out .= '</div>'."\n";
				$this->addContent($out);
			}

		}
	}

}

//##### Singleton shortcut function #####
function PageAdmin()
{
	static $page;
	if ( !$page )
	{
		$page = new PageAdmin();
	}
	return $page;
}

?>
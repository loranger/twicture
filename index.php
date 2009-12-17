<?php

require_once('lib/require.php');

$css = Twicture()->getURL(true) . 'css/style.css';

if( !Twicture()->isInstalled() )
{
	$install = new Install();
	if( $install->hasError() )
	{
		Page()->addCss( $css );
		$errors = $install->getErrors();
		if( count($errors) )
		{
			$out = '';
			foreach ($errors as $error) {
				$out .= $error;
			}
			Page()->addContent(sprintf( '<div id="error"><h3>%s</h3>%s</div>', ngettext( _('Error'), _('Errors'), count($errors)), $out) );
		}
		$warns = $install->getWarns();
		if( count($warns) )
		{
			$out = '';
			foreach ($warns as $warn) {
				$out .= $warn;
			}
			Page()->addContent( $out );
		}

		die();
	}
}

if( array_key_exists('media', $_FILES) )
{
	$upload = new Upload( $_FILES['media'] );

	if ( !$upload->hasError() && !Auth()->checkUser( getParam('username'), getParam('password') ) )
	{
		$upload->addError( 1001, _('Invalid twitter username or password') );
	}
	else if( $picture = Twicture()->addPicture( $upload->getFile() ) )
	{
		$upload->writeElement( 'mediaurl', $picture->getShortenURL() );
	}
	else
	{
		$upload->addError( 1007, _('Failed to add image to Twicture') );
	}

	echo $upload->getXML();
	die();
}

switch( getParam('action') )
{
	case 'view':
		$picture = Twicture()->getPictureFromName( getParam('item') );

		if($picture)
		{
			Page()->setTitle( $picture->getUserName() );
			Page()->addCss( $css );
			$pic = '<img src="'.$picture->getImageURL(true).'" alt="'.$picture->getFilename().'" id="picture"/>'."\n";
			Page()->addContent($pic);
			if ( !Page()->browserIsIPhone() )
			{
				if( $picture->getMessage() )
				{
					Page()->addContent( sprintf('<div id="message" style="width: %dpx;">&#x275D; %s &#x275E;</div>', $picture->getWidth(), $picture->getMessage() ) );
				}
				Page()->addContent( sprintf('<div id="date" style="width: %dpx;">%s</div>', $picture->getWidth(), $picture->getDate() ) );
			}
		}
		else
		{
			Page()->setTitle( _('Huh ?') );
			Page()->addCss( $css );
			Page()->addContent( sprintf( '<div id="notfound"><h1>%s</h1></div>', _('Picture not found') ) );
		}
		break;
	case 'help':
		PageHelp()->addCss( $css );
		break;
	case 'backup':
	case 'delete':
	case 'empty':
	case 'admin':
		if( !Auth()->check() )
		{
			PageAdmin()->addCss( $css );
		}
		else
		{
			switch( getParam('action') )
			{
				case 'backup':
					return Twicture()->getBackup();
					break;
				case 'delete':
					$picture = Twicture()->getPictureFromName( getParam('item') );
					if( $picture )
					{
						$picture->delete();
					}
					redirect( Twicture()->getAdminURL() );
					break;
				case 'empty':
					foreach ( Twicture()->getPictures() as $picture ) {
						$picture->delete();
					}
					redirect( Twicture()->getAdminURL() );
					break;
				default:
					PageAdmin()->addCss( $css );
					break;
			}
		}
		break;
	default:
		$pictures = Twicture()->getPictures();

		Page()->addCss( $css );
		foreach ($pictures as $picture) {
			$link = '<a href="'.$picture->getURL().'">'."\n";
			$link .= '<img src="data/'.$picture->getThumbFilename().'.png" alt="'.$picture->getThumbFilename().'"/>'."\n";
			$link .= '</a>'."\n";
			Page()->addContent($link);
		}
		break;
}

?>
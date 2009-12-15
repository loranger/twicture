<?php

class Upload extends XMLWriter
{
	private $file;

	function __construct( $post_file )
	{
		$this->file = $post_file;

		$this->openMemory();
		$this->checkErrors();
	}

	function getFile()
	{
		return $this->file['tmp_name'];
	}

	function isImage()
	{
		return @getimagesize( $this->getFile() );
	}

	function addError( $code, $message )
	{
		$this->startElement( 'err' );
		$this->writeAttribute( 'code', $code );
		$this->writeAttribute( 'msg', $message );
		$this->endElement();
	}

	function hasError()
	{
		return ( $this->file['error'] > 0 );
	}

	function checkErrors()
	{
		if ( !$this->hasError() && !$this->isImage() )
		{
			$this->file['error'] = UPLOAD_ERR_EXTENSION;
		}

		if( $this->hasError() )
		{
			switch ( $this->file['error'] ) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$this->addError( 1004, sprintf( _('Image larger than %s'), ini_get('post_max_size') ) );
					break;
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE:
					$this->addError( 1002, _('Image not found or partially uploaded') );
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
				case UPLOAD_ERR_CANT_WRITE:
					$this->addError( 1005, _('Failed to write image to disk') );
					break;
				case UPLOAD_ERR_EXTENSION:
					$this->addError( 1003, _('Invalid image type') );
					break;
				default:
					$this->addError( 1006, _('Unknown upload error') );
			}
		}
	}

	function getXML()
	{
		return $this->flush();
	}

}

?>
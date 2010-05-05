<?php

class Twicture
{

	private $pictures = array();

	function __construct()
	{
	}

	function isInstalled()
	{
		return ( is_writable( DATAPATH ) && file_exists( DBPATH ) );
	}

	function getURL( $relative = false )
	{
		$url = '';
		if( !$relative )
		{
			$url .= 'http://' . $_SERVER['HTTP_HOST'];
		}
		$url .= dirname( $_SERVER['PHP_SELF'] );
		if( !empty( $url ) )
		{
			$url .=  '/';
		}
		return $url;
	}

	function getAdminURL( $relative = false )
	{
		return $this->getURL( $relative ).'admin/';
	}

	function getViewURL( $relative = false )
	{
		return $this->getURL( $relative ).'view/';
	}

	function getDataURL( $relative = false )
	{
		return $this->getURL( $relative ).'data/';
	}

	function getDeleteURL( $relative = false )
	{
		return $this->getURL( $relative ).'delete/';
	}

	function getBackupURL( $relative = false )
	{
		return $this->getURL( $relative ).'backup/';
	}

	function getEmptyURL( $relative = false )
	{
		return $this->getURL( $relative ).'empty/';
	}

	function addPicture( $file )
	{

		$im = new Imagick($file);

		$extension = strtolower( $im->getImageFormat() );
		/*
		$exif = $im->getImageProperties();
		$date = ( array_key_exists( 'exif:DateTime', $exif) ) ? strtotime( $exif['exif:DateTime'] ) : time();
		$filename = $date;
		*/
		$date = time();
		$filename = $date;

		$statement = DB()->prepare( 'insert into post (user, filename, extension, message, date) values (:user, :filename, :extension, :message, :date);' );

		$data = array(
					':user' => Auth()->findUser( getParam('username') ),
					':filename' => trim( $filename ),
					':extension' => trim( $extension ),
					':message' => trim( getParam('message') ),
					':date' => $date);

		if( $statement->execute( $data ) )
		{
			$picture = $this->getPicture( DB()->lastInsertId() );
			$picture->import( $file );
			return $picture;
		}

		return false;
	}

	function getPictureFromName( $name )
	{
		$statement = DB()->prepare( 'select id from post where filename = :filename;' );
		$statement->bindParam( ':filename', trim( $name ) );
		$statement->execute();
		if( $result = $statement->fetch() )
		{
			return $this->getPicture( $result['id'] );
		}
		return false;
	}

	function getPicture( $id )
	{
		if( !array_key_exists($id, $this->pictures) )
		{
			$this->pictures[$id] = new Picture($id);
		}
		return $this->pictures[$id];
	}

	function getPictures()
	{
		$statement = DB()->prepare( 'select id from post order by id desc;' );
		$statement->execute();

		$photos = array();
		while ($result = $statement->fetch(PDO::FETCH_ASSOC) ) {
			$photo = $this->getPicture( $result['id'] );
			array_push( $photos, $photo );
		}
		return $photos;
	}

	function getBackup()
	{
		$pics = $this->getPictures();

		$zip = new ZipArchive();
		$filename = Auth()->getUser().'.zip';
		$filepath = DATAPATH . '/tmp.zip';

		$res = $zip->open($filepath, ZIPARCHIVE::OVERWRITE);
		foreach ($pics as $pic) {
			$zip->addFile( DATAPATH . '/' . $pic->getFilename().'.'.$pic->getExtension(), $pic->getFilename().'.'.$pic->getExtension() );
		}
		$zip->close();

		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($filepath) );
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, must-revalidate");
		readfile($filepath);
		@unlink($filepath);
	}

}

//##### Singleton shortcut function #####
function Twicture()
{
	static $twicture;
	if ( !$twicture )
	{
		$twicture = new Twicture();
	}
	return $twicture;
}

?>
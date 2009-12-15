<?php

class Picture
{

	private $thumb;

	function __construct( $id )
	{
		$st = DB()->prepare( 'select * from post where id = :id;' );
		$st->bindParam(':id', $id);
		if( $st->execute() )
		{
			$st->setFetchMode(PDO::FETCH_INTO, $this);
			$result = $st->fetch();
			return $result;
		}
		return false;
	}

	function import( $file )
	{
		@copy($file, DATAPATH . DIRECTORY_SEPARATOR . $this->filename . '.' . $this->extension);

		$img = new Imagick( DATAPATH . DIRECTORY_SEPARATOR . $this->filename . '.' . $this->extension );
		$exif = $img->getImageProperties();
		if ( array_key_exists( 'exif:Orientation', $exif) )
		{
			switch( $exif['exif:Orientation'] )
			{
				case 1:
					break;
				case 3:
					$img->rotateImage(new ImagickPixel(), 180);
					break;
				case 6:
					$img->rotateImage(new ImagickPixel(), 90);
					break;
				case 8:
					$img->rotateImage(new ImagickPixel(), -90);
					break;
			}
		}
		$img->resizeImage(480, 480, imagick::FILTER_LANCZOS, 1, true);

		$statement = DB()->prepare( 'update post set width = :width where id = :id;' );
		$statement->bindParam( ':id', intval( $this->id ) );
		$statement->bindParam( ':width', $img->getImageWidth() );
		$statement->execute();

		$img->writeImage();

		$this->buildThumbnail();
	}

	function delete()
	{
		$statement = DB()->prepare( 'delete from post where id = :id;' );
		$statement->bindParam( ':id', intval( $this->id ) );
		if( $statement->execute() )
		{
			$files = glob( DATAPATH . DIRECTORY_SEPARATOR . $this->filename . '*' );
			foreach ($files as $file) {
				@unlink($file);
			}
			return true;
		}
		return false;
	}

	function buildThumbnail()
	{
		$img = new Imagick( DATAPATH . DIRECTORY_SEPARATOR . $this->filename . '.' . $this->extension );
		$img->cropThumbnailImage(80,80);

		$img->borderImage( new ImagickPixel('black'), 3, 3 );

		$circle = new Imagick();
		$circle->newImage( $img->getImageWidth(), $img->getImageHeight(), new ImagickPixel('transparent'), 'png');
		$draw = new ImagickDraw();
		$draw->setFillColor( "#4096EE" );
		$draw->circle( $circle->getImageWidth()/2, $circle->getImageHeight()/2, $circle->getImageWidth()/2, $circle->getImageHeight() );
		$circle->drawImage( $draw );
		$circle->resizeImage( $img->getImageWidth(), ($img->getImageHeight() / 20), Imagick::FILTER_LANCZOS, 1 );

		$shadow = $circle->clone();
		$circle->destroy();
		$shadow->setImageBackgroundColor( new ImagickPixel( 'gray' ) );
		$shadow->shadowImage( 80, 3, 5, 5 );

		$thumb = new Imagick();
		$space = 5;
		$thumb->newImage( $shadow->getImageWidth(), ($img->getImageHeight() + $shadow->getImageHeight() + $space ), new ImagickPixel('transparent'), 'png');
		$thumb->compositeImage( $shadow, imagick::COMPOSITE_OVER, 0, $img->getImageHeight() + $space );
		$thumb->compositeImage( $img, imagick::COMPOSITE_OVER, ($shadow->getImageWidth() - $img->getImageWidth())/2, 0 );
		$img->destroy();
		$shadow->destroy();

		$thumb->writeImage( DATAPATH . DIRECTORY_SEPARATOR . $this->getThumbFilename() . '.' . $thumb->getImageFormat() );
	}

	function getUserName()
	{
		if ( $user = Auth()->getUser( intval($this->user) ) )
		{
			return $user['username'];
		}
		return false;
	}

	function getFilename()
	{
		return $this->filename;
	}

	function getExtension()
	{
		return $this->extension;
	}

	function getMessage()
	{
		if( trim($this->message) != '' )
		{
			return trim($this->message);
		}
		return false;
	}

	function getDate()
	{
		return ucfirst(getRelativeDate($this->date));
	}

	function getWidth()
	{
		return $this->width;
	}

	function getThumbFilename()
	{
		if( !$this->thumb )
		{
			$this->thumb = $this->filename.'_thumb';

			if( !file_exists( DATAPATH . DIRECTORY_SEPARATOR . $this->thumb . '.png' ) )
			{
				$this->buildThumbnail();
			}
		}
		return $this->thumb;
	}

	function getImageURL( $relative = false )
	{
		return sprintf('%sdata/%s.%s', Twicture()->getURL( $relative ), $this->getFilename(), $this->getExtension() );
	}

	function getURL( $relative = false )
	{
		return Twicture()->getViewURL( $relative ) . $this->filename;
	}

	function getDeleteURL( $relative = false )
	{
		return Twicture()->getDeleteURL( $relative ) . $this->filename;
	}

	function getShortenURL()
	{
		$service = 'http://is.gd/api.php?longurl='.urlencode( $this->getURL() );
		$url = @file_get_contents($service);
		if( !$url )
		{
			$url = $this->getURL();
		}
		return $url;
	}

}


?>
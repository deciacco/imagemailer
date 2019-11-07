<?php

//
// class.originaldropshadow.php
// version 1.0.0, 22nd May, 2003
//
// License
//
// PHP class to create thumbnails of images and/or to add a drop shadow effect.
//
// Copyright (C) 2002 Andrew Collington, php@amnuts.com, http://php.amnuts.com/
//
// This program is free software; you can redistribute it and/or modify it under
// the terms of the GNU General Public License as published by the Free Software
// Foundation; either version 2 of the License, or (at your option) any later
// version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License along with
// this program; if not, write to the Free Software Foundation, Inc., 59 Temple
// Place - Suite 330, Boston, MA 02111-1307, USA.
//
// Description
//
// This class is an extension of the shadow class file found on the website
// http://php.amnuts.com/.  It allows you to emulate the version 1.x version of
// the dropshadow class by using the newer version 2.x.
//
// Requirements
//
// PHP 4.1.0+, GD 2.0.1+
//
// Andrew Collington, 2003
// php@amnuts.com, http://php.amnuts.com/
//


class originalDropShadow extends dropShadow
{
	var $_size;
	var $_type;

	function setDebugging($do = FALSE)
	{
		$this->_showDebug = ($do ? TRUE : FALSE);
	}

	function setImageType($type = '')
	{
		if ($type != "jpg" && $type != "png") $this->_type = "png";
		else $this->_type = $imgtype;
		if ($this->_type == 'jpg') $this->_type = 'jpeg';
	}

	// set maximum size of image, width or height - whichever is greater
	// 0 = no resizing
	function setImageSize($size = 0)
	{
		$this->_size = $size;
	}

	// a wrapper for the createDropShadow function that forces it not to create the drop-shadow
	// $input      = directory/filename you wish to save to
	// $output     = directory/filename you wish to save to
	// $background = array of ints representing the RGB value, eg, array(255,255,255) - white
	// $isstring   = set to 1 if the $input will be a string representing the image, as you
	//               might draw from a database.
	function createThumbnail($input = '', $output = '', $isstring = FALSE)
	{
		$this->createDropShadow($input, $output, array(255,255,255), $isstring, 1);
	}

	// does the grunt work of putting all the images together
	// $input      = directory/filename you wish to save to
	// $output     = directory/filename you wish to save to
	// $background = array of ints representing the RGB value, eg, array(255,255,255) - white
	// $isstring   = set to 1 if the $input will be a string representing the image, as you
	//               might draw from a database.
	function createDropShadow($input = '', $output = '', $background = array(), $isstring = FALSE, $withoutshadow = 0)
	{
		// load the image
		$ok = FALSE;
		if ($isstring) $ok = $this->loadImageFromString($input);
		else $ok = $this->loadImage($input, $this->_type);
		if ($ok == FALSE)
		{
			$this->_debug('createDropShadow', 'The image could not be loaded.');
			return FALSE;
		}

		// resize the image
		if ($this->_size)
		{
			$sizes = @GetImageSize($input);
			if ($sizes[0] > $sizes[1]) $this->resizeToSize(0, $this->_size);
			else $this->resizeToSize($this->_size, 0);
		}
		
		// apply dropshadow and/or save
		if (!$withoutshadow)
		{
			$this->applyShadow(dechex($background[0]) . dechex($background[1]) . dechex($background[2]));
			$this->saveShadow($output, $this->_type, 90);
		}
		else
		{
			$this->saveFinal($output, $this->_type, 90);
		}
	}

}


?>

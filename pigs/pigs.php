<?php
/* 
 * -------------------------------------------------------
 * P.I.G.S
 * -------------------------------------------------------
 * 
 * @Version: 4.0.0
 * @Author:  Julian Richen
 * @Link:    http://www.firedartstudios.com/labs/pigs
 * @GitHub:  https://github.com/FireDart/pigs
 * @License: The MIT License (MIT)
 * 
 * P.I.G.S is a PHP Image Grabbing Script that allows you 
 * to create powerful raw galleries. P.I.G.S easily allows 
 * you to grabs and displays images from a selected 
 * directory, creates sized thumbnail of each image, and 
 * use a custom Content Viewer(Lightbox) & Ajax Pagination 
 * Plug-in.
 *
 * -------------------------------------------------------
 * Requirements / Minimum
 * -------------------------------------------------------
 * - PHP 5.3.0
 * You get the entire script + JSON support as an output.
 * 
 * -------------------------------------------------------
 * Requirements / Recommended
 * -------------------------------------------------------
 * - PHP 5.5.0
 * - Imagemagick
 * You get the entire script + the full power of PHP 5.5.0 
 * and the image processing speed of Imagemagick.
 * 
 * -------------------------------------------------------
 * Support File Types
 * -------------------------------------------------------
 * jpg, jpeg, gif/transparent gif, png/transparent png
 * 
 * -------------------------------------------------------
 * Usage
 * -------------------------------------------------------
 * Include the pigs.class.php file into your page
 * include('path/to/pigs/dir/pigs.php');
 * Call pigs
 * $pigs = new Pigs();
 * 
 * Simple array of images with no modifications & 200px thumbs
 * echo $pigs->dir("path/to/images/")->render();
 * 
 * Simple gallery with resize and custom thumbs
 * echo $pigs->dir("path/to/images/")->size(1024)->thumb(150)->render("gallery");
 * 
 * For more options append them after $pigs-> and before ->render()
 * All options
 * ->dir("path/to/images/") // Path to images to process
 * ->size(1024) // Image resize size
 * ->thumb(200) // Thumbnail size
 * ->originals(true) // Keep original images
 * ->pagination(true) // Enable Pagination
 * ->limit(9) // Amount of images per page
 * ->pattern("%gallery%/%page%") // Create custom url example.tld/gallery/demo/10
 * ->base("example.tld/gallery/") // Base for custom url
 * ->current(<get dynamic maybe?>) // Current page number for custom url
 * ->cache(true) // Cache the gallery for faster rendering
 * ->render("gallery"); // Render
 * 
 */

class PigsException extends Exception { }

class Pigs {
	/*
	 * Set Default Values
	 */
	public $extensions = array('jpg', 'jpeg', 'png', 'gif');
	public $size       = false;
	public $thumb      = 100;
	public $originals  = false;
	public $pagination = false;
	public $limit      = 10;
	public $current    = false;
	public $id         = null;
	public $cache      = true;
	protected $pattern = false;
	public $base       = null;
	
	/* 
	 * __construct
	 * 
	 * Get some global stuff set
	 * Fix bad jpeg's and prevent errors
	 * 
	 * @param void
	 * @return void
	 */
	public function __construct() {
		try {
			if(version_compare(phpversion(), '5.3.0') < 0) {
				throw new PigsException('PHP 5.3.0 or greater is needed for minimum usage, however PHP 5.5 is recommended.');
			}
		} catch (PigsException $e) {
			echo '[PIGS] ' . $e->getMessage();
		}
		ini_set('gd.jpeg_ignore_warning', true);
	}
	
	/* 
	 * dir
	 * 
	 * Find the directory
	 * 
	 * @param array $dir Directory to use
	 * @return $this
	 */
	public function dir($dir) {
		/* Clean-up the path & make new ones */
		$this->dir                = rtrim($dir, '/') . '/';
		$this->dir_thumbs         = $this->dir . 'thumbs/';
		$this->dir_originals      = $this->dir . 'originals/';
		return $this;
	}
	public function size($data) {
		$this->size = (is_int($data)) ? $data : false;
		return $this;
	}
	public function thumb($data) {
		$this->thumb = (is_int($data)) ? $data : $this->thumb;
		return $this;
	}
	public function originals($data) {
		$this->originals = (is_bool($data)) ? $data : false;
		return $this;
	}
	public function pagination($data) {
		$this->pagination = (is_bool($data)) ? $data : false;
		return $this;
	}
	public function limit($data) {
		$this->limit = (is_int($data)) ? $data : false;
		return $this;
	}
	public function pattern($data) {
		$this->pattern = $data;
		return $this;
	}
	public function base($data) {
		$this->base = $data;
		return $this;
	}
	public function current($data) {
		$this->current = $data;
		return $this;
	}
	public function id($data) {
		$this->id = $data;
		return $this;
	}
	/* 
	 * cache
	 * 
	 * Cache the folder?
	 * 
	 * @param boolean $cache Cache the folder?
	 * @return $this
	 */
	public function cache($data) {
		$this->cache = (is_bool($data)) ? $data : false;
		return $this;
	}
	
	/* 
	 * render
	 * 
	 * This returns data
	 * 
	 * @param string $return array or list
	 * @return mixed|array Return either an array of the content or styled content
	 */
	public function render($type = "array") {
		try {
			/* if directory is empty then kill everything */
			if(!isset($this->dir) OR $this->dir == "") {
				throw new PigsException('The "' . $this->dir . '" directory is empty.');
			} else {
				if(!file_exists($this->dir) OR !is_dir($this->dir)) {
					throw new PigsException('The "' . $this->dir . '" does not exist.');
				}
			}
			/* Make thumb directory */
			$this->makeDirectory($this->dir_thumbs);
			if($this->originals == true) {
				/* Make originals directory */
				$this->makeDirectory($this->dir_originals);
			}
			/* Check custom pattern */
			if($this->pattern != null) {
				if($this->current === false) {
					throw new PigsException('The current() method is needed when using the pattern() method.');
				}
				if($this->base === null) {
					throw new PigsException('The base() method is needed when using the pattern() method.');
				}
			}		
			/* Images */
			$images = $this->generateList($this->dir);
			/* Cache exist? */
			if($this->cache == true) {
				if(file_exists($this->dir . 'pigs.txt')) {
					$cacheContents = file_get_contents($this->dir . 'pigs.txt');
					$cacheContents = explode("|", $cacheContents);
					if($cacheContents[0] != $this->size OR $cacheContents[1] != $this->thumb) {
						$this->processImages($this->dir, $this->dir_thumbs, $this->dir_originals, $images, $this->size, $this->thumb, $this->originals);
						file_put_contents($this->dir . 'pigs.txt', $this->size . '|' . $this->thumb);
					}
				} else {
					$this->processImages($this->dir, $this->dir_thumbs, $this->dir_originals, $images, $this->size, $this->thumb, $this->originals);
					file_put_contents($this->dir . 'pigs.txt', $this->size . '|' . $this->thumb);
				}
			} else {
				$this->processImages($this->dir, $this->dir_thumbs, $this->dir_originals, $images, $this->size, $this->thumb, $this->originals);
				file_put_contents($this->dir . 'pigs.txt', $this->size . '|' . $this->thumb);
			}
			/* Create a gallery id if it does not exist */
			if($this->id == null) {
				$gallery_id = basename($this->dir, '/');
			} else {
				$gallery_id = $this->id;
			}
			/* Fix gallery id if it does not fit the naming convention */
			$gallery_id = strtolower(str_replace(" ", "_", $gallery_id));
			if(strlen($gallery_id) > 50) {
				$gallery_id = substr($gallery_id, 0, 50);
			}
			/* Current Page? */
			if($this->current === false) {
				if(isset($_GET[$gallery_id])) {
					$this->current = (int) $_GET[$gallery_id];
				} else {
					$this->current = 0;
				}
			}
			if($this->current > 0) {
				$this->current = $this->current - 1;
			}
			$this->current  = $this->current * $this->limit;
			/* Generate the output */
			$data = array(
				'directory' => array(
					'path' => $this->dir,
					'thumbs' => $this->dir_thumbs,
					'originals' => $this->dir_originals,
					),
				'images' => $images,
				'pagination'  => array(
					'enable'  => $this->pagination,
					'id'      => $gallery_id,
					'total'   => $this->total,
					'limit'   => $this->limit,
					'current' => $this->current,
					'pattern' => $this->pattern,
					'base'    => $this->base,
					),
				);
				
			if($type == "gallery") {
				if(!empty($data['images'])) {
					/* Generate Paged array */
					if($this->pagination == true) {
						$images = array_splice($data['images'], $this->current, $this->limit);
					}
					/* Create UI list of images with ID */
					$gallery = '<div class="pigs_gallery" id="' . $gallery_id . '">' . "\n";
					$gallery .= '	<ul class="pigs_images">' . "\n";
					foreach($images as $image) {
						if($this->originals == true) {
							$data_orignal = ' data-original="' . $this->base . $this->dir_originals . $image . '"';
						} else {
							$data_orignal = '';
						}
						$gallery .= '		<li><a href="' . $this->base . $this->dir . $image . '" target="_blank"' . $data_orignal . '><img alt="' . $this->base . $image . '" src="' . $this->base . $this->dir_thumbs . $image . '" /></a></li>' . "\n";
					}
					$gallery .= '	</ul>' . "\n";
					/* Set parameters for pagination */
					if($this->pagination == true) {
						$gallery .= $this->generatePagination($gallery_id);
					}
					$gallery .= '</div>' . "\n";
				} else {
					$gallery = "No Images";
				}
			} elseif($type == "json") {
				$gallery = json_encode($data);
			} else {
				$gallery = $data;
			}
			return $gallery;
		} catch (PigsException $e) {
			echo '[PIGS] ' . $e->getMessage();
		}
	}
	
	/*
	 * generateList
	 * 
	 * Creates an array all the images in a directory
	 * 
	 * @param string $directory Path to the images to manipulate
	 * @return array $images List of all the images
	 */
	protected function generateList($directory) {
		if(!is_readable($directory)) {
			throw new PigsException('The "' . $directory . '" directory is not readable.');
		}
		$handle = opendir($directory);
		$images = array();
		while(false !== ($file = readdir($handle))) {
			/* Get file extension */
			$extension = explode('.', $file);
			$extension = end($extension);
			$extension = strtolower($extension);
			
			/* Only get allow filetype images */
			if(in_array($extension, $this->extensions)) {
				$images[] = $file;
			}
		}
		sort($images);
		/* Count the total images */
		$this->total = count($images);
		/* Return array of images */
		return $images;
	}
	
	/*
	 * createDirectoryList
	 * 
	 * Creates an array all the images in a directory
	 * 
	 * @param string $directory Path to the images to manipulate
	 * @return void
	 */
	private function makeDirectory($directory) {
		if(!is_writable($directory)) {
			throw new PigsException('The "' . $directory . '" directory is not writeable.');
		}
		if(!is_readable($directory)) {
			throw new PigsException('The "' . $directory . '" directory is not readable.');
		}
		if(!is_dir($directory)) {
			$umask = umask(0);
			if(!mkdir($directory, 0775, true)) {
				throw new PigsException('Failed to create the "' . $directory . '" directory, check permissions.');
			}
			umask($umask);
			chmod($directory, 0775);
		}
	}
	
	/*
	 * manipulateImage
	 * 
	 * Can resize or create thumbnails of images
	 * 
	 * @param string  $original What image do we manipulate?
	 * @param string  $destination Where does it go?
	 * @param int     $desired_width With?
	 * @param boolean $thumb Is it a thumb?
	 * @return void Creates the image in that specified directory
	 */
	public function manipulateImage($original, $destination, $desired_width, $thumb = false) {
		/* Can we use imagemagick? */
		if(extension_loaded('imagick')) {
			$im = new Imagick();
			$im->readImage($original);
			/* Make thumb? */
			if($thumb == true) {
				$im->cropThumbnailImage($desired_width, $desired_width);
			} else {
				$im->resizeImage($desired_width, null, Imagick::FILTER_LANCZOS, 1);
			}
			/* Create it */
			$im->writeImage($destination);
			/* Free */
			$im->destroy();
		} else {
			/* Find Image Type */
			$extension = getimagesize($original);
			$extension = strtolower($extension['mime']);
			
			/* Create an Image from it */
			switch($extension) {
				case 'image/jpg':
					$image = imagecreatefromjpeg($original);
					break;
				case 'image/jpeg':
					$image = imagecreatefromjpeg($original);
					break;
				case 'image/gif':
					$image = imagecreatefromgif($original);
					break;
				case 'image/png':
					$image = imagecreatefrompng($original);
					break;
			}
			
			/* Find the width & height of the image */
			$width  = imagesx($image);
			$height = imagesy($image);

			/* Calculate measurements */
			if($thumb == true) {
				if($width > $height) {
					/* For landscape images */
					$x_offset    = ($width - $height) / 2;
					$y_offset    = "0";
					$square_size = $width - ($x_offset * 2);
				} else {
					/* For portrait and square images */
					$x_offset    = "0";
					$y_offset    = ($height - $width) / 2;
					$square_size = $height - ($y_offset * 2);
				}
				
				$width  = $square_size;
				$height = $square_size;
				
				$new_width  = $desired_width;
				$new_height = $desired_width;
			} else {
				$ratio  = $width/$height;
			
				if($width > $height) {
					/* For landscape images */
					$new_width  = $desired_width;
					$new_height = $desired_width / $ratio;

				} else {
					/* For Portrait images */
					$new_height = $desired_width;
					$new_width  = $desired_width * $ratio;
				}
				
				$x_offset = 0;
				$y_offset = 0;
			}
			
			/* Create True Image Color */
			$virtual_image = imagecreatetruecolor($new_width, $new_height);

			/* Preserve Transparency */
			if($extension == "image/gif" OR $extension == "image/png"){
				imagecolortransparent($virtual_image, imagecolorallocatealpha($virtual_image, 0, 0, 0, 127));
				imagealphablending($virtual_image, false);
				imagesavealpha($virtual_image, true);
			}
			
			/* Resize Image */
			imagecopyresampled($virtual_image, $image, 0, 0, $x_offset, $y_offset, $new_width, $new_height, $width, $height);
			
			/* Now we finish the process and deliver it to its destination */
			switch($extension) {
				case 'image/jpg':
					imagejpeg($virtual_image, $destination, 100);
					break;
				case 'image/jpeg':
					imagejpeg($virtual_image, $destination, 100);
					break;
				case 'image/gif':
					imagegif($virtual_image, $destination);
					break;
				case 'image/png':
					imagepng($virtual_image, $destination);
					break;
			}
			imagedestroy($virtual_image);
		}
	}
	
	/*
	 * processImages
	 * 
	 * process the images for pigs
	 * Renames, copies originals, resizes & creates thumbs
	 * 
	 * @param string  $directory Path to the images to manipulate
	 * @param string  $directory_thumbs Path to thumb storage
	 * @param string  $directory_originals Path to original storage
	 * @param array   $images Images to process
	 * @param int     $image_size Size of images to be severed
	 * @param int     $thumb_size Size of thumbs to be severed
	 * @param boolean $originals Keep the original?
	 * @return void
	 */
	private function processImages($directory, $directory_thumbs, $directory_originals, $images, $image_size, $thumb_size, $originals) {
		/* Loop all the images to process them */
		foreach($images as $image) {
			/* Do a pre-emptive search and fix any bad file names */
			$find    = array("&",   "#", "@", "^", "<", ">", ":", '"', "/", "\\", "|", "?", "!", "*", "$", "%", "(", ")", "=");
			$replace = array("and", "",  "",  "",  "",  "",  "",  "",  "",  "",   "",  "",  "",  "",  "",  "",  "",  "",  "");
			/* Clean any html */
			$image_clean_name = str_replace($find, $replace, $image);
			/* Rename */
			if($image_clean_name != $image) {
				rename($directory . $image, $directory . $image_clean_name);
			}
			
			/* Update name in script */
			$image = $image_clean_name;
			
			/* If orignals are set move them first to protect them */
			if($originals == true) {
				if(!file_exists($directory_originals . $image)) {
					copy($directory . $image, $directory_originals . $image);
				}
			}
			
			/* Resize images (if need be) */
			if($originals == true) {
				list($currentImageWidthOriginals) = getimagesize($directory_originals . $image);
			}
			list($currentImageWidth) = getimagesize($directory . $image);
			if($image_size != false && $currentImageWidth != $image_size) {
				if($currentImageWidth > $image_size) {
					$this->manipulateImage($directory . $image, $directory . $image, $image_size);
				} elseif($currentImageWidth < $image_size && isset($currentImageWidthOriginals) && $currentImageWidthOriginals > $image_size && $originals == true) {
					$this->manipulateImage($directory_originals . $image, $directory . $image, $image_size);
				}
			}
			
			/* Create the thumbs */
			if(!file_exists($directory_thumbs . $image)) {
				$this->manipulateImage($directory . $image, $directory_thumbs . $image, $thumb_size, true);
			} elseif(file_exists($directory_thumbs . $image)) {
				list($currentImageThumb) = getimagesize($directory_thumbs . $image);
				if($thumb_size != $currentImageThumb) {
					$this->manipulateImage($directory . $image, $directory_thumbs . $image, $thumb_size, true);
				}
			}
		}
	}
	
	/*
	 * generatePagination
	 * 
	 * Outputs the pagination needed for pigs
	 * 
	 * @param string $gallery_id Id of gallery
	 * @return mixed
	 */
	protected function generatePagination($gallery_id) {
		/* Should we show it? */
		$numberOfPages = ceil($this->total / $this->limit);
		if($numberOfPages > 1) {
			$this->current = ($this->current / $this->limit) + 1;
			$pagination_data = '	<ul class="pigs_pagination">' . "\n";
			
			/* Prev */
			if($this->current > 1) {
				$pagination_data .= '		<li class="pigs_left"><a href="' . $this->url($gallery_id, ($this->current - 1)) . '">&lt;&lt; Prev</a></li>' . "\n";
			} else {
				$pagination_data .= '		<li class="pigs_left"><span>&lt;&lt; Prev</span></li>' . "\n";
			}
			/* Number of pages + links */
			for($i = 1; $i <= $numberOfPages; $i++){
				if($this->current == $i) {
					$pagination_data .= '		<li class="pigs_current"><span>' . $i . '</span></li>' . "\n";
				} else {
					$pagination_data .= '		<li><a href="' . $this->url($gallery_id, $i) . '">'.$i.'</a></li>' . "\n";
				}
			}
			
			/* Prev */
			if($numberOfPages > $this->current) {
				$pagination_data .= '		<li class="pigs_right"><a href="' . $this->url($gallery_id, ($this->current + 1)) . '">Next &gt;&gt;</a></li>' . "\n";
			} else {
				$pagination_data .= '		<li class="pigs_right"><span>Next &gt;&gt;</span></li>' . "\n";
			}
			
			$pagination_data .= '	</ul>' . "\n";
			
			return $pagination_data;
		} else {
			return null;
		}
	}
	
	/*
	 * url
	 * 
	 * Creates the url for pagination so only the right get variable
	 * is changed
	 * 
	 * @param string $gallery_id The Gallery ID
	 * @param int    $value The value of what we are changing
	 * @return string
	 */
	protected function url($gallery_id, $value) {
		if($this->pattern !== false) {
			$changedURL = $this->base . str_replace(array("%gallery%", "%page%"), array($gallery_id, $value), $this->pattern);
		} else {
			$params = $_GET;
			$params[$gallery_id] = $value;
			/* Third param is for valid &amp; signs */
			$changedURL = '?' . http_build_query($params, '', '&amp;');
		}
		return $changedURL;
	}
}
# pigs
_4.0.0.rc4_

-----

P.I.G.S is a PHP Image Grabbing Script that allows you to create powerful raw galleries.

P.I.G.S easily allows you to grabs and displays images from a selected directory, creates sized thumbnail of each image, and use a custom Content Viewer(Lightbox) & Ajax Pagination Plug-in

P.I.G.S supports JPG's, JPEG's, PNG's and GIF's, it even supports transparent PNG's/GIF's.

## Requirements
**Minimum**
- PHP 5.0.0
- You get the entire script but you won't have JSON support as an output.

**Recommended**
- PHP 5.2.0
- You get the entire script + JSON support as an output.

**Optimal**
- PHP 5.5.0
- Imagemagick
- You get the entire script + the full power of PHP 5.5.0 and the image processing speed of Imagemagick.

Support File Types
-----
* JPEG
* GIF
..* Transparent GIF
* PNG
..* Transparent PNG

## Install
Include Pigs class.
```
include('path/to/pigs/dir/pigs.php');
```

Call the Pigs class and string on arguments
```
$pigs = new pigs();
echo $pigs->dir("path/to/images/") // Path to images to process
		  ->size(1024) // Image resize size
		  ->thumb(200) // Thumbnail size
		  ->originals(true) // Keep original images
		  ->pagination(true) // Enable Pagination
		  ->limit(9) // Amount of images per page
		  ->cache(true) // Cache the gallery for faster rendering
		  ->render("gallery"); // Render
```

For render options you have
* **array** (Leave blank) Returns array of data
* **gallery** Returns styled gallery
* **json** Retunrs json of array

Finally include the optional P.I.G.S's styles and scripts if you plan on using "gallery".
```
<!--CSS-->
<link rel="stylesheet" type="text/css" href="path/to/pigs/dir/pigs.css" />
<!--JS-->
<script type="text/javascript" src="path/to/pigs/dir/pigs.js"></script>
<script type="text/javascript">
window.onload = function() {
	window.myPigs = new Pigs(".pigs_gallery ul.pigs_images a");
	// window.myPigs = new Pigs("");
};
</script>
```
# pigs
_**4.0.0.rc4** - Currently testing bugs for final release._

-----

P.I.G.S is a PHP Image Grabbing Script that allows you to create powerful raw galleries.

P.I.G.S easily allows you to grabs and displays images from a selected directory, creates sized thumbnail of each image, and use a custom Content Viewer(Lightbox) & Ajax Pagination Plug-in

P.I.G.S supports JPG's, JPEG's, PNG's and GIF's, it even supports transparent PNG's/GIF's.

## Requirements
**Minimum**
- PHP 5.3.0
- You get the entire script + JSON support as an output.

**Recommended**
- PHP 5.5.0
- Imagemagick
- You get the entire script + the full power of PHP 5.5.0 and the image processing speed of Imagemagick.

Support File Types
-----
* JPEG
* GIF
    * Transparent GIF
* PNG
    * Transparent PNG

## Demo
http://www.firedartstudios.com/labs/pigs

## Download & Quick Install
When you download this git the only thing you need to do for a working demo is to drop some images inside of the /images/ folder in the root.

## Install & Options
Include Pigs class.
```
include('path/to/pigs/dir/pigs.php');
```

Call the Pigs class and string on arguments
### Basic
```
var_dump(
	$pigs->dir("path/to/images/")
		 ->render()
);
```

### Gallery
```
echo $pigs->dir("path/to/images/")
		  ->size(1024)
		  ->thumb(150)
		  ->render("gallery");
```

### All Options
```
$pigs = new pigs();
echo $pigs->dir("path/to/images/") // Path to images to process
		  ->size(1024) // Image resize size
		  ->thumb(200) // Thumbnail size
		  ->originals(true) // Keep original images
		  ->pagination(true) // Enable Pagination
		  ->limit(9) // Amount of images per page
		  ->pattern("%gallery%/%page%") // Create custom url example.tld/gallery/demo/10
		  ->base("example.tld/gallery/") // Base for custom url
		  ->current(<get dynamic maybe?>) // Current page number for custom url
		  ->cache(true) // Cache the gallery for faster rendering
		  ->render("gallery"); // Render
```

_**Note**: If you use the custom url pattern then you will not be able to have two galleries per page using pagination._

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
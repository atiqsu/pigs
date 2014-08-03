<?php
include('pigs/pigs.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>PIGS - Demo</title>
	<!--PIGS Files-->
	<link rel="stylesheet" type="text/css" href="pigs/reset.css" />
	<link rel="stylesheet" type="text/css" href="pigs/pigs.css" />
</head>
<body>
<?php
$pigs = new pigs();
echo $pigs->dir("images")
		  ->size(1024)
		  ->thumb(200)
		  ->originals(true)
		  ->pagination(true)
		  ->limit(9)
		  ->cache(true)
		  ->render("gallery");
?>
<script type="text/javascript" src="pigs/pigs.js"></script>
<script type="text/javascript">
window.onload = function() {
	window.myPigs = new Pigs(".pigs_gallery ul.pigs_images a");
	// window.myPigs = new Pigs("");
};
</script>
</body>
</html>
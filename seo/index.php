<?php
include('../pigs/pigs.php');

// We are simulating a mini MVC pattern so please ignore the mess
$BASE_URL  = 'http';
if(isset($_SERVER["HTTPS"]) == "on") {$BASE_URL .= "s";}
$BASE_URL .= "://";
$BASE_URL .= $_SERVER["SERVER_NAME"];

$BASE_URI = $BASE_URL . rtrim(dirname($_SERVER["PHP_SELF"]), '/') . '/';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>PIGS - Demo</title>
	<!--PIGS Files-->
	<link rel="stylesheet" type="text/css" href="<?php echo $BASE_URI; ?>../pigs/reset.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $BASE_URI; ?>../pigs/pigs.css" />
</head>
<body>
<?php
if(isset($_GET['url']) && !empty($_GET['url'])) {
	$url = explode("/", $_GET['url']);
	$page    = $url[1];
} else {
	$page = 1;
}

$pigs = new pigs();
echo $pigs->dir("../images")
		  ->size(1024)
		  ->thumb(200)
		  ->originals(true)
		  ->pagination(true)
		  ->limit(9)
		  
		  ->pattern("%gallery%/%page%")
		  ->base($BASE_URI)
		  ->current((int) $page)
		  
		  ->cache(true)
		  ->render("gallery");
?>
<script type="text/javascript" src="<?php echo $BASE_URI; ?>../pigs/pigs.js"></script>
<script type="text/javascript">
window.onload = function() {
	window.myPigs = new Pigs(".pigs_gallery ul.pigs_images a");
	// window.myPigs = new Pigs("");
};
</script>
</body>
</html>
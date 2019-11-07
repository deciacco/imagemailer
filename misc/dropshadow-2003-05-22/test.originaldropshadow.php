<?php

// just to make the page background the same
function rgb2hex($rgb="")
{
	if ($rgb == "") return "#FFFFFF";
	$hex = split(",",$_POST['colour']);
	return "#" . dechex($hex[0]) . dechex($hex[1]) . dechex($hex[2]);
}

// get a file list
if (($handle=opendir("./images/")))
{
	while ($node = readdir($handle))
	{
		$nodebase = basename($node);
		if ($nodebase!="." && $nodebase!="..")
		{
			$files[] = $node;
		}
	}
}

?>

<html>
<body bgcolor="<?=rgb2hex($_POST['colour'])?>">

<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
<input type="hidden" name="display" value="1">
<p>input file name<br><select name="input">
<?php for($i=0; $i<count($files); $i++) echo "<option value=\"{$files[$i]}\">{$files[$i]}</option>\n"; ?>
</select></p>
<p>image size (0 for no resize)<br><input type="text" name="size" value="0"></p>
<p>colour (RRR,GGG,BBB)<br><input type="text" name="colour" value="255,255,255"></p>
<p><input type="submit" value="process"></p>
</form>

<hr noshade>

<?php

if ($_POST['display'])
{
	$colours = split(",",$_POST['colour']);

	// the actual example

	require "class.dropshadow.php";
	require "class.originaldropshadow.php";

	$ds = new originalDropShadow();
	$ds->setDebugging(TRUE);
	$ds->setImageSize($_POST['size']);
	$ds->setImageType("jpg");
	$ds->setShadowPath("./shadows/");
	$ds->createDropShadow("images/{$_POST['input']}", "output.jpg", $colours);

	echo "<p><img src=\"output.jpg\" alt=\"finished result\"></p>\n";
}

?>

</body>
</html>
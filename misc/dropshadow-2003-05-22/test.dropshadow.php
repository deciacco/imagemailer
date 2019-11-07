<?php

	require "class.dropshadow.php";

	$ds = new dropShadow(FALSE);
	$ds->setShadowPath('./shadows/');
	$ds->loadImage('./images/test.jpg');
	$ds->resizeByPercent(50, 0);
	$ds->applyShadow('CCCCCC');
	$ds->showShadow('png');

?>
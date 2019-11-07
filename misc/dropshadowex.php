<?php
/* set drop shadow options */

/* offset of drop shadow from top left */
define("DS_OFFSET",  5);
 
/* number of steps from black to background color */
define("DS_STEPS", 10);

/* distance between steps */
define("DS_SPREAD", 1);

/* define the background color */
$background = array("r" => 255, "g" => 255, "b" => 255);


if(isset($src) && file_exists($src)) {

  /* create a new canvas.  New canvas dimensions should be larger than the original's */
  list($o_width, $o_height) = getimagesize($src);
  $width  = $o_width + DS_OFFSET;
  $height = $o_height + DS_OFFSET;
  $image = imagecreatetruecolor($width, $height);

  /* determine the offset between colors */
  $step_offset = array("r" => ($background["r"] / DS_STEPS), "g" => ($background["g"] / DS_STEPS), "b" => ($background["b"] / DS_STEPS));

  /* calculate and allocate the needed colors */
  $current_color = $background;
  for ($i = 0; $i <= DS_STEPS; $i++) {
    $colors[$i] = imagecolorallocate($image, round($current_color["r"]), round($current_color["g"]), round($current_color["b"]));

    $current_color["r"] -= $step_offset["r"];
    $current_color["g"] -= $step_offset["g"];
    $current_color["b"] -= $step_offset["b"];
  }

  /* floodfill the canvas with the background color */
  imagefilledrectangle($image, 0,0, $width, $height, $colors[0]);

  /* draw overlapping rectangles to create a drop shadow effect */
  for ($i = 0; $i < count($colors); $i++) {
    imagefilledrectangle($image, DS_OFFSET, DS_OFFSET, $width, $height, $colors[$i]);
    $width -= DS_SPREAD;
    $height -= DS_SPREAD;
  }

  /* overlay the original image on top of the drop shadow */
  $original_image = imagecreatefromjpeg($src);
  imagecopymerge($image, $original_image, 0,0, 0,0, $o_width, $o_height, 100);

  /* output the image */
  header("Content-type: image/jpeg");
  imagejpeg($image, "", 100);
  
  /* clean up the image resources */
  imagedestroy($image);
  imagedestroy($original_image);
}
?>
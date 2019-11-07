<?php
/************************************************
 * Includes
 */
include "include/winbinder.php";

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors",1);
ini_set("log_errors",1);
ini_set("error_log","php_err.txt"); 

/************************************************
 * Constants
 */
define('CAPTION', 'Image Mailer v1.0 Beta - Settings Editor');
define('APPNAME', 'mailersettings');
define('OUT_DIR', getenv('TEMP') . DIRECTORY_SEPARATOR); // put resized imgs in the temp dir
define('IMG_POSTFIX', '');

$acceptedTypes = array('jpg','gif','png');

if(strlen($argv[0]) > strlen(__FILE__)) // run as exe
{
    define('IS_EXE', true);
    define('EXE_PATH', dirname($argv[0]));
}
else // run from php
    define('EXE_PATH', dirname(__FILE__));

define('INI_FILE', EXE_PATH . DIRECTORY_SEPARATOR . 'imgsend.ini');

// windows constants for API
define('EM_SCROLL', 181);
define('SB_BOTTOM', 7);

// dropshadow constants
define('DS_OFFSET', 5);
define('DS_STEPS', 20);
define('DS_SPREAD', 1);
$backGroundColor = array('r' => 255, 'g' => 255, 'b' => 255);

/************************************************
 * Main Progam
 */
if(IS_EXE){
    $KERNEL = wb_load_library("KERNEL");
    $GLOBALS["ExitProcess"] = wb_get_function_address("ExitProcess", $KERNEL);
}

if(wb_get_instance(CAPTION, false))
    wb_message_box(null, "Looks like the Settings Editor is running.\nPlease close the Settings Editor and run this program again.", "Information", WBC_INFO);
else
{
    if($argc <= 1) // let's see if there are arguments, otherwise there is no point
        wb_message_box(null, "No arguments were passed!", "Information", WBC_INFO);
    else
    {
        $fileInfo = array();

        // ok we have arguments, let's take only those we care about --> .jpg, .gif, .png
        for($i = 1; $i < $argc; $i++) // first argument skipped --> exe name
        {
            $tmpArgInfo = pathinfo($argv[$i]); // breakout argument into separate path parts
            
             // if file is one we can process
            if(in_array(strtolower($tmpArgInfo['extension']), $acceptedTypes))
            {
                $fileInfos[] = $tmpArgInfo;
            }

            unset($tmpArgInfo);
        }

        if(count($fileInfos) == 0){
            wb_message_box(null, "No valid image files found!", "Information", WBC_INFO);
        }
        else{
            //create the main window and editbox to give the user some feedback
            $mainwin = wb_create_window(NULL, NakedWindow, "Image Mailer", 330, 150);
            
            // add the icon to the window so it shows up on the taskbar
            wb_set_image($mainwin, EXE_PATH . DIRECTORY_SEPARATOR . 'imgicon.ico', null, 0);
            
            $editBox = wb_create_control($mainwin, EditBox, '', 1,1,328,148, 100, 
                WBC_MULTILINE | WBC_READONLY); 
            
            wb_set_visible($mainwin, true);

            update_edit_box($editBox, "Initializing...");

            //get settings from ini file
            $settings = array('MainSettings' => 
                            array(
                                'target'=> 500, 
                                'resample'=> 'Y', 
                                'quality'=>100,
                                'dropshadow'=> 'Y',
                                'captions'=> 'Y',
                                'usetemplate' => 'Y'
                            ));
            get_ini_settings($settings);

            update_edit_box($editBox, "Target Size: " . $settings['MainSettings']['target'] . 'px');
            update_edit_box($editBox, "Image Quality: " . $settings['MainSettings']['quality']);
            update_edit_box($editBox, "Resampling: " . 
                ($settings['MainSettings']['resample'] == 'Y' ? 'Yes' : 'No'));
            update_edit_box($editBox, "Dropshadow: " . 
                ($settings['MainSettings']['dropshadow'] == 'Y' ? 'Yes' : 'No'));
            update_edit_box($editBox, "Captions: " . 
                ($settings['MainSettings']['captions'] == 'Y' ? 'Yes' : 'No'));

            // loop through the images and resize
            foreach($fileInfos as $fileInfo){
                $filesToSend[] = create_new_image(
                        $fileInfo, 
                        OUT_DIR, 
                        $settings['MainSettings']['target'],
                        $settings['MainSettings']['quality'], 
                        IMG_POSTFIX, 
                        $settings['MainSettings']['resample'],
                        $editBox
                    );
            }

            if($settings['MainSettings']['dropshadow'] == 'Y'){
                update_edit_box($editBox, "Formatting...");
                create_drop_shadows($filesToSend, $backGroundColor);
            }

            update_edit_box($editBox, "Creating e-mail...");

            // attach each image to a new outlook e-mail
            send_to_outlook($filesToSend, 
                ($settings['MainSettings']['captions'] == 'Y' ? true : false),
                ($settings['MainSettings']['usetemplate'] == 'Y' ? true : false),
                $editBox);
            
            update_edit_box($editBox, "Finished!");
            wb_wait($mainwin, 800);
            wb_destroy_window($mainwin);
            
            cleanupAllBamcompileFiles();
        }
    }
}

if(IS_EXE)
    core_ExitProcess(0);
else
    exit(0);
//--------------------------------------------------------------------------------------------

/************************************************
 * 
 */
function core_ExitProcess($eid){   
    return wb_call_function($GLOBALS["ExitProcess"], array($eid));
}

/************************************************
 * 
 */
function cleanupAllBamcompileFiles()
{   
    $folder = getenv('TEMP');

    if ($h=opendir($folder)) {
        while (false !== ($file=readdir($h))) {
            
            $file=trim($file);
            
            if ($file == "." or $file == ".." or !is_file($folder.DIRECTORY_SEPARATOR.$file))
                continue;
            
            if (strpos($file,".tmp") !== false && strpos($file,"php") !== false) {
                @unlink($folder.DIRECTORY_SEPARATOR.$file);
            }
        }
    }
    closedir($h);     
}
/************************************************
 * 
 */
function get_ini_settings(&$pSettings)
{
    $defaultValues = @parse_ini_file(INI_FILE, true);

    if($defaultValues != false){
        $pSettings['MainSettings']['target'] = $defaultValues['MainSettings']['target'];
        $pSettings['MainSettings']['quality'] = $defaultValues['MainSettings']['quality'];
        $pSettings['MainSettings']['resample'] = $defaultValues['MainSettings']['resample'];
        $pSettings['MainSettings']['dropshadow'] = $defaultValues['MainSettings']['dropshadow'];
        $pSettings['MainSettings']['captions'] = $defaultValues['MainSettings']['captions'];
        $pSettings['MainSettings']['usetemplate'] = $defaultValues['MainSettings']['usetemplate'];
    }
}

/************************************************
 * 
 */
function send_to_outlook(&$pFilesToSend, $pCaptions, $pUseTemplate, &$pEditBox)
{
        $oOutlook = new COM("Outlook.Application") or die("Cannot create Outlook object");
        $oNameSpace = $oOutlook->getNameSpace("MAPI");
        $oMailFolder = $oNameSpace->getDefaultFolder(6);
        $oMailItem = $oMailFolder->Items->Add("IPM.Note.FormA");

        $oMailItem->Subject = "Photos";
        
        $tbl .= '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
        foreach($pFilesToSend as $fileToSend){
            $oMailItem->Attachments->Add($fileToSend);
            $imgBaseName = pathinfo($fileToSend, PATHINFO_BASENAME);
            $imgFileName = substr($imgBaseName , 0, strrpos($imgBaseName,'.'));
            $imgSize = number_format((filesize($fileToSend) / 1024), 1, '.', ',');
            
            $tbl .= "<tr><td><div align=\"center\"><img src='cid:$imgBaseName'></div></td></tr>\n"; 
            
            if($pCaptions)
                $tbl .= "<tr><td height=\"55\" valign=\"top\"><div align=\"center\"><em>$imgFileName</em></div></td></tr>\n";
            else
                $tbl .= "<tr><td>&nbsp;</td></tr>\n";
        }
        $tbl .= "</table>";
        
        $ad = "<br />\n<br />\n<small><i>This e-mail generated with Image Mailer for Outlook v1.0 Beta.</i></small><br>\n";
        $ad .= "<small><i>Visit <a href=\"http://www.deciacco.com/blog/php/easily-email-images-with-microsoft-outlook\">http://www.deciacco.com/</a> for more information.</i></small>\n";

        if($pUseTemplate && get_template($email_tpl))
        {
            update_edit_box($pEditBox, "Using template...");
            $email_tpl = str_replace('{IMAGES_TABLE}', $tbl, $email_tpl);
            $email_tpl = str_replace('</body>', $ad."\n</body>", $email_tpl);
            $oMailItem->HTMLBody = $email_tpl;
        }
        else{       
            $msg = "<html>";
            $msg .= "<head><style type=\"text/css\"><!-- body,td,th {font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #000; } body { background-color: #FFF; } --> </style></head>";
            $msg .= '<p>Start your message here...</p>';
            $msg .= $tbl;
            $msg .= $ad;
            $msg .= "</html>";
            $oMailItem->HTMLBody = $msg;
        }
        
        $oMailItem->Display();
}

/************************************************
 * 
 */
function get_template(&$pMsg)
{
    $success = false;
    $email_template = @file_get_contents(EXE_PATH.DIRECTORY_SEPARATOR.'htmltemplate.htm');
    if($email_template != false){
        $pMsg .= $email_template;
        $success = true;
    }
    return $success;
}

/************************************************
 * 
 */
function update_edit_box(&$pEditBox, $pMsg)
{
        $tmpText = wb_get_text($pEditBox);

        if(strlen($tmpText) > 1)
            $tmpMsg = "$tmpText\n$pMsg";
        else
            $tmpMsg = $pMsg;
           
        wb_set_text($pEditBox,  $tmpMsg);
        wb_send_message($pEditBox, EM_SCROLL, SB_BOTTOM, 0);
        wb_wait($mainwin, 200);
}

/************************************************
 * 
 */
function create_new_image(&$pFileInfo, $pOutputDirectory, 
                                $target, 
                                $pImgQuality, $pFilePostfix, $pResample, &$pEditBox)
{
    $imageFileFullName = $pFileInfo['dirname'].DIRECTORY_SEPARATOR.$pFileInfo['basename'];
    
    if(strtolower($pFileInfo['extension']) == 'jpg')
        $imageExif = exif_read_data($imageFileFullName); // get exif information

    list($origw, $origh) = getimagesize($imageFileFullName);
    
    // only resize if longest side is greater than the target size
    if(max($origw, $origh) > $target)
    {
        update_edit_box($pEditBox, "Resizing:  " . 
                $pFileInfo['basename'] . ", please wait...");
            
        if($origw > $origh)
            $pct = ($target / $origw);
        else
            $pct = ($target / $origh);

        $pImageMaxW = round($origw * $pct);
        $pImageMaxH = round($origh * $pct);
        
        $resized = imagecreatetruecolor($pImageMaxW, $pImageMaxH);

        $fileExt = strtolower($pFileInfo['extension']);

        switch($fileExt)
        {
            case 'jpg':
                $image = imagecreatefromjpeg($imageFileFullName);
                break;
            case 'gif':
                $image = imagecreatefromgif($imageFileFullName);
                break;
            case 'png':
                $image = imagecreatefrompng($imageFileFullName);
        }
        
        if($pResample == 'Y')
            imagecopyresampled($resized, $image, 0, 0, 0, 0, 
                            $pImageMaxW, $pImageMaxH, $origw, $origh);
        else
            imagecopyresized($resized, $image, 0, 0, 0, 0, 
                            $pImageMaxW, $pImageMaxH, $origw, $origh);
        
        // php 4 does not have filename in pathinfo function yet
        $fileName = substr($pFileInfo['basename'] , 0, strrpos($pFileInfo['basename'],'.'));

        $newImageFileFullName = $pOutputDirectory . 
            $fileName . 
            $pFilePostfix.".$fileExt";

        // rotate the image if exif orientation flag found and the img is a jpg
        if(strtolower($pFileInfo['extension']) == 'jpg')
        {
            switch($imageExif["Orientation"])
            {
                case 3:
                    update_edit_box($pEditBox, "rotate.180");
                    $resized = imagerotate($resized, 180, 0);
                    break;
                case 6:
                    update_edit_box($pEditBox, "rotate.CW.90");
                    $resized = imagerotate($resized, 360-90, 0);
                    break;
                case 8:
                    update_edit_box($pEditBox, "rotate.CCW.90");
                    $resized = imagerotate($resized, 360-270, 0);
                    break;
                default:
                    break;
            }
        }

        switch($fileExt)
        {
            case 'jpg':
                imagejpeg(
                    $resized, 
                    $newImageFileFullName, 
                    $pImgQuality);
                break;
            case 'gif':
                imagegif($resized, 
                    $newImageFileFullName, 
                    $pImgQuality);
                break;
            case 'png':
                // Image quality for png must be 0-9
                imagepng($resized, 
                    $newImageFileFullName, 
                    round($pImgQuality/10)-1);
        }

        imagedestroy($image);
    }
    else
        $newImageFileFullName = $imageFileFullName;

    return $newImageFileFullName;
}

/************************************************
 * 
 */
function create_drop_shadows(&$pImageFiles, $pBackGroundColor)
{
    foreach($pImageFiles as $src)
    {
        $fileExt = pathinfo($src, PATHINFO_EXTENSION);

        list($o_width, $o_height) = getimagesize($src);

        $canvas = imagecreatetruecolor($o_width+12, $o_height+12);
        $wborder = imagecreatetruecolor($o_width+10, $o_height+10);
        
        $white = imagecolorallocate($canvas, 255,255,255);
        $black = imagecolorallocate($canvas, 0,0,0);

        imagefilledrectangle($canvas, 0,0, $o_width+12, $o_height+12, $black);
        imagefilledrectangle($wborder, 0,0, $o_width+10, $o_height+10, $white);

        imagecopy($canvas, $wborder, 1,1,0,0, $o_width+10, $o_height+10);

        switch($fileExt)
        {
            case 'jpg':
                $original_image = imagecreatefromjpeg($src);
                break;
            case 'gif':
                $original_image = imagecreatefromgif($src);
                break;
            case 'png':
                $original_image = imagecreatefrompng($src);
        }

        imagecopy($canvas, $original_image, 6,6,0,0, $o_width, $o_height);
        imagedestroy($original_image);

        $width  = $o_width+12+DS_OFFSET;
        $height = $o_height+12+DS_OFFSET;

        $newcanvas = imagecreatetruecolor($width, $height);

        $step_offset = array(
                "r" => ($pBackGroundColor["r"] / DS_STEPS), 
                "g" => ($pBackGroundColor["g"] / DS_STEPS), 
                "b" => ($pBackGroundColor["b"] / DS_STEPS));

        $current_color = $pBackGroundColor;
        
        for ($i = 0; $i <= DS_STEPS; $i++) {
            $colors[$i] = imagecolorallocate($newcanvas, 
                round($current_color["r"]), 
                round($current_color["g"]), 
                round($current_color["b"]));

            $current_color["r"] -= $step_offset["r"];
            $current_color["g"] -= $step_offset["g"];
            $current_color["b"] -= $step_offset["b"];
        }

        imagefilledrectangle($newcanvas, 0,0, $width, 
            $height, $colors[0]);
       
        for ($i = 0; $i < count($colors); $i++) {
            imagefilledrectangle($newcanvas, DS_OFFSET, DS_OFFSET, 
                $width, 
                $height, $colors[$i]);

            $width -= DS_SPREAD;
            $height -= DS_SPREAD;
        }

        imagecopymerge($newcanvas, $canvas, 0,0, 0,0, $o_width+12, $o_height+12, 100);

        switch($fileExt)
        {
            case 'jpg':
                imagejpeg(
                    $newcanvas, 
                    $src, 
                    100);
                break;
            case 'gif':
                imagegif(
                    $newcanvas, 
                    $src, 
                    100);
                break;
            case 'png':
                imagepng(
                    $newcanvas, 
                    $src, 
                    9);
        }

        imagedestroy($canvas);
        imagedestroy($newcanvas);
    }
}
?>
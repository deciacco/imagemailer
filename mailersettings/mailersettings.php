<?
include "include\\winbinder.php";                 // Location of WinBinder library

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors",1);
ini_set("log_errors",1);
ini_set("error_log","php_err.txt"); 


if(strlen($argv[0]) > strlen(__FILE__)) // run as exe
{
	define('IS_EXE', true);
    define('EXE_PATH', dirname($argv[0]));
}
else // run from php
	define('EXE_PATH', dirname(__FILE__));

define('INI_FILE', EXE_PATH . DIRECTORY_SEPARATOR . 'imgsend.ini');

$rdbtnImgSize = array(  '300' => 1281,
                        '550' => 1282,
                        '1024' => 1283,
                        '1280' => 1284,
						'800' => 1285
                     );

$rdbtnQuality = array(  '50' => 1021,
                        '80' => 1022,
                        '100' => 1023
                     );

$rdbtnResample = array( 'Y' => 1031,
                        'N' => 1032
                      );

define('LBL_SAYSOMETHING', 1288);
define('CHK_DROPSHADOW', 1290 );
define('CHK_CAPTIONS', 1291);
define('CHK_USETEMPLATE', 1293);
define('LBL_COPYRIGHT', 1292);

if(IS_EXE){
    $KERNEL = wb_load_library("KERNEL");
    $GLOBALS["ExitProcess"] = wb_get_function_address("ExitProcess", $KERNEL);
}

eval(parse_rc(file_get_contents("resource\\frmSettings.rc"), '$mainwin', null, 'PopupWindow')); 
wb_set_image($mainwin, EXE_PATH . DIRECTORY_SEPARATOR . 'otheroptions.ico', null, 0);

//get settings from ini file
$settings = array('MainSettings' => 
				array(
					'target' => 550, 
					'resample' => 'Y', 
					'quality' => 100,
					'dropshadow' => 'Y',
                    'captions' => 'Y',
                    'usetemplate' => 'Y'
					));
get_ini_settings($settings);

wb_set_value(wb_get_control($mainwin,$rdbtnImgSize[$settings['MainSettings']['target']]),  TRUE);
wb_set_value(wb_get_control($mainwin,$rdbtnQuality[$settings['MainSettings']['quality']]),  TRUE);
wb_set_value(wb_get_control($mainwin,$rdbtnResample[$settings['MainSettings']['resample']]),  TRUE);
wb_set_value(wb_get_control($mainwin,CHK_DROPSHADOW),  ($settings['MainSettings']['dropshadow'] == 'Y' ? TRUE : FALSE));
wb_set_value(wb_get_control($mainwin,CHK_CAPTIONS),  ($settings['MainSettings']['captions'] == 'Y' ? TRUE : FALSE));
wb_set_value(wb_get_control($mainwin,CHK_USETEMPLATE),  ($settings['MainSettings']['usetemplate'] == 'Y' ? TRUE : FALSE));

wb_set_handler($mainwin, "process_main");
wb_main_loop();

function process_main($window, $id)
{
    global $settings, $rdbtnImgSize, $rdbtnQuality, $rdbtnResample;

    switch($id) {

        case 1003: // Ok button cliked
            exit_app($window, $settings, $rdbtnImgSize, 
					$rdbtnQuality, $rdbtnResample);
            break;

        case IDCLOSE:                         
            exit_app($window, $settings, $rdbtnImgSize, 
					$rdbtnQuality, $rdbtnResample);
            break;

        default:
            //wb_message_box($window, $id);
            break;
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
function get_settings(&$pWindow, &$pSettings, 
    &$pRdbtnImgSize, &$pRdbtnQuality, &$pRdbtnResample)
{
    
    foreach($pRdbtnImgSize as $key => $btnId)
    {
        $is_sel_val = wb_get_value(wb_get_control($pWindow, $btnId));
        if($is_sel_val)
            $pSettings['MainSettings']['target'] = (int)$key;
    }
    
    foreach($pRdbtnQuality as $key => $btnId)
    {
        
        $is_sel_val = wb_get_value(wb_get_control($pWindow, $btnId));
        if($is_sel_val)
            $pSettings['MainSettings']['quality'] = (int)$key;
    }

    foreach($pRdbtnResample as $key => $btnId)
    {
        
        $is_sel_val = wb_get_value(wb_get_control($pWindow, $btnId));
        if($is_sel_val)
            $pSettings['MainSettings']['resample'] = $key;
    }

    $pSettings['MainSettings']['dropshadow'] = (wb_get_value(wb_get_control($pWindow, CHK_DROPSHADOW)) ? 'Y' : 'N');

    $pSettings['MainSettings']['captions'] = (wb_get_value(wb_get_control($pWindow, CHK_CAPTIONS)) ? 'Y' : 'N');

    $pSettings['MainSettings']['usetemplate'] = (wb_get_value(wb_get_control($pWindow, CHK_USETEMPLATE)) ? 'Y' : 'N');
}

/************************************************
 * 
 */
function exit_app(&$pWindow, &$pSettings, &$pRdbtnImgSize, 
			&$pRdbtnQuality, &$pRdbtnResample)
{
    get_settings($pWindow, $pSettings, $pRdbtnImgSize, $pRdbtnQuality, $pRdbtnResample);
    put_ini_settings($pSettings);
    wb_destroy_window($pWindow);
    cleanupAllBamcompileFiles();
}

/************************************************
 * 
 */
function get_ini_settings(&$pSettings)
{
	$defaultValues = parse_ini_file(INI_FILE, true);

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
function put_ini_settings(&$pSettings)
{
    $fHndl = fopen(INI_FILE, "w"); // open and wipe the file

    while( list($keySection, $valueSection) = each($pSettings))
    {
        fwrite($fHndl, "[$keySection]\r\n");
        
        while (list($key, $val) = each($valueSection)) {
           
           fwrite($fHndl, "$key = $val\r\n");
        }
    }

    fclose($fHndl);
}
?>
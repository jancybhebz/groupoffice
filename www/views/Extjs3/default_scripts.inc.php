<?php

use GO\Base\Util\Crypt;
use go\core\fs\File;
use go\core\fs\Folder;
use go\core\jmap\Response;
use go\core\Module;
use go\core\webclient\Extjs3;

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: default_scripts.inc.php 22455 2018-03-06 15:17:33Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

$webclient = Extjs3::get();

$baseUrl = $webclient->getRelativeUrl();


$settings['max_rows_list'] = 50;

$settings['config']['theme'] = GO::config()->theme;
$settings['config']['product_name'] = GO::config()->product_name;
$settings['config']['host'] = GO::config()->host;
$settings['config']['title'] = GO::config()->title;
$settings['config']['full_url'] = GO::config()->full_url;
$settings['config']['allow_password_change'] = GO::config()->allow_password_change;
$settings['config']['allow_themes'] = GO::config()->allow_themes;
$settings['config']['allow_profile_edit'] = GO::config()->allow_profile_edit;
$settings['config']['max_users'] = GO::config()->max_users;
$settings['config']['debug'] = go()->getDebugger()->enabled;
$settings['config']['max_attachment_size'] = GO::config()->max_attachment_size;
$settings['config']['max_file_size'] = GO::config()->max_file_size;
$settings['config']['help_link'] = GO::config()->help_link;
$settings['config']['support_link'] = GO::config()->support_link;
$settings['config']['report_bug_link'] = GO::config()->report_bug_link;
$settings['config']['nav_page_size'] = intval(GO::config()->nav_page_size);
$settings['config']['logoutWhenInactive'] = intval(go()->getSettings()->logoutWhenInactive);
$settings['config']['tickets_no_email_required'] = GO::config()->tickets_no_email_required;
$settings['config']['default_country'] = GO::config()->default_country;
$settings['config']['checker_interval'] = (int) GO::config()->checker_interval;
$settings['config']['remember_login'] = GO::config()->remember_login;
$settings['config']['encode_callto_link'] = GO::config()->encode_callto_link;
$settings['config']['login_message'] = GO::config()->login_message;
$settings['config']['hideAbout'] = \GO::config()->hideAbout;
$settings['config']['email_allow_body_search'] = GO::config()->email_allow_body_search;

$settings['config']['lostPasswordURL'] = go()->getSettings()->lostPasswordURL;

$settings['state_index'] = 'go';
$settings['language'] = go()->getLanguage()->getIsoCode();
$settings['show_contact_cf_tabs'] = array();


$root_uri = go()->getDebugger()->enabled ? GO::config()->host : GO::config()->root_path;
$view_root_uri = $root_uri . 'views/Extjs3/';
$view_root_path = GO::config()->root_path . 'views/Extjs3/';



if(go()->getDebugger()->enabled) {
  $cacheFile = \go\core\App::get()->getTmpFolder()->getFile('debug.js');
  $cacheFile->delete();
} else
{
  $cacheFile = \go\core\App::get()->getDataFolder()->getFolder('cache/clientscripts')->create()->getFile('all.js');
}

//echo '<script type="text/javascript" src="' . GO::url('core/language', ['lang' => \GO::language()->getLanguage()]) . '"></script>';
echo '<script type="text/javascript" src="'.$baseUrl.'views/Extjs3/javascript/ext-base-debug.js?mtime='.filemtime(__DIR__ . '/javascript/ext-base-debug.js').'"></script>';
echo '<script type="text/javascript" src="'.$baseUrl.'views/Extjs3/javascript/ext-all-debug.js?mtime='.filemtime(__DIR__ . '/javascript/ext-all-debug.js').'"></script>';
echo '<script type="text/javascript" src="' . GO::view()->getUrl() . 'lang.php?lang='.\go()->getLanguage()->getIsoCode() . '&v='.$webclient->getLanguageJS()->getModifiedAt()->format("U").'"></script>';

?>


<script type="text/javascript">

	Ext.namespace("GO");

	GO.settings = <?php echo json_encode($settings); ?>;
	GO.language = "<?php echo go()->getLanguage()->getIsoCode(); ?>";
	GO.calltoTemplate = '<?php echo GO::config()->callto_template; ?>';
	GO.calltoOpenWindow = <?php echo GO::config()->callto_open_window ? "true" : "false"; ?>;
<?php
if (isset(GO::session()->values['security_token'])) {
	echo 'GO.securityToken="' . GO::session()->values['security_token'] . '";';
}

//if (isset($_GET['SET_LANGUAGE']) && preg_match('/[a-z_]/', $_GET['SET_LANGUAGE'])) {
//	echo 'GO.loginSelectedLanguage = "' . $_GET['SET_LANGUAGE'] . '";';
//} 
echo 'window.name="' . GO::getId() . '";';

if (isset(GO::session()->values['security_token']))
	echo 'Ext.Ajax.extraParams={security_token:"' . GO::session()->values['security_token'] . '"};';

//GO::router()->getController()->fireEvent('inlinescripts');
?>
</script>
<?php
$gouiScripts = [];
$rootFolder = new Folder(GO::config()->root_path);
$strip = strlen($rootFolder->getPath()) + 1;
if ($cacheFile->exists()) {
    $gouiScripts = go()->getCache()->get("gouiScripts");

    if(!$gouiScripts) {
	    $gouiScripts = [];
	    $load_modules = GO::modules()->getAllModules(true);
	    foreach ($load_modules as $module) {
            $bundleFile = new File($module->moduleManager->path(). 'views/goui/dist/Index.js');
            if($bundleFile->exists()) {
                $gouiScripts[] = $bundleFile;
            }
        }
    }

	echo '<script type="text/javascript" src="' . GO::view()->getUrl() . 'script.php?v='.$cacheFile->getModifiedAt()->format("U"). '"></script>';
} else {

	$scripts = array();

	$load_modules = GO::modules()->getAllModules(true);

	$scripts[] = "var BaseHref = '" . $baseUrl . "';";

	$scripts[] = new File(GO::config()->root_path . 'views/Extjs3/javascript/namespaces.js');
	
	//for t() function to auto detect module package and name
	$scripts[] = "go.module='core';go.package='core';";
	
	$bundleFile = new File(GO::config()->root_path . 'views/Extjs3/javascript/scripts.js');
	if ($bundleFile->exists()) {
		$scripts[] = $bundleFile;
	} else {
		$data = file_get_contents(GO::config()->root_path . 'views/Extjs3/javascript/scripts.txt');
		$lines = array_map('trim', explode("\n", $data));
		foreach ($lines as $line) {
			if (!empty($line)) {
				$scripts[] = new File(GO::config()->root_path . $line);
			}
		}
	}

	if (count($load_modules)) {
		$modules = array();
		foreach ($load_modules as $module) {
			if ($module->moduleManager instanceof Module) {
				$prefix = dirname(str_replace("\\", "/", get_class($module->moduleManager))) . "/views/extjs3/";
				$scriptsFile = $module->moduleManager->path() . 'views/extjs3/scripts.txt';

				//fallback to old dir
				$modulePath = GO::config()->root_path . 'modules/' . $module->moduleManager->getName() . '/';
			} else {
				$scriptsFile = false;
				$modulePath = $module->moduleManager->path();
			}
			$pkg = $module->package ? $module->package : "legacy";
			
			$js = $module->package ? 'Ext.ns("go.modules.' . $module->package . '.' . $module->name . '");' : 'Ext.ns("GO.' . $module->name  . '");';
            $js .= "go.module = '" . $module->name . "';";
            $js .= "go.package = '" . $pkg . "';";
            $js .= "go.Translate.setModule('" . $pkg . "', '" .$module->name . "');";

			$scripts[] = $js;

			$bundleFile = new File($module->moduleManager->path(). 'views/goui/dist/Index.js');
			if($bundleFile->exists()) {
				$gouiScripts[] = $bundleFile;
			}


            if (!$scriptsFile || !file_exists($scriptsFile)) {
                $scriptsFile = $modulePath . 'scripts.txt';
                if (!file_exists($scriptsFile))
                    $scriptsFile = $modulePath . 'views/Extjs3/scripts.txt';

                $prefix = "";
            }

            if (file_exists($scriptsFile)) {
                $data = file_get_contents($scriptsFile);
                $lines = array_map('trim', explode("\n", $data));
                foreach ($lines as $line) {
                    if (!empty($line)) {
                        $scripts[] = new File(GO::config()->root_path . $prefix . trim($line));
                    }
                }
            }

		}
	}

	//two modules may include the same script
	//$scripts = array_map('trim', $scripts);
	//	$scripts = array_unique($scripts);

  if(!go()->getDebugger()->enabled) {
    $minify = new \MatthiasMullie\Minify\JS();
  } else
  {
    //$fp = $cacheFile->open("w");
    $js = "";
  }


	foreach ($scripts as $script) {

		if (go()->getDebugger()->enabled) {
			if (is_string($script)) {
				echo '<script type="text/javascript">' . $script . '</script>' . "\n";
			} else if ($script instanceof File) {
                $relPath = substr($script->getPath(), $strip);
                $parts = explode('/', $relPath);

                $relPath = $baseUrl . $relPath;

				echo '<script type="text/javascript" src="'.$relPath. '?mtime='.$script->getModifiedAt()->format("U").'"></script>' . "\n";
			}

		} else {      

			$minify->add($script);
		}
	}
	
	if (!go()->getDebugger()->enabled) {
		$minify->gzip($cacheFile->getPath());		
		echo '<script type="text/javascript" src="' . GO::view()->getUrl() . 'script.php?v= '. $cacheFile->getModifiedAt()->format("U") . '"></script>';
	} else
  {
//    $fp = $cacheFile->open('w');
//    fwrite($fp, $js);
//    fclose($fp);
  }
//  echo '<script type="text/javascript" src="' . GO::url('core/clientScripts', ['mtime' => GO::config()->mtime, 'lang' => \GO::language()->getLanguage()]) . '"></script>';


	go()->getCache()->set("gouiScripts", $gouiScripts);
}

foreach($gouiScripts as $gouiScript) {
	$relPath = substr($gouiScript->getPath(), $strip);
	$parts = explode('/', $relPath);

	$relPath = $baseUrl . $relPath;

	echo '<script type="module" src="'.$relPath. '?mtime='.$gouiScript->getModifiedAt()->format("U").'"></script>' . "\n";
}

if (file_exists(GO::view()->getTheme()->getPath() . 'MainLayout.js')) {
	echo '<script src="' . GO::view()->getTheme()->getUrl() . 'MainLayout.js" type="text/javascript"></script>';
	echo "\n";
}
?>
<script type="text/javascript">
<?php

//these parameter are passed by dialog.php. These are used to directly link to
//a dialog.
if (isset($_REQUEST['f'])) {
	if (substr($_REQUEST['f'], 0, 9) == '{GOCRYPT}')
		$fp = Crypt::decrypt($_REQUEST['f']);
	else
		$fp = json_decode(base64_decode($_REQUEST['f']), true);

	GO::debug("External function parameters:");
	GO::debug($fp);
	?>
		if (GO.<?php echo $fp['m']; ?>)
		{
			GO.mainLayout.on("render", function () {
				GO.<?php echo $fp['m']; ?>.<?php echo $fp['f']; ?>.call(this, <?php echo json_encode($fp['p']); ?>);
			});
		}
	<?php
}
?>

Ext.onReady(GO.mainLayout.boot, GO.mainLayout);
</script>

<?php 
require_once('const.php');

$lang = "fr";
if(isset($_COOKIE['lang'])) {
    if(file_exists(getLanguageFile($_COOKIE['lang']))) {
		$lang = $_COOKIE['lang'];
	}
}

$json_lang = file_get_contents(getLanguageFile($lang));
$t = json_decode($json_lang, true);

function getLanguageFile($lang) {
    return getAbsolutePath().DIR_STR."/lang.".$lang.".json";
}

function getAbsolutePath() {
    $absolutePath = $_SERVER['DOCUMENT_ROOT']."/";
    $part2 = str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']);
    $explode = explode("/", $part2);
    if(isset($explode[1])) {
        $absolutePath .= $explode[1]."/";
    }
    return $absolutePath;
}

function getVersions() {
	$listVersions = array();
	foreach(getVersionsFolders() as $file) {
		$split = explode("-", $file);
		$version = $split[1];
		$lang = $split[2];
		$scored = isset($split[3]) ? $split[3] : "";
		
		if(!isset($listVersions[$lang])) {
			$listVersions[$lang] = array();
		}
		
		$listVersions[$lang][] = array(
			"file" => $file,
			"version" => $version,
			"scored" => $scored
		);	
	}
	return $listVersions; //json_encode($listVersions, true);
}

function getVersionsFolders() {
    $listVersions = array();
    foreach(scandir(getAbsolutePath().DIR_VERSIONS, 1) as $file) {
        if ($file != "." && $file != ".." && is_dir(getAbsolutePath().DIR_VERSIONS.'/'.$file)) {
            $listVersions[] = $file;
        }
    }
    return $listVersions;
}

function getFileVersion($json) {
    if(isset($json['meta']) && isset($json['meta']['version'])) {
        if(in_array($json['meta']['version'], getVersionsFolders())) {
            return $json['meta']['version'];
        }
    } 
    
    if(isset($json['version'])) {
        return $json['version'];
    }
    
    return null;
}

function getVersionText($v) {
	$version = $v['version'];
	if($v['scored'] != "") { $version .=" (".$v['scored'].")"; }
	return $version;
}

function getJSONFromFile($filepath) {
	$file = file_get_contents($filepath);
	return json_decode($file, true);
}


function includeDependencies() {
	$rep = "class";
	foreach(scanAllDir($rep) as $dep) {
		include_once($rep."/".$dep);
	}
}

function scanAllDir($dir) {
  $result = [];
  foreach(scandir($dir) as $filename) {
    if ($filename[0] === '.') continue;
    $filePath = $dir . '/' . $filename;
    if (is_dir($filePath)) {
      foreach (scanAllDir($filePath) as $childFilename) {
        $result[] = $filename . '/' . $childFilename;
      }
    } else {
      $result[] = $filename;
    }
  }
  return $result;
}

function getColor($colorName) {
	global $colors;
	return $colors[$colorName];
}

function getChoices($choices) {
	if(!is_array($choices))
		return $choices;
	
	$choicesRes = array();
	foreach($choices as $choice) {
		if(!is_array($choice)) {
			$choicesRes[] = $choice;
		} else {
			foreach($choice as $text => $score) {
				$choicesRes[] = $text;
			}
		}
	}
	return $choicesRes;
}


function getClientIP() {
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    
    if(filter_var($client, FILTER_VALIDATE_IP))
        $ip = $client;
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
        $ip = $forward;
    else
        $ip = $remote;
    
    return $ip;
}

function remAccent($arg) {
    return str_replace(
        array('ç', 'é','ë','è','ö','ü','ä','ù','ô','/','¹'),
        array('c', 'e','e','e','o','u','ae','u','o','-',"1"),
        $arg);
}
?>
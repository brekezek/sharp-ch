<?php 
require_once('const.php');

$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if(isset($_COOKIE['lang'])) {
    $lang = $_COOKIE['lang'];   
} else {
    $_COOKIE['lang'] = $lang;  
}
if(!file_exists(getLanguageFile($lang))) {
    $lang = "en";
    $_COOKIE['lang'] = "en";
}

$json_lang = file_get_contents(getLanguageFile($lang));
$t = json_decode($json_lang, true);

function getLanguageFile($lang) {
    return getAbsolutePath().DIR_STR."/lang.".$lang.".json";
}


function getBase() {
    $uri = $_SERVER['REQUEST_URI'];
    $suri = explode("/", $uri);
    return "/".$suri[1]."/";
}

function getLang() {
    $lang = "fr";
    if(isset($_COOKIE['lang'])) {
        $lang = $_COOKIE['lang'];
    }
    return $lang;
}

function getLanguageList() {
    $langs = array();
    foreach(scanAllDir(getAbsolutePath()."str") as $filename) {
        $split = explode(".", $filename);
        if(isset($split[1]) && $split[1] != "fr") {
            $langs[] = $split[1];
        }
    }
    array_unshift($langs, "fr");
    return $langs;
}

function getQuestionnaireSections($version, $excludeList=array()) {
    $orderedAspectsList = array();
    $pathVersion = getAbsolutePath().DIR_VERSIONS."/".$version;
    $packages = getJSONFromFile($pathVersion."/_meta_package.json")['order'];
    $sections = array();
    if(is_array($packages)) {
        foreach($packages as $package) {
            $pathPackage = $pathVersion."/".$package;
            $categories = getJSONFromFile($pathPackage."/_meta_category.json");
            if(!in_array($package, $excludeList)) {
                $sections[$package] = array(
                    "title" => $categories['title'],
                    "color" => $categories['color']
                );
            }
        }
    }
    return $sections;
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
    if(!file_exists($filepath))
        return array();
    
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

function getURLScores($filename, $version) {
    $data = base64_encode(urlencode(serialize(array(
        "filename" => $filename,
        "version" => $version
    ))));
    $url = "http://".$_SERVER['HTTP_HOST']."/";
    $phpself = explode("/", substr($_SERVER['PHP_SELF'], 1, strlen($_SERVER['PHP_SELF'])));
    if(count($phpself) > 1) {
        $url .= $phpself[0]."/";
    }
    $url .= "scores/data/".$data;
    return $url;
}

function getFormattedTime($time, $patternDay = "%02d %s ", $patternHours = "%02d%s%02d") {
    global $t;
    $days = floor($time / (3600*24));
    $hours = floor(($time - $days*24*3600) / 3600);
    $min = floor(($time - $days*24*3600 - $hours*3600) /  60);
    
    // $hours = 24*$days + $hours;
    $timeF = array();
    $jour = $t['jours'];
    if($days <= 1) $jour = substr($jour, 0, -1);
    $timeF['jours'] = ($days > 0)  ? sprintf($patternDay, $days, $jour) : "";
    $timeF['hours'] = sprintf($patternHours, $hours, "h", $min);
    return $timeF;
}

function drawCircleChart($color, $nbTotalSample, $nbEffectifSample, $label, $mode = 'p') {
    $percentage = ($nbEffectifSample / $nbTotalSample) * 100;
    ?>
    <div class="text-center">
        <div class="single-chart">
            <svg viewbox="0 0 36 36" class="circular-chart <?= $color ?>">
              <path class="circle-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path class="circle"
                stroke-dasharray="<?= $percentage ?>, 100"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
             
              <text x="18" y="20.35" class="days"><?= $mode == "p" ? round($percentage, 1)."%" : $nbEffectifSample." / ".$nbTotalSample ?></text>
            </svg>
        </div>
        
        <div class="lead" style="font-size:1rem">
    	<?= $label ?>
    	</div>
	</div>
    <?php 
}

function drawCircleChartForTime($color, $time, $label) {
    global $endingTime, $startingTime;
    $percentage = ($time / ($endingTime - $startingTime)) * 100;
    ?>
    <div class="text-center">
        <div class="single-chart">
            <svg viewbox="0 0 36 36" class="circular-chart <?= $color ?>">
              <path class="circle-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path class="circle"
                stroke-dasharray="<?= $percentage ?>, 100"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <?php $timeF = getFormattedTime($time); ?>
              <text x="18" y="<?= empty($timeF['jours']) ? "20.35" : "16.5" ?>" class="days"><?= $timeF['jours'] ?></text>
              <text x="18" y="<?= empty($timeF['jours']) ? "20.35" : "22.65" ?>" class="<?= empty($timeF['jours']) ? "days" : "hours" ?>"><?= $timeF['hours'] ?></text>
            </svg>
        </div>
        
        <div class="lead">
    	<?= $label ?>
    	</div>
	</div>
    <?php 
}

function isIE() {
    return preg_match('/Trident\/7.0; rv:11.0/', $_SERVER['HTTP_USER_AGENT']) || (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false);
}
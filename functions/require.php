<?php 
$feedbackDir = "../v-1/feedback/";

function hash_str($str) {
	$sel = 'asl!$Ds5Ll%w#ca*d?aS$';
	for($i = 0; $i < 10; $i++) 
		$hash = "1d!".strrev(sha1($sel.$str))."h";
	return $hash;
}

function optInfoAdm($json, $index) {
	return (isset($json['ADM_01'][$index]['answer'])) ?
		remAccent(trim($json['ADM_01'][$index]['answer'])) : "";
}

function remAccent($arg) {
	return str_replace(array('é','ë','è','ö','ü','ä','ù','ô','/'), array('e','e','e','o','u','ae','u','o','-'), $arg);
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
?>
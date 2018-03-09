<?php
require_once('../required/const.php');

function getVersions() {
	$listVersions = array();
	foreach(scandir('../'.DIR_VERSIONS, 1) as $file) {
		if ($file != "." && $file != ".." && is_dir('../'.DIR_VERSIONS.'/'.$file)) {
			$split = explode("-", $file);
			$version = $split[1];
			$lang = $split[2];
			$listVersions[] = array(
				"file" => $file,
				"version" => $version,
				"lang" => $lang
			);
		}
	}
	return $listVersions; //json_encode($listVersions, true);
}

?>
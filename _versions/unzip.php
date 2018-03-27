<?php
require_once("../required/const.php");
$fileUnziped = 0;
foreach(scandir(".", 1) as $file) {
	if ($file != "." && $file != ".." && !is_dir($file) && strpos($file, '.zip') !== false) {
		$zip = new ZipArchive;
		if ($zip->open($file) === TRUE) {
			$zip->extractTo('./'.str_replace('.zip', '', $file));
			$zip->close();
			unlink($file);
			echo $file.' - ok';
		} else {
			echo $file.' - échec';
		}
		$fileUnziped++;
	}
}

if($fileUnziped == 0) {
	echo 'Rien à dézipper';
}
/*

*/
?>
<?php 
if(isset($_COOKIE['version'])) {
	echo sha1(uniqid().$_COOKIE['version']).".json";
}
?>
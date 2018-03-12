<?php
interface iQuestion {
	public function draw();
	public function getResult();
	public function setResult();
	public function parseQuestion($json);
	
}
?>
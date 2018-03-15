<?php
interface iQuestion {
	public function draw();
	public function setJSONAnswer($json);
	public function getAnswer();
}
?>
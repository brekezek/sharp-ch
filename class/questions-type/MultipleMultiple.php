<?php
require_once("MultipleOne.php");

class MultipleMultiple extends MultipleOne {

	function __construct($index, $json) {
		parent::__construct($index, $json);
		parent::isMultiple(true);
	}
	
	function draw() {
		$this->inputName = $this->inputName."[]";
		return parent::draw();
	}
	
}
?>
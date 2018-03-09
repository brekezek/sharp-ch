<?php
class Color {
	
	private $colorName; 
	
	function __construct($colorName) {
		$this->colorName = $colorName;
	}
	
	function getColorName() {
		return $this->colorName;
	}
	function getClass() {
		return "cat-".$this->colorName;
	}
}
?>
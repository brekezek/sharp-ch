<?php
class Color {
	
	private $colorName; 
	private $colors = array(
	    "grey" => array(189, 195, 199),
	    "red" => array(192, 57, 43),
	    "blue" => array(52, 152, 219),
	    "green" => array(39, 174, 96),
	    "orange" => array(230, 126, 34)
	);
	
	function __construct($colorName) {
		$this->colorName = $colorName;
	}
	
	function getColorName() {
		return $this->colorName;
	}
	function getClass() {
		return "cat-".$this->colorName;
	}
	function getRGBA($alpha = 1) {
	    return "rgba(".implode(",", $this->colors[$this->colorName]).",".$alpha.")";
	}
	function getRGBRaw() {
	    $color = $this->colors[$this->colorName];
	    return array("r" => $color[0], "g" => $color[1], "b" => $color[2]);
	}
	function getTextColor($alpha = 1) {
	    return $this->colorName == "grey" ? "rgba(0,0,0,".$alpha.")" : "rgba(255,255,255,".$alpha.")";
	}
}
?>
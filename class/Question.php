<?php
require_once("InterfaceQuestion.php");

class Question implements InterfaceQuestion {
	private $index;
	private $type;
	private $title;
	private $mandatory;
	private $color;
	private $currentIndex;
	
	function __construct($index, $json) {
		$this->index = $index;
		$this->parseQuestion($json);
	}
	
	function parseQuestion($json) {
		$this->type = $json['question-type'];
		$this->title = $json['title'];
		$this->mandatory = isset($json['mandatory']) ? $json['mandatory'] : false;
	}
	
	function setCurrentIndex($index) {
		$this->currentIndex = $index;
	}
	
	function isMandatory() {
		return ($this->mandatory == true || $this->mandatory == 1 || $this->mandatory == "true");
	}
	
	function getTitle() {
		return $this->title;
	}
	
	function setColor($color) {
		$this->color = $color;
	}
	
	function draw() {
		
	}
	
	function getResult() {
		
	}
	
	function setResult() {
		
	}
	
}
/*
question-type
scoring-type
scoring
title
choices
mandatory
lines
columns
scoring-function
scoring-grid
result-required
hidden
result-define
placeholder
all_visible

---- tables ----
title
type
scoring
scoring2
col-based-on
choices
scoring-type
scoring-grid
score-reversed
result-define
result-required
scoring-function
scoring-range
ignore-choice-number
*/
?>
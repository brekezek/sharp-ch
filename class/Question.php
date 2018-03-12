<?php
require_once("InterfaceQuestion.php");

abstract class Question implements iQuestion {
	protected $index;
	protected $type;
	protected $title;
	protected $mandatory;
	protected $color;
	protected $currentIndex;
	
	protected $inputName;
	protected $uid;

	
	function __construct($index, $json) {
		$this->index = $index;
		
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
	
	function getLabel() {
		$mandatoryStar = "";
		if($this->isMandatory()) {
			$mandatoryStar = '<span class="text-danger font-weight-bold">*</span>';
		}
		return
		'<label
			for="'.$this->uid.'"
			class="w-100 p-2 rounded">
				<b>'.$mandatoryStar.$this->index.'.</b> '.$this->title.'
		</label>';
	}
	
	function parseQuestion($json) {}
	function getResult() {}
	function setResult() {}
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
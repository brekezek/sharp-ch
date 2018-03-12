<?php
class Text extends Question {

	function __construct($index, $json) {
		parent::__construct($index, $json);
	}
	
	function parseQuestion($json) {}
	
	function draw() {
		return
		'<div class="form-group">'.
			parent::getLabel().
			'<textarea 
				 name="'.$this->inputName.'" 
				 id="'.$this->uid.'" 
				 placeholder="" 
				 class="form-control w-100 rounded" 
				 style="max-height:110px; min-height:40px; height:40px" 
				 '.(parent::isMandatory() ? "required" : "").'>'.parent::getAnswer().'</textarea>'.
		'</div>';
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
<?php
class MultipleOne extends Question {
	private $choices;
	
	function __construct($index, $json) {
		parent::__construct($index, $json);
		$this->parseQuestion($json);
	}
	
	function parseQuestion($json) {
		$this->choices = $json['choices'];
	}
	
	function draw() {
		$html =
		'<div class="form-group">'.
			parent::getLabel().
			'<select 
				 name="'.$this->inputName.'" 
				 id="'.$this->uid.'" 
				 placeholder="Texte" 
				 class="form-control w-100 rounded"
				 '.(parent::isMandatory() ? "required" : "").'>';
		
		foreach($this->choices as $choice) {
			$html .= '<option value="">'.$choice.'</option>';
		}
				
		$html .=
			'</select>'.
		'</div>';
		return $html;
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
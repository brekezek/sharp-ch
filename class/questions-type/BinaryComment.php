<?php
class BinaryComment extends Binary {
	
	private $comment;
	
	function __construct($index, $json) {
		parent::__construct($index, $json);
		parent::hasComment(true);
	}
	
	function draw() {
		global $t;

		$html = parent::startWrapper();
		$html .= parent::draw();
		
		$this->fetchJSON();
		
		$displayed = ($this->comment != "" && parent::getAnswer() == $this->choices[0]);
		
		$html .= '<textarea 
                 '.($this->readonly ? "readonly" : 'name="'.$this->inputName.'"').' '.
                 parent::getAdditionnalHTMLAttributes().' '.
				 'id="'.$this->uid.'" 
				 placeholder="'.$this->placeholder.'" 
				 class="form-control w-100 rounded mt-1" 
				 style="max-height:110px; min-height:40px; height:40px; '.($displayed ? "" : "display:none").'" 
				 '.(parent::isMandatory() ? "required" : "").'>'.$this->comment.'</textarea>';
				 
		$html .= parent::endWrapper();
		
		return $html;
	}
	
	private function fetchJSON() {
		$this->comment = isset($this->jsonAnswer['comment']) ? trim($this->jsonAnswer['comment']) : "";
		$this->inputName = "answers[".$this->aspectId."][".$this->index."][comment]";
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
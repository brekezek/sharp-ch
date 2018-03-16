<?php
class MultipleOne extends Question {
	protected $choices;
	protected $isMultiple;
	
	function __construct($index, $json) {
		parent::__construct($index, $json);
		$this->choices = getChoices($json['choices']);
	}
	
	function draw() {
		global $t;

		$html =
		'<div class="form-group">'.
			parent::getLabel().
			'<select 
				 '.($this->isMultiple ? ' multiple size="'.(count($this->choices)+1).'" ' : "").'
				 name="'.$this->inputName.'" 
				 id="'.$this->uid.'"  
				 class="form-control w-100 rounded"
				 aria-describedby="help_'.$this->uid.'"
				 '.(parent::isMandatory() ? "required" : "").'>';
		
				$html .= '<option style="color:#ddd" value=""';
				if(parent::getAnswer() == "") $html .= ' selected';
				$html .= '>';
				
				if($this->placeholder != "") 
					$html .= $this->placeholder; 
				else
					$html .= $t['choose...'];
				
				$html .= '</option>';
				
				foreach($this->choices as $choice) {
					$selected = "";
					if(parent::getAnswer() == $choice) $selected = "selected";
					$html .= '<option value="'.$choice.'" '.$selected.'>'.str_replace(OTHER_INPUT_TAG, '', $choice).'</option>';
				}
				
			$html .= '</select>';
			
			if($this->isMultiple) {
				$html .=
				'<small id="help_'.$this->uid.'" class="form-text text-muted text-center pc_only">'.
					$t['help_multiple_multiple'].
				'</small>';
			}
			
		$html .= '</div>';
		return $html;
	}
	
	protected function isMultiple($bool) {
		$this->isMultiple = $bool;
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
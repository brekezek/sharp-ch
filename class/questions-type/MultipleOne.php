<?php
class MultipleOne extends Question {
	protected $choices;
	protected $isMultiple;
	protected $otherExists;
	protected $maxChoiceSize = -1;
	
	function __construct($index, $json) {
		parent::__construct($index, $json);
		$this->choices = getChoices($json['choices']);
		$this->otherExists = false;
		
		foreach($this->choices as $choice) {
		    $size = strlen($choice);
		    if($this->maxChoiceSize < $size)
		        $this->maxChoiceSize = $size;
		}
	}
	
	function draw() {
		global $t;
			
		$html =
		parent::startWrapper().
			parent::getLabel().
			'<select 
                 '.parent::scoredAttr().' '.
                 parent::getAdditionnalHTMLAttributes().
                 ($this->readonly ? "disabled" : "").' 
                 style="'.parent::getTextColor().'" 
				 '.($this->isMultiple ? ' multiple size="'.(count($this->choices)).'" ' : "").
				 'name="'.$this->inputName.'" 
				 id="'.$this->uid.'"  
				 class="form-control w-100 rounded '.($this->isMultiple && $this->isInTable && $this->maxChoiceSize > 58 ? 'min-width-multiselect' : '').'"
                '.($this->otherExists ? 'other-exist="1"' : '').'
				 aria-describedby="help_'.$this->uid.'"
				 '.(parent::isMandatory() ? "required" : "").'>';
		
				if(!$this->isMultiple) {
					$html .= '<option style="color:#ddd" value=""';
					if(parent::getAnswer() == "") $html .= ' selected';
					$html .= '>';
					
					if($this->readonly && trim($this->getAnswer()) == "") {
					    $html .= "-";
					} else {
    					if($this->placeholder != "") 
    						$html .= $this->placeholder; 
    					else
    						$html .= $t['choose...'];
					}
					$html .= '</option>';
				}
				
				$otherSelected = false;
				foreach($this->choices as $choice) {
					$isOther = "false";
					if(strpos($choice, OTHER_INPUT_TAG) !== false) {
						$isOther = "true";
						$choice = str_replace(OTHER_INPUT_TAG, '', $choice);
					}
					$selected = "";
					
					if($this->isMultiple && is_array(parent::getAnswer())) {
						if(in_array($choice, getChoices(parent::getAnswer()))) $selected = "selected";
					} else {
						if(parent::getAnswer() == $choice) $selected = "selected";
					}
					
					if($isOther && $selected == "selected") $otherSelected = true;
					
					$html .= '<option isOther="'.$isOther.'" value="'.$choice.'" '.$selected.'>'.$choice.'</option>';
				}
				
			$html .= '</select>';
			
			if($this->isMultiple && !$this->isInTable) {
				$html .=
				'<small id="help_'.$this->uid.'" class="form-text text-muted text-center pc_only" '.($otherSelected ? 'style="display:none"' : "").'>'.
					$t['help_multiple_multiple'].
				'</small>';
			}
			
		$html .= parent::endWrapper();
		return $html;
	}
	
	protected function isMultiple($bool) {
		$this->isMultiple = $bool;
	}
	
	protected function otherExists($bool) {
	    $this->otherExists = $bool;
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
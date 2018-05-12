<?php
class Binary extends Question {
	protected $choices;
	protected $hasComment = false;
	
	function __construct($index, $json) {
		parent::__construct($index, $json);
		$this->choices = getChoices($json['choices']);
	}
	
	function draw() {
		global $t;
		
		$tab = array("checked","active");
		
		$html = "";
		
		if(!$this->hasComment)
			$html .= parent::startWrapper();
		
		$html .= parent::getLabel().
			'<div class="btn-group btn-group-toggle" data-toggle="buttons">';
			
			$i = 0;
			foreach($this->choices as $choice) {
				$id = ($i == 0) ? $this->uid : $this->uid."_".$i;
				
				$checked = array("","");
				
				// echo $this->getAnswer();
				if($this->getAnswer() == $choice) {
					$checked = $tab;
				} 
				
				$withComment = "";
				if($this->hasComment) {
					$withComment = "binary_comment";
				}
				
				$value = $choice;
				if(count($this->choices) == 2 && strlen(implode('', $this->choices)) <= 2*3) {
				    $value = 1 - $i;
				}
		
				$html .=
				'<label class="btn btn-secondary '.$checked[1].' '.$withComment.'">'.
					'<input index="'.$i.'" type="radio" id="'.$id.'" name="'.$this->inputName.'"
                    '.($this->readonly ? "disabled" : "").' '.parent::scoredAttr().' '.parent::getAdditionnalHTMLAttributes().' '.
					'value="'.$value.'" autocomplete="off" '.$checked[0].' '.(parent::isMandatory() ? "required" : "").'> '.$choice.
				'</label>';
				/*
				'<div class="custom-control custom-radio custom-control-inline">'.
					'<input type="radio" id="'.$id.'" name="'.$this->inputName.'" value="'.$choice.'" class="custom-control-input" >'.
					'<label class="custom-control-label" for="'.$id.'">'.$choice.'</label>'.
				'</div>';
				*/
				
				$i++;
			}
		

		
		$html .= '</div>';
		
		if(!$this->hasComment)
			$html .= parent::endWrapper();
		
		return $html;
	}
	
	public function getAnswer() {
	    $answer = parent::getAnswer();
	    if(is_bool($answer) || is_numeric($answer)) {
	        if($answer == true)
	            return $this->choices[0];
	        else 
	            return $this->choices[1];
	    }
	    return $answer;
	}
	
	protected function hasComment($bool) {
		$this->hasComment = $bool;
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
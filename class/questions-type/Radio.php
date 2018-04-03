<?php
class Radio extends Question {
	
    private $value;
    
	function __construct($index, $json) {
		parent::__construct($index, $json);
		$this->value = "";
	}
	
	function setValue($val) {
	    $this->value = $val;
	}
	
	function draw() {
		global $t;
		
		$html = "";

		$html .= parent::startWrapper();
		
		$html .= parent::getLabel();
		
		$answer = "";
		if($this->isTrue(parent::getAnswer())) {
		    $answer = '1';
		}
		
		$html .=
		'<div class="custom-control custom-radio custom-control-inline mr-0">'.
			'<input type="radio" id="'.$this->uid.'" name="radio_'.$this->inputName.'" '.parent::scoredAttr().' 
                value="'.$this->value.'" class="custom-control-input" '.($this->isTrue(parent::getAnswer()) ? "checked" : "").' 
                '.($this->readonly ? "disabled" : "").'>'.
			'<label type="radio" class="custom-control-label" for="'.$this->uid.'"></label>'.
			'<input type="hidden" value="'.$answer.'" name="'.$this->value.'" radio-group="'.$this->inputName.'"> '.
		'</div>';
		

		$html .= parent::endWrapper();
		
		return $html;
	}
	
	private function isTrue($val) {
	    return ($val === "true" || $val === true || $val === 1 || $val === "1");
	}
		
}
?>
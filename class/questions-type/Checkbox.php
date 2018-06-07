<?php
class Checkbox extends Question {
	
	function __construct($index, $json) {
		parent::__construct($index, $json);
	}
	
	function draw() {
		global $t;
		
		$html = "";

		$html .= parent::startWrapper();
		
		$html .= parent::getLabel();
		
		$answer = "0";
		if($this->isTrue(parent::getAnswer())) {
		    $answer = '1';
		}
		
		$html .=
		'<div class="custom-control custom-checkbox custom-control-inline mr-0">'.
			'<input type="checkbox" id="'.$this->uid.'" trigger="'.$this->inputName.'"
                '.($this->readonly ? "disabled" : '').' '.parent::scoredAttr().' '.parent::getAdditionnalHTMLAttributes().' '.
                'class="custom-control-input" '.($this->isTrue(parent::getAnswer()) ? "checked" : "").'>'.
			'<label type="checkbox" class="custom-control-label" for="'.$this->uid.'"></label>'.
			'<input type="hidden" value="'.$answer.'" '.($this->readonly ? '' : 'name="'.$this->inputName.'"').'> '.
		'</div>';
		

		$html .= parent::endWrapper();
		
		return $html;
	}
	
	private function isTrue($val) {
	    return ($val === "true" || $val === true || $val === 1 || $val === "1");
	}
		
}
?>
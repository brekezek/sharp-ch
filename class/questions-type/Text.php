<?php
class Text extends Question {

	function __construct($index, $json) {
		parent::__construct($index, $json);
	}
	
	function draw() {
		return
		parent::startWrapper().
			parent::getLabel().
			'<textarea data-adaptheight
                 '.($this->readonly ? "readonly" : "").' 
				 name="'.$this->inputName.'" 
				 id="'.$this->uid.'" 
				 placeholder="'.$this->placeholder.'" 
				 class="form-control w-100 rounded" 
				 style="max-height:110px; min-height:40px; height:40px; min-width: 128px; '.parent::getTextColor().'" 
				 '.(parent::isMandatory() ? "required" : "").'>'.(($this->readonly && parent::getAnswer() == "") ? "-" : "").parent::getAnswer().'</textarea>'.
		parent::endWrapper();
	}
	
}
?>
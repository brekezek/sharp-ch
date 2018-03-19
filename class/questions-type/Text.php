<?php
class Text extends Question {

	function __construct($index, $json) {
		parent::__construct($index, $json);
	}
	
	function draw() {
		return
		parent::startWrapper().
			parent::getLabel().
			'<textarea 
				 name="'.$this->inputName.'" 
				 id="'.$this->uid.'" 
				 placeholder="'.$this->placeholder.'" 
				 class="form-control w-100 rounded" 
				 style="max-height:110px; min-height:40px; height:40px; min-width: 128px" 
				 '.(parent::isMandatory() ? "required" : "").'>'.parent::getAnswer().'</textarea>'.
		parent::endWrapper();
	}
	
}
?>
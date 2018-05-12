<?php
class Integer extends Question {

	function __construct($index, $json) {
		parent::__construct($index, $json);
	}
	
	function draw() {
		global $t;
		return
		parent::startWrapper().
			parent::getLabel().
			'<input 
                 '.parent::scoredAttr().' '.
                 parent::getAdditionnalHTMLAttributes().' '.
                 ($this->readonly ? "readonly" : "").'
				 type="number" 
				 name="'.$this->inputName.'" 
				 id="'.$this->uid.'" 
				 placeholder="'.($this->placeholder != "" ? $this->placeholder : "0").'" 
				 min="0"
				 class="form-control w-100 rounded '.($this->isInTable ? "text-center" : "").'" 
				 style="max-height:110px; min-height:40px; height:40px; '.parent::getTextColor().'"
				 value="'.parent::getAnswer().'"
				 '.(parent::isMandatory() ? "required" : "").'>'.
		parent::endWrapper();
	}
	
}
?>
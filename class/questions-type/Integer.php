<?php
class Integer extends Question {

	function __construct($index, $json) {
		parent::__construct($index, $json);
	}
	
	function draw() {
		global $t;
		return
		'<div class="form-group">'.
			parent::getLabel().
			'<input 
				 type="number" 
				 name="'.$this->inputName.'" 
				 id="'.$this->uid.'" 
				 placeholder="'.($this->placeholder != "" ? $this->placeholder : $t['number']).'" 
				 min="0"
				 class="form-control w-100 rounded" 
				 style="max-height:110px; min-height:40px; height:40px"
				 value="'.parent::getAnswer().'"
				 '.(parent::isMandatory() ? "required" : "").'>'.
		'</div>';
	}
	
}
?>
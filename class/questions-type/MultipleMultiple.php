<?php
require_once("MultipleOne.php");

class MultipleMultiple extends MultipleOne {

	private $comment;
	
	function __construct($index, $json) {
		parent::__construct($index, $json);
		parent::isMultiple(true);
	}
	
	function draw() {
		global $t;
		
		$html = "";
		$otherExist = false;
		$otherSelected = false;
		
		foreach($this->choices as $choice) {
			if(strpos($choice, OTHER_INPUT_TAG) !== false) {
				if(is_array(parent::getAnswer()) && in_array(str_replace(OTHER_INPUT_TAG, '', $choice), getChoices(parent::getAnswer()))) {
					$otherSelected = true;
				}
				$otherExist = true;
			}
		}
		
		if($otherExist) {
		    $html .= parent::startWrapper();
		}
		
		$this->inputName = $this->inputName."[]";
		$html .= parent::draw();
			
		if($otherExist) {
				$this->fetchJSON();
				
				$displayed = $this->comment != "" || $otherSelected;
				
				$html .= '<textarea 
						 name="'.$this->inputName.'" 
						 id="'.$this->uid.'" 
						 placeholder="'.(trim($this->placeholder) == "" ? $t['other_placeholder'] : $this->placeholder).'" 
						 class="form-control w-100 rounded mt-1" 
						 style="max-height:110px; min-height:40px; height:40px; '.($displayed ? "" : "display:none").'" 
						 >'.$this->comment.'</textarea>';
						 
						 
			$html .= parent::endWrapper();
		}
		
		return $html;
	}
	
	private function fetchJSON() {
		$this->comment = isset($this->jsonAnswer['comment']) ? trim($this->jsonAnswer['comment']) : "";
		$this->inputName = "answers[".$this->aspectId."][".$this->index."][comment]";
	}
	
}
?>
<?php
require_once("InterfaceQuestion.php");

abstract class Question implements iQuestion {
	protected $index;
	protected $type;
	protected $title;
	protected $mandatory;
	protected $color;
	protected $currentIndex;
	protected $aspectId;
	protected $inputName;
	protected $uid;
	protected $jsonAnswer;
	protected $answer;
	protected $hidden;
	protected $placeholder;
	protected $all_visible;
	
	function __construct($index, $json) {
		$this->index = $index;
		
		$this->type = $json['question-type'];
		$this->title = $json['title'];
		$this->mandatory = isset($json['mandatory']) ? $json['mandatory'] : false;
		$this->hidden = isset($json['hidden']) ? $json['hidden'] : false;
		$this->placeholder = isset($json['placeholder']) ? $json['placeholder'] : "";
		$this->all_visible = isset($json['all_visible']) ? $json['all_visible'] : true;
		
		$this->uid = $this->type."_".uniqid();
		
	}
	
	public function setAspectId($id) {
		$this->aspectId = $id;
		$this->inputName = "answers[".$this->aspectId."][".$this->index."][answer]";
	}
	
	public function setColor($color) {
		$this->color = $color;
	}

	public function setCurrentIndex($index) {
		$this->currentIndex = $index;
	}
	
	public function setJSONAnswer($json) {
		$this->jsonAnswer = $json;
		if(isset($json['answer'])) {
			$this->answer = $json['answer'];
		}
	}
	
	protected function isMandatory() {
		return ($this->mandatory == true || $this->mandatory == 1 || $this->mandatory == "true");
	}
	
	protected function getTitle() {
		return $this->title;
	}
	
	public function getIndex() {
		return $this->index;
	}
	
	protected function getLabel() {
		$mandatoryStar = "";
		if($this->isMandatory()) {
			$mandatoryStar = '<span class="text-danger font-weight-bold">*</span>';
		}
		return
		'<label
			for="'.$this->uid.'"
			class="w-100 p-2 rounded">
				<b>'.$mandatoryStar.$this->index.'.</b> '.$this->title.'
		</label>';
	}
	
	public function getAnswer() {
		if(is_array($this->answer)) {
			return $this->answer;
		} else {
			if(trim($this->answer) != "") {
				return $this->answer;
			}
		}
		return "";
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
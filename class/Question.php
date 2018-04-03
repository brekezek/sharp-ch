<?php
require_once("InterfaceQuestion.php");

abstract class Question implements iQuestion {
    protected $jsonQuestion;
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
	protected $readonly;
	protected $isInTable;
	protected $scored;
	
	function __construct($index, $json) {
		$this->index = $index;
		
		$this->jsonQuestion = $json;
		$this->type = $json['question-type'];
		$this->title = $json['title'];
		$this->mandatory = isset($json['mandatory']) ? $json['mandatory'] : false;
		$this->hidden = isset($json['hidden']) ? $json['hidden'] : false;
		$this->placeholder = isset($json['placeholder']) ? $json['placeholder'] : "";
		$this->all_visible = isset($json['all_visible']) ? $json['all_visible'] : false;
		
		$this->scored = isset($json['scoring']) && $json['scoring'] != "-";
		
		$this->uid = $this->type."_".uniqid();
		$this->isInTable = false;
		$this->readonly = false;
	}
	
	public function setAspectId($id) {
		$this->aspectId = $id;
		
		$this->inputName = "answers[".$this->aspectId."][".$this->index."]";
		if(!$this->isInTable) {
		  $this->inputName .= "[answer]";
		}
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
	
	protected function scoredAttr() {
	    if($this->scored) {
	        $attr = 'scored="true"';
	        if($this->isInTable) {
	            $attr .= ' isInTable="true"';
	        }
	        if(isset($this->jsonQuestion['result-required'])) {
	            $attr .= ' result-required="'.$this->jsonQuestion['result-required'].'"';
	        }
	        if(isset($this->jsonQuestion['result-define'])) {
	            $attr .= ' result-define="'.$this->jsonQuestion['result-define'].'"';
	        }
	        return $attr." ";
	    } else {
	        return '';
	    }
	}
	
	protected function getTitle() {
		return $this->title;
	}
	
	public function getIndex() {
		return $this->index;
	}
	
	protected function getLabel() {
		if($this->isInTable) return "";
		
		$mandatoryStar = "";
		if($this->isMandatory()) {
			$mandatoryStar = '<span class="text-danger font-weight-bold">*</span>';
		}
		
		if($this->type == "table") {
			return '<b>'.$mandatoryStar.$this->index.'.</b> '.$this->title;
		} else {
			return 
			'<label
				for="'.$this->uid.'"
				class="w-100 p-2 rounded">
					<b>'.$mandatoryStar.$this->index.'.</b> '.$this->title.'
			</label>';
		}
	}
	
	protected function getTextColor() {
	    if($this->readonly) {
	        if($this->getAnswer() == "")
	            return "color:red";
	        else
	            return "color:green";
	    } else {
	        return "";
	    }
	}
	
	public function isInTable($bool) {
		$this->isInTable = $bool;
	}
	
	public function getAnswer() {
		if(is_array($this->answer)) {
			return $this->answer;
		} else {
		    if(is_bool($this->answer)) {
		        return (bool)$this->answer;
		    }
			if(trim($this->answer) != "") {
				return $this->answer;
			}
		}
		return "";
	}
	
	public function setReadOnly($readonly) {
	    $this->readonly = $readonly;
	}
	
	protected function startWrapper() {
	    return $this->isInTable ? '' : '<div class="form-group" numQuest="'.$this->index.'">';
	}
	
	protected function endWrapper() {
	    return $this->isInTable ? '' : '</div>';
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
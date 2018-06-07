<?php
class Text extends Question {

	function __construct($index, $json) {
		parent::__construct($index, $json);
	}
	
	function draw() {
	    $inputType = $this->inputType == null ? "textarea" : "input";
		
		$html = parent::startWrapper();
		$html.= parent::getLabel();
		
		/*
		if(isset($this->jsonQuestion['dataSource'])) {
		    parent::bindDataSource($this->jsonQuestion['dataSource']);
		    $html .= $this->dataSource;
		}
		*/
		
		$html.=	'<'.$inputType.' data-adaptheight 
                 '.($this->readonly ? "readonly" : 'name="'.$this->inputName.'"').' 
                 '.parent::scoredAttr(). 
                 parent::getAdditionnalHTMLAttributes().
				 ' id="'.$this->uid.'" 
				 placeholder="'.$this->placeholder.'" 
				 class="form-control w-100 rounded" 
				 style="max-height:210px; min-height:40px; height:40px; min-width: 128px; '.parent::getTextColor().';" ';
        		 if($inputType != "textarea") {
        		      $html .= 'type="'.$this->inputType.'" ';
        		      $html .= 'value="'.(($this->readonly && parent::getAnswer() == "") ? "-" : "").parent::getAnswer().'" ';
        		 }
				 $html.= (parent::isMandatory() ? "required" : "").'>';
		
		if($this->inputType == null) {
    	    $html .= (($this->readonly && parent::getAnswer() == "") ? "-" : "");
    	   	$html .= parent::getAnswer();
    	   	$html .= '</'.$inputType.'>';
		}
	   	
		$html.= parent::endWrapper();
		
		return $html;
	}
	
}
?>
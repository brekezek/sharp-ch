<?php
class QuestionFactory {
	
	private $type;
	private $index;
	private $json;
	
	public function __construct($index, $json) {
		$this->type = $json['question-type'];
		$this->index = $index;
		$this->json = $json;
	}
	
	public function getQuestion() {
		switch($this->type) {
			case "text": 
			case "text_answer":
				return new Text($this->index, $this->json);
			break;
			
			// ------------------------------------------
			
			case "multiple_multiple_solution":
				return null;
			break;
			
			// ------------------------------------------
			
			case "multiple_one_solution":
				return null; //new MultipleOne($this->index, $this->json);
			break;
			
			// ------------------------------------------
			
			case "binary_answer_with_comment":
			case "binary_answer":
				return null;
			break;
			
			// ------------------------------------------
			case "integer_answer":
				return null;
			break;
			
			// ------------------------------------------
			case "table":
				return null;
			break;
			
		}
	}
}
?>
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
			
			case "multiple_multiple_solution":
				return new MultipleMultiple($this->index, $this->json);
			break;
			
			case "multiple_one_solution":
				if(count($this->json['choices']) == 3)
					return new Binary($this->index, $this->json);
				else 
					return new MultipleOne($this->index, $this->json);
			break;
			
			case "binary_answer_with_comment":
				return new BinaryComment($this->index, $this->json);;	
			break;
			
			case "binary_answer":
				return new Binary($this->index, $this->json);
			break;
			
			case "integer_answer":
				return new Integer($this->index, $this->json);
			break;
			
			case "table":
				return null;
			break;
			
		}
	}
}
?>
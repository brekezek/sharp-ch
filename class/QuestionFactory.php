<?php
class QuestionFactory {
	
	private $type;
	private $index;
	private $json;
	private $isInTable;
	
	public function __construct($index, $json) {
		$this->type = $json['question-type'];
		$this->index = $index;
		$this->json = $json;
		$this->isInTable = false;
	}
	
	public function isInTable($isInTable) {
		$this->isInTable = $isInTable;
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
				if(count($this->json['choices']) == 3 && !$this->isInTable)
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
				return new Table($this->index, $this->json);
			break;
			
			case "toggle":
				return new Checkbox($this->index, $this->json);
			break;
			
			case "toggle_one":
				return new Radio($this->index, $this->json);
			break;
		}
	}
}
?>
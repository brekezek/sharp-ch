<?php
class Table extends Question {
	protected $rows;
	protected $columns;
	private $equivQuestionType;
	
	function __construct($index, $json) {
		parent::__construct($index, $json);
		
		$this->rows = getChoices($json['lines']);
		$this->columns = $json['columns'];
		
		$this->equivQuestionType = array(
			"binary" => "binary_answer",
			"toggle" => "toggle", // Une case à cocher
			"toggle_exactly_one" => "toggle_one", // un groupe bouton radio, un seul choix possible dans la colonne
			"integer" => "integer_answer",
			"text" => "text_answer",
			"choice" => "multiple_one_solution",
			"choice_multiple" => "multiple_multiple_solution"
		);
		
		
	}
	
	function draw() {
		global $t;
		
		//echo '<pre>';
		//print_r($this->jsonAnswer);
		
		$html =
		'<div class="form-group table-responsive">';
			
	    $uniq_data_type = '';
		if(count($this->columns) == 1) {
		    $uniq_data_type = 'data-type="'.$this->equivQuestionType[$this->columns[0]['type']].'"';
		}
		
		$html .= '<table class="table table-striped table-hover" '.$uniq_data_type.'>
		  <thead>
			<tr>
				<th scope="col" class="align-middle border-right border-top-0">'.parent::getLabel().'</th>';
				foreach($this->columns as $col) {
					$html .= '<th scope="col" class="text-center align-middle border-right border-top-0">'.$col['title'].'</th>';
				}
			$html.=
			'</tr>
		  </thead>
		  <tbody>';
			
		  $indexRow = 0;
		  foreach($this->rows as $row) {
				$row = str_replace(OTHER_INPUT_TAG, '', $row);
				$html .= '<tr>';
					$html .= '<td class="align-middle border-right">'.$row.'</td>';
					foreach($this->columns as $indexCol => $col) {
						$json = $col;
						$json['question-type'] = $this->equivQuestionType[$col['type']];
						
						if($col['type'] == "binary") {
							$json['choices'] = array($t['yes'], $t['no']);
						}
						
						$questFactory = new QuestionFactory($this->index, $json);
						$questFactory->isInTable(true);
						$questionObj = $questFactory->getQuestion();
						
						if($questionObj != null) {
							$questionObj->isInTable(true);
							$questionObj->setAspectId($this->aspectId);
							
							if(isset($this->jsonAnswer[$indexRow]) && isset($this->jsonAnswer[$indexRow][$indexCol])) {
							    $questionObj->setJSONAnswer($this->jsonAnswer[$indexRow][$indexCol]);
							} 
							
							if($json['question-type'] == "toggle_one") {
							    $questionObj->inputName .= "[".$indexCol."][answer]";
							    $questionObj->setValue("answers[".$this->aspectId."][".$this->index."][".$indexRow."][".$indexCol."][answer]");
							} else {
							     $questionObj->inputName .= "[".$indexRow."][".$indexCol."][answer]";
							}
						}
						$html .= '<td data-type="'.$json['question-type'].'" class="align-middle text-center border-right">'.($questionObj == null ? "" : $questionObj->draw()).'</td>';
					}
				$html .= '</tr>';
			    ++$indexRow;
		  } 
			
		 $html .= '</tbody>'.
		 '</table>';
		 
			
		$html .= '</div>';
		return $html;
	}
	
}
?>
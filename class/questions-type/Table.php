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
			
		$html =
		'<div class="form-group">';
			
		$html .= '<table class="table table-striped">
		  <thead>
			<tr>
				<th scope="col">'.parent::getLabel().'</th>';
				foreach($this->columns as $col) {
					$html .= '<th scope="col">'.$col['title'].'</th>';
				}
			$html.=
			'</tr>
		  </thead>
		  <tbody>';
		  
		  foreach($this->rows as $row) {
				$row = str_replace(OTHER_INPUT_TAG, '', $row);
				$html .= '<tr>';
					$html .= '<td>'.$row.'</td>';
					foreach($this->columns as $col) {
						$json = $col;
						$json['question-type'] = $this->equivQuestionType[$col['type']];
						
						if($col['type'] == "binary") {
							$json['choices'] = array($t['yes'], $t['no']);
						}
						
						$questFactory = new QuestionFactory(0, $json);
						$questionObj = $questFactory->getQuestion();
						
						if($questionObj != null)
							$questionObj->isInTable(true);
						
						$html .= '<td>'.($questionObj == null ? "" : $questionObj->draw()).'</td>';
					}
				$html .= '</tr>';
		  } 
			
		  $html .= '</tbody>'.
		'</table>';
			
		$html .= '</div>';
		return $html;
	}
	
}
?>
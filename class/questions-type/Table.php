<?php
class Table extends Question {
	protected $rows;
	protected $columns;
	private $equivQuestionType;
	private $dependencesAnswers = array();
	private $dynamicTable = false;
	
	function __construct($index, $json) {
		parent::__construct($index, $json);
		
		$this->dynamicTable = isset($this->jsonQuestion['dynamic-table']) ? $this->jsonQuestion['dynamic-table'] : false;
		
		$this->rows = getChoices($json['lines']);
		$this->columns = $json['columns'];
		
		$this->equivQuestionType = array(
			"binary" => "binary_answer",
			"toggle" => "toggle", // Une case a� cocher
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
		'<div class="form-group table-responsive '.($this->enabled ? '' : 'filtered').'" numQuest="'.$this->index.'">';
			
	    $uniq_data_type = '';
		if(count($this->columns) == 1) {
		    $uniq_data_type = 'data-type="'.$this->equivQuestionType[$this->columns[0]['type']].'"';
		}
		
		if($this->dynamicTable && count($this->jsonAnswer) > count($this->rows)) {
		    $this->rows = array_unique(array_merge($this->rows, array_map(function($item){ return $item+1; }, array_keys($this->jsonAnswer))));
		}
		
		$html .= '<table class="table table-striped '.($this->readonly ? '' : 'table-hover').' '.($this->dynamicTable ? "dynamic-table" : "").'" '.$uniq_data_type.'>
		  <thead>
			<tr>
				<th scope="col" class="align-middle border-right border-top-0">'.parent::getLabel().'</th>';
				foreach($this->columns as $col) {
					$html .= '<th scope="col" class="text-center align-middle border-right border-top">'.$col['title'].'</th>';
				}
			$html.=
			'</tr>
		  </thead>
		  <tbody>';
			
		  $indexRow = 0;
		  $dependenceInRow = array();
		  
		  foreach($this->rows as $row) {
		        $row_brut = $row;
				$row = str_replace(OTHER_INPUT_TAG, '', $row);
				$isOther = strpos($row_brut, OTHER_INPUT_TAG) !== false;
				
				$html .= '<tr '.($isOther ? "other" : "").' indexRow="'.$this->uid."_".$indexRow.'">';
				
					$html .= '<td class="align-middle border-right '.($isOther ? "border-bottom" : "").'">'.$row.'</td>';
					
					foreach($this->columns as $indexCol => $col) {
						$json = $col;
						$json['question-type'] = $this->equivQuestionType[$col['type']];
						
						if($col['type'] == "binary") {
							$json['choices'] = array($t['yes'], $t['no']);
						}
						
						// La colonne requière des données supplémentaires
						$autoFillAnswer = null;
						if(isset($col['autofill']) && !$this->readonly) {
                            $partsAutofill = explode(".", $col['autofill']);
                            $aspectId = $partsAutofill[0];
                            $questNum = $partsAutofill[1];
                            $colIndex = -1;
                            if(isset($partsAutofill[2])) $colIndex = $partsAutofill[2];
                            
                            if(!isset($this->dependencesAnswers[$indexCol])) {
                                $this->dependencesAnswers[$indexCol] = getJSONFromFile(getAbsolutePath().DIR_ANSWERS."/".$_COOKIE['filename']);
                            }
                            
                            if(isset($this->dependencesAnswers[$indexCol][$aspectId][$questNum])) {
                                $toExplore = $this->dependencesAnswers[$indexCol][$aspectId][$questNum];
                                if(is_array($toExplore)) {
                                    if(isset($toExplore[$indexRow][$colIndex]) && trim($toExplore[$indexRow][$colIndex]['answer']) != "") { 
                                        $autoFillAnswer = $toExplore[$indexRow][$colIndex];
                                        $dependenceInRow[$indexRow] = $indexRow;
                                    }   
                                }
                            }
						}
						

						$displayCell = $this->all_visible || count($this->columns) == 1 || $indexCol == 0 || (in_array($indexRow, $dependenceInRow) && !$this->readonly);
						$triggerDisplay = !$this->all_visible && $indexCol == 0 && count($this->columns) > 1;
						
						$questFactory = new QuestionFactory($this->index, $json);
						$questFactory->isInTable(true);
						$questionObj = $questFactory->getQuestion();
						
						if($questionObj != null) {
							$questionObj->isInTable(true);
							$questionObj->setAspectId($this->aspectId);
							$questionObj->setReadOnly($this->readonly);
							
							
							
							if(isset($this->jsonAnswer[$indexRow])) {    
							    if(isset($this->jsonAnswer[$indexRow][$indexCol])) {
							       $questionObj->setJSONAnswer($this->jsonAnswer[$indexRow][$indexCol]);
							    }
							} 
							
							if(isset($autoFillAnswer) && $autoFillAnswer != null && trim($this->jsonAnswer[$indexRow][$indexCol]['answer']) == "" && !$this->readonly) {
							    $questionObj->setJSONAnswer($autoFillAnswer);
							}
							
							
							if(!$displayCell) {
							    if(isset($this->jsonAnswer[$indexRow][0]['answer'])) {
							        $firstColAnswer = $this->jsonAnswer[$indexRow][0]['answer'];
							        $displayCell = (trim($firstColAnswer) != "" && trim($firstColAnswer) != "0");
							    }
							}
							
							if($json['question-type'] == "toggle_one") {
							    $questionObj->inputName .= "[".$indexCol."][answer]";
							    $questionObj->setValue("answers[".$this->aspectId."][".$this->index."][".$indexRow."][".$indexCol."][answer]");
							} else {
							     $questionObj->inputName .= "[".$indexRow."][".$indexCol."][answer]";
							}
						}
						
						
						$html .=
						'<td '.($triggerDisplay ? 'trigger-display="'.$this->uid."_".$indexRow.'"' : "").' data-type="'.$json['question-type'].'" class="align-middle text-center border-right border-bottom">'.
						  '<span class="display-manager" style="'.($displayCell ? "" : "display:none").'">'.
						      ($questionObj == null ? "" : $questionObj->draw()).
						  '</span>'.
						'</td>';
						
    				      
						   
					}
					
					if($this->dynamicTable && !$this->readonly) {
					    $html .= '<td class="border-bottom">';
					    $html .= '<button type="button" class="delete-row btn btn-danger '.($indexRow > 0 ? "" : "d-none" ).'"><span class="oi oi-delete mr-1"></span> '.$t['del'].'</button>';
					    $html .= '</td>';
					}
					
				$html .= '</tr>';
				
				// Other exists
				if($isOther) {
				    /*
				    $otherFilled = false;
				    if(isset($this->jsonAnswer[$indexRow])) {
    				    foreach($this->jsonAnswer[$indexRow] as $rowAnswer) {
    				        if(isset($rowAnswer['answer'])) {
    				            if(trim($rowAnswer['answer']) != "" && $rowAnswer['answer'] != "0") {
    				                $otherFilled = true;
    				                break;
    				            }
    				        }
    				    }
				    }*/
				    
				    $comment = isset($this->jsonAnswer[$indexRow]['comment']) ? trim($this->jsonAnswer[$indexRow]['comment']) : "";
				    $inputName = "answers[".$this->aspectId."][".$this->index."][".$indexRow."][comment]";
				    //$displayed = true; //$otherFilled === true || ($comment != "");
				    
				    $html .=
                    '<tr other-field style="background:'.(($indexRow % 2 == 0) ? "rgba(0,0,0,.05)" : "transparent").';">'.
				        '<td class="border border-left-0" colspan="'.(count($this->columns)+1).'">'.
				        '<input class="form-control w-100 rounded" name="'.$inputName.'" id="'."text_".uniqid().'" 
						      placeholder="'.$t['other_placeholder'].'" type="text" value="'.$comment.'" '.($this->readonly ? "readonly" : "").'>'.
				        '</td>'.
				    '</tr>';
				}
				
			    ++$indexRow;
		  } 
			
		 $html .= '</tbody>';
		 
		 if($this->dynamicTable && !$this->readonly) {
		     $html .= '<tfoot>';
		     $html .=
  		     '<tr><td class="bg-white border-bottom text-center p-0" colspan="'.(count($this->columns)+2).'">'.
	           '<button type="button" id="add-row" class="btn btn-normal w-100 h-100 rounded-0 py-3 btn-lg text-muted">'.$t['add'].' <span class="oi oi-plus lead float-right mt-1"></span></button>'.
             '</td></tr>';
		     $html .= '</tfoot>';
		 }
		 
		 $html .= '</table>';
		 
			
		$html .= '</div>';
		return $html;
	}
	
}
?>
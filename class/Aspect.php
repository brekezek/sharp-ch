<?php
class Aspect {
	/* String */
	private $id;
    /* String */
	private $title;
    /* Couleur */
	private $color;
	/* String */
    private $subtitle;
    
    private $imgFile;
	/* List<ParsedAbstractQuestion> */
	private $questions;
	/* Int */
	private $index;
	/* Int : Ou on est dans le questionnaire (ex. ->5<-/53) */
	private $currentIndex;
	
	private $jsonAnswers;
	
	private $readonly;
	
	function __construct($id, $title, $color, $subtitle, $index) {
		$this->id = $id;
		$this->title = $title;
		$this->color = $color;
		$this->subtitle = $subtitle;
		$this->questions = array();
		$this->index = $index;
		$this->readonly = false;
		$this->imgFile = "img/questionnaire/".$this->id.".png";
	}
	
	public function getColor() {
		return $this->color;
	}
	
	public function setCurrentIndex($index) {
		$this->currentIndex = $index;
		foreach($this->questions as $question)
			$question->setCurrentIndex($index);
	}
	
	public function addQuestion($question) {
		$question->setColor($this->color);
		$question->setAspectId($this->id);
		
		if(isset($this->jsonAnswers[$question->getIndex()]))
			$question->setJSONAnswer($this->jsonAnswers[$question->getIndex()]);
		
		array_push($this->questions, $question);
	}
	
	public function draw($currentIndex, $nbAspects) {
		$this->drawHeader($currentIndex, $nbAspects);
		$this->drawQuestions();
	}
	
	public function drawQuestions() {
		$html = 
		'<div class="rounded container bg-light p-2 my-3">';
		foreach($this->questions as $question) {
		    $question->setReadOnly($this->readonly);
			$html .= $question->draw();
		}
		$html .=
		'</div>';
		echo $html;
	}
	
	private function drawHeader($currentIndex, $nbAspects) {
		$html = 
		'<div id="aspect-header" data-aspect-id="'.$this->id.'" class="aspect-header '.$this->color->getClass().' rounded container d-flex justify-content-start align-items-center">'.
			'<div class="px-1 font-weight-bold text-center" style="min-width:78px;">';
		         if(file_exists($this->imgFile)) {
			         $html .= '<div class=""><img src="'.$this->imgFile.'" style="max-height:44px; max-width: 125px;"></div>';
		         }
			     $html .= $currentIndex." / ".$nbAspects.
			'</div>'.
			'<div class="p-3 w-100 pl-0">'.
			'<h5>'.$this->subtitle.'</h5>'.
				'<div>'.
				'<h6 class="float-left">'.$this->title.'</h6>'.
					'<div class="badge badge-dark p-2 float-right text-align-right">'.$this->id.'</div>'.
				'</div>'.
			'</div>'.
		'</div>';
		echo $html;
	}
	
	public function drawThumbnail() {
	    $title = $this->title;
	    
	    $subtitleWords = explode(" ", $this->subtitle);
	    $subtitle = "";
	    for($i = 0; $i < 4; $i++) {
	        if(isset($subtitleWords[$i]))
    	        $subtitle .= $subtitleWords[$i]." ";
	    }
	    $subtitle = trim($subtitle);
	    if(count($subtitleWords) > 3) {
	        $subtitle .= "...";
	    }
	    if($this->id == "ADM_01") {
	       $subtitle = $this->title;
	       $title = "";
	    }
	    
	    global $t;
		$html = '
		<div data-toggle="tooltip" data-placement="top" title="'.$this->subtitle.'" data-index="'.$this->index.'" class="card text-center rounded m-2 cat-hover cat-border-'.$this->color->getColorName().' '.($this->index == $this->currentIndex ? "cat-active" : "").'" style="width: 12.7%; max-width: 160px; min-width:140px; max-height: 180px">
		  <div class="card-header d-flex p-1 '.$this->color->getClass().'">';
            
			$html .= '<div style="text-align:left; width:100%; padding-left:6px">'.$this->index.'</div>';
			if(file_exists($this->imgFile)) {
			    $html .= '<div style="padding-right:4px; margin-top:-2px"><img src="'.$this->imgFile.'" style="max-height:16px; max-width: 75px;"></div>';
			}
		  $html .= '</div>

		  <div class="card-body py-2 px-1" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:center">
			<h5 class="card-title small p-0 m-0 mb-2 font-weight-bold" style="width:130px; max-width:140px;">'.$subtitle.'</h5>
			<p class="card-text m-0 subtitle" style="max-width:140px; font-size:70%">'.$title.'</p>
			<p class="card-text small d-none">Score</p>
		  </div>
		  '.($this->index == $this->currentIndex ? '<div class="small bg-danger text-white">'.$t['active'].'</div>' : "").'
		  <div class="card-footer p-1 '.$this->color->getClass().'">
			'.$this->id.'
		  </div>
		</div>';
		echo $html;
	}
	
	/* ------ JSON processing ------- */
	public function setJSONAnswers($json) {
		$this->jsonAnswers = $json;
	}
	
	
	public function setReadOnly($readonly) {
	    $this->readonly = $readonly;
	}
}
?>
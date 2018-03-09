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
	/* List<ParsedAbstractQuestion> */
	private $questions;
	/* Int */
	private $index;
	/* Int : Ou on est dans le questionnaire (ex. ->5<-/53) */
	private $currentIndex;
	
	function __construct($id, $title, $color, $subtitle, $index) {
		$this->id = $id;
		$this->title = $title;
		$this->color = $color;
		$this->subtitle = $subtitle;
		$this->questions = array();
		$this->index = $index;
	}
	
	function setCurrentIndex($index) {
		$this->currentIndex = $index;
		foreach($this->questions as $question)
			$question->setCurrentIndex($index);
	}
	
	function addQuestion($question) {
		$question->setColor($this->color);
		array_push($this->questions, $question);
	}
	
	function draw($currentIndex, $nbAspects) {
		$this->drawHeader($currentIndex, $nbAspects);
		$this->drawQuestions();
	}
	
	function drawQuestions() {
		$html = 
		'<div class="rounded container bg-light p-2 my-3">Questions';
		foreach($this->questions as $question)
			$html .= $question->draw();
		$html .=
		'</div>';
		echo $html;
	}
	
	function drawHeader($currentIndex, $nbAspects) {
		$html = 
		'<div class="aspect-header '.$this->color->getClass().' rounded container d-flex justify-content-start align-items-center">'.
			'<div class="px-1 font-weight-bold" style="width:75px">'.$currentIndex." / ".$nbAspects.'</div>'.
			'<div class="p-3 w-100">'.
				'<h5>'.$this->title.'</h5>'.
				'<div>'.
					'<h6 class="float-left">'.$this->subtitle.'</h6>'.
					'<div class="badge badge-dark p-2 float-right text-align-right">'.$this->id.'</div>'.
				'</div>'.
			'</div>'.
		'</div>';
		echo $html;
	}
	
	function drawThumbnail() {
		$html = '
		<div data-index="'.$this->index.'" class="card text-center rounded m-2 cat-hover cat-border-'.$this->color->getColorName().' '.($this->index == $this->currentIndex ? "cat-active" : "").'" style="width: 12.7%; max-width: 160px; min-width:140px;">
		  <div class="card-header p-1 '.$this->color->getClass().'">
			'.$this->index.'
		  </div>
		  <div class="card-body py-2 px-1" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:center">
			<h5 class="card-title small p-0 m-0 mb-2 font-weight-bold" style="width:130px">'.$this->title.'</h5>
			<p class="card-text m-0" style="font-size:70%">'.mb_substr($this->subtitle, 0, 25).(strlen($this->subtitle) > 25 ? "..." : "").'</p>
			<p class="card-text small d-none">Score</p>
		  </div>
		  '.($this->index == $this->currentIndex ? '<div class="small bg-danger text-white">Actif</div>' : "").'
		  <div class="card-footer p-1 '.$this->color->getClass().'">
			'.$this->id.'
		  </div>
		</div>';
		echo $html;
	}
	/* ------ JSON processing ------- */
	
}
?>
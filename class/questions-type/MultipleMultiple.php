<?php
require_once("MultipleOne.php");

class MultipleMultiple extends MultipleOne {

	function __construct($index, $json) {
		parent::__construct($index, $json);
		parent::isMultiple(true);
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
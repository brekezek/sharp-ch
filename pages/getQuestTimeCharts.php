<?php
require_once '../required/common.php';

$endingTime = $_COOKIE['expirationQuest'];
$startingTime = $endingTime - LIFE_COOKIE_QUEST_PENDING*60*60*24;

$timeUsed = time() - $startingTime;
$timeLeft = $endingTime - time();

if(isset($_COOKIE['expirationQuest'])) {?>
	<link rel="stylesheet" href="css/circle-chart.css">
	<div class="" style="min-width: 250px;">
    	<?php drawCircleChart("blue", $timeUsed, $t['time-used']); ?>
    	<hr class="my-4">
    	<?php drawCircleChart("orange", $timeLeft, $t['time-left']); ?>
	</div>
<?php } ?>
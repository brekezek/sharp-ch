<?php require_once('../required/common.php'); ?>
<style>
.icon-red:before { color:#c0392b; }
.icon-blue:before { color:#2980b9; }
.icon-green:before { color:#27ae60; }
.icon-orange:before { color:#e67e22; }
</style>

<div class="text-center">
  <h1 class="display-4"><?= $t['instructions'] ?></h1>
</div>

<hr class="my-4">

<div class="d-flex align-items-center">
    <div class="oi oi-reload lead display-4 mr-4 text-muted icon-red"></div>
    <div class="pt-2">
        <div class="lead text-uppercase font-weight-bold"><?= $t['instruct-title1']?></div>
        <p class="mb-0 text-muted"><?= $t['instruct-content1']?></p>
    </div>
</div>

<hr class="my-4">

<div class="d-flex align-items-center">
    <div class="oi oi-timer lead display-4 mr-4 icon-blue"></div>
    <div class="pt-2">
        <div class="lead text-uppercase font-weight-bold"><?= LIFE_COOKIE_QUEST_PENDING." ".$t['jours'] ?></div>
        <p class="mb-0 text-muted"><?= $t['instruct-content2']?></p>
    </div>
</div>

<hr class="my-4">

<div class="d-flex align-items-center">
    <div class="oi oi-pencil lead display-4 mr-4 icon-green"></div>
    <div class="pt-2">
        <div class="lead text-uppercase font-weight-bold"><?= $t['instruct-title2']?></div>
        <p class="mb-0 text-muted"><?= $t['instruct-content3']?></p>
    </div>
</div>

<hr class="my-4">

<div class="d-flex align-items-center">
    <div class="oi oi-pie-chart lead display-4 mr-4 icon-orange"></div>
    <div class="pt-2">
        <div class="lead text-uppercase font-weight-bold"><?= $t['instruct-title3']?></div>
        <p class="mb-0 text-muted"><?= $t['instruct-content4']?></p>
    </div>
</div>

<hr class="my-4">
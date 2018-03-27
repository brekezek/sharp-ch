<?php
if(!isset($logged) || !$logged) {
    header('Location: ../../admin.php');
}
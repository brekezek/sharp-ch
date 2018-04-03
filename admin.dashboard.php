<?php
require_once 'required/common.php';
include_once 'required/db_connect.php';
include_once 'required/securite.fct.php';
includeDependencies();

sec_session_start();
$logged = login_check($mysqli);

if(!$logged) { 
    header('Location: required/logout.php');
}

if(isset($_SESSION['resultsDefined'])) {
    unset($_SESSION['resultsDefined']);
}

$idxPage = 1;
if(isset($_GET['page'])) {
    $idxPage = intval($_GET['page']);
}
$pages = array(
    1 => array("page" => "quest-collected.php", "padding" => false),
    6 => array("page" => "participants.php", "padding" => false)
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
	<link href="open-iconic/font/css/open-iconic-bootstrap.min.css" rel="stylesheet">
	<link href="css/dashboard.css" rel="stylesheet">
	
	<!-- Custom -->
	<link rel="stylesheet" href="css/style.css">
	
	<style>
	main {
	   margin-left:220px;
	   width:100%
	}
	.sidebar {
	   width: 220px;
	   max-width: 220px;
	}
	.sidebar-sticky {
	   margin-top:55px;
	}
	@media (max-width: 768px) { 
	   main {
	       margin-left: 0;
	   }
	   nav.navbar {
	       background: black!important;
	   }
	   .sidebar {
	       position: relative;
	       width: 100%;
	       max-width: none;
	   }
	   .sidebar-sticky {
	       position: relative;
	       height: auto;
	       margin-top:0;
	       top:0;
	   }
	}
	</style>
	
	<title>SHARP-CH</title>
	<link rel="shortcut icon" href="img/favicon.png">
</head>
<body>
	
	<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
        <?php 
        $name = $_SESSION['name'];
        if(strlen($name) > 18) $name = substr($name, 0, 17)."..";
        ?>
        
        <div class="navbar-brand col-sm-3 col-md-2 mr-0" style="width:220px; max-width:220px; flex:none">
       		<?= $name ?> <span class="badge badge-danger">admin</span>
        </div>
        
        <!-- <input class="form-control form-control-dark w-100 d-none" type="text" placeholder="Recherche" id="mainSearch"> -->
        
        <div class="w-100 text-left ml-2">
        	<a href="index.php" class="btn btn-outline-light btn-sm"><span class="oi oi-home mr-1"></span> Accueil</a>
        </div>
        
        <ul class="navbar-nav px-3">
            <li class="nav-item text-nowrap">
              <a class="nav-link" href="required/logout.php">Déconnexion</a>
            </li>
        </ul>
    </nav>
    
    <nav class="col-md-2 d-md-block bg-light sidebar">
          <div class="sidebar-sticky">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link <?= $idxPage == 1 ? "active" : "" ?>" href="?page=1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                   Questionnaires récoltés <span class="sr-only">(current)</span>
                </a>
              </li>
              
           
              <li class="nav-item d-none">
                <a class="nav-link <?= $idxPage == 3 ? "active" : "" ?>" href="?page=3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                  Customers
                </a>
              </li>
              <li class="nav-item d-none">
                <a class="nav-link <?= $idxPage == 4 ? "active" : "" ?>" href="?page=4">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                  Reports
                </a>
              </li>
              <li class="nav-item d-none">
                <a class="nav-link <?= $idxPage == 5 ? "active" : "" ?>" href="?page=5">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                  Integrations
                </a>
              </li>
            </ul>

            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
              <span>Gestion</span>
            
            </h6>
            <ul class="nav flex-column mb-2">
              <li class="nav-item">
                <a class="nav-link <?= $idxPage == 6 ? "active" : "" ?>" href="?page=6">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                  Participants
                </a>
              </li>
              
              <li class="nav-item">
                <a class="nav-link <?= $idxPage == 2 ? "active" : "" ?>" href="?page=2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                  Modifier un aspect
                </a>
              </li>
              
            </ul>
          </div>
        </nav>
        
    <div class="container-fluid">
      <div class="row">
        
		<!-- Bootstrap & JQuery -->
    	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    	<script src="js/cookie.js"></script>
    	
        <main role="main" class="<?php if(!$pages[$idxPage]["padding"]) { ?>pt-0 px-0<?php } else { ?>pt-3 px-3<?php }?>">
    		<?php
    		if(isset($pages[$idxPage]['page']) && file_exists("pages/admin/".$pages[$idxPage]['page'])) {
    		    include_once("pages/admin/".$pages[$idxPage]['page']);
    		} else { ?>
    		<div class="alert alert-warning">Cette page n'existe pas encore</div>
    		<?php } ?>
        </main>
        
      </div>
    </div>
	
	
</body>
</html>
<?php
require_once 'required/common.php';
include_once 'required/db_connect.php';
include_once 'required/securite.fct.php';
includeDependencies();

sec_session_start();
$logged = login_check($mysqli);

if(!$logged) { 
    header('Location: admin/logout');
}

if(isset($_SESSION['resultsDefined'])) {
    unset($_SESSION['resultsDefined']);
}

$pages = array(
    1 => array("page" => "quest-collected.php", "padding" => false, "title" => $t['quest-collected']),
    
    "participants" => array("page" => "participants.php", "padding" => false, "title" => $t['participants']),
    "json-editor" => array("page" => "edit-aspect.php", "padding" => true, "title" => $t['edit-aspect']),
    "translate" => array("page" => "translations.php", "padding" => false, "title" => $t['translations'])
);
$pagesKeys = array_keys($pages);

$idxPage = $pagesKeys[0];
if(isset($_GET['page'])) {
    $idxPage = $_GET['page'];
}


function getInfoPage($idx) {
    global $idxPage, $pages;
    if(isset($pages[$idx]['title']))
        return $pages[$idx]['title'];
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php if(!is_array($_SERVER['QUERY_STRING'])) {?><base href="<?= ($_SERVER['SERVER_NAME'] == "localhost") ? "/sharp-site/" : getBase() ?>"><?php } ?>
	
	<!-- Fonts -->
	<!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"> -->
	
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
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
	
	<title>SHARP - <?= getInfoPage($idxPage) ?></title>
	<link rel="shortcut icon" href="img/favicon.png">
</head>
<body>
	
	<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
        <?php 
        $name = $_SESSION['name'];
        if(strlen($name) > 18) $name = substr($name, 0, 17)."..";
        ?>
        
        <div class="navbar-brand col-sm-3 col-md-2 mr-0" style="width:220px; max-width:220px; flex:none">
        	<a href="home" class="btn btn-outline-light btn-sm mr-1" data-toggle="tooltip" data-placement="right" title="<?= $t['accueil']?>"><span class="oi oi-home"></span></a>
       		<?= $name ?> 
        </div>
        
        <!-- <input class="form-control form-control-dark w-100 d-none" type="text" placeholder="Recherche" id="mainSearch"> -->
        
        <div class="w-100 text-left ml-2" id="buttons">
        	
        </div>
        
        <ul class="navbar-nav px-3">
            <li class="nav-item text-nowrap">
              <a class="nav-link" href="required/logout.php"><?= $t['signout'] ?></a>
            </li>
        </ul>
    </nav>
    
    <nav class="col-md-2 d-md-block bg-light sidebar">
          <div class="sidebar-sticky">
            <ul class="nav flex-column">
            
            
              <li class="nav-item">
                <a class="nav-link <?= $idxPage == $pagesKeys[0] ? "active" : "" ?>" href="admin/dashboard/<?= $pagesKeys[0] ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                   <?= getInfoPage($pagesKeys[0]) ?> <span class="sr-only">(current)</span>
                </a>
              </li>
              
           
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
              <span><?= $t['gestion'] ?></span>
            
            </h6>
            <ul class="nav flex-column mb-2">
              <li class="nav-item">
                <a class="nav-link <?= $idxPage == $pagesKeys[1] ? "active" : "" ?>" href="admin/dashboard/<?= $pagesKeys[1] ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                  <?= getInfoPage($pagesKeys[1]) ?>
                </a>
              </li>
              
              <li class="nav-item">
                <a class="nav-link <?= $idxPage == $pagesKeys[2] ? "active" : "" ?>" href="admin/dashboard/<?= $pagesKeys[2] ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                  <?= getInfoPage($pagesKeys[2]) ?>
                </a>
              </li>
              
              <li class="nav-item">
                <a class="nav-link <?= $idxPage == $pagesKeys[3] ? "active" : "" ?>" href="admin/dashboard/<?= $pagesKeys[3] ?>">
                  <svg class="feather" height="24" viewBox="0 0 48 48" width="24" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M0 0h48v48h-48z" fill="none"/><path d="M25.74 30.15l-5.08-5.02.06-.06c3.48-3.88 5.96-8.34 7.42-13.06h5.86v-4.01h-14v-4h-4v4h-14v3.98h22.34c-1.35 3.86-3.46 7.52-6.34 10.72-1.86-2.07-3.4-4.32-4.62-6.7h-4c1.46 3.26 3.46 6.34 5.96 9.12l-10.17 10.05 2.83 2.83 10-10 6.22 6.22 1.52-4.07zm11.26-10.15h-4l-9 24h4l2.25-6h9.5l2.25 6h4l-9-24zm-5.25 14l3.25-8.67 3.25 8.67h-6.5z"/></svg>
                  <?= getInfoPage($pagesKeys[3])?>
                </a>
              </li>
              
            </ul>
          </div>
        </nav>
        
    <div class="container-fluid">
      <div class="row">
        
		<!-- Bootstrap & JQuery -->
    	<script src="js/jquery.min.js"></script>
    	<script src="js/popper.min.js"></script>
    	<script src="js/bootstrap.min.js"></script>
    	<script src="js/cookie.js"></script>
    	
        <main role="main" class="<?php if(!$pages[$idxPage]["padding"]) { ?>pt-0 px-0<?php } else { ?>pt-3 px-3<?php }?>">
        	
        	<?php if(in_array($idxPage, array(1, "participants"))) {?>
        	<div id="loader" class="w-50" style="margin-top: 150px; margin-left:17.5%; position:absolute">
            	<div class="text-center lead mb-1" style="font-size:2rem;"><?= $t['loading'] ?></div>
            	<div class="progress">
                  <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                </div>
            </div>
            <?php } ?>
            
            <div id="content-loaded" <?php if(in_array($idxPage, array(1, "participants"))) {?>style="display:none"<?php } ?>>
        		<?php
        		if(isset($pages[$idxPage]['page']) && file_exists("pages/admin/".$pages[$idxPage]['page'])) {
        		    include_once("pages/admin/".$pages[$idxPage]['page']);
        		} else { ?>
        		<div class="alert alert-warning m-2">Cette page n'existe pas encore</div>
        		<?php } ?>
    		</div>
        </main>
        
        <script>
		$(function(){
			$('body').tooltip({
			    selector: '[data-toggle=tooltip]'
			});
		});
        </script>
        
      </div>
    </div>
	
	
</body>
</html>
<?php
require_once '../../required/common.php';
include_once '../../required/db_connect.php';
include_once '../../required/securite.fct.php';

sec_session_start();
$logged = login_check($mysqli);

if(!$logged) {
    exit();
}

if(isset($_POST['pid'])) {
    $pid = intval($_POST['pid']);
    $querySelectParticipant = 
    "SELECT * FROM participants p
    LEFT JOIN regions r ON r.rid=p.rid
    LEFT JOIN prod_systems ps ON ps.psid=p.cluster
    WHERE pid=".$pid."";                        
    $stmtPart = $mysqli->query($querySelectParticipant);
    $infoPart = $stmtPart->fetch_assoc();
    $stmtPart->close();
    
    echo '<h3 class="text-capitalize font-weight-bold">'.$infoPart['firstname']." ".$infoPart['lastname'].'</h3>';
    
    $lang = getLang();
    $fields = array("email" => $t['email'], "ktidb" => "ktidb", "ofs" => "ofs", "rlabel_".$lang => $t['atelier'], "pslabel_".$lang => $t['cluster']);
    
    
    foreach($fields as $key => $label) {
        echo '<div class="d-inline-block mr-1" style="width:'.(strlen($label.$infoPart[$key]) > 40 ? "99.98%" : "49.4%").'">';
            echo '<div class="d-flex p-1 px-2 bg-light rounded mb-1">';
                echo '<div class="font-weight-bold" style="width:190px">'.$label.'</div>';
                echo '<div>'.(empty($infoPart[$key]) ? "-" : $infoPart[$key]).'</div>';
            echo '</div>';
        echo '</div>';
    }
    
    // -----------------------------------------------------
    echo '<hr>';
    
    echo '<h4 class="lead">'.$t['list-questionnaires'].'</h4>';
    $queryQuest = "SELECT * FROM questionnaires WHERE pid=".$pid;
    
    echo '<table id="repondants" class="table table-striped table-hover display table-sm" data-page-length="11" style="border-collapse:collapse!important">';
    echo '<thead>';
    echo '<tr>';
        echo '<th>'.$t['collected_by'].'</th>';
        echo '<th>'.$t['version'].'</th>';
        echo '<th>'.$t['creation'].'</th>';
        echo '<th>IP</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach($mysqli->query($queryQuest) as $quest) {
        $json = getJSONFromFile(getAbsolutePath().DIR_ANSWERS."/".$quest['file']);
        
        $ip = isset($json['meta']['client-ip']) ? $json['meta']['client-ip'] : "-";
        
        echo '<tr>
            <td>'.$quest['collecte_par'].'</td>
            <td>'.$quest['version'].'</td>
            <td>'.date("d M Y, H:i:s", strtotime($quest['creation_date'])).'</td>
            <td>'.$ip.'</td>
        </tr>';
    }
    echo '</tbody>';
    echo '</table>';
  
}
<?php
class ChartData {
    private $section;
    private $qid;
    private $rid = null;
    private $cluster = null;
    private $scoresRegionByAspect;
    private $scoresClusterByAspect;
    private $valuesStr;
    private $hasNoScore;
    
    public function __construct($data, $section) {
        $this->section = $section;
        $this->qid = $data['qid']; // id du questionnaire
        $this->rid = $data['rid']; // id de la region
        $this->cluster = $data['cluster']; // id du cluster
        
        $this->scoresRegionByAspect = array();
        $this->scoresClusterByAspect = array();
        $this->valuesStr = array();
        
        $this->getData();
        $this->bindData();
    }
    
    private function getData() {
        global $mysqli;
        
        if($this->rid !== null) {
            foreach($mysqli->query($this->getQuery("rid")) as $row) {
                $this->scoresRegionByAspect[$row['aspectId']] = round($row['scoreRegion'],2);
            }
        }
        if($this->cluster !== null) {
            foreach($mysqli->query($this->getQuery("cluster")) as $row) {
                $this->scoresClusterByAspect[$row['aspectId']] = round($row['scoreCluster'],2);
            }
        }
    }
    
    private function bindData() {
        global $mysqli;
        
        $labelField = "label_".getLang();
        
        $this->valuesStr['labels'] = $this->valuesStr['personnal'] = $this->valuesStr['rid'] = $this->valuesStr['cluster'] = "";
        
        $query =
        "SELECT aspectId, ".$labelField.", score, type FROM scores s
        LEFT JOIN label_aspects a ON a.aid=s.aid
        WHERE s.qid=".$this->qid." AND a.aspectId LIKE '".$this->section."_%'
        ORDER BY score ASC, aspectId ASC";
        
        $results = $mysqli->query($query);
        
        $this->hasNoScore = $results->num_rows;
        
        $sum = array("resilience" => 0, "importance" => 0, "academic" => 0);
        $nbDefined = array("resilience" => 0, "importance" => 0, "academic" => 0);
        
        
        /* On doit ajouter les données en deux étapes sinon elles ne sont pas dans l'ordre!
         * donc on ne peut pas sortir les données pour l'rid et le cluster de la condition
         * sinon on perd l'ordre */
        foreach($results as $row) {
            if(trim($row['score']) != "") {
                if($row['type'] == "resilience") {
                    $this->valuesStr['labels'] .= $row[$labelField].";";
                    if(isset($this->scoresRegionByAspect[$row['aspectId']])) {
                        $this->valuesStr['rid'] .= $this->scoresRegionByAspect[$row['aspectId']].";";
                        $this->valuesStr['cluster'] .= $this->scoresClusterByAspect[$row['aspectId']].";";
                    }
                }
            }
            
            if($row['score'] != "") {
                if(in_array($row['type'], array_keys($sum))) {
                    $sum[$row['type']] += round($row["score"], 1);
                    $nbDefined[$row['type']]++;
                }
                
                if($row['type'] == "resilience") {
                    $this->valuesStr['personnal'] .= round($row["score"], 1).";";
                }
            }
        }
        
        /* On ajoute les données pour les aspects qui n'ont aucun scores personnels (données pas complétées),
         * mais qui ont quand meme des données pour l'rid et le cluster */
        $results->data_seek(0);
        foreach($results as $row) {
            if($row['score'] === null) {
                if($row['type'] == "resilience") {
                    $this->valuesStr['labels'] .= $row[$labelField].";";
                    if(isset($this->scoresRegionByAspect[$row['aspectId']])) {
                        $this->valuesStr['rid'] .= $this->scoresRegionByAspect[$row['aspectId']].";";
                        $this->valuesStr['cluster'] .= $this->scoresClusterByAspect[$row['aspectId']].";";
                    }
                }
            }
        }
        
        $this->valuesStr['labels'] = substr($this->valuesStr['labels'], 0, -1);
        $this->valuesStr['personnal'] = substr($this->valuesStr['personnal'], 0, -1);
        
        $this->valuesStr['rid'] = ($this->rid !== null) ? substr($this->valuesStr['rid'], 0, -1) : "";
        $this->valuesStr['cluster'] = ($this->cluster !== null) ? substr($this->valuesStr['cluster'], 0, -1) : "";
        $this->valuesStr['avgPersonnalResilience'] = ($nbDefined['resilience'] != 0) ? ($sum['resilience'] / $nbDefined['resilience']) : 0;
        $this->valuesStr['importance'] = ($nbDefined['importance'] != 0) ? ($sum['importance'] / $nbDefined['importance']) : 0;
        $this->valuesStr['conduiteExploitation'] = ($nbDefined['academic'] != 0) ? ($sum['academic'] / $nbDefined['academic']) : 0;
    }
    
    function getValues() {
        return $this->valuesStr;
    }
    
    function hasScores() {
        return $this->hasNoScore > 0;
    }
    
    private function getQuery($type) {
        if($type == "rid") {
            $fieldScore = "scoreRegion";
            $fieldCustom = "rid";
        } else {
            $fieldScore = "scoreCluster";
            $fieldCustom = "cluster";
        }
        
        return
        "SELECT aspectId, avg(score) as ".$fieldScore." FROM scores s
            LEFT JOIN label_aspects a ON a.aid=s.aid
            WHERE type='resilience' AND a.aspectId LIKE '".$this->section."_%' AND
            s.qid IN (
                SELECT qid FROM questionnaires q LEFT JOIN participants p ON p.pid=q.pid
                WHERE ".$fieldCustom."=(SELECT ".$fieldCustom." FROM participants p INNER JOIN questionnaires q ON q.pid=p.pid WHERE qid=".$this->qid." LIMIT 1)
            )
            GROUP BY s.aid
            ORDER BY s.aid ASC";
    }
}
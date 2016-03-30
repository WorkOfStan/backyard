<?php

namespace GodsDev\Backyard;

// before all Backyard will be in classes, it is necessary to have global 
// $backyardConf for set-up in src/conf/conf.php
if(!isset($backyardConf)){
    $backyardConf = array();
}

class Backyard {
    protected $backyardConf = array();

    public function __construct(array $backyardConfConstruct = array()) {
        //global $backyardConf;
        $this->backyardConf = $backyardConfConstruct;
        $backyardConf = $this->backyardConf;        
        require_once __DIR__ . '/../src/backyard_system.php';
        
        $this->Json = new \GodsDev\Backyard\Json($backyardConf);
        
    }

}

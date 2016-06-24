<?php

namespace GodsDev\Backyard;

// before all Backyard will be in classes, it is necessary to have global 
// $backyardConf for set-up in src/conf/conf.php
if(!isset($backyardConf)){
    $backyardConf = array();
}

class Backyard {
    protected $BackyardConf = array();

    public function __construct(array $backyardConfConstruct = array()) {
        //global $backyardConf;
        $this->BackyardConf = $backyardConfConstruct;
        $backyardConf = $this->BackyardConf;        
        require_once __DIR__ . '/../src/backyard_system.php';
        
        //@todo how to automatically $this->Something assign to new \GodsDev\Backyard\Something($backyardConf)
        $this->Json = new \GodsDev\Backyard\Json($backyardConf);
        
    }

}

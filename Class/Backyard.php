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
        $this->BackyardConf = $backyardConf;//after backyard_system processing
        
        //@todo how to automatically $this->Something assign to new \GodsDev\Backyard\Something($backyardConf)
        //by something like spl_autoload_register("$this->Autoload");
        //so that not all are loaded
        $this->BackyardArray = new \GodsDev\Backyard\BackyardArray($this->BackyardConf);
        $this->BackyardCrypt = new \GodsDev\Backyard\BackyardCrypt($this->BackyardConf);
        $this->BackyardErrorLog = new \GodsDev\Backyard\BackyardErrorLog($this->BackyardConf);
        $this->BackyardGeo = new \GodsDev\Backyard\BackyardGeo($this->BackyardConf);
        $this->BackyardHttp = new \GodsDev\Backyard\BackyardHttp($this->BackyardConf);
        $this->BackyardJson = new \GodsDev\Backyard\BackyardJson($this->BackyardConf);
        $this->BackyardMysql = new \GodsDev\Backyard\BackyardMysql($this->BackyardConf);
        $this->BackyardTime = new \GodsDev\Backyard\BackyardTime($this->BackyardConf);
        
    }
    
//    public function Autoload($class) {
//        my_error_log($class,2);//debug
//    }

}

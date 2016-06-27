<?php

namespace GodsDev\Backyard;

class Backyard {
    
   use \LazyProperty\LazyPropertiesTrait;
    
    protected $BackyardConf = array();
    
    public $BackyardArray, $Crypt, $BackyardError, $Geo, $Http, $Json, $Mysqli, $BackyardTime;//object
    public $PageTimestamp;


    public function __construct(array $backyardConfConstruct = array()) {        //global $backyardConf;
        $this->BackyardTime = new BackyardTime($this->BackyardError);
        $this->PageTimestamp = $this->BackyardTime->getPageTimestamp(); //Initiation of $page_timestamp SHOULD be the first thing a page will do.
        
        $this->BackyardConf = $backyardConfConstruct;
        $this->BackyardError = new BackyardError($this->BackyardConf, $this->BackyardTime);
        
        //@todo - check if really, when LazyProperty is active, NetBeans does not hint the methods
        //$this->BackyardTime = new \GodsDev\Backyard\BackyardTime($this->BackyardError);
        //$this->PageTimestamp = $this->BackyardTime->getmicrotime();

        $this->initLazyProperties(['BackyardArray', 'Crypt', 'Geo', 'Http', 'Json', 'Mysqli']);
        
    }

    private function getBackyardArray(){
        return $this->BackyardArray ?: $this->BackyardArray = new BackyardArray($this->BackyardError);
    }

    private function getCrypt(){
        return $this->Crypt ?: $this->Crypt = new BackyardCrypt($this->BackyardError);
    }

    private function getGeo(){
        return $this->Geo ?: $this->Geo = new BackyardGeo($this->BackyardError, $this->BackyardConf);
    }    
    
    private function getHttp(){
        return $this->Http ?: $this->Http = new BackyardHttp($this->BackyardError);
    }
    
    private function getJson(){
        return $this->Json ?: $this->Json = new BackyardJson($this->BackyardError);
    }

    private function getMysqli($host_port, $user, $pass, $db){
        return $this->Mysqli ?: $this->Mysqli = new BackyardMysqli($host_port, $user, $pass, $db, $this->BackyardError);
    }    
        

        
        
    
    
}

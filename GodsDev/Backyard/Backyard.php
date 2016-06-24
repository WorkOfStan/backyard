<?php

namespace GodsDev\Backyard;


class Backyard {
    
   use \LazyProperty\LazyPropertiesTrait;
    
    protected $BackyardConf = array();
    private $BackyardError = NULL;
    
    public $BackyardArray, $Crypt, $Geo, $Http, $Json, $Mysqli, $BackyardTime;


    public function __construct(array $backyardConfConstruct = array()) {        //global $backyardConf;
        $this->BackyardConf = $backyardConfConstruct;
        $this->BackyardError = new BackyardError;
        $this->initLazyProperties(['Json', 'Http']);
        //$this->getJson();
        //$this->getHttp();
    }

    private function getBackyardArray(){
        return $this->BackyardArray ?: $this->BackyardArray = new \GodsDev\Backyard\BackyardArray($this->BackyardError);
    }

    private function getCrypt(){
        return $this->Crypt ?: $this->Crypt = new \GodsDev\Backyard\BackyardCrypt($this->BackyardError);
    }

    private function getGeo(){
        return $this->Geo ?: $this->Geo = new \GodsDev\Backyard\BackyardGeo($this->BackyardError);
    }    
    
    private function getHttp(){
        return $this->Http ?: $this->Http = new \GodsDev\Backyard\BackyardHttp($this->BackyardError);
    }
    
    private function getJson(){
        return $this->Json ?: $this->Json = new \GodsDev\Backyard\BackyardJson($this->BackyardError);
    }

    private function getMysqli(){
        //@todo//return $this->Mysqli ?: $this->Mysqli = new \GodsDev\Backyard\BackyardMysqli($host_port, $user, $pass, $db, $this->BackyardError);
    }    
        
    private function getBackyardTime(){
        return $this->BackyardTime ?: $this->BackyardTime = new \GodsDev\Backyard\BackyardTime($this->BackyardError);
    }    
        

        
/**
 * 
 * @param string $errorNumber
 * @param string $errorString
 * @param string $feedbackButtonMarkup
 * @return void (die)
 */
public function dieGraciously($errorNumber, $errorString, $feedbackButtonMarkup = false) {
    global $backyardConf;
    $this->myErrorLog("Die with error {$errorNumber} - {$errorString}", 1);
    if ($feedbackButtonMarkup) {
        echo("<html><body>" . str_replace(urlencode("%CUSTOM_VALUE%"), urlencode("Error {$errorNumber} - "
                        . (($backyardConf['die_graciously_verbose']) ? " - {$errorString}" : "")
                ), $feedbackButtonMarkup)); //<html><body> na začátku pomůže, pokud ještě výstup nezačal
    }
    die("Error {$errorNumber}" . (($backyardConf['die_graciously_verbose']) ? " - {$errorString}" : ""));
}
        
        
    
    
}

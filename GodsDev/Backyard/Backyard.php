<?php

namespace GodsDev\Backyard;

class Backyard {


    protected $BackyardConf = array();
    public $BackyardArray; //object
    public $Crypt; //object
    public $BackyardError; //object
    public $Geo; //object
    protected $varHttp; //object
    public $Json; //object
    public $BackyardTime; //object
    public $PageTimestamp; //object

    /**
     * 
     * @param array $backyardConfConstruct
     */
    public function __construct(array $backyardConfConstruct = array()) {
        $this->BackyardTime = new BackyardTime();
        $this->PageTimestamp = $this->BackyardTime->getPageTimestamp(); //Initiation of $page_timestamp SHOULD be the first thing a page will do.

        $this->BackyardConf = $backyardConfConstruct;
        $this->BackyardError = new BackyardError($this->BackyardConf, $this->BackyardTime);

        /*
         * Works for functions. Btw: getter cannot be overwritten by mistake
         * 
         * @return \GodsDev\Backyard\BackyardArray BackyardArray object
         */
                $this->BackyardArray = new BackyardArray($this->BackyardError);
                $this->Crypt = new BackyardCrypt($this->BackyardError);
                $this->Geo = new BackyardGeo($this->BackyardError, $this->BackyardConf);
                $this->varHttp = $this->Http();//new BackyardHttp($this->BackyardError);
                $this->Json = new BackyardJson($this->BackyardError, $this->Http);
    }

    /**
     * 
     * @param string $host_port
     * @param string $user
     * @param string $pass
     * @param string $db
     * @return \GodsDev\Backyard\BackyardMysqli
     */
    public function newMysqli($host_port, $user, $pass, $db) {
        return new BackyardMysqli($host_port, $user, $pass, $db, $this->BackyardError);
    }
    
    /**
     * 
     * @return \GodsDev\Backyard\BackyardHttp BackyardHttp object
     */
    public function Http() {
        return $this->varHttp ? : 
                $this->varHttp = 
                new BackyardHttp($this->BackyardError);
    }


}

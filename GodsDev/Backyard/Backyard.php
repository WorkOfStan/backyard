<?php

namespace GodsDev\Backyard;

class Backyard {


    protected $BackyardConf = array();
    public $BackyardArray, $Crypt, $BackyardError, $Geo, $Http, $Json, $BackyardTime; //object
    public $PageTimestamp;

    /**
     * 
     * @param array $backyardConfConstruct
     */
    public function __construct(array $backyardConfConstruct = array()) {
        $this->BackyardTime = new BackyardTime();
        $this->PageTimestamp = $this->BackyardTime->getPageTimestamp(); //Initiation of $page_timestamp SHOULD be the first thing a page will do.

        $this->BackyardConf = $backyardConfConstruct;
        $this->BackyardError = new BackyardError($this->BackyardConf, $this->BackyardTime);

                $this->BackyardArray = new BackyardArray($this->BackyardError);
                $this->Crypt = new BackyardCrypt($this->BackyardError);
                $this->Geo = new BackyardGeo($this->BackyardError, $this->BackyardConf);
                $this->Http = new BackyardHttp($this->BackyardError);
                $this->Json = new BackyardJson($this->BackyardError);
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

}

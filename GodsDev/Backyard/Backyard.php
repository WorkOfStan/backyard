<?php

namespace GodsDev\Backyard;

class Backyard {

    /**
     *
     * @var array
     */
    protected $BackyardConf = array();

    /**
     *
     * @var \GodsDev\Backyard\BackyardArray BackyardArray object
     */
    public $BackyardArray;

    /**
     *
     * @var \GodsDev\Backyard\BackyardCrypt BackyardCrypt object
     */
    public $Crypt;

    /**
     *
     * @var \GodsDev\Backyard\BackyardError BackyardError object
     */
    public $BackyardError;

    /**
     *
     * @var \GodsDev\Backyard\BackyardGeo BackyardGeo object
     */
    public $Geo;

    /**
     *
     * @var \GodsDev\Backyard\BackyardHttp BackyardHttp object
     */
    public $Http;

    /**
     *
     * @var \GodsDev\Backyard\BackyardJson BackyardJson object
     */
    public $Json;

    /**
     *
     * @var \GodsDev\Backyard\BackyardTime BackyardTime object
     */
    public $BackyardTime;

    /**
     *
     * @var float
     */
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

}

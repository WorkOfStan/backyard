<?php

namespace WorkOfStan\Backyard;

class Backyard
{

    /**
     *
     * @var array<mixed> int,string,bool,array
     */
    protected $BackyardConf = array();

    /**
     *
     * @var \WorkOfStan\Backyard\BackyardArray BackyardArray object
     */
    public $BackyardArray;

    /**
     *
     * @var \WorkOfStan\Backyard\BackyardCrypt BackyardCrypt object
     */
    public $Crypt;

    /**
     *
     * @var \WorkOfStan\Backyard\BackyardError BackyardError object
     */
    public $BackyardError;

    /**
     *
     * @var \WorkOfStan\Backyard\BackyardGeo BackyardGeo object
     */
    public $Geo;

    /**
     *
     * @var \WorkOfStan\Backyard\BackyardHttp BackyardHttp object
     */
    public $Http;

    /**
     *
     * @var \WorkOfStan\Backyard\BackyardJson BackyardJson object
     */
    public $Json;

    /**
     *
     * @var \WorkOfStan\Backyard\BackyardTime BackyardTime object
     */
    public $BackyardTime;

    /**
     *
     * @var float
     */
    public $PageTimestamp;

    /**
     *
     * @param array<mixed> $backyardConfConstruct contains int,string,bool,array
     */
    public function __construct(array $backyardConfConstruct = array())
    {
        $this->BackyardTime = new BackyardTime();
        //Initiation of $page_timestamp SHOULD be the first thing a page will do.
        $this->PageTimestamp = $this->BackyardTime->getPageTimestamp();
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
     * @return \WorkOfStan\Backyard\BackyardMysqli
     */
    public function newMysqli($host_port, $user, $pass, $db)
    {
        return new BackyardMysqli($host_port, $user, $pass, $db, $this->BackyardError);
    }
}

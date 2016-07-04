<?php

namespace GodsDev\Backyard;

class Backyard {

    use \LazyProperty\LazyPropertiesTrait;

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

        //@todo - when LazyProperty is active, NetBeans does not hint the methods - wouldn't using interface solved it?
        $this->initLazyProperties(array('BackyardArray', 'Crypt', 'Geo', 'Http', 'Json'));
    }

    private function getBackyardArray() {
        return $this->BackyardArray ? : $this->BackyardArray = new BackyardArray($this->BackyardError);
    }

    private function getCrypt() {
        return $this->Crypt ? : $this->Crypt = new BackyardCrypt($this->BackyardError);
    }

    private function getGeo() {
        return $this->Geo ? : $this->Geo = new BackyardGeo($this->BackyardError, $this->BackyardConf);
    }

    private function getHttp() {
        return $this->Http ? : $this->Http = new BackyardHttp($this->BackyardError);
    }

    private function getJson() {
        return $this->Json ? : $this->Json = new BackyardJson($this->BackyardError);
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

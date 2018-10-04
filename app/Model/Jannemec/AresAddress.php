<?php
namespace jannemec;
/**
 * Adresa
 *
 * @author nemec
 */
class AresAddress {
    use \Nette\SmartObject;
    /**
     * identifikace adresy
     * @var int
     */
    private $id;

    /**
     * Kód státu
     * @var String
     */
    private $ks;

    /**
     * Název obce
     * @var String
     */
    private $n;

    /**
     * Název ulice
     * @var String
     */
    private $nu;

    /**
     * Číslo do adresy
     * @var int
     */
    private $ca;

    /**
     * PSČ
     * @var String
     */
    private $psc;

    /**
     * Address id getter
     * @return int
     */
    public function getId() {
        return($this->id);
    }

    /**
     * Address id setter
     * @param int $id
     */
    public static function setId($id) {
        $this->id = $id;
    }

    /**
     * Kód státu getter
     * @return String
     */
    public function getKs() {
        return($this->ks);
    }

    /**
     * Kód státu setter
     * @param String $ks
     */
    public static function setKs($ks) {
        $this->ks = $ks;
    }

    /**
     * Název obce getter
     * @return String
     */
    public function getN() {
        return($this->n);
    }

    /**
     * Název obce setter
     * @param String $n
     */
    public static function setN($n) {
        $this->n = $n;
    }

    /**
     * Název ulice getter
     * @return String
     */
    public function getNu() {
        return($this->nu);
    }

    /**
     * Název ulice setter
     * @param String $nu
     */
    public static function setNu($nu) {
        $this->nu = $nu;
    }

    /**
     * PSČ getter
     * @return String
     */
    public function getPsc() {
        return($this->psc);
    }

    /**
     * PSČ setter
     * @param String $psc
     */
    public static function setPsc($psc) {
        $this->psc = $psc;
    }

    /**
     * Číslo do adresy getter
     * @return String
     */
    public function getCa() {
        return($this->ca);
    }

    /**
     * PSČ setter
     * @param String $n
     */
    public static function setCa($ca) {
        $this->ca = $ca;
    }
}
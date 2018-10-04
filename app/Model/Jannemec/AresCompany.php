<?php
namespace jannemec;
/**
 * Description of AresCompany
 *
 * @author nemec
 */
class AresCompany {
    use \Nette\SmartObject;
    /**
     *
     * @var String
     */
    protected $ico = null;

    /**
     *
     * @var String
     */
    protected $name = null;
    
    /**
     *
     * @var String
     */
    protected $area = null;
    
    /**
     *
     * @var String
     */
    protected $city = null;
    
    /**
     *
     * @var String
     */
    protected $subcity = null;

    /**
     *
     * @var String
     */
    protected $cityPart = null;
    
    /**
     *
     * @var String
     */
    protected $mestskaCast = null;

    /**
     *
     * @var String
     */
    protected $psc = null;
    
    /**
     *
     * @var String
     */
    protected $street = null;
    
    /**
     *
     * @var String
     */
    protected $street_nr = null;
    
    /**
     *
     * @var String
     */
    protected $home_nr = null;

    public function __construct($ico
            , $name = null) {
        $this->setIco($ico);
        if (!is_null($name)) {$this->setName($name); };
        
    }

    public function getIco() {
        return($this->ico);
    }

    public function getName() {
        return($this->name);
    }

    public function getArea() {
        return($this->area);
    }

    public function getCity() {
        return($this->city);
    }

    public function getSubcity() {
        return($this->subcity);
    }

    public function getCityPart() {
        return($this->cityPart);
    }
    
    public function getMestskaCast() {
        return($this->mestskaCast);
    }

    public function getPsc() {
        return($this->psc);
    }

    public function getStreet() {
        return($this->street);
    }

    public function getStreet_nr() {
        return($this->street_nr);
    }

    public function getHome_nr() {
        return($this->home_nr);
    }
    
    public function setIco($ico) {
        $this->ico = $ico;
    }
    
     public function setName($name) {
        $this->name = $name;
    }
    
    public function setArea($area) {
        $this->area = $area;
    }
    
    public function setCity($city) {
        $this->city = $city;
    }

    public function setSubcity($subcity) {
        $this->subcity = $subcity;
    }

    public function setCityPart($cityPart) {
        $this->cityPart = $cityPart;
    }
    
    public function setMestskaCast($mestskaCast) {
        $this->mestskaCast = $mestskaCast;
    }

    public function setPsc($psc) {
        $this->psc = $psc;
    }

    public function setStreet($street) {
        $this->street = $street;
    }

    public function setStreet_nr($street_nr) {
        $this->street_nr = $street_nr;
    }

    public function setHome_nr($home_nr) {
        $this->home_nr = $home_nr;
    }



    public function getAddress() {
        return(array('street' => $this->getStreet()
                , 'street_nr' => $this->getStreet_nr()
                , 'ico' => $this->getIco()
                , 'city' => $this->getCity()
                , 'area' => $this->gtArea()
                ));
    }

}
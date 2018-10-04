<?php
namespace Model;

/**
 * Description of Infos
 *
 * @author Nemec
 */
class Infos {
   use \Nette\SmartObject;
    /**
     * Doplněná cache
     * @var \Nette\Caching\Cache 
     */
    private $cache = null;
    public function getCache() {
        return($this->cache);
    }
    
    private static $cacheExpire = 10 * 60;
    
    /** 
     * 
     * @var \Dibi\Connection 
     */
    protected $dbf;
    public function getDbf(): \Dibi\Connection {
        return $this->dbf;
    }

    public function setDbf(\Dibi\Connection $dbf) {
        $this->dbf = $dbf;
    }

        /**
     *
     * @param \Nette\Database\Context  $dbf
     * @param \Nette\Caching\Cache  $apiParams
     */
    public function __construct(\Dibi\Connection $dbf, \Nette\Caching\Cache $cache){
        $this->dbf = $dbf;
        $this->cache = $cache;
    }
    
    public function getCacheKey(string $key) {
        if (true && ($this->cache instanceOf \Nette\Caching\Cache) && ($out = $this->cache->load($key))) {
            $key;
        } else {
            return(null);
        }
    }
    public function setCacheKey(string $key, $val) {
        if ($this->cache instanceOf \Nette\Caching\Cache) {
            $this->cache->save($key, $val, array(
                \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                \Nette\Caching\Cache::SLIDING => FALSE));
            return(true);
        } else {
            return(false);
        }
    }
    
    //==========================================================================
    /**
     * Return list of projects
     * @param bool $onlyActive
     * @return array
     */
    public function getProjects(bool $onlyActive = true) : array {
        $out = $this->getDbf()->select('phproj, phpursale, phcontract, phacsta, phacfin, userid, phdesig1, phdesig2, phdesig3, phac, phtk')
                ->from('pcprojh with (nolock)')
                ->where('1=1')
                ;
        if ($onlyActive) {
            $out = $out->and('phtk=%s', 'Y');
        }
        return($out->orderBy('phproj')->fetchAll());
    }
    
    public function getUsers(bool $onlyActive = true) : array {
        $out = $this->getDbf()->select('*')
                ->from('syusers with (nolock)')
                ->where('subj=%s', '03')
                    ;
        if ($onlyActive) {
            $out = $out->and('plat=%s', 'A');
        }
        return($out->orderBy('userid')->fetchAll());
    }
    
    public function getUser(string $username) : \Dibi\Row {
        $out = null;
        if ($out = $this->getCacheKey('infos_user_' . $username)) {
            
        } else {
            $out = $this->getDbf()->select('userfull as fullname, *')
                ->from('syusers with (nolock)')
                ->where('userid=%s', $username)
                    ->orderBy('userid')
                ;
            $out = $out->fetch();
            $this->setCacheKey('infos_user_' . $username, $out);
        }
        return($out);
    }
    
    protected function getCompaniesSRC() {
        $out = $this->getDbf()->select('zeme, ico, przkrat, nazev, ulice, obec, psc, platdph, platdan, zeme_reg, mena, chgdat, adresa1, adresa4')
                ->from('kmpartneri with (nolock)')
                ->where('1=1')
                    ->and('plat=%s', 'A')
                ;
        return($out);
    }
    
    public function getCompanies(bool $asArray = false)  {
        $out = $this->getCompaniesSRC();
        return($asArray ? $out->orderBy('ico')->fetchAll() : $out->orderBy('ico')->getIterator());
    }
    
    
    public function getCompaniesForVAT(bool $asArray = false)  {
        $out = $this->getCompaniesSRC();
        $out = $out->and('platdph=%s','A');
        return($asArray ? $out->orderBy('chgdat DESC')->fetchAll() : $out->orderBy('ico')->getIterator());
    }
}

<?php
namespace Model;

/**
 * Description of OKBase
 *
 * @author Nemec
 */
class OKBase {
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
     * Return all active persons from the system
     * @return \Iterator
     */
    public function getEmployees(): \Iterator {
        return($this->getDbf()->select('*')
                ->from('[CAPOKSYS01\SQLEXPRESS].[okbase2].[okbase].[EXT_V_Osoba]')
                ->orderBy('prijmeni, jmeno')
                ->getIterator());
    }
}

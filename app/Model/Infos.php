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
    
    protected static $companiesToImport = ['62029100', '45272956', '27226697', '63078180', '48135267', '63998530', '25669214', '15936601', '26199416'];
    protected static $companiesToImportGeneral = ['27684547', '63998530'];
    
    protected static function getCompaniesToImport() : array {
        return(self::$companiesToImport);
    }
    protected static function getCompaniesToImportGeneral() : array {
        return(self::$companiesToImportGeneral);
    }
    //==========================================================================
    /**
     * Return list of projects
     * @param bool $onlyActive
     * @return array
     */
    public function getProjects(bool $onlyActive = true, bool $byDate = false) : array {
        $out = $this->getDbf()->select('phproj, phpursale, phpursaleup, phcontract, phacsta, phacfin, userid, phdesig1, phdesig2, phdesig3, phac, phtk, chgtim, tproj, stcode, dpdruh2, phkukon')
                ->from('pcprojh with (nolock)')
                ->where('1=1')
                    ->and('subj=%s', '03')
                    //->and('phpursale=phproj')
                ;
        if ($onlyActive) {
            $out = $out->and('phtk=%s', 'Y')
                        ->and('phkukon is null');
        }
        if ($byDate) {
            return($out->orderBy('chgtim desc')->fetchAll());
        } else {
            return($out->orderBy('phproj')->fetchAll());
        }
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
    
    /**
     * 
     * @param string $tproj
     * @return string
     */
    public function getProjectType(string $tproj) : string {
        $out = null;
        if ($out = $this->getCacheKey('infos_projecttype_' . $tproj)) {
            
        } else {
            $out = $this->getDbf()->select('tptext2')
                    ->from('kmprojtyp with (nolock)')
                    ->where('subj=%s', '03')
                    ->and('tproj=%s', $tproj)
                    ->orderBy('tproj')
                    ->fetchSingle();
            if (!$out) {
                $out = '';
            }
            $this->setCacheKey('infos_projecttype_' . $tproj, $out);
        }
        return($out);
    }
    
    /**
     * 
     * @param string $stcode
     * @return string
     */
    public function getMarketSegment(string $stcode) : string {
        $out = null;
        if ($out = $this->getCacheKey('infos_marketsegment_' . $stcode)) {
            
        } else {
            $out = $this->getDbf()->select('sttext2')
                    ->from('pcsegtrh with (nolock)')
                    ->where('subj=%s', '03')
                    ->and('stcode=%s', $stcode)
                    ->orderBy('stcode')
                    ->fetchSingle();
            if (!$out) {
                $out = '';
            }
            $this->setCacheKey('infos_marketsegment_' . $stcode, $out);
        }
        return($out);
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
        $out = $this->getDbf()->select('A.zeme, A.ico, A.przkrat, A.nazev, A.ulice, A.obec, A.psc, A.platdph, A.platdan, A.zeme_reg, A.mena, A.chgdat, A.adresa1, A.adresa4')
                ->from('kmpartneri A with (nolock)')
                    ->leftJoin('dbo.kmparsubj B with (nolock)')->on('B.zeme = A.zeme')->and('B.ico = A.ico')
                ->where('1=1')
                    ->and('A.plat=%s', 'A')
                    ->and('((B.subj=%s and (A.chgdat>=%d or A.ico in %l)) or (B.subj is null and (A.chgdat>=%d or A.ico in %l)))', '03'
                            , new \DateTime('2010-01-01'), self::getCompaniesToImport()
                            , new \DateTime('2013-01-01'), self::getCompaniesToImportGeneral())
                    //->and('A.chgdat>=%d', new \DateTime('2010-01-01'));
                ;
                //$out->test(); exit;
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

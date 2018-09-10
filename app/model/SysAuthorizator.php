<?php
namespace Model;
use \Nette\Security\IAuthorizator;
/**
 * Description of SysAuthorization
 *
 * @author nemec
 */
class SysAuthorizator extends \Nette\Security\Permission implements \Nette\Security\IAuthorizator  {
    /** @var \Nette\Database\Context */
    private $dbf = null;
    private $registeredTime = null;
    
    /**
     * 
     * @param int $time
     */
    public function setRegisteredTime($time = null) {
        $this->registeredTime = is_null($time) ? time() : intval($time);
    }
    
    /**
     * 
     * @return int
     */
    public function getRegisteredTime() {
        return($this->registeredTime);
    }

    /**
     * 
     * @param \Nette\Database\Connection  $dbf
     * @throws InvalidArgumentException
     */
    function  __construct(\Nette\Database\Context $dbf) {
        $this->dbf = $dbf;
        $this->addRoles(); // add roles
        $this->addResources(); // add all resources

        // Nastavení oprávnění jednotlivých skupin/rolí
        $linkList = $this->dbf->table('sys_group_right');
        foreach($linkList as $linkItem) {
            if ($linkItem->value != '' && ($linkItem->value != '1')) {
                $this->allow($linkItem->sys_group->name, $linkItem->sys_right->name, $linkItem->value);
            } else {
                $this->allow($linkItem->sys_group->name, $linkItem->sys_right->name);
            }
        }
        $this->setRegisteredTime();
    }

    /**
     * Add all Resources
     */
    function addResources() {
        $rightList = $this->dbf->table('sys_right');
        foreach($rightList as $right) {
            if ($right->name != '') {
                $this->addResource($right->name);
            }
        }
    }

    /**
     * Add all roles
     */
    function addRoles() {
        $groupList = $this->dbf->table('sys_group');
        foreach($groupList as $group) {
            if ($group->name != '') {
                $this->addRole($group->name);
            }
        }
    }
    
    private static $instance = null;
    
    public static function getInstanceSingleton(\Nette\Database\Context $dbf, \Nette\Caching\Cache $cache, $authorizatorCacheTimeout = 3600) {
        if ((!is_null(self::$instance)) && (self::$instance  instanceOf \Model\SysAuthorizator)) {
            
        } elseif (($cache instanceOf \Nette\Caching\Cache) && ($acache = $cache->load('authorizator')) && ($acache instanceof SysAuthorizator) && ($acache->getRegisteredTime() + $authorizatorCacheTimeout >= time())) {
            self::$instance = $acache;
            self::$instance->dbf = $dbf;
        } else {
            self::$instance = new SysAuthorizator($dbf);
            self::$instance->dbf = null;
            if ($cache instanceOf \Nette\Caching\Cache) {
                $cache->save('authorizator', self::$instance, array(
                    \Nette\Caching\Cache::EXPIRE => time() + $authorizatorCacheTimeout,
                    \Nette\Caching\Cache::SLIDING => FALSE));
            }
            self::$instance->dbf = $dbf;
        }
        return(self::$instance);
    }
    
    public static function clearCacheSingleton(\Nette\Caching\Cache $cache) {
        $cache->remove('authorizator');
    }
}
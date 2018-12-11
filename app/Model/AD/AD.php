<?php
namespace Adldap;

/**
 * Description of AD
 *
 * @author nemec
 */
class AD extends Adldap {
    /**
     * Doplněná cache
     * @var \Nette\Caching\Cache 
     */
    private $cache = null;
    private static $cacheExpire = 3600;
    
    function __construct(array $options = [], \Nette\Caching\Cache $cache) {
        //var_dump($options); exit;
        parent::__construct($options);
        $this->cache = $cache;
    }
    
    /**
     * Vrací seznam všech skupin
     * @param boolean $all  Pokud je nastaveno na true, hkledá v celém ad, jinak jen větev silon
     * @return array
     */
    public function getGroups($all = false) {
        if (true && ($this->cache instanceOf \Nette\Caching\Cache) && ($this->cache->load('ad.groups'))) {
            // existuje v cache
            $output = $this->cache->load('ad.groups');
        } else {
            if (!$this->getLdapBind()) {
                $this->connect();
            }
            $tmp = $this->group()->all();
            $output = array();
            if (is_array($tmp)) {
                foreach($tmp as $item) {
                    $output[$item['cn']] = isset($item['distinguishedname']) ? $item['distinguishedname'] : $item['name'];//$item;
                }
            }
            ksort($output);
            if ($this->cache instanceOf \Nette\Caching\Cache) {
                $this->cache->save('ad.groups', $output, array(
                    \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                    \Nette\Caching\Cache::SLIDING => FALSE));
            }
        }
        if (!$all && is_array($output)) {
            foreach($output as $key => $val) {
                if ((substr($val, -28) != 'OU=CHPN groups,DC=chpn,DC=cz') && (substr($val, -19) != 'OU=OTK,DC=otk,DC=cz')) {
                    unset($output[$key]);
                }
            }
        }
        return($output);
    }
    
    /**
     * Vrací seznam všech uživatelů, včetně skupin, jejichž je členem
     * @param boolean $all  Pokud je nastaveno na true, hkledá v celém ad, jinak jen větev silon
     * @return array
     */
    public function getUsers($all = false, $force = false) {
        if (true && (!$force && ($this->cache instanceOf \Nette\Caching\Cache) && ($this->cache->load('ad.users')))) {
            // existuje v cache
            $output = $this->cache->load('ad.users');
        } else {
            $output = $this->readUsers(true);
            if ($this->cache instanceOf \Nette\Caching\Cache) {
                $this->cache->save('ad.users', $output, array(
                    \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                    \Nette\Caching\Cache::SLIDING => FALSE));
            }
        }
        if (!$all && is_array($output)) {
            foreach($output as $key => $val) {
                if ((substr($val['dn'], -27) != 'OU=CHPN users,DC=chpn,DC=cz') && (substr($val['dn'], -30) != 'OU=CHPN external,DC=chpn,DC=cz')) {
                    unset($output[$key]);
                }
            }
        }
        return($output);
    }
      
    /**
     * Get the username and full name of user by phone number
     * Phone number without prefix +420and without spaces
     * @param string $phone
     */
    public function getUserByPhone(string $phone): array {
        $phone = self::normalizePhone($phone);
        $out = [];
        foreach($this->getUsers() as $key => $user) {
            $tel = isset($user['mobile']) ? self::normalizePhone($user['mobile']) : '';
            if ($tel == $phone) {
                $out[$key] = $user;
            } else {
                $tel = isset($user['telephoneNumber']) ? self::normalizePhone($user['telephoneNumber']) : '';
                if ($tel == $phone) {
                    $out[$key] = $user;
                } else {
                    $tel = isset($user['homePhone']) ? self::normalizePhone($user['homePhone']) : '';
                    if ($tel == $phone) {
                        $out[$key] = $user;
                    }
                }
            }
        }
        return($out);
    }
    
    private static function normalizePhone(string $phone) : string {
        return(strtr($phone, [' ' => '', '+420' => '']));
    }
    /**
     * Načte z AD uživatele
     * @param boolean $all  Pokud je nastaveno na true, hledá v celém ad, jinak jen větev silon
     * @return array
     */
    public function getUser($username) {
        if (true && ($this->cache instanceOf \Nette\Caching\Cache) && ($this->cache->load('ad.users'))) {
            // existuje v cache
            $output = $this->cache->load('ad.users');
        } else {
            $output = $this->readUsers(true);
            if ($this->cache instanceOf \Nette\Caching\Cache) {
                $this->cache->save('ad.users', $output, array(
                    \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                    \Nette\Caching\Cache::SLIDING => FALSE));
            }
        }       
        return(isset($output[$username]) ? $output[$username] : null);
    }
    
    
    private function readUsers($all = false) {
        if (!$this->getLdapBind()) {
            $this->connect();
        }
        $output = array();
        
        // Nejprve kontakty
        if ($all) {
            $tmp = $this->contact()->all();
            foreach($tmp as $user) {
                /*if (isset($user['sn']) && ($user['sn'] == 'Nemec Jan')) {
                    \Tracy\Debugger::dump($user); exit;
                } elseif (isset($user['sn'])) {
                    echo $user['sn'] . '<br />';
                }*/
                $output[$user['distinguishedname']]['displayname'] = isset($user['displayname']) ? $user['displayname'] : $user['cn'];
                $output[$user['distinguishedname']]['samaccountname'] = $user['distinguishedname'];
                $output[$user['distinguishedname']]['memberof'] = [];
                $output[$user['distinguishedname']]['dn'] = $user['dn'];
                $output[$user['distinguishedname']]['sn'] = isset($user['sn']) ? $user['sn'] : '';
                $output[$user['distinguishedname']]['wWWHomePage'] = isset($user['wwwhomepage']) ? $user['wwwhomepage'] : '';
                $output[$user['distinguishedname']]['streetAddress'] = isset($user['streetaddress']) ? $user['streetaddress'] : '';
                $output[$user['distinguishedname']]['co'] = isset($user['co']) ? $user['co'] : '';
                $output[$user['distinguishedname']]['postalCode'] = isset($user['postalcode']) ? $user['postalcode'] : '';
                $output[$user['distinguishedname']]['l'] = isset($user['l']) ? $user['l'] : '';
                $output[$user['distinguishedname']]['disabled'] = isset($user['useraccountcontrol']) ? (((intval($user['useraccountcontrol']) & 2) > 0) ? true : false) : false;
                $output[$user['distinguishedname']]['givenName'] = isset($user['givenname']) ? $user['givenname'] : '';
                $output[$user['distinguishedname']]['mobile'] = isset($user['mobile']) ? $user['mobile'] : '';
                $output[$user['distinguishedname']]['mail'] = isset($user['mail']) ? $user['mail'] : '';
                $output[$user['distinguishedname']]['physicalDeliveryOfficeName'] = isset($user['physicaldeliveryofficename']) ? $user['physicaldeliveryofficename'] : '';
                $output[$user['distinguishedname']]['title'] = isset($user['title']) ? $user['title'] : '';
                $output[$user['distinguishedname']]['department'] = isset($user['department']) ? $user['department'] : '';
                $output[$user['distinguishedname']]['company'] = isset($user['company']) ? $user['company'] : '';
                $output[$user['distinguishedname']]['telephoneNumber'] = isset($user['telephonenumber']) ? $user['telephonenumber'] : '';
                $output[$user['distinguishedname']]['homePhone'] = isset($user['homephone']) ? $user['homephone'] : '';
                $output[$user['distinguishedname']]['pager'] = isset($user['pager']) ? $user['pager'] : '';
                $output[$user['distinguishedname']]['facsimileTelephoneNumber'] = isset($user['facsimiletelephonenumber']) ? $user['facsimiletelephonenumber'] : '';
                $output[$user['distinguishedname']]['jpegPhoto'] = isset($user['jpegPhoto']) ? $user['jpegPhoto'] : '';
                $output[$user['distinguishedname']]['thumbnailPhoto'] = isset($user['thumbnailphoto']) ? $user['thumbnailphoto'] : '';
                $output[$user['distinguishedname']]['manager'] = isset($user['manager']) ? $user['manager'] : '';
                $output[$user['distinguishedname']]['sub'] = [];
                $output[$user['distinguishedname']]['lastlogon'] = null;
                $output[$user['distinguishedname']]['scriptPath'] = null;
                $output[$user['distinguishedname']]['type'] = 'contact';
            }
        }
        
        //A následně uživatele
        $tmp = $this->user()->all();
        
        foreach($tmp as $user) {
            if ($all || (isset($user['dn']) && strtoupper(substr($user['dn'], -19)) == 'OU=CHPN users,DC=chpn,DC=cz')) {
                /*if (isset($user['sn']) && ($user['sn'] == 'Nemec')) {
                    \Tracy\Debugger::dump($user); exit;
                } elseif (isset($user['sn'])) {
                    echo $user['sn'] . '<br />';
                }*/
                $output[$user['samaccountname']]['displayname'] = isset($user['displayname']) ? $user['displayname'] : $user['cn'];
                $output[$user['samaccountname']]['samaccountname'] = $user['samaccountname'];
                $output[$user['samaccountname']]['memberof'] = [];
                $output[$user['samaccountname']]['dn'] = $user['dn'];
                $output[$user['samaccountname']]['sn'] = isset($user['sn']) ? $user['sn'] : '';
                $output[$user['samaccountname']]['wWWHomePage'] = isset($user['wwwhomepage']) ? $user['wwwhomepage'] : '';
                $output[$user['samaccountname']]['streetAddress'] = isset($user['streetaddress']) ? $user['streetaddress'] : '';
                $output[$user['samaccountname']]['co'] = isset($user['co']) ? $user['co'] : '';
                $output[$user['samaccountname']]['postalCode'] = isset($user['postalcode']) ? $user['postalcode'] : '';
                $output[$user['samaccountname']]['l'] = isset($user['l']) ? $user['l'] : '';
                $output[$user['samaccountname']]['disabled'] = isset($user['useraccountcontrol']) ? (((intval($user['useraccountcontrol']) & 2) > 0) ? true : false) : false;
                $output[$user['samaccountname']]['givenName'] = isset($user['givenname']) ? $user['givenname'] : '';
                $output[$user['samaccountname']]['mobile'] = isset($user['mobile']) ? $user['mobile'] : '';
                $output[$user['samaccountname']]['mail'] = isset($user['mail']) ? $user['mail'] : '';
                $output[$user['samaccountname']]['physicalDeliveryOfficeName'] = isset($user['physicaldeliveryofficename']) ? $user['physicaldeliveryofficename'] : '';
                $output[$user['samaccountname']]['title'] = isset($user['title']) ? $user['title'] : '';
                $output[$user['samaccountname']]['department'] = isset($user['department']) ? $user['department'] : '';
                $output[$user['samaccountname']]['company'] = isset($user['company']) ? $user['company'] : '';
                $output[$user['samaccountname']]['telephoneNumber'] = isset($user['telephonenumber']) ? $user['telephonenumber'] : '';
                $output[$user['samaccountname']]['homePhone'] = isset($user['homephone']) ? $user['homephone'] : '';
                $output[$user['samaccountname']]['pager'] = isset($user['pager']) ? $user['pager'] : '';
                $output[$user['samaccountname']]['facsimileTelephoneNumber'] = isset($user['facsimiletelephonenumber']) ? $user['facsimiletelephonenumber'] : '';
                $output[$user['samaccountname']]['jpegPhoto'] = isset($user['jpegPhoto']) ? $user['jpegPhoto'] : '';
                $output[$user['samaccountname']]['thumbnailPhoto'] = isset($user['thumbnailphoto']) ? $user['thumbnailphoto'] : '';
                $output[$user['samaccountname']]['manager'] = isset($user['manager']) ? $user['manager'] : '';
                $output[$user['samaccountname']]['sub'] = [];
                $output[$user['samaccountname']]['lastlogon'] = (isset($user['lastlogon']) && !empty($user['lastlogon'])) ? $user['lastlogon'] : null;
                $output[$user['samaccountname']]['scriptPath'] = (isset($user['scriptpath']) && !empty($user['scriptpath'])) ? $user['scriptpath'] : null;
                $output[$user['samaccountname']]['proxyaddresses'] = (isset($user['proxyaddresses']) && !empty($user['proxyaddresses'])) ? $user['proxyaddresses'] : null;
                $output[$user['samaccountname']]['type'] = 'user';
                
                // přepočet data
                if (!is_null($output[$user['samaccountname']]['lastlogon'])) {
                    $tmp = new \DateTime('1601-01-01');
                    $tmp->add(new \DateInterval('PT' . round($output[$user['samaccountname']]['lastlogon'] / 10000000) . 'S'));
                            
                    $output[$user['samaccountname']]['lastlogon'] = $tmp;
                    //\Tracy\Debugger::dump($tmp); exit;
                }
                if (isset($user['memberof']) && is_array($user['memberof'])) {
                    foreach($user['memberof'] as $key => $val) {
                        if (is_numeric($key)) {
                            $output[$user['samaccountname']]['memberof'][] = $val;
                        }
                    }
                }
                if (isset($user['directreports']) && is_array($user['directreports'])) {
                    foreach($user['directreports'] as $key => $val) {
                        if (is_numeric($key)) {
                            $output[$user['samaccountname']]['sub'][] = $val;
                        }
                    }
                }
            }
        }
        
        uasort($output, array($this, 'namePhoneSort'));
        $users = [];
        foreach($output as $key => $val) {
            $users[$val['dn']] = $key;
        }
        foreach($output as $key => $val) {
            if (!empty($val['manager'])) {
                if (isset($users[$val['manager']])) {
                    $output[$key]['managerid'] = $users[$val['manager']];
                } else {
                    $output[$key]['managerid'] = $val['manager'];
                }
            }
            $output[$key]['subid'] = [];
            foreach($val['sub'] as $r) {
                if (isset($users[$r])) {
                    $output[$key]['subid'][] = $users[$r];
                } else {
                    $output[$key]['subid'][] = $r;
                }
            }
        }
        //\Tracy\Debugger::dump($output); exit;
        return($output);
    }
    
    public function namePhoneSort($a, $b) {
        $collator = new \Collator('cs-CZ');
        $tmp = $collator->compare($a['displayname'], $b['displayname']);
        //$tmp = $this->mb_strcasecmp($a['displayname'], $b['displayname'], 'UTF8');
        if ($tmp != 0) {
            return($tmp);
        } else {
            return($this->mb_strcasecmp($a['samaccountname'], $b['samaccountname'], 'UTF8'));
        }
    }
    
    function mb_strcasecmp($str1, $str2, $encoding = null) {
        if (null === $encoding) { $encoding = mb_internal_encoding(); }
        return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
    }
    
    /**
     * Vrací true, pokud je uživatel členem dané skupiny
     * @param string $groupName Hledaný název skupiny
     * @param string $username  Dotazovaný uživatel
     * @return boolean
     */
    public function isInGroup($groupName, $username) {
        if (true && ($this->cache instanceOf \Nette\Caching\Cache) && (isset($this->cache['ad.users'])) && (!is_null($this->cache['ad.users']))) {
            $output = $this->cache['ad.users'];
        } else {
            $output = $this->readUsers();
            if ($this->cache instanceOf \Nette\Caching\Cache) {
                $this->cache->save('ad.users', $output, array(
                    \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                    \Nette\Caching\Cache::SLIDING => FALSE));
            }
        }
        
        if (!isset($output[$username])) {
            return(false);
        } else {
            foreach($output[$username]['memberof'] as $group) {
                if (preg_match('/^CN=([^,]+),.*$/', $group, $matches)) {
                    if (isset($matches[1]) && ($matches[1] == $groupName)) {
                        return(true);
                    }
                }
            }
            return(false);
        }
    }
    
    /**
     * Get the email address for given email
     * @param string $user
     * @return string
     */
    public function getEmailForUser(string $user) {
        $tmp = array();
        if (false && ($this->cache instanceOf \Nette\Caching\Cache) && ($this->cache->load('ad.users'))) {
            // existuje v cache
            $tmp = $this->cache->load('ad.user');
        } else {
            $tmp = $this->readUsers();
            if ($this->cache instanceOf \Nette\Caching\Cache) {
                $this->cache->save('ad.users', $tmp, array(
                    \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                    \Nette\Caching\Cache::SLIDING => FALSE));
            }
        }
        //echo '<pre>'; var_dump($tmp); echo '</pre>';
        if (isset($tmp[$user]['mail'])) {
            return($tmp[$user]['mail']);
        } elseif (isset($tmp[strtolower($user)]['mail'])) {
            return($tmp[strtolower($user)]['mail']);
        } else {
            return('');
        }
    }
    
    public static function hasEmail(array $user):bool {
        $out = false;
        if (isset($user['proxyaddresses']) && is_array($user['proxyaddresses'])) {
            foreach($user['proxyaddresses'] as $row) {
                if ((strpos($row, '@casaleproject.cz') !== false) || (strpos($row, '@chpn.cz') !== false)) {
                    $out = true;
                    break;
                }
            }
        }
        return($out);
    }
}

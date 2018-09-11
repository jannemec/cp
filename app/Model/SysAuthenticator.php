<?php
namespace Model;
/**
 * Description of SysAuthenticator
 *
 * @author nemec
 */
class SysAuthenticator implements \Nette\Security\IAuthenticator {
    use \Nette\SmartObject;
    /** @var \Nette\Database\Context */
    private $dbf = null;

    function  __construct(\Nette\Database\Context $dbf) {
        $this->dbf = $dbf;
    }
    
    public function authenticate(array $credentials) {
        $username = $credentials[self::USERNAME];
        $password = isset($credentials[self::PASSWORD]) ? $credentials[self::PASSWORD] : null; // . sha1($credentials[self::USERNAME]);

        // nejprve ověříme, jestli jde o LDAP, NTLM, nebo username/password
        if (is_null($password)) {
            $tmp = explode('\\', $username);
            $username = isset($tmp[1]) ? $tmp[1] : $tmp[0];
            $domain = isset($tmp[1]) ? $tmp[0] : '';
        } else {
            $domain = null;
        }
        $userList = $this->dbf->table('sys_user')->where('username = ?', $username);
        if (count($userList) != 1 && is_null($domain)) { // uživatel nenalezen?
            throw new \Nette\Security\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
        } elseif (count($userList) != 1) {
            // Uživatel je ověřen proti doméně/LDAP, ale nemáme v dbf - nastavíme jako uživatele user
            $userList = $this->dbf->table('sys_user')->where('username = ?', 'user');
            $user = $userList->fetch();
            $userArr = $user->toArray();
            $userArr['name'] = $username;
        } else {
            $user = $userList->fetch();
            $userArr = $user->toArray();
        }
        
        if (is_null($domain) && ($user->hash != md5($password))) { // pro ověření
            throw new \Nette\Security\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
        };

        $rowRoles = $this->dbf->table('sys_user_group')->where('sys_user_id = ?', $user->id);
        
        $roles = array('guest');
        foreach($rowRoles as $key => $val) {
            $roles[] = $val->sys_group->name;
        };
        //\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, WWW_DIR . '/log');
        //\Tracy\Debugger::dump($roles);exit;
        return new \Nette\Security\Identity($user->name, $roles, array('username' => $username, 'id' => $user->id, 'user' => $userArr)); // vrátíme identitu
    }
    
    public function getCredentials() {
        return($this->dao->getItemsList('\jannemec\userright\SysUser'));
    }
}
?>
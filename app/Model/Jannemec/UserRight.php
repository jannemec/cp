<?php
/**
 * Facade for Diamac access
 *
 * @author nemec
 */
namespace Model\Jannemec;

class UserRight {
    use \Nette\SmartObject;
    /** @var \Nette\Database\Context */
    private $dbf = null;
            
    /**
     *
     * @param \Nette\Database\Context  $dbf
     */
    public function __construct(\Nette\Database\Context $dbf){
        $this->dbf = $dbf;
    }
    
    /**
     * 
     * @return \Nette\Database\Table\Selection
     */
    public function getUsers() {
        return($this->dbf->table('sys_user')->order('username'));
    }
    
    /**
     * 
     * @param int $id
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getUser(int $id) {
        return($this->dbf->table('sys_user')->where('id = ?', $id)->fetch());
    }
    
    /**
     * 
     * @param string $username
     * @param string $password
     * @return \Nette\Database\Table\ActiveRow
     */
    public function validateUser(string $username, string $password) {
        return($this->dbf->table('sys_user')->where('username = ? AND hash = ?', $username, md5(trim($password)))->fetch());
    }
    
    
    
    /**
     * 
     * @param string $username
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getUserByUsername(string $username) {
        return($this->dbf->table('sys_user')->where('username = ?', $username)->fetch());
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     */
    public function delUserById(int $id) {
        // First delete all dependencies
        $this->dbf->table('sys_user_group')->where('sys_user_id = ?', $id)->delete();
        $this->dbf->table('sys_user_data')->where('sys_user_id = ?', $id)->delete();
        return($this->dbf->table('sys_user')->where('id = ?', $id)->delete());
    }
    
    /**
     * 
     * @param array $params
     * @return  Nette\Database\Table\IRow
     */
    public function createUser(array $params) {
        if (!isset($params['lmdt'])) {
            $params['lmdt'] = new \DateTime();
        }
        if (!isset($params['rgdt'])) {
            $params['rgdt'] = new \DateTime();
        }
        if (!isset($params['lmchid'])) {
            $params['lmchid'] = 'system';
        }
        if (!isset($params['hash'])) {
            $params['hash'] = md5('otk');
        }
        if (!isset($params['description'])) {
            $params['description'] = '';
        }
        return($this->dbf->table('sys_user')->insert($params));
    }
    
    /**
     * 
     * @param \Nette\Database\Table\ActiveRow $user
     * @param array $params
     * @return boolean
     */
    public function updateUser(\Nette\Database\Table\ActiveRow $user, array $params) {
        if (!isset($params['lmdt'])) {
            $params['lmdt'] = new \DateTime();
        }
        if (!isset($params['lmchid'])) {
            $params['lmchid'] = 'system';
        }
        return($user->update($params));
    }
    
    /**
     * 
     * @param int $user_id
     * @param string $name - name of the data
     * @return \Nette\Database\Table\Selection
     */
    public function getSysUserData(int $user_id, string $name = '') {
        if (empty($name)) {
            return($this->dbf->table('sys_user_data')->where('sys_user_id = ?', $user_id)->order('name'));
        } else {
            return($this->dbf->table('sys_user_data')->where('sys_user_id = ? AND name = ? ', $user_id, $name)->fetchField('value'));
        }
    }
    
    public function removeUserDataById(int $id) {
        return($this->dbf->table('sys_user_data')->where('id = ?', $id)->delete());
    }
    
    public function updateSysUserData(\Nette\Database\Table\ActiveRow $data, array $params) {
        return($data->update($params));
    }
    
    /**
     * 
     * @param array $params
     */
    public function addSysUserData(array $params) {
        return($this->dbf->table('sys_user_data')->insert($params));
    }
    
    /**
     * 
     * @return \Nette\Database\Table\Selection
     */
    public function getGroups() {
        return($this->dbf->table('sys_group')->order('name'));
    }
    
    /**
     * 
     * @param int $id
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getGroup(int $id) {
        return($this->dbf->table('sys_group')->where('id = ?', $id)->fetch());
    }
    
    /**
     * 
     * @param string $name
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getGroupByName(string $name) {
        return($this->dbf->table('sys_group')->where('name = ?', $name)->fetch());
    }
    
    /**
     * 
     * @param array $params
     * @return  Nette\Database\Table\IRow
     */
    public function createGroup(array $params) {
        if (!isset($params['lmdt'])) {
            $params['lmdt'] = new \DateTime();
        }
        if (!isset($params['rgdt'])) {
            $params['rgdt'] = new \DateTime();
        }
        if (!isset($params['lmchid'])) {
            $params['lmchid'] = 'system';
        }
        if (!isset($params['description'])) {
            $params['description'] = '';
        }
        return($this->dbf->table('sys_group')->insert($params));
    }
    
    /**
     * 
     * @param \Nette\Database\Table\ActiveRow $group
     * @param array $params
     * @return boolean
     */
    public function updateGroup(\Nette\Database\Table\ActiveRow $group, array $params) {
        if (!isset($params['lmdt'])) {
            $params['lmdt'] = new \DateTime();
        }
        if (!isset($params['lmchid'])) {
            $params['lmchid'] = 'system';
        }
        return($group->update($params));
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     */
    public function delGroupById(int $id) {
        // First delete all dependencies
        $this->dbf->table('sys_group_right')->where('sys_group_id = ?', $id)->delete();
        $this->dbf->table('sys_user_group')->where('sys_group_id = ?', $id)->delete();
        return($this->dbf->table('sys_group')->where('id = ?', $id)->delete());
    }
    
    /**
     * 
     * @param int $user_id
     * @param int $group_id
     * @return boolean
     */
    public function isMemberOf(int $user_id, int $group_id) {
        return($this->dbf->table('sys_user_group')->where('sys_user_id = ? AND sys_group_id = ?', $user_id, $group_id)->count() > 0);
    }
    
    /**
     * Set membership
     * @param int $user_id
     * @param int $group_id
     * @param boolean $is_member
     * @return boolean
     */
    public function setMemberOf(int $user_id, int $group_id, $is_member) {
        $state = $this->dbf->table('sys_user_group')->where('sys_user_id = ? AND sys_group_id = ?', $user_id, $group_id)->fetch();
        if ($state && $is_member) {
            // Is member and should be - O.K
            return($is_member);
        } elseif (!$state && !$is_member) {
            // Is not member and should not be - O.K
            return($is_member);
        } elseif ($is_member) {
            // Should be created
            $params = array(
                  'sys_user_id' => $user_id
                , 'sys_group_id' => $group_id
                , 'value' => 1
                , 'rgdt' => new \DateTime()
                , 'lmdt' => new \DateTime()
                , 'lmchid' => 'system'
                , 'description' => ''
                );
            return($this->dbf->table('sys_user_group')->insert($params));
        } else {
            // Should be deleted
            return($this->dbf->table('sys_user_group')->where('sys_user_id = ? AND sys_group_id = ?', $user_id, $group_id)->delete() > 0);
        }
    }
    
    
    /**
     * 
     * @param int $group_id
     * @param int $right_id
     * @return boolean
     */
    public function isAllowed(int $group_id, int $right_id) {
        return($this->dbf->table('sys_group_right')->where('sys_group_id = ? AND sys_right_id = ?', $group_id, $right_id)->count() > 0);
    }
    
    /**
     * Set allowed
     * @param int $group_id
     * @param int $right_id
     * @param boolean $is_allowed
     * @return boolean
     */
    public function setAllowed(int $group_id, int $right_id, $is_allowed) {
        $state = $this->dbf->table('sys_group_right')->where('sys_group_id = ? AND sys_right_id = ?', $group_id, $right_id)->fetch();
        if ($state && $is_allowed) {
            // Is member and should be - O.K
            return($is_allowed);
        } elseif (!$state && !$is_allowed) {
            // Is not member and should not be - O.K
            return($is_allowed);
        } elseif ($is_allowed) {
            // Should be created
            $params = array(
                  'sys_group_id' => $group_id
                , 'sys_right_id' => $right_id
                , 'value' => 1
                , 'rgdt' => new \DateTime()
                , 'lmdt' => new \DateTime()
                , 'lmchid' => 'system'
                , 'description' => ''
                );
            return($this->dbf->table('sys_group_right')->insert($params));
        } else {
            // Should be deleted
            return($this->dbf->table('sys_group_right')->where('sys_group_id = ? AND sys_right_id = ?', $group_id, $right_id)->delete() > 0);
        }
    }
    
    
    /**
     * 
     * @return \Nette\Database\Table\Selection
     */
    public function getRights() {
        return($this->dbf->table('sys_right')->order('name'));
    }
    
    /**
     * 
     * @param int $id
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getRight(int $id) {
        return($this->dbf->table('sys_right')->where('id = ?', $id)->fetch());
    }
    
    /**
     * 
     * @param string $name
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getRightByName(string $name) {
        return($this->dbf->table('sys_right')->where('name = ?', $name)->fetch());
    }
    
    /**
     * 
     * @param array $params
     * @return  Nette\Database\Table\IRow
     */
    public function createRight(array $params) {
        if (!isset($params['lmdt'])) {
            $params['lmdt'] = new \DateTime();
        }
        if (!isset($params['rgdt'])) {
            $params['rgdt'] = new \DateTime();
        }
        if (!isset($params['lmchid'])) {
            $params['lmchid'] = 'system';
        }
        if (!isset($params['description'])) {
            $params['description'] = '';
        }
        return($this->dbf->table('sys_right')->insert($params));
    }
    
    /**
     * 
     * @param \Nette\Database\Table\ActiveRow $right
     * @param array $params
     * @return boolean
     */
    public function updateRight(\Nette\Database\Table\ActiveRow $right, array $params) {
        if (!isset($params['lmdt'])) {
            $params['lmdt'] = new \DateTime();
        }
        if (!isset($params['lmchid'])) {
            $params['lmchid'] = 'system';
        }
        return($right->update($params));
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     */
    public function delRightById(int $id) {
        // First delete all dependencies
        $this->dbf->table('sys_group_right')->where('sys_right_id = ?', $id)->delete();
        return($this->dbf->table('sys_right')->where('id = ?', $id)->delete());
    }
}
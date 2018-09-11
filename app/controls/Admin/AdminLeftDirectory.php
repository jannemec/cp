<?php
/**
 * Description of MyLeftDirectory
 *
 * @author nemec
 */

class AdminLeftDirectory extends \Nette\Application\UI\Control {

    public function __construct($parent = null, $name = null) {
        if (is_null($name)) {
            $name = 'adminLeftDirectory';
        }
        parent::__construct($parent, $name);
    }

    public function render() {

        $this->template->setFile(dirname(__FILE__) . '/../templates/LeftDirectory.latte');

        $this->template->menuItems = array();
        
        $this->template->menuItems[$this->getParent()->link('users')] = array(
            'name' => 'Users'
            , 'title' => 'Users edit'
            , 'class' => 'ajax'
        );
        
        $this->template->menuItems[$this->getParent()->link('groups')] = array(
            'name' => 'Groups'
            , 'title' => 'Groups edit'
            , 'class' => 'ajax'
        );
        
        $this->template->menuItems[$this->getParent()->link('rights')] = array(
            'name' => 'Rights'
            , 'title' => 'Rights edit'
            , 'class' => 'ajax'
        );
        
        $this->template->menuItems[$this->getParent()->link('phpInfo')] = array(
            'name' => 'PHPInfo'
            , 'title' => 'PHPInfo()'
            , 'class' => 'ajax'
        );
        
        $this->template->menuItems[$this->getParent()->link('netteInfo')] = array(
            'name' => 'Nette'
            , 'title' => 'Nette info'
            , 'class' => 'ajax'
        );
        
        $this->template->menuItems[$this->getParent()->link('user')] = array(
            'name' => 'USER'
            , 'title' => 'User info'
            , 'class' => 'ajax'
        );

        $this->template->render();
    }

}

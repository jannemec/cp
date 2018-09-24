<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controls\Home;

/**
 * Description of SharepointITCalender
 *
 * @author u935
 */
class PhoneBook extends \Nette\Application\UI\Control {
    
    /** @var \Adldap\AD */
    private $adService = null;
    
    public function __construct($parent = null, $name = null, \Adldap\AD $adService = null) {   
        parent::__construct();
        $parent->addComponent($this, $name);
        $this->adService = $adService;
    }
    
    public function render() {
        $this->template->setFile(dirname(__FILE__) . '/templates/PhoneBook.latte');
        $out = $this->adService->getUsers(true, false);
        $this->template->rows = array();
        foreach($out as $row) {
            if ($row == 'No data returned.') {
                break;
            }
            //\Tracy\Debugger::dump($row); exit;
            // We have to exclude public folders and exceptions
            if (substr($row['dn'], -31) == 'OU=CHPN public mb,DC=chpn,DC=cz') {
            } elseif (substr($row['dn'], -23) == 'OU=Admins,DC=chpn,DC=cz') {
            } elseif (substr($row['dn'], -26) == 'OU=UsersTemp,DC=chpn,DC=cz') {
            } elseif (substr($row['dn'], -33) == 'OU=Service Accounts,DC=chpn,DC=cz') {
            } elseif (substr($row['displayname'], 0, 13) == 'HealthMailbox') {
            } elseif (substr($row['displayname'], 0, 24) == 'Discovery Search Mailbox') {
            } elseif (substr($row['displayname'], 0, 18) == 'Microsoft Exchange') {
            } else {
                $this->template->rows[] = $row;
            }
        }
        $this->template->render();
    }
}

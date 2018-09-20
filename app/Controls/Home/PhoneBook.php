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
        $out = $this->adService->getUsers(false);
        $this->template->rows = array();
        foreach($out as $row) {
            if ($row == 'No data returned.') {
                break;
            }
            //\Tracy\Debugger::dump($row); exit;
            $this->template->rows[] = $row;
        }
        $this->template->render();
    }
}

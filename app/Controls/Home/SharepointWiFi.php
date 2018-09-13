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
class SharepointWiFi extends \Nette\Application\UI\Control {
    
    /** @var \Model\Jannemec\Sharepoint */
    private $sharePointService = null;
    public function __construct($parent = null, $name = null, \Model\Jannemec\Sharepoint $sharePointService) {   
        parent::__construct();
        $parent->addComponent($this, $name);
        $this->sharePointService = $sharePointService;
    }
    
    public function render() {
        $this->template->setFile(dirname(__FILE__) . '/templates/SharepointWiFi.latte');
        $out = $this->sharePointService->getWifis();
        $this->template->rows = array();
        foreach($out as $row) {
            if ($row == 'No data returned.') {
                break;
            }
            $this->template->rows[] = $row;
        }
        $this->template->baseurl = $this->sharePointService->getITUrl(false);
        $this->template->render();
    }
}

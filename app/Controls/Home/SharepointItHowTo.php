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
class SharepointItHowTo extends \Nette\Application\UI\Control {
    
    /** @var \Model\Jannemec\Sharepoint */
    private $sharePointService = null;
    
    public function __construct($parent = null, $name = null, \Model\Jannemec\Sharepoint $sharePointService = null) {   
        parent::__construct();
        if (empty($name)) {
            $name = 'sharepointItHowTo';
        }
        $parent->addComponent($this, $name);
        $this->sharePointService = $sharePointService;
    }
    
    public function render() {
        $this->template->setFile(dirname(__FILE__) . '/templates/SharepointItHowTo.latte');
        $out = $this->sharePointService->getITHowTos(true);
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
    
    public function handleGetITHowToArticle(string $id) {
        $out = $this->sharePointService->getITHowTo($id);
        if (!empty($out)) {
            //\Tracy\Debugger::dump($out->getProperty('Description')); exit;
            $html = $out->getProperty('Description');
            // Opravit odkazy na obrázky
            $html = strtr($html, ['src="/' => 'src="' . $this->sharePointService->getBaseUrl() . '/']);
            echo $html;
        } else {
            echo 'Article not found!';
        }
        exit;
        //$this->getParent()->terminate();
    }
}

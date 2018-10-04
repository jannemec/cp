<?php

namespace Controls\Home;

/**
 * Description of SharepointITCalender
 *
 * @author u935
 */
class SharepointProjects extends \Nette\Application\UI\Control {
    
    /** @var \jannemec\Sharepoint */
    private $sharePointService = null;
    
    public function __construct($parent = null, $name = null, \Model\Jannemec\Sharepoint $sharePointService = null) {   
        parent::__construct();
        if (empty($name)) {
            $name = 'sharepointProjects';
        }
        $parent->addComponent($this, $name);
        $this->sharePointService = $sharePointService;
    }
    
    public function render() {
        $this->template->setFile(dirname(__FILE__) . '/templates/SharepointProjects.latte');
        $toDt = new \DateTime();
        $toDt->add(new \DateInterval('P7D'));
        $out = $this->sharePointService->getProjects(true);
        $this->template->docs = array();
        foreach($out as $event) {
            if ($event == 'No data returned.') {
                break;
            }
            //\Tracy\Debugger::dump($event); exit;
            $url = explode(';', $event->fileref);
            $url = array_pop($url);
            $event->url = trim($url, '#');
            $event->lmod = \DateTime::createFromFormat('Y-m-d H:i:s', $event->modified);
            $tm = new \DateTime();
            $event->tm = intval($tm->diff(new \DateTime($event->Modified))->format('%d'));
            $this->template->docs[] = $event;
            //\Tracy\Debugger::dump($event); exit;
        }
        $this->template->baseurl = $this->sharePointService->getProjectsUrl(true);
        $this->template->render();
    }
}
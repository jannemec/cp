<?php

namespace App\Presenters;

use Nette;



class ProjectPresenter extends BasePresenter {
    
    
    /** @var \Model\Jannemec\Sharepoint */
    protected $sharepointService;
    
    /**
     * Inject sharepoint service
     * @param \Model\JannemecSharepoint
     */
    public function injectSharepointService(\Model\Jannemec\Sharepoint $sharepointService) {
        $this->sharepointService = $sharepointService;
    }

    /** @var \Model\OKBase */
    protected $OKBaseService;
    
    /**
     * Inject HR OKBase service
     * @param \Model\OKBase
     */
    public function injectOKBaseService(\Model\OKBase $OKBaseService) {
        $this->OKBaseService = $OKBaseService;
    }
    
    /** @var \Model\Infos */
    protected $infosService;
    
    /**
     * Inject infos service
     * @param \Model\Infos
     */
    public function injectInfosService(\Model\Infos $infosService) {
        $this->infosService = $infosService;
    }
    
    
    
    
    public function renderDefault() {
        $this->template->title = $this->translator->translate('Projekty') . ' ';
        $this->template->page_title = $this->translator->translate('Projekty');
        
        $this->template->projects = $this->infosService->getProjects(true);
        $this->template->infosService = $this->infosService;
        //\Tracy\Debugger::dump($this->template->projects); exit;
    }
}

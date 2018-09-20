<?php

namespace App\Presenters;

use Nette;



class ToolPresenter extends BasePresenter {
    
    
    /** @var \Model\Jannemec\Sharepoint */
    protected $sharepointService;
    
    /**
     * Inject sharepoint service
     * @param \Model\JannemecSharepoint
     */
    public function injectSharepointService(\Model\Jannemec\Sharepoint $sharepointService) {
        $this->sharepointService = $sharepointService;
    }

    
    
    
    
    
    public function renderDefault() {
        $this->template->title = $this->translator->translate('Nástroje') . ' ';
        $this->template->page_title = $this->translator->translate('Nástroje');
        
        /*$this->template->news = $this->toolsService->getNews(5);
        
        $this->template->nastenkaD = $this->toolsService->getNastenkaByType('D');
        $this->template->nastenkaF = $this->toolsService->getNastenkaByType('F');
        $this->template->nastenkaP = $this->toolsService->getNastenkaByType('P');
        
        $this->template->HR_pozice = $this->toolsService->getNastenkaByType('JOBS');*/
        
        if ($this->isAjax()) {
            $this->redrawControl();
            $this->redrawControl('contentSnippet');
            $this->redrawControl('titleSnippet');
            $this->redrawControl('pageTitleSnippet');
            $this->redrawControl('header_tmplSnippet');
        }
    }
    
    
    public function renderPhonesImport() {
        $this->template->title = $this->translator->translate('Telefony') . ' ';
        $this->template->page_title = $this->translator->translate('Telefony');
        
        
    }
    
    
    
    public function createComponentPhoneBook() {
        return(new \Controls\Home\PhoneBook($this, 'phoneBook', $this->adService));
    }
}

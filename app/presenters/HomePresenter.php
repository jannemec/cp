<?php

namespace App\Presenters;

use Nette;



class HomePresenter extends BasePresenter {
    
    
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
        $this->template->title = $this->translator->translate('CasaleProject - intranet ');
        $this->template->page_title = $this->translator->translate('Intranet');
        
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
    
    
    public function renderCompanyStructure() {
        $this->template->title = $this->translator->translate('Struktura firmy ');
        $this->template->page_title = $this->translator->translate('Struktura firmy ');
        
        $users = $this->adService->getUsers(false, true);
        $this->template->allUsers = $users;
        //\Tracy\Debugger::dump($users); exit;
        $this->template->tree = $this->createTree($users, '');
        //\Tracy\Debugger::dump($this->template->tree); exit;
    }
    
    function createTree(&$list, string $parents = ''){
        $tree = array();
        if (empty($parents)) {
            // První krok
            foreach($list as $key => $val) {
                /*if ($key == 'u935') {
                    \Tracy\Debugger::dump($val['managerid']);
                    \Tracy\Debugger::dump($list[$val['managerid']]);
                    exit;
                }*/
                if (true && $val['company'] != 'CASALE PROJECT a.s.') {
                    // Není zaměstnanec
                    unset($list[$key]);
                } elseif (empty($val['managerid'])) {
                    $tree[$key] = ['user' => $val, 'subtree' => []];
                } elseif (!isset($list[$val['managerid']])) {
                    $tree[$key] = ['user' => $val, 'subtree' => []];
                }
            }
            foreach($tree as $key => $val) {
                unset($list[$key]);
            }
            //\Tracy\Debugger::dump($tree); exit;
            //\Tracy\Debugger::dump($list); exit;
            foreach($tree as $key => $val) {
                $tree[$key]['subtree'] = $this->createTree($list, $key);
            }
        } else {
            foreach ($list as $key => $val) {
                if ($val['managerid'] == $parents) {
                    $tree[$key] = ['user' => $val, 'subtree' => []];
                    unset($list[$key]);
                }
            }
            //\Tracy\Debugger::dump($tree); exit;
            foreach($tree as $key => $val) {
                $tree[$key]['subtree'] = $this->createTree($list, $key);
            }
        } 
        uasort($tree, [$this, 'sortADByDepname']);
        return($tree);
    }
    
    public function sortADByDepname($a, $b) {
        if ($a['user']['department'] < $b['user']['department']) {
            return(-1);
        } elseif ($a['user']['department'] > $b['user']['department']) {
            return(1);
        } elseif ($a['user']['displayname'] < $b['user']['displayname']) {
            return(-1);
        } elseif ($a['user']['displayname'] > $b['user']['displayname']) {
            return(1);
        } else {
            return(0);
        }
    }
    
    public function showCompanyTree($tree) {
        if (!empty($tree)) {
            echo '<ul>';
            foreach($tree as $key => $val) {
                echo '<li' . ($val['user']['disabled'] ? ' data-jstree=\'{"icon":"fas fa-trash"}\'' : (empty($val['subtree']) ? ' data-jstree=\'{"icon":"far fa-user"}\'' : ' data-jstree=\'{"icon":"fas fa-users"}\'')) . '>';
                echo $key . ': ' . $val['user']['displayname'] . '(' . $val['user']['department'] . ')';
                if (!empty($val['subtree'])) {
                    $this->showCompanyTree($val['subtree']);
                }
                echo '</li>';
            }
            echo '</ul>';
        }
    }


}

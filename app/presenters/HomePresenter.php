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
        
        $this->template->officeMap = [];
        $this->template->users = $this->adService->getUsers(false);
        foreach($this->systemService->getOfficeSchemaDataSource() as $person) {
            $person = $person->toArray();
            $person['name'] = empty($person['personact']) ? $person['persondef'] : $person['personact'];
            $person['txt'] = '<b>' . $person['name'] . '</b>';
            foreach($this->template->users as $user) {
                //var_dump($user); exit;
                if (($person['name'] == $user['displayname']) || ($person['name'] == $user['displayname'] . ' ' . $user['pager'])) {
                    $person['txt'] .= '<br />' . $user['title'] . '<br />' . $user['telephoneNumber'] . '<br />' . $user['mail'];
                }
            }
            $this->template->officeMap[] = $person;
        };
        
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

    public function createComponentSharepointProjects() {
        return(new \Controls\Home\SharepointProjects($this, 'sharepointProjects', $this->sharepointService));
    }
    
    public function createComponentSharepointWifi() {
        return(new \Controls\Home\SharepointWiFi($this, 'sharepointWifi', $this->sharepointService));
    }
    
    public function createComponentSharepointItHowTo() {
        return(new \Controls\Home\SharepointItHowTo($this, 'sharepointItHowTo', $this->sharepointService));
    }
    
    public function createComponentPhoneBook() {
        return(new \Controls\Home\PhoneBook($this, 'phoneBook', $this->adService));
    }
}

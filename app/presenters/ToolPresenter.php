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

    /** @var \Model\OKBase */
    protected $OKBaseService;
    
    /**
     * Inject sharepoint service
     * @param \Model\OKBase
     */
    public function injectOKBaseService(\Model\OKBase $OKBaseService) {
        $this->OKBaseService = $OKBaseService;
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
    
    
    
    public function createComponentPhonesImport() {
        $frm = new \Nette\Application\UI\Form($this, 'phonesImport');
        $frm->getElementPrototype()->class('pure-form pure-form-aligned');
        $frm->addGroup('Import dat');
        $frm->addTextArea('data', 'Import', 40, 4)
                ->setRequired(true);
        
        $frm->addGroup();
        $frm->addSubmit('sbmt', 'IMPORT')->getControlPrototype()->class('button-xsmall pure-button');;
        $frm['sbmt']->onClick([$this, 'phonesImportSubmitted']);
        
        $frm->onSuccess[] = [$this, 'phonesImportSubmitted'];
        
        $default = [];
        $frm->setDefaults($default);
        
        $renderer = $frm->getRenderer();
        $renderer->wrappers['controls']['container'] = null; //'dl';
        $renderer->wrappers['pair']['container'] = 'div class=pure-control-group';
        $renderer->wrappers['label']['container'] = null; //'dt';
        $renderer->wrappers['control']['container'] = null; //'dd';
        return($frm);
    }
    
    public function phonesImportSubmitted(\Nette\Application\UI\Form $frm) {
        if ($frm['sbmt']->isSubmittedBy()) {
            // Import dat
            $this->flashMessage('Data odeslána', 'info');
            $data = $frm['data']->getvalue();
            $users = $this->adService->getUsers(true, true);
            /*foreach($users as $user) {
                echo $user['displayname'] . '<br />';
                if ($user['displayname'] == 'Sarmanova Michaela') {
                    \Tracy\Debugger::dump($user);
                }
            }
            exit;*/
            foreach(explode("\n", $data) as $row) {
                if (trim($row) != '') {
                    $row = explode("\t", $row);
                    if (count($row) > 2) {
                        // Check the row
                        $name = trim($row[0]);
                        // Ořezání titulů
                        $name = trim(strtr($name, ['Mgr.' => '', 'Ing.' => '', 'Bc.' => '', 'ing' => '', 'PH.D.' => '', 'DiS.' => '', 'PhD.' => '', 'Ph.D' => '', 'ing.' => '']), ' .,');
                        // Přehození křestní - příjmení
                        $name = explode(' ', $name);
                        $tmp = array_shift($name);
                        $name = trim(implode(' ', $name) . ' ' . $tmp);
                        // Zrušení diakritiky
                        $name2 = \Model\Jannemec\Tools::utf2ascii(strtr($name, ['ö' => 'o']));
                        $name3 = strtr($name2, ['Internet ' => '', 'Martinek' => 'Martinek1']);
                        $phone = trim($row[1]);
                        if (substr($phone, 0, 3) == '420') {
                            $phone = '+' . $phone;
                            /*echo '++++' . $phone . '<br />';
                        } else {
                            echo '----' . $phone . '<br />';*/
                        }
                        $found = false;
                        foreach($users as $key => $user) {
                            //\Tracy\Debugger::dump($user); exit;
                            if ((!isset($user['disabled']) || !$user['disabled']) && ((trim(strtr($user['mobile'], [' ' => ''])) == $phone)
                                    || (trim(strtr($user['telephoneNumber'], [' ' => ''])) == $phone)
                                    || (trim(strtr($user['homePhone'], [' ' => ''])) == $phone))) {
                                $found = true;
                                $users[$key]['OK'] = true;
                                if (($user['displayname'] != $name) && ($user['displayname'] != $name2) && ($user['displayname'] != $name3)) {
                                    $this->flashMessage('Nesouhlasí jméno AD ' . $user['displayname'] . '-' . $name . ' pro tel. ' . $phone, 'info');
                                }
                                break;
                            }
                        }
                        if (!$found) {
                            if (in_array(trim($row[4]), ['Minute-Rated Tariff 1.00 CZK', 'Fix Number User IP Phone 1.00 CZK', 'Fix Number User PSTN 1.00 CZK'])) {
                                $this->flashMessage('Telefon ' . $phone . ' ' . $name . ' nenalezen v AD', 'info');
                            } else {
                                $this->flashMessage('Telefon ' . $phone . ' ' . $name . ' nenalezen v AD', 'error');
                            }
                        }
                    }
                }
            }
            foreach($users as $key => $user) {
                if (!isset($user['OK']) && (($user['mobile'] != '') || ($user['telephoneNumber'] != ''))) {
                    // Ještě výjimky
                    if (!in_array($user['mobile'], ['+420 739461975'])
                            && (!isset($user['disabled']) || !$user['disabled'])) {
                        $this->flashMessage('Uživatel ' . $user['displayname'] . ' ' . $user['mobile'] . ' nemá tel. v dbf', 'error');
                    }
                }
            }
        } else {
            // Chyba
            $this->flashMessage('Data nebyla odeslána', 'error');
        }
    }
    
    
    public function renderOKBase() {
        $this->template->title = $this->translator->translate('OKBase zaměstnanci') . ' ';
        $this->template->page_title = $this->translator->translate('OKBase zaměstnanci');
        
        $this->template->employees = [];
        $this->template->users = $this->adService->getUsers();
        foreach($this->OKBaseService->getEmployees() as $employee) {
            foreach($this->template->users as $key => $user) {
                //\Tracy\Debugger::dump($user); exit;
                $employee->status = 'Not found';
                if ((\Model\Jannemec\Tools::utf2ascii($employee->jmeno) == \Model\Jannemec\Tools::utf2ascii($user['givenName'])) 
                        && (\Model\Jannemec\Tools::utf2ascii($employee->prijmeni) == \Model\Jannemec\Tools::utf2ascii($user['sn']))) {
                    // Uživatel nalezen
                    $employee->status = '';
                    if (mb_strtolower($employee->email) != mb_strtolower($user['mail'])) {
                        $employee->status = 'Email: ' . $user['mail'];
                    }
                    if (($employee->telefon != strtr($user['telephoneNumber'], ['+420 ' => ''])) && ($employee->telefon != strtr($user['mobile'], ['+420 ' => '']))) {
                        $employee->status = 'Tel: ' . $user['telephoneNumber'];
                    }
                    unset($this->template->users[$key]);
                    break;
                }
            }
            $this->template->employees[] = $employee;
        };
        
    }
}

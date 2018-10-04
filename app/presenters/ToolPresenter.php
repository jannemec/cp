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
                    if (count($row) >= 4) {
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
                if (!isset($user['OK']) 
                        && ((trim($user['mobile']) != '') || (trim($user['telephoneNumber']) != ''))
                        && (trim($user['company']) == 'CASALE PROJECT a.s.')) {
                    // Ještě výjimky
                    if (!in_array($user['mobile'], ['+420 739461975'])
                            && (!isset($user['disabled']) || !$user['disabled'])) {
                        $this->flashMessage('Uživatel ' . $user['displayname'] . ' ' . $user['mobile'] . '/' . $user['telephoneNumber'] . ' nemá tel. v dbf', 'error');
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
        $this->template->users = $this->adService->getUsers(false, true);
        foreach($this->OKBaseService->getEmployees() as $employee) {
            // Nejprve zkusíme ty povolené
            $employee->status = 'Not found';
            $employee->username = '';
            foreach($this->template->users as $key => $user) {
                //\Tracy\Debugger::dump($user); exit;
                if (!isset($user['disabled']) || !$user['disabled']) {
                    if ((\Model\Jannemec\Tools::utf2ascii($employee->jmeno) == \Model\Jannemec\Tools::utf2ascii($user['givenName'])) 
                            && ((\Model\Jannemec\Tools::utf2ascii($employee->prijmeni) == \Model\Jannemec\Tools::utf2ascii($user['sn']))
                                    || (strtr(\Model\Jannemec\Tools::utf2ascii($employee->prijmeni), $this->OKBaseService->getNamesExceptions()) == \Model\Jannemec\Tools::utf2ascii($user['sn'])))
                            ) {
                        // Uživatel nalezen
                        //\Tracy\Debugger::dump($employee); \Tracy\Debugger::dump($user); exit;
                        $employee->status = '';
                        if (mb_strtolower($employee->email) != mb_strtolower($user['mail'])) {
                            $employee->status = 'Email: ' . $user['mail'];
                        }
                        // Kontrola telefonu
                        if (($employee->telefon != strtr($user['telephoneNumber'], ['+420 ' => ''])) && ($employee->telefon != strtr($user['mobile'], ['+420 ' => '']))) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') . 'Tel: ' . $user['telephoneNumber'];
                        }
                        // Kontrola os.č.
                        if (mb_strtolower($employee->osoc) != mb_strtolower($user['pager'])) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') .  'Os.č.: ' . $user['pager'];
                        }
                        // Kontrola oddělelní
                        if (mb_strtolower($employee->utvar) != substr(mb_strtolower($user['physicalDeliveryOfficeName']), 0, strlen($employee->utvar))) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') .  'Útvar: ' . $user['physicalDeliveryOfficeName'];
                        }
                        // Kontrola oddělelní
                        if ($user['company'] != 'CASALE PROJECT a.s.') {
                            $employee->status .= ($employee->status == '' ? '': '<br />') .  'Společnost: ' . $user['company'];
                        }
                        // Kontrola je aktivní
                        if (isset($user['disabled']) && $user['disabled']) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') .  'Účet zablokován.';
                        }
                        $employee->username = $user['samaccountname'];
                        unset($this->template->users[$key]);
                        break;
                    }
                }
            }
            if ($employee->status != 'Not found') {
                $this->template->employees[] = $employee;
                continue;
            }
            
            // A následně ty zablokované
            foreach($this->template->users as $key => $user) {
                //\Tracy\Debugger::dump($user); exit;
                if (isset($user['disabled']) && $user['disabled']) {
                    if ((\Model\Jannemec\Tools::utf2ascii($employee->jmeno) == \Model\Jannemec\Tools::utf2ascii($user['givenName'])) 
                            && ((\Model\Jannemec\Tools::utf2ascii($employee->prijmeni) == \Model\Jannemec\Tools::utf2ascii($user['sn']))
                                    || (strtr(\Model\Jannemec\Tools::utf2ascii($employee->prijmeni), $this->OKBaseService->getNamesExceptions()) == \Model\Jannemec\Tools::utf2ascii($user['sn'])))
                            ) {
                        // Uživatel nalezen
                        //\Tracy\Debugger::dump($employee); \Tracy\Debugger::dump($user); exit;
                        $employee->status = '';
                        if (mb_strtolower($employee->email) != mb_strtolower($user['mail'])) {
                            $employee->status = 'Email: ' . $user['mail'] . 'x' . $employee->email;
                        }
                        // Kontrola telefonu
                        if (($employee->telefon != strtr($user['telephoneNumber'], ['+420 ' => ''])) && ($employee->telefon != strtr($user['mobile'], ['+420 ' => '']))) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') . 'Tel: ' . $user['telephoneNumber'] . 'x' . $employee->telefon;
                        }
                        // Kontrola os.č.
                        if (mb_strtolower($employee->osoc) != mb_strtolower($user['pager'])) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') .  'Os.č.: ' . $user['pager'] . 'x' . $employee->osoc;
                        }
                        // Kontrola oddělelní
                        if (mb_strtolower($employee->utvar) != substr(mb_strtolower($user['physicalDeliveryOfficeName']), 0, strlen($employee->utvar))) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') .  'Útvar: ' . $user['physicalDeliveryOfficeName'] . 'x' . $employee->utvar;
                        }
                        // Kontrola oddělelní
                        if ($user['company'] != 'CASALE PROJECT a.s.') {
                            $employee->status .= ($employee->status == '' ? '': '<br />') .  'Společnost: ' . $user['company'] . 'x' . 'CASALE PROJECT a.s.';
                        }
                        // Kontrola je aktivní
                        if (isset($user['disabled']) && $user['disabled']) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') .  'Účet zablokován.';
                        }
                        $employee->username = $user['samaccountname'];
                        unset($this->template->users[$key]);
                        break;
                    }
                }
            }
            $this->template->employees[] = $employee;
        };
        
        // A doplníme ty z AD, které jsme nenalezli
        foreach($this->template->users as $user) {
            //\Tracy\Debugger::dump($user); exit;
            if ((!isset($user['disabled']) || !$user['disabled']) && ($user['company'] == 'CASALE PROJECT a.s.')) {
                //\Tracy\Debugger::dump($user); exit;
                $employee = new \Dibi\Row([
                          'osoc' => $user['pager']
                        , 'status' => 'Nenalezen v OKBase'
                        , 'jmeno' => $user['givenName']
                        , 'prijmeni' => $user['sn']
                        , 'utvar' => $user['physicalDeliveryOfficeName']
                        , 'email' => $user['mail']
                        , 'telefon' => $user['telephoneNumber']
                        , 'username' => $user['samaccountname']
                        ]);
                $this->template->employees[] = $employee;
            }
        }
        
        // Ještě zkontrolujeme uživatele z INFOSu
        $this->template->infos = $this->infosService->getUsers();
        foreach($this->template->employees as $employee) {
            if (empty($employee['status'])) {
                //\Tracy\Debugger::dump($employee);
                $found = false;
                foreach($this->template->infos as $key => $val) {
                    if (trim(mb_strtolower($employee['username']) == trim(mb_strtolower($val->useridos)))) {
                        // Nalezeno
                        if (trim($val->utvar) != trim($employee['utvar'])) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') . 'INFOS Útvar: ' . $val->utvar . 'x' . $employee['utvar'];
                        }
                        if (trim($val->osoc) != trim($employee['osoc'])) {
                            $employee->status .= ($employee->status == '' ? '': '<br />') . 'INFOS Os č.: ' . $val->osoc . 'x' . $employee['osoc'];
                        }
                        $found = true;
                        unset($this->template->infos[$key]);
                        break;
                    }
                }
                if (!$found) {
                    $employee['status'] = 'User not found in Infos';
                }
            }
        }

        // A doplníme ty z AD, které jsme nenalezli
        foreach($this->template->infos as $user) {
            //\Tracy\Debugger::dump($user); exit;
            $employee = new \Dibi\Row([
                      'osoc' => $user['osoc']
                    , 'status' => 'Not found in OKBase - exists in INFOS'
                    , 'jmeno' => $user->userfull
                    , 'prijmeni' => ''
                    , 'utvar' => $user->utvar
                    , 'email' => ''
                    , 'telefon' => ''
                    , 'username' => mb_strtolower($user->useridos)
                    ]);
            $this->template->employees[] = $employee;
        }
    }
    
    public function renderCasaleSchema() {
        $this->template->title = $this->translator->translate('Schema kanceláří') . ' ';
        $this->template->page_title = $this->translator->translate('Schema kanceláří');
        
        $this->template->adUsers = $this->adService->getUsers();
        /*if ($this->isAjax()) {
		$this->redrawControl('flashes');
		$this['officeSchemaDatagrid']->reload();
	}*/
    }
    
    public function createComponentOfficeSchemaDatagrid() {
        return(new \Controls\Tool\OfficeSchemaDatagrid($this, 'officeSchemaDatagrid', $this->adService, $this->systemService));
    }
    
    public function handleDeletePosition($id) {
        $this->systemService->getOfficeSchemaDataSource()->where('id', $id)->delete();
        $this->flashMessage("Item deleted [$id].", 'success');

	if ($this->isAjax()) {
		$this->redrawControl('flashes');
		$this['officeSchemaDatagrid']->reload();
	} else {
		$this->redirect('this');
	}
    }
}

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
            $helpDeskOut = "Asset Name\tState\tAssigned to User\tProduct Name\tDescription\tProduct Type";
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
                        $name3 = strtr($name2, [
                            'Internet ' => ''
                            , 'Martinek' => 'Martinek1'
                            , 'ze mzdy Truhlarova srazka' => 'Truhlarova Elena chp']);
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
                            if (!in_array(substr($row[4], 0, 10), ['Fix Number'])) {
                                $helpDeskOut .= "\n" . $phone . '';
                                $helpDeskOut .= "\t" . 'In Store';
                                $helpDeskOut .= "\t" . '';
                                $helpDeskOut .= "\t" . 'mobile phone';
                                $helpDeskOut .= "\t" . $row[4];
                                $helpDeskOut .= "\tPhone";
                            }
                            if (in_array(trim($row[4]), ['Minute-Rated Tariff 1.00 CZK', 'Fix Number User IP Phone 1.00 CZK', 'Fix Number User PSTN 1.00 CZK'])) {
                                $this->flashMessage('Telefon ' . $phone . ' ' . $name . ' nenalezen v AD', 'info');
                            } else {
                                $this->flashMessage('Telefon ' . $phone . ' ' . $name . ' nenalezen v AD', 'error');
                            }
                        } else {
                            if (!in_array(substr($row[4], 0, 10), ['Fix Number'])) {
                                $helpDeskOut .= "\n" . $phone . '';
                                $helpDeskOut .= "\t" . 'In Use';
                                $helpDeskOut .= "\t" . $user['displayname'];
                                $helpDeskOut .= "\t" . 'mobile phone';
                                $helpDeskOut .= "\t" . $row[4];
                                $helpDeskOut .= "\tPhone";
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
            
            $this->template->helpdeskOut = $helpDeskOut;
            $flnm = 'phones_' . date('Ymd') . '.csv';
            $fl = fopen(WWW_DIR . '\\temp\\' . $flnm, 'w+', false);
            fwrite($fl, strtr($helpDeskOut, ["\t" => ',']));
            fclose($fl);
            $this->template->helpdeskLink = BASE_URI . 'temp/' . $flnm;
        } else {
            // Chyba
            $this->flashMessage('Data nebyla odeslána', 'error');
        }
    }
    
    public function renderCardReader() {
        $this->template->title = $this->translator->translate('CARD dec - hex') . ' ';
        $this->template->page_title = $this->translator->translate('CARD dec - hex');
    }
    
    public function renderADUsers() {
        $this->template->title = $this->translator->translate('AD - doména - user') . ' ';
        $this->template->page_title = $this->translator->translate('AD - doména - user');
        
        $this->template->users = $this->adService->getUsers(true, true);
        $this->template->skript_names = [];
        foreach($this->template->users as $user) {
            if (($user['type'] == 'user') && (!$user['disabled']) && !empty($user['scriptPath']) && !in_array($user['scriptPath'], $this->template->skript_names)) {
                $this->template->skript_names[$user['scriptPath']] = $user['scriptPath'];
            } 
        }
        $this->template->limitLogin = new \DateTime();
        $this->template->limitLogin->sub(new \DateInterval('P180D'));
    }
    
    public function renderADComps() {
        $this->template->title = $this->translator->translate('AD - doména - PC ') . ' ';
        $this->template->page_title = $this->translator->translate('AD - doména - PC');
        
        $this->template->pcs = $this->adService->getPCs(true, true);
        //\Tracy\Debugger::dump($this->template->pcs); exit;
        /*$this->template->skript_names = [];
        foreach($this->template->users as $user) {
            if (($user['type'] == 'user') && (!$user['disabled']) && !empty($user['scriptPath']) && !in_array($user['scriptPath'], $this->template->skript_names)) {
                $this->template->skript_names[$user['scriptPath']] = $user['scriptPath'];
            } 
        }*/
        $this->template->limitLogin = new \DateTime();
        $this->template->limitLogin->sub(new \DateInterval('P180D'));
    }
    
    public function renderOKBase() {
        $this->template->title = $this->translator->translate('OKBase zaměstnanci') . ' ';
        $this->template->page_title = $this->translator->translate('OKBase zaměstnanci');
        
        $this->template->employees = [];
        $this->template->users = $this->adService->getUsers(false, true);
        /*foreach($this->OKBaseService->getEmployees() as $employee) {
            \Tracy\debugger::dump($employee->osoc);
        };
        exit;*/
        $osocs = [];
        foreach($this->OKBaseService->getEmployees() as $employee) {
            
            $employee->status = 'Not found in AD';
            $employee->username = '';
            $employee->ADComment = '';
            /*foreach($this->template->users as $key => $user) {
                echo \Model\Jannemec\Tools::utf2ascii($user['givenName']);
                echo ' ';
                echo \Model\Jannemec\Tools::utf2ascii($user['sn']);
                echo '<br />';
            }
            exit;*/
            
            // Nejprve ty, kde sedí jméno a osobní číslo
            if (in_array(trim($employee->osoc), $osocs)) {
                continue;
            }
            foreach($this->template->users as $key => $user) {
                //\Tracy\Debugger::dump($user); exit;
                //\Tracy\Debugger::dump($employee); exit;
                if (!isset($user['disabled']) || !$user['disabled']) {
                    if ((trim($employee->osoc) == trim($user['pager']))
                            && (\Model\Jannemec\Tools::utf2ascii($employee->jmeno) == \Model\Jannemec\Tools::utf2ascii($user['givenName'])) 
                            && ((\Model\Jannemec\Tools::utf2ascii($employee->prijmeni) == \Model\Jannemec\Tools::utf2ascii($user['sn']))
                                    || (strtr(\Model\Jannemec\Tools::utf2ascii($employee->prijmeni), $this->OKBaseService->getNamesExceptions()) == \Model\Jannemec\Tools::utf2ascii($user['sn'])))
                            ) {
                        // Uživatel nalezen
                        //\Tracy\Debugger::dump($employee); \Tracy\Debugger::dump($user); exit;
                        $employee->status = '';
                        $employee->ADComment = $user['description'];
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
                        $osocs[] = trim($employee->osoc);
                        //echo '1: ' . $employee->username . '<br />';
                        break;
                    }
                }
            }
            
            if ($employee->status != 'Not found in AD') {
                $this->template->employees[] = $employee;
                continue;
            }
            
            // Nejprve zkusíme ty povolené
            if (in_array(trim($employee->osoc), $osocs)) {
                continue;
            }
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
                        $employee->ADComment = $user['description'];
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
                        $osocs[] = trim($employee->osoc);
                        //echo '2: ' . $employee->username . '<br />';
                        break;
                    }
                }
            }
            if ($employee->status != 'Not found in AD') {
                $this->template->employees[] = $employee;
                continue;
            }
            
            // A následně ty zablokované
            if (in_array(trim($employee->osoc), $osocs)) {
                continue;
            }
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
                        $employee->ADComment = $user['description'];
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
                        $osocs[] = trim($employee->osoc);
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
                            , 'status' => ''
                            , 'jmeno' => $user['givenName']
                            , 'prijmeni' => $user['sn']
                            , 'utvar' => $user['physicalDeliveryOfficeName']
                            , 'email' => $user['mail']
                            , 'telefon' => $user['telephoneNumber']
                            , 'username' => $user['samaccountname']
                            , 'ADComment' => $user['description']
                            ]);
                if (true
                    && (strpos($user['givenName'], 'travel') !== false)
                    ) {
                    $employee->status = 'Nenalezen v OKBase';
                }
                $this->template->employees[] = $employee;
            }
        }
        
        // Ještě zkontrolujeme uživatele z INFOSu
        $this->template->infos = $this->infosService->getUsers();
        foreach($this->template->employees as $employee) {
            if (empty($employee['status'])) {
                //\Tracy\Debugger::dump($employee);
                if (!isset($employee['ADComment'])) {
                    $employee['ADComment'] = '???';
                }
                $found = false;
                foreach($this->template->infos as $key => $val) {
                    // Opravíme výjimky v uživatelích
                    $username = trim(mb_strtolower($val->useridos));
                    switch($username) {
                        case 'nemec3':
                            $username = 'nemec';
                            break;
                        /*case 'lepka2':
                            $username = 'lepka';
                            break;*/
                        case 'lepka':
                            $username = 'lepka';
                            break;
                        case 'krejci1':
                            $username = 'krejci';
                            break;
                        case 'janousek':
                            $username = 'janouse1';
                            break;
                        case 'marcalik':
                            $username = 'lmarcali';
                            break;
                    }
                    //echo trim(mb_strtolower($employee['username'])) . '_' . $username . '_' . trim(mb_strtolower($val->useridos)) . '<br />';
                    if ((trim(mb_strtolower($employee['username'])) == trim(mb_strtolower($val->useridos))) || (trim(mb_strtolower($employee['username'])) == $username)) {
                        // Nalezeno
                        //echo trim(mb_strtolower($employee['username'])) . '<br />';
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
                    if (!isset($employee['ADComment'])) {
                        $employee['ADComment'] = '???';
                    }
                }
            }
        }

        // A doplníme ty z AD, které jsme nenalezli
        foreach($this->template->infos as $user) {
            //\Tracy\Debugger::dump($user); exit;
            // Vyhození výjimek
            if ((strpos($user->userfull, 'infos') !== false)
                    && (strpos($user->userfull, 'import') !== false)
                    && (strpos($user->userfull, 'maily') !== false)
                    && (strpos($user->userfull, 'travel') !== false)
                    ) {
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
    
    public function renderProjectsImport() {
        $this->template->title = $this->translator->translate('Provedena aktualizace projektů') . ' ';
        $this->template->page_title = $this->translator->translate('Aktualizace porjektů');
        
        $projects = $this->infosService->getProjects(false, true);
        
        $sites = [];
        foreach($this->sharepointService->getProjectSites() as $site) {
            $tmp = explode('/', $url = trim($site->Url));
            $project = array_pop($tmp);
            $project = trim($project);
            $obj = new \stdClass();
            $obj->Url = $url;
            $obj->Description = $project;
            $sites[$project] = $obj;
        }
        
        //\Tracy\Debugger::dump($sites); exit;
        $counter = 500;
        $updates1 = $updates2 = 0;
        foreach($projects as $project) {
            if (true || (trim($project->phpursale) == '0D0030C')) {
                //\Tracy\Debugger:: dump($project);exit;
                // If the change is older one month, quit
                $dt = new \DateTime($project->chgtim);;
                //\Tracy\Debugger::dump($dt->getTimestamp());exit;
                if ($dt->getTimestamp() < time() - 30 * 24 * 3600) {
                    break;
                }
                //var_dump([$dt->getTimestamp(), time() - 30 * 24 * 3600]); exit;
                $shpProj = $this->sharepointService->getITProject(trim($project['phpursale']));
                // Dohledání názvu projektu - aktuální/pokud je prázdný, tak sebe sama ...
                if (empty($project->phpursaleup)) {
                    $parent = null;
                    $projname = trim($project->phproj);
                } else {
                    $parentProject = $this->sharepointService->getITProject(trim($project['phpursaleup']));
                    if ($parentProject) {
                        $parent = $parentProject->Id;
                        $projname = trim($project->phproj);
                    } else {
                        $parent = null;
                        $projname = trim($project->phproj);
                    }
                }
                // Dohledání jména osoby
                $personName = $this->infosService->getUser($project->userid);
                if ($personName) {
                    $personName = trim($personName->userfull);
                } else {
                    $personName = trim($project->userid);
                }
                // Dohledání oboru
                $obor = $this->infosService->getMarketSegment(is_null($project->stcode) ? '' : $project->stcode);
                if (!$obor) {
                    $obor = trim($project->stcode);
                }
                // Dohledání site
                $site = isset($sites[trim($project['phpursale'])]) ? $sites[trim($project['phpursale'])] : null;
                
                if (empty($shpProj)) {
                    // Založení záznamu
                    $record = [
                          'name' => trim($project->phdesig1)
                        , 'description' => trim($project->phdesig2)
                        , 'pid' => trim($project->phpursale)
                        , 'person' => $personName
                        , 'project' => trim($projname)
                        , 'parent' => $parent
                        , 'person' => trim($project->userid)
                        , 'contract' => trim($project->phcontract)
                        , 'status' => trim($project->phtk) == 'Y' ? 'Active' : 'Closed'
                        , 'product' => trim($project->dpdruh2)
                        , 'obor' => $obor
                        , 'sitelink' => $site
                    ];
                    $out = $this->sharepointService->addITProject($record);
                    $counter--;
                    $updates1++;
                    if ($counter <= 0) {
                        break;
                    }
                } else {
                    // Konrola/aktualizace záznamu
                    $update = [];
                    if (is_null($site) && !is_null($shpProj->SiteLink)) {
                        $update['SiteLink'] = $site;
                    } elseif (is_null($shpProj->SiteLink) && !is_null($site)) {
                        $update['SiteLink'] = $site;
                    } elseif (is_null($site) && is_null($shpProj->SiteLink)) {
                        // Do nothing
                    } elseif (trim($site->Url) != trim($shpProj->SiteLink->Url)) {
                        $update['SiteLink'] = $site;
                    }
                    if (trim($obor) != $shpProj->Obor) {
                        $update['Obor'] = $obor;
                    }
                    if (trim($project->dpdruh2) != $shpProj->Product) {
                        $update['Product'] = trim($project->dpdruh2);
                    }
                    if (trim($projname) != $shpProj->Project) {
                        $update['Project'] = trim($projname);
                    }
                    if ($personName != $shpProj->Person) {
                        $update['Person'] = $personName;
                    }
                    if (trim($project->phdesig1) != $shpProj->Title) {
                        $update['Title'] = trim($project->phdesig1);
                    }
                    if (trim($project->phdesig2) != $shpProj->Description) {
                        $update['Description'] = trim($project->phdesig2);
                    }
                    if (trim($project->phcontract) != $shpProj->Contract) {
                        $update['Contract'] = trim($project->phcontract);
                    }
                    //var_dump([$project->phtk, $shpProj->Status]); exit;
                    if (trim($project->phtk) == 'Y' && in_array($shpProj->Status, ['Closed', 'Canceled', 'Preparation'])) {
                        $update['Status'] = 'Active';
                    } elseif (trim($project->phtk) != 'Y' && in_array($shpProj->Status, ['Active', 'Preparation'])) {
                        $update['Status'] = 'Closed';
                    }
                    if (!empty($update)) {
                        /*\Tracy\Debugger::dump($shpProj);
                        \Tracy\Debugger::dump($project);
                        var_dump($update); exit;*/
                        $out = $this->sharepointService->updateITProject($shpProj, $update);
                        $counter--;
                        $updates2++;
                        if ($counter <= 0) {
                            break;
                        }
                    }
                }
            }
        }
        $this->template->updates1 = $updates1;
        $this->template->updates2 = $updates2;
    }
    
    public function renderPProjectsImport() {
        $this->template->title = $this->translator->translate('Provedena aktualizace projektů') . ' ';
        $this->template->page_title = $this->translator->translate('Aktualizace porjektů');
        
        $projects = $this->infosService->getProjects(false, true);
        
        $sites = [];
        foreach($this->sharepointService->getProjectSites() as $site) {
            $tmp = explode('/', $url = trim($site->Url));
            $project = array_pop($tmp);
            $project = trim($project);
            $obj = new \stdClass();
            $obj->Url = $url;
            $obj->Description = $project;
            $sites[$project] = $obj;
        }
        
        //\Tracy\Debugger::dump($sites); exit;
        $counter = 500;
        $updates1 = $updates2 = 0;
        foreach($projects as $project) {
            if (true || (trim($project->phpursale) == '0D0030C')) {
                //\Tracy\Debugger:: dump($project);exit;
                // If the change is older one month, quit
                $dt = new \DateTime($project->chgtim);;
                //\Tracy\Debugger::dump($dt->getTimestamp());exit;
                if ($dt->getTimestamp() < time() - 30 * 24 * 3600) {
                    break;
                }
                //var_dump([$dt->getTimestamp(), time() - 30 * 24 * 3600]); exit;
                $shpProj = $this->sharepointService->getProjectProject(trim($project['phpursale']));
                // Dohledání názvu projektu - aktuální/pokud je prázdný, tak sebe sama ...
                if (empty($project->phpursaleup)) {
                    $parent = null;
                    $projname = trim($project->phproj);
                } else {
                    $parentProject = $this->sharepointService->getProjectProject(trim($project['phpursaleup']));
                    if ($parentProject) {
                        $parent = $parentProject->Id;
                        $projname = trim($project->phproj);
                    } else {
                        $parent = null;
                        $projname = trim($project->phproj);
                    }
                }
                // Dohledání jména osoby
                $personName = $this->infosService->getUser($project->userid);
                if ($personName) {
                    $personName = trim($personName->userfull);
                } else {
                    $personName = trim($project->userid);
                }
                // Dohledání oboru
                $obor = $this->infosService->getMarketSegment(is_null($project->stcode) ? '' : $project->stcode);
                if (!$obor) {
                    $obor = trim($project->stcode);
                }
                // Dohledání site
                $site = isset($sites[trim($project['phpursale'])]) ? $sites[trim($project['phpursale'])] : null;
                
                if (empty($shpProj)) {
                    // Založení záznamu
                    $record = [
                          'name' => trim($project->phdesig1)
                        , 'description' => trim($project->phdesig2)
                        , 'pid' => trim($project->phpursale)
                        , 'person' => $personName
                        , 'project' => trim($projname)
                        , 'parent' => $parent
                        , 'person' => trim($project->userid)
                        , 'contract' => trim($project->phcontract)
                        , 'status' => trim($project->phtk) == 'Y' ? 'Active' : 'Closed'
                        , 'product' => trim($project->dpdruh2)
                        , 'obor' => $obor
                        , 'sitelink' => $site
                    ];
                    $out = $this->sharepointService->addProjectProject($record);
                    $counter--;
                    $updates1++;
                    if ($counter <= 0) {
                        break;
                    }
                } else {
                    // Konrola/aktualizace záznamu
                    $update = [];
                    if (is_null($site) && !is_null($shpProj->SiteLink)) {
                        $update['SiteLink'] = $site;
                    } elseif (is_null($shpProj->SiteLink) && !is_null($site)) {
                        $update['SiteLink'] = $site;
                    } elseif (is_null($site) && is_null($shpProj->SiteLink)) {
                        // Do nothing
                    } elseif (trim($site->Url) != trim($shpProj->SiteLink->Url)) {
                        $update['SiteLink'] = $site;
                    }
                    if (trim($obor) != $shpProj->Obor) {
                        $update['Obor'] = $obor;
                    }
                    if (trim($project->dpdruh2) != $shpProj->Product) {
                        $update['Product'] = trim($project->dpdruh2);
                    }
                    if (trim($projname) != $shpProj->Project) {
                        $update['Project'] = trim($projname);
                    }
                    if ($personName != $shpProj->Person) {
                        $update['Person'] = $personName;
                    }
                    if (trim($project->phdesig1) != $shpProj->Title) {
                        $update['Title'] = trim($project->phdesig1);
                    }
                    if (trim($project->phdesig2) != $shpProj->Description) {
                        $update['Description'] = trim($project->phdesig2);
                    }
                    if (trim($project->phcontract) != $shpProj->Contract) {
                        $update['Contract'] = trim($project->phcontract);
                    }
                    //var_dump([$project->phtk, $shpProj->Status]); exit;
                    if (trim($project->phtk) == 'Y' && in_array($shpProj->Status, ['Closed', 'Canceled', 'Preparation'])) {
                        $update['Status'] = 'Active';
                    } elseif (trim($project->phtk) != 'Y' && in_array($shpProj->Status, ['Active', 'Preparation'])) {
                        $update['Status'] = 'Closed';
                    }
                    if (!empty($update)) {
                        /*\Tracy\Debugger::dump($shpProj);
                        \Tracy\Debugger::dump($project);
                        var_dump($update); exit;*/
                        $out = $this->sharepointService->updateProjectProject($shpProj, $update);
                        $counter--;
                        $updates2++;
                        if ($counter <= 0) {
                            break;
                        }
                    }
                }
            }
        }
        $this->template->updates1 = $updates1;
        $this->template->updates2 = $updates2;
    }
    
    public function renderDistributionGroups($name = '_allcp') {
        $this->template->title = $this->translator->translate('Distribuční skupiny') . ' ';
        $this->template->page_title = $this->translator->translate('Distribuční skupiny');
        
        $users = $this->adService->getUsers(true, true);
        $this->template->users = [];
        foreach($users as $user) {
            switch ($name) {
                case '_allcp':
                    //\Tracy\Debugger::dump($user);exit;
                    if ((!$user['disabled']) && $this->adService->hasEmail($user) && ($user['company'] == "CASALE PROJECT a.s.")) {
                        $this->template->users[] = $user;
                    }
                    break;
                default:
                    break;
            }
        }
        //\Tracy\Debugger::dump($this->template->users); exit;
    }
    
    public function renderADExport() {
        $this->template->title = $this->translator->translate('Export AD') . ' ';
        $this->template->page_title = $this->translator->translate('Export AD');
        
        $users = $this->adService->getUsers(true, true);
        $data = [];
        foreach($users as $key => $user) {
            if ($user['type'] == 'user') {
                $data[] = [$key, $user["displayname"], $user["displayname"], $user["title"], $user["department"], $user["company"], $user["physicalDeliveryOfficeName"]];
            }
            if (false && ($key == 'Nemec')) {
                var_dump($user);
                \Tracy\Debugger::dump($user);exit;
            }
        }
        echo 'A';
        $response = new \Ublaboo\Responses\CSVResponse($data, 'users.csv', 'windows-1250', ';');
        $response->send($this->getHttpRequest(), $this->getHttpResponse());
        $this->terminate();
    }
    
    public function renderVendorsImport() {
        $this->template->title = $this->translator->translate('Provedena aktualizace projektů') . ' ';
        $this->template->page_title = $this->translator->translate('Aktualizace porjektů');
        
        $vendors = $this->infosService->getCompanies();
        $counter = 100;
        $updates1 = $updates2 = 0;
        foreach($vendors as $vendor) {
            //\Tracy\Debugger:: dump($vendor);exit;
            $shpVendor = $this->sharepointService->getITVendor(trim($vendor->zeme) . trim($vendor->ico));
            if (empty($shpVendor)) {
                // Založení záznamu
                $record = [
                      'title' => trim($vendor->nazev)
                    , 'abbreviation' => trim($vendor->przkrat)
                    , 'idv' => trim($vendor->zeme) . trim($vendor->ico)
                    , 'country' => trim($vendor->zeme)
                    , 'vat' => trim($vendor->platdan)
                ];
                $out = $this->sharepointService->addITVendor($record);
                //\Tracy\Debugger::dump($out); exit;
                $counter--;
                $updates1++;
                if ($counter <= 0) {
                    break;
                }
            } else {
                // Konrola/aktualizace záznamu
                $update = [];
                if (trim($vendor->przkrat) != $shpVendor->Abbreviation) {
                    $update['Abbreviation'] = trim($vendor->przkrat);
                }
                if (trim($vendor->nazev) != $shpVendor->Title) {
                    $update['Title'] = trim($vendor->nazev);
                }
                if (trim($vendor->zeme) != $shpVendor->Country) {
                    $update['Description'] = trim($vendor->zeme);
                }
                if (trim($vendor->platdan) != $shpVendor->VAT) {
                    $update['VAT'] = trim($vendor->platdan);
                }
                if (!empty($update)) {                
                    $out = $this->sharepointService->updateITVendor($shpVendor, $update);
                    $counter--;
                    $updates2++;
                    if ($counter <= 0) {
                        break;
                    }
                }
            }
        }
        $this->template->updates1 = $updates1;
        $this->template->updates2 = $updates2;
    }
    
    public function renderPVendorsImport() {
        $this->template->title = $this->translator->translate('Provedena aktualizace projektů') . ' ';
        $this->template->page_title = $this->translator->translate('Aktualizace porjektů');
        
        $vendors = $this->infosService->getCompanies();
        $counter = 100;
        $updates1 = $updates2 = 0;
        foreach($vendors as $vendor) {
            //\Tracy\Debugger:: dump($vendor);exit;
            $shpVendor = $this->sharepointService->getProjectVendor(trim($vendor->zeme) . trim($vendor->ico));
            if (empty($shpVendor)) {
                // Založení záznamu
                $record = [
                      'title' => trim($vendor->nazev)
                    , 'abbreviation' => trim($vendor->przkrat)
                    , 'idv' => trim($vendor->zeme) . trim($vendor->ico)
                    , 'country' => trim($vendor->zeme)
                    , 'vat' => trim($vendor->platdan)
                ];
                $out = $this->sharepointService->addProjectVendor($record);
                //\Tracy\Debugger::dump($out); exit;
                $counter--;
                $updates1++;
                if ($counter <= 0) {
                    break;
                }
            } else {
                // Konrola/aktualizace záznamu
                $update = [];
                if (trim($vendor->przkrat) != $shpVendor->Abbreviation) {
                    $update['Abbreviation'] = trim($vendor->przkrat);
                }
                if (trim($vendor->nazev) != $shpVendor->Title) {
                    $update['Title'] = trim($vendor->nazev);
                }
                if (trim($vendor->zeme) != $shpVendor->Country) {
                    $update['Description'] = trim($vendor->zeme);
                }
                if (trim($vendor->platdan) != $shpVendor->VAT) {
                    $update['VAT'] = trim($vendor->platdan);
                }
                if (!empty($update)) {                
                    $out = $this->sharepointService->updateProjectVendor($shpVendor, $update);
                    $counter--;
                    $updates2++;
                    if ($counter <= 0) {
                        break;
                    }
                }
            }
        }
        $this->template->updates1 = $updates1;
        $this->template->updates2 = $updates2;
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

<?php

namespace App\Presenters;

use Nette;



class CronPresenter extends BasePresenter {
    
    
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
     * Inject OKBase service
     * @param \Model\OKBase
     */
    public function injectOKBaseService(\Model\OKBase $OKBaseService) {
        $this->OKBaseService = $OKBaseService;
    }
    
    /** @var \Model\Infos */
    protected $infosService;
    
    /**
     * Inject INFOS service
     * @param \Model\Infos
     */
    public function injectInfosService(\Model\Infos $infosService) {
        $this->infosService = $infosService;
    }
    
    /** @var \jannemec\Soap */
    protected $soapService;

    /**
     * Inject soap service
     * @param \jannemec\Soap
     */
    public function injectSOAPService(\jannemec\Soap $soapService) {
        $this->soapService = $soapService;
    }

    
    
    
    
    /**
     * functions runs before action or render method are taken 
     */
    protected function startup() {
        parent::startup();
        // Vypnout diagnostiku
        \Tracy\Debugger::enable(\Tracy\Debugger::PRODUCTION, WWW_DIR . '/log/cron');
    }
    
    
    public function renderDefault() {
        $this->template->title = $this->translator->translate('Cron') . ' ';
        $this->template->page_title = $this->translator->translate('Cron');
    }
    
    
    public function actionOKBase() {
        \Tracy\Debugger::log('CRON task start: ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        if (false || (date('w') == 7)) { // Only Sunday
            // Provede srovnání zaměstnanců AD x OKBase
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
                        if (trim(mb_strtolower($employee['username']) == trim($val->useridos))) {
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

            // A doplníme ty z INFOSu, které jsme nenalezli
            foreach($this->template->infos as $user) {
                //\Tracy\Debugger::dump($user); exit;
                $employee = new \Dibi\Row([
                          'osoc' => $user['osoc']
                        , 'status' => 'Nenalezen v OKBase - v INFOS'
                        , 'jmeno' => $user->userfull
                        , 'prijmeni' => ''
                        , 'utvar' => $user->utvar
                        , 'email' => ''
                        , 'telefon' => ''
                        , 'username' => $user->useridos
                        ]);
                $this->template->employees[] = $employee;
            }
            
            
            $adActions = [];
            foreach($this->template->employees as $employee) {
                if (!empty($employee['status'])) {
                    switch ($employee['status']) {
                        case 'Nenalezen v OKBase':
                            // Zatím neřešíme
                            break;
                        case 'Not found':
                            if (empty($employee['email'])) {
                                // Neřešíme - nemá email, asi není v AD jako uživatel
                            } else {
                                $adActions[] = ['action' => 'E', 'result' => 'Uživatel ' . $employee['osoc'] . ' '  . $employee['jmeno'] . ' ' . $employee['prijmeni'] .  ' nenalezen v AD'];
                            }
                            break;
                        case 'Nenalezen v OKBase':
                            $adActions[] = ['action' => 'W', 'result' => 'Uživatel ' . $employee['username'] . ' '  . $employee['jmeno'] . ' ' . $employee['prijmeni'] .  ' nenalezen v OKBase'];
                            break;
                        default:
                            $adActions[] = ['action' => 'W', 'result' => 'Uživatel ' . $employee['osoc'] . ' '  . $employee['jmeno'] . ' ' . $employee['prijmeni'] .  ' ' . strtr($employee['status'], ['<br />' => ', '])];
                            break;
                    }
                }
            }
            
            
            if (!empty($adActions)) {
                $mail = new \Nette\Mail\Message;
                $mail->setFrom('jnemec@casaleproject.cz', 'CASALE PROJECT intranet');
                $mail->addTo('jnemec@casaleproject.cz');
                //$mail->addTo('jkovarovicova@casaleproject.cz');
                $subject = "CASALE PROJECT OKBase rozdíly " . date('j.n.Y H:i:s');
                $mail->setSubject($subject);

                $html = '';
                foreach($adActions as $row) {
                    switch ($row['action']) {
                        case 'E':
                            $html .= 'Chyba: ' . $row['result'] . '<br />';
                            break;
                        case 'W':
                            $html .= 'Upozornění: ' . $row['result'] . '<br />';
                            break;
                        defaul:
                            $html .= 'Neznámá akce: ' . $row['action'] . '<br />';
                    }
                } 
                $mail->setHtmlBody($html);
                $mail->setPriority(\Nette\Mail\Message::HIGH);
                $mailer = new \Nette\Mail\SendmailMailer();
                $mailer->send($mail);
                $this->systemService->storeMailLog($subject, $html);
            }
        }
        \Tracy\Debugger::log('CRON task end:   ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $this->terminate();
    }
    
    public function actionVerifyVATs() {
        \Tracy\Debugger::log('CRON task: ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $vats = $this->infosService->getCompaniesForVAT(true);
        $warrnings = array();
        $i = 1;
        foreach($vats as $key => $firm) {
            if (in_array(trim($firm->ico), array(
                ))) {
                // výjimky
                unset($vats[$key]);
            /*} elseif (substr($firmnum, 0, 4) == 'VZOR') {
                // vzoroví zákazníci
                unset($vats[$key]);*/
            } else {
                if (true && ($i != date('w') + 1)) {
                    unset($vats[$key]);
                }
                $i++;
                if ($i > 7) {
                    $i = 1;
                }
            }
        }
        
        //var_dump(count($vats)); exit;
        foreach($vats as $key => $firm) {
            if (strlen(trim($firm->platdph)) == 'N') {
                //$warrnings[$firmnum] = 'VAT nevyplněno ' . trim($firm->OKCUNO) . ', ' . trim($firm->OKCUNM) . '!';
                //Kontrola zrušena
            } elseif (trim($firm->platdan) == '') {
                $warrnings[$key] = 'VAT nevyplněn ' . trim($firm->ico) . ' ' . trim($firm->nazev) . '!';
                //Kontrola zrušena
            } elseif (strlen(trim($firm->platdan)) <= 7) {
                $warrnings[$key] = 'VAT neplatný ' . trim($firm->platdan) . ', ' .  trim($firm->ico) . ' ' . trim($firm->nazev) . '!';
                //Kontrola zrušena
            } elseif (substr($firm->platdan, 0, 2) != $firm->zeme_reg) {
                $warrnings[$key] = 'VAT nezačíná zemí ' . trim($firm->platdan) . ', ' .  trim($firm->ico) . ' ' . trim($firm->nazev) . '!';
            } else {
                //echo '<pre>'; var_dump(array(trim(substr(trim($firm->OKVRNO), 2)), trim(substr(trim($firm->OKVRNO), 0, 2)))); echo '</pre>';
                $cmp = $this->soapService->checkVat(trim(substr(trim($firm->platdan), 2)), trim(substr(trim($firm->platdan), 0, 2)));
                if (!$cmp->valid) {
                    // Vyzkoušíme s doplněnou mezerou
                    $cmp = $this->soapService->checkVat(trim(substr(trim($firm->platdan), 2)) . ' ', trim(substr(trim($firm->platdan), 0, 2)));
                }
                if(!$cmp->valid) {
                    //$warrnings[$firmnum] = 'VAT ' . trim($firm->OKVRNO) . ' neplatný - nenalezen ' . trim($firm->OKCUNO) . ', ' . trim($firm->OKCUNM) . '!';
                    //Kontrola zrušena - systém je často nedostupný ...
                } elseif (substr(trim($cmp->name), 0, strlen('Group registration')) == 'Group registration') {
                    // Cannot be verified - there is only info -Group registration - This VAT ID corresponds to a Group of Taxpayers-
                } elseif (substr(trim($cmp->name), 0, strlen('SA SOC. D\'AFFRETEMENT ET DE TRANSIT')) == 'SA SOC. D\'AFFRETEMENT ET DE TRANSIT') {
                    // Změna společnosti
                } elseif (substr(trim($cmp->name), 0, strlen('SA SAT TRANSPORTS')) == 'SA SAT TRANSPORTS') {
                    // Změna společnosti
                } elseif ((trim($cmp->name) != '---') 
                        && (trim($cmp->name) != trim($firm->nazev))
                        && (trim($cmp->name) != (trim($firm->nazev) . ' ' . trim($firm->nazev)))  // V některých případech se jméno zdvojí v rejstříku
                        && (trim($cmp->name) != (trim($firm->nazev) . ' ' . trim($firm->adresa1)))  // Pro dlouhé firmy se druhá část dává do ulice ...
                        ) {
                    // Ještě tableátory a podobné netisknutelné znaky ...
                    $name = preg_replace("/[\s]+/", " ", $cmp->name);
                    $name = strtr($name, ['&nbsp;' => ' ']);
                    $name = strtr($name, ['  ' => ' ']);
                    $name = strtr($name, ['  ' => ' ']);
                    $uname = strtr($firm->nazev, ['  ' => ' ']);
                    $uname = strtr($uname, ['  ' => ' ']);
                    if (trim($name) != trim($uname)) {
                        $warrnings[$key] = ' VAT ' . trim($firm->platdan) . ' název <b style="white-space: pre;">-' . trim(mb_substr($firm->nazev, 0, 36, 'UTF-8')) . '-</b> neodpovídá - dle rejstříku <b style="white-space: pre;">-' . $cmp->name . '-</b> ' . trim($firm->ico) . ' ' . $firm->chgdat;
                    }
                    //var_dump($warrnings);exit;
                }       
                //echo '<pre>'; var_dump($warrnings); echo '</pre>'; exit;
                if (count($warrnings) >= 100) {
                    break;
                }
            }
        }
        
        if (count($warrnings) != 0) {
            $mail = new \Nette\Mail\Message;
            $mail->setFrom('jnemec@casaleproject.cz', 'CASALE intranet');
            $mail->addTo('jnemec@casaleproject.cz');
            //$mail->addTo('');
            $mail->setSubject("CASALE PROJECT kontrola VAT " . date('j.n.Y H:i:s'));
            $html = implode("<br />\n", $warrnings);
            $mail->setHtmlBody($html);
            $mail->setPriority(\Nette\Mail\Message::HIGH);
            $mailer = new \Nette\Mail\SendmailMailer();
            $mailer->send($mail);
        }
        \Tracy\Debugger::log('CRON task end:   ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $this->terminate();
    }
    
    public function actionImportInfosProjects() {
        \Tracy\Debugger::log('CRON task: ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $projects = $this->infosService->getProjects(false);
        foreach($projects as $project) {
            //\Tracy\Debugger:: dump($project);exit;
            $shpProj = $this->sharepointService->getITProject(trim($project['phproj']));
            if (empty($shpProj)) {
                // Založení záznamu
                $record = [
                      'name' => trim($project->phdesig1)
                    , 'description' => trim($project->phdesig2)
                    , 'pid' => trim($project->phproj)
                    , 'person' => trim($project->userid)
                    , 'contract' => trim($project->phcontract)
                ];
                $out = $this->sharepointService->addITProject($record);
            } else {
                // Konrola/aktualizace záznamu
                $update = [];
                if (trim($project->userid) != $shpProj->Person) {
                    $update['Person'] = trim($project->userid);
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
                if (!empty($update)) {
                    //\Tracy\Debugger::dump($update); exit;
                    $out = $this->sharepointService->updateITProject($shpProj, $update);
                }
            }
        }
        \Tracy\Debugger::log('CRON task end:   ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $this->terminate();
    }

}

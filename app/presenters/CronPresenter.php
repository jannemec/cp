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
        if (false || (date('w') == 6)) { // Only Saturday
            // Provede srovnání zaměstnanců AD x OKBase
            $this->template->employees = [];
            $this->template->users = $this->adService->getUsers(false, true);
            $osocs = [];
        
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
            
            
            $adActions = [];
            foreach($this->template->employees as $employee) {
                if (!empty($employee['status'])) {
                    switch ($employee['status']) {
                        case 'Not found':
                            if (empty($employee['email'])) {
                                // Neřešíme - nemá email, asi není v AD jako uživatel
                            } else {
                                //ještě vypustíme MD - mateřské dovoené
                                if (!in_array(trim($employee['osoc']), ['3839', '5668', '6665', '3817'])) {
                                    $adActions[] = ['action' => 'E', 'result' => 'Uživatel ' . $employee['osoc'] . ' '  . $employee['jmeno'] . ' ' . $employee['prijmeni'] .  ' nenalezen v AD'];
                                }
                            }
                            break;
                        case 'Nenalezen v OKBase':
                            // Zatím neřešíme
                            break;
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
        /*foreach($vats as $key => $firm) {
            if (trim($firm->platdan) != 'CZ27082377') {
                unset($vats[$key]);
            }
        }*/
        foreach($vats as $key => $firm) {
            if (in_array(trim($firm->ico), array(
                ))) {
                // výjimky
                unset($vats[$key]);
//            } elseif (substr($firmnum, 0, 4) == 'VZOR') {
                // vzoroví zákazníci
//                unset($vats[$key]);
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
            //var_dump($firm); exit;
            if (strlen(trim($firm->platdph)) == 'N') {
                //$warrnings[$firmnum] = 'VAT nevyplněno ' . trim($firm->OKCUNO) . ', ' . trim($firm->OKCUNM) . '!';
                //Kontrola zrušena
            } elseif (in_array(trim($firm->zeme), ['RU', 'TR', 'CN', 'CA', 'US', 'AU', 'CH', 'JP', 'NO'])) {
                // Rusko, Turecko, Čína, Canada, USA, Austrálie, Švýcarsko, Japonsko, Norsko
                //Kontrola zrušena - země které nejsou v EU
            } elseif (trim($firm->platdan) == '') {
                $warrnings[$key] = 'VAT nevyplněn ' . trim($firm->ico) . ' ' . trim($firm->nazev) . '!';
                //Kontrola zrušena
            } elseif (strlen(trim($firm->platdan)) <= 7) {
                $warrnings[$key] = 'VAT neplatný ' . trim($firm->platdan) . ', ' .  trim($firm->ico) . ' ' . trim($firm->nazev) . '!';
                //Kontrola zrušena
            } elseif (substr($firm->platdan, 0, 2) != $firm->zeme_reg) {
                $warrnings[$key] = 'VAT nezačíná zemí ' . trim($firm->platdan) . ', ' .  trim($firm->ico) . ' ' . trim($firm->nazev) . ' země ' . trim($firm->zeme) . '!';
            } else {
                //echo '<pre>'; var_dump(array(trim(substr(trim($firm->OKVRNO), 2)), trim(substr(trim($firm->OKVRNO), 0, 2)))); echo '</pre>';
                $cmp = $this->soapService->checkVat(trim(substr(trim($firm->platdan), 2)), trim(substr(trim($firm->platdan), 0, 2)));
                if (!$cmp->valid) {
                    // Vyzkoušíme s doplněnou mezerou
                    $cmp = $this->soapService->checkVat(trim(substr(trim($firm->platdan), 2)) . ' ', trim(substr(trim($firm->platdan), 0, 2)));
                }
                $trimMask = " ()\t\n\r\0\x0B" . '"';
                // Náhrada nepoužitelných znaků ...
                if($cmp->valid && isset($cmp->name)) {
                    $cmp->name = strtr($cmp->name, [
                          '&nbsp;' => ' '
                        , 'Å' => 'A'
                        , 'Å' => 'A'
                        , 'å' => 'a'
                        //, 'Ш' => 'Š'
                        //, 'Л' => 'L'
                        //, 'Б' => 'B'
                        //, 'Ъ' => ''
                        //, 'Г' => 'G'
                        //, 'И' => 'I'
                        //, 'Д' => 'D'
                        , 'ACCACE TAX REPRESENTATION SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ' => 'ACCACE TAX REPRESENTATION Sp. z o.o.'
                        , 'BHDT GmbH' => 'BHDT Best High Pressure Drilling Technology'
                        , '"Brunnbauer-Armaturen" Produktionsges.m.b.H.' => 'Brunnbauer-Armaturen'
                        , 'CÍSAŘ, ČEŠKA, SMUTNÝ s.r.o., advokátní kancel ář' => 'CÍSAŘ, ČEŠKA, SMUTNÝ s.r.o., advokátní kancelář'
                        , 'COMMERZBANK Aktiengesellschaft, pobočka Prah' => 'COMMERZBANK Aktiengesellschaft, pobočka Praha'
                        , 'COMPANIA NAŢIONALĂ PENTRU CONTROLUL CAZANELOR, INSTALAŢIILOR DE RIDICAT ŞI RECIPIENTELOR SUB PRESIUNE - (CNCIR) SA' => 'COMPANIA NATIONALA PENTRU'
                        , 'Ди енд Ес Транс Лоджистикс - ЕООД' => 'D&S TRANS LOGISTICS LTD'
                        , 'Dи енд Ес Транс Lоджистикс - ЕООD' => 'D&S TRANS LOGISTICS LTD'
                        , 'EDUA Company E, s.r.o.' => 'Tutor,s.r.o.'
                        , 'Emerson Automation Solutions Final Control Cz ech s.r.o.' => 'Emerson Automation Solutions Final Control Czech s.r.o.'
                        , 'EXTRACEM KÜLÖNLEGES ÉPITŐANYAGIPARI TERMÉKEKET GYÁRTÓ ÉS FORGALMAZÓ KFT' => 'EXTRACEM Kft.'
                        , 'Hlavní město PRAHA' => 'Základní škola a mateřská škola'
                        , 'ХРАМАР - ЕООД' => 'Hramar LTD'
                        , 'Химкомплект инженеринг - АD' => 'Chimcomplect Engineering - AD'
                        , 'Ing. Jan Moša Ing. Jan Moša - TECHNICKO INŽENÝRSKÁ ČINNOST' => 'Ing. Jan Moša - TECHNICKO INŽENÝRSKÁ ČINNOST'
                        , 'Ing. Jiří Malůšek' => 'Ing. Jiří Malůšek (KANIA patenty)'
                        , 'Ing.Ph.D. Monika Randáková Ing. Monika Randáková - KONTO-SERVIS' => 'Ing. Monika Randáková'
                        , 'Ing. Vojtěch Štibora Ing. Vojtěch Štibora - HILL PRODUCTION' => 'Ing. Vojtěch Štibora - HILL PRODUCTION'
                        , "'INTERGRAPH POLSKA' SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ" => 'INTERGRAPH POLSKA Sp. z o.o.'
                        , 'Jakub Maršíček' => 'Jakub Maršíček - MK SERVIS'
                        , 'Jan Havelka' => 'Jan Havelka -HAVE'
                        , 'Jiří Rákosník Jiří Rákosník- REKLAMNÍ DÍLNA' => 'Jiří Rákosník- REKLAMNÍ DÍLNA'
                        , 'Jiří Šůcha Jiří Šůcha Odborné služby elektrotechnické' => 'Jiří Šůcha'
                        , 'JUDr. Vladimír Zavadil JUDr. Vladimír Zavadil' => 'JUDr. Vladimír Zavadil - advokát'
                        , 'JUDr.Ph.D. Michael Bartončík' => 'JUDr.Ph.D. Michael Bartončík, advokát'
                        , 'JUDr. Michal Voříšek' => 'JUDr. Michal Voříšek - notář v Brně'
                        , 'LEITNER + LEITNER AUDIT KÖNYVVIZSGÁLÓ ÉS TANÁCSADÓ KORLÁTOLT FELELŐSSÉGŰ TÁRSASÁG' => 'LEITNER + LEITNER AUDIT Kft'
                        , 'Luboš Štorkán Luboš Štorkán - ELPOŠ' => 'Luboš Štorkán - ELPOŠ'
                        , 'НЕОХИМ - АД' => 'NEOCHIM - AD'
                        , 'Milan Škoda' => 'Milan Škoda - FOTO'
                        , 'Mgr. Jana Soudková Jana Soudková' => 'Mgr. Jana Soudková'
                        , 'Mgr. Josef Bartončík' => 'Mgr. Josef Bartončík, advokát'
                        , '"MUSAT & ASOCIATII" - SOCIETATE PROFESIONALA DE AVOCATI CU RASPUNDERE LIMITATA' => 'MUSAT & ASOCIATII'
                        , 'QUALI-TOP KERESKEDELMI ES SZOLGALTATO KORLATOLT FELELOSEGU TAR- SASAG' => 'QUALI-TOP'
                        , '-"R.& E.M. SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ"' => 'R.& E.M. Sp. z o.o.'
                        , 'ШЕЛ БЪЛГАРИЯ - ЕАД' => 'SHELL BULGARIA - EAD'
                        , 'S & B PROCONT SRL REPREZENTANT FISCAL PENTRU UTA (UNION TANK ECKSTEIN GMBH&CO.KG' => 'UNION TANK Eckstein GmbH&Co. KG'
                        , 'SHOP-ASSISTANT ÉPÍTŐIPARI ÉS KERESKEDELMI KORLÁTOLT FELELŐSSÉGŰ TÁRSASÁG' => 'SHOP-ASSISTANT ÉPÍTŐIPARI ÉS KERESKEDELMI Kft.'
                        , 'SIL4S TANÚSÍTÓ ÉS SZOLGÁLTATÓ KORLÁTOLT FELELŐSSÉGŰ TÁRSASÁG' => 'SIL4S TANÚSÍTÓ ÉS SZOLGÁLTATÓ  Kft'
                        , 'SMC Industrial Automation CZ s.r.o.-v jazyce českém       SMC Industrial Automation CZ Gmb H. -v jazyce německém' => 'SMC Industrial Automation CZ s.r.o.'
                        , 'SOLARPONT KERESKEDELMI ÉS SZOLGÁLTATÓ KORLÁTOLT FELELŐSSÉGŰ TÁRSASÁG' => 'SOLARPONT KERESKEDELMI ÉS'
                        , 'SPIRAX SARCO, spol. s r.o.,' => 'SPIRAX SARCO, spol. s r.o., organizačná zložka'
                        , 'Svaz strojírenské technologie, zájmové sdruže ní (ve zkratce "SST")' => 'Svaz strojírenské technologie, zájmové sdružení'
                        , 'ТЕХНОЕКСПОРТ СТОРИДЖ - ЕООД' => 'Technoexport Storage Ltd.'
                        , 'TOTÁL-KER SZOLNOK KENŐANYAG KERESKEDELMI KORLÁTOLT FELELŐSSÉGÜ TÁRSASÁG' => 'TOTÁL-KER SZOLNOK Kft.'
                        , 'TŰV SÜD Slovakia s.r.o.' => 'TÜV SÜD Slovakia s.r.o.'
                        , 'Václav Brožík' => 'Václav Brožík - KOVOVÝROBA'
                        , 'VENTURE INDUSTRIES SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ' => 'VENTURE INDUSTRIES Sp. z o.o.'
                        , 'Yokogawa GesmbH' => 'YOKOGAWA REPRESENTATIVE OFFICE'
                        , 'Zibak Wail' => 'Zibak Wail - HOTEL CENTRÁL ZIBAK COMPANY'
                        //, '' => ''
                    ]);
                    $cmp->name2 = strtr($cmp->name, [
                          'AF Chemical Equipment HandelsgmbH' => 'AF Chemical Equipment Handels gmbH'
                        , 'Aleš Bambula' => 'Aleš Bambula - ALMA SERVIS'
                        , 'Ди енд Ес Транс Лоджистикс - ЕООД' => 'D&S TRANS LOGISTICS LTD'
                        , 'JUDr. Vladimír Zavadil' => 'JUDr. Vladimír Zavadil - advokát'
                        , 'František Matoulek' => 'František Matoulek-X-RAY'
                        , 'FLOWSERVE GB LIMITED !! FLOWSERVE FLOW CONTROL' => 'FLOWSERVE GB LIMITED'
                        , 'Химкомплект инженеринг - АД' => 'Chimcomplect Engineering - AD'
                        , 'Ing. Martin Begala MARTIN BEGALA' => 'Ing. Martin Begala'
                        , 'MAKS-D, s.r.o. - odštěpný závod zahranič ávnické osoby' => 'MAKS-D, s.r.o. - odštěpný závod zahraniční právnické osoby'
                        , 'MITROIU C. ?TEFAN PERSOANĂ FIZICĂ AUTORIZATĂ' => 'MITROIU C. STEFAN PERSOANĂ FIZICĂ'
                        , 'НЕОХIМ - АD' => 'NEOCHIM - AD'
                        , 'KORLÁTOLT FELELŐSSÉGŰ TÁRSASÁG' => 'Kft'
                        , 'SCP DENIS CALIPPE, THIERRY CORBEAUX ET E' => 'SCP Denis CALIPPE et Thierry CORBEAUX'
                        , 'SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ SPÓŁKA KOMANDYTOWA' => 'sp. z o.o.'
                        , 'ТЕХНОЕКСПОРТ СТОРИДЖ - ЕООД' => 'Technoexport Storage Ltd.'
                        , '"R.& E.M. SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ"' => 'R.& E.M. Sp. z o.o.'
                    ]
                            );
                }
                //echo '<pre>'; var_dump($cmp); echo '</pre>';
                //echo '<pre>'; var_dump($firm);  echo '</pre>';exit;
                if(!$cmp->valid) {
                    //$warrnings[$firmnum] = 'VAT ' . trim($firm->OKVRNO) . ' neplatný - nenalezen ' . trim($firm->OKCUNO) . ', ' . trim($firm->OKCUNM) . '!';
                    //Kontrola zrušena - systém je často nedostupný ...
                } elseif (substr(trim($cmp->name, $trimMask), 0, strlen('Group registration')) == 'Group registration') {
                    // Cannot be verified - there is only info -Group registration - This VAT ID corresponds to a Group of Taxpayers-
                } elseif (substr(trim($cmp->name, $trimMask), 0, strlen('Áfa csoport / VAT Group')) == 'Áfa csoport / VAT Group') {
                    // Cannot be verified - there is only info 
                } elseif (substr(trim($cmp->name, $trimMask), 0, strlen('SA SOC. D\'AFFRETEMENT ET DE TRANSIT')) == 'SA SOC. D\'AFFRETEMENT ET DE TRANSIT') {
                    // Změna společnosti
                } elseif (substr(trim($cmp->name, $trimMask), 0, strlen('SA SAT TRANSPORTS')) == 'SA SAT TRANSPORTS') {
                    // Změna společnosti
                } elseif ((trim($cmp->name, $trimMask) != '---') 
                        && (trim($cmp->name, $trimMask) != trim($firm->nazev, $trimMask))
                        && (trim($cmp->name2, $trimMask) != trim($firm->nazev, $trimMask))
                        && (trim($cmp->name, $trimMask) != (trim($firm->nazev, $trimMask) . ' ' . trim($firm->nazev, $trimMask)))  // V některých případech se jméno zdvojí v rejstříku
                        && (trim($cmp->name, $trimMask) != (trim($firm->nazev, $trimMask) . ' ' . trim($firm->adresa1, $trimMask)))  // Pro dlouhé firmy se druhá část dává do ulice ...
                        && (trim($cmp->name, $trimMask) != (trim($firm->nazev, $trimMask) . trim($firm->adresa1, $trimMask)))  // Pro dlouhé firmy se druhá část dává do ulice
                        && (trim($cmp->name, $trimMask) != (trim($firm->nazev, $trimMask) . ' - ' . trim($firm->adresa1, $trimMask)))  // Pro dlouhé firmy se druhá část dává do ulice
                        ) {
                    // Ještě tabelátory a podobné netisknutelné znaky ...
                    $name = preg_replace("/[\s]+/", " ", $cmp->name);
                    $name = strtr($name, ['&nbsp;' => ' ']);
                    // Unprintable char 194
                    for($i = 0; $i < strlen($name); $i++) {
                        if (ord(substr($name, $i)) == 194) {
                            $name = substr($name, 0, $i) . ' ' . substr($name, $i + 1);
                        }
                    }
                    $name = strtr($name, ['  ' => ' ']);
                    $name = strtr($name, ['  ' => ' ']);
                    $uname = strtr($firm->nazev, ['  ' => ' ']);
                    $uname = strtr($uname, ['  ' => ' ']);
                    $u2name = strtr($firm->nazev . ' ' . trim($firm->adresa1, $trimMask), ['  ' => ' ']);
                    $u2name = strtr($u2name, ['  ' => ' ']);
                    /*for($i = 0; $i < strlen($name); $i++) {
                        echo ord(substr($name, $i, 1)) . '<br />';
                    }
                    var_dump($name); var_dump($uname);exit;*/
                    if (trim($name, $trimMask) == trim($uname, $trimMask)) {
                        // O.K.
                    } elseif (substr(trim($name, $trimMask), -strlen(trim($uname, $trimMask))) == trim($uname, $trimMask)) {
                        // O.K. - srovnání konce - VAT názvy, kde se první část duplikuje
                    } elseif (substr(trim($name, $trimMask), -strlen(trim($u2name, $trimMask))) == trim($u2name, $trimMask)) {
                        // O.K. - srovnání konce pro dva řádky - VAT názvy, kde se první část duplikuje
                    } else {
                        $warrnings[$key] = ' VAT ' . trim($firm->platdan) . ' název <b style="white-space: pre;">-' . trim($firm->nazev) . '-</b> neodpovídá - dle rejstříku <b style="white-space: pre;">-' . $cmp->name . '-</b> ' . trim($firm->ico) . ' ' . $firm->chgdat;
                    }
                    //var_dump($warrnings);exit;
                }       
                //echo '<pre>'; var_dump($warrnings); echo '</pre>'; exit;
                if (count($warrnings) >= 100) {
                    break;
                }
            }
        }
        //echo '<pre>'; var_dump($warrnings); echo '</pre>'; exit;
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
        $counter = 5000;
        
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
                        //, 'status' => trim($project->phtk) == 'Y' ? 'Active' : 'Closed'
                        , 'status' => (trim($project->phtk) == 'Y' && is_null($project->phkukon)) ? 'Active' : 'Closed'
                        , 'product' => trim($project->dpdruh2)
                        , 'obor' => $obor
                        , 'sitelink' => $site
                    ];
                    $out = $this->sharepointService->addITProject($record);
                    $counter--;
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
                    //if (trim($project->phtk) == 'Y' && in_array($shpProj->Status, ['Closed', 'Canceled', 'Preparation'])) {
                    if ((trim($project->phtk) == 'Y' && is_null($project->phkukon)) && in_array($shpProj->Status, ['Closed', 'Canceled', 'Preparation'])) {
                        $update['Status'] = 'Active';
                    //} elseif (trim($project->phtk) != 'Y' && in_array($shpProj->Status, ['Active', 'Preparation'])) {
                    } elseif (((trim($project->phtk) != 'Y') || !is_null($project->phkukon)) && in_array($shpProj->Status, ['Active', 'Preparation'])) {
                        $update['Status'] = 'Closed';
                    }
                    if (!empty($update)) {
                        /*\Tracy\Debugger::dump($shpProj);
                        \Tracy\Debugger::dump($project);
                        var_dump($update); exit;*/
                        $out = $this->sharepointService->updateITProject($shpProj, $update);
                        $counter--;
                        if ($counter <= 0) {
                            break;
                        }
                    }
                }
            }
        }
        \Tracy\Debugger::log('CRON task end:   ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $this->terminate();
    }
    
    public function actionImportInfosPProjects() {
        \Tracy\Debugger::log('CRON task: ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $projects = $this->infosService->getProjects(false);
        $counter = 5000;
        
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
                        //, 'status' => trim($project->phtk) == 'Y' ? 'Active' : 'Closed'
                        , 'status' => (trim($project->phtk) == 'Y' && is_null($project->phkukon)) ? 'Active' : 'Closed'
                        , 'product' => trim($project->dpdruh2)
                        , 'obor' => $obor
                        , 'sitelink' => $site
                    ];
                    $out = $this->sharepointService->addProjectProject($record);
                    $counter--;
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
                    //if (trim($project->phtk) == 'Y' && in_array($shpProj->Status, ['Closed', 'Canceled', 'Preparation'])) {
                    if ((trim($project->phtk) == 'Y' && is_null($project->phkukon)) && in_array($shpProj->Status, ['Closed', 'Canceled', 'Preparation'])) {
                        $update['Status'] = 'Active';
                    //} elseif (trim($project->phtk) != 'Y' && in_array($shpProj->Status, ['Active', 'Preparation'])) {
                    } elseif (((trim($project->phtk) != 'Y') || !is_null($project->phkukon)) && in_array($shpProj->Status, ['Active', 'Preparation'])) {
                        $update['Status'] = 'Closed';
                    }
                    if (!empty($update)) {
                        /*\Tracy\Debugger::dump($shpProj);
                        \Tracy\Debugger::dump($project);
                        var_dump($update); exit;*/
                        $out = $this->sharepointService->updateProjectProject($shpProj, $update);
                        $counter--;
                        if ($counter <= 0) {
                            break;
                        }
                    }
                }
            }
        }
        \Tracy\Debugger::log('CRON task end:   ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $this->terminate();
    }
    
    public function actionImportInfosVendors() {
        \Tracy\Debugger::log('CRON task: ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $vendors = $this->infosService->getCompanies(false);
        $counter = 5000;
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
                    $update['Country'] = trim($vendor->zeme);
                }
                if (trim($vendor->platdan) != $shpVendor->VAT) {
                    $update['VAT'] = trim($vendor->platdan);
                }
                
                if (!empty($update)) {
                    
                    $out = $this->sharepointService->updateITVendor($shpVendor, $update);
                    //\Tracy\Debugger::dump($out);
                    //\Tracy\Debugger::dump($update);
                    //\Tracy\Debugger::dump($shpProj); exit;
                    $counter--;
                    if ($counter <= 0) {
                        break;
                    }
                }
            }
        }
        \Tracy\Debugger::log('CRON task end:   ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $this->terminate();
    }
    
    
    
    public function actionImportInfosPVendors() {
        \Tracy\Debugger::log('CRON task: ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $vendors = $this->infosService->getCompanies(false);
        /**foreach($vendors as $vendor) {
            if (trim($vendor->ico) == '27273725') {
                echo 'A'; exit;
            }
        }
        echo 'N'; exit;*/
        $counter = 5000;
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
                \Tracy\Debugger::log('CRON vendor add ' . trim($vendor->zeme) . trim($vendor->ico), \Tracy\Debugger::INFO);
                //\Tracy\Debugger::dump($out); exit;
                $counter--;
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
                    $update['Country'] = trim($vendor->zeme);
                }
                if (trim($vendor->platdan) != $shpVendor->VAT) {
                    $update['VAT'] = trim($vendor->platdan);
                }
                
                if (!empty($update)) {
                    
                    $out = $this->sharepointService->updateProjectVendor($shpVendor, $update);
                    \Tracy\Debugger::log('CRON vendor update ' . trim($vendor->zeme) . trim($vendor->ico), \Tracy\Debugger::INFO);
                    //\Tracy\Debugger::dump($out);
                    //\Tracy\Debugger::dump($update);
                    //\Tracy\Debugger::dump($shpProj); exit;
                    $counter--;
                    if ($counter <= 0) {
                        break;
                    }
                }
            }
        }
        \Tracy\Debugger::log('CRON task end:   ' . date('YmdHis') . ' ' . $this->getView(), \Tracy\Debugger::INFO);
        $this->terminate();
    }

}

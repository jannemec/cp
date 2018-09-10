<?php
namespace App;
use \h4kuna\Gettext\InjectTranslator;
/**
 * Base class for all application presenters.
 *
 * @author     Jan Němec
 * @package    cscz.biz
 */
abstract class BasePresenter extends \Nette\Application\UI\Presenter {

    public $oldLayoutMode = FALSE;

    
    /** @var \jannemec\UserRight */
    public $userRightService;

    /**
     * Inject userright service
     * @param \jannemec\UserRight
     */
    public function injectUserRightService(\jannemec\UserRight $userRightService) {
        $this->userRightService = $userRightService;
    }

   /** 
    * @var \model\M3\M3Facade 
    */
    protected $m3Service;
    
    /**
     * Inject rockwell service
     * @param \model\Rockwell
     */
    public function injectM3Service(\model\M3\M3Facade $m3Service) {
        $this->m3Service = $m3Service;
    }
    
    public function getM3Service(): \model\M3\M3Facade {
        return($this->m3Service);
    }
    
    /**
     * Items of the menu [$link => [name, description], ...]
     * @var Array
     */
    protected $menu = array();

    /*
     * @Persistent
     * Logged user
     */
    public $user;
    public $sysUser = null;
    /** \Nette\Caching\Cache */
    protected $cache = null;
    /**
     * Inject rockwell service
     * @param \model\Rockwell
     */
    public function injectCache(\Nette\Caching\Cache $cache) {
        $this->cache = $cache;
    }
    
    public function getCache(): \Nette\Caching\Cache {
        return($this->cache);
    }
    

    /*
     * @Persistent
     * Link to get back
     */
    public $backlink = null;
    
    public $freePage = false;
    public function isFreePage() {
        return($this->freePage);
    }

    public function getUsername() {
        $user = $this->getUser();
        if ($user instanceOf \Nette\Security\User) {
            $identity = $user->getIdentity();
            
            if ($identity instanceOf \Nette\Security\Identity) {
                $data = $identity->getData();
                return(isset($data['username']) ? $data['username'] : 'guest');
            }
        }
        return('guest');
    }
    
    public function getUserFullname() {
        $user = $this->getUser();
        if ($user instanceOf \Nette\Security\User) {
            $identity = $user->getIdentity();
            
            if ($identity instanceOf \Nette\Security\Identity) {
                $data = $identity->getData();
                return(isset($data['user']['name']) ? $data['user']['name'] : 'guest');
            }
        }
        return('guest');
    }
    
    public function getM3Username(bool $full = false) {
        $username = $this->getUsername();
        if (strtolower(substr($username, 0, 1)) == 'u') {
            return(($full ? 'U' : '') . substr($username, 1));
        } elseif ($username == 'guest') {
            return('');
        } else {
            return($username);
        }
    }
    
    public function getUserEmno() {
        $username = $this->getUsername();
        if (strtolower(substr($username, 0, 1)) == 'u') {
            return(substr($username, 1));
        } elseif ($username == 'guest') {
            return('');
        } else {
            return($username);
        }
    }
    
    protected function defaultAjaxInvalidate() {
        $this->redrawControl(null, false);
        $this->redrawControl('contentSnippet');
        $this->redrawControl('title');
        $this->redrawControl('pageTitleSnippet');
        $this->redrawControl('topMenuSnippet');
    }
    
    public function getUserEmail() {
        $adUser = $this->adService->getUser($this->getUsername());
        if (isset($adUser['mail'])) {
            return($adUser['mail']);
        }
        $user = $this->getUser();
        if ($user instanceOf \Nette\Security\User) {
            $identity = $user->getIdentity();
            if ($identity instanceOf \Nette\Security\Identity) {
                $data = $identity->getData();
                return(isset($data['username']) ? ($data['username'] . '@otk.cz') : 'intranet@otk.cz');
            }
        }
        return('intranet@otk.cz');
    }
    
    public function getUserPhone() {
        $adUser = $this->adService->getUser($this->getUsername());
        return(isset($adUser['telephoneNumber']) ? $adUser['telephoneNumber'] : '');
    }
    
    public function getUserMobile() {
        $adUser = $this->adService->getUser($this->getUsername());
        return(isset($adUser['mobile']) ? $adUser['mobile'] : '');
    }

    public function setLang($lang) {
        $this->lang = $lang;
        // Uložení do session
        $section = $this->session->getSection('default');
        $section->myLang = $this->lang;
    }

    public function getLang() {
        if (is_null($this->lang)) {
            // Není - musíme jít do cache
            $section = $this->session->getSection('default');
            $this->lang = isset($section->myLang) ? $section->myLang : null;
            if (is_null($this->lang)) {
                // Nenalezli jsme - vezmeme default z konfigu
                $params = $this->context->getParameters();
                $this->lang = isset($params['lang']) ? $params['lang'] : 'cs';
            }
        }
        return($this->lang);
    }

    public function isLogged() {
        if ($this->user instanceOf NUser) {
            return($this->user->isLoggedIn());
        } else {
            return(false);
        }
    }

    /** @persistent */
    public $lang;

    
    
    /** @var \model\System */
    protected $systemService = null;

    /**
     * Inject system
     * @param $systemService \model\System
     */
    public function injectSystemService(\model\System $systemService) {
        $this->systemService = $systemService;
    }
    
    public function getAdService() :\Adldap\AD {
        return($this->adService);
    }
    
    /** @var \\Adldap\AD */
    protected $adService;

    /**
     * Inject AD service
     * @param \model\Rockwell
     */
    public function injectADService(\Adldap\AD $adService) {
        $this->adService = $adService;
    }
    
    /** @var \model\EskoService */
    protected $eskoService;

    /**
     * Inject rockwell service
     * @param \model\Rockwell
     */
    public function injectEskoService(\model\EskoService $eskoService) {
        $this->eskoService = $eskoService;
    }    
    /** @var \h4kuna\Gettext\GettextSetup */
    protected $translator = null;

    /**
     * Inject translator
     * @param $translator \h4kuna\Gettext\GettextSetup
     */
    public function injectTranslator(\h4kuna\Gettext\GettextSetup $translator) {
        $this->translator = $translator;
    }
    
    /** 
     * 
     * @return \h4kuna\Gettext\GettextSetup 
     */
    public function getTranslator() {
        return($this->translator);
    }

    
    public function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        // if not set, the default language will be used
        if (!isset($this->lang)) {
            $this->lang = $this->translator->getLanguage();
        } else {
            if (!in_array($this->lang, ['cs', 'en', 'de'])) {
                $this->lang = 'cs';
            }
            $this->translator->setLanguage($this->lang);
        }
        $template->setTranslator($this->translator);
        return $template;
    }

    protected function startup() {
        parent::startup();
        $this->lang = $this->translator->setLanguage($this->lang);
        
        // Enable Nette Debugger for error visualisation & logging
        \Tracy\Debugger::enable(\Tracy\Debugger::DETECT, WWW_DIR . '/log');
        \Tracy\Debugger::$strictMode = TRUE;

        // Login via server - windows authentication
        if (!$this->getUser()->isLoggedIn()) {
            // Uživatel není ověřen - zkusíme NTLM
            if (isset($_SERVER['REMOTE_USER']) && ($_SERVER['REMOTE_USER'] != '')) {
                //autorizujeme
                $remoteUser = $_SERVER['REMOTE_USER'];
                try {
                    $this->getUser()->login($remoteUser, null);
                    \Tracy\Debugger::log('Logged user:' . $remoteUser, \Tracy\Debugger::INFO);
                } catch (\Nette\Security\AuthenticationException $e) {
                    // Přihlášení neproběhlo
                    \Tracy\Debugger::log('Unable to login user:' . $remoteUser, \Tracy\Debugger::WARNING);
                }
            } /*else {
                if (isset($_REQUEST['username'])) {
                        try {
                            $this->getUser()->login($_REQUEST['username'], $_REQUEST['password']);
                        } catch (\Nette\Security\AuthenticationException $e) {
                            // Přihlášení neproběhlo
                        }
                }
            }*/
            // Zkusíme cli - pro cron
            if (!$this->getUser()->isLoggedIn()) {
                $params = $this->getRequest()->getParameters();
                if (isset($params['cliCode']) && (($params['cliCode'] == 'otk2016') || ($params['cliCode'] == 'api'))) {
                    $this->getUser()->login('cron', null);
                }
            }
        }
        

        $this->user = $this->getUser();

        if ($this->user->isLoggedIn()) {
            $tmp = $this->user->getIdentity()->getData();
            $this->sysUser = $tmp['user'];
            if ($this->user->isAllowed('Admin')) {
                \Tracy\Debugger::enable(\Tracy\Debugger::DETECT, WWW_DIR . '/log');
            } else {
                \Tracy\Debugger::enable(\Tracy\Debugger::PRODUCTION, WWW_DIR . '/log');
            }
        } else {
            $this->sysUser = null;
            \Tracy\Debugger::enable(\Tracy\Debugger::PRODUCTION, WWW_DIR . '/log');
        }

        if (!$this->isFreePage()
                && (($this->name != 'Home') && ($this->name != 'Error') && !$this->user->isAllowed($this->name, $this->view) && !$this->user->isAllowed('Admin')
)) {
            \Tracy\Debugger::log('Unallowed access: ' . $this->getHttpRequest()->getUrl()  . '-' . $this->getUsername(), \Tracy\Debugger::WARNING);
            $this->redirect('Home:');
        } else {
            \Tracy\Debugger::log('Access: ' . $this->getHttpRequest()->getUrl()  . '-' . $this->getUsername(), \Tracy\Debugger::INFO);
        }
    }

    protected function beforeRender() {
        $this->menu = array();

        $this->template->translator = $this->translator;
        $this->template->setTranslator($this->translator);

        $this->template->baseUri = BASE_URI;
    }
   
    public function actionSetLang($lang = null, $backlink = null) {
        $this->setLang($lang);
        if (is_null($backlink)) {
            $this->redirect('default');
        } else {
            $this->redirect($backlink);
        }
    }
    
    /** @var \WebLoader\Nette\LoaderFactory @inject */
    public $webLoader;

    /** @return CssLoader */
    protected function createComponentCss() {
        return($this->webLoader->createCssLoader($this, 'css'));
    }
    
    /** @return CssLoader */
    protected function createComponentCssPrint() {
        return($this->webLoader->createCssLoader($this, 'cssPrint'));
    }
    
    /** @return CssLoader */
    protected function createComponentCssOld() {
        return($this->webLoader->createCssLoader($this, 'cssOld'));
    }
    
    /** @return CssLoader */
    protected function createComponentJs() {
        return($this->webLoader->createJavaScriptLoader($this, 'js'));
    }
    
    /** @return JsLoader */
    protected function createComponentJQuery() {
        return $this->webLoader->createJavaScriptLoader($this, 'jQuery');
    }
    
    protected function createComponentLoginForm() {
        return(new \controls\LoginForm($this, 'loginForm'));
    }
    
    protected function createComponentTopMenu() {
        return(new \controls\TopMenu($this, 'topMenu'));
    }
    
    protected function createComponentFromToSelect() {
        return(new \controls\FromToSelect($this, 'fromToSelect'));
    }
    
    protected function createComponentYearSelect() {
        return(new \controls\YearSelect($this, 'yearSelect'));
    }
    
    protected function createComponentYearMonthSelect() {
        return(new \controls\YearMonthSelect($this, 'yearMonthSelect'));
    }
    
    protected function createComponentDateSelect() {
        return(new \controls\DateSelect($this, 'dateSelect'));
    }
    
    protected function createComponentDiviMonthSelect() {
        return(new \controls\DiviMonthSelect($this, 'diviMonthSelect'));
    }
    
    protected function createComponentGDPRLetter() {
        return(new \controls\GDPRLetter($this, 'gDPRLetter'));
    }
    
    protected function createComponentDiviDateSelect() {
        return(new \controls\DiviDateSelect($this, 'diviDateSelect'));
    }
    
    protected function createComponentDiviIntervalSelect() {
        return(new \controls\DiviIntervalSelect($this, 'diviIntervalSelect'));
    }
    
    protected function createComponentIntervalSelect() {
        return(new \controls\IntervalSelect($this, 'intervalSelect'));
    }
    
    protected function createComponentDiviIntervalAllSelect() {
        return(new \controls\DiviIntervalAllSelect($this, 'diviIntervalAllSelect'));
    }
    
    public function createComponentMotivySimpleGrid() {
        if($this->getRequest()->getParameter('cuno')) {
            return(new \controls\MotivySimpleGrid($this, 'motivySimpleGrid', $this->m3Service, $this->eskoService, $this->getRequest()->getParameter('cuno'))); 
        } else {
            return(new \controls\MotivySimpleGrid($this, 'motivySimpleGrid', $this->m3Service, $this->eskoService)); 
        }
    }
    
    public function createComponentStoreItemSelect() {
        return(new \controls\StoreItemSelect($this, 'storeItemSelect'));
    }
    
    public function createComponentMfnoDOSelect() {
        return(new \controls\MfnoDOSelect($this, 'mfnoDOSelect'));
    }
    
    public function createComponentCunoSelect() {
        return(new \controls\CunoSelect($this, 'cunoSelect'));
    }
    
    public function handleSetParam(string $type, string $key, string $name, string $value) {
        echo $this->systemService->setParam($type, $key, $name, $value, $this->getUsername());
        $this->terminate();
    }
    
    public function handleSetEMNOVALUE(string $cono, string $divi, string $emno, string $name, string $val) {
        $result = $this->m3Service->M3APICRS530MIUpdBasicData(
                ['CONO' => $cono, 'DIVI' => $divi, 'EMNO' => $emno, 'FACI' => $this->m3Service->getFaci(), $name=>$val]
                );
        if (isset($result['statusCode']) && ($result['statusCode'] == 200)) {
            echo 1;
        } else {
            echo isset($result['reasonPhrase']) ? $result['reasonPhrase'] : 'API Error';
        }
        $this->terminate();
    }
    
    public function handleMotivPDF($Prodid, $Cusid) {
        //echo 'file://' . strtr($this->eskoService->getUrlPdf(), array('\\' => '/')) . '/Z=' . $Cusid . '/' . $Prodid . '.pdf'; exit;
        $this->redirectUrl('file://' . strtr($this->eskoService->getUrlPdf(), array('\\' => '/')) . '/Z=' . $Cusid . '/' . $Prodid . '.pdf');
        //$this->terminate();
    }
    
    public function renderMotivePdf(string $url) {
        $fl = explode('\\', strtr($url, array('/' => '\\')));
        $filename = array_pop($fl);
        $filepath = implode('\\' , $fl);           
        $shell = new \COM('WScript.Network');
        $connect = true;
        //var_dump([$url, $filename, $filepath, $this->eskoService->getUrlPdf()]); exit;
        foreach($drives = $shell->EnumNetworkDrives() as $key => $val) {
            if ((($key % 2) == 0) && ($val == 'Z')) {
                if ($drives[$key + 1] == $this->eskoService->getUrlPdf()) {
                    $connect = false;
                } else {
                    try {
                        $shell->RemoveNetworkDrive("Z:");
                    } catch (\com_exception $r) { }
                }
            }
        }
        if ($connect) {
            try {
                $shell->MapNetworkDrive("Z:", $this->eskoService->getUrlPdf(), true, $this->eskoService->getUsername(), $this->eskoService->getPassword());
            } catch (\com_exception $r) {}
        }
        
        $fullFileName = strtr($url, ['/' => '\\']);
        $fullFileName = 'Z:' . strtr($fullFileName, [$this->eskoService->getUrlPdf() => '\\']);
        //echo $fullFileName; exit;
        if (file_exists($fullFileName)) {
            $response = new \Nette\Application\Responses\FileResponse($fullFileName, null, null, true);
            $this->sendResponse($response);
            $shell = null;
            $this->terminate();
        }
        $this->flashMessage('Autorka nenalezena', 'error');
        $this->setView('empty');
    }
    public function renderMotiveImage(string $url, string $maxSize = '') {
        $fl = explode('\\', strtr($url, array('/' => '\\')));
        $filename = array_pop($fl);
        $filepath = implode('\\' , $fl);           
        $myurl = WWW_DIR . '\\esko\\' . $filename;
        if (!file_exists($myurl)) {
            // Zkusíme ho zkopírovat
            
            $shell = new \COM('WScript.Network');
            $connect = true;
            foreach($drives = $shell->EnumNetworkDrives() as $key => $val) {
                if ((($key % 2) == 0) && ($val == 'X')) {
                    if ($drives[$key + 1] == $this->eskoService->getUrl()) {
                        $connect = false;
                    } else {
                        try {
                            $shell->RemoveNetworkDrive("X:");
                        } catch (\com_exception $r) { }
                    }
                }
            }
            if ($connect) {
                try {
                    $shell->MapNetworkDrive("X:", $this->eskoService->getUrl(), true, $this->eskoService->getUsername(), $this->eskoService->getPassword());
                } catch (\com_exception $r) {}
            }
            
            $shorturl = substr(strtr($url, ['/' => '\\']), strlen($this->eskoService->getUrl()));
            //var_dump([$this->eskoService->getUrl(), 'X:\\' . $shorturl, $this->eskoService->getUrl(), $url]); exit;
            if (file_exists('X:\\' . $shorturl)) {
                copy('X:\\' . $shorturl, $myurl);
            }
            $im = @imagecreatefromjpeg($myurl);
             
            /*
            $path = $this->eskoService->getUrl();
            $batchname = WWW_DIR . '\\esko\\..\\batch\\esko.bat';

            //echo "cmd /C " . $batchname . ' "' . $path . '" "' . strtr($url, array('/' => '\\')) . '"'; exit;
            // Volání batch soubory
            $WshShell = new \COM("WScript.Shell"); 
            $oExec = $WshShell->Run("cmd /C " . $batchname . ' "' . $path . '" "' . strtr($url, array('/' => '\\')) . '"', 0, true);
            $im = @imagecreatefromjpeg($myurl);
            */
            if(!$im) {
                /* Create a black image */
                if (!empty($maxSize)) {
                    $im = imagecreatetruecolor(intval($maxSize), 30);
                } else {
                    $im  = imagecreatetruecolor(450, 30);
                }
                $bgc = imagecolorallocate($im, 255, 255, 255);
                $tc  = imagecolorallocate($im, 0, 0, 0);
                if (!empty($maxSize)) {
                    imagefilledrectangle($im, 0, 0, intval($maxSize), 30, $bgc);
                } else {
                    imagefilledrectangle($im, 0, 0, 450, 30, $bgc);
                }
                /* Output an error message */
                //imagestring($im, 1, 5, 5, 'Error loading ' . $myurl, $tc);
                $shorturl = substr(strtr($url, ['/' => '\\']), strlen($this->eskoService->getUrl()));
                imagestring($im, 1, 5, 5, 'ERR ' . $filename, $tc);
                $this->getHttpResponse()->setExpiration('+ 1 hours');
                header('Content-Type: image/jpeg');
                imagejpeg($im);
                imagedestroy($im);
            } else {
                // vytištění obsahu dokumentu
                if (!empty($maxSize) && (intval($maxSize) > 0)) {
                    list($width, $height) = getimagesize($myurl);
                    if (($width >= $height) || (intval($height) <= 0)) {
                        $im = imagescale($im, intval($maxSize), -1);
                    } else {
                        $im = imagescale($im, round(intval($maxSize) * $width/$height), intval($maxSize));
                    }
                } else {
                    $im = imagescale($im, 450, -1);
                }
                imagejpeg($im, $myurl);
                $this->getHttpResponse()->setExpiration('+ 1 hours');
                header('Content-Type: image/jpeg');
                imagejpeg($im);
                imagedestroy($im);
            }
        } else {
            $this->getHttpResponse()->setExpiration('+ 1 hours');
            header('Content-Type: image/jpeg');
            echo file_get_contents($myurl);
        }
        $this->terminate();
    }
    
    public function createComponentOdpisMWOMAT() {
        $frm = new \Nette\Application\UI\Form();

        $default = array(
            'cono' => $this->m3Service->getCono()
            , 'faci' => $this->m3Service->getFaci()
            , 'rudi' => '02P'
            , 'whlo' => '14'
            , 'rpdt' => date('Ymd')
            , 'rptm' => date('H') . (date('j') < 15 ? '00' : (date('j') < 30 ? '15' : (date('j') < 45 ? '30' : '45'))) . '00'
        );
        
        $frm->getElementPrototype()->class('pure-form pure-form-aligned');
        $frm->addHidden('reload');
        $frm->addHidden('cono');
        $frm->addHidden('faci');
        $frm->addHidden('opno');
        $frm->addHidden('prno');
        $frm->addText('mtno')->getControlPrototype()->addAttributes(['readonly' =>'readonly']);
        $frm->addHidden('whlo');
        $frm->addHidden('createLine');
        
        $frm->addHidden('timestamp');
        $frm->addText('mfno', 'DO')->setRequired()->getControlPrototype()->addAttributes(['readonly' =>'readonly']);
        $frm->addText('itds', 'Materiál')->getControlPrototype()->addAttributes(['readonly' =>'readonly']);
        $frm->addText('rpqa', 'Množství:')->setRequired()->getControlPrototype()->addClass('nr');
        $frm->addDependentSelectBox('bano', 'Dávka', $frm['mtno'], $frm['whlo'])->setDependentCallback(function ($values) {
            $data =  new \NasExt\Forms\DependentData();
            $items = [];
            
            $first = null;
            // foreach($this->m3Service->getItnoBanos($values['mtno'], $values['whlo'], true) as $val) { // V této varinatě hledá na všech skladech
            foreach($this->m3Service->getItnoBanos($values['mtno'], $values['whlo'], false) as $val) {
                $items[trim($val->MLWHLO). '||' . trim($val->MLWHSL). '||' . trim($val->MLBANO)] = trim($val->MLBANO) . ' (' . number_format($val->MLSTQT - $val->MLALQT, 3, ',', '`') . ' ' . trim($val->MMUNMS) . ') - ' . trim($val->MLWHLO)
                        . ' ' . trim($val->MLWHSL);
                if (!$first) {
                    $first = trim($val->MLBANO);
                }
            }
            $data->setItems($items)->setPrompt('---');
            //$data->setPrompt('--- Vyber ---');
            if (!is_null($first)) {
                $data->setValue($first);
            }
            return($data);
        })/*->setPrompt('--- Vyber ---')*/->setRequired()->setHtmlId('overViewVydej_emno');
        $frm->addText('maxrpqa', 'Zbývá:')->getControlPrototype()->addClass('nr')->addAttributes(['readonly' =>'readonly']);
        $frm->addCheckBox('overProduction', 'Odepisuji více než je plánováno');
        $frm['rpqa']->addRule(\Nette\Application\UI\Form::FLOAT, 'Počet hodin musí být číslo');
        /*$frm['rpqa']->addConditionOn($frm['overProduction'], Form::EQUAL, false)
                ->addRule(\Nette\Application\UI\Form::FLOAT, 'Počet hodin musí být číslo');*/
        $frm->addCheckbox('rend', 'Ukončit:');//->getControlPrototype()->style('float: left;');

        $frm->addSubmit('sbmt', 'Odepsat')->getControlPrototype()->class('button-xsmall pure-button ajax');
        
        $frm->onSuccess[] = [$this, 'setOperationIssue'];
        
        $renderer = $frm->getRenderer();
        $renderer->wrappers['controls']['container'] = null; //'dl';
        $renderer->wrappers['pair']['container'] = 'div class=pure-control-group';
        $renderer->wrappers['label']['container'] = null; //'dt';
        $renderer->wrappers['control']['container'] = null; //'dd';
        
        $frm->setDefaults($default);
        
        return($frm);
    }
    
    
    public function createComponentOdpisMWOOPE() {
        $frm = new \Nette\Application\UI\Form();

        $default = array(
            'cono' => $this->m3Service->getCono()
            , 'faci' => $this->m3Service->getFaci()
            , 'rudi' => '02P'
            , 'rpdt' => date('Ymd')
            , 'rptm' => date('H') . (date('j') < 15 ? '00' : (date('j') < 30 ? '15' : (date('j') < 45 ? '30' : '45'))) . '00'
        );
        
        $frm->getElementPrototype()->class('pure-form pure-form-aligned');
        $frm->addHidden('reload');
        $frm->addHidden('cono');
        $frm->addHidden('faci');
        $frm->addHidden('opno');
        $frm->addHidden('prno');
        $frm->addHidden('maxmaqa');
        $frm->addHidden('timestamp');
        $frm->addText('mfno', 'DO')->setRequired()->getControlPrototype()->addAttributes(['readonly' =>'readonly']);
        $frm->addText('upit', 'Hodin:')->setRequired()->getControlPrototype()->addClass('nr');
        $frm['upit']->addRule(\Nette\Application\UI\Form::FLOAT, 'Počet hodin musí být číslo')
                ->addRule(\Nette\Application\UI\Form::MIN, 'Minimální čas musí být %d hodin. Čas musí být zadán!', 0.01)
                ->addRule(\Nette\Application\UI\Form::MAX, 'Nelze zadat čas více než %d hodin.', 72);;
        $workers = [];
        if (is_array($this->dept)) {
            foreach($this->dept as $dept) {
                foreach($this->m3Service->getEmployeeNames($dept) as $key => $worker) {
                    if (!isset($workers[trim($worker->EAEMNO)])) {
                        $workers[trim($worker->EAEMNO)] = $worker->EAEMNO . ' ' . $worker->EAEMNM;
                    }
                }
            }
        } else {
            foreach($this->m3Service->getEmployeeNames($this->dept) as $key => $worker) {
                if (!isset($workers[trim($worker->EAEMNO)])) {
                    $workers[trim($worker->EAEMNO)] = $worker->EAEMNO . ' ' . $worker->EAEMNM;
                }
            }
        }
        if (isset($this->rudis) && is_array($this->rudis)) {
            $frm->addSelect('rudi', 'Režim', $this->rudis);
        } elseif (isset($this->rudis) && !empty($this->rudis)) {
            $frm->addHidden('rudi');
            $default['rudi'] = $this->rudi;
        } else {
            $frm->addHidden('rudi');
        }
        if (isset($this->units) && is_array($this->units)) {
            $frm->addCheckBox('overProduction', 'Nadnáklad O.K.');
            $frm->addText('maqa', 'Vyrobeno:')->setRequired(false)->getControlPrototype()->addClass('nr');
            $frm['maqa']->addRule(\Nette\Application\UI\Form::FLOAT, 'Vyrobeno musí být číslo');
            //$frm['maqa']->addConditionOn($frm['overProduction'], \Nette\Application\UI\Form::EQUAL, false)
            //        ->addRule(\Nette\Application\UI\Form::MAX, 'Pozor je ndnáklad.', $frm['maxmaqa']);
            $frm->addSelect('maun', 'Jednotky', $this->rudis);
            $frm->onValidate[] = [$this, 'validateOdpisMWOOPE'];
        } elseif (isset($this->units) && !empty($this->units)) {
            $frm->addCheckBox('overProduction', 'Nadnáklad O.K.');
            $frm->addText('maqa', 'Vyrobeno:')->setRequired(false)->getControlPrototype()->addClass('nr');
            $frm['maqa']->addRule(\Nette\Application\UI\Form::FLOAT, 'Vyrobeno musí být číslo');
            //$frm['maqa']->addConditionOn($frm['overProduction'], \Nette\Application\UI\Form::EQUAL, false)
            //        ->addRule(\Nette\Application\UI\Form::MAX, 'Pozor je ndnáklad.', $frm['maxmaqa']);
            $frm->addText('maun', 'Jednotky')->setRequired()->getControlPrototype()->addAttributes(['readonly' =>'readonly']);
            $default['maun'] = $this->units;
            $frm->onValidate[] = [$this, 'validateOdpisMWOOPE'];
        } else {
            $frm->addHidden('maun');
        }
        $frm->addSelect('emno', 'Pracovník:', $workers)->setRequired();
        $frm->addSelect('shfc', 'Směna:', ['1' => '1. směna', '2' => '2. směna', '3' => '3. směna', '4' => '4. směna'])->setRequired();
        $frm->addCheckbox('rend', 'Ukončit:');//->getControlPrototype()->style('float: left;');
        
        
        $rpdts = [];
        $tm = time();
        for($i = 0; $i < 4; $i++) {
            $rpdts[date('Ymd', $tm - $i * 24 * 3600)] = date('j.n.Y', $tm - $i * 24 * 3600);
        }
        $frm->addSelect('rpdt', 'Dt. odpisu:', $rpdts)->setRequired();
        $rptms = [];
        for($i = 0; $i < 24; $i++) {
            for($j = 0; $j < 4; $j++) {
                $rptms[($i < 10 ? '0' : '') . $i . ($j < 1 ? '0' : '') . ($j * 15) . '00'] = $i . ':' . ($j < 1 ? '0' : '') . ($j * 15);
            }
        }
        $rptms['235959'] = '23:59';
        $frm->addSelect('rptm', 'Čas odpisu:', $rptms)->setRequired();
        
        
        $frm->addSubmit('sbmt', 'Odepsat')->getControlPrototype()->class('button-xsmall pure-button ajax');
        
        $frm->onSuccess[] = [$this, 'setOperationUpit'];
        
        $renderer = $frm->getRenderer();
        $renderer->wrappers['controls']['container'] = null; //'dl';
        $renderer->wrappers['pair']['container'] = 'div class=pure-control-group';
        $renderer->wrappers['label']['container'] = null; //'dt';
        $renderer->wrappers['control']['container'] = null; //'dd';
        
        $frm->setDefaults($default);
        
        return($frm);
    }
    
    public function validateOdpisMWOOPE(\Nette\Application\UI\Form $frm) {
    }
    
    protected $dept = [];
    protected $plgrs = [];
    protected $plgrengs = [];
    
    public function createComponentOdpisMWOOPEStroj() {
        $frm = new \Nette\Application\UI\Form();

        $default = array(
            'cono' => $this->m3Service->getCono()
            , 'faci' => $this->m3Service->getFaci()
            , 'rudi' => '01T'
            , 'hoursMinutes' => 'H'
            , 'rpdt' => date('Ymd')
            , 'rptm' => date('H') . (date('j') < 15 ? '00' : (date('j') < 30 ? '15' : (date('j') < 45 ? '30' : '45'))) . '00'
        );
        
        $frm->getElementPrototype()->class('pure-form pure-form-aligned');
        $frm->addHidden('reload');
        $frm->addHidden('cono');
        $frm->addHidden('plgr');
        $frm->addHidden('faci');
        $frm->addHidden('opno');
        $frm->addHidden('prno');
        $frm->addHidden('maxmaqa');
        $frm->addHidden('timestamp');
        $frm->addHidden('hoursMinutes');
        $frm->addText('mfno', 'DO')->setRequired()->getControlPrototype()->addAttributes(['readonly' =>'readonly']);
        $frm->addText('time', 'Hodin:')->setRequired()->getControlPrototype()->addClass('nr');
        $frm['time']->addRule(\Nette\Application\UI\Form::FLOAT, 'Počet hodin musí být číslo');
        
        $frm['time']->addConditionOn($frm['hoursMinutes'], \Nette\Application\UI\Form::EQUAL, 'H')
                    ->addRule(\Nette\Application\UI\Form::MIN, 'Minimální čas musí být %d hodin. Čas musí být zadán!', 0.01)
                    ->addRule(\Nette\Application\UI\Form::MAX, 'Nelze zadat čas více než %d hodin.', 16);
        
        $frm['time']->addConditionOn($frm['hoursMinutes'], \Nette\Application\UI\Form::EQUAL, 'M')
                    ->addRule(\Nette\Application\UI\Form::MIN, 'Minimální čas musí být %d minut. Čas musí být zadán!', 1)
                    ->addRule(\Nette\Application\UI\Form::MAX, 'Nelze zadat čas více než %d minut.', 60*24*3);
        /*$frm->addText('time2', 'Minut:')->setRequired()->getControlPrototype()->addClass('nr');
        $frm['time2']->addRule(\Nette\Application\UI\Form::INTEGER, 'Počet minut musí být číslo')
                ->addRule(\Nette\Application\UI\Form::MIN, 'Minimální čas musí být 1 minuta. Čas musí být zadán!', 1)
                ->addRule(\Nette\Application\UI\Form::MAX, 'Nelze zadat čas více než %d minut.', 16 * 60);*/
        //$frm->addText('timeaux', '')->setRequired()->getControlPrototype()->addClass('nr floatleft')->readonly(true);
        $workers = [];
        if (is_array($this->dept)) {
            foreach($this->dept as $dept) {
                $tmpWorkers = [];
                foreach($this->m3Service->getEmployeeNames($dept) as $key => $worker) {
                    if (!isset($workers[trim($worker->EAEMNO)]) && !isset($tmpWorkers[trim($worker->EAEMNO)])) {
                        if (empty($this->plgrengs) || in_array(trim($worker->EAPLGR), $this->plgrengs)) {
                            $workers[trim($worker->EAEMNO)] = $worker->EAEMNO . ' ' . $worker->EAEMNM;
                        } else {
                            $tmpWorkers[trim($worker->EAEMNO)] = $worker->EAEMNO . ' ' . $worker->EAEMNM;
                        }
                    }
                }
                if (!empty($tmpWorkers)) {
                    foreach($tmpWorkers as $key => $worker) {
                        $workers[$key] = $worker;
                    }
                }
            }
        } else {
            $tmpWorkers = [];
            foreach($this->m3Service->getEmployeeNames($this->dept) as $key => $worker) {
                if (!isset($workers[trim($worker->EAEMNO)]) && !isset($tmpWorkers[trim($worker->EAEMNO)])) {
                    if (empty($this->plgrengs) || in_array(trim($worker->EAPLGR), $this->plgrengs)) {
                        $workers[trim($worker->EAEMNO)] = $worker->EAEMNO . ' ' . $worker->EAEMNM;
                    } else {
                        $tmpWorkers[trim($worker->EAEMNO)] = $worker->EAEMNO . ' ' . $worker->EAEMNM;
                    }
                }
            }
            if (!empty($tmpWorkers)) {
                foreach($tmpWorkers as $key => $worker) {
                    $workers[$key] = $worker;
                }
            }
        }
        if (isset($this->rudis) && is_array($this->rudis)) {
            $frm->addSelect('rudi', 'Režim', $this->rudis);
        } elseif (isset($this->rudis) && !empty($this->rudis)) {
            $frm->addSelect('rudi', 'Režim', $this->m3Service->getDruhyPraciArray());
            $default['rudi'] = $this->rudi;
        } else {
            $frm->addSelect('rudi', 'Režim', $this->m3Service->getDruhyPraciArray());
        }
        if (isset($this->plgrs) && is_array($this->plgrs) && !empty($this->plgrs)) {
            $frm->addSelect('dplg', 'Stroj', $this->plgrs);
        } else {
            $frm->addHidden('dplg');
        }
        if (isset($this->units) && is_array($this->units)) {
            $frm->addCheckBox('overProduction', 'Nadnáklad O.K.');
            $frm->addText('maqa', 'Vyrobeno:')->setRequired(false)->getControlPrototype()->addClass('nr');
            $frm['maqa']->addRule(\Nette\Application\UI\Form::FLOAT, 'Vyrobeno musí být číslo');
            $frm['maqa']->addConditionOn($frm['rudi'], \Nette\Application\UI\Form::IS_NOT_IN, ['01T', '41T'])
                    ->addRule(\Nette\Application\UI\Form::EQUAL, 'Nevýrobní čas - vyrobeno by mělo být nulové.', 0);
            $frm['maqa']->addConditionOn($frm['rudi'], \Nette\Application\UI\Form::IS_IN, ['01T', '41T'])
                    ->addRule(\Nette\Application\UI\Form::NOT_EQUAL, 'Výrobní čas - vyrobeno by mělo být nenulové.', 0);
            //$frm['maqa']->addConditionOn($frm['overProduction'], \Nette\Application\UI\Form::EQUAL, false)
            //        ->addRule(\Nette\Application\UI\Form::MAX, 'Pozor je ndnáklad.', $frm['maxmaqa']);
            $frm->addSelect('maun', 'Jednotky', $this->rudis);
            $frm->onValidate[] = [$this, 'validateOdpisMWOOPE'];
        } elseif (isset($this->units) && !empty($this->units)) {
            $frm->addCheckBox('overProduction', 'Nadnáklad O.K.');
            $frm->addText('maqa', 'Vyrobeno:')->setRequired(false)->getControlPrototype()->addClass('nr');
            $frm['maqa']->addRule(\Nette\Application\UI\Form::FLOAT, 'Vyrobeno musí být číslo');
            $frm['maqa']->addConditionOn($frm['rudi'], \Nette\Application\UI\Form::IS_NOT_IN, ['01T', '41T'])
                    ->addRule(\Nette\Application\UI\Form::EQUAL, 'Nevýrobní čas - vyrobeno by mělo být nulové.', 0);
            $frm['maqa']->addConditionOn($frm['rudi'], \Nette\Application\UI\Form::IS_IN, ['01T', '41T'])
                    ->addRule(\Nette\Application\UI\Form::NOT_EQUAL, 'Výrobní čas - vyrobeno by mělo být nenulové.', 0);
            //$frm['maqa']->addConditionOn($frm['overProduction'], \Nette\Application\UI\Form::EQUAL, false)
            //        ->addRule(\Nette\Application\UI\Form::MAX, 'Pozor je ndnáklad.', $frm['maxmaqa']);
            $frm->addText('maun', 'Jednotky')->setRequired()->getControlPrototype()->addAttributes(['readonly' =>'readonly']);
            $default['maun'] = $this->units;
            $frm->onValidate[] = [$this, 'validateOdpisMWOOPE'];
        } else {
            $frm->addHidden('maun');
        }
        $frm->addSelect('emno', 'Pracovník:', $workers)->setRequired();
        $frm->addSelect('shfc', 'Směna:', ['1' => '1. směna', '2' => '2. směna', '3' => '3. směna', '4' => '4. směna'])->setRequired();
        $frm->addCheckbox('rend', 'Ukončit:');//->getControlPrototype()->style('float: left;');
        
        if (($this->getUser()->isAllowed('Admin') || $this->getUser()->isAllowed('OdpisyAdmin'))) {
            $rpdts = [];
            $tm = time();
            for($i = 0; $i < 4; $i++) {
                $rpdts[date('Ymd', $tm - $i * 24 * 3600)] = date('j.n.Y', $tm - $i * 24 * 3600);
            }
            $frm->addSelect('rpdt', 'Dt. odpisu:', $rpdts)->setRequired();

            $rptms = [];
            for($i = 0; $i < 24; $i++) {
                for($j = 0; $j < 4; $j++) {
                    if ($i + $j == 0) {
                        $rptms['000100'] = $i . ':' . ($j < 1 ? '0' : '') . ($j * 15);
                    } else {
                        $rptms[($i < 10 ? '0' : '') . $i . ($j < 1 ? '0' : '') . ($j * 15) . '00'] = $i . ':' . ($j < 1 ? '0' : '') . ($j * 15);
                    }
                }
            }
            $rptms['235959'] = '23:59';
            $frm->addSelect('rptm', 'Čas odpisu:', $rptms)->setRequired();
            $frm->addHidden('rpdts', 'Dt. odpisu:')->setValue(date('Ymd'));
            $frm->addHidden('rptms', 'Čas odpisu:')->setValue('000000');
        } else {
            //$frm['rpdt']->setDisabled(true)->setValue(date('Ymd'));
            //$frm['rptm']->setDisabled(true)->setValue('000000');
            $frm->addText('rpdts', 'Dt. odpisu:')->setValue(date('j.n.Y'))->setAttribute('readonly', 'readonly');
            $frm->addText('rptms', 'Čas odpisu:')->setValue('00:00')->setAttribute('readonly', 'readonly');
            $frm->addHidden('rpdt', 'Dt. odpisu:')->setValue(date('Ymd'));
            $frm->addHidden('rptm', 'Čas odpisu:')->setValue('000000');
        }
        
        $frm->addSubmit('sbmt', 'Odepsat')->getControlPrototype()->class('button-xsmall pure-button ajax');
        
        $frm->onSuccess[] = [$this, 'setOperationUpitUmatStroj'];
        
        $renderer = $frm->getRenderer();
        $renderer->wrappers['controls']['container'] = null; //'dl';
        $renderer->wrappers['pair']['container'] = 'div class=pure-control-group';
        $renderer->wrappers['label']['container'] = null; //'dt';
        $renderer->wrappers['control']['container'] = null; //'dd';
        
        $frm->setDefaults($default);
        
        return($frm);
    }
    
    public function actionGetParamDoc(string $type, string $code, string $name) {
        $doc = $this->systemService->getParamDocRow($type, $code, $name);
        //\Nette\Diagnostics\Debugger::dump($doc); exit;
        if ($doc) {
            header('Content-Type: ' . $doc['enctype']);
            header('Content-Disposition: attachment;filename="' . $doc['filename'] . '"');
            header("Cache-Control: public"); 
            header("Content-Description: File Transfer");
            echo $doc['value'];
            $this->terminate();
        }
        // Jinak zobrazíme template soubor nenalezen
    }
    
    public function actionGetParamDocById(string $id) {
        $doc = $this->systemService->getParamDocById(intval($id));
        //\Nette\Diagnostics\Debugger::dump($doc); exit;
        if ($doc) {
            header('Content-Type: ' . $doc['enctype']);
            header('Content-Disposition: attachment;filename="' . $doc['filename'] . '"');
            header("Cache-Control: public"); 
            header("Content-Description: File Transfer");
            echo $doc['value'];
            $this->terminate();
        }
        // Jinak zobrazíme template soubor nenalezen
    }
    
    protected function encodePassword(string $password, string $hash = 'vlk'): string {
        $cipher = "aes-128-cbc";
        if (in_array($cipher, openssl_get_cipher_methods())) {
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($password, $cipher, $hash, $options = OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, $hash, $as_binary = true);
            $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw );
        } else {
            $ciphertext = $password;
        }
        return($ciphertext);
    }
    
    protected function decodePassword(string $encoded, string $hash = 'vlk'): string {
        $cipher = "aes-128-cbc";
        $c = base64_decode($encoded);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $hash, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $hash, $as_binary = true);
        if (hash_equals($hmac, $calcmac)) {//PHP 5.6+ timing attack safe comparison
            return($original_plaintext);
        } else {
            return($encoded);
        }
        
    }
    
    public function renderBarcode(string $id = 'ABC', string $output='PNG', string $type = '39', $return = false) {
        switch($output) {
            case 'JPG':
                header('Content-Type: image/jpeg');
                $generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
                break;
            case 'PNG':
                header('Content-Type: image/png');
                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                break;
            case 'SVG':
                $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                break;
            case 'HTML':
            default: 
                $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
                
        }
        //echo $generator->getBarcode($id, $generator::TYPE_CODE_128);
        switch($type) {
            case '128':
                if ($return) {
                    return($generator->getBarcode($id, $generator::TYPE_CODE_128));
                } else {
                    echo $generator->getBarcode($id, $generator::TYPE_CODE_128);
                }
                break;
            case '39':
            default:
                if ($return) {
                    return($generator->getBarcode($id, $generator::TYPE_CODE_39));
                } else {
                    echo $generator->getBarcode($id, $generator::TYPE_CODE_39);
                }
        }
        $this->terminate();
        /*
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode('081231723897', $generator::TYPE_CODE_128)) . '">';
         */
    }
    
}
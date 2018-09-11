<?php
namespace App\Presenters;
use \h4kuna\Gettext\InjectTranslator;
/**
 * Base class for all application presenters.
 *
 * @author     Jan Němec
 * @package    cscz.biz
 */
abstract class BasePresenter extends \Nette\Application\UI\Presenter {

    public $oldLayoutMode = FALSE;

    
    /** @var \Model\Jannemec\UserRight */
    public $userRightService;

    /**
     * Inject userright service
     * @param \Model\Jannemec\UserRight
     */
    public function injectUserRightService(\Model\Jannemec\UserRight $userRightService) {
        $this->userRightService = $userRightService;
    }

    /** @var \Model\Jannemec\System */
    protected $systemService = null;

    /**
     * Inject system
     * @param $systemService \Model\Jannemec\System
     */
    public function injectSystemService(\Model\Jannemec\System $systemService) {
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
    
    
    public function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        // if not set, the default language will be used
        if (!isset($this->lang)) {
            $this->lang = $this->translator->getLanguage();
        } else {
            if (!in_array($this->lang, ['cs', 'en'])) {
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
    
    public function handleSetParam(string $type, string $key, string $name, string $value) {
        echo $this->systemService->setParam($type, $key, $name, $value, $this->getUsername());
        $this->terminate();
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
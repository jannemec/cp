<?php
/**
 * Description of loginForm
 *
 * @author nemec
 */

namespace Controls;

class LoginForm extends \Nette\Application\UI\Form {

    /** @persistent */
    public $backlink = '';
    
    private $watermarks = array();

    public function __construct($parent = null, $name = null) {
        //$this->translator = new GettextTranslator('techorder.' . $parent->getLang() . '.mo', $parent->getLang()); // druhý parametr je volitelný
        if (is_null($name)) {
            $name = 'loginForm';
        }
        parent::__construct($parent, $name);
        $this->getElementPrototype()->class('pure-form loginform');
        if ($parent->getUser()->isLoggedIn()) {
            $this->addGroup();
            //        ->setOption('container', Nette\Utils\Html::el('fieldset')->class('submit'));
            //$this->addSubmit('logout', 'Odhlásit')->onClick(array($this, 'logoutFormSubmitted'));
            $this->addImage('logout', BASE_URI . 'myicons/logouticon.png')
            //->onClick(array($this, 'searchFormSubmitted'))
            ->getControlPrototype()
            ->class('formIcon')->setAttribute('alt', 'logout');
            $this->onSuccess[] = array($this, 'logoutFormSubmitted');
        } else {
            $this->addGroup();
            $this->addText('username', 'Email:')
                ->addRule(\Nette\Forms\Form::FILLED, 'Zadejte uživatelské jméno.');
            $this->watermarks['username'] = 'username';
            $this->addPassword('password', 'Heslo:')
                ->addRule(\Nette\Forms\Form::FILLED, 'Zadej heslo.');
            $this->watermarks['password'] = 'password';
            //$this->addGroup()
            //        ->setOption('container', Nette\Utils\Html::el('fieldset')->class('submit'));
            //$this->addSubmit('login', 'Přihlásit')->onClick(array($this, 'loginFormSubmitted'))0
            $this->addImage('login', BASE_URI . 'myicons/loginicon.png')
                ->getControlPrototype()
                ->class('formIcon')->setAttribute('alt', 'login');
            $this->onSuccess[] = array($this, 'loginFormSubmitted');
        }
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = null;
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;
    }

    public function setUsername($value) {
        $this['username']->value = $value;
    }
    
    public function loginFormSubmitted(\Nette\Application\UI\Form $form) {
        try {
            // Zkusíme ověření proti dbf
            if ($this->parent->userRightService->validateUser($form['username']->getValue(), $form['password']->getValue())) {
                 $this->getParent()->getUser()->login($form['username']->getValue(), null);
            }
            if (!$this->getParent()->getUser()->isLoggedIn()) {
                $this->parent->getUser()->login($form['username']->getValue(), $form['password']->getValue());
                if ($this->backlink != '') {
                    $this->parent->getApplication()->restoreRequest($this->backlink);
                } else {
                    $this->parent->redirect('Home:');
                }
            }
        } catch (\Nette\Security\AuthenticationException $e) {
            // Chyba přihlášení - přeposlat na stránku
            if ($e->getCode() == \Nette\Security\IAuthenticator::INVALID_CREDENTIAL) {
                // špatný login - nedělej nic
                $this->parent->forward('Home:LoginError', array('username' => $form['username']->getValue()));
            } elseif ($e->getCode() == \Nette\Security\IAuthenticator::NOT_APPROVED) {
                // Není ještě povolen ... přesměruj na chybu
                $this->parent->forward('Home:LoginError', array('username' => $form['username']->getValue()));
            } elseif ($e->getCode() == \Nette\Security\IAuthenticator::IDENTITY_NOT_FOUND) {
                $this->parent->forward('Home:LoginError', array('username' => $form['username']->getValue()));
            }
        }
    }

    public function logoutFormSubmitted(\Nette\Application\UI\Form $form) {
        $this->parent->getUser()->logout(true);
        $this->parent->redirect('Home:');
    }

    public function render(...$args) {
        parent::render();
        $latte = new \Latte\Engine;
        $parameters = [];
        if ($this->parent->getUser()->isLoggedIn()) {
            $parameters['username'] = $this->parent->getUser()->getIdentity()->user['name'];
        }
        $parameters['presenter'] = $this->parent;
        $parameters['watermarks'] = array();
        foreach ($this->watermarks as $key => $val) {
            $parameters['watermarks'][$this[$key]->getHtmlId()] = $val;
        }
        $latte->render(__DIR__ . '/templates/LoginForm.latte', $parameters); 
    }
    
    public static function setEmail($email) {
        $this['email']->value = $email;
    } 
}

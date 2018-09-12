<?php
namespace Controls\Admin;

/**
 * Description of loginForm
 *
 * @author nemec
 */

class UserEditForm extends \Nette\Application\UI\Control {

    /** @persistent */
    public $backlink = '';

    /** @var \Nette\Database\Table\ActiveRow */
    protected $eUser;

    public function __construct($parent = null, $name = null) {
        if (is_null($name)) {
            $name = 'userEditForm';
        }
        parent::__construct($parent, $name);
    }

    /**
     * 
     * @param \Nette\Database\Table\ActiveRow $eUser
     */
    public function setEUser(\Nette\Database\Table\ActiveRow $eUser) {
        $this->eUser = $eUser;
    }
    /**
     * 
     * @return \Nette\Database\Table\ActiveRow 
     */
    public function getEUser() {
        return($this->eUser);
    }
    public function createComponent($name) {
        switch ($name) {
            case 'inAEditUserForm':
                $form = new \Nette\Application\UI\Form();
                $form->getElementPrototype()->class('pure-form pure-form-aligned');
                $form->addHidden('id');

                $form->addGroup('Name');
                $form->addText('username', 'Username: ')->setDisabled(true);
                $form->addText('name', 'Jméno: ');

                $form->addGroup('Description');
                $form->addText('description', 'Poznámka: ');
                $form->addPassword('password', 'Heslo: ')->setValue("");

                $form->addGroup();
                        //->setOption('container', \Nette\Utils\Html::el('fieldset')->class('submit'));
                $form->addSubmit('save', 'SAVE')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['save']->onClick(array($this, 'editFormSubmitted'));
                $form->addSubmit('cancel', 'CANCEL')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['cancel']->onClick(array($this, 'editFormSubmitted'));
                $form->addSubmit('back', 'BACK')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['back']->onClick(array($this->getParent(), 'groups'));
                $form->onSuccess[] = array($this, 'editFormSubmitted');

                $renderer = $form->getRenderer();
                $renderer->wrappers['controls']['container'] = null;
                $renderer->wrappers['pair']['container'] = 'div class="pure-control-group"';
                $renderer->wrappers['label']['container'] = null;
                $renderer->wrappers['control']['container'] = null;
                
                if ($this->eUser instanceOf \Nette\Database\Table\ActiveRow) {
                    $form['id']->setValue($this->eUser->id);
                    $form['name']->setValue($this->eUser->name);
                    $form['username']->setValue($this->eUser->username);
                    $form['description']->setValue($this->eUser->description);
                }

                $this->addComponent($form, 'inAEditUserForm');
                
                break;
            case 'inAEditUserDetailForm':
                $form = new \Nette\Application\UI\Form();
                $form->getElementPrototype()->class('pure-form pure-form-aligned');
                
                $form->addHidden('id');

                $form->addGroup('Hodnoty');
                
                if ($this->eUser instanceOf \Nette\Database\Table\ActiveRow) {
                    $datas = $this->parent->userRightService->getSysUserData($this->eUser->id);
                    foreach($datas as $data) {
                        $form->addText('data_name_' . $data->id, 'Proměnná: ')->setValue($data->name);
                        $form->addTextArea('data_value_' . $data->id, 'Hodnota: ')->setValue($data->value);
                    }
                }
                $form->addText('data_name_new', 'Proměnná new: ')->setValue('');
                $form->addTextArea('data_value_new', 'Hodnota new: ')->setValue('');
                
                $form->addGroup();
                        //->setOption('container', \Nette\Utils\Html::el('fieldset')->class('submit'));
                $form->addSubmit('save', 'SAVE')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['save']->onClick(array($this, 'editFormSubmitted2'));
                $form->addSubmit('cancel', 'CANCEL')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['cancel']->onClick(array($this, 'editFormSubmitted2'));
                $form->addSubmit('back', 'BACK')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['back']->onClick(array($this->getParent(), 'users'));
                $form->onSuccess[] = array($this, 'editFormSubmitted2');

                $renderer = $form->getRenderer();
                $renderer->wrappers['controls']['container'] = null;
                $renderer->wrappers['pair']['container'] = 'div class="pure-control-group"';
                $renderer->wrappers['label']['container'] = null;
                $renderer->wrappers['control']['container'] = null;

                if ($this->eUser instanceOf \Nette\Database\Table\ActiveRow) {
                    $form['id']->setValue($this->eUser->id);
                }
                
                $this->addComponent($form, 'inAEditUserDetailForm');
                break;
            default:
                parent::createComponent($name);
                return;
        }
    }

    public function editFormSubmitted2(\Nette\Application\UI\Form $form) {
        try {
            if ($sbmtBy = $form->isSubmitted()) {
                if ($form['back']->isSubmittedBy()) {
                    // Cancel - na seznam
                    $this->getParent()->redirect('users');
                } elseif ($form['cancel']->isSubmittedBy()) {
                    // Aktualizace údajů
                    $this->eUser = $this->getParent()->userRightService->getUser(intval($form['id']->value));
                    $this->getParent()->redirect('editUser', array('id' => $form['id']->value));
                } else {
                    // Aktualizace údajů - přidání/smazání parametru
                    $this->eUser = $this->getParent()->userRightService->getUser(intval($form['id']->value));
                    $datas = $this->getParent()->userRightService->getSysUserData($this->eUser->id);
                    foreach($datas as $data) {
                        if (trim($form['data_name_' . $data->id]->value) == '') {
                            // Smazaný název - vymazat hodnotu
                            $this->getParent()->userRightService->removeUserDataById($data->id);
                        } elseif ((trim($form['data_name_' . $data->id]->value) != $data->name) || (trim($form['data_value_' . $data->id]->value) != $data->value)) {
                            // Hodnoty se změnily
                            $params = array(
                                  'name' => trim($form['data_name_' . $data->id]->value)
                                , 'value' => trim($form['data_value_' . $data->id]->value)
                                );
                            $this->getParent()->userRightService->updateSysUserData($data, $params);
                        } else {
                            // Nezměněné hodnoty
                        }
                    }
                    // A podíváme se na poslední položku
                    if (trim($form['data_name_new']->value) != '') {
                        // Založíme
                        $params = array(
                              'sys_user_id' => $this->eUser->id
                            , 'name' => trim($form['data_name_new']->value)
                            , 'value' => trim($form['data_value_new']->value)
                            );
                        $this->getParent()->userRightService->addSysUserData($params);
                    }
                    $this->getParent()->redirect('editUser', array('id' => $form['id']->value));
                }
            } else {
                // Formulář nebyl odeslán ... nedělej nic
            }
        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function editFormSubmitted(\Nette\Application\UI\Form $form) {
        try {
            if ($sbmtBy = $form->isSubmitted()) {
                if ($form['back']->isSubmittedBy()) {
                    // Cancel - na seznam
                    $this->getParent()->redirect('users');
                } elseif ($form['cancel']->isSubmittedBy()) {
                    // Aktualizace údajů
                    $this->eUser = $this->getParent()->userRightService->getUser($form['id']->value);
                    $this->getParent()->redirect('editUser', array('id' => $form['id']->value));
                } else {
                    // Aktualizace údajů
                    $this->eUser = $this->getParent()->userRightService->getUser($form['id']->value);
                    $params = array();
                    $params['description'] = $form['description']->value;
                    $params['name'] = $form['name']->value;
                    if (trim($form['password']->value) != '') {
                        $params['hash'] = md5(trim($form['password']->value));
                    }
                    $this->getParent()->userRightService->updateUser($this->eUser, $params);
                    $this->getParent()->redirect('editUser', array('id' => $form['id']->value));
                }
            } else {
                // Formulář nebyl odeslán ... nedělej nic
            }
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function render($backlink = NULL) {
        if ($backlink !== NULL) {
            $this->backlink = $backlink;
        }       
        $template = $this->createTemplate();
        $template->setFile(dirname(__FILE__) . "/UserEditFormComponent.latte");
        $template->render();
    }
}
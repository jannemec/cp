<?php
namespace Admin;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of loginForm
 *
 * @author nemec
 */

class UserGroupForm extends \Nette\Application\UI\Control {

    /** @persistent */
    public $backlink = '';

    /** @var \Nette\Database\Table\ActiveRow */
    protected $eUser;

    public function __construct($parent = null, $name = null) {
        if (is_null($name)) {
            $name = 'userGroupForm';
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
            case 'inEditUserGroupForm':
                $form = new \Nette\Application\UI\Form();
                $form->getElementPrototype()->class('pure-form pure-form-aligned');
                $form->addHidden('id')->setValue($this->eUser->id);
                $form->addGroup('Skupiny');
                foreach($this->getParent()->userRightService->getGroups() as $group) {
                    $f = $form->addCheckbox('group_' . $group->id, $group->name);
                    if ($this->getParent()->userRightService->isMemberOf($this->eUser->id, $group->id)) {
                        $f->setValue(true);
                    } else {
                        $f->setValue(false);
                    }
                }
                $form->addGroup()
                        ->setOption('container', \Nette\Utils\Html::el('fieldset')->class('submit'));
                $form->addSubmit('save', 'SAVE')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['save']->onClick(array($this, 'editFormSubmitted'));
                $form->addSubmit('cancel', 'CANCEL')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['cancel']->onClick(array($this, 'editFormSubmitted'));
                $form->onSuccess[] = array($this, 'editFormSubmitted');

                $renderer = $form->getRenderer();
                $renderer->wrappers['controls']['container'] = null;
                $renderer->wrappers['pair']['container'] = 'div class="pure-control-group"';
                $renderer->wrappers['label']['container'] = null;
                $renderer->wrappers['control']['container'] = null;

                $this->addComponent($form, 'inEditUserGroupForm');
                break;
            default:
                parent::createComponent($name);
                return;
        }
    }


    public function editFormSubmitted(\Nette\Application\UI\Form $form) {
        try {
            if ($form['cancel']->isSubmittedBy()) {
                $this->getParent()->redirect('editUser', $form['id']->value);
            } elseif ($form['save']->isSubmittedBy()) {
                $this->eUser = $this->getParent()->userRightService->getUser($form['id']->value);
                foreach($this->getParent()->userRightService->getGroups() as $group) {
                    $this->getParent()->userRightService->setMemberOf($this->eUser->id, $group->id, $form['group_' . $group->id]->value);
                }
            } else {} // Formulář nebyl odeslán ... nedělej nic
        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function render($backlink = NULL) {
        if ($backlink !== NULL) {
            $this->backlink = $backlink;
        }
        $template = $this->createTemplate();
        $template->setFile(dirname(__FILE__) . "/UserGroupFormComponent.latte");
        $template->render();
    }
}

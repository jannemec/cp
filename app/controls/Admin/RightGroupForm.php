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

class RightGroupForm extends \Nette\Application\UI\Control {

    /** @persistent */
    public $backlink = '';

    /** @var \Nette\Database\Table\ActiveRow */
    protected $eRight;
    /** @var \Nette\Database\Table\ActiveRow */
    protected $eGroup;

    public function __construct($parent = null, $name = null) {
        if (is_null($name)) {
            $name = 'rightGroupForm';
        }
        parent::__construct($parent, $name);
    }

    /**
     * 
     * @param \Nette\Database\Table\ActiveRow $eRight
     */
    public function setERight(\Nette\Database\Table\ActiveRow $eRight) {
        $this->eRight = $eRight;

    }
    /**
     * 
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getERight() {
        return($this->eRight);
    }

    /**
     * 
     * @param \Nette\Database\Table\ActiveRow $eGroup
     */
    public function setEGroup(\Nette\Database\Table\ActiveRow $eGroup) {
        $this->eGroup = $eGroup;

    }

    /**
     * 
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getEGroup() {
        return($this->eGroup);
    }

    public function createComponent($name) {
        switch ($name) {
            case 'inEditRightGroupForm':
                $form = new \Nette\Application\UI\Form();
                $form->getElementPrototype()->class('pure-form pure-form-aligned');
                if (!is_null($this->eRight)) {
                    $form->addHidden('id')->setValue($this->eRight->id);
                    $form->addHidden('way')->setValue('right');
                    $form->addGroup('Skupiny');
                    foreach($this->getParent()->userRightService->getGroups() as $group) {
                        $f = $form->addCheckbox('group_' . $group->id, $group->name);
                        if ($this->getParent()->userRightService->isAllowed($group->id, $this->eRight->id)) {
                            $f->setValue(true); 
                        } else {
                            $f->setValue(false);
                        }
                    }
                } else {
                    $form->addHidden('id')->setValue($this->eGroup->id);
                    $form->addHidden('way')->setValue('group');
                    $form->addGroup('Práva');
                    foreach($this->getParent()->userRightService->getRights() as $right) {
                        $f = $form->addCheckbox('right_' . $right->id, $right->name);
                        if ($this->getParent()->userRightService->isAllowed($this->eGroup->id, $right->id)) {
                            $f->setValue(true);
                        } else {
                            $f->setValue(false);
                        }
                    }
                }

                $form->addGroup();//->setOption('container', \Nette\Utils\Html::el('fieldset')->class('submit'));
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

                $this->addComponent($form, 'inEditRightGroupForm');
                break;
            default:
                parent::createComponent($name);
                return;
        }
    }


    public function editFormSubmitted(\Nette\Application\UI\Form $form) {
        try {
            if ($sbmtBy = $form->isSubmitted()) {
                if ($form['cancel']->isSubmittedBy()) {
                    // Jen obnovení údajů
                    if ($form['way']->value == 'right') {
                        $this->getParent()->redirect('editRight', $form['id']->value);
                    } else {
                        $this->getParent()->redirect('editGroup', $form['id']->value);
                    }
                } else {
                    // Aktualizace údajů
                    if ($form['way']->value == 'right') {
                        $this->eRight = $this->getParent()->userRightService->getRight($form['id']->value);
                        foreach($this->getParent()->userRightService->getGroups() as $group) {
                            $this->getParent()->userRightService->setAllowed($group->id, $this->eRight->id, $form['group_' . $group->id]->value);
                        }
                    } else {
                        $this->eGroup = $this->getParent()->userRightService->getGroup($form['id']->value);
                        foreach($this->getParent()->userRightService->getRights() as $right) {
                            $this->getParent()->userRightService->setAllowed($this->eGroup->id, $right->id, $form['right_' . $right->id]->value);
                        }
                    }
                }
            } else {
                // Formulář nebyl odeslán ... nedělej nic
            }
        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function render($backlink = NULL) {
        if ($backlink !== NULL) {
            $this->backlink = $backlink;
        }
        $template = $this->createTemplate();
        $template->setFile(dirname(__FILE__) . "/RightGroupFormComponent.latte");
        $template->render();
    }
}
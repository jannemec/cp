<?php
namespace Controls\Admin;

/**
 * Description of loginForm
 *
 * @author nemec
 */

class GroupEditForm extends \Nette\Application\UI\Control {

    /** @persistent */
    public $backlink = '';

    /** @var \Nette\Database\Table\ActiveRow */
    protected $eGroup;

    public function __construct($parent = null, $name = null) {
        if (is_null($name)) {
            $name = 'groupEditForm';
        }
        parent::__construct($parent, $name);
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
            case 'inAEditGroupForm':
                $form = new \Nette\Application\UI\Form();
                $form->getElementPrototype()->class('pure-form pure-form-aligned');
                $form->addHidden('id')->setValue($this->eGroup->id);
                $form->addGroup('Name');
                $form->addText('name', 'Jméno: ')->setValue($this->eGroup->name);
                $form->addText('description', 'Poznámka: ')->setValue($this->eGroup->description);
                $form->addGroup();//->setOption('container', \Nette\Utils\Html::el('fieldset')->class('submit'));
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
                $this->addComponent($form, 'inAEditGroupForm');
                break;
            default:
                parent::createComponent($name);
                return;
        }
    }


    public function editFormSubmitted(\Nette\Application\UI\Form $form) {
        try {
            if ($sbmtBy = $form->isSubmitted()) {
                if ($form['back']->isSubmittedBy()) {
                    // Cancel - na seznam
                    $this->getParent()->redirect('groups');
                } elseif ($form['cancel']->isSubmittedBy()) {
                    // Aktualizace údajů
                    $this->eGroup = $this->getParent()->userRightService->getGroup(intval($form['id']->value));
                    $this->getParent()->redirect('editGroup', array('id' => $form['id']->value));
                } else {
                    // Aktualizace údajů
                    $this->eGroup = $this->getParent()->userRightService->getGroup(intval($form['id']->value));
                    $params = array(
                          'description' => $form['description']->value
                        , 'name' => $form['name']->value
                    );
                    $this->getParent()->userRightService->updateGroup($this->eGroup, $params);
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
        $template->setFile(dirname(__FILE__) . "/GroupEditFormComponent.latte");
        //$template->form = $this->form;
        $template->render();
    }
}

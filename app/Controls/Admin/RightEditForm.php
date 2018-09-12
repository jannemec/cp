<?php
namespace Controls\Admin;
/**
 * Description of loginForm
 *
 * @author nemec
 */

class RightEditForm extends \Nette\Application\UI\Control {

    /** @persistent */
    public $backlink = '';

    /** @var \Nette\Database\Table\ActiveRow */
    protected $eRight;

    public function __construct($parent = null, $name = null) {
        if (is_null($name)) {
            $name = 'rightEditForm';
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
    public function createComponent($name) {
        switch ($name) {
            case 'inAEditRightForm':
                $form = new \Nette\Application\UI\Form();
                $form->getElementPrototype()->class('pure-form pure-form-aligned');
                $form->addHidden('id')->setValue($this->eRight->id);
                $form->addGroup('Name');
                $form->addText('name', 'Jméno: ')->setValue($this->eRight->name);
                $form->addText('description', 'Poznámka: ')->setValue($this->eRight->description);
                $form->addGroup();
                        //->setOption('container', \Nette\Utils\Html::el('fieldset')->class('submit'));
                $form->addSubmit('save', 'SAVE')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['save']->onClick(array($this, 'editFormSubmitted'));
                $form->addSubmit('cancel', 'CANCEL')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['cancel']->onClick(array($this, 'editFormSubmitted'));
                $form->addSubmit('back', 'BACK')->getControlPrototype()->class('button-xsmall pure-button');
                    $form['back']->onClick(array($this->getParent(), 'users'));
                $form->onSuccess[] = array($this, 'editFormSubmitted');
                $renderer = $form->getRenderer();
                $renderer->wrappers['controls']['container'] = null;
                $renderer->wrappers['pair']['container'] = 'div class="pure-control-group"';
                $renderer->wrappers['label']['container'] = null;
                $renderer->wrappers['control']['container'] = null;
                $this->addComponent($form, 'inAEditRightForm');
                break;
            default:
                parent::createComponent($name);
                return;
        }
    }


    public function editFormSubmitted(\Nette\Application\UI\Form $form) {
        try {
            if ($form->isSubmitted()) {
                if ($form['back']->isSubmittedBy()) {
                    $this->getParent()->redirect('rights');
                } elseif ($form['cancel']->isSubmittedBy()) {
                    // Aktualizace údajů
                    $this->eRight = $this->getParent()->userRightService->getRight(intval($form['id']->value));
                } else {
                    // Aktualizace údajů
                    $this->eRight = $this->getParent()->userRightService->getRight(intval($form['id']->value));
                    $params = array(
                        'name' => trim($form['name']->value)
                        , 'description' => trim($form['description']->value)
                    );
                    $this->getParent()->userRightService->updateRight($this->eRight, $params);
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
        $template->setFile(dirname(__FILE__) . "/RightEditFormComponent.latte");
        $template->render();
    }
}
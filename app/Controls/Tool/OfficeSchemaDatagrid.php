<?php
namespace Controls\Tool;
/**
 * Description of OfficeSchemaDatagrid
 *
 * @author Nemec
 */
class OfficeSchemaDatagrid extends \Ublaboo\DataGrid\DataGrid {
    /**
     *
     * @var \Adldap\AD 
     */
    protected $adService = null;
    
    /**
     *
     * @var \Model\Jannemec\System 
     */
    protected $systemService = null;
    
    protected $dtsrc = null;
    
    public function __construct($parent = null, $name = null, \Adldap\AD $adService = null, \Model\Jannemec\System $systemService = null) {
        parent::__construct();
        $parent->addComponent($this, $name);
        $this->adService = $adService;
        $this->systemService = $systemService;
        
        $this->dtsrc = $this->systemService->getOfficeSchemaDataSource();
        $this->setDataSource($this->dtsrc);
        $this->addColumnNumber('floor', 'Patro')->setSortable(true);
        $this->addColumnNumber('xloc', 'X');
        $this->addColumnNumber('yloc', 'Y');
        $this->addColumnText('pos', 'Position')->setSortable(true);
        
        $this->addColumnText('personact', 'Aktulní osoba')->setSortable(true);
        $this->addColumnText('persondef', 'Domácí osoba')->setSortable(true);
        
        $this->addFilterText('pos', 'Position');
        $this->addFilterText('personact', 'Aktulní osoba');
        $this->addFilterText('persondef', 'Domácí osoba');
        $this->addFilterSelect('floor', 'Patro:', ['' => '-', '1' => '1st Floor', '2' => '2nd Floor']);
        
        $this->addAction('delete', '', 'deletePosition!')
		->setIcon('trash')
		->setTitle('Delete')
		->setClass('btn btn-xs btn-danger ajax')
		->setConfirm('Do you really want to delete position %s?', 'pos');
        
        $this->addInlineEdit()
            ->setClass('btn btn-xs')
            ->setIcon('edit')
            ->onControlAdd[] = function($container) {
                    $container->addHidden('id');
                    $container->addSelect('floor', '', ['1' => '1st Floor', '2' => '2nd Floor'])
                            ->setRequired(false);
                    $container->addInteger('xloc', '')
                            ->setRequired(false);
                    $container->addInteger('yloc', '')
                            ->setRequired(false);
                    $container->addText('pos', '')
                            ->setRequired(false)
                            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Délka max %d znaků.', 8);
                    $container->addText('personact', '')
                            ->setRequired(false)
                            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Délka max %d znaků.', 32);
                    $container->addText('persondef', '')
                            ->setRequired(false)
                            ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Délka max %d znaků.', 32);
            };
        $this->getInlineEdit()->onSetDefaults[] = function($container, $item) {
                $container->setDefaults([
                      'id' => $item->id
                    , 'floor' => $item->floor
                    , 'xloc' => $item->xloc
                    , 'yloc' => $item->yloc
                    , 'pos' => $item->pos
                    , 'personact' => $item->personact
                    , 'persondef' => $item->persondef
                ]);
        };

        $p = $this->getParent();
        $this->getInlineEdit()->onSubmit[] = function($id, $values) use ($p) {
            unset($values['id']);
            $values['lmdt'] = new \DateTime();
            $values['lmchid'] = $this->getParent()->getUsername();
            $this->dtsrc->where('id', $id)->update($values);
            $p->redrawControl('autoCompleteStartup');
            $this->redrawControl();
        };
        
        $this->addInlineAdd()
                ->setClass('btn btn-xs')
                ->onControlAdd[] = function($container) {
                        //$container->addText('id', '')->setAttribute('readonly');
                        $container->addSelect('floor', '', ['1' => '1st Floor', '2' => '2nd Floor'])
                                ->setRequired(false);
                        $container->addInteger('xloc', '')
                                ->setRequired(false);
                        $container->addInteger('yloc', '')
                                ->setRequired(false);
                        $container->addText('pos', '')
                                ->setRequired(false)
                                ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Délka max %d znaků.', 8);
                        $container->addText('personact', '')
                                ->setRequired(false)
                                ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Délka max %d znaků.', 32);
                        $container->addText('persondef', '')
                                ->setRequired(false)
                                ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Délka max %d znaků.', 32);
                };
        
        $p = $this->getParent();
        $this->getInlineAdd()->onSubmit[] = function($values) use ($p) {
                /**
                 * Save new values
                 */
                $message = '';
                $fields = [];
                foreach($values as $key=>$value) {
                    $message .= $key . ': ' . $value . ', ';
                    $fields[$key] = $value;
                };
                $fields['lmdt'] = new \DateTime();
                $fields['lmchid'] = $p->getUsername();
                $out = $this->dtsrc->insert($fields);
                $message = trim($message, ', ');
                if ($out) {
                    $p->flashMessage("Record with values [$message] was added!", 'success');
                } else {
                    $p->flashMessage("Error adding record [$message]!", 'error');
                }
                $p->redrawControl('flashes');
                $p->redrawControl('autoCompleteStartup');
                $this->redrawControl();
                //$p->redrawControl('officeSchemaDatagrid-table');
        };
        
        
        $this->addExportCsv('Csv export', 'office.csv')
            ->setTitle('Csv export')
            ;
        $this->setColumnsHideable(false);
    }
    
    
}

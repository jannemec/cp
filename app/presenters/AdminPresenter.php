<?php
namespace App\Presenters;
/**
 * Description of AdminPresenter
 *
 * @author nemec
 */
class AdminPresenter extends BasePresenter {

    /** @var \jannemec\Sharepoint */
    protected $sharepointService;
    
    /**
     * Inject sharepoint service
     * @param \Model\Jannemec\Sharepoint
     */
    public function injectSharepointService(\Model\Jannemec\Sharepoint $sharepointService) {
        $this->sharepointService = $sharepointService;
    }
    
    /** @var \Model\Jannemec\UserRight */
    public $userRightService;

    /**
     * Inject userright service
     * @param \Model\Jannemec\UserRight
     */
    public function injectUserRightService(\Model\Jannemec\UserRight $userRightService) {
        $this->userRightService = $userRightService;
    }
    
    /** @var \Model\Infos */
    protected $infosService;
    
    /**
     * Inject infos service
     * @param \Model\Infos
     */
    public function injectInfosService(\Model\Infos $infosService) {
        $this->infosService = $infosService;
    }
    
    /**
     * functions runs before action or render method are taken 
     */
    protected function startup() {
        parent::startup();
    }

    public function actionDefault() {
        
    }
    
    protected function createComponentUserEditForm() {
        return(new \Controls\Admin\UserEditForm($this));
    }
    
    protected function createComponentUserGroupForm() {
        return(new \Controls\Admin\UserGroupForm($this));
    }
    
    protected function createComponentRightEditForm() {
        return(new \Controls\Admin\RightEditForm($this));
    }

    protected function createComponentRightGroupForm() {
        return(new \Controls\Admin\RightGroupForm($this));
    }
    
    protected function createComponentGroupEditForm() {
        return(new \Controls\Admin\GroupEditForm($this));
    }
    
    protected function createComponentLeftDirectory($name) {
        return(new \Controls\AdminLeftDirectory($this, 'leftDirectory'));
    }

    public function beforeRender() {
        parent::beforeRender();
    }

    public function renderDefault() {
        $this->template->title = $this->translator->translate('OTK GROUP, a.s. - intranet ');
        $this->template->page_title = $this->translator->translate('CZ intranet');
    }

    public function renderPhpinfo() {
        $this->template->title = $this->translator->translate('PHPInfo - ');
        $this->template->page_title = $this->translator->translate('Info o nastavení PHP'); 
    }
    
    public function renderNetteinfo() {
        $this->template->title = 'NetteInfo - ';
        $this->template->page_title = 'Nette framework';
        if ($this->isAjax()) {
            $this->defaultAjaxInvalidate();
        }
    }

    public function renderUser() {
        $this->template->title = 'Uživatel - ';
        $this->template->page_title = 'Info o uživateli';
        if ($this->isAjax()) {
            $this->defaultAjaxInvalidate();
        }
    }
    
    
    public function renderUsers($user_name = null) {
        $this->template->title = 'Přehled uživatelů ';
        $this->template->page_title = 'Uživatelé - editace';
        $this->template->users = [];
        foreach($this->userRightService->getUsers() as $key => $user) {
            $this->template->users[$key] = $user;
        };
        if (is_null($user_name)) {
            $this->template->user_name = '';
        } else {
            $this->template->user_name = $user_name;
        }
        
        if ($this->isAjax()) {
            $this->defaultAjaxInvalidate();
        }
    }

    public function actionEditUser($id) {
        $eUser = $this->userRightService->getUser($id);
        $this['userEditForm']->setEUser($eUser);
        $this['userGroupForm']->setEUser($eUser);
        if (!($eUser instanceOf \Nette\Database\Table\ActiveRow)) {
            $this->setView('default');
        } else {
            $this->template->page_title = 'Uživatel ' . $eUser->name;
            $this->template->eUser = $eUser; 
        }
    }
    public function renderEditUser($id) {
        $this->template->title = 'Editace uživatele ';
    }


    public function handleDeleteUser($id) {
        if ($id !== $this->user) {
            // Just check the user does not deletes himselves
            $this->userRightService->delUserById($id);
        } else {
            $this->flashMessage('You cannot delete yourself.', 'error');
        }
        
    }

    public function handleAddUser($user_name) {
        try {
            $user_name = trim(rawurldecode($user_name));
            if ($this->userRightService->getUserByUsername($user_name)) {
                $this->flashMessage('This username already exists', 'error');
            } elseif (mb_strlen($user_name) < 4) {
                $this->flashMessage('Minimum username length is 5 chars', 'error');
            } else {
                $user = $this->userRightService->createUser(array('username' => $user_name, 'name' => $user_name));
                $this->redirect('editUser', array('id' => $user->id));
            }
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    /**
     * ===============================================================
     */
    public function renderRights($right_name = null) {
        $this->template->title = 'Přehled oprávnění/zdrojů ';
        $this->template->page_title = 'Zdroje - editace';
        $this->template->rights = $this->userRightService->getRights();
        $this->template->right_name = is_null($right_name) ? '' : strval($right_name);
        if ($this->isAjax()) {
            $this->defaultAjaxInvalidate();
        }
    }

    public function actionEditRight($id) {
        $eRight = $this->userRightService->getRight($id);
        $this['rightEditForm']->setERight($eRight);
        $this['rightGroupForm']->setERight($eRight);
        if (!($eRight instanceOf \Nette\Database\Table\ActiveRow)) {
            $this->setView('default');
        } else {
            $this->template->page_title = 'Oprávnění ' . $eRight->name;
            $this->template->eRight = $eRight;
        }
    }
    public function renderEditRight($id) {
        $this->template->title = 'Editace oprávnění/zdroje '; 
    }

    public function handleDeleteRight($id) {
        $right = $this->userRightService->getRight($id);
        if (!is_null($right) && !in_array($right->name, array('Admin', 'admin', 'user', 'guest'))) {
            // Just check the user does not deletes (admin, guest, user) right
            $this->userRightService->delRightById($id);
        } else {
            $this->flashMessage('You cannot delete this default rights.', 'error');
        }
    }

    public function handleAddRight($right_name) {
        try {
            $right_name = trim(rawurldecode($right_name));
            if ($this->userRightService->getRightByName($right_name)) {
                $this->flashMessage('This right already exists', 'error');
            } elseif (mb_strlen($right_name) < 2) {
                $this->flashMessage('Minimum right name length is 2 chars', 'error');
            } else {
                $right = $this->userRightService->createRight(array('name' => $right_name));
                $this->redirect('editRight', array('id' => $right->id));
            }
        } catch (FlashEntityException $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    /**
     * ===============================================================
     */
    public function renderGroups($group_name = null) {
        $this->template->title = 'Přehled skupin/rolí ';
        $this->template->page_title = 'Role - editace';
        $this->template->groups = $this->userRightService->getGroups();
        $this->template->group_name = is_null($group_name) ? '' : strval($group_name);
        if ($this->isAjax()) {
            $this->defaultAjaxInvalidate();
        }
    }

    public function actionEditGroup($id) {
        $eGroup = $this->userRightService->getGroup($id);
        $this['groupEditForm']->setEGroup($eGroup);
        $this['rightGroupForm']->setEGroup($eGroup);
        if (!($eGroup instanceOf \Nette\Database\Table\ActiveRow)) {
            $this->redirect('default');
        } else {
            $this->template->page_title = 'Role ' . $eGroup->name;
            $this->template->eGroup = $eGroup;
        }
    }
    public function renderEditGroup($id) {
        $this->template->title = 'Editace skupiny/role ';
    }

    public function handleDeleteGroup($id) {
        $group = $this->userRightService->getGroup($id);
        if (!is_null($group) && !in_array($group->name, array('Admin', 'admin', 'guest'))) {
            // Just check the user does not deletes (admin, guest) group
            $this->userRightService->delGroupById($id);
        } else {
            $this->flashMessage('You cannot delete this default groups.', 'error');
        }
    }
    
    public function handleAddGroup($group_name) {
        try {
            $group_name = trim(rawurldecode($group_name));
            if ($this->userRightService->getGroupByName($group_name)) {
                $this->flashMessage('This group already exists', 'error');
            } elseif (mb_strlen($group_name) < 5) {
                $this->flashMessage('Minimum group name length is 5 chars', 'error');
            } else {
                $group = $this->userRightService->createGroup(array('name' => $group_name));
                $this->redirect('editGroup', array('id' => $group->id));
            }
        } catch (FlashEntityException $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }
    }
    
    //==========================================================================
    
    
    public function renderTest() {
        
        
        // Vytvoření nového projektu
        $projects = $this->infosService->getProjects(false);
        $counter = 100;
        foreach($projects as $project) {
            //\Tracy\Debugger:: dump($project);exit;
            $shpProj = $this->sharepointService->getITProject(trim($project['phproj']));
            if (empty($shpProj)) {
                // Založení záznamu
                $record = [
                      'name' => trim($project->phdesig1)
                    , 'description' => trim($project->phdesig2)
                    , 'pid' => trim($project->phproj)
                    , 'person' => trim($project->userid)
                    , 'contract' => trim($project->phcontract)
                ];
                $out = $this->sharepointService->addITProject($record);
                //\Tracy\Debugger::dump($out); exit;
                $counter--;
                if ($counter <= 0) {
                    break;
                }
            } else {
                // Konrola/aktualizace záznamu
                $update = [];
                if (trim($project->userid) != $shpProj->Person) {
                    $update['Person'] = trim($project->userid);
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
                if (!empty($update)) {
                    //\Tracy\Debugger::dump($update); exit;
                    $out = $this->sharepointService->updateITProject($shpProj, $update);
                    $counter--;
                    if ($counter <= 0) {
                        break;
                    }
                }
            }
                
            //\Tracy\Debugger:: dump($shpProj);exit;
        }
        
        // Zobrazení projektů
        $this->terminate();
        $itProjects = $this->sharepointService->getITProjects();
        \Tracy\debugger::dump($itProjects);
        
        
        // Zobrazení všech subsite
        $this->terminate();
        $this->sharepointService->getProjectSites();
        $this->sharepointService->testOnly();
        
        
        
        // Ověření připojení na AD
        $this->terminate();
        $t = $this->adService;
        \Tracy\Debugger::dump($this->adService); exit;
    }
}
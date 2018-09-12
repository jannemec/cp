<?php
/**
 * Description of topMenu
 *
 * @author nemec
 */

namespace Controls;

class TopMenu extends \Nette\Application\UI\Control {
    
    public function __construct($parent = null, $name = null) {   
        parent::__construct();
        $parent->addComponent($this, $name);
    }
    
    public function render() {    
        
        $this->template->setFile(dirname(__FILE__) . '/templates/TopMenu.latte');
        $this->template->menu = [];
        
        $this->template->menu[0] = [
              'link' => $this->parent->link('Home:default')
            , 'txt' => $this->parent->getTranslator()->translate('Úvod')
            , 'description' => $this->parent->getTranslator()->translate('Úvodní stránka')
            , 'aclass' => 'ajax'
            , 'submenu' => [
                      [
                        'link' => 'https://spgcz.sharepoint.com/SitePages/DomovskaStranka.aspx'
                        , 'txt' => $this->parent->getTranslator()->translate('Sharepoint Casale')
                        , 'description' => $this->parent->getTranslator()->translate('Sharepoint úložiště')
                        , 'submenu' => [ ]
                    ]
                    , [
                        'link' => 'https://spgcz.sharepoint.com/sites/projekty/Lists/Projekty/AllItems.aspx'
                        , 'txt' => $this->parent->getTranslator()->translate('Sharepoint Project Portal')
                        , 'description' => $this->parent->getTranslator()->translate('Sharepoint projektový portál')
                        , 'submenu' => [ ]
                    ]
                    , [
                        'link' => 'https://capoksys01.chpn.cz/okbase/welcome.action'
                        , 'txt' => $this->parent->getTranslator()->translate('OKbase')
                        , 'description' => $this->parent->getTranslator()->translate('Docházkový systém')
                        , 'submenu' => [ ]
                    ]
                    , [
                        'link' => 'https://sdpondemand.manageengine.com/app/itdesk/HomePage.do'
                        , 'txt' => $this->parent->getTranslator()->translate('IT Helpdesk')
                        , 'description' => $this->parent->getTranslator()->translate('IT Helpdesk')
                        , 'submenu' => [ ]
                    ]
                ]
        ];
        
        if ($this->getParent()->getUser()->isAllowed('Admin')) {
            $this->template->menu[0]['submenu'][] = [
                'link' => $this->parent->link('Home:companyStructure')
                , 'txt' => $this->parent->getTranslator()->translate('Struktura firmy')
                , 'description' => $this->parent->getTranslator()->translate('Firemní struktura')
                , 'submenu' => [ ]
            ];
        };
        
        // ---------------------------------------------------------------------------------------------------------
        // Admin
        // ---------------------------------------------------------------------------------------------------------
        
        if ($this->parent->user->isAllowed('Admin')) {
            $this->template->menu[99] = [
                'txt' => $this->parent->getTranslator()->translate('Admin')
                //, 'link' => $this->parent->link('Production:default')
                //, 'aclass' => 'ajax'
                , 'submenu' => [
                      [
                        'link' => $this->parent->link('Admin:phpinfo')
                        , 'txt' => $this->parent->getTranslator()->translate('PHPInfo')
                        , 'description' => $this->parent->getTranslator()->translate('Informace o nastavení PHP')
                    ]
                    , [
                        'link' => $this->parent->link('Admin:netteInfo')
                        , 'txt' => $this->parent->getTranslator()->translate('Nette Info')
                        , 'description' => $this->parent->getTranslator()->translate('Informace o systému Nette')
                    ]
                    , [
                        'link' => $this->parent->link('Admin:user')
                        , 'txt' => $this->parent->getTranslator()->translate('Uživatel')
                        , 'description' => $this->parent->getTranslator()->translate('Informace o uživateli')
                    ]
                    , [
                        'link' => $this->parent->link('Admin:users')
                        , 'txt' => $this->parent->getTranslator()->translate('Uživatelé')
                        , 'description' => $this->parent->getTranslator()->translate('Administrace uživatelů')
                    ]
                    , [
                        'link' => $this->parent->link('Admin:groups')
                        , 'txt' => $this->parent->getTranslator()->translate('Skupiny/role')
                        , 'description' => $this->parent->getTranslator()->translate('Administrace rolí')
                    ]
                    , [
                        'link' => $this->parent->link('Admin:rights')
                        , 'txt' => $this->parent->getTranslator()->translate('Rights/zdroje')
                        , 'description' => $this->parent->getTranslator()->translate('Administrace zdrojů')
                    ]
                    , [
                        'link' => '/phpmyadmin'
                        , 'txt' => $this->parent->getTranslator()->translate('PHPMyAdmin')
                        , 'description' => $this->parent->getTranslator()->translate('Administrace dbf')
                    ]
                    , [
                        'link' => 'http://sharepoint.otk.cz:5000/default.aspx'
                        , 'txt' => $this->parent->getTranslator()->translate('Sharepoint admin')
                        , 'description' => $this->parent->getTranslator()->translate('Administrace sharepoint serveru')
                    ]
                    , [
                        'link' => $this->parent->link('Admin:test')
                        , 'txt' => $this->parent->getTranslator()->translate('!!TEST')
                        , 'description' => $this->parent->getTranslator()->translate('Jen pro testování')
                    ]
                ]
            ];
        }
        
        
        
        $this->template->render();
    }
    
    public static function showLink($link, $level = 0) {
        $out = $level == 0 ? '<ul class="mymenu">' : '';
        $submenu = isset($link['submenu']) && is_array($link['submenu']) && (count($link['submenu']) > 0);
        //NDebugger::dump($link); exit;
        $out .= '<li' . (isset($link['class']) ? (' class="' . $link['class'] . '"') : '') . '>' 
                . (isset($link['link']) ? ('<a href="' . $link['link'] . '"' 
                . (isset($link['aclass']) ? (' class="' . $link['aclass'] . '"') : '')  
                . (isset($link['description']) ? (' title="' . $link['description'] . '"') : '') . '>') : '')
                . $link['txt']
                . (isset($link['link']) ? '</a>' : '');
        if ($submenu) {
            $out .= '<ul class="submenu">';
            foreach($link['submenu'] as $sublink) {
                $out .= self::showLink($sublink, $level + 1);
            }
            $out .= '</ul>';
        }
        $out .= '</li>' . ($level == 0 ? '</ul>' : '');
        return($out);
        
    }
}
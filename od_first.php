<?php

if(!defined('_PS_VERSION_')){
    exit;
}

use OrbitaDigital\OdFirst\Resources;
require __DIR__ .'/vendor/autoload.php';

class Od_first extends Module{
    /**
     * Name of the module
     */
    const MODULE_ADMIN_CONTROLLER = 'AdminOdFirst';
    public function __construct()
    {
        $this->name = 'od_first';
        $this->tab = 'front_office_features';
        $this->version = '1.1.3';
        $this->author = 'alejandro';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => '1.7.99',
        ];
        $this->bootstrap = true;

        parent::__construct();
        //This is displayed in the module manager with the version and author
        $this->displayName = $this->l('od_first module');
        $this->description = $this->l('Small application to control users');

        $this->confirmUnistall = $this->l('Are you sure you want to unistall?');

    }
    /**
     * Installing the module.
     */
    public function install(){
            return
            parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionAdminControllerSetMedia')
            && Resources::generateTable();
    }
    /**
     * Unistall the module
     */
    public function uninstall(){
        
        return 
        parent::uninstall()
            && Resources::removeTable()
            && $this->unregisterHook('displayHeader')
            && $this->unregisterHook('actionAdminControllerSetMedia');
    }
    
    public function hookActionAdminControllerSetMedia(){
        //TODO do something
    }
    public function hookDisplayHeader(){
        $this->context->smarty->assign([
            'od_hello' => 'Hola estoy utilizando el hook del header en el modulo od_first',
            'od_bad' => 'si no existe el hello',
        ]);
        return $this->display(__FILE__, '/templates/views/hook/od_first.tpl');
    }
    /**
     * Redirect to the admin controller link
     */
    public function getContent(){
        Tools::redirectAdmin(($this->context->link->getAdminLink(static::MODULE_ADMIN_CONTROLLER)));
    }
     

    
}

<?php

if(!defined('_PS_VERSION_')){
    exit;
}

use OrbitaDigital\OdFirst\Resources;

require_once __DIR__ .'/vendor/autoload.php';

class Od_first extends Module{
    /**
     * Name of the module
     */
    const MODULE_ADMIN_CONTROLLER = 'AdminOdFirst';
    public function __construct()
    {
        $this->name = 'od_first';
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
            Resources::generateTable()
            && $this->registerHook('actionAdminControllerSetMedia')
            && parent::install();
    }
    /**
     * Unistall the module
     */
    public function uninstall(){
        
        return 
        Resources::removeTable()
        && $this->unregisterHook('actionAdminControllerSetMedia')
        && parent::uninstall();
    }
    public function hookActionAdminControllerSetMedia(){
        $this->context->controller->addJs(_PS_MODULE_DIR_.'od_first/views/js/odjs.js');
        $this->context->controller->addCSS(_PS_MODULE_DIR_.'od_first/views/css/styles.css');
    }
    /**
     * Redirect to the admin controller link
     */
    public function getContent(){
        Tools::redirectAdmin(($this->context->link->getAdminLink(static::MODULE_ADMIN_CONTROLLER)));
    }
     

    
}

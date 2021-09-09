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
        $this->version = '1.1.6';
        $this->author = 'alejandro';
        // The need_instance variable is used to indicate whether an instance of the module needs to be created when the Modules tab is loaded. If the value is 0, then the module will not be loaded, which will save time and memory. If the value is 1, then the module will be loaded. !!If your module may need to display a warning message on the Modules tab!!, then you should choose the value 1. Otherwise, choose 0 to save time and memory.
        // Por lo que leí pierdes los "warnings" de tu modulo, yo preferiria que me indicase que el modulo falla
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
            && parent::install()
            && $this->registerHook('actionAdminControllerSetMedia');
    }
    /**
     * Unistall the module
     */
    public function uninstall(){
        
        return 
        Resources::removeTable()
        && parent::uninstall()
        && $this->unregisterHook('actionAdminControllerSetMedia');
    }
    public function hookActionAdminControllerSetMedia(){
        if ($this->context->controller->controller_name == 'AdminOdFirst'){
            $this->context->controller->addJs(_PS_MODULE_DIR_.'od_first/views/js/odjs.js');
            $this->context->controller->addCSS(_PS_MODULE_DIR_.'od_first/views/css/styles.css');
            $this->context->controller->addCSS('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css');
        }
        
    }
    /**
     * Redirect to the admin controller link
     */
    public function getContent(){
        Tools::redirectAdmin(($this->context->link->getAdminLink(static::MODULE_ADMIN_CONTROLLER)));
    }
     

    
}

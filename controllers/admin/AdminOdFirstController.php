<?php

use OrbitaDigital\OdFirst\Resources;
use OrbitaDigital\OdFirst\Fields;

class AdminOdFirstController extends ModuleAdminController{
    /**
     * $this->errors
     * $this->confirmations
     * $this->warnings
     * $this->informations
     */
    protected $currentTab = 1;
    public function __construct(){
        $this->bootstrap = true;
        $this->name = 'odfirst';
        $this->module = 'od_first';
        $this->table = 'od_first';
        parent::__construct();
    }

    /**
     * Process the Add user button which add users to the database
     */
    public function ajaxProcessAddValues(){
        $ver = new Resources();
        $jsonData = json_decode($_GET['dataString']);
        $array_verify = ["name" => $jsonData[0],"age" => $jsonData[1],"date" => $jsonData[2]];
        $result = $ver->add($array_verify);
        $this->ajaxDie(json_encode($result));
    }
    public function ajaxProcessChangeRemoved(){
        $user_id = json_decode($_GET['dataString']);
        $result = Resources::changeRemoved($user_id);
        $this->ajaxDie(json_encode($result));
    }
    public function ajaxProcessFindUser(){
        $user_id = json_decode($_GET['dataString']);
        $result = Resources::findUser($user_id);
        $this->ajaxDie(json_encode($result));
    }
    /**
     * Process the verify button which verify the add users fields
     */
    public function ajaxProcessVerifyValues(){
        $jsonData = json_decode($_GET['dataString']);
        $array_verify = ["name" => $jsonData[0],"age" => $jsonData[1],"date" => $jsonData[2]];
        $result = Resources::validate($array_verify);
        $this->ajaxDie(json_encode($result));
    }
    /**
     * Saves values into the database after verification
     */
    public function ajaxProcessModifyValues(){
        $ver = new Resources();
        $jsonData = json_decode($_GET['dataString']);
        $array_verify = ["id" => $jsonData[0],"name" => $jsonData[1],"age" => $jsonData[2],"date" => $jsonData[3]];
        $result = $ver->save($array_verify);
        echo json_encode($result);
    }
    public function ajaxProcessCurrentNav(){
        $jsonData = json_decode($_GET['dataString']);
        switch ($jsonData){
            case '#form':
                setcookie('navSelected',1);
                break;
            case '#table':
                setcookie('navSelected',2);
                break;
            case '#modify':
                setcookie('navSelected',3);
                break;
        }
    }
    /**
     * Generate content when loading the controller
     */
    public function initContent()
    {
        $this->displayTabs();
        /**
         * Locate the template in fetch as string and then, is assigned in content.
         */
        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_.'od_first/views/templates/admin/od_admin.tpl');
        $this->context->smarty->assign(array(
            'content' => $content,
        ));
        
    }
    /**
     * Display the entire content of the admin page.
     */
    public function displayTabs(){
        $this->checkOperations();
        $output = '';
        $navHeader = $this->generateNavHead();
        $navBody = $this->generateNavBody();
        $output = $navHeader.$navBody;
        /**
         * Smarty assign mainbody (which is used in od_admin.tpl) which is the entire content string format
         */
        $this->context->smarty->assign([
        'mainBody' => $output,
        ]);
    }
    /**
     * Checks for various submits or actions are pressed.
     */
    public function checkOperations(){
        if(Tools::isSubmit('submitResetodfirst')){
            $this->context->controller->informations[] = "Filters reseted";
            //unset the filters
            $this->processResetFilters();
        }
        //Delete(Remove) and update the table
        else if(Tools::isSubmit('deleteodfirst')){            
            $done = Resources::deleteUser($_GET['ID']);
            if($done == false){
                $this->context->controller->informations[] = "User is already removed";
            }
            else{
                ($done == true) ? $this->context->controller->informations[] = "User removed" : $this->context->controller->errors[] = "Error processing the information (query error)";
            }
        }
        //If modify button is pressed
        else if(Tools::isSubmit('updateodfirst')){
            setcookie('navSelected',3);
            //obtain the url, removes the updateodfirst and return the header without it
            $url = $_SERVER['REQUEST_URI'];
            $newUrl = preg_replace('/(\\?|&)updateodfirst=.*?(&|$)/','',$url);
            header("Location: ".$newUrl);
        }
        if(isset($_COOKIE['navSelected'])){
            $this->currentTab = $_COOKIE['navSelected'];
        }
    }
    /**
     * Display the formulary in the first nav.
     * @return string containing the entire formulary
     */
    public function displayForm(){
        
        $helper = new HelperForm();
        
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->name_controller = $this->name;
        $helper->submit_action = 'submit' . $this->name;
        $helper->table = $this->table;
        $helper->token = Tools::getAdminTokenLite('AdminOdFirst');

        Media::addJsDef(array(
            'admin_od' => $this->context->link->getAdminLink('AdminOdFirst')
        ));
        return $helper->generateForm([$this->formFields()]);
    }
    /**
     * Display the entire table, second nav.
     */
    public function displayTable(){
        $query = 'SELECT * FROM `'._DB_PREFIX_.$this->table.'`';
        //If there's no filters, query return everything (don't enter here)
        if(Tools::isSubmit('submitFilter')){
            $whereStr = Resources::getFilters($_POST);
            //search is pressed but all the field are empty
            if(!is_null($whereStr)){
                $query = $query.' WHERE '.$whereStr;
            }
        }
        //If the arrows to order the table are pressed, order the table.
        if(Tools::getIsset('odfirstOrderby')){
        $orderDir = $this->checkOrderDirection(Tools::getValue('odfirstOrderway','desc'));
        $order = $this->checkOrderBy(Tools::getValue('odfirstOrderby','ID'));
        $ordering = ' ORDER BY '.$order.' '.$orderDir;
        $query .= $ordering;
        }
        $result = Db::getInstance()->executeS($query);
        
        $helper = new HelperList();
        $helper->actions = array('edit','delete');
        $helper->identifier = 'ID';
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->orderBy = $order;
        $helper->orderWay = $orderDir;
        $helper->show_toolbar = true;
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->table = $this->name;
        $helper->title = 'User listed';
        $helper->token = Tools::getAdminTokenLite('AdminOdFirst');

        //Total of registers
        $helper->listTotal = count($result);
        //page and selected_pagination (limit) is saved in POST.
        $page = ($page = Tools::getValue('submitFilter' . $helper->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper->table . '_pagination')) ? $pagination : 50;
        
        //If not here, table isn't updated
        //starting row/user
        $calc_page = ($page-1) * $pagination;
        //Pagination limit.
        $limit = ' LIMIT '.$calc_page.','.$pagination;
        $query .= $limit;
        $result = Db::getInstance()->executeS($query);

        return $helper->generateList($result,$this->tableFields());
    }
    /**
     * The content of modify tab, third nav
     * @return string string containing the entire tab
     */
    public function displayModify(){

        $helper = new HelperForm();
        
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->name_controller = $this->name;
        $helper->submit_action = 'submit' . $this->name;
        $helper->table = $this->table;
        $helper->token = Tools::getAdminTokenLite('AdminOdFirst');
        //Avoid obtaining the ID for the modify tab when deleting an user
        if(!Tools::getIsset('deleteodfirst')){
            $id = Tools::getValue('ID');
        }
        if(Tools::getIsset('submitodfirst')){
            $id = Tools::getValue('find_id');
        }
        if($id != null){
            $query_mod = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'od_first WHERE ID='.$id);
        
            $helper->fields_value = array(
                'mod_id' => $query_mod[0]['ID'],
                'mod_name' => $query_mod[0]['name'],
                'mod_age' => $query_mod[0]['age'],
                'mod_date' => $query_mod[0]['date_birth'],
                'mod_date_add' => $query_mod[0]['date_add'],
                'mod_date_upd' => $query_mod[0]['date_upd'],
                'mod_date_del' => $query_mod[0]['date_del'],
            );
        }
        Media::addJsDef(array(
            'admin_od' => $this->context->link->getAdminLink('AdminOdFirst')
        ));
        return $helper->generateForm([$this->modifyFields()]);
    }
    /**
     * Display a small field with id to find an user inside modify
     * @return string string containing the field with a button
     */
    public function displayModifyId(){
        $form = [
            'form' => [
                'input' => [
                    Fields::createFormField('text','ID','find_id','Enter ID','id'),
                ], 
                'submit' => [
                    'title' => 'Find user',
                    'class' => 'btn btn-default pull-left',
                ]
            ],
        ];
        $helper = new HelperForm();
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->name_controller = $this->name;
        $helper->submit_action = 'submit' . $this->name;
        $helper->table = $this->table;
        $helper->token = Tools::getAdminTokenLite('AdminOdFirst');

        Media::addJsDef(array(
            'admin_od' => $this->context->link->getAdminLink('AdminOdFirst')
        ));
        return $helper->generateForm([$form]);
    }
    /**
     * Generate the nav with the tabs
     * @return string $output html string with the nav
     */
    public function generateNavHead(){
            $output = '';
            $output .= '
            <ul class="nav nav-tabs" id="nav-tab" role="tablist">';
                //**Changes active from the li
                ($this->currentTab == 1) ? $output .= '<li class="nav-item active">' : $output .= '<li class="nav-item">';
                $output .= '<a class="nav-link" id="form-tab" data-toggle="tab" href="#form" role="tab" aria-controls="form" aria-selected="true">Add users</a>
                </li>';
                ($this->currentTab == 2) ? $output .= '<li class="nav-item active">' : $output .= '<li class="nav-item">';
                $output .= '<a class="nav-link" id="table-tab" data-toggle="tab" href="#table" role="tab" aria-controls="table" aria-selected="false">Views users</a>
                </li>';
                ($this->currentTab == 3) ? $output .= '<li class="nav-item active">' : $output .= '<li class="nav-item">';
                $output .= '<a class="nav-link" id="modify-tab" data-toggle="tab" href="#modify" role="tab" aria-controls="modify" aria-selected="false">Modify user</a></li>
            </ul>';
            $output .= '<div class="tab-content" id="tabsBody">';
            return $output;
    }
    /**
     * Generate the diferent tabs for the body
     * @return string $navBody html code of the tabs
     */
    public function generateNavBody(){
        $navBody = '';

        ($this->currentTab == 1) ? $navBody .= '<div class="tab-pane active"' : $navBody .= '<div class="tab-pane"' ;
            $navBody .= '" role="tabpanel" id="form" aria-labelledby="form-tab" >'.$this->displayForm().'</div>';
        ($this->currentTab == 2) ? $navBody .= '<div class="tab-pane active"' : $navBody .= '<div class="tab-pane"' ;
            $navBody .= '<div class="tab-pane" role="tabpabel" id="table" aria-labelledby="table-tab">'.$this->displayTable().'</div>';
        ($this->currentTab == 3) ? $navBody .= '<div class="tab-pane active"' : $navBody .= '<div class="tab-pane"' ;
            $navBody .= '<div class="tab-pane" role="tabpabel" id="modify" aria-labelledby="modify-tab">'.$this->displayModify().$this->displayModifyId().'</div>
            </div>';
        return $navBody;
    }
    /**
     * Generates first tab fields
     */
    public function formFields(){
        $form = [
            'form' => [
                'input' => [
                    Fields::createFormField('text','Name','form_name'),
                    Fields::createFormField('text','Age','form_age','Only numbers accepted'),
                    Fields::createFormField('date','Birth date','form_date'),
                ],
                'buttons' => [
                    Fields::createButton('btnSave','btnSave','Add user','btn-success'),
                    Fields::createButton('btnVerify','btnVerify','Verify fields','btn-info'),
                ],
            ],
        ];
        return $form;
    }
    /**
     * Generates second tab fields
     */
    public function tableFields(){
        $table_list = array(
            'ID' => Fields::createTableField('ID',100,'text','int','',true,false),
            'name' => Fields::createTableField('Name',100,'text','','',true,true,true),
            'age' => array(
                'title' => 'Age',
                'width' => 100,
                'type' => 'number',
                'orderby' => true,
                'search' => false,
                'suffix' => 'years',
                'callback' => 'checkAge',
                'callback_object' => $this,
            ),
            'date_birth' => Fields::createTableField('Date',150,'date'),
            'date_add' => Fields::createTableField('Creation date',200,'datetime'),
            'date_upd' => Fields::createTableField('Update date',200,'datetime'),
            'date_del' => Fields::createTableField('Remove date',200,'datetime'),
            'removed' => array(
                'title' => 'Deleted',
                'width' => 200,
                'type' => 'bool',
                'callback' => 'checkIcons',
                'callback_object' => $this,
                'filter_type' => 'bool',
            ),
        );
        return $table_list;
    }
    /**
     * Generate third tab fields
     */
    public function modifyFields(){
        $modFields = [
            'form' => [
                'input' => [
                    Fields::createFormField('text','ID','mod_id','','id',true),
                    Fields::createFormField('text','Name','mod_name','','name',false,true),
                    Fields::createFormField('text','Age','mod_age','Only numbers accepted','age',false,true),
                    Fields::createFormField('date','Date','mod_date','','date','',true),
                    Fields::createFormField('text','Added at','mod_date_add','','creation_date',true),
                    Fields::createFormField('text','Last updated','mod_date_upd','','date_upd',true),
                    Fields::createFormField('text','Deleted at','mod_date_del','','date_del',true),
                ],
                'buttons' => [
                    Fields::createButton('btnEdit','btnEdit','Update user'),
                ],
            ],
        ];
        return $modFields;
    }
     /**
     * Limit the age to 100 if it's over this number
     * @return int $value the age or 100
     */
    public function checkAge($value){
        if($value > 100){
            return 100;
        }
        else{
            return $value;
        }
    }
    /**
     * Initialize icons, 0 non deleted, 1 deleted.
     */
    public function checkIcons($value){
        return ($value==0) ? '<i class="bi bi-x-lg text-danger font-medium"></i>' : '<i class="bi bi-check-lg text-success font-medium"></i>';
    }
}
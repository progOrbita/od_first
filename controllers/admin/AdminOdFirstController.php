<?php

use OrbitaDigital\OdFirst\Resources;

class AdminOdFirstController extends ModuleAdminController{
    /**
     * $this->errors
     * $this->confirmations
     * $this->warnings
     * $this->informations
     */
    protected $currentTab = 1;
    protected $modify_Id;
    public function __construct(){
        $this->bootstrap = true;
        $this->name = 'odfirst';
        $this->module = 'od_first';
        $this->table = 'odFirst';
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
        echo json_encode($result);
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
            case '#adding':
                setcookie('navSelected',1);
                break;
            case '#table':
                setcookie('navSelected',2);
                break;
            case '#modify':
                setcookie('navSelected',3);
                break;
            default:
                setcookie('navSelected',1);
                break;
        }
        echo json_encode($this->currentTab);
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
        
        $this->currentTab = 1;
        
        if(Tools::isSubmit('submitResetodfirst')){
            $this->context->controller->informations[] = "Filters reseted";
            //unset the filters
            $this->processResetFilters();
        }
        //Delete(Remove) and update the table
        else if(Tools::isSubmit('deleteodfirst')){            
            $done = Resources::deleteUser($_GET['ID']);
            setcookie('navSelected',2);
            if($done == 'removed'){
                $this->context->controller->informations[] = "User is already removed";
            }
            else{
                ($done == true) ? $this->context->controller->informations[] = "User removed" : $this->context->controller->errors[] = "Error processing the information (query error)";
            }
        }
        //If the search filter, the arrow to order any field, or page is selected load the table tab as currentTab
        else if(Tools::isSubmit('submitFilter') || Tools::getIsset('odfirstOrderby') || Tools::getIsset('page')){
            setcookie('navSelected',2);
            $this->currentTab = 2;
            echo 'cookie '.$_COOKIE['navSelected'];
        }
        //If modify button is pressed
        else if(Tools::isSubmit('updateodfirst')){
            $this->Modify_id = Tools::getValue('ID');
            setcookie('navSelected',3);
            $this->currentTab = 3;
            unset($_GET['updateodfirst']);
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
        $id = $this->Modify_id;
        if($id != null){
        $query_mod = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'odFirst WHERE ID='.$id);
        
            $helper->fields_value = array(
                'mod_id' => $query_mod[0]['ID'],
                'mod_name' => $query_mod[0]['name'],
                'mod_age' => $query_mod[0]['age'],
                'mod_date' => $query_mod[0]['date'],
                'mod_creation_date' => $query_mod[0]['creation_date'],
                'mod_mod_date' => $query_mod[0]['mod_date'],
                'mod_del_date' => $query_mod[0]['del_date'],

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
                    [
                    'type' => 'text',
                    'label' => 'ID',
                    'name' => 'find_id',
                    'class' => 'id',
                    'size' => '10',
                    'desc' => 'Enter ID',
                    ],
                ], 
                'buttons' => [
                    [
                    'type' => 'button',
                    'id' => 'btnFind',
                    'name' => 'findButton',
                    'title' => 'Find User',
                    ],
                ],
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
     * Generates first tab fields
     */
    public function formFields(){
        $form = [
            'form' => [
                'input' => [
                    [
                    'type' => 'text',
                    'label' => 'Name',
                    'name' => 'name',
                    ],
                    [
                    'type' => 'text',
                    'label' => 'Age',
                    'name' => 'age',
                    'desc' => 'Only numbers accepted',
                    ],
                    [
                    'type' => 'date',
                    'label' => 'Birth date',
                    'name' => 'date',
                    ],
                ],
                'buttons' => [
                    [
                    'type' => 'button',
                    'id' => 'btnSubmit',
                    'name' => 'commonButton',
                    'title' => 'Add user',
                    ],
                    [
                    'type' => 'button',
                    'id' => 'btnVerify',
                    'class' => 'btn btn-success',
                    'name' => 'btnVerify',
                    'title' => 'Verify fields'
                    ],
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
            'ID' => array(
                'title' => 'ID',
                'width' => 100,
                'type' => 'text',
                'orderby' => true,
                'search' => false,
                'filter_type' => 'int',
            ),
            'name' => array(
                'title' => 'Name',
                'width' => 100,
                'type' => 'text',
                'orderby' => true,
                'havingFilter' => true,
            ),
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
            'date' => array(
                'title' => 'Date',
                'width' => 150,
                'type' => 'date',
            ),
            'creation_date' => array(
                'title' => 'Creation date',
                'width' => 200,
                'type' => 'datetime',
            ),
            'mod_date' => array(
                'title' => 'Modification date',
                'width' => 200,
                'type' => 'datetime',
            ),
            'del_date' => array(
                'title' => 'Remove date',
                'width' => 200,
                'type' => 'datetime',
            ),
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
                    [
                    'type' => 'text',
                    'label' => 'ID',
                    'name' => 'mod_id',
                    'class' => 'id',
                    'disabled' => true,
                    ],
                    [
                    'type' => 'text',
                    'label' => 'Name',
                    'name' => 'mod_name',
                    'class' => 'name',
                    'required' => true,
                    ],
                    [
                    'type' => 'text',
                    'label' => 'Age',
                    'name' => 'mod_age',
                    'class' => 'age',
                    'required' => true,
                    'desc' => 'only numbers accepted',
                    ],
                    [
                    'type' => 'date',
                    'label' => 'Date',
                    'name' => 'mod_date',
                    'class' => 'date',
                    'required' => true,
                    ],
                    [
                    'type' => 'text',
                    'label' => 'Created at',
                    'name' => 'mod_creation_date',
                    'class' => 'creation_date',
                    'disabled' => true,
                    ],
                    [
                    'type' => 'text',
                    'label' => 'last modified',
                    'name' => 'mod_mod_date',
                    'class' => 'mod_date',
                    'disabled' => true,
                    ],
                    [
                    'type' => 'text',
                    'label' => 'deleted at',
                    'name' => 'mod_del_date',
                    'class' => 'del_date',
                    'disabled' => true,
                    ],
                ],
                'buttons' => [
                    [
                    'type' => 'button',
                    'id' => 'btnEdit',
                    'name' => 'commonButton',
                    'title' => 'Update user',
                    ],
                ],
            ],
        ];
        return $modFields;
    }
    public function checkIcons($value){
        return ($value==0) ? '<i class="bi bi-x-lg text-danger"></i>' : '<i class="bi bi-check-lg text-success"></i>';
    }
}
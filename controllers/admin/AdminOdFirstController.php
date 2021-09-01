<?php

use OrbitaDigital\OdFirst\Resources;
use Symfony\Component\Validator\Constraints\IsNull;

class AdminOdFirstController extends ModuleAdminController{
    /**
     * $this->errors
     * $this->confirmations
     * $this->warnings
     * $this->informations
     */
    protected $active = 1;
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
        echo json_encode($result);
    }
    /**
     * Process the verify button which verify the add users fields
     */
    public function ajaxProcessVerifyValues(){
        $jsonData = json_decode($_GET['dataString']);
        $array_verify = ["name" => $jsonData[0],"age" => $jsonData[1],"date" => $jsonData[2]];
        $result = Resources::validate($array_verify);
        echo json_encode($result);
    }
    public function ajaxProcessModifyValues(){
        $ver = new Resources();
        $jsonData = json_decode($_GET['dataString']);
        $array_verify = ["id" => $jsonData[0],"name" => $jsonData[1],"age" => $jsonData[2],"date" => $jsonData[3]];
        $result = $ver->save($array_verify);
        echo json_encode($result);
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
        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_.'od_first/templates/views/admin/od_admin.tpl');
        $this->context->smarty->assign(array(
            'content' => $content,
        ));
        
    }
    /**
     * Display the entire content of the adminControllerModule.
     */
    public function displayTabs(){
        
        $this->checkOperations();
        $output = '';
        $navHeader = Resources::generateNav($this->active);
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
     * 
     */
    public function checkOperations(){
        $this->active = 1;
        if(Tools::isSubmit('submitResetodfirst')){
            $this->displayInformation($this->l("filters reset'd"));
            //unset the filters
            $this->processResetFilters();
            $this->active = 2;
        }
        //Delete(Remove) and update the table
        if(Tools::isSubmit('deleteodfirst')){
            $this->displayInformation($this->l("name remove'd"));
            $this->active = 2;
            Resources::deleteUser($_GET['ID']);
        }
        //If removed X or check is pressed, it changes to the inverse
        if(Tools::isSubmit('statusodfirst')){
            $this->displayInformation($this->l("ID modified"));
            $this->active = 2;
            Resources::changeRemoved($_GET['ID']);
        }
        //If the search filter, the arrow to order any field, or page is selected load the table tab as active
        if(Tools::isSubmit('submitFilter') || Tools::getIsset('odfirstOrderby') || Tools::getIsset('page')){
            $this->active = 2;
        }
        //If modify button is pressed
        if(Tools::isSubmit('updateodfirst')){
            $this->active = 3;
            Tools::getValue($_GET['ID']);
        }
    }
    /**
     * Display the formulary in the first nav.
     * @return string containing the entire formulary
     */
    public function displayForm(){
        $form = [
            'form' => [
                'input' => [
                    [
                    'type' => 'text',
                    'label' => 'naming',
                    'name' => 'name',
                    'class' => 'name',
                    'required' => true,
                    ],
                    [
                    'type' => 'text',
                    'label' => 'numbing',
                    'name' => 'age',
                    'class' => 'age',
                    'required' => true,
                    'desc' => 'only numbers accepted',
                    ],
                    [
                    'type' => 'date',
                    'label' => 'dating',
                    'name' => 'date',
                    'class' => 'date',
                    'required' => true,
                    ],
                ],
                'buttons' => [
                    [
                    'type' => 'button',
                    'id' => 'btnSubmit',
                    'name' => 'commonButton',
                    'title' => 'add user',
                    ],
                    [
                    'type' => 'button',
                    'id' => 'btnVerify',
                    'class' => 'btn btn-success',
                    'name' => 'btnVerify',
                    'title' => 'verify it'
                    ],
                ],
            ],
        ];
        $helper = new HelperForm();
        
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminOdFirst');
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->submit_action = 'submit' . $this->name;

        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        Media::addJsDef(array(
            'admin_od' => $this->context->link->getAdminLink('AdminOdFirst')
        ));
        $this->context->controller->addJS(
            _PS_MODULE_DIR_.'od_first/views/js/odjs.js'
            );
        $this->context->controller->addCSS(
            _PS_MODULE_DIR_.'od_first/views/css/styles.css'
        );
        return $helper->generateForm([$form]);
    }
    /**
     * Display the entire table, second nav.
     */
    public function displayTable(){
        $res = new Resources();
        $query = 'SELECT * FROM `'._DB_PREFIX_.'odFirst`';
        //If there's no filters, query return everything (don't enter here)
        if(Tools::isSubmit('submitFilter')){
            $whereStr = Resources::getFilters($_POST);
            //search is pressed but all the field are empty
            if(!is_null($whereStr)){
                $query = $query.' WHERE '.$whereStr;
            }
        }
        //If the arrows to order the table are pressed 
        if(Tools::getIsset('odfirstOrderby')){
        $orderBy = Tools::getValue('odfirstOrderby','ID');
        $orderWay = Tools::getValue('odfirstOrderway','desc');
        $ordering = ' ORDER BY '.$orderBy.' '.$orderWay;
        $query .= $ordering;
        }
        $field_list = array(
            'ID' => array(
                'title' => 'ID',
                'width' => 100,
                'type' => 'text',
                'orderby' => true,
                'search' => false,
                'filter_type' => 'int',
            ),
            'name' => array(
                'title' => 'name',
                'width' => 100,
                'type' => 'text',
                'orderby' => true,
                'havingFilter' => true,
            ),
            'age' => array(
                'title' => 'age',
                'width' => 100,
                'type' => 'number',
                'orderby' => true,
                'search' => false,
                'suffix' => 'years',
                'callback' => 'checkAge',
                'callback_object' => $this,
            ),
            'date' => array(
                'title' => 'date',
                'width' => 150,
                'type' => 'date',
            ),
            'creation_date' => array(
                'title' => 'creation',
                'width' => 200,
                'type' => 'datetime',
            ),
            'mod_date' => array(
                'title' => 'modificacion date',
                'width' => 200,
                'type' => 'datetime',
            ),
            'del_date' => array(
                'title' => 'delete date',
                'width' => 200,
                'type' => 'datetime',
            ),
            'removed' => array(
                'title' => 'deleted',
                'width' => 200,
                'type' => 'bool',
                'filter_type' => 'bool',
                'active' => 'status',
            ),
        );
        $result = Db::getInstance()->executeS($query);
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;

        $helper->actions = array('edit','delete','view');
        $helper->identifier = 'ID';
        $helper->show_toolbar = true;
        $helper->title = 'User listed';
        
        $helper->orderBy = Tools::getValue('odfirstOrderby','ID');
        $helper->orderWay = Tools::getValue('odfirstOrderway','desc');
        
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminOdFirst');
        $helper->currentIndex = AdminController::$currentIndex;
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
        if(!$result){
            $this->displayWarning('Error somewhere in the query');
        }
        return $helper->generateList($result,$field_list);
    }
    /**
     * The content of modify tab
     * @return string string containing the entire tab
     */
    public function displayModify(){
        $form = [
            'form' => [
                'input' => [
                    [
                    'type' => 'text',
                    'label' => 'id',
                    'name' => 'mod_id',
                    'class' => 'id',
                    'disabled' => true,
                    ],
                    [
                    'type' => 'text',
                    'label' => 'naming',
                    'name' => 'mod_name',
                    'class' => 'name',
                    'required' => true,
                    ],
                    [
                    'type' => 'text',
                    'label' => 'numbing',
                    'name' => 'mod_age',
                    'class' => 'age',
                    'required' => true,
                    'desc' => 'only numbers accepted',
                    ],
                    [
                    'type' => 'date',
                    'label' => 'dating',
                    'name' => 'mod_date',
                    'class' => 'date',
                    'required' => true,
                    ],
                    [
                    'type' => 'text',
                    'label' => 'created at',
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
                    'title' => 'update user',
                    ],
                ],
            ],
        ];
        $id = 1;
        $helper = new HelperForm();
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminOdFirst');
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->submit_action = 'submit' . $this->name;
        if(Tools::isSubmit('updateodfirst')){
            $id = Tools::getValue('ID');
        }
        $helper->fields_value = array(
            'mod_id' => $id,
            'mod_name' => Db::getInstance()->getValue('SELECT name FROM ps_odFirst WHERE ID='.$id),
            'mod_age' => Db::getInstance()->getValue('SELECT age FROM ps_odFirst WHERE ID='.$id),
            'mod_date' => Db::getInstance()->getValue('SELECT date FROM ps_odFirst WHERE ID='.$id),
            'mod_creation_date' => Db::getInstance()->getValue('SELECT creation_date FROM ps_odFirst WHERE ID='.$id),
            'mod_mod_date' => Db::getInstance()->getValue('SELECT mod_date FROM ps_odFirst WHERE ID='.$id),
            'mod_del_date' => Db::getInstance()->getValue('SELECT del_date FROM ps_odFirst WHERE ID='.$id),  
        );
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        Media::addJsDef(array(
            'admin_od' => $this->context->link->getAdminLink('AdminOdFirst')
        ));
        $this->context->controller->addJS(
            _PS_MODULE_DIR_.'od_first/views/js/odjs.js'
            );
        $this->context->controller->addCSS(
            _PS_MODULE_DIR_.'od_first/views/css/styles.css'
        );
        return $helper->generateForm([$form]);
    }
    /**
     * Generate the diferent tabs for the body
     * @return string $navBody html code of the tabs
     */
    public function generateNavBody(){
        $navBody = '';
        if($this->active == 1){
            $navBody .= '<div class="tab-pane active" role="tabpanel" id="adding" aria-labelledby="adding-tab" >'.$this->displayForm().'</div>
            <div class="tab-pane" role="tabpabel" id="table" aria-labelledby="table-tab">'.$this->displayTable().'</div>
            <div class="tab-pane" role="tabpabel" id="modify" aria-labelledby="modify-tab">'.$this->displayModify().$this->displayModifyId().'</div>
            </div>';
        }
        else if ($this->active == 2){
            $navBody .= '<div class="tab-pane" role="tabpanel" id="adding" aria-labelledby="adding-tab" >'.$this->displayForm().'</div>
            <div class="tab-pane active" role="tabpabel" id="table" aria-labelledby="table-tab">'.$this->displayTable().'</div>
            <div class="tab-pane" role="tabpabel" id="modify" aria-labelledby="modify-tab">'.$this->displayModify().$this->displayModifyId().'</div>
            </div>';
        }
        else if ($this->active == 3){
            $navBody .= '<div class="tab-pane" role="tabpanel" id="adding" aria-labelledby="adding-tab" >'.$this->displayForm().'</div>
            <div class="tab-pane" role="tabpabel" id="table" aria-labelledby="table-tab">'.$this->displayTable().'</div>
            <div class="tab-pane active" role="tabpabel" id="modify" aria-labelledby="modify-tab">'.$this->displayModify().$this->displayModifyId().'</div>
            </div>';
        }
        return $navBody;
    }
    public function checkAge($value){
        if($value > 100){
            return 100;
        }
        else{
            return $value;
        }
    }
}
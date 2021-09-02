<?php
declare (strict_types=1);

namespace OrbitaDigital\OdFirst;
use Db;
use Tools;

class Resources{
    protected $table = _DB_PREFIX_.'odFirst';
    /**
    * Add a new user into the database
    * @param array $array_data array containing the data of the user
    * @return mixed $query (bool) if data don't contains errors. $array_error (array) with the errors and good values otherwise.
    */
    public function add(array $array_data){
        $array_error = $this->validate($array_data);
        if(count($array_error['error']) === 0){
            $query = Db::getInstance()->execute('INSERT INTO '.$this->table.'(name,age,date,creation_date,mod_date) VALUES ("'.$array_data['name'].'",'.$array_data['age'].',"'.$array_data['date'].'",NOW(),NOW())');
            return $query;
        }
        else{
            return $array_error;
        }
    }
    /**
     * Change beetwen removed/non-removed user
     * @param int $id id of the register
     * @return bool $remove removed value, false if error
     */
    public static function changeRemoved(int $id){
        //obtain the removed value
        $removed = Db::getInstance()->getValue('SELECT removed FROM '._DB_PREFIX_.'odFirst WHERE ID="'.$id.'"');
        if($removed == 0){
            $query = Db::getInstance()->execute('UPDATE'._DB_PREFIX_.'odFirst SET removed=1, mod_date=NOW(), del_date=NOW() WHERE id="'.$id.'"');
            if($query){
            return Db::getInstance()->getValue('SELECT removed from '._DB_PREFIX_.'odFirst WHERE ID="'.$id.'"');
            }
            return $query;
        }
        else{
            $query = Db::getInstance()->execute('UPDATE'._DB_PREFIX_.'odFirst SET removed=0, mod_date=NOW(), del_date=NULL WHERE id="'.$id.'"');
            if($query)
            return Db::getInstance()->getValue('SELECT removed from '._DB_PREFIX_.'odFirst WHERE ID="'.$id.'"');
            }
            return $query;
    }
    /**
    * Check and remove a name from the table by id
    * @param int $id user id to be deleted from the table
    * @return bool $query true if succesfully, false otherwise
    */
    public static function deleteUser(int $id){
        $checkRemoved = Db::getInstance()->getValue('SELECT removed FROM '._DB_PREFIX_.'odFirst WHERE id="'.$id.'"');
        if($checkRemoved==1){
            return 'removed';
        }
        $query = Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'odFirst SET removed=1, mod_date=NOW(), del_date=NOW() WHERE id="'.$id.'"');
        return $query;
    }
    /**
     * find an user given an ID.
     */
    public static function findUser(int $id){
        $query = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'odFirst WHERE id="'.$id.'"');
        return $query;
    }
    /**
     * Generate the nav tab for admincontroller
     * @param int $nav active nav to be shown
     * @return string $output string containing the nav
     */
    public static function generateNav(int $nav){
        $output = '';
        $output .= '
        <ul class="nav nav-tabs" id="nav-tab" role="tablist">';
        //Only changes active from the li
        if($nav == 1){
        $output .= '<li class="nav-item active">';
        }
        else{
            $output .= '<li class="nav-item">';
        }
            $output .= '<a class="nav-link" id="adding-tab" data-toggle="tab" href="#adding" role="tab" aria-controls="adding" aria-selected="true">Add users</a>
        </li>';
        if($nav == 2){
            $output .= '<li class="nav-item active">';
        }
        else{
            $output .= '<li class="nav-item">';
        }
        $output .= '<a class="nav-link" id="table-tab" data-toggle="tab" href="#table" role="tab" aria-controls="table" aria-selected="false">Views users</a>
        </li>';
        if($nav == 3){
            $output .= '<li class="nav-item active">';
        }
        else{
            $output .= '<li class="nav-item">';
        }
        $output .= '<a class="nav-link" id="modify-tab" data-toggle="tab" href="#modify" role="tab" aria-controls="modify" aria-selected="false">Modify user</a></li>
        </ul>';
        $output .= '<div class="tab-content" id="tabsBody">';
        return $output;
    }
    /**
    * Generate the entire table for the database
    */
    public static function generateTable(){
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."odFirst` (
            `ID` int AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255),
            `age` int,
            `date` DATE,
            `creation_date` DATETIME,
            `mod_date` DATETIME,
            `del_date` DATETIME,
            `removed` BIT DEFAULT 0
        )";
        return Db::getInstance()->execute($query);
    }
    /**
    * Obtain the filters given the POST from the admin table
    * @return whereStr string with all the conditions
    */
    public static function getFilters(array $dataPost){
        $filterArray = [];
        //dataPost filters is odfirstFilter_name, _age, _date...
        foreach ($dataPost as $key => $value) {
            $find = 'odfirstFilter_';
            if(strstr($key,$find)){
                $datakey = str_replace($find,'',$key);
                $filterArray[$datakey] = $value; 
            }
        }
        $whereStr = '';
        foreach ($filterArray as $column => $value) {   
            //removed can be 0
            if(empty($value) && $column != "removed" || $value==""){
                continue;
            }
            switch ($column){
            case "name":
                $whereArr[] = $column.' LIKE "%'.$value.'%"';
                break;
            //0 -> beggining date to filter
            //1 -> end date to filter
            case "date":
            case "creation_date":
            case "mod_date":
                if(!empty($value[0])){
                    $whereArr[] = $column.' >= "'.$value[0].'"';
                }
                if(!empty($value[1])){
                    $whereArr[] = $column.' <= "'.$value[1].'"';
                }
            break;
            case "removed":
                $whereArr[] = $column.' = '.$value;
                break;
            }
        }
        $whereStr = implode(' AND ',$whereArr);
        return $whereStr;
    }
    /**
    * Drop table when uninstalling the module
    */
    public static function removeTable(){
        $dropQuery = "DROP TABLE IF EXISTS `ps_odFirst` ";
        return Db::getInstance()->execute($dropQuery);
    }
    /**
    * Update an element in the database
    * @param array $array_data array with the elements that are going to be modified onto the database
    * @return mixed $query if the validation is successfully or $array_save which gives two arrays with the errors and right inputs
    */
    public function save(array $array_save){
        $array_error = $this->validate($array_save);
        if(count($array_error['error']) === 0){
            $query = Db::getInstance()->execute('UPDATE '.$this->table.' SET name="'.$array_save['name'].'", age='.$array_save['age'].', date="'.$array_save['date'].'",mod_date=NOW() WHERE ID = '.$array_save['id']);
            return $query;
        }
        else{
            return $array_error;
        }
    }
    /**
    * validate an array of elements, used for a form
    * @param array $array_data array to be checked. Insert in error if values are wrong otherwise in good if values are fine
    * @return array $arr array with the verified elements divided in error and good
    */
    public static function validate (array $array_verify){
        $arr = ['error' => [], 'good' => []];
            foreach ($array_verify as $keyVal => $value) {
                $cadenaVacia = trim($value);
                if(empty($cadenaVacia)){
                    array_push($arr['error'], $keyVal);
                }
                else{
                    array_push($arr['good'],$keyVal);
                }
            }
        return $arr;
    }
    

    /**
     * Count the users given the conditions
     * @param string $conditions all the filters/conditions
     * @return int $usersNumber the number of registers found
     */
    function countUsers(string $conditions){
        $countQuery = "SELECT COUNT(*) FROM ".$this->table." WHERE ".$conditions;
        $usersNumber = Db::getInstance()->getValue($countQuery);
        return $usersNumber;
    }
    /**
    * Find users either registered or removed from the database within a limit
    * @param array $array_data the inputs to filter the query if any
    * @param int $pagination number of page to show
    * @param int $result_limit Limit the registers per page
    * Return mixed $values number of registers found and registers themselves
    */
    function find(array $array_data, int $pagination, int $result_limit){
        //number of result per page
        /**
         * What results are returned 
         * page 1 = 0, page 2 (2-1*5) = 5 (3-1*5) = 10. 
         * Limit first number is start, second how many of them are returned
         */
        $calc_page = ($pagination-1) * $result_limit;
        $queryString = 'SELECT * FROM '.$this->table.' WHERE ';
        $whereArr = [];
        $dateQuery = $array_data['dateType'];
        foreach ($array_data as $column => $value) {   
            //Removed is 0, so is needed to check it. 
            if(empty($value) && $column !="removed"){
                continue;
            }
            switch ($column){
                case "name":
                    $whereArr[] = $column.' LIKE "%'.$value.'%"';
                    break;
                case "dateBeg":
                    $whereArr[] = $dateQuery.' >= "'.$value.'"';
                    break;
                case "dateEnd":
                    $whereArr[] = $dateQuery.' <= "'.$value.'"';
                    break;
                case "removed":
                    $whereArr[] = $column.' = '.$value;
                    break;
            }
        }
        $whereStr = implode(' AND ',$whereArr);
        //Count users
        $usersNumber = $this->countUsers($whereStr);
        //For pagination
        $limit = ' LIMIT '.$calc_page.','.$result_limit;
        $queryRequest = $queryString.$whereStr.$limit;
        $query = Db::getInstance()->executeS($queryRequest);
        $values = [$usersNumber,$query];
        return $values;
    }

}
?>
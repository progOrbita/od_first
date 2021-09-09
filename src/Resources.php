<?php
declare (strict_types=1);

namespace OrbitaDigital\OdFirst;
use Db;
use Validate;

class Resources{
    protected $table = _DB_PREFIX_.'od_first';
    /**
    * Add a new user into the database
    * @param array $array_data array containing the data of the user
    * @return mixed $query (bool) if data don't contains errors. $array_error (array) with the errors and good values otherwise.
    */
    public function add(array $array_data){
        $array_error = $this->validate($array_data);
        if(count($array_error['error']) === 0){
            return Db::getInstance()->execute('INSERT INTO '.$this->table.'(name,age,date_birth,date_add,date_upd) VALUES ("'.$array_data['name'].'",'.$array_data['age'].',"'.$array_data['date'].'",NOW(),NOW())');
        }
        
        return $array_error;
    }
    /**
     * Change beetwen removed/non-removed user
     * @param int $id id of the register
     * @return bool $remove removed value, false if error
     */
    public static function changeRemoved(int $id){

        $removed = Resources::getRemoved($id);
        if($removed == 0){
            $query = Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'od_first SET removed=1, date_upd=NOW(), date_del=NOW() WHERE id="'.$id.'"');
            if($query==true){
                return Resources::getRemoved($id);
            }
        }
        else{
            $query = Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'od_first SET removed=0, date_upd=NOW(), date_del=NULL WHERE id="'.$id.'"');
            if($query==true){
                return Resources::getRemoved($id);
            }
        }
        return $query;
    }
    /**
     * Obtain removed value.
     */
    public static function getRemoved(int $id){
        return Db::getInstance()->getValue('SELECT removed FROM '._DB_PREFIX_.'od_first WHERE ID="'.$id.'"');
    }
    /**
    * Check and remove a name from the table by id
    * @param int $id user id to be deleted from the table
    * @return bool $query true if succesfully, false otherwise
    */
    public static function deleteUser(int $id){
        $checkRemoved = Db::getInstance()->getValue('SELECT removed FROM '._DB_PREFIX_.'od_first WHERE ID="'.$id.'"');
        if($checkRemoved==1){
            return false;
        }
        return Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'od_first SET removed=1, date_upd=NOW(), date_del=NOW() WHERE ID="'.$id.'"');
    }
    /**
     * find an user given an ID.
     */
    public static function findUser(int $id){
        return Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'od_first WHERE ID="'.$id.'"');
    }
    /**
     * Generate the nav tab for admincontroller
     * @param int $nav active nav to be shown
     * @return string $output string containing the nav
     */
    
    /**
    * Generate the entire table for the database
    */
    public static function generateTable(){
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."od_first` (
            `ID` int AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255),
            `age` int,
            `date_birth` DATE,
            `date_add` DATETIME,
            `date_upd` DATETIME,
            `date_del` DATETIME,
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

                case "date_birth":
                case "date_add":
                case "date_upd":
                case "date_del":
                    //*** 0 -> beggining date to filter
                    //*** 1 -> end date to filter
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
        $dropQuery = "DROP TABLE IF EXISTS"._DB_PREFIX_."`_od_first` ";
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
            return Db::getInstance()->execute('UPDATE '.$this->table.' SET name="'.$array_save['name'].'", age='.$array_save['age'].', date_birth="'.$array_save['date'].'",date_upd=NOW() WHERE ID = '.$array_save['id']);
        }
        return $array_error;
    }
    /**
    * validate an array of elements, used for a form
    * @param array $array_data array to be checked. Insert in error if values are wrong otherwise in good if values are fine
    * @return array $arr array with the verified elements divided in error and good
    */
    public static function validate (array $array_verify){
        $arr = ['error' => [], 'good' => []];
            foreach ($array_verify as $keyVal => $value) {
            switch($keyVal){
                case 'name':
                    !empty(trim($value)) ? array_push($arr['good'],$keyVal) : array_push($arr['error'], $keyVal);
                    break;

                case 'age':
                    Validate::isInt($value) ? array_push($arr['good'],$keyVal) : array_push($arr['error'], $keyVal);
                    break;
                
                case 'date':
                    Validate::isDate($value) ? array_push($arr['good'],$keyVal) : array_push($arr['error'], $keyVal);
                    break;
                }
            }
        return $arr;
    }
}
?>
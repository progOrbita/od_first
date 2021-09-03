<?php
declare (strict_types=1);

namespace OrbitaDigital\OdFirst;
use Db;
use Validate;

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
            return Db::getInstance()->execute('INSERT INTO '.$this->table.'(name,age,date,creation_date,mod_date) VALUES ("'.$array_data['name'].'",'.$array_data['age'].',"'.$array_data['date'].'",NOW(),NOW())');
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
            $query = Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'odFirst SET removed=1, mod_date=NOW(), del_date=NOW() WHERE id="'.$id.'"');
            if($query==true){
                return Resources::getRemoved($id);
            }
        }
        else{
            $query = Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'odFirst SET removed=0, mod_date=NOW(), del_date=NULL WHERE id="'.$id.'"');
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
        return Db::getInstance()->getValue('SELECT removed FROM '._DB_PREFIX_.'odFirst WHERE ID="'.$id.'"');
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
        return Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'odFirst SET removed=1, mod_date=NOW(), del_date=NOW() WHERE id="'.$id.'"');
    }
    /**
     * find an user given an ID.
     */
    public static function findUser(int $id){
        return Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'odFirst WHERE id="'.$id.'"');
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

                case "date":
                case "creation_date":
                case "mod_date":
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
            return Db::getInstance()->execute('UPDATE '.$this->table.' SET name="'.$array_save['name'].'", age='.$array_save['age'].', date="'.$array_save['date'].'",mod_date=NOW() WHERE ID = '.$array_save['id']);
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
                $cadenaVacia = trim($value);
                if(empty($cadenaVacia)){
                    array_push($arr['error'], $keyVal);
                }
                if($keyVal=='age'){
                    Validate::isInt($value) ? array_push($arr['good'],$keyVal) : array_push($arr['error'], $keyVal);
                }
                if($keyVal=='date'){
                    Validate::isDate($value) ? array_push($arr['good'],$keyVal) : array_push($arr['error'], $keyVal);
                }
                else{
                    array_push($arr['good'],$keyVal);
                }
            }
        return $arr;
    }
}
?>
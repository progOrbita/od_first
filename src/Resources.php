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
     * @return bool $remove true if done, false if error
     */
    public static function changeRemoved(int $id){
        $query = Db::getInstance()->getValue('SELECT removed FROM '._DB_PREFIX_.'odFirst WHERE ID="'.$id.'"');
        if($query == 0){
            $remove = Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'odFirst SET removed=1, mod_date=NOW(), del_date=NOW() WHERE id="'.$id.'"');
            if($remove){
            return Db::getInstance()->getValue('SELECT removed from '._DB_PREFIX_.'odFirst WHERE ID="'.$id.'"');
            }
        }
        else{
            $remove = Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'odFirst SET removed=0, mod_date=NOW(), del_date=NULL WHERE id="'.$id.'"');
            if($remove)
            return Db::getInstance()->getValue('SELECT removed from '._DB_PREFIX_.'odFirst WHERE ID="'.$id.'"');
        }
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
    /**
    * Restore an user removed
    * @param int $id id of the user to be restored
    * @return bool $query true if was done, false otherwise.
    */
    function undo(int $id){
        $query = Db::getInstance()->execute('UPDATE '.$this->table.' SET removed=0, mod_date=NOW(), del_date=NULL WHERE id="'.$id.'"');
        return $query;
    }
    /**
    * Creates the entire table. 
    * header is table start and thead. 
    * body is all the rows with the information 
    * footer close tbody and table tags.
    * pages includes pagination after the table
    * @param array $tableData data obtained from the query
    * @param int $removed 0 -> register tab, 1 -> removed tab.
    * @param array $totalRegistered array with two numbers counting all the registers and removed users.
    * @param int $num_limit integer with the users displayed per page
    * @return string $header+$body+$footer+$pages containing the entire table in html
    */
    function createTable(array $tableData, int $removed, int $totalUsers,int $cur_page, int $num_limit){
            $body = "";
            $i = 0;
            $len = count($tableData);
            $pages = "";
            $header = '
                <table class="table table-sm table-dark table-striped table-hover table-bordered table-fixed">
            <caption style="padding-top: 0px" id="tableCaption"></caption>
            <thead>
                <tr class="bg-info">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Date</th>
                    <th>Creation date</th>
                    <th>Modification date</th>';
                    //if there's no users added in the table
                    if($len == 0 && $removed == 0){
                        $header .= '<th class="col-2">actions</th>';
                    }
                    //For removed users, key is to insert th only once
                    foreach ($tableData as $key => $value) {
                        foreach ($value as $keyVal => $string) {
                            if($key == 0 && $keyVal=="removed" && $string==1){
                                $header .= '<th>Deletion date</th>
                                                <th>Actions</th>';
                            }
                            //for non-removed users
                            if($key == 0 && $keyVal=="removed" && $string==0){
                                $header .= '<th class="col-2">Actions</th>';
                            }
                        }
                    } 
            $header .=  '</tr>
                    </thead>
                <tbody>';
                //If there's no users added in the table
                if($len == 0 && $removed == 0){
            $body .='<tr>
                <td><input class="form-control text-light btn-secondary" value="" disabled></input></td>
                <td><input type="text" class="form-control .name" name="insName" id="insertName"></input></td>
                <td><input type="number" class="form-control .age" name="insAge" id="insertAge"></input></td>
                <td><input type="date" class="form-control .date" name="insDate" id="insertDate"></input></td>
                <td><input class="form-control text-light btn-secondary" value="" disabled></input></td>
                <td><input class="form-control text-light btn-secondary" value="" disabled></input></td>
                <td><i style="font-size: 2rem;" class="bi bi-pencil-square" type="button" data-toggle="tooltip" title="add new user" name="addNew" id="addNew"></i></td>
                </tr>';          
            }
            //Key is the result number. Value is array containing the data
                foreach ($tableData as $key => $value) {
                    $body .= '<tr>';
                    //array have keyVal (ID, name...) and the string which is keyVal value (12,user).
                    foreach ($value as $keyVal => $string) {
                        switch($keyVal){
                            case "name":
                                $body .= '<td><input type="text" class="form-control '.$keyVal.'" value="'.$string.'"></input></td>'; 
                            break;
                            case "age":
                                $body .= '<td><input type="number" class="form-control '.$keyVal.'" value="'.$string.'"></input></td>';
                                break; 
                            case "date":
                                $body .= '<td><input type="date" class="form-control '.$keyVal.'" value="'.$string.'"></input></td>'; 
                                break;
                            case "ID":
                            case "creation_date":
                            case "mod_date":
                                $body .= '<td><input class="form-control text-light btn-secondary '.$keyVal.'" value="'.$string.'" disabled></input></td>'; 
                                break;
                            case "del_date":
                                    if($string != ""){
                                    $body .= '<td><input class="form-control text-light btn-secondary '.$keyVal.'" value="'.$string.'" disabled></input></td>'; 
                                }
                            break;
                            case "removed":
                                if($string==0){
                                    $body .=
                                    '<td>
                                        <i style="font-size: 2rem;" class="bi bi-x-octagon-fill" type="button" data-toggle="tooltip" title="Remove user" name="delete" id="delete" value="'.$value["ID"].'"></i>
                                        <i style="font-size: 2rem;" class="bi bi-check-square text-success" type="button" data-toggle="tooltip" title="verify fields" name="verify" id="verify" value="'.$value["ID"].'"></i>
                                        <i style="font-size: 2rem;" class="bi bi-key-fill text-success" type="button" data-toggle="tooltip" title="update user" name="save" id="save" value="'.$value["ID"].'"></i></td>';
                                //Last row from registered users (removed == 0). To include an empty row with a specific button
                                if($i == $len - 1){
                                   $body .= '</tr>
                                      <tr>
                                        <td><input class="form-control text-light btn-secondary" value="" disabled></input></td>
                                        <td><input type="text" class="form-control .name" name="insName" id="insertName"></input></td>
                                        <td><input type="number" class="form-control .age" name="insAge" id="insertAge"></input></td>
                                        <td><input type="date" class="form-control .date" name="insDate" id="insertDate"></input></td>
                                        <td><input class="form-control text-light btn-secondary" value="" disabled></input></td>
                                        <td><input class="form-control text-light btn-secondary" value="" disabled></input></td>
                                        <td><i style="font-size: 2rem;" class="bi bi-pencil-square" type="button" data-toggle="tooltip" title="add new user" name="addNew" id="addNew"></i></td>';
                                    }
                                    $i++;
                                }
                                if($string==1){
                                    $body .='<td><i style="font-size: 2rem;" class="bi bi-eject" type="button" data-toggle="tooltip" title="undo" name="undo" id="undo" value="'.$value["ID"].'"></i>';
                                }
                            break;
                        }
                    }
                    $body .= '</tr>';
                }
            $footer = '</tbody></table>';
                //pagination creation. 0 registered, 1 removed users
                //calculations for displaying text
                $pagesNumber = ceil($totalUsers/$num_limit);
                $current_number = (($cur_page-1)*$num_limit)+1;
                $current_limit = $cur_page*$num_limit;
                //If there's less than current limit
                if($totalUsers < $current_limit){
                    $current_limit = $totalUsers;
                }
                if($current_limit == 0){
                    $current_number = 0;
                }
                $pages .= '<nav><ul class="pagination justify-content-center">';
                    for ($i = 1; $i <= $pagesNumber; $i++) {
                        //Check the current page to add active
                        if( $i == $cur_page){
                            $pages .= '<li class="page-item active"><input class="btn btn-secondary page-link" type="button" id="pagination" value="'.$i.'"></input></li>';
                        }
                        else{
                        $pages .= '<li class="page-item"><input class="btn btn-secondary page-link" type="button" id="pagination" value="'.$i.'"></input></li>';
                        }
                    }
                    $pages .= '</ul></nav><span> Displaying '.$current_number.'-'.$current_limit.' of <span id="totalUsers">'.$totalUsers.'</span> results</span>';
            return $header.$body.$footer.$pages;
        }
    }
?>
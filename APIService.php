<?php
    class APIService {
        public $db;

        public function __construct() {
            $this->connectDB();
        }

        //--Connect Database
        public function connectDB() {
            $configDB = [
                'host'=>'127.0.0.1',
                'username'=>'root',
                'password'=>'',
                'db'=>'php_test',
                'charset'=>'utf-8'
            ];
            $this->db = mysqli_connect($configDB['host'], $configDB['username'], $configDB['password'], $configDB['db']);

            if(mysqli_connect_errno()) {
                die('<b>Parse error: </b>'.mysqli_connect_error());
                exit();
            } else {
                $this->db->set_charset($configDB['charset']);
                return true;
            }
        }

        //--Get Column name from table
        public function getColumnNameFromTable($tableName = '') {
            $columnNameArr = [];

            $sqlCmd = "SHOW COLUMNS FROM $tableName";
            $query = $this->db->query($sqlCmd);

            if($query) {
                while($columnName = $query->fetch_array()) {
                    array_push($columnNameArr, $columnName['Field']);
                }

                return json_encode($columnNameArr, JSON_UNESCAPED_UNICODE);
            }

            return false;
        }

        //--Get all data from table
        public function getDataArray($tableName = '') {
            $dataArr = [];

            $sqlCmd = "SELECT * FROM $tableName";
            $query = $this->db->query($sqlCmd);

            if($query) {
                while($data = $query->fetch_assoc()) {
                    array_push($dataArr, $data);
                }

                echo json_encode($dataArr, JSON_UNESCAPED_UNICODE);
            }

            return false;
        }

        //--Get data from table by filter
        public function getDataObject($tableName = '', $columnFilter = '', $valueFilter = '') {
            $dataObj = [];

            $sqlCmd = "SELECT * FROM $tableName WHERE $columnFilter = '$valueFilter'";
            $query = $this->db->query($sqlCmd);

            if($query) {
                 $dataObj = $query->fetch_assoc();

                 if($dataObj != null)
                    return json_encode($dataObj, JSON_UNESCAPED_UNICODE);
            }

            return false;
        }

        //--Insert Data in table
        public function insertData($tableName = '', $dataArr = []) {
            if((!empty($tableName)) && (count($dataArr) != 0)) {
                foreach($dataArr as $data) {
                    $status = false;
                    $columns = "";
                    $values = "";
                    $index = 1;

                    foreach($data as $key=>$val) {
                        if($index > 1) {
                            $columns .= ", ";
                            $values .= ", ";
                        }

                        $columns .= "$key";
                        $values .= "'$val'";
                        $index++;
                    }

                    $sqlCmd = "INSERT INTO $tableName($columns) VALUES($values)";
                    $status = $this->db->query($sqlCmd);
                }

                if($status)
                    return true;
                
                return false;
            }
        }

        //--Insert Data in primary and secondary table
        public function insertDataMultiTable($primaryTableName = '', $primaryDataArr = []) {
            if((!empty($primaryTableName)) && (count($primaryDataArr) != 0)) {
                foreach($primaryDataArr as $primaryData) {
                    $status = false;
                    $primaryColumns = "";
                    $primaryValues = "";
                    $primaryIndex = 1;
                    $secondaryTableArr = [];

                    foreach($primaryData as $key=>$val) {
                        switch($key) {
                            case 'secondaryTableArr': 
                                array_push($secondaryTableArr, $val);
                                break;
                            default:
                                if($primaryIndex > 1) {
                                    $primaryColumns .= ", ";
                                    $primaryValues .= ", ";
                                }

                                $primaryColumns .= "$key";
                                $primaryValues .= "'$val'";
                                $primaryIndex++;
                                break;
                        }
                    }

                    $sqlCmd = "INSERT INTO $primaryTableName($primaryColumns) VALUES($primaryValues)";
                    $status = $this->db->query($sqlCmd);
                    $primaryLastInsertID = $this->db->insert_id;

                    if($status && (count($secondaryTableArr) != 0)) {
                        foreach($secondaryTableArr as $secondaryTable){
                            $secondaryTableName = $secondaryTable['tableName'];
                            $secondaryForeignKey = $secondaryTable['foreignKey'];
                            $secondaryDataArr = $secondaryTable['dataArr'];

                            foreach($secondaryDataArr as $secondaryData) {
                                $secondaryColumns = "";
                                $secondaryValues = "";
                                $secondaryIndex = 1;

                                foreach($secondaryData as $key=>$val) {
                                    if($secondaryIndex > 1) {
                                        $secondaryColumns .= ", ";
                                        $secondaryValues .= ", ";
                                    }

                                    $secondaryColumns .= "$key";
                                    $secondaryValues .= "'$val'";
                                    $secondaryIndex++;
                                }

                                $sqlCmd = "INSERT INTO $secondaryTableName($secondaryColumns, $secondaryForeignKey) VALUES($secondaryValues, $primaryLastInsertID)";
                                $status = $this->db->query($sqlCmd);
                            }
                        }
                    }
                }

                if($status) 
                    return true;

                return false;
            }
        }

        //--Update Data in table
        public function updateData($tableName = '', $dataArr = [], $columnFilter = '', $columnUpdateDTFilter = ''){
            if((!empty($tableName)) && (count($dataArr) != 0) && (!empty($columnFilter))) {
                foreach($dataArr as $data) {
                    $status = false;
                    $updates = "";
                    $condition = "";
                    $index = 1;

                    foreach($data as $key=>$val) {
                        switch($key) {
                            case $columnFilter:
                                $condition .= "$columnFilter = '$val'";
                                break;
                            default:
                                if($index > 1)
                                    $updates .= ", ";
                                
                                $updates .= "$key = '$val'";
                                $index++;
                                break;
                        }
                    }

                    if(!empty($columnUpdateDTFilter))
                        $updates .= ", $columnUpdateDTFilter = NOW()";

                    $sqlCmd = "UPDATE $tableName SET $updates WHERE $condition";
                    $status = $this->db->query($sqlCmd);
                }

                if($status)
                    return true;
                
                return false;
            }
        }

        //--Update Data in primary and secondary table
        public function updateDataMultiTable($primaryTableName = '', $primaryDataArr = [], $primaryColumnFilter = '', $primaryColumnUpdateDTFilter = '') {
            if((!empty($primaryTableName)) && (count($primaryDataArr) != 0) && (!empty($primaryColumnFilter))) {
                foreach($primaryDataArr as $primaryData) {
                    $status = false;
                    $primaryUpdates = "";
                    $primaryCondition = "";
                    $primaryIndex = 1;
                    $secondaryTableArr = [];

                    foreach($primaryData as $key=>$val) {
                        switch($key) {
                            case 'secondaryTableArr': 
                                array_push($secondaryTableArr, $val);
                                break;
                            case $primaryColumnFilter:
                                $primaryCondition .= "$primaryColumnFilter = '$val'";
                                break;
                            default:
                                if($primaryIndex > 1)
                                    $primaryUpdates .= ", ";

                                $primaryUpdates .= "$key = '$val'";
                                $primaryIndex++;
                                break;
                        }
                    }

                    if(!empty($primaryColumnUpdateDTFilter))
                        $primaryUpdates .= ", $primaryColumnUpdateDTFilter = NOW()";

                    $sqlCmd = "UPDATE $primaryTableName SET $primaryUpdates WHERE $primaryCondition";
                    $status = $this->db->query($sqlCmd);

                    if($status && (count($secondaryTableArr) != 0)) {
                        foreach($secondaryTableArr as $secondaryTable) {
                            $secondaryTableName = $secondaryTable['tableName'];
                            $secondaryColumnFilter = $secondaryTable['columnFilter'];
                            $secondaryColumnUpdateDTFilter = isset($secondaryTable['columnUpdateDTFilter']) ? $secondaryTable['columnUpdateDTFilter'] : '';
                            $secondaryDataArr = $secondaryTable['dataArr'];

                            foreach($secondaryDataArr as $secondaryData) {
                                $secondaryUpdates = "";
                                $secondaryCondition = "";
                                $secondaryIndex = 1;

                                foreach($secondaryData as $key=>$val) {
                                    switch($key) {
                                        case $secondaryColumnFilter:
                                            $secondaryCondition .= "$secondaryColumnFilter = '$val'";
                                            break;
                                        default:
                                            if($secondaryIndex > 1) 
                                                $secondaryUpdates .= ", ";

                                            $secondaryUpdates .= "$key = '$val'";
                                            $secondaryIndex++;
                                            break;
                                    }
                                }

                                if(!empty($secondaryColumnUpdateDTFilter))
                                    $secondaryUpdates .= ", $secondaryColumnUpdateDTFilter = NOW()";

                                $sqlCmd = "UPDATE $secondaryTableName SET $secondaryUpdates WHERE $secondaryCondition";
                                $status = $this->db->query($sqlCmd);
                            }
                        }
                    }
                }

                if($status) 
                    return true;
                
                return false;
            }
        }

        //--Delete Data in table 
        public function deleteData($tableName = '', $columnFilter = '', $valueFilter = '') {
            if((!empty($tableName)) && (!empty($columnFilter)) && (!empty($valueFilter))) {
                $status = false;
                $condition = "$columnFilter = '$valueFilter'";

                $sqlCmd = "DELETE FROM $tableName WHERE $condition";
                $status = $this->db->query($sqlCmd);

                if($status) 
                    return true;

                return false;
            }
        }

        //--Delete Data in primary and secondary table
        public function deleteDataMultiTable($primaryTableName = '', $columnFilter = '', $valueFilter = '', $secondaryTableNameArr = []) {
            if((!empty($primaryTableName)) && (!empty($columnFilter)) && (!empty($valueFilter))) {
                if((count($secondaryTableNameArr) != 0)) {
                    foreach($secondaryTableNameArr as $secondaryTableName) {
                        $status = false;

                        $sqlCmd = "DELETE FROM $secondaryTableName WHERE $columnFilter = $valueFilter";
                        $status = $this->db->query($sqlCmd);
                    }
                }

                if($status) {
                    $sqlCmd = "DELETE FROM $primaryTableName WHERE $columnFilter = $valueFilter";
                    $status = $this->db->query($sqlCmd);
                }

                if($status)
                    return true;

                return false;
            }
        }

        //--Query for multi statement
        public function getQueryMultiStatement($sqlCmd = ''){
            if((!empty($sqlCmd))) {
                $statementOrder = 1;

                if($this->db->multi_query($sqlCmd)) { 
                    do {
                        if($result = $this->db->store_result()) {
                            $statementOrder++;
                            $result->free_result();
                        }
                    } while($this->db->more_results() && $this->db->next_result());

                    if($this->db->errno) {
                        echo '<b>Parse error: </b>Multi query failed is statement ('.$statementOrder.') because of '.$this->db->error;
                        return false;
                    }
                } else {
                    echo '<b>Parse error: </b>Multi query failed is statement ('.$statementOrder.') because of '.$this->db->error;
                    return false;
                }

                return true;
            }
        }

        //--Login
        public function login($dataArr = []) {
            if((count($dataArr) != 0)) {
                $sqlCmd = "SELECT * FROM users WHERE email = ? AND password = ?";
                $query = $this->db->prepare($sqlCmd);
                $query->bind_param('ss', $dataArr['email'], $dataArr['password']);
                $query->execute();
                $userData = $query->get_result()->fetch_assoc();

                if((count($userData) != 0)) {
                    if((!isset($_SESSION)))
                        session_start();
                        
                    $_SESSION = [
                        'user_id'=>$userData['user_id'],
                        'email'=>$userData['email'],
                        'firstname'=>$userData['firstname'],
                        'lastname'=>$userData['lastname']
                    ];

                    return json_encode($_SESSION, JSON_UNESCAPED_UNICODE);
                }

                return false;
            }
        }

        //--Logout
        public function logout() {
            if((!isset($_SESSION)))
                session_start();

            $_SESSION = [];
            session_destroy();

            return true;
        }
    }

    new APIService();
?>
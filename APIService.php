<?php
    class APIService {
        public $db;
        public $insertDataArr = [
            ['email'=>'c', 'firstname'=>'c', 'lastname'=>'c'], 
            ['email'=>'d', 'firstname'=>'d', 'lastname'=>'d']
        ];
        public $updateDataArr = [
            ['id'=>8, 'email'=>'1', 'firstname'=>'1', 'lastname'=>'1'], 
            ['id'=>9, 'email'=>'2', 'firstname'=>'2', 'lastname'=>'2']
        ];
        public $deleteDataArr = [8, 9];

        public function __construct() {
            $this->connectDB();
            $this->deleteData();
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

        //--Update Data in table
        public function updateData($tableName = '', $dataArr = [], $columnFilter = ''){
            if((!empty($tableName)) && (count($dataArr) != 0) && (!empty($columnFilter))) {
                foreach($dataArr as $data) {
                    $status = false;
                    $updates = "";
                    $condition = "";
                    $index = 1;

                    foreach($data as $key=>$val) {
                        if($key == $columnFilter) {
                            $condition .= "$key = '$val'";
                            continue;
                        }

                        if($index > 1)
                            $updates .= ", ";
                        
                        $updates .= "$key = '$val'";
                        $index++;
                    }

                    $sqlCmd = "UPDATE $tableName SET $updates WHERE $condition";
                    $status = $this->db->query($sqlCmd);
                }

                if($status)
                    return true;
                
                return false;
            }
        }

        //--Delete Data in table 
        public function deleteData($tableName = '', $dataArr = [], $columnFilter = '') {
            if((!empty($tableName)) && (count($dataArr) != 0) && (!empty($columnFilter))) {
                $status = false;
                $condition = "";

                foreach($dataArr as $data) {
                    $condition = "$columnFilter = '$data'";

                    $sqlCmd = "DELETE FROM $tableName WHERE $condition";
                    $status = $this->db->query($sqlCmd);
                }

                if($status) 
                    return true;

                return false;
            }
        }
    }

    new APIService();
?>
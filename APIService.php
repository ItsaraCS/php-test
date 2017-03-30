<?php
    class APIService {
        public $db;

        public function __construct() {
            $this->connectDB();
        }

        /*
            - Description: 'เชื่อมต่อ Database'
        */
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

        /*
            - Description: 'ฟังก์ชันจะรีเทิร์นอาเรย์ของชื่อ Column จาก Table ที่ต้องการ'
            - Parameter: 
                $tableName = 'ชื่อ Table'
        */
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

        /*
            - Description: 'ฟังก์ชันจะรีเทิร์นอาเรย์ของข้อมูลจาก Table ที่ต้องการ'
            - Parameter: 
                $tableName = 'ชื่อ Table'
        */
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

        /*
            - Description: 'ฟังก์ชันจะรีเทิร์นข้อมูลจาก Table ตามเงื่อนไขที่ต้องการ'
            - Parameter: 
                $tableName = 'ชื่อ Table'
                $columnFilter = 'ชื่อ Column ที่ใช้เป็นเงื่อนไขในการค้นหา'
                $valueFilter = 'ค่าที่ใช้เป็นเงื่อนไขในการค้นหา'
        */
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

        /*
            - Description: 'บันทึกข้อมูลเข้า Table'
            - Parameter: 
                $tableName = 'ชื่อ Table'
                $dataArr[] = 'อาเรย์ของข้อมูลที่ต้องการบันทึก'
        */
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

        /*
            - Description: 'บันทึกข้อมูลเข้า Table หลัก และ Table ที่สัมพันธ์กับ Table หลัก'
            - Parameter: 
                $primaryTableName = 'ชื่อ Table หลัก'
                $primaryDataArr[] = 'อาเรย์ของข้อมูลที่ต้องการบันทึกเข้า Table หลัก'
                $primaryDataArr['secondaryTableArr'] = 'อาเรย์ของข้อมูลที่ต้องการบันทึกใน Table ที่สัมพันธ์กับ Table หลัก [OPTION]'
                $primaryDataArr['secondaryTableArr']['tableName'] = 'ชื่อ Table ที่สัมพันธ์กับ Table หลัก'
                $primaryDataArr['secondaryTableArr']['foreignKey'] = 'ชื่อ Foreign Key'
                $primaryDataArr['secondaryTableArr']['dataArr'] = 'อาเรย์ของข้อมูลที่ต้องการบันทึกเข้า Table ที่สัมพันธ์กับ Table หลัก'
        */
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

        /*
            - Description: 'แก้ไขข้อมูลใน Table'
            - Parameter: 
                $tableName = 'ชื่อ Table'
                $dataArr[] = 'อาเรย์ของข้อมูลที่ต้องการแก้ไข'
                $columnFilter = 'ชื่อ Column ที่ใช้เป็นเงื่อนไขในการแก้ไข'
                $columnUpdateDTFilter = 'ชื่อ Column ของวัน-เวลา ที่ต้องการแก้ไข [OPTION]'
        */
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

        /*
            - Description: 'แก้ไขข้อมูลใน Table หลัก และ Table ที่สัมพันธ์กับ Table หลัก'
            - Parameter: 
                $primaryTableName = 'ชื่อ Table หลัก'
                $primaryDataArr[] = 'อาเรย์ของข้อมูลที่ต้องการแก้ไขใน Table หลัก'
                $primaryColumnFilter = 'ชื่อ Column ที่ใช้เป็นเงื่อนไขในการแก้ไขใน Table หลัก'
                $primaryColumnUpdateDTFilter = 'ชื่อ Column ของวัน-เวลา ที่ต้องการแก้ไขใน Table หลัก [OPTION]'
                $primaryDataArr['secondaryTableArr'] = 'อาเรย์ของข้อมูลที่ต้องการแก้ไขใน Table ที่สัมพันธ์กับ Table หลัก [OPTION]'
                $primaryDataArr['secondaryTableArr']['tableName'] = 'ชื่อ Table ที่สัมพันธ์กับ Table หลัก'
                $primaryDataArr['secondaryTableArr']['columnFilter'] = 'ชื่อ Column ที่ใช้เป็นเงื่อนไขในการแก้ไข'
                $primaryDataArr['secondaryTableArr']['dataArr'] = 'อาเรย์ของข้อมูลที่ต้องการแก้ไขใน Table ที่สัมพันธ์กับ Table หลัก'
        */
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

        /*
            - Description: 'ลบข้อมูลใน Table'
            - Parameter: 
                $tableName = 'ชื่อ Table'
                $columnFilter = 'ชื่อ Column ที่ใช้เป็นเงื่อนไขในการลบ'
                $valueFilter = 'ค่าที่ใช้เป็นเงื่อนไขในการลบ'
        */
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

        /*
            - Description: 'ลบข้อมูลใน Table หลัก และ Table ที่สัมพันธ์กับ Table หลัก'
            - Parameter: 
                $primaryTableName = 'ชื่อ Table หลัก'
                $columnFilter = 'ชื่อ Column ที่ใช้เป็นเงื่อนไขในการลบ'
                $valueFilter = 'ค่าที่ใช้เป็นเงื่อนไขในการลบ'
                $secondaryTableNameArr[] = 'อาเรย์ของชื่อ Table ที่สัมพันธ์กับ Table หลักที่ต้องการลบข้อมูล [OPTION]'
        */
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

        /*
            - Description: 'Query คำสั่ง SQL ตั้งแต่ 1 คำสั่งขึ้นไป'
            - Parameter: 
                $sqlCmd = 'คำสั่ง SQL ทั้งหมด (คั่นแต่ละคำสั่งด้วยเครื่องหมาย ;)'
        */
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

        /*
            - Description: 'ตรวจสอบผู้ใช้งานที่เข้าสู่ระบบ และรีเทิร์นอาเรย์ของ SESSION ผู้ใช้งาน'
            - Parameter: 
                $dataArr[] = 'ข้อมูลของผู้ใช้งาน'
        */
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

        /*
            - Description: 'ออกจากระบบ และเคลียร์ SESSION ของผู้ใช้งาน'
        */
        public function logout() {
            if((!isset($_SESSION)))
                session_start();

            $_SESSION = [];
            session_destroy();

            return true;
        }

        /*
            - Description: 'ส่งอีเมล์ และสามารถแนบไฟล์ได้'
            - Parameter: 
                $addressEmailArr[] = 'อาเรย์ของอีเมล์'
                $addressEmailArr[]['emailName'] = 'ชื่ออีเมล์ผู้รับ'
                $addressEmailArr[]['emailSubject'] = 'หัวข้ออีเมล์ผู้รับ [OPTION]'
                $subject = 'หัวข้ออีเมล์'
                $msgHTML = 'ข้อความหรือ HTML สำหรับส่งอีเมล์'
                $ccEmailArr[] = 'อาเรย์ของอีเมล์สำหรับ CC [OPTION]'
                $ccEmailArr[]['emailName'] = 'ชื่ออีเมล์ผู้รับสำหรับ CC [OPTION]'
                $ccEmailArr[]['emailSubject'] = 'หัวข้ออีเมล์ผู้รับสำหรับ CC [OPTION]'
                $bccEmailArr[] = 'อาเรย์ของอีเมล์สำหรับ BCC [OPTION]'
                $bccEmailArr[]['emailName'] = 'ชื่ออีเมล์ผู้รับสำหรับ BCC [OPTION]'
                $bccEmailArr[]['emailSubject'] = 'หัวข้ออีเมล์ผู้รับสำหรับ BCC [OPTION]'
                $attacthment['filePath'] = 'ที่อยู่ไฟล์แนบ [OPTION]'
                $attacthment['fileSubject'] = 'หัวข้อไฟล์แนบ [OPTION]'
        */
        public function sendEmail($addressEmailArr = [], $subject = '', $msgHTML = '', $ccEmailArr = [], $bccEmailArr = [], $attacthment = []) {
            if((count($addressEmailArr) != 0) && (!empty($subject)) && (!empty($msgHTML))) {
                require("Library/PHPMailer/PHPMailerAutoload.php");

                $mail = new PHPMailer;
                $mail->CharSet = 'utf-8';
                $mail->IsSMTP();
                $mail->SMTPDebug = 0;
                $mail->Debugoutput = 'html';
                $mail->Host = 'smtp.live.com'; //--smtp.gmail.com | smtp.live.com
                $mail->Port = 587; //--587 | 25
                $mail->SMTPSecure = 'tls'; //--tls | ssl
                $mail->SMTPAuth = true;
                $mail->Username = 'itsara.ra.cs@hotmail.com';
                $mail->Password = 'IT1501033a';
                $mail->SetFrom('itsara.ra.cs@hotmail.com', 'อิศรา รากจันทึก-Hotmail');
                $mail->Subject = $subject;
                $mail->MsgHTML($msgHTML);

                foreach($addressEmailArr as $addressEmail) {
                    $mail->AddAddress($addressEmail['emailName'], $addressEmail['emailSubject']);
                }

                if((count($ccEmailArr) != 0)) {
                    foreach($ccEmailArr as $ccEmail) {
                        $mail->AddCC($ccEmail['emailName'], $ccEmail['emailSubject']);
                    }
                }

                if((count($bccEmailArr) != 0)) {
                    foreach($bccEmailArr as $bccEmail) {
                        $mail->AddBCC($bccEmail['emailName'], $bccEmail['emailSubject']);
                    }
                }
                
                if((!empty($attacthment['filePath'])))
                    $mail->AddAttachment($attacthment['filePath'], $attacthment['fileSubject']);

                if(!$mail->Send())
                    echo 'NOT PASS';//return false; //--$mail->ErrorInfo;
                
                echo 'PASS';//return true;
            }
        }
    }

    new APIService();
?>
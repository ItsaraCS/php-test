<?php
    require_once('APIService.php');

    class TestService extends APIService {
        public $insertDataArr = [
            ['email'=>'c', 'password'=>'1234', 'firstname'=>'c', 'lastname'=>'c'], 
            ['email'=>'d', 'password'=>'5678', 'firstname'=>'d', 'lastname'=>'d']
        ];

        public $updateDataArr = [
            ['id'=>8, 'email'=>'1', 'firstname'=>'1', 'lastname'=>'1'], 
            ['id'=>9, 'email'=>'2', 'firstname'=>'2', 'lastname'=>'2']
        ];

        public $insertDataMultiTableArr = [
            ['email'=>'a', 'password'=>'1234', 'firstname'=>'a', 'lastname'=>'a', 'secondaryTableArr'=>
                [
                    'tableName'=>'menus',
                    'foreignKey'=>'user_id',
                    'dataArr'=>[
                        ['menu_item'=>'1', 'menu_name'=>'Menu 1'],
                        ['menu_item'=>'2', 'menu_name'=>'Menu 2']
                    ]
                ],
                [
                    'tableName'=>'orders',
                    'foreignKey'=>'user_id',
                    'dataArr'=>[
                        ['order_code'=>'P001', 'order_name'=>'Order 1'],
                        ['order_code'=>'P002', 'order_name'=>'Order 2']
                    ]
                ]
            ], 
            ['email'=>'b', 'password'=>'5678', 'firstname'=>'b', 'lastname'=>'b', 'secondaryTableArr'=>
                [
                    'tableName'=>'menus',
                    'foreignKey'=>'user_id',
                    'dataArr'=>[
                        ['menu_item'=>'3', 'menu_name'=>'Menu 3'],
                        ['menu_item'=>'4', 'menu_name'=>'Menu 4']
                    ]
                ],
                [
                    'tableName'=>'orders',
                    'foreignKey'=>'user_id',
                    'dataArr'=>[
                        ['order_code'=>'P003', 'order_name'=>'Order 3'],
                        ['order_code'=>'P004', 'order_name'=>'Order 4']
                    ]
                ]
            ]
        ];

        public $updateDataMultiTableArr = [
            ['user_id'=>1, 'email'=>'aa', 'password'=>'4321', 'firstname'=>'aa', 'lastname'=>'aa', 'secondaryTableArr'=>
                [
                    'tableName'=>'menus',
                    'columnFilter'=>'menu_id',
                    'dataArr'=>[
                        ['menu_id'=>1, 'menu_item'=>'11', 'menu_name'=>'Menu 11'],
                        ['menu_id'=>2, 'menu_item'=>'22', 'menu_name'=>'Menu 22']
                    ]
                ],
                [
                    'tableName'=>'orders',
                    'columnFilter'=>'order_id',
                    'columnUpdateDTFilter'=>'update_at',
                    'dataArr'=>[
                        ['order_id'=>1, 'order_code'=>'P0011', 'order_name'=>'Order 11'],
                        ['order_id'=>2, 'order_code'=>'P0022', 'order_name'=>'Order 22']
                    ]
                ]
            ], 
            ['user_id'=>2, 'email'=>'bb', 'password'=>'8765', 'firstname'=>'bb', 'lastname'=>'bb', 'secondaryTableArr'=>
                [
                    'tableName'=>'menus',
                    'columnFilter'=>'menu_id',
                    'dataArr'=>[
                        ['menu_id'=>3, 'menu_item'=>'33', 'menu_name'=>'Menu 33'],
                        ['menu_id'=>4, 'menu_item'=>'44', 'menu_name'=>'Menu 44']
                    ]
                ],
                [
                    'tableName'=>'orders',
                    'columnFilter'=>'order_id',
                    'columnUpdateDTFilter'=>'update_at',
                    'dataArr'=>[
                        ['order_id'=>3, 'order_code'=>'P0033', 'order_name'=>'Order 33'],
                        ['order_id'=>4, 'order_code'=>'P0044', 'order_name'=>'Order 44']
                    ]
                ]
            ]
        ];
        
        public $deleteDataMultiTableArr = ['menus', 'orders'];

        public $getQueryMultiStatementSqlCmd = "SELECT * FROM menus; SELECT * FROM orders";

        public function __construct() {
            $this->connectDB();
            //$this->getColumnNameFromTable('users');
            //$this->getDataArray('users');
            //$this->getDataObject('users', 'email', 1);
            //$this->insertData('users', $this->insertDataArr);
            //$this->updateData('users', $this->updateDataArr, 'user_id');
            //$this->deleteData('users', 'user_id', 2);
            //$this->insertDataMultiTable('users', $this->insertDataMultiTableArr);
            //$this->updateDataMultiTable('users', $this->updateDataMultiTableArr, 'user_id');
            //$this->deleteDataMultiTable('users', 'user_id', 2, ['menus', 'orders']);
            //$this->getQueryMultiStatement($this->getQueryMultiStatementSqlCmd);
            //$this->checkLogin(['email'=>'a', 'password'=>"1' OR '1=1"]);
            //$this->login(['email'=>'a', 'password'=>'1234']);
            //$this->logout();

            /*$data = json_decode(file_get_contents('php://input'), true);
            $this->$data['funcName']($data['param']);*/
            $this->sendEmail(
                [
                    ['emailName'=>'itsara.ra.cs@gmail.com', 'emailSubject'=>'อิศรา รากจันทึก-Gmail'],
                    ['emailName'=>'itsara.ra.cs@hotmail.com', 'emailSubject'=>'อิศรา รากจันทึก-Hotmail']
                ],
                'ทดสอบการส่งอีเมล์', 
                "<h1>My Message</h1><br>
                <table width='100%' border='1'>
                <tr>
                <td><div align='center'><strong>My Message </strong></div></td>
                <td><div align='center'><font color='red'>My Message</font></div></td>
                <td><div align='center'><font size='2'>My Message</font></div></td>
                </tr>
                <tr>
                <td><div align='center'>My Message</div></td>
                <td><div align='center'>My Message</div></td>
                <td><div align='center'>My Message</div></td>
                </tr>
                <tr>
                <td><div align='center'>My Message</div></td>
                <td><div align='center'>My Message</div></td>
                <td><div align='center'>My Message</div></td>
                </tr>
                </table>", [], [],
                ['filePath'=>'Logo.jpg', 'fileSubject'=>'ทดสอบแนบไฟล์ภาพ']
            );
        }
    }

    new TestService();
?>
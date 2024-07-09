<?php
defined('BASEPATH') or exit('No direct script access allowed');
class AcctMigration extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Connection_model');
        $this->load->model('MainPage_model');
        $this->load->model('AcctSavingsImportMutation_model');
        $this->load->model('AcctCreditAccount_Import_model');
        $this->load->model('AcctProfitLossMigration_model');
        $this->load->model('AcctBalanceSheetMigration_model');
        $this->load->model('AcctSavingsAccountMigration_model');
        $this->load->model('AcctSicantikMigration_model');
        $this->load->model('AcctDepositoAccountMigration_model');
        $this->load->model('AcctSavingsCashMutation_model');
        $this->load->model('AcctSavingsBankMutation_model');
        $this->load->model('AcctSavingsAccount_model');
        $this->load->model('AcctCreditAccount_model');
        $this->load->model('Core_source_fund_model');
        $this->load->model('AcctDepositoAccount_model');
        $this->load->model('CoreMember_model');
        $this->load->helper('sistem');
        $this->load->helper('url');
        $this->load->database('default');
        $this->load->library('configuration');
        $this->load->library('fungsi');
        $this->load->library(['PHPExcel', 'PHPExcel/IOFactory']);
    }

    //** List */
    public function index()
    {
        $auth = $this->session->userdata('auth');
        $unique = $this->session->userdata('unique');
        $sesi = $this->session->userdata('filter-acctprofitlossmigration');
        if (!is_array($sesi)) {
            $sesi['start_date'] = date('Y-m-d');
            $sesi['end_date'] = date('Y-m-d');
        }
        $this->session->set_userdata('filter-acctprofitlossmigration', $sesi);

        $this->session->unset_userdata('addAcctProfitLossMigration-' . $unique['unique']);
        $this->session->unset_userdata('acctprofitlossmigrationtoken-' . $unique['unique']);

        // $this->AcctProfitLossMigration_model->truncate();

        $data['main_view']['content'] = 'AcctMigration/index';
        $this->load->view('MainPage_view', $data);
    }

    public function filter()
    {
        $data = [
            'start_date' => $this->input->post('start_date', true),
            'end_date' => $this->input->post('end_date', true),
        ];

        $this->session->set_userdata('filter-acctsavingsimportmutation', $data);
        redirect('savings-import-mutation');
    }

    public function reset_list()
    {
        $this->session->unset_userdata('filter-acctsavingsimportmutation');
        redirect('savings-import-mutation');
    }

    public function getAcctSavingsImportMutationList()
    {
        $auth = $this->session->userdata('auth');
        $sesi = $this->session->userdata('filter-acctsavingsimportmutation');
        if (!is_array($sesi)) {
            $sesi['start_date'] = date('Y-m-d');
            $sesi['end_date'] = date('Y-m-d');
        }
        $list = $this->AcctSavingsImportMutation_model->get_datatables_master($sesi['start_date'], $sesi['end_date']);
        $data = [];
        $no = $_POST['start'];
        foreach ($list as $val) {
            if ($val->mutation_id == 1) {
                $mutasi = 'Setoran';
            } else {
                $mutasi = 'Penarikan';
            }
            $no++;
            $row = [];
            $row[] = $no;
            $row[] = $this->AcctSavingsImportMutation_model->getCoreMemberName($val->member_id);
            $row[] = $this->AcctSavingsImportMutation_model->getAcctSavingsName($val->savings_id);
            $row[] = $this->AcctSavingsImportMutation_model->getAcctSavingsAccountNo($val->savings_account_id);
            $row[] = $mutasi;
            $row[] = date('d-m-Y', strtotime($val->savings_cash_mutation_date));
            $row[] = number_format($val->savings_cash_mutation_amount, 2);
            $row[] = $val->savings_cash_mutation_remark;
            $row[] = '<a href="' . base_url() . 'savings-import-mutation/print-note/' . $val->savings_cash_mutation_id . '" class="btn btn-xs btn-info" role="button"><i class="fa fa-print"></i> Kwitansi</a>';

            $data[] = $row;
        }
        $output = [
            'draw' => $_POST['draw'],
            'recordsTotal' => $this->AcctSavingsImportMutation_model->count_all_master($sesi['start_date'], $sesi['end_date']),
            'recordsFiltered' => $this->AcctSavingsImportMutation_model->count_filtered_master($sesi['start_date'], $sesi['end_date']),
            'data' => $data,
        ];
        echo json_encode($output);
    }

    public function getAcctSavingsImportMutationListBank()
    {
        $auth = $this->session->userdata('auth');
        $sesi = $this->session->userdata('filter-acctsavingsimportmutation');
        if (!is_array($sesi)) {
            $sesi['start_date'] = date('Y-m-d');
            $sesi['end_date'] = date('Y-m-d');
        }

        $list = $this->AcctSavingsImportMutation_model->get_datatables_master_bank($sesi['start_date'], $sesi['end_date']);

        $data = [];
        $no = $_POST['start'];
        foreach ($list as $val) {
            if ($val->mutation_id == 1) {
                $mutasi = 'Setoran';
            } else {
                $mutasi = 'Penarikan';
            }
            $no++;
            $row = [];
            $row[] = $no;
            $row[] = $this->AcctSavingsImportMutation_model->getCoreMemberName($val->member_id);
            $row[] = $this->AcctSavingsImportMutation_model->getAcctSavingsName($val->savings_id);
            $row[] = $this->AcctSavingsImportMutation_model->getAcctSavingsAccountNo($val->savings_account_id);
            $row[] = $mutasi;
            $row[] = $this->AcctSavingsImportMutation_model->getBankAccountName($val->bank_account_id);
            $row[] = date('d-m-Y', strtotime($val->savings_bank_mutation_date));
            $row[] = number_format($val->savings_bank_mutation_amount, 2);
            $row[] = $val->savings_bank_mutation_remark;
            $row[] = '<a href="' . base_url() . 'savings-import-mutation/print-note/' . $val->savings_bank_mutation_id . '" class="btn btn-xs btn-info" role="button"><i class="fa fa-print"></i> Kwitansi</a>';

            $data[] = $row;
        }

        $output = [
            'draw' => $_POST['draw'],
            'recordsTotal' => $this->AcctSavingsImportMutation_model->count_all_master($sesi['start_date'], $sesi['end_date']),
            'recordsFiltered' => $this->AcctSavingsImportMutation_model->count_filtered_master($sesi['start_date'], $sesi['end_date']),
            'data' => $data,
        ];

        echo json_encode($output);
    }

    public function detailAcctSavingsImportMutation()
    {
        $auth = $this->session->userdata('auth');
        $debt_repayment_id = $this->uri->segment(3);

        $data['main_view']['debtrepaymentdetail'] = $this->AcctSavingsImportMutation_model->getAcctSavingsImportMutation_Detail($debt_repayment_id);
        $data['main_view']['debtrepaymentitem'] = $this->AcctSavingsImportMutation_model->getAcctSavingsImportMutationItem($debt_repayment_id);
        $data['main_view']['content'] = 'AcctSavingsImportMutation/DetailAcctSavingsImportMutation_view';
        $this->load->view('MainPage_view', $data);
    }

    //** form add Laba rugi */
    public function addAcctProfitLossMigration()
    {
        $auth = $this->session->userdata('auth');
        $unique = $this->session->userdata('unique');
        $token = $this->session->userdata('acctprofitlossmigrationtoken-' . $unique['unique']);

        if (empty($token)) {
            $token = md5(date('Y-m-d H:i:s'));
            $this->session->set_userdata('acctprofitlossmigrationtoken-' . $unique['unique'], $token);
            $this->session->unset_userdata('addprofitlossmigration-' . $unique['unique']);
        }
        // Define monthlist
        $monthlist = array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );

        // Define yearlist (e.g., from 2000 to current year)
        $yearlist = array();
        $current_year = date('Y');
        for ($year = 2000; $year <= $current_year; $year++) {
            $yearlist[$year] = $year;
        }

        $data['main_view']['monthlist'] = $monthlist;
        $data['main_view']['yearlist'] = $yearlist;    
        $data['main_view']['profitloss'] = $this->AcctProfitLossMigration_model->getdataProfitLoss();
        $data['main_view']['content'] = 'AcctMigration/FormAddAcctProfitLossMigration_view';
        $this->load->view('MainPage_view', $data);
    }

    //** Add Array laba rugi Excel To Db*/
    public function addArrayAcctProfitLossMigration()
    {
        $auth = $this->session->userdata('auth');

        $this->AcctProfitLossMigration_model->truncateAcctProfitLossMigration();

        $fileName = $_FILES['excel_file']['name'];
        $fileSize = $_FILES['excel_file']['size'];
        $fileError = $_FILES['excel_file']['error'];
        $fileType = $_FILES['excel_file']['type'];

        $config['upload_path'] = './assets/';
        $config['file_name'] = $fileName;
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size'] = 10000;

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('excel_file')) {
            $msg =
                "<div class='alert alert-danger alert-dismissable'>
                    " . $this->upload->display_errors('', '') . "
                </div> ";
            $this->session->set_userdata('message', $msg);
            redirect('migration/add-profit-loss');
        } else {
        $media = $this->upload->data('excel_file');
        $inputFileName = './assets/' . $config['file_name'];

        try {
            $inputFileType = IOFactory::identify($inputFileName);
            $objReader = IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);

                    $data = [
                        'id'                => $rowData[0][0],
                        'account_code'      => $rowData[0][1],
                        'account_name'      => $rowData[0][2],
                        'account_amount'    => $rowData[0][3],
                        'test'              => $rowData[0][4],
                    ];
                        $this->AcctProfitLossMigration_model->insertAcctProfitLossMigration($data);
            }
            unlink($inputFileName);
            // echo json_encode($data);
            // exit;
            $msg = "<div class='alert alert-success'>                
							Import Data laba rugi Excel
						</div> ";
            $this->session->set_userdata('message', $msg);
            redirect('migration/add-profit-loss');
        }
    }

    public function processAddAcctProfitLossMigration() {
        $auth = $this->session->userdata('auth');
    
        $monthperiod = $this->input->post('month_period');
        $yearperiod = $this->input->post('year_period');
        if($this->AcctProfitLossMigration_model->updateProfitLossAmount($monthperiod, $yearperiod) == true) {
            $this->AcctProfitLossMigration_model->truncateAcctProfitLossMigration();

            $msg = "<div class='alert alert-success alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Berhasil
                    </div>";
        } else {
            $msg = "<div class='alert alert-danger alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Gagal
                    </div>";
        }
    
        $this->session->set_userdata('message', $msg);
        redirect('migration/add-profit-loss');
    }

    //** form add Neraca */
    public function addAcctBalanceSheetMigration()
    {
        $auth = $this->session->userdata('auth');
        $unique = $this->session->userdata('unique');
        $token = $this->session->userdata('acctbalancesheetmigrationtoken-' . $unique['unique']);

        if (empty($token)) {
            $token = md5(date('Y-m-d H:i:s'));
            $this->session->set_userdata('acctbalancesheetmigrationtoken-' . $unique['unique'], $token);
            $this->session->unset_userdata('addbalancesheetmigration-' . $unique['unique']);
        }
        // Define monthlist
        $monthlist = array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );

        // Define yearlist (e.g., from 2000 to current year)
        $yearlist = array();
        $current_year = date('Y');
        for ($year = 2000; $year <= $current_year; $year++) {
            $yearlist[$year] = $year;
        }

        $data['main_view']['monthlist'] = $monthlist;
        $data['main_view']['yearlist'] = $yearlist;    
        $data['main_view']['balancesheet'] = $this->AcctBalanceSheetMigration_model->getdataBalanceSheet();
        $data['main_view']['content'] = 'AcctMigration/FormAddAcctBalanceSheetMigration_view';
        $this->load->view('MainPage_view', $data);
    }

    //** Add Array Neraca Excel To Db*/
    public function addArrayAcctBalanceSheetMigration()
    {
        $auth = $this->session->userdata('auth');

        $this->AcctBalanceSheetMigration_model->truncateAcctBalanceSheetMigration();

        $fileName = $_FILES['excel_file']['name'];
        $fileSize = $_FILES['excel_file']['size'];
        $fileError = $_FILES['excel_file']['error'];
        $fileType = $_FILES['excel_file']['type'];

        $config['upload_path'] = './assets/';
        $config['file_name'] = $fileName;
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size'] = 10000;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel_file')) {
            $msg = "<div class='alert alert-danger alert-dismissable'>" . $this->upload->display_errors('', '') . "</div> ";
            $this->session->set_userdata('message', $msg);
            redirect('migration/add-balance-sheet');
        } else {
            $media = $this->upload->data();
            $inputFileName = './assets/' . $media['file_name'];

            try {
                $inputFileType = IOFactory::identify($inputFileName);
                $objReader = IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);

                   // Debugging: echo data to check
                    // echo '<pre>';
                    // print_r($rowData);
                    // echo '</pre>';

                $data = [
                    'id' => $rowData[0][0],
                    'account_code1' => $rowData[0][1],
                    'account_name1' => $rowData[0][2],
                    'opening_balance1' => $rowData[0][3],
                    'account_code2' => $rowData[0][4],
                    'account_name2' => $rowData[0][5],
                    'opening_balance2' => $rowData[0][6],
                    'test1' => $rowData[0][7],
                    'test2' => $rowData[0][8],
                ];
                 // Debugging: echo data array before inserting into database
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';

                $this->AcctBalanceSheetMigration_model->insertAcctBalanceSheetMigration($data);
            }

            unlink($inputFileName);
            $msg = "<div class='alert alert-success'>Import Data Opening Balance Excel</div> ";
            $this->session->set_userdata('message', $msg);
            redirect('migration/add-balance-sheet');
        }
    }

    //** Save Neraca */
    public function processAddAcctBalanceSheetMigration() {
        $auth = $this->session->userdata('auth');
    
        $monthperiod = $this->input->post('month_period');
        $yearperiod = $this->input->post('year_period');
        if($this->AcctBalanceSheetMigration_model->updateBalanceSheetAmount($monthperiod, $yearperiod) == true) {
            $this->AcctBalanceSheetMigration_model->truncateAcctBalanceSheetMigration();

            $msg = "<div class='alert alert-success alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Berhasil
                    </div>";
        } else {
            $msg = "<div class='alert alert-danger alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Gagal
                    </div>";
        }
    
        $this->session->set_userdata('message', $msg);
        redirect('migration/add-balance-sheet');
    }

    //** form add tabungan */
    public function addAcctSavingsAccountMigration()
    {
        $auth = $this->session->userdata('auth');
        $unique = $this->session->userdata('unique');
        $token = $this->session->userdata('acctsavingsaccountmigrationtoken-' . $unique['unique']);

        if (empty($token)) {
            $token = md5(date('Y-m-d H:i:s'));
            $this->session->set_userdata('acctsavingsaccountmigrationtoken-' . $unique['unique'], $token);
            $this->session->unset_userdata('addsavingsaccountmigration-' . $unique['unique']);
        }
        // Define monthlist
        $monthlist = array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );

        // Define yearlist (e.g., from 2000 to current year)
        $yearlist = array();
        $current_year = date('Y');
        for ($year = 2000; $year <= $current_year; $year++) {
            $yearlist[$year] = $year;
        }

        $data['main_view']['monthlist'] = $monthlist;
        $data['main_view']['yearlist'] = $yearlist;    
        $data['main_view']['savingsaccount'] = $this->AcctSavingsAccountMigration_model->getdataSavingsAccount();
        $data['main_view']['content'] = 'AcctMigration/FormAddAcctSavingsAccountMigration_view';
        $this->load->view('MainPage_view', $data);
    }

    //** Add Array tabungan Excel To Db*/
    public function addArrayAcctSavingsAccountMigration()
    {
        $auth = $this->session->userdata('auth');

        $this->AcctSavingsAccountMigration_model->truncateAcctSavingsAccountMigration();

        $fileName = $_FILES['excel_file']['name'];
        $fileSize = $_FILES['excel_file']['size'];
        $fileError = $_FILES['excel_file']['error'];
        $fileType = $_FILES['excel_file']['type'];

        $config['upload_path'] = './assets/';
        $config['file_name'] = $fileName;
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size'] = 10000;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel_file')) {
            $msg = "<div class='alert alert-danger alert-dismissable'>" . $this->upload->display_errors('', '') . "</div> ";
            $this->session->set_userdata('message', $msg);
            redirect('migration/add-savings-account');
        } else {
            $media = $this->upload->data();
            $inputFileName = './assets/' . $media['file_name'];

            try {
                $inputFileType = IOFactory::identify($inputFileName);
                $objReader = IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);

                // Debugging: echo data to check
                // echo '<pre>';
                // print_r($rowData);
                // echo '</pre>';

                $data = [
                    'no_rek' => $rowData[0][0],
                    'no_agt' => $rowData[0][1],
                    'member_id' => $rowData[0][2],
                    'nama' => $rowData[0][3],
                    'saldo_akhr' => $rowData[0][4],
                ];
                // Debugging: echo data array before inserting into database
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';

                $this->AcctSavingsAccountMigration_model->insertAcctSavingsAccountMigration($data);
            }

            $updateResult = $this->AcctSavingsAccountMigration_model->updateMemberId();
            // Logging: echo or log the value of $updateResult
            // echo 'updateMemberId result: ';
            // var_dump($updateResult);
            // You can also log this to a file using error_log()
            error_log('updateMemberId result: ' . print_r($updateResult, true));

            if ($updateResult) {
                unlink($inputFileName);
                $msg = "<div class='alert alert-success'>Import Data Tabungan Excel berhasil</div> ";
                $this->session->set_userdata('message', $msg);
                redirect('migration/add-savings-account');
            } else {
                unlink($inputFileName);
                $msg = "<div class='alert alert-danger'>Import Data Tabungan Excel gagal</div> ";
                $this->session->set_userdata('message', $msg);
                redirect('migration/add-savings-account');
            }
        }
    }

    //** Save tabungan */
    public function processAddAcctSavingsAccountMigration() {
        $auth = $this->session->userdata('auth');
        if($this->AcctSavingsAccountMigration_model->insertSavingsAccountAmount() == true) {
            $this->AcctSavingsAccountMigration_model->truncateAcctSavingsAccountMigration();

            $msg = "<div class='alert alert-success alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Berhasil
                    </div>";
        } else {
            $msg = "<div class='alert alert-danger alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Gagal
                    </div>";
        }
    
        $this->session->set_userdata('message', $msg);
        redirect('migration/add-savings-account');
    }

    //** hapus data tabungan lama */
    public function processtruncateAcctSavingsAccountOld() {
        $auth = $this->session->userdata('auth');
       
        if($this->AcctSavingsAccountMigration_model->truncateAcctSavingsAccountOld() == true) {
            $msg = "<div class='alert alert-success alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses hapus Berhasil
                    </div>";
        } else {
            $msg = "<div class='alert alert-danger alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses hapus Gagal
                    </div>";
        }
    
        $this->session->set_userdata('message', $msg);
        redirect('migration/add-savings-account');
    }
    
    //** form add Sicantik */
    public function addSicantikMigration()
    {
        $auth = $this->session->userdata('auth');
        $unique = $this->session->userdata('unique');
        $token = $this->session->userdata('acctsavingsaccountmigrationtoken-' . $unique['unique']);

        if (empty($token)) {
            $token = md5(date('Y-m-d H:i:s'));
            $this->session->set_userdata('acctsavingsaccountmigrationtoken-' . $unique['unique'], $token);
            $this->session->unset_userdata('addsavingsaccountmigration-' . $unique['unique']);
        }
        // Define monthlist
        $monthlist = array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );

        // Define yearlist (e.g., from 2000 to current year)
        $yearlist = array();
        $current_year = date('Y');
        for ($year = 2000; $year <= $current_year; $year++) {
            $yearlist[$year] = $year;
        }

        $data['main_view']['monthlist'] = $monthlist;
        $data['main_view']['yearlist'] = $yearlist;    
        $data['main_view']['savingsaccount'] = $this->AcctSicantikMigration_model->getdataSicantik();
        $data['main_view']['content'] = 'AcctMigration/FormAddAcctSicantikMigration_view';
        $this->load->view('MainPage_view', $data);
    }

    //** Add Array Sicantik Excel To Db*/
    public function addArraySicantikMigration()
    {
        $auth = $this->session->userdata('auth');

        $this->AcctSicantikMigration_model->truncateAcctSicantikMigration();

        $fileName = $_FILES['excel_file']['name'];
        $fileSize = $_FILES['excel_file']['size'];
        $fileError = $_FILES['excel_file']['error'];
        $fileType = $_FILES['excel_file']['type'];

        $config['upload_path'] = './assets/';
        $config['file_name'] = $fileName;
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size'] = 10000;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel_file')) {
            $msg = "<div class='alert alert-danger alert-dismissable'>" . $this->upload->display_errors('', '') . "</div> ";
            $this->session->set_userdata('message', $msg);
            redirect('migration/add-sicantik');
        } else {
            $media = $this->upload->data();
            $inputFileName = './assets/' . $media['file_name'];

            try {
                $inputFileType = IOFactory::identify($inputFileName);
                $objReader = IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);

                // Debugging: echo data to check
                // echo '<pre>';
                // print_r($rowData);
                // echo '</pre>';

                $data = [
                    'no_rek' => $rowData[0][0],
                    'member_id' => $rowData[0][1],
                    'no_agt' => $rowData[0][2],
                    'nama' => $rowData[0][3],
                    'tanggal_buka' => $rowData[0][4],
                    'saldo_akhr' => $rowData[0][5],
                    'setoran_awal' => $rowData[0][6],
                ];
                // Debugging: echo data array before inserting into database
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';

                $this->AcctSicantikMigration_model->insertAcctSicantikMigration($data);
            }

            $updateResult = $this->AcctSicantikMigration_model->updateMemberId();
            // Logging: echo or log the value of $updateResult
            // echo 'updateMemberId result: ';
            // var_dump($updateResult);
            // You can also log this to a file using error_log()
            error_log('updateMemberId result: ' . print_r($updateResult, true));

            if ($updateResult) {
                unlink($inputFileName);
                $msg = "<div class='alert alert-success'>Import Data Sicantik Excel berhasil</div> ";
                $this->session->set_userdata('message', $msg);
                redirect('migration/add-sicantik');
            } else {
                unlink($inputFileName);
                $msg = "<div class='alert alert-danger'>Import Data Sicantik Excel gagal</div> ";
                $this->session->set_userdata('message', $msg);
                redirect('migration/add-sicantik');
            }
        }
    }

    //** Save Sicantik */
    public function processAddSicantikMigration() {
        $auth = $this->session->userdata('auth');
        if($this->AcctSicantikMigration_model->insertSicantikAmount() == true) {
            $this->AcctSicantikMigration_model->truncateAcctSicantikMigration();

            $msg = "<div class='alert alert-success alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Berhasil
                    </div>";
        } else {
            $msg = "<div class='alert alert-danger alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Gagal
                    </div>";
        }
    
        $this->session->set_userdata('message', $msg);
        redirect('migration/add-sicantik');
    }

    //** form add Deposito */
    public function addDepositoAccountMigration()
    {
        $auth = $this->session->userdata('auth');
        $unique = $this->session->userdata('unique');
        $token = $this->session->userdata('acctdepositoaccountmigrationtoken-' . $unique['unique']);

        if (empty($token)) {
            $token = md5(date('Y-m-d H:i:s'));
            $this->session->set_userdata('acctdepositoaccountmigrationtoken-' . $unique['unique'], $token);
            $this->session->unset_userdata('adddepositoaccountmigration-' . $unique['unique']);
        }
        // Define monthlist
        $monthlist = array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );

        // Define yearlist (e.g., from 2000 to current year)
        $yearlist = array();
        $current_year = date('Y');
        for ($year = 2000; $year <= $current_year; $year++) {
            $yearlist[$year] = $year;
        }

        $data['main_view']['monthlist'] = $monthlist;
        $data['main_view']['yearlist'] = $yearlist;    
        $data['main_view']['depositoaccount'] = $this->AcctDepositoAccountMigration_model->getdataDeposito();
        $data['main_view']['content'] = 'AcctMigration/FormAddAcctDepositoAccountMigration_view';
        $this->load->view('MainPage_view', $data);
    }

    //** Add Array Deposito Excel To Db*/
    public function addArrayDepositoAccountMigration()
    {
        $auth = $this->session->userdata('auth');

        $this->AcctDepositoAccountMigration_model->truncateAcctDepositoMigration();

        $fileName = $_FILES['excel_file']['name'];
        $fileSize = $_FILES['excel_file']['size'];
        $fileError = $_FILES['excel_file']['error'];
        $fileType = $_FILES['excel_file']['type'];

        $config['upload_path'] = './assets/';
        $config['file_name'] = $fileName;
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size'] = 10000;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel_file')) {
            $msg = "<div class='alert alert-danger alert-dismissable'>" . $this->upload->display_errors('', '') . "</div> ";
            $this->session->set_userdata('message', $msg);
            redirect('migration/add-deposito-account');
        } else {
            $media = $this->upload->data();
            $inputFileName = './assets/' . $media['file_name'];

            try {
                $inputFileType = IOFactory::identify($inputFileName);
                $objReader = IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);

                // Debugging: echo data to check
                // echo '<pre>';
                // print_r($rowData);
                // echo '</pre>';

                 // Konversi tanggal ke format yang sesuai
                    $tanggal_buka = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($rowData[0][9]));
                    $jatuh_tempo = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($rowData[0][10]));

                    // Debugging: Verifikasi tanggal
                    // echo 'Tanggal Buka: ' . $tanggal_buka . ', Jatuh Tempo: ' . $jatuh_tempo;

                    $data = [
                        'id' => $rowData[0][0],
                        'no_berjangka' => $rowData[0][1],
                        'no_anggota' => $rowData[0][2],
                        'member_id' => $rowData[0][3],
                        'deposito_id' => $rowData[0][4],
                        'savings_account_id' => $rowData[0][5],
                        'no_tabungan' => $rowData[0][6],
                        'nama' => $rowData[0][7],
                        'jangka_waktu' => $rowData[0][8],
                        'tanggal_buka' => $tanggal_buka,
                        'jatuh_tempo' => $jatuh_tempo,
                        'saldo' => $rowData[0][11],
                        'suku_bunga' => $rowData[0][12],
                        'sk_bg' => $rowData[0][13],
                    ];
                // Debugging: echo data array before inserting into database
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';

                $this->AcctDepositoAccountMigration_model->insertAcctDepositoMigration($data);
            }

            //update member
            $memberid = $this->AcctDepositoAccountMigration_model->updateMemberId();
            //update savings id
            if($memberid){
                $savingaccount = $this->AcctDepositoAccountMigration_model->updateSavingsAccountId();
                if($savingaccount){
                    //sk bunga
                    $skbg = $this->AcctDepositoAccountMigration_model->updateSk_Bg();
                    if($skbg){
                       //update deposito id
                        $updateResult = $this->AcctDepositoAccountMigration_model->updateDepositoId(); 
                    }
                }
            }
            
            error_log('updateMemberId result: ' . print_r($memberid, true));

            if ($memberid) {
                unlink($inputFileName);
                $msg = "<div class='alert alert-success'>Import Data Deposito Excel berhasil</div> ";
                $this->session->set_userdata('message', $msg);
                redirect('migration/add-deposito-account');
            } else {
                unlink($inputFileName);
                $msg = "<div class='alert alert-danger'>Import Data Deposito Excel gagal</div> ";
                $this->session->set_userdata('message', $msg);
                redirect('migration/add-deposito-account');
            }
        }
    }

    //** Save Deposito */
    public function processAddDepositoAccountMigration() {
        $auth = $this->session->userdata('auth');
        if($this->AcctDepositoAccountMigration_model->insertDepositoAmount() == true) {

            $depositoNo = $this->AcctDepositoAccountMigration_model->updateDepositoAccount();

            if($depositoNo){
                $this->AcctDepositoAccountMigration_model->truncateAcctDepositoMigration();
            }

            $msg = "<div class='alert alert-success alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Berhasil
                    </div>";
        } else {
            $msg = "<div class='alert alert-danger alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses Migrasi Gagal
                    </div>";
        }
    
        $this->session->set_userdata('message', $msg);
        redirect('migration/add-deposito-account');
    }

    //** hapus data migrasi deposit lama */
    public function processtruncateMigrasiDeposito() {
        $auth = $this->session->userdata('auth');
       
        if($this->AcctDepositoAccountMigration_model->truncateAcctDepositoMigration() == true) {
            $msg = "<div class='alert alert-success alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses hapus Berhasil
                    </div>";
        } else {
            $msg = "<div class='alert alert-danger alert-dismissable'>  
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>                     
                    Proses hapus Gagal
                    </div>";
        }
    
        $this->session->set_userdata('message', $msg);
        redirect('migration/add-deposito-account');
    }
    
    public function function_elements_add()
    {
        $unique = $this->session->userdata('unique');
        $name = $this->input->post('name', true);
        $value = $this->input->post('value', true);
        $sessions = $this->session->userdata('addacctsavingsimport-' . $unique['unique']);
        $sessions[$name] = $value;
        $this->session->set_userdata('addacctsavingsimport-' . $unique['unique'], $sessions);
    }
}
?>

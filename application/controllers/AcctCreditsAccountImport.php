<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctCreditsAccountImport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctSavingsImportMutation_model');
			$this->load->model('AcctCreditAccount_Import_model');
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
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		//** List */
		public function index(){
			$auth 		= $this->session->userdata('auth');
			$unique 	= $this->session->userdata('unique');
			$sesi		= $this->session->userdata('filter-acctcreditsaccountimport');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');				
			}
			$this->session->set_userdata('filter-acctcreditsaccountimport', $sesi);

			$this->session->unset_userdata('addAcctCreditsAccountImport-'.$unique['unique']);	
			$this->session->unset_userdata('acctcreditsaccounttoken-'.$unique['unique']);
			// $this->session->unset_userdata('acctacctcreditsaccountimporttokenedit-'.$unique['unique']);
			// $this->session->unset_userdata('addacctsavingsimport-'.$unique['unique']);

			$this->AcctCreditAccount_Import_model->truncateAcctCreditsAccountImport();

			$data['main_view']['acctcreditsaccountimport']			= $this->AcctCreditAccount_Import_model->getAcctCreditsAccountImport();
			$data['main_view']['content']							= 'AcctCreditsAccountImport/ListAcctCreditAccountImport_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				"start_date" 	=> $this->input->post('start_date',true),
				"end_date" 		=> $this->input->post('end_date',true),
			);

			$this->session->set_userdata('filter-acctsavingsimportmutation',$data);
			redirect('savings-import-mutation');
		}

		public function reset_list(){
			$this->session->unset_userdata('filter-acctsavingsimportmutation');
			redirect('savings-import-mutation');
		}
		
		public function getAcctSavingsImportMutationList(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctsavingsimportmutation');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
			}

			$list = $this->AcctSavingsImportMutation_model->get_datatables_master($sesi['start_date'], $sesi['end_date']);

	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $val) {
				if($val->mutation_id == 1){
					$mutasi = "Setoran";
				}else{
					$mutasi = "Penarikan";
				}
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $this->AcctSavingsImportMutation_model->getCoreMemberName($val->member_id);
	            $row[] = $this->AcctSavingsImportMutation_model->getAcctSavingsName($val->savings_id);
	            $row[] = $this->AcctSavingsImportMutation_model->getAcctSavingsAccountNo($val->savings_account_id);
	            $row[] = $mutasi;
	            $row[] = date('d-m-Y', strtotime($val->savings_cash_mutation_date));
	            $row[] = number_format($val->savings_cash_mutation_amount, 2);
	            $row[] = $val->savings_cash_mutation_remark;
				$row[] = '<a href="'.base_url().'savings-import-mutation/print-note/'.$val->savings_cash_mutation_id.'" class="btn btn-xs btn-info" role="button"><i class="fa fa-print"></i> Kwitansi</a>';
	            
	            $data[] = $row;
	        }

	        $output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->AcctSavingsImportMutation_model->count_all_master($sesi['start_date'], $sesi['end_date']),
				"recordsFiltered" => $this->AcctSavingsImportMutation_model->count_filtered_master($sesi['start_date'], $sesi['end_date']),
				"data" => $data,
			);

	        echo json_encode($output);
		}
		
		public function getAcctSavingsImportMutationListBank(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctsavingsimportmutation');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
			}

			$list = $this->AcctSavingsImportMutation_model->get_datatables_master_bank($sesi['start_date'], $sesi['end_date']);

	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $val) {
				if($val->mutation_id == 1){
					$mutasi = "Setoran";
				}else{
					$mutasi = "Penarikan";
				}
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $this->AcctSavingsImportMutation_model->getCoreMemberName($val->member_id);
	            $row[] = $this->AcctSavingsImportMutation_model->getAcctSavingsName($val->savings_id);
	            $row[] = $this->AcctSavingsImportMutation_model->getAcctSavingsAccountNo($val->savings_account_id);
	            $row[] = $mutasi;
	            $row[] = $this->AcctSavingsImportMutation_model->getBankAccountName($val->bank_account_id);
	            $row[] = date('d-m-Y', strtotime($val->savings_bank_mutation_date));
	            $row[] = number_format($val->savings_bank_mutation_amount, 2);
	            $row[] = $val->savings_bank_mutation_remark;
				$row[] = '<a href="'.base_url().'savings-import-mutation/print-note/'.$val->savings_bank_mutation_id.'" class="btn btn-xs btn-info" role="button"><i class="fa fa-print"></i> Kwitansi</a>';
	            
	            $data[] = $row;
	        }

	        $output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->AcctSavingsImportMutation_model->count_all_master($sesi['start_date'], $sesi['end_date']),
				"recordsFiltered" => $this->AcctSavingsImportMutation_model->count_filtered_master($sesi['start_date'], $sesi['end_date']),
				"data" => $data,
			);

	        echo json_encode($output);
		}

		public function detailAcctSavingsImportMutation(){
			$auth 				= $this->session->userdata('auth');
			$debt_repayment_id 	= $this->uri->segment(3);

			$data['main_view']['debtrepaymentdetail']		= $this->AcctSavingsImportMutation_model->getAcctSavingsImportMutation_Detail($debt_repayment_id);
			$data['main_view']['debtrepaymentitem']			= $this->AcctSavingsImportMutation_model->getAcctSavingsImportMutationItem($debt_repayment_id);
			$data['main_view']['content']					= 'AcctSavingsImportMutation/DetailAcctSavingsImportMutation_view';
			$this->load->view('MainPage_view',$data);
		}
		
		//** form add */
		public function addAcctCreditsAccountImport(){
			$auth 	= $this->session->userdata('auth');
			$unique = $this->session->userdata('unique');
			$token 	= $this->session->userdata('acctcreditsaccounttoken-'.$unique['unique']);

			if(empty($token)){
				$token = md5(date('Y-m-d H:i:s'));
				$this->session->set_userdata('acctcreditsaccounttoken-'.$unique['unique'], $token);
				$this->session->unset_userdata('addcreditaccount-' . $unique['unique']);
			}

			$data['main_view']['memberidentity']			= $this->configuration->MemberIdentity();
			$data['main_view']['membergender']				= $this->configuration->MemberGender();
			$data['main_view']['paymentperiod']				= $this->configuration->CreditsPaymentPeriod();
			$data['main_view']['methods']					= $this->configuration->AcquittanceMethodReal();
			$data['main_view']['paymentpreference']			= $this->configuration->PaymentPreference();
			$data['main_view']['paymenttype']				= $this->configuration->PaymentType();
			$data['main_view']['coreoffice']				= create_double($this->AcctCreditAccount_model->getCoreOffice(),'office_id', 'office_name');
			$data['main_view']['sumberdana']				= create_double($this->Core_source_fund_model->getData(),'source_fund_id', 'source_fund_name');
			$data['main_view']['acctsavingsaccount']		= create_double($this->AcctDepositoAccount_model->getAcctSavingsAccount($auth['branch_id']),'savings_account_id', 'savings_account_no');
			$data['main_view']['creditid']					= create_double($this->AcctCreditAccount_model->getAcctCredits(),'credits_id', 'credits_name');
			$data['main_view']['acctbankaccount']			= create_double($this->AcctCreditAccount_model->getBankAccount(),'bank_account_id', 'bank_account_name');
			$data['main_view']['coremember']				= $this->CoreMember_model->getCoreMember_Detail($this->uri->segment(3));
			$data['main_view']['memberacctcreditsaccount']	= $this->AcctCreditAccount_model->getMemberAcctCreditsAccount($this->uri->segment(3));
			$data['main_view']['content']					= 'AcctCreditsAccountImport/FormAddAcctCreditsAccountImport_view';
			$this->load->view('MainPage_view',$data);
		}

		//** Add Array */
		public function addArrayAcctCreditsAccountImport(){
			$auth 		= $this->session->userdata('auth');

			$this->AcctCreditAccount_Import_model->truncateAcctCreditsAccountImport();

			$fileName 	= $_FILES['excel_file']['name'];
			$fileSize 	= $_FILES['excel_file']['size'];
			$fileError 	= $_FILES['excel_file']['error'];
			$fileType 	= $_FILES['excel_file']['type'];

			$config['upload_path'] 		= './assets/';
            $config['file_name'] 		= $fileName;
            $config['allowed_types'] 	= 'xls|xlsx';
            $config['max_size']        	= 10000;

			$this->load->library('upload');
            $this->upload->initialize($config);

			if(! $this->upload->do_upload('excel_file') ){
				$msg = "<div class='alert alert-danger alert-dismissable'>
					".$this->upload->display_errors('', '')."
					</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('credit-account-import/add');
			}else{
				$media 			= $this->upload->data('excel_file');
				$inputFileName 	= './assets/'.$config['file_name'];

				try {
					$inputFileType 	= IOFactory::identify($inputFileName);
					$objReader 		= IOFactory::createReader($inputFileType);
					$objPHPExcel 	= $objReader->load($inputFileName);
				} catch(Exception $e) {
					die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
				}

				$sheet 			= $objPHPExcel->getSheet(0);
				$highestRow 	= $sheet->getHighestRow();
				$highestColumn 	= $sheet->getHighestColumn();

				for ($row = 2; $row <= $highestRow; $row++){
					$rowData 	= $sheet->rangeToArray('A'.$row.':'.$highestColumn.$row, NULL, TRUE, FALSE);

					$member_id 				= $this->AcctCreditAccount_Import_model->getCoreMemberID($rowData[0][0]);

					// echo json_encode($member_id);
					// exit;
					// $savings_account_id 	= $this->AcctCreditAccount_Import_model->getSavingsAccountID($rowData[0][1]);

					// $this->form_validation->set_rules('credit_id', 'jenis Pinjaman', 'required');
					// $this->form_validation->set_rules('credits_account_last_balance_principal', 'Pinjaman', 'required');
					// $this->form_validation->set_rules('credit_account_interest', 'Bunga Per Bulan', 'required');
					// $this->form_validation->set_rules('payment_type_id', 'Jenis Angsuran', 'required');
					// $this->form_validation->set_rules('payment_period', 'Angsuran Tiap', 'required');
					// $this->form_validation->set_rules('credit_account_period', 'Jangka Waktu', 'required');
					// $this->form_validation->set_rules('office_id', 'Business Officer (BO)', 'required');
					// $this->form_validation->set_rules('sumberdana', 'Sumber Dana', 'required');
					
					// if($this->form_validation->run()==true){
					$data = array (
						"credits_account_date" 				=> tgltodb($this->input->post('credit_account_date',true)),
						"office_id"							=> $this->input->post('office_id',true),
						"source_fund_id"					=> $this->input->post('sumberdana',true),
						"credits_id"						=> $this->input->post('credit_id',true),
						"branch_id"							=> $auth['branch_id'],
						"payment_preference_id"				=> $this->input->post('payment_preference_id',true),
						"payment_type_id"					=> $this->input->post('payment_type_id',true),
						"method_id"							=> $this->input->post('method_id',true),
						"bank_account_id"					=> $this->input->post('bank_account_id',true),
						"credits_payment_period"			=> $this->input->post('payment_period',true),
						"credits_account_payment_date"		=> tgltodb($this->input->post('credit_account_payment_to',true)),
						"credits_account_due_date"			=> tgltodb($this->input->post('credit_account_due_date',true)),

						//import
						"member_id"							=> $member_id,
						"savings_account_id"				=> $rowData[0][1],
						"credits_account_serial"			=> $rowData[0][2],
						"credits_account_period"			=> $rowData[0][3],
						"credits_account_amount"			=> $rowData[0][4],
						"credits_account_interest"			=> $rowData[0][5],
						"credits_account_special"			=> $rowData[0][6],
						"credits_account_adm_cost"			=> $rowData[0][7],
						"credits_account_insurance"			=> $rowData[0][8],
						"credits_account_discount"			=> $rowData[0][9],
						"credits_account_remark"			=> $rowData[0][10],
						"credits_account_bank_name"			=> $rowData[0][11],
						"credits_account_bank_account"		=> $rowData[0][12],
						"credits_account_bank_owner"		=> $rowData[0][13],
						"credits_account_amount_received"	=> $rowData[0][14],
						"credits_account_principal_amount"	=> $rowData[0][15],
						"credits_account_interest_amount"	=> $rowData[0][16],
						"credits_account_payment_amount"	=> $rowData[0][17],
						"credits_account_last_balance"		=> $rowData[0][18],
						"credits_account_token" 			=> $this->input->post('credits_account_token',true).$val['member_id'],
						"data_state"						=> 0,
						"created_id"						=> $auth['user_id'],
						"created_on"						=> date('Y-m-d H:i:s'),
					);


					// if($data['member_id'] != ''){
						$this->AcctCreditAccount_Import_model->insertAcctCreditsAccountImport($data);
					// }
				}
				unlink($inputFileName);
				// echo json_encode($data);
				// exit;
				$msg = "<div class='alert alert-success'>                
							Import Data Excel
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('credit-account-import/add');
			}
		}
		
		public function processAddAcctSavingsImportMutation(){
			$auth 			= $this->session->userdata('auth');
			$unique 		= $this->session->userdata('unique');
			$sessiondata	= $this->session->userdata('addacctsavingsimport-'.$unique['unique']);
			
			$preferencecompany 			= $this->AcctSavingsImportMutation_model->getPreferenceCompany();
			$acctsavingsimportmutation	= $this->AcctSavingsImportMutation_model->getAcctSavingsImportMutation();

			foreach($acctsavingsimportmutation as $key => $val){
				if($sessiondata['method_id'] == 1){
					if($sessiondata['mutation_id'] == 1){
						$acctsavingsaccount = $this->AcctSavingsImportMutation_model->getAcctSavingsAccount_Detail($val['savings_account_id']);

						$data = array(
							'savings_account_id'						=> $val['savings_account_id'],
							'mutation_id'								=> 2,
							'import_status'								=> 1,
							'member_id'									=> $val['member_id'],
							'savings_id'								=> $acctsavingsaccount['savings_id'],
							'branch_id'									=> $auth['branch_id'],
							'savings_cash_mutation_date'				=> date('Y-m-d'),
							'savings_cash_mutation_opening_balance'		=> $acctsavingsaccount['savings_account_last_balance'],
							'savings_cash_mutation_last_balance'		=> ($acctsavingsaccount['savings_account_last_balance']-$val['savings_import_mutation_amount']),
							'savings_cash_mutation_amount'				=> $val['savings_import_mutation_amount'],
							'savings_cash_mutation_amount_adm'			=> 0,
							'salary_payment_status'						=> 0,
							'savings_cash_mutation_remark'				=> $val['savings_import_mutation_remark'],
							'savings_cash_mutation_token'				=> md5(rand()),
							'operated_name'								=> $auth['username'],
							'created_id'								=> $auth['user_id'],
							'created_on'								=> date('Y-m-d H:i:s'),
						);	

						if($this->AcctSavingsImportMutation_model->insertAcctSavingsCashMutation($data)){
							$acctsavingscash_last 			= $this->AcctSavingsCashMutation_model->getAcctSavingsCashMutation_Last($data['created_id']);
							
							$transaction_module_code 		= "PTA";
							$transaction_module_id 			= $this->AcctSavingsImportMutation_model->getTransactionModuleID($transaction_module_code);
							
							$journal_voucher_period 		= date("Ym", strtotime($data['savings_cash_mutation_date']));

							$data_journal = array(
								'branch_id'						=> $auth['branch_id'],
								'journal_voucher_period' 		=> $journal_voucher_period,
								'journal_voucher_date'			=> date('Y-m-d'),
								'journal_voucher_title'			=> $val['savings_import_mutation_remark'].' '.$acctsavingscash_last['member_name'],
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingscash_last['member_name'],
								'journal_voucher_token'			=> $data['savings_cash_mutation_token'],
								'transaction_module_id'			=> $transaction_module_id,
								'transaction_module_code'		=> $transaction_module_code,
								'transaction_journal_id' 		=> $acctsavingscash_last['savings_cash_mutation_id'],
								'transaction_journal_no' 		=> $acctsavingscash_last['savings_account_no'],
								'created_id' 					=> $data['created_id'],
								'created_on' 					=> $data['created_on'],
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucher($data_journal);

							$journal_voucher_id 				= $this->AcctSavingsImportMutation_model->getJournalVoucherID($data['created_id']);
							$account_id 						= $this->AcctSavingsImportMutation_model->getAccountID($data['savings_id']);
							$account_id_default_status 			= $this->AcctSavingsImportMutation_model->getAccountIDDefaultStatus($account_id);

							$data_debit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucherItem($data_debit);

							$account_id_default_status 			= $this->AcctSavingsImportMutation_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

							$data_credit = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_cash_id'],
								'created_id' 					=> $auth['user_id']
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucherItem($data_credit);

							if($data['savings_cash_mutation_amount_adm'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_cash_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'PT'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);

								$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

								$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_mutation_adm_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> 'PT'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}
					}else{
						$acctsavingsaccount = $this->AcctSavingsImportMutation_model->getAcctSavingsAccount_Detail($val['savings_account_id']);

						$data = array(
							'savings_account_id'						=> $val['savings_account_id'],
							'mutation_id'								=> 1,
							'import_status'								=> 1,
							'member_id'									=> $val['member_id'],
							'savings_id'								=> $acctsavingsaccount['savings_id'],
							'branch_id'									=> $auth['branch_id'],
							'savings_cash_mutation_date'				=> date('Y-m-d'),
							'savings_cash_mutation_opening_balance'		=> $acctsavingsaccount['savings_account_last_balance'],
							'savings_cash_mutation_last_balance'		=> ($acctsavingsaccount['savings_account_last_balance']+$val['savings_import_mutation_amount']),
							'savings_cash_mutation_amount'				=> $val['savings_import_mutation_amount'],
							'savings_cash_mutation_amount_adm'			=> 0,
							'salary_payment_status'						=> 0,
							'savings_cash_mutation_remark'				=> $val['savings_import_mutation_remark'],
							'savings_cash_mutation_token'				=> md5(rand()),
							'operated_name'								=> $auth['username'],
							'created_id'								=> $auth['user_id'],
							'created_on'								=> date('Y-m-d H:i:s'),
						);

						if($this->AcctSavingsImportMutation_model->insertAcctSavingsCashMutation($data)){
							$acctsavingscash_last 			= $this->AcctSavingsCashMutation_model->getAcctSavingsCashMutation_Last($data['created_id']);
							
							$transaction_module_code 		= "PTA";
							$transaction_module_id 			= $this->AcctSavingsImportMutation_model->getTransactionModuleID($transaction_module_code);
							
							$journal_voucher_period 		= date("Ym", strtotime($data['savings_cash_mutation_date']));

							$data_journal = array(
								'branch_id'						=> $auth['branch_id'],
								'journal_voucher_period' 		=> $journal_voucher_period,
								'journal_voucher_date'			=> date('Y-m-d'),
								'journal_voucher_title'			=> $val['savings_import_mutation_remark'].' '.$acctsavingscash_last['member_name'],
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingscash_last['member_name'],
								'journal_voucher_token'			=> $data['savings_cash_mutation_token'],
								'transaction_module_id'			=> $transaction_module_id,
								'transaction_module_code'		=> $transaction_module_code,
								'transaction_journal_id' 		=> $acctsavingscash_last['savings_cash_mutation_id'],
								'transaction_journal_no' 		=> $acctsavingscash_last['savings_account_no'],
								'created_id' 					=> $data['created_id'],
								'created_on' 					=> $data['created_on'],
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucher($data_journal);

							$journal_voucher_id 				= $this->AcctSavingsImportMutation_model->getJournalVoucherID($data['created_id']);

							$account_id_default_status 			= $this->AcctSavingsImportMutation_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

							$data_debit = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_cash_id'],
								'created_id' 					=> $auth['user_id']
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucherItem($data_debit);

							$account_id 						= $this->AcctSavingsImportMutation_model->getAccountID($data['savings_id']);
							$account_id_default_status 			= $this->AcctSavingsImportMutation_model->getAccountIDDefaultStatus($account_id);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucherItem($data_credit);

							if($data['savings_cash_mutation_amount_adm'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_cash_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'PT'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);

								$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

								$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_mutation_adm_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> 'PT'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}
					}
				}else{
					if($sessiondata['mutation_id'] == 1){
						$acctsavingsaccount = $this->AcctSavingsImportMutation_model->getAcctSavingsAccount_Detail($val['savings_account_id']);

						$data = array(
							'savings_account_id'						=> $val['savings_account_id'],
							'mutation_id'								=> 2,
							'import_status'								=> 1,
							'member_id'									=> $val['member_id'],
							'savings_id'								=> $acctsavingsaccount['savings_id'],
							'branch_id'									=> $auth['branch_id'],
							'bank_account_id'							=> $sessiondata['bank_account_id'],
							'savings_bank_mutation_date'				=> date('Y-m-d'),
							'savings_bank_mutation_opening_balance'		=> $acctsavingsaccount['savings_account_last_balance'],
							'savings_bank_mutation_last_balance'		=> ($acctsavingsaccount['savings_account_last_balance']-$val['savings_import_mutation_amount']),
							'savings_bank_mutation_amount'				=> $val['savings_import_mutation_amount'],
							'savings_bank_mutation_token'				=> md5(rand()),
							'savings_bank_mutation_remark'				=> $val['savings_import_mutation_remark'],
							'savings_bank_mutation_amount_adm'			=> 0,
							'salary_payment_status'						=> 0,
							'operated_name'								=> $auth['username'],
							'created_id'								=> $auth['user_id'],
							'created_on'								=> date('Y-m-d H:i:s'),
						);	

						if($this->AcctSavingsImportMutation_model->insertAcctSavingsBankMutation($data)){
							$acctsavingsbank_last 			= $this->AcctSavingsBankMutation_model->getAcctSavingsBankMutation_Last($data['created_id']);
							
							$transaction_module_code 		= "PTA";
							$transaction_module_id 			= $this->AcctSavingsImportMutation_model->getTransactionModuleID($transaction_module_code);
							
							$journal_voucher_period 		= date("Ym", strtotime($data['savings_bank_mutation_date']));

							$data_journal = array(
								'branch_id'						=> $auth['branch_id'],
								'journal_voucher_period' 		=> $journal_voucher_period,
								'journal_voucher_date'			=> date('Y-m-d'),
								'journal_voucher_title'			=> $val['savings_import_mutation_remark'].' '.$acctsavingsbank_last['member_name'],
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingsbank_last['member_name'],
								'journal_voucher_token'			=> $data['savings_bank_mutation_token'],
								'transaction_module_id'			=> $transaction_module_id,
								'transaction_module_code'		=> $transaction_module_code,
								'transaction_journal_id' 		=> $acctsavingsbank_last['savings_bank_mutation_id'],
								'transaction_journal_no' 		=> $acctsavingsbank_last['savings_account_no'],
								'created_id' 					=> $data['created_id'],
								'created_on' 					=> $data['created_on'],
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucher($data_journal);

							$journal_voucher_id 				= $this->AcctSavingsImportMutation_model->getJournalVoucherID($data['created_id']);
							$account_id 						= $this->AcctSavingsImportMutation_model->getAccountID($data['savings_id']);
							$account_id_default_status 			= $this->AcctSavingsImportMutation_model->getAccountIDDefaultStatus($account_id);

							$data_debit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingsbank_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
								'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['savings_bank_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucherItem($data_debit);

							$account_id							= $this->AcctSavingsImportMutation_model->getBankAccountId($data['bank_account_id']);
							$account_id_default_status 			= $this->AcctSavingsImportMutation_model->getAccountIDDefaultStatus($account_id);

							$data_credit = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingsbank_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
								'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['savings_bank_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucherItem($data_credit);

							if($data['savings_bank_mutation_amount_adm'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_bank_mutation_amount_adm'],
									'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount_adm'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'PT'.$data['savings_bank_mutation_token'].$data['savings_bank_mutation_amount_adm'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);

								$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

								$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_mutation_adm_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_bank_mutation_amount_adm'],
									'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount_adm'],
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> 'PT'.$data['savings_bank_mutation_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}
					}else{
						$acctsavingsaccount = $this->AcctSavingsImportMutation_model->getAcctSavingsAccount_Detail($val['savings_account_id']);

						$data = array(
							'savings_account_id'						=> $val['savings_account_id'],
							'mutation_id'								=> 1,
							'import_status'								=> 1,
							'member_id'									=> $val['member_id'],
							'savings_id'								=> $acctsavingsaccount['savings_id'],
							'branch_id'									=> $auth['branch_id'],
							'bank_account_id'							=> $sessiondata['bank_account_id'],
							'savings_bank_mutation_date'				=> date('Y-m-d'),
							'savings_bank_mutation_opening_balance'		=> $acctsavingsaccount['savings_account_last_balance'],
							'savings_bank_mutation_last_balance'		=> ($acctsavingsaccount['savings_account_last_balance']+$val['savings_import_mutation_amount']),
							'savings_bank_mutation_amount'				=> $val['savings_import_mutation_amount'],
							'savings_bank_mutation_amount_adm'			=> 0,
							'salary_payment_status'						=> 0,
							'savings_bank_mutation_remark'				=> $val['savings_import_mutation_remark'],
							'savings_bank_mutation_token'				=> md5(rand()),
							'operated_name'								=> $auth['username'],
							'created_id'								=> $auth['user_id'],
							'created_on'								=> date('Y-m-d H:i:s'),
						);

						if($this->AcctSavingsImportMutation_model->insertAcctSavingsBankMutation($data)){
							$acctsavingsbank_last 			= $this->AcctSavingsBankMutation_model->getAcctSavingsBankMutation_Last($data['created_id']);
							
							$transaction_module_code 		= "PTA";
							$transaction_module_id 			= $this->AcctSavingsImportMutation_model->getTransactionModuleID($transaction_module_code);
							
							$journal_voucher_period 		= date("Ym", strtotime($data['savings_bank_mutation_date']));

							$data_journal = array(
								'branch_id'						=> $auth['branch_id'],
								'journal_voucher_period' 		=> $journal_voucher_period,
								'journal_voucher_date'			=> date('Y-m-d'),
								'journal_voucher_title'			=> $val['savings_import_mutation_remark'].' '.$acctsavingsbank_last['member_name'],
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingsbank_last['member_name'],
								'journal_voucher_token'			=> $data['savings_bank_mutation_token'],
								'transaction_module_id'			=> $transaction_module_id,
								'transaction_module_code'		=> $transaction_module_code,
								'transaction_journal_id' 		=> $acctsavingsbank_last['savings_bank_mutation_id'],
								'transaction_journal_no' 		=> $acctsavingsbank_last['savings_account_no'],
								'created_id' 					=> $data['created_id'],
								'created_on' 					=> $data['created_on'],
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucher($data_journal);

							$journal_voucher_id 				= $this->AcctSavingsImportMutation_model->getJournalVoucherID($data['created_id']);

							$account_id							= $this->AcctSavingsImportMutation_model->getBankAccountId($data['bank_account_id']);
							$account_id_default_status 			= $this->AcctSavingsImportMutation_model->getAccountIDDefaultStatus($account_id);

							$data_debit = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingsbank_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
								'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['savings_bank_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucherItem($data_debit);
							
							$account_id 						= $this->AcctSavingsImportMutation_model->getAccountID($data['savings_id']);
							$account_id_default_status 			= $this->AcctSavingsImportMutation_model->getAccountIDDefaultStatus($account_id);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> $val['savings_import_mutation_remark'].' '.$acctsavingsbank_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
								'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['savings_bank_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);
							$this->AcctSavingsImportMutation_model->insertAcctJournalVoucherItem($data_credit);

							if($data['savings_bank_mutation_amount_adm'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_bank_mutation_amount_adm'],
									'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount_adm'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'PT'.$data['savings_bank_mutation_token'].$data['savings_bank_mutation_amount_adm'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);

								$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

								$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_mutation_adm_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_bank_mutation_amount_adm'],
									'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount_adm'],
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> 'PT'.$data['savings_bank_mutation_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}
					}
				}
			}

			$auth = $this->session->userdata('auth');
			$msg = "<div class='alert alert-success alert-dismissable'>  
					<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
						Tambah Import Tabungan Sukses
					</div> ";
			$this->session->unset_userdata('addAcctSavingsImportMutation');
			$this->session->set_userdata('message',$msg);
			redirect('savings-import-mutation');
		}

		public function printNoteAcctSavingsImportMutation(){
			
			$auth 						= $this->session->userdata('auth');
			$savings_cash_mutation_id 	= $this->uri->segment(3);
			$acctsavingscashmutation	= $this->AcctSavingsCashMutation_model->getAcctSavingsCashMutation_Detail($savings_cash_mutation_id);
			$preferencecompany 			= $this->AcctSavingsCashMutation_model->getPreferenceCompany();

			$keterangan 	= 'POTONG TABUNGAN';
			$keterangan2 	= 'Telah diterima dari';
			$paraf 			= 'Penyetor';

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new tcpdf('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7); 

			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 12);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td rowspan=\"2\" width=\"20%\">".$img."</td>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">BUKTI ".$keterangan."</div></td>
			    </tr>
			    <tr>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
			    </tr>
			</table>";
			$pdf->writeHTML($tbl, true, false, false, false, '');
			

			$tbl1 = "
			".$keterangan2." :
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingscashmutation['member_name']."</div></td>
			    </tr>
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">Bagian</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingscashmutation['division_name']."</div></td>
				</tr>
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">Jenis Tabungan</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingscashmutation['savings_name']."</div></td>
				</tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">No. Rekening</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingscashmutation['savings_account_no']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingscashmutation['member_address']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".numtotxt($acctsavingscashmutation['savings_cash_mutation_amount'])."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$keterangan."</div></td>
			    </tr>
			     <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctsavingscashmutation['savings_cash_mutation_amount'], 2)."</div></td>
			    </tr>
			     <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Biaya Administrasi</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctsavingscashmutation['savings_cash_mutation_amount_adm'], 2)."</div></td>
			    </tr>				
			</table>";

			$tbl2 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			    	<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctSavingsCashMutation_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
			    </tr>
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$paraf."</div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
			    </tr>				
			</table>";

			$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');


			ob_clean();

			$js ='';
			$filename = 'Kwitansi_'.$keterangan.'_'.$acctsavingscashmutation['member_name'].'.pdf';
			$js .= 'print(true);';
			$pdf->IncludeJS($js);
			$pdf->Output($filename, 'I');
		}

		public function function_elements_add(){
			$unique 		 = $this->session->userdata('unique');
			$name 			 = $this->input->post('name',true);
			$value 			 = $this->input->post('value',true);
			$sessions		 = $this->session->userdata('addacctsavingsimport-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addacctsavingsimport-'.$unique['unique'],$sessions);
		}
	}
?>
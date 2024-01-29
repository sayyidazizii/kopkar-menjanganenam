<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctSavingsSalaryMutation extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctSavingsSalaryMutation_model');
			$this->load->model('AcctSavingsAccount_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$auth 	= $this->session->userdata('auth');
			$unique = $this->session->userdata('unique');
			$this->session->unset_userdata('acctsavingssalarymutationtoken-'.$unique['unique']);

			$data['main_view']['content']			= 'AcctSavingsSalaryMutation/ListAcctSavingsSalaryMutation_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				"start_date" 				=> tgltodb($this->input->post('start_date',true)),
				"end_date" 					=> tgltodb($this->input->post('end_date',true)),
				
			);

			$this->session->set_userdata('filter-acctsavingsmutation',$data);
			redirect('savings-salary-mutation');
		}

		public function reset_search(){
			$this->session->unset_userdata('filter-acctsavingsmutation');
			redirect('savings-salary-mutation');
		}

		public function getAcctSavingsSalaryMutation(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctsavingsmutation');
			if(!is_array($sesi)){
				$sesi['start_date']				= date('Y-m-d');
				$sesi['end_date']				= date('Y-m-d');
			}

			$savingscashstatus = $this->configuration->SavingsCashMutationStatus();

			$list = $this->AcctSavingsSalaryMutation_model->get_datatables($sesi['start_date'], $sesi['end_date'], $auth['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $savingsaccount) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $savingsaccount->member_name;
	            $row[] = $savingsaccount->savings_name;
	            $row[] = $savingsaccount->savings_account_no;
	            $row[] = tgltoview($savingsaccount->savings_cash_mutation_date);
	            $row[] = $savingsaccount->mutation_name;
	            $row[] = number_format($savingsaccount->savings_cash_mutation_amount, 2);
	            $row[] = $savingscashstatus[$savingsaccount->savings_cash_mutation_status];
	            if($savingsaccount->validation_status == 0){
	            	$row[] = '<a href="'.base_url().'savings-salary-mutation/print-note/'.$savingsaccount->savings_cash_mutation_id.'" class="btn btn-info btn-xs" role="button"><span class="glyphicon glyphicon-print"></span> Kwitansi</a> 

	            		<a href="'.base_url().'savings-salary-mutation/validation/'.$savingsaccount->savings_cash_mutation_id.'" class="btn btn-success btn-xs" role="button"><span class="glyphicon glyphicon-check"></span> Validasi</a>';
	            } else {
	            	 $row[] = '<a href="'.base_url().'savings-salary-mutation/print-note/'.$savingsaccount->savings_cash_mutation_id.'" class="btn btn-info btn-xs" role="button"><span class="glyphicon glyphicon-print"></span> Kwitansi</a>';
	            }
	            $data[] = $row;
	        }

	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctSavingsSalaryMutation_model->count_all($sesi['start_date'], $sesi['end_date'], $auth['branch_id']),
	                        "recordsFiltered" => $this->AcctSavingsSalaryMutation_model->count_filtered($sesi['start_date'], $sesi['end_date'], $auth['branch_id']),
	                        "data" => $data,
	                );
	        echo json_encode($output);
		}

		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctsavingscashmutation-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addacctsavingscashmutation-'.$unique['unique'],$sessions);
		}

		public function reset_data(){
			$unique 	= $this->session->userdata('unique');
			$sessions	= $this->session->unset_userdata('addacctsavingscashmutation-'.$unique['unique']);
			redirect('savings-salary-mutation/add');
		}

		public function getListAcctSavingsAccount(){
			$auth = $this->session->userdata('auth');
			$list = $this->AcctSavingsAccount_model->get_datatables($auth['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $savingsaccount) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $savingsaccount->savings_account_no;
	            $row[] = $savingsaccount->member_name;
	            $row[] = $savingsaccount->member_address;
	            $row[] = '<a href="'.base_url().'savings-salary-mutation/add/'.$savingsaccount->savings_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	            $data[] = $row;
	        }

	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctSavingsAccount_model->count_all($auth['branch_id']),
	                        "recordsFiltered" => $this->AcctSavingsAccount_model->count_filtered($auth['branch_id']),
	                        "data" => $data,
	                );
	        echo json_encode($output);
		}
		
		public function addAcctSavingsSalaryMutation(){
			$unique = $this->session->userdata('unique');
			$token 	= $this->session->userdata('acctsavingscashmutationtoken-'.$unique['unique']);

			if(empty($token)){
				$token = md5(date('Y-m-d H:i:s'));
				$this->session->set_userdata('acctsavingscashmutationtoken-'.$unique['unique'], $token);
			}

			$data['main_view']['acctsavingsaccount']		= $this->AcctSavingsSalaryMutation_model->getAcctSavingsAccountSalaryMutation();	
			$data['main_view']['acctmutation']				= create_double($this->AcctSavingsSalaryMutation_model->getAcctMutation(),'mutation_id', 'mutation_name');
			$data['main_view']['content']					= 'AcctSavingsSalaryMutation/FormAddAcctSavingsSalaryMutation_view';
			$this->load->view('MainPage_view',$data);
		}

		public function getMutationFunction(){
			$mutation_id 	= $this->input->post('mutation_id');

			$mutation_function 			= $this->AcctSavingsSalaryMutation_model->getMutationFunction($mutation_id);
			echo json_encode($mutation_function);		
		}
		
		public function processAddAcctSavingsSalaryMutation(){
			$auth 				= $this->session->userdata('auth');
			$token				= md5(rand());
			$acctsavingsaccount = $this->AcctSavingsSalaryMutation_model->getAcctSavingsAccountSalaryMutation();

			foreach($acctsavingsaccount as $key => $val){
				$data = array(
					'savings_account_id'						=> $val['savings_account_id'],
					'mutation_id'								=> $this->input->post('mutation_id', true),
					'member_id'									=> $val['member_id'],
					'savings_id'								=> $val['savings_id'],
					'branch_id'									=> $auth['branch_id'],
					'savings_cash_mutation_date'				=> date('Y-m-d'),
					'savings_cash_mutation_opening_balance'		=> $val['savings_account_last_balance'],
					'savings_cash_mutation_last_balance'		=> $val['savings_account_last_balance']+$val['savings_account_deposit_amount'],
					'savings_cash_mutation_amount'				=> $val['savings_account_deposit_amount'],
					'savings_cash_mutation_amount_adm'			=> 0,
					'savings_cash_mutation_remark'				=> '',
					'savings_cash_mutation_token'				=> $token.$val['savings_account_id'],
					'operated_name'								=> $auth['username'],
					'salary_payment_status'						=> 1,
					'created_id'								=> $auth['user_id'],
					'created_on'								=> date('Y-m-d H:i:s'),
				);

				$savings_cash_mutation_token 	= $this->AcctSavingsSalaryMutation_model->getSavingsCashMutationToken($data['savings_cash_mutation_token']);

				$transaction_module_code 		= "TTAB";
				$transaction_module_id 			= $this->AcctSavingsSalaryMutation_model->getTransactionModuleID($transaction_module_code);
				
				$journal_voucher_period 		= date("Ym", strtotime($data['savings_cash_mutation_date']));
				
				if($savings_cash_mutation_token->num_rows()==0){
					if($this->AcctSavingsSalaryMutation_model->insertAcctSavingsSalaryMutation($data)){
						$acctsavingscash_last 			= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutation_Last($data['created_id']);

						$data_journal = array(
							'branch_id'							=> $auth['branch_id'],
							'journal_voucher_period' 			=> $journal_voucher_period,
							'journal_voucher_date'				=> date('Y-m-d'),
							'journal_voucher_title'				=> 'MUTASI POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_description'		=> 'MUTASI POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_token'				=> $data['savings_cash_mutation_token'],
							'transaction_module_id'				=> $transaction_module_id,
							'transaction_module_code'			=> $transaction_module_code,
							'transaction_journal_id' 			=> $acctsavingscash_last['savings_cash_mutation_id'],
							'transaction_journal_no' 			=> $acctsavingscash_last['savings_account_no'],
							'created_id' 						=> $data['created_id'],
							'created_on' 						=> $data['created_on'],
						);
						
						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucher($data_journal);

						$journal_voucher_id 					= $this->AcctSavingsSalaryMutation_model->getJournalVoucherID($data['created_id']);

						$preferencecompany 						= $this->AcctSavingsSalaryMutation_model->getPreferenceCompany();

						if($data['mutation_id'] == $preferencecompany['cash_deposit_id'] || $data['mutation_id'] == 14){

							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);


							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_salary_payment_id'],
								'journal_voucher_description'	=> 'SETORAN POTONG GAJI '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debet);

							$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> 'SETORAN POTONG GAJI '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);
							if($data['savings_cash_mutation_amount_adm'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_cash_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'STR1'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
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
									'journal_voucher_item_token'	=> 'STR2'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);

								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						} else if($data['mutation_id'] == 2){
							$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

							$data_debit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> 'PENARIKAN POTONG GAJI '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debit);

							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

							$data_credit = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_salary_payment_id'],
								'journal_voucher_description'	=> 'PENARIKAN POTONG GAJI '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);

							if($data['savings_cash_mutation_amount_adm'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_cash_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
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
									'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);

								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}

						} else if($data['mutation_id'] == 3){
							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_salary_payment_id'],
								'journal_voucher_description'	=> 'KOREKSI KREDIT '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debet);

							$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> 'KOREKSI KREDIT '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);

							if($data['savings_cash_mutation_amount_adm'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_cash_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
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
									'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);

								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}

						} else if($data['mutation_id'] == 4){
							$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

							$data_debit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> 'KOREKSI DEBET '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debit);

							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

							$data_credit = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_salary_payment_id'],
								'journal_voucher_description'	=> 'KOREKSI DEBET '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);

							if($data['savings_cash_mutation_amount_adm'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_cash_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
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
									'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);

								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						} else {
							if($this->AcctSavingsSalaryMutation_model->closedAcctSavingsAccount($data['savings_account_id'])){
								$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

								$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

								$data_debit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_id,
									'journal_voucher_description'	=> 'TUTUP REKENING '.$acctsavingscash_last['member_name'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
									'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
									'created_id' 					=> $auth['user_id']
								);

								$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debit);

								$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

								$data_credit = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_salary_payment_id'],
									'journal_voucher_description'	=> 'TUTUP REKENING '.$acctsavingscash_last['member_name'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
									'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
									'created_id' 					=> $auth['user_id']
								);

								$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);

								if($data['savings_cash_mutation_amount_adm'] > 0){
									$data_debet = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $preferencecompany['account_cash_id'],
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
										'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 0,
										'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
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
										'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
										'created_id' 					=> $auth['user_id']
									);

									$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
								}
							}
							
						}

						$memberaccountdebt = $this->AcctSavingsSalaryMutation_model->getCoreMemberAccountReceivableAmount($data['member_id']);

						$member_account_receivable_amount = $memberaccountdebt['member_account_receivable_amount'] + $data['savings_cash_mutation_amount'];

						$member_account_savings_debt 	= $memberaccountdebt['member_account_savings_debt'] + $data['savings_cash_mutation_amount'];

						$data_member = array(
							"member_id" 						=> $data['member_id'],
							"member_account_receivable_amount" 	=> $member_account_receivable_amount,
							"member_account_savings_debt" 		=> $member_account_savings_debt,
						);

						$this->AcctSavingsSalaryMutation_model->updateCoreMember($data_member);
						
					}else{
					}
				} else {
					$acctsavingscash_last 			= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutation_Last($data['created_id']);
					
					$data_journal = array(
						'branch_id'							=> $auth['branch_id'],
						'journal_voucher_period' 			=> $journal_voucher_period,
						'journal_voucher_date'				=> date('Y-m-d'),
						'journal_voucher_title'				=> 'MUTASI POTONG GAJI '.$acctsavingscash_last['member_name'],
						'journal_voucher_description'		=> 'MUTASI POTONG GAJI '.$acctsavingscash_last['member_name'],
						'journal_voucher_token'				=> $data['savings_cash_mutation_token'],
						'transaction_module_id'				=> $transaction_module_id,
						'transaction_module_code'			=> $transaction_module_code,
						'transaction_journal_id' 			=> $acctsavingscash_last['savings_cash_mutation_id'],
						'transaction_journal_no' 			=> $acctsavingscash_last['savings_account_no'],
						'created_id' 						=> $data['created_id'],
						'created_on' 						=> $data['created_on'],
					);

					$journal_voucher_token 	= $this->AcctSavingsSalaryMutation_model->getJournalVoucherToken($data_journal['journal_voucher_token']);

					if($journal_voucher_token->num_rows()== 0){
						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucher($data_journal);
					}

					$journal_voucher_id 	= $this->AcctSavingsSalaryMutation_model->getJournalVoucherID($data['created_id']);

					$preferencecompany 		= $this->AcctSavingsSalaryMutation_model->getPreferenceCompany();

					if($data['mutation_id'] == $preferencecompany['cash_deposit_id']){
						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

						$data_debet = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_salary_payment_id'],
							'journal_voucher_description'	=> 'SETORAN POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
							'created_id' 					=> $auth['user_id']
						);

						$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debet);
						}

						$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

						$data_credit = array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_id,
							'journal_voucher_description'	=> 'SETORAN POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
							'created_id' 					=> $auth['user_id']
						);

						$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);
						}

						if($data['savings_cash_mutation_amount_adm'] > 0){

							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> 'STR1'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
								'created_id' 					=> $auth['user_id']
							);

							$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

							if($journal_voucher_item_token->num_rows()==0){
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);
							}

							$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

							$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_mutation_adm_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> 'STR2'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
								'created_id' 					=> $auth['user_id']
							);

							$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

							if($journal_voucher_item_token->num_rows()==0){
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}

					} else {	
						$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

						$data_debit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_id,
							'journal_voucher_description'	=> 'PENARIKAN POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
							'created_id' 					=> $auth['user_id']
						);

						$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_debit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debit);
						}

						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_salary_payment_id'],
							'journal_voucher_description'	=> 'PENARIKAN POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
							'created_id' 					=> $auth['user_id']
						);

						$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);
						}

						if($data['savings_cash_mutation_amount_adm'] > 0){

							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
								'created_id' 					=> $auth['user_id']
							);

							$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

							if($journal_voucher_item_token->num_rows()==0){
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);
							}

							$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

							$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_mutation_adm_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
								'created_id' 					=> $auth['user_id']
							);

							$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

							if($journal_voucher_item_token->num_rows()==0){
								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}
					}

					
					$memberaccountdebt = $this->AcctSavingsSalaryMutation_model->getCoreMemberAccountReceivableAmount($data['member_id']);

					$member_account_receivable_amount = $memberaccountdebt['member_account_receivable_amount'] + $data['savings_cash_mutation_amount'];

					$member_account_savings_debt 	= $memberaccountdebt['member_account_savings_debt'] + $data['savings_cash_mutation_amount'];

					$data_member = array(
						"member_id" 						=> $data['member_id'],
						"member_account_receivable_amount" 	=> $member_account_receivable_amount,
						"member_account_savings_debt" 		=> $member_account_savings_debt,
					);

					$this->AcctSavingsSalaryMutation_model->updateCoreMember($data_member);

				}
			}
					
			$auth = $this->session->userdata('auth');
			$msg = "<div class='alert alert-success alert-dismissable'>  
					<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
						Tambah Data Mutasi Simpanan Sukses
					</div> ";
			$sesi = $this->session->userdata('unique');
			$this->session->unset_userdata('addacctsavingscashmutation-'.$sesi['unique']);
			$this->session->unset_userdata('acctsavingscashmutationtoken-'.$sesi['unique']);
			$this->session->set_userdata('message',$msg);
			// redirect('savings-salary-mutation');
			$this->printNoteAcctSavingsSalaryMutationProcess($token);
		}

		public function printNoteAcctSavingsSalaryMutation(){
			$auth = $this->session->userdata('auth');
			$savings_cash_mutation_id 	= $this->uri->segment(3);
			$acctsavingscashmutation	= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutation_Detail($savings_cash_mutation_id);
			$preferencecompany 			= $this->AcctSavingsSalaryMutation_model->getPreferenceCompany();

			if($acctsavingscashmutation['mutation_id'] == $preferencecompany['cash_deposit_id']){
				$keterangan 	= 'SETORAN POTONG GAJI';
				$keterangan2 	= 'Telah diterima dari';
				$paraf 			= 'Penyetor';
			} else if($acctsavingscashmutation['mutation_id'] == $preferencecompany['cash_withdrawal_id']){
				$keterangan 	= 'PENARIKAN POTONG GAJI';
				$keterangan2 	= 'Telah dibayarkan kepada';
				$paraf 			= 'Penerima';
			} else if($acctsavingscashmutation['mutation_id'] == 3){
				$keterangan 	= 'KOREKSI KREDIT';
				$keterangan2 	= 'Telah diterima dari';
				$paraf 			= 'Penyetor';
			} else if($acctsavingscashmutation['mutation_id'] == 4){
				$keterangan 	= 'KOREKSI DEBET';
				$keterangan2 	= 'Telah dibayarkan kepada';
				$paraf 			= 'Penerima';
			}


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

			// ---------------------------------------------------------

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
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN POTONG GAJI</div></td>
			    </tr>
			    <tr>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			

			$tbl1 = "
			Telah diterima dari :
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
			        <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".numtotxt($acctsavingscashmutation['savings_cash_mutation_amount'])."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: SETORAN POTONG GAJI</div></td>
			    </tr>
			     <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctsavingscashmutation['savings_cash_mutation_amount'], 2)."</div></td>
			    </tr>			
			</table>";

			$tbl2 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			    	<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctSavingsSalaryMutation_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
			    </tr>
				<br>
				<br>
				<br>
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$paraf."</div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$preferencecompany['signature_name']."</div></td>
			    </tr>				
			</table>";

			$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');


			ob_clean();

			$js ='';
			// -----------------------------------------------------------------------------
			
			$filename = 'Kwitansi_'.$keterangan.'_'.$acctsavingscashmutation['member_name'].'.pdf';

			$js .= 'print(true);';

			$pdf->IncludeJS($js);
			
			$pdf->Output($filename, 'I');

			//============================================================+
			// END OF FILE
			//============================================================+
		}

		public function printNoteAcctSavingsSalaryMutationProcessOld($token){
			$auth 						= $this->session->userdata('auth');
			$acctsavingscashmutation	= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutationByToken($token);
			$preferencecompany 			= $this->AcctSavingsSalaryMutation_model->getPreferenceCompany();

			$keterangan 				= 'SETORAN POTONG GAJI';
			$keterangan2 				= 'Telah diterima dari';
			$paraf 						= 'Penyetor';

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

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);

			$pdf->AddPage();

			$pdf->SetFont('helvetica', '', 12);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";
			$tbl = "";

			foreach($acctsavingscashmutation as $key => $val){
				$tbl .= "
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
					<tr>
						<td width=\"100%\"><div style=\"text-align: center; font-size:14px\">TANDA TERIMA SIMPANAN</div></td>
					</tr>
					<br>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left; font-size:14px\">Telah terima dari</div></td>
						<td width=\"75%\"><div style=\"text-align: left; font-size:14px\">: ".$val['member_name']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left; font-size:14px\">Bagian</div></td>
						<td width=\"75%\"><div style=\"text-align: left; font-size:14px\">: ".$val['division_name']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left; font-size:14px\">Uang Sejumlah</div></td>
						<td width=\"75%\"><div style=\"text-align: left; font-size:14px\">: ".nominal($val['savings_cash_mutation_amount'])."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left; font-size:14px\">Untuk Pembayaran</div></td>
						<td width=\"75%\"><div style=\"text-align: left; font-size:14px\">: ".$val['savings_name']."</div></td>
					</tr>
				</table>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
					<tr>
						<td width=\"50%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"20%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctSavingsSalaryMutation_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
					</tr>
					<br>
					<br>
					<br>
					<br>
					<tr>
						<td width=\"50%\"><div style=\"text-align: center;\">".$paraf."</div></td>
						<td width=\"20%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
					</tr>				
				</table>
				<br />
				";
			}

			$pdf->writeHTML($tbl, true, false, false, false, '');


			ob_clean();

			$js ='';
			// -----------------------------------------------------------------------------
			
			$filename = 'Kwitansi_'.$keterangan.'_'.$acctsavingscashmutation['member_name'].'.pdf';

			$js .= 'print(true);';

			$pdf->IncludeJS($js);
			
			$pdf->Output($filename, 'I');

			//============================================================+
			// END OF FILE
			//============================================================+
		}

		public function printNoteAcctSavingsSalaryMutationProcess($token){
			$auth 						= $this->session->userdata('auth');
			$acctsavingscashmutation	= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutationByToken($token);
			$preferencecompany 			= $this->AcctSavingsSalaryMutation_model->getPreferenceCompany();

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

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);

			$pdf->AddPage();

			$pdf->SetFont('helvetica', '', 12);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			
			$no = 1;
			foreach($acctsavingscashmutation as $key => $val){
				$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

				$tbl = "
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
					<tr>
						<td rowspan=\"2\" width=\"20%\">".$img."</td>
						<td width=\"40%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN POTONG GAJI</div></td>
					</tr>
					<tr>
						<td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
					</tr>
				</table>";

				$pdf->writeHTML($tbl, true, false, false, false, '');
				

				$tbl1 = "
				Telah diterima dari :
				<br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
						<td width=\"80%\"><div style=\"text-align: left;\">: ".$val['member_name']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Bagian</div></td>
						<td width=\"80%\"><div style=\"text-align: left;\">: ".$val['division_name']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Jenis Tabungan</div></td>
						<td width=\"80%\"><div style=\"text-align: left;\">: ".$val['savings_name']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">No. Rekening</div></td>
						<td width=\"80%\"><div style=\"text-align: left;\">: ".$val['savings_account_no']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
						<td width=\"80%\"><div style=\"text-align: left;\">: ".numtotxt($val['savings_cash_mutation_amount'])."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
						<td width=\"80%\"><div style=\"text-align: left;\">: SETORAN POTONG GAJI</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
						<td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($val['savings_cash_mutation_amount'], 2)."</div></td>
					</tr>			
				</table>";

				$tbl2 = "
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
					<tr>
						<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"20%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctSavingsSalaryMutation_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
					</tr>
					<br>
					<br>
					<br>
					<tr>
						<td width=\"30%\"><div style=\"text-align: center;\">".$paraf."</div></td>
						<td width=\"20%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"30%\"><div style=\"text-align: center;\">".$preferencecompany['signature_name']."</div></td>
					</tr>				
				</table>
				";

				if($no % 3 == 0){
					$tbl2 .= "
						<br pagebreak=\"true\"/>
					";
				}

				$no++;

				$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');
			}



			ob_clean();

			$js ='';
			// -----------------------------------------------------------------------------
			
			$filename = 'Kwitansi_'.$keterangan.'_'.$acctsavingscashmutation['member_name'].'.pdf';

			$js .= 'print(true);';

			$pdf->IncludeJS($js);
			
			$pdf->Output($filename, 'I');

			//============================================================+
			// END OF FILE
			//============================================================+
		}

		public function validationAcctSavingsSalaryMutation(){
			$auth = $this->session->userdata('auth');
			$savings_cash_mutation_id = $this->uri->segment(3);

			$data = array (
				'savings_cash_mutation_id'  	=> $savings_cash_mutation_id,
				'validation'					=> 1,
				'validation_id'					=> $auth['user_id'],
				'validation_on'					=> date('Y-m-d H:i:s'),
			);

			if($this->AcctSavingsSalaryMutation_model->validationAcctSavingsSalaryMutation($data)){
				$msg = "<div class='alert alert-success alert-dismissable'>  
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
							Validasi Setoran Tunai Sukses
						</div>";
				$this->session->set_userdata('message',$msg);
				redirect('savings-salary-mutation/print-validation/'.$savings_cash_mutation_id);
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'> 
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
							Validasi Setoran Tunai Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('savings-salary-mutation');
			}
		}

		public function printValidationAcctSavingsSalaryMutation(){
			$savings_cash_mutation_id 	= $this->uri->segment(3);
			$acctsavingscashmutation	= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutation_Detail($savings_cash_mutation_id);
			$preferencecompany			= $this->AcctSavingsSalaryMutation_model->getPreferenceCompany();


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

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);

			$pdf->AddPage();

			$pdf->SetFont('helveticaI', '', 7);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
			    <tr>
			    	<td rowspan=\"2\" width=\"10%\">" .$img."</td>
			    </tr>
			    <tr>
			    </tr>
			</table>
			<br/>
			<br/>
			<br/>
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"55%\"><div style=\"text-align: right; font-size:14px\">".$acctsavingscashmutation['savings_account_no']."</div></td>
			        <td width=\"45%\"><div style=\"text-align: right; font-size:14px\">".$acctsavingscashmutation['member_name']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"52%\"><div style=\"text-align: right; font-size:14px\">".$acctsavingscashmutation['validation_on']."</div></td>
			        <td width=\"18%\"><div style=\"text-align: right; font-size:14px\">".$this->AcctSavingsSalaryMutation_model->getUsername($acctsavingscashmutation['validation_id'])."</div></td>
			        <td width=\"30%\"><div style=\"text-align: right; font-size:14px\"> IDR &nbsp; ".number_format($acctsavingscashmutation['savings_cash_mutation_amount'], 2)."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			

			ob_clean();

			// -----------------------------------------------------------------------------
			
			$filename = 'Validasi.pdf';
			$pdf->Output($filename, 'I');

			//============================================================+
			// END OF FILE
			//============================================================+
		}
		
		public function voidAcctSavingsSalaryMutation(){
			$data['main_view']['acctsavingscashmutation']	= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutation_Detail($this->uri->segment(3));
			$data['main_view']['memberidentity']			= $this->configuration->MemberIdentity();
			$data['main_view']['content']					= 'AcctSavingsSalaryMutation/FormVoidAcctSavingsSalaryMutation_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processVoidAcctSavingsSalaryMutation(){
			$auth	= $this->session->userdata('auth');

			$newdata = array (
				"savings_cash_mutation_id"	=> $this->input->post('savings_cash_mutation_id',true),
				"voided_on"					=> date('Y-m-d H:i:s'),
				'data_state'				=> 2,
				"voided_remark" 			=> $this->input->post('voided_remark',true),
				"voided_id"					=> $auth['user_id']
			);
			
			$this->form_validation->set_rules('voided_remark', 'Keterangan', 'required');

			if($this->form_validation->run()==true){
				if($this->AcctSavingsSalaryMutation_model->voidAcctSavingsSalaryMutation($newdata)){
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Pembatalan Mutasi Simpanan Sukses
							</div>";
					$this->session->set_userdata('message',$msg);
					redirect('savings-salary-mutation');
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Pembatalan Mutasi Simpanan Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('savings-salary-mutation');
				}
			}else{
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('savings-salary-mutation');
			}
		}

		public function function_state_add(){
			$unique 	= $this->session->userdata('unique');
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctsavingscashmutation-'.$unique['unique']);
			$sessions['active_tab'] = $value;
			$this->session->set_userdata('addacctsavingscashmutation-'.$unique['unique'],$sessions);
		}
	}
?>
<?php
	// defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctDepositoAccount extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctDepositoAccount_model');
			$this->load->model('CoreMember_model');
			$this->load->model('Library_model');
			$this->load->model('AcctSavingsAccount_model');
			$this->load->model('AcctSavingsTransferMutation_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$unique = $this->session->userdata('unique');
			
			$export_master_data_id 					= $this->Library_model->getIDMenu('deposito-account/get-master-data-list');
			$export_master_data_id_mapping 			= $this->Library_model->getIDMenuOnSystemMapping($export_master_data_id);

			if($export_master_data_id_mapping == 1){
				$export_id = 1;
			}else{
				$export_id = 0;
			}

			$this->session->unset_userdata('acctdepositoaccounttoken-'.$unique['unique']);
			$this->session->unset_userdata('member_id');
	
			$data['main_view']['acctdeposito']		= create_double($this->AcctDepositoAccount_model->getAcctDeposito(),'deposito_id', 'deposito_name');
			$data['main_view']['corebranch']		= create_double($this->AcctDepositoAccount_model->getCoreBranch(),'branch_id','branch_name');	
			$data['main_view']['export_id']			= $export_id;	
			$data['main_view']['content']			= 'AcctDepositoAccount/ListAcctDepositoAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				// "start_date" 	=> tgltodb($this->input->post('start_date',true)),
				// "end_date" 		=> tgltodb($this->input->post('end_date',true)),
				"deposito_id"	=> $this->input->post('deposito_id',true),
				"branch_id"		=> $this->input->post('branch_id',true),
			);

			$this->session->set_userdata('filter-acctdepositoaccount',$data);
			redirect('deposito-account');
		}

		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctdepositoaccount-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addacctdepositoaccount-'.$unique['unique'],$sessions);
		}

		public function reset_data(){
			$unique 	= $this->session->userdata('unique');
			$sessions	= $this->session->unset_userdata('addacctdepositoaccount-'.$unique['unique']);
			$this->session->unset_userdata('member_id');
			redirect('deposito-account/add');
		}

		public function reset_close(){
			$member_id = $this->uri->segment(3);
			$unique 	= $this->session->userdata('unique');
			$this->session->unset_userdata('addacctdepositoaccount-'.$unique['unique']);
			redirect('deposito-account/add-closed/'.$member_id);
		}

		public function reset_search(){
			$this->session->unset_userdata('filter-acctdepositoaccount');
			redirect('deposito-account');
		}

		public function getListAcctDepositoAccount(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctdepositoaccount');
			if(!is_array($sesi)){
				// $sesi['start_date']		= date('Y-m-d');
				// $sesi['end_date']		= date('Y-m-d');
				$sesi['deposito_id']		='';
				if($auth['branch_status'] == 0){
					$sesi['branch_id']		= $auth['branch_id'];
				} else {
					$sesi['branch_id']		= '';
				}
			}
			
			$print_certificate_id 					= $this->Library_model->getIDMenu('deposito-account/print-certificate-deposito');
			$print_certificate_id_mapping 			= $this->Library_model->getIDMenuOnSystemMapping($print_certificate_id);
			

			$list = $this->AcctDepositoAccount_model->get_datatables_master($sesi['deposito_id'], $sesi['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $depositoaccount) {
				$deposito_accrual_last_balance = $this->AcctDepositoAccount_model->getAcctDepositoAccrualLastBalance($depositoaccount->deposito_account_id);
				$acctdepositoaccount 		   = $this->AcctDepositoAccount_model->getAcctDepositoAccountDetail($depositoaccount->deposito_account_id);
	
				$interest_total		 		   = $deposito_accrual_last_balance + $acctdepositoaccount['deposito_account_nisbah'];

				$button = '';

				$button_print_certificate 	= '<a href="'.base_url().'deposito-account/print-certificate-front/'.$depositoaccount->deposito_account_id.'" class="btn btn-xs yellow" role="button"><i class="fa fa-print"></i> Cetak Depan Sertifikat</a>
				<a href="'.base_url().'deposito-account/print-certificate-back/'.$depositoaccount->deposito_account_id.'" class="btn btn-xs green" role="button"><i class="fa fa-print"></i> Cetak Belakang Sertifikat</a>';

				if($print_certificate_id_mapping == 1){
					$button .= $button_print_certificate;
				}
				if($depositoaccount->deposito_account_extra_type == '1'){
					$type_extra = 'ARO';
				}else{
					$type_extra = 'Manual';
				}
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $depositoaccount->deposito_account_no;
	            $row[] = $depositoaccount->member_name;
	            $row[] = $depositoaccount->deposito_name;
	            $row[] = $type_extra;
	            $row[] = $depositoaccount->deposito_account_serial_no;
	            $row[] = tgltoview($depositoaccount->deposito_account_date);
	            $row[] = tgltoview($depositoaccount->deposito_account_due_date);
	            $row[] = number_format($depositoaccount->deposito_account_amount, 2);
	            $row[] = number_format($interest_total);
	            if($depositoaccount->validation == 0){
	            	$row[] = '<a href="'.base_url().'deposito-account/print-note/'.$depositoaccount->deposito_account_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Kwitansi</a>
				        <a href="'.base_url().'deposito-account/validation/'.$depositoaccount->deposito_account_id.'" class="btn btn-xs green-jungle" role="button"><i class="fa fa-check"></i> Validasi</a>';
			    } else {
			    	$row[] = '<a href="'.base_url().'deposito-account/print-note/'.$depositoaccount->deposito_account_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Kwitansi</a>'.$button;
			    }
	            $data[] = $row;
	        }



	        // print_r($list);exit;
	 
	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctDepositoAccount_model->count_all_master($sesi['deposito_id'], $sesi['branch_id']),
	                        "recordsFiltered" => $this->AcctDepositoAccount_model->count_filtered_master($sesi['deposito_id'], $sesi['branch_id']),
	                        "data" => $data,
	                );
	        //output to json format
	        echo json_encode($output);
		}

		public function getMasterDataAcctDepositoAccount(){
			$data['main_view']['acctdeposito']		= create_double($this->AcctDepositoAccount_model->getAcctDeposito(),'deposito_id', 'deposito_name');
			$data['main_view']['corebranch']		= create_double($this->AcctDepositoAccount_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']	= 'AcctDepositoAccount/ListMasterDataAcctDepositoAccount_view';
			$this->load->view('MainPage_view', $data);
		}

		public function filtermasterdata(){
			$data = array (
				"start_date" 	=> tgltodb($this->input->post('start_date',true)),
				"end_date" 		=> tgltodb($this->input->post('end_date',true)),
				"deposito_id"	=> $this->input->post('deposito_id',true),
				"branch_id"		=> $this->input->post('branch_id',true),
			);

			$this->session->set_userdata('filter-masterdataacctdepositoaccount',$data);
			redirect('deposito-account/get-master');
		}

		public function getMasterDataAcctDepositoAccountList(){
			$sesi	= 	$this->session->userdata('filter-masterdataacctdepositoaccount');
			$auth 	= $this->session->userdata('auth');
			if(!is_array($sesi)){
				// $sesi['start_date']		= date('Y-m-d');
				// $sesi['end_date']		= date('Y-m-d');
				$sesi['deposito_id']		= '';
				if($auth['branch_status'] == 0){
					$sesi['branch_id']		= $auth['branch_id'];
				} else {
					$sesi['branch_id']		= '';
				}
			}

			$list = $this->AcctDepositoAccount_model->get_datatables_master($sesi['deposito_id'], $sesi['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $depositoaccount) {
				if($depositoaccount->deposito_account_extra_type == '1'){
					$type_extra = 'ARO';
				}else{
					$type_extra = 'Manual';
				}
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $depositoaccount->deposito_account_no;
	            $row[] = $depositoaccount->member_name;
	            $row[] = $depositoaccount->deposito_name;
	            $row[] = $type_extra;
	            $row[] = $depositoaccount->deposito_account_serial_no;
	            $row[] = tgltoview($depositoaccount->deposito_account_date);
	            $row[] = tgltoview($depositoaccount->deposito_account_due_date);
	            $row[] = number_format($depositoaccount->deposito_account_amount, 2);
	            $row[] = $depositoaccount->deposito_interest_rate;
	            $data[] = $row;
	        }
	 
	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctDepositoAccount_model->count_all_master($sesi['deposito_id'], $sesi['branch_id']),
	                        "recordsFiltered" => $this->AcctDepositoAccount_model->count_filtered_master($sesi['deposito_id'], $sesi['branch_id']),
	                        "data" => $data,
	                );
	        echo json_encode($output);
		}

		public function getListCoreMember(){
			$auth = $this->session->userdata('auth');
			$data_state = 0;
			$list = $this->CoreMember_model->get_datatables_status($data_state);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $customers) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $customers->member_no;
	            $row[] = $customers->member_name;
	            $row[] = $customers->member_address;
	            $row[] = '<a href="'.base_url().'deposito-account/add/'.$customers->member_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	            $data[] = $row;
	        }
	 
	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->CoreMember_model->count_all($data_state),
	                        "recordsFiltered" => $this->CoreMember_model->count_filtered($data_state),
	                        "data" => $data,
	                );
	        echo json_encode($output);
		}

		public function getListAcctSavingAccount(){
			$member_id 			= $this->uri->segment(3);
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
	            $row[] = '<a href="'.base_url().'deposito-account/add/'.$member_id.'/'.$savingsaccount->savings_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
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
		
		public function addAcctDepositoAccount(){			
			$member_id 			= $this->uri->segment(3);
			$savings_account_id = $this->uri->segment(4);

			$unique = $this->session->userdata('unique');
			$token 	= $this->session->userdata('acctdepositoaccounttoken-'.$unique['unique']);

			if(empty($token)){
				$token = md5(rand());
				$this->session->set_userdata('acctdepositoaccounttoken-'.$unique['unique'], $token);
			}
			$depositoextratype = array(
				array(
					"deposito_account_extra_type" => 0,
					"deposito_account_extra_type_name" => 'Manual',
				),
				array(
					"deposito_account_extra_type" => 1,
					"deposito_account_extra_type_name" => 'ARO',
				),
			);
			
			$data['main_view']['coremember']				= $this->AcctDepositoAccount_model->getCoreMember_Detail($member_id);
			$data['main_view']['acctdeposito']				= create_double($this->AcctDepositoAccount_model->getAcctDeposito(),'deposito_id', 'deposito_name');
			$data['main_view']['depositoextratype']			= create_double($depositoextratype,'deposito_account_extra_type', 'deposito_account_extra_type_name');
			$data['main_view']['coreoffice']				= create_double($this->AcctDepositoAccount_model->getCoreOffice(),'office_id', 'office_name');
			$data['main_view']['acctsavingsaccount']		= $this->AcctDepositoAccount_model->getAcctSavingsAccount_Detail($savings_account_id);	
			$data['main_view']['membergender']				= $this->configuration->MemberGender();
			$data['main_view']['memberidentity']			= $this->configuration->MemberIdentity();
			$data['main_view']['familyrelationship']		= $this->configuration->FamilyRelationship();
			$data['main_view']['content']					= 'AcctDepositoAccount/FormAddAcctDepositoAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function getDepositoAccountNo(){
			$date = date('Y-m-d');
			$auth = $this->session->userdata('auth');

			$deposito_id 	= $this->input->post('deposito_id');
			$branchcode 	= $this->AcctDepositoAccount_model->getBranchCode($auth['branch_id']);
			

			$depositocode 			= $this->AcctDepositoAccount_model->getDepositoCode($deposito_id);
			$depositoperiod 		= $this->AcctDepositoAccount_model->getDepositoPeriod($deposito_id);
			$depositonisbah 		= $this->AcctDepositoAccount_model->getDepositoNisbah($deposito_id);
			$lastdepositoaccountno 	= $this->AcctDepositoAccount_model->getLastAccountDepositoNo($auth['branch_id'], $deposito_id);

			if($lastdepositoaccountno->num_rows() <> 0){      
			   //jika kode ternyata sudah ada.      
			   $data = $lastdepositoaccountno->row_array();    
			   $kode = intval($data['last_deposito_account_no']) + 1;    
			 } else {      
			   //jika kode belum ada      
			   $kode = 1;    
			}
			
			$kodemax 		= str_pad($kode, 5, "0", STR_PAD_LEFT); // angka 4 menunjukkan jumlah digit angka 0
		  	//$new_deposito_account_no 		= $depositocode.$branchcode.$kodemax;    // hasilnya ODJ-9921-0001 dst.
		  	$new_deposito_account_no 		= $branchcode.$depositocode.$kodemax;    // hasilnya ODJ-9921-0001 dst.
		  	$new_deposito_account_serial 	= $depositoperiod.".".$kodemax;

		  	$deposito_due_date = date('d-m-Y', strtotime('+'.$depositoperiod.'month', strtotime($date)));
			
			$result = array ();
			$result = array (
					'deposito_period'				=> $depositoperiod,
					'deposito_account_no'			=> $new_deposito_account_no,
					'deposito_account_serial_no'	=> $new_deposito_account_serial,
					'deposito_account_due_date'		=> $deposito_due_date,
					'deposito_account_nisbah'		=> $depositonisbah,
			);
			

			echo json_encode($result);		
		}
		
		public function processAddAcctDepositoAccount(){
			$auth = $this->session->userdata('auth');

			$data = array(
				'member_id'								=> $this->input->post('member_id', true),
				'deposito_id'							=> $this->input->post('deposito_id', true),
				'office_id'								=> $this->input->post('office_id', true),
				'branch_id'								=> $auth['branch_id'],
				'savings_account_id'					=> $this->input->post('savings_account_id', true),
				'deposito_account_date'					=> date('Y-m-d'),
				'deposito_account_due_date'				=> tgltodb($this->input->post('deposito_account_due_date', true)),
				// 'deposito_account_no'					=> $this->input->post('deposito_account_no', true),
				'deposito_account_serial_no'			=> $this->input->post('deposito_account_serial_no', true),
				'deposito_account_amount'				=> $this->input->post('deposito_account_amount', true),
				// 'deposito_account_nisbah'				=> $this->input->post('deposito_account_nisbah', true),
				'deposito_account_period'				=> $this->input->post('deposito_period', true),
				'deposito_member_heir'					=> $this->input->post('deposito_member_heir', true),
				'deposito_member_heir_address'			=> $this->input->post('deposito_member_heir_address', true),
				'deposito_member_heir_relationship'		=> $this->input->post('deposito_member_heir_relationship', true),
				'deposito_account_extra_type'			=> $this->input->post('deposito_account_extra_type', true),
				'deposito_account_token'				=> $this->input->post('deposito_account_token', true),
				'created_id'							=> $auth['user_id'],
				'created_on'							=> date('Y-m-d H:i:s'),
			);

			// print_r($data_debet);exit;
			
			$this->form_validation->set_rules('member_id', 'Anggota', 'required');
			$this->form_validation->set_rules('deposito_id', 'Jenis Simpanan berjangka', 'required');
			$this->form_validation->set_rules('savings_account_id', 'No. Simpanan', 'required');
			$this->form_validation->set_rules('deposito_account_amount', 'Nominal', 'required');
			$this->form_validation->set_rules('office_id', 'Business Officer (BO)', 'required');

			$transaction_module_code 	= "DEP";
			$transaction_module_id 		= $this->AcctDepositoAccount_model->getTransactionModuleID($transaction_module_code);

			$deposito_account_token 	= $this->AcctDepositoAccount_model->getDepositoAccountToken($data['deposito_account_token']);
			
			if($this->form_validation->run()==true){
				if($deposito_account_token->num_rows() == 0){
					if($this->AcctDepositoAccount_model->insertAcctDepositoAccount($data)){
						$deposito_account_id = $this->AcctDepositoAccount_model->getDepositoAccountID($data['created_on']);

						$date 	= date('d', strtotime($data['deposito_account_date']));
						$month 	= date('m', strtotime($data['deposito_account_date']));
						$year 	= date('Y', strtotime($data['deposito_account_date']));

						for ($i=1; $i<= $data['deposito_account_period']; $i++) { 
							$depositoprofitsharing = array ();

							$month = $month + 1;

							if($month == 13){
								$month = 01;
								$year = $year + 1;
							}

							$deposito_profit_sharing_due_date = $year.'-'.$month.'-'.$date;

							$depositoprofitsharing = array (
								'deposito_account_id'				=> $deposito_account_id,
								'branch_id'							=> $auth['branch_id'],
								'deposito_id'						=> $data['deposito_id'],
								'deposito_account_nisbah'			=> $this->input->post('deposito_account_nisbah', true),
								'member_id'							=> $data['member_id'],
								'deposito_profit_sharing_due_date'	=> $deposito_profit_sharing_due_date,
								'deposito_daily_average_balance'	=> $data['deposito_account_amount'],
								'deposito_account_last_balance'		=> $data['deposito_account_amount'],
								'savings_account_id'				=> $data['savings_account_id'],
							);

							$this->AcctDepositoAccount_model->insertAcctDepositoProfitSharing($depositoprofitsharing);

						}

						
						$acctdepositoaccount_last 	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Last($data['created_on']);

							
						$journal_voucher_period = date("Ym", strtotime($data['deposito_account_date']));
						
						$data_journal = array(
							'branch_id'						=> $auth['branch_id'],
							'journal_voucher_period' 		=> $journal_voucher_period,
							'journal_voucher_date'			=> date('Y-m-d'),
							'journal_voucher_title'			=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
							'journal_voucher_description'	=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
							'journal_voucher_token'			=> $data['deposito_account_token'],
							'transaction_module_id'			=> $transaction_module_id,
							'transaction_module_code'		=> $transaction_module_code,
							'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
							'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_serial_no'],
							'created_id' 					=> $data['created_id'],
							'created_on' 					=> $data['created_on'],
						);
						
						$this->AcctDepositoAccount_model->insertAcctJournalVoucher($data_journal);

						$journal_voucher_id = $this->AcctDepositoAccount_model->getJournalVoucherID($data['created_id']);

						$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();

						$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
								'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
								'journal_voucher_debit_amount'	=> ABS($data['deposito_account_amount']),
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['deposito_account_token'].$preferencecompany['account_cash_id'],
								'created_id' 					=> $auth['user_id'],
							);

							$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);

							$account_id = $this->AcctDepositoAccount_model->getAccountID($data['deposito_id']);

							$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
								'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
								'journal_voucher_credit_amount'	=> ABS($data['deposito_account_amount']),
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['deposito_account_token'].$account_id,
								'created_id' 					=> $auth['user_id'],
							);

							$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);


						$auth = $this->session->userdata('auth');
						// $this->fungsi->set_log($auth['username'],'1003','Application.machine.processAddmachine',$auth['username'],'Add New machine');
						$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Rekening Simpanan Berjangka Sukses
								</div> ";
						$sesi = $this->session->userdata('unique');
						$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
						$this->session->unset_userdata('acctdepositoaccounttoken-'.$sesi['unique']);
						$this->session->unset_userdata('member_id');
						$this->session->unset_userdata('savings');
						$this->session->set_userdata('message',$msg);
						redirect('deposito-account/print-note//'.$acctdepositoaccount_last['deposito_account_id']);
					}else{
						$this->session->set_userdata('addacctdepositoaccount',$data);
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Rekening Simpanan Berjangka Tidak Berhasil
								</div> ";
						$this->session->set_userdata('message',$msg);
						redirect('deposito-account');
					}
				} else {
					$acctdepositoaccount_last 	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Last($data['created_on']);

							
					$journal_voucher_period = date("Ym", strtotime($data['deposito_account_date']));
					
					$data_journal = array(
						'branch_id'						=> $auth['branch_id'],
						'journal_voucher_period' 		=> $journal_voucher_period,
						'journal_voucher_date'			=> date('Y-m-d'),
						'journal_voucher_title'			=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
						'journal_voucher_description'	=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
						'journal_voucher_token'			=> $data['deposito_account_token'],
						'transaction_module_id'			=> $transaction_module_id,
						'transaction_module_code'		=> $transaction_module_code,
						'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
						'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_serial_no'],
						'created_id' 					=> $data['created_id'],
						'created_on' 					=> $data['created_on'],
					);

					$journal_voucher_token = $this->AcctDepositoAccount_model->getJournalVoucherToken($data['deposito_account_token']);

					if($journal_voucher_token->num_rows() == 0){
						$this->AcctDepositoAccount_model->insertAcctJournalVoucher($data_journal);
					}
					
					$journal_voucher_id = $this->AcctDepositoAccount_model->getJournalVoucherID($data['created_id']);

					$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();

					$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

					$data_debet = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_cash_id'],
						'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
						'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
						'journal_voucher_debit_amount'	=> ABS($data['deposito_account_amount']),
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 0,
						'journal_voucher_item_token'	=> $data['deposito_account_token'].$preferencecompany['account_cash_id'],
						'created_id' 					=> $auth['user_id'],
					);

					$journal_voucher_item_token = $this->AcctDepositoAccount_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows() == 0){
						$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);
					}

					$account_id = $this->AcctDepositoAccount_model->getAccountID($data['deposito_id']);

					$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);

					$data_credit =array(
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $account_id,
						'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
						'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
						'journal_voucher_credit_amount'	=> ABS($data['deposito_account_amount']),
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'journal_voucher_item_token'	=> $data['deposito_account_token'].$account_id,
						'created_id' 					=> $auth['user_id'],
					);

					$journal_voucher_item_token = $this->AcctDepositoAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows() == 0){
						$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);
					}


					$auth = $this->session->userdata('auth');
					// $this->fungsi->set_log($auth['username'],'1003','Application.machine.processAddmachine',$auth['username'],'Add New machine');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Rekening Simpanan Berjangka Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
					$this->session->unset_userdata('acctdepositoaccounttoken-'.$sesi['unique']);
					$this->session->unset_userdata('member_id');
					$this->session->unset_userdata('savings');
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account/print-note/'.$acctdepositoaccount_last['deposito_account_id']);
				}
				
			}else{
				$this->session->set_userdata('addacctdepositoaccount',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('deposito-account');
			}
		}

		public function printNoteAcctDepositoAccount(){
			$auth = $this->session->userdata('auth');
			$deposito_account_id 	= $this->uri->segment(3);
			$preferencecompany 		= $this->AcctDepositoAccount_model->getPreferenceCompany();
			$acctdepositoaccount	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($deposito_account_id);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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
			    	<td rowspan=\"2\" width=\"20%\">" .$img."</td>
			        <td width=\"50%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN SIMPANAN BERJANGKA</div></td>
			    </tr>
			    <tr>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');

			$tbl1 = "
			Telah diterima uang dari :
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctdepositoaccount['member_name']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">No. Rekening</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctdepositoaccount['deposito_account_no']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctdepositoaccount['member_address']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".numtotxt($acctdepositoaccount['deposito_account_amount'])."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: SETORAN SIMPANAN BERJANGKA</div></td>
			    </tr>
			     <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctdepositoaccount['deposito_account_amount'], 2)."</div></td>
			    </tr>				
			</table>";

			$tbl2 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			    	<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctDepositoAccount_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
			    </tr>
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: center;\">Penyetor</div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
			    </tr>				
			</table>";

			$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');
			if (ob_get_length() > 0){
				ob_clean();	
			}
			// -----------------------------------------------------------------------------
			
			$filename = 'Kwitansi.pdf';
			$pdf->Output($filename, 'I');
		}

		public function validationAcctDepositoAccount(){
			$auth = $this->session->userdata('auth');
			$deposito_account_id = $this->uri->segment(3);

			$data = array (
				'deposito_account_id'  	=> $deposito_account_id,
				'validation'			=> 1,
				'validation_id'			=> $auth['user_id'],
				'validation_on'			=> date('Y-m-d H:i:s'),
			);

			if($this->AcctDepositoAccount_model->validationAcctDepositoAccount($data)){
				$msg = "<div class='alert alert-success alert-dismissable'>  
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
							Validasi Rekening Simpanan Berjangka Sukses
						</div>";
				$this->session->set_userdata('message',$msg);
				redirect('deposito-account/print-validation/'.$deposito_account_id);
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'> 
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
							Validasi Rekening Simpanan Berjangka Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('deposito-account');
			}
		}

		public function printValidationAcctDepositoAccount(){
			$deposito_account_id 	= $this->uri->segment(3);
			$acctdepositoaccount	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($deposito_account_id);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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

			$tbl = "
			<br><br><br><br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"55%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['deposito_account_no']."</div></td>
			        <td width=\"40%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['member_name']."</div></td>
			        <td width=\"5%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['office_id']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"52%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['validation_on']."</div></td>
			        <td width=\"18%\"><div style=\"text-align: right; font-size:14px\">".$this->AcctDepositoAccount_model->getUsername($acctdepositoaccount['validation_id'])."</div></td>
			        <td width=\"30%\"><div style=\"text-align: right; font-size:14px\"> IDR &nbsp; ".number_format($acctdepositoaccount['deposito_account_amount'], 2)."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			if (ob_get_length() > 0){
				ob_clean();
			}
			// -----------------------------------------------------------------------------
			
			$filename = 'Validasi.pdf';
			$pdf->Output($filename, 'I');
		}

		//-----------------------------------------------------------------------------------------------------------------------------//

		public function voidAcctDepositoAccount(){
			$data['main_view']['membergender']				= $this->configuration->MemberGender();
			$data['main_view']['memberidentity']			= $this->configuration->MemberIdentity();
			$data['main_view']['acctdepositoaccount']		= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($this->uri->segment(3));
			$data['main_view']['content']			= 'AcctDepositoAccount/FormVoidAcctDepositoAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processVoidAcctDepositoAccount(){
			$auth	= $this->session->userdata('auth');

			$newdata = array (
				"deposito_account_id"	=> $this->input->post('deposito_account_id',true),
				"voided_on"				=> date('Y-m-d H:i:s'),
				'data_state'			=> 2,
				"voided_remark" 		=> $this->input->post('voided_remark',true),
				"voided_id"				=> $auth['user_id']
			);
			
			$this->form_validation->set_rules('voided_remark', 'Keterangan', 'required');

			if($this->form_validation->run()==true){
				if($this->AcctDepositoAccount_model->voidAcctDepositoAccount($newdata)){
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Pembatalan Rekening Berjangka Sukses
							</div>";
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account');
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Pembatalan Rekening Berjangka Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account');
				}
			}else{
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('deposito-account');
			}
		}

		//-----------------------------------------------------------------------------------------------------------------------------//

		public function printCertificateDeposito(){
			$unique = $this->session->userdata('unique');

			$this->session->unset_userdata('acctdepositoaccounttoken-'.$unique['unique']);
			$this->session->unset_userdata('member_id');
	
			$data['main_view']['content']			= 'AcctDepositoAccount/ListAcctDepositoPrintCertificate_view';
			$this->load->view('MainPage_view',$data);
		}

		public function getListPrintAcctDepositoAccount(){
			$auth 	= $this->session->userdata('auth');

			$list = $this->AcctDepositoAccount_model->get_datatables();
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $depositoaccount) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $depositoaccount->deposito_account_no;
	            $row[] = $depositoaccount->member_name;
	            $row[] = $depositoaccount->deposito_name;
	            $row[] = $depositoaccount->deposito_account_serial_no;
	            $row[] = tgltoview($depositoaccount->deposito_account_date);
	            $row[] = tgltoview($depositoaccount->deposito_account_due_date);
	            $row[] = number_format($depositoaccount->deposito_account_amount, 2);
	            $row[] = $depositoaccount->deposito_account_nisbah;
	            $row[] = '
	            	<a href="'.base_url().'deposito-account/print-certificate-front/'.$depositoaccount->deposito_account_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Cetak Depan Sertifikat</a>
	            	<br>
	            	<a href="'.base_url().'deposito-account/print-certificate-back/'.$depositoaccount->deposito_account_id.'" class="btn btn-xs green" role="button"><i class="fa fa-print"></i> Cetak Belakang Sertifikat</a>';
	            $data[] = $row;
	        }

	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctDepositoAccount_model->count_all(),
	                        "recordsFiltered" => $this->AcctDepositoAccount_model->count_filtered(),
	                        "data" => $data,
	                );
	        echo json_encode($output);
		}

		public function printCertificateAcctDepositoAccountFront(){
			$deposito_account_id 	= $this->uri->segment(3);
			$acctdepositoaccount	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($deposito_account_id);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');

			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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
			$pdf->SetFont('helvetica', '', 7);

			// -----------------------------------------------------------------------------

			$tbl = "
			<table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"5%\"></td>
			        <td width=\"60%\"><div style=\"text-align: left; font-size:16px; color: #0298db; font-weight: bold;\">SERTIFIKAT SIMPANAN MASA DEPAN</div></td>
			        <td width=\"25%\"><div style=\"text-align: center; font-size:12px\"> No. Seri : ".$acctdepositoaccount['deposito_account_no']."</div></td>
			    </tr>
			</table>
			<br><br><br><br><br>
			<table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"90%\"><div style=\"text-align: left; font-size:12px\">Telah dibukukan ke dalam rekening simpanan masa depan :</div></td>
			    </tr>
			</table>
			<table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"20%\"><div style=\"text-align: left; font-size:12px\">Atas Nama</div></td>
			        <td width=\"2%\"><div style=\"text-align: left; font-size:12px\">:</div></td>
			        <td width=\"18%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['member_name']."</div></td>
			        <td width=\"5%\"><div style=\"text-align: left; font-size:12px\"></div></td>
			        <td width=\"13%\"><div style=\"text-align: left; font-size:12px\">No Anggota : </div></td>
			        <td width=\"18%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['member_no']."</div></td>
				</tr>
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"20%\"><div style=\"text-align: left; font-size:12px\">Alamat</div></td>
			        <td width=\"2%\"><div style=\"text-align: left; font-size:12px\">:</div></td>
			        <td width=\"68%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['member_address']."</div></td>
			    </tr>
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"20%\"><div style=\"text-align: left; font-size:12px\">Jumlah</div></td>
			        <td width=\"2%\"><div style=\"text-align: left; font-size:12px\">:</div></td>
			        <td width=\"68%\"><div style=\"text-align: left; font-size:12px\">Rp ".number_format($acctdepositoaccount['deposito_account_amount'], 2)." (".numtotxt($acctdepositoaccount['deposito_account_amount']).")</div></td>
			    </tr>
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"20%\"><div style=\"text-align: left; font-size:12px\">Jangka Waktu</div></td>
			        <td width=\"2%\"><div style=\"text-align: left; font-size:12px\">:</div></td>
			        <td width=\"68%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['deposito_account_period']." bulan</div></td>
			    </tr>
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"20%\"><div style=\"text-align: left; font-size:12px\">Tanggal Jatuh Tempo</div></td>
			        <td width=\"2%\"><div style=\"text-align: left; font-size:12px\">:</div></td>
			        <td width=\"68%\"><div style=\"text-align: left; font-size:12px\">".date('d-m-Y', strtotime($acctdepositoaccount['deposito_account_due_date']))."</div></td>
			    </tr>";
				if($acctdepositoaccount['deposito_account_extra_type'] == 0){
					$tbl .= "
					<tr>
						<td width=\"5%\"></td>
						<td width=\"20%\"><div style=\"text-align: left; font-size:12px\">Perpanjangan</div></td>
						<td width=\"2%\"><div style=\"text-align: left; font-size:12px\">:</div></td>
						<td width=\"68%\"><div style=\"text-align: left; font-size:12px\">Manual</div></td>
					</tr>
					";
				}else{
					$tbl .= "
					<tr>
						<td width=\"5%\"></td>
						<td width=\"20%\"><div style=\"text-align: left; font-size:12px\">Perpanjangan</div></td>
						<td width=\"2%\"><div style=\"text-align: left; font-size:12px\">:</div></td>
						<td width=\"68%\"><div style=\"text-align: left; font-size:12px\">Otomatis (ARO - Automatic Roll Over) sebesar nilai nominal</div></td>
					</tr>
					";
				}
			    $tbl .= "
				<tr>
					<td width=\"5%\"></td>
			        <td width=\"20%\"><div style=\"text-align: left; font-size:12px\">Suku Bunga</div></td>
			        <td width=\"2%\"><div style=\"text-align: left; font-size:12px\">:</div></td>
			        <td width=\"68%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['deposito_interest_rate']."%</div></td>
			    </tr>
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"20%\"><div style=\"text-align: left; font-size:12px\">Bunga Dibayar</div></td>
			        <td width=\"2%\"><div style=\"text-align: left; font-size:12px\">:</div></td>
			        <td width=\"68%\"><div style=\"text-align: left; font-size:12px\">Tiap bulan tanggal ".date('d', strtotime($acctdepositoaccount['deposito_account_date']))." dibukukan ke simpanan sukarela dengan nomor anggota ".$acctdepositoaccount['member_no'].".</div></td>
			    </tr>
			</table>
			<br><br><br>
			<table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"90%\"><div style=\"text-align: left; font-size:10px\">Kami menegaskan bahwa Simpanan Masa Depan Bapak/Ibu pada kami sebagaimana tertera di sini beserta lampirannya sesuai dengan isi permohonan dan persetujuan Bapak/Ibu.  Saat pencairan Simpanan Masa Depan Bapak/Ibu wajib membawa Sertifikat Simpanan Masa Depan ini.</div></td>
			    </tr>
			</table>
			<br><br>
			<table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"90%\"><div style=\"text-align: left; font-size:10px\">Sertifikat Simpanan Masa Depan ini sah apabila data yang tercetak sesuai dengan data pada Koperasi Menjangan Enam dan ditandatangani oleh pejabat Koperasi Menjangan Enam yang berwenang.</div></td>
			    </tr>
			</table>
			<br><br>
			<table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"65%\"></td>
			        <td width=\"30%\"><div style=\"text-align: left; font-size:12px\">Semarang, ".date('d-m-Y')."</div></td>
			    </tr>
			</table>
			<br><br>
			<table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"90%\"><div style=\"text-align: left; font-size:10px\">KOPERASI MENJANGAN ENAM</div></td>
			    </tr>
			    <tr>
					<td width=\"5%\"></td>
			        <td width=\"90%\"><div style=\"text-align: left; font-size:10px\">Jl. Simongan 131 - Semarang, telp./facs.: 024 766 300 34</div></td>
			    </tr>
			</table>
			<table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"65%\"></td>
			        <td width=\"30%\"><div style=\"text-align: left; font-size:12px; text-decoration: underline;\">Deden Kurniadi</div></td>
			    </tr>
			    <tr>
					<td width=\"65%\"></td>
			        <td width=\"30%\"><div style=\"text-align: left; font-size:12px\">Ketua Umum</div></td>
			    </tr>
			</table>
			";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			if (ob_get_length() > 0){
				ob_clean();
			}
			// -----------------------------------------------------------------------------
			
			$filename = 'Cetak_Sertifikat_Bg_Depan.pdf';
			$pdf->Output($filename, 'I');
		}

		public function printCertificateAcctDepositoAccountBack(){
			$deposito_account_id 	= $this->uri->segment(3);
			$acctdepositoaccount	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($deposito_account_id);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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
			$pdf->SetFont('helvetica', '', 7);

			// -----------------------------------------------------------------------------

			$tbl = "
			<table cellspacing=\"2\" cellpadding=\"1\" border=\"0\">
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: center; font-size:14px;\">
							Syarat-syarat Untuk Penempatan Simpanan Berjangka di Koperasi Menjangan Enam (Koperasi)
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td style\"height: 65px\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						1. Pemilik Simpanan Masa Depan akan menerima suatu Sertifikat Simpanan Masa Depan yang merupakan suatu memorandum Simpanan Berjangka dan sekaligus perjanjian antara Koperasi dengan Pemilik Simpanan Masa Depan
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						2. Jika Koperasi tidak diberitahu tentang adanya kekeliruan dalam jangka waktu 7 (tujuh) hari setelah tanggal valuta, maka hal tersebut dianggap pengakuan oleh nasabah dan Simpanan yang telah ditempatkan dianggap benar.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						3. Sertifikat Simpanan Masa Depan ini diterbitkan olah Koperasi atas nama pemilik dan tidak dapat dipindahtangankan.  Bilamana Pemilik Simpanan Masa Depan dinyatakan tidak kuasa menurut hukum untuk mengambil Simpanan Masa Depan atau bunganya karena meninggal dunia atau karena sebab-sebab lainnya, maka Simpanan Masa Depan tersebut atau bunganya akan dibayarkan kepada ahli warisnya yang sah menurut hukum, pada saat Simpanan Masa Depan jatuh tempo.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						4. Sertifikat Simpanan Masa Depan ini hanya dapat dibayarkan kembali pada tanggal jatuh tempo di Koperasi dalam mata uang rupiah dengan menunjukkan Sertifikat Simpanan Masa Depan.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						5. Biaya meterai, pemindahbukuan atau biaya-biaya lain karena penempatan dan atau penarikan dari Simpanan Masa Depan akan ditanggung oleh Pemilik Simpanan Masa Depan yang harus dibayar atas permintaan dan Koperasi diberi wewenang untuk memotong dari jumlah nominal dan atau bunga sejumlah dimaksud pada saat jatuh tempo atau pada saat pencairan.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						6. Simpanan Masa Depan dengan Automatic Roll Over (ARO) dan nominal yang ditransfer atau dipindahbukukan pada saat jatuh tempo, apabila sampai dengan jam 14.00 pada hari jatuh tempo Koperasi tidak menerima instruksi pencairan dan Sertifikat Simpanan Masa Depan, maka Koperasi secara otomatis akan memperpanjang Simpanan Masa Depan tersebut dengan memberlakukan syarat-syarat Simpanan Masa Depan terdahulu dan suku bunga yang berlaku saat diperbaharui.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						7. Bilamana Simpanan Masa Depan diperpanjang, maka Koperasi tidak akan menerbitkan Sertifikat Simpanan Masa Depan baru namun cukup menerbitkan konfirmasi sebagai bukti Simpanan Masa Depan nasabah telah diperpanjang.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						8. Penarikan sebagian atau penarikan seluruh jumlah Simpanan Masa Depan sebelum jatuh tempo dikenakan penalty sebesar 0.6% dari nilai nominal Simpanan Masa Depan.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						9. Bunga Simpanan Masa Depan dipindahbukukan ke Simpanan Sukarela atau ditransfer setiap bulan pada tanggal jatuh tempo bunga/Simpanan Masa Depan setelah dipotong pajak.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						10. Instruksi transfer hanya akan dilaksanakan pada hari kerja.  Apabila tanggal jatuh tempo nominal Simpanan Masa Depan dan atau jatuh tempo bunga bertepatan dengan hari libur, maka nominal Simpanan Masa Depan dan atau bunga Simpanan Masa Depan aka ditransfer pada hari kerja berikutnya.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						11. Pemilik Simpanan Masa Depan bertanggungjawab penuh atas penyalahgunaan Sertifikat Simpanan Masa Depan.  Apabila Sertifikat Simpanan Masa Depan tersebut hilang atau dicuri,Pemilik Simpanan Masa Depan segera melaporkan secara tertulis maupun lisan kepada Koperasi disertai dengan Surat Keterangan dari Kepolisian.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						12. Koperasi tidak berkewajiban atau bertanggunjawab kepada Pemilik Simpanan Masa Depan atas setiap pengurangan karena pajak dan atau bea dan atau penyusutan nilai dana yang dikreditkan pada rekening dan atau atas tidak tersedianya dana tersebut karena adanya sebab-sebab di luar kuasa Koperasi.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						13. Perubahan nama, alamat, tanda tangan dan hal-hal lain yang menyimpang dari keterangan-keterangan yang pernah diberikan kepada Koperasi, Pemilik Simpanan Masa Depan wajib memberitahukan pada Koperasi dengan segera.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"96%\">
						<div style=\"text-align: justify; font-size:10px;\">
						14. Koperasi berhak mengadakan perubahan terhadap syarat-syarat tersebut di atas bila hal itu terpaksa dilakukan berhubung keadaan yang memaksa atau karena peraturan pemerintah.
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"20%\" style=\"height: 25px;\">
					</td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"20%\">
						<div style=\"text-align: center; font-size:10px;\">
						Mengetahui / Menyetujui : Pejabat Koperasi
						</div>
					</td>
					<td width=\"30%\">
						<div style=\"text-align: center; font-size:10px; font-weight: bold; border: 2px solid #000000;\">
						Bukti Pencairan Simpanan Masa Depan 
						</div>
					</td>
					<td width=\"48%\">
						<div style=\"text-align: center; font-size:10px;\">
						Nilai Simpanan Berjangka pada saat pencairan telah kami terima 
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"20%\" style=\"height: 45px;\">
					</td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"20%\"></td>
					<td width=\"30%\"></td>
					<td width=\"48%\">
						<div style=\"text-align: center; font-size:10px;\">
						Materai Rp. 10.000,- 
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"20%\" style=\"height: 15px;\">
					</td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"20%\">
						<div style=\"text-align: center; font-size:10px;\">
						.......................................... 
						</div>
					</td>
					<td width=\"30%\"></td>
					<td width=\"48%\">
						<div style=\"text-align: center; font-size:10px;\">
						.......................................... 
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
				<tr>
					<td width=\"2%\"></td>
					<td width=\"20%\">
						<div style=\"text-align: center; font-size:10px;\">
						Tanda Tangan 
						</div>
					</td>
					<td width=\"30%\"></td>
					<td width=\"48%\">
						<div style=\"text-align: center; font-size:10px;\">
						Tanda Tangan Pemilik Masa Depan 
						</div>
					</td>
					<td width=\"2%\"></td>
				</tr>
			</table>";
			

			$pdf->writeHTML($tbl, true, false, false, false, '');
			if (ob_get_length() > 0){
				ob_clean();
			}
			// -----------------------------------------------------------------------------
			
			$filename = 'Cetak_Sertifikat_Bg_Belakang.pdf';
			$pdf->Output($filename, 'I');
		}

		//-----------------------------------------------------------------------------------------------------------------------------//

		public function getAcctDepositoAccountDueDate(){
			$sesi 	= $this->session->userdata('filter-acctdepositoaccountduedate');
			$auth 	= $this->session->userdata('auth');
			if(!is_array($sesi)){
				$sesi['deposito_id']		='';
				if($auth['branch_status'] == 0){
					$sesi['branch_id']		= $auth['branch_id'];
				} else {
					$sesi['branch_id']		= '';
				}
			}

			$data['main_view']['acctdepositoaccount']		= $this->AcctDepositoAccount_model->getAcctDepositoAccountDueDate($sesi['deposito_id'], $sesi['branch_id']);
			$data['main_view']['corebranch']				= create_double($this->AcctDepositoAccount_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['acctdeposito']				= create_double($this->AcctDepositoAccount_model->getAcctDeposito(),'deposito_id', 'deposito_name');	
			$data['main_view']['content']					= 'AcctDepositoAccount/ListAcctDepositoAccountExtra_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filterAcctDepositoAccountDueDate(){
			$data = array (
				"deposito_id"	=> $this->input->post('deposito_id',true),
				"branch_id"		=> $this->input->post('branch_id',true),
			);

			$this->session->set_userdata('filter-acctdepositoaccountduedate',$data);
			redirect('deposito-account/get-due-date');
		}

		public function reset_search_duedate(){
			$this->session->unset_userdata('filter-acctdepositoaccountduedate');
			redirect('deposito-account/get-due-date');
		}

		public function addAcctDepositoAccountExtra(){
			$data['main_view']['membergender']				= $this->configuration->MemberGender();
			$data['main_view']['memberidentity']			= $this->configuration->MemberIdentity();
			$data['main_view']['token']						= md5(date('Y-m-d H:i:s'));
			$data['main_view']['acctdepositoaccount']		= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($this->uri->segment(3));
			$data['main_view']['content']					= 'AcctDepositoAccount/FormAddAcctDepositoAccountExtra_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processAddAcctDepositoAccountExtra(){
			$auth = $this->session->userdata('auth');
			$amount_administration =  $this->input->post('deposito_account_amount_adm', true);
			$data = array(
				'deposito_account_id'					=> $this->input->post('deposito_account_id', true),
				'deposito_id'							=> $this->input->post('deposito_id', true),
				'member_id'								=> $this->input->post('member_id', true),
				'branch_id'								=> $auth['branch_id'],
				'deposito_account_extra_period'			=> $this->input->post('deposito_account_extra_period', true),
				'deposito_account_extra_date'			=> tgltodb($this->input->post('deposito_account_extra_date', true)),
				'deposito_account_extra_due_date'		=> tgltodb($this->input->post('deposito_account_extra_due_date', true)),
				'deposito_account_extra_token'			=> $this->input->post('deposito_account_extra_token', true),
				'created_id'							=> $auth['user_id'],
				'created_on'							=> date('Y-m-d H:i:s'),
			);

			$data_update = array (
				'deposito_account_period'		=> $this->input->post('deposito_account_period', true) + $this->input->post('deposito_account_extra_period', true),
				'deposito_account_due_date'		=> tgltodb($this->input->post('deposito_account_extra_due_date', true)),
			);

			$data_deposito = array (
				'deposito_account_due_date'		=> tgltodb($this->input->post('deposito_account_due_date', true)),
				'deposito_account_nisbah'		=> $this->input->post('deposito_account_nisbah', true),
				'deposito_account_amount'		=> $this->input->post('deposito_account_amount', true),
				'savings_account_id'			=> $this->input->post('savings_account_id', true),
			);
			
			$this->form_validation->set_rules('deposito_account_extra_period', 'Jangka Waktu', 'required');
			
			if($this->form_validation->run()==true){
				$deposito_account_extra_token 			= $this->AcctDepositoAccount_model->getDepositoAccountExtraToken($data['deposito_account_extra_token']);
			
				if($deposito_account_extra_token->num_rows()==0){
					if($this->AcctDepositoAccount_model->insertAcctDepositoAccountExtra($data, $data_update)){
						$date 	= date('d', strtotime($data_deposito['deposito_account_due_date']));
						$month 	= date('m', strtotime($data_deposito['deposito_account_due_date']));
						$year 	= date('Y', strtotime($data_deposito['deposito_account_due_date']));

						for ($i=1; $i<= $data['deposito_account_extra_period']; $i++) { 
							$depositoprofitsharing = array ();

							$month = $month + 1;

							if($month == 13){
								$month = 01;
								$year = $year + 1;
							}

							$deposito_profit_sharing_due_date = $year.'-'.$month.'-'.$date;

							$depositoprofitsharing = array (
								'deposito_account_id'				=> $data['deposito_account_id'],
								'branch_id'							=> $auth['branch_id'],
								'deposito_id'						=> $data['deposito_id'],
								'deposito_account_nisbah'			=> $data_deposito['deposito_account_nisbah'],
								'member_id'							=> $data['member_id'],
								'deposito_profit_sharing_due_date'	=> $deposito_profit_sharing_due_date,
								'deposito_daily_average_balance'	=> $data_deposito['deposito_account_amount'],
								'deposito_account_last_balance'		=> $data_deposito['deposito_account_amount'],
								'savings_account_id'				=> $data_deposito['savings_account_id'],
								'deposito_profit_sharing_token'		=> 'PST'.$data['deposito_account_extra_token'],
							);

							$depositoprofitsharing_data = $this->AcctDepositoAccount_model->getAcctDepositoProfitSharing($depositoprofitsharing);
							
							if($depositoprofitsharing_data->num_rows() == 0){
								$this->AcctDepositoAccount_model->insertAcctDepositoProfitSharing($depositoprofitsharing);
							}
						}

						if($amount_administration > 0){
							$transaction_module_code = "PDEP";

							$transaction_module_id 		= $this->AcctDepositoAccount_model->getTransactionModuleID($transaction_module_code);
							$acctdepositoaccount_last 	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($data['deposito_account_id']);

							$journal_voucher_period = date("Ym", strtotime($data['deposito_account_extra_due_date']));
							
							$data_journal = array(
								'branch_id'						=> $auth['branch_id'],
								'journal_voucher_period' 		=> $journal_voucher_period,
								'journal_voucher_date'			=> date('Y-m-d'),
								'journal_voucher_title'			=> 'PERPANJANGAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
								'journal_voucher_description'	=> 'PERPANJANGAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
								'transaction_module_id'			=> $transaction_module_id,
								'transaction_module_code'		=> $transaction_module_code,
								'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
								'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_no'],
								'journal_voucher_token' 		=> 'ED'.$data['deposito_account_extra_token'],
								'created_id' 					=> $auth['user_id'],
								'created_on' 					=> date('Y-m-d H:i:s'),
							);
							
							$this->AcctDepositoAccount_model->insertAcctJournalVoucher($data_journal);

							$journal_voucher_id = $this->AcctDepositoAccount_model->getJournalVoucherID($data_journal['created_id']);

							$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();

							$account_id = $this->AcctDepositoAccount_model->getAccountID($acctdepositoaccount_last['deposito_id']);

							$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);
						
							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $amount_administration,
								'journal_voucher_debit_amount'	=> $amount_administration,
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> 'STR1'.$data['deposito_account_extra_token'].$amount_administration,
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);

							$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();

							$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_mutation_adm_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $amount_administration,
								'journal_voucher_credit_amount'	=> $amount_administration,
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> 'STR2'.$data['deposito_account_extra_token'].$preferencecompany['account_mutation_adm_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
						$auth = $this->session->userdata('auth');
						$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Perpanjangan Simpanan Berjangka Sukses
								</div> ";
						$sesi = $this->session->userdata('unique');
						$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
						$this->session->set_userdata('message',$msg);
						redirect('deposito-account/get-due-date');
					}else{
						$this->session->set_userdata('addacctdepositoaccount',$data);
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Perpanjangan Simpanan Berjangka Tidak Berhasil
								</div> ";
						$this->session->set_userdata('message',$msg);
						redirect('deposito-account/add-extra/'.$data['deposito_account_id']);
					}
				}else{
					$date 	= date('d', strtotime($data_deposito['deposito_account_due_date']));
					$month 	= date('m', strtotime($data_deposito['deposito_account_due_date']));
					$year 	= date('Y', strtotime($data_deposito['deposito_account_due_date']));
					
					for ($i=1; $i<= $data['deposito_account_extra_period']; $i++) { 
						$depositoprofitsharing = array ();

						$month = $month + 1;

						if($month == 13){
							$month = 01;
							$year = $year + 1;
						}

						$deposito_profit_sharing_due_date = $year.'-'.$month.'-'.$date;

						$depositoprofitsharing = array (
							'deposito_account_id'				=> $data['deposito_account_id'],
							'branch_id'							=> $auth['branch_id'],
							'deposito_id'						=> $data['deposito_id'],
							'deposito_account_nisbah'			=> $data_deposito['deposito_account_nisbah'],
							'member_id'							=> $data['member_id'],
							'deposito_profit_sharing_due_date'	=> $deposito_profit_sharing_due_date,
							'deposito_daily_average_balance'	=> $data_deposito['deposito_account_amount'],
							'deposito_account_last_balance'		=> $data_deposito['deposito_account_amount'],
							'savings_account_id'				=> $data_deposito['savings_account_id'],
							'deposito_profit_sharing_token'		=> 'PST'.$data['deposito_account_extra_token'],
						);
						$deposito_profit_sharing_token = $this->AcctDepositoAccount_model->getAcctDepositoProfitSharingToken($depositoprofitsharing['deposito_profit_sharing_token']);
							if($deposito_profit_sharing_token->num_rows() == 0){
								$depositoprofitsharing_data = $this->AcctDepositoAccount_model->getAcctDepositoProfitSharing($depositoprofitsharing);
							
							if($depositoprofitsharing_data->num_rows() == 0){
								$this->AcctDepositoAccount_model->insertAcctDepositoProfitSharing($depositoprofitsharing);
							}
						}
					}

					$token = 'ED'.$data['deposito_account_extra_token'];
					$journal_voucher_token = $this->AcctDepositoAccount_model->getAcctJournalVoucherToken($token);
					if($journal_voucher_token->num_rows() == 0){
						if($amount_administration > 0){
							$transaction_module_code = "PDEP";
	
							$transaction_module_id 		= $this->AcctDepositoAccount_model->getTransactionModuleID($transaction_module_code);
							$acctdepositoaccount_last 	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($data['deposito_account_id']);
	
							$journal_voucher_period = date("Ym", strtotime($data['deposito_account_extra_due_date']));
							
							$data_journal = array(
								'branch_id'						=> $auth['branch_id'],
								'journal_voucher_period' 		=> $journal_voucher_period,
								'journal_voucher_date'			=> date('Y-m-d'),
								'journal_voucher_title'			=> 'PERPANJANGAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
								'journal_voucher_description'	=> 'PERPANJANGAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
								'transaction_module_id'			=> $transaction_module_id,
								'transaction_module_code'		=> $transaction_module_code,
								'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
								'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_no'],
								'journal_voucher_token' 		=> 'ED'.$data['deposito_account_extra_token'],
								'created_id' 					=> $auth['user_id'],
								'created_on' 					=> date('Y-m-d H:i:s'),
							);
							
							$this->AcctDepositoAccount_model->insertAcctJournalVoucher($data_journal);
	
							$token = 'STR1'.$data['deposito_account_extra_token'].$amount_administration;
							$journal_voucher_item_token = $this->AcctDepositoAccount_model->getAcctJournalVoucherItemToken($token);
							if($journal_voucher_item_token->num_rows() == 0){
								$journal_voucher_id = $this->AcctDepositoAccount_model->getJournalVoucherID($data_journal['created_id']);
								$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();
								$account_id = $this->AcctDepositoAccount_model->getAccountID($acctdepositoaccount_last['deposito_id']);
								$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);
		
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_cash_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $amount_administration,
									'journal_voucher_debit_amount'	=> $amount_administration,
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'STR1'.$data['deposito_account_extra_token'].$amount_administration,
									'created_id' 					=> $auth['user_id']
								);
								
								$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);
							}
							$token = 'STR2'.$data['deposito_account_extra_token'].$amount_administration;
							$journal_voucher_item_token = $this->AcctDepositoAccount_model->getAcctJournalVoucherItemToken($token);
								if($journal_voucher_item_token->num_rows() == 0){
								$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();
		
								$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);
		
								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_mutation_adm_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $amount_administration,
									'journal_voucher_credit_amount'	=> $amount_administration,
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> 'STR2'.$data['deposito_account_extra_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}
					}
					
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Perpanjangan Simpanan Berjangka Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account/get-due-date');
				}
			}else{
				$this->session->set_userdata('addacctdepositoaccount',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('deposito-account/add-extra/'.$data['deposito_account_id']);
			}
		}

		//-----------------------------------------------------------------------------------------------------------------------------//

		public function getClosedAcctDepositoAccount(){
			$sesi = $this->session->userdata('filter-closedacctdepositoaccount');
			$auth 	= $this->session->userdata('auth');
			if(!is_array($sesi)){
				$sesi['deposito_id']		='';
				if($auth['branch_status'] == 0){
					$sesi['branch_id']		= $auth['branch_id'];
				} else {
					$sesi['branch_id']		= '';
				}
			}

			$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
			$data['main_view']['acctdepositoaccount']		= $this->AcctDepositoAccount_model->getClosedAcctDepositoAccount($sesi['deposito_id'], $sesi['branch_id']);
			$data['main_view']['acctdeposito']				= create_double($this->AcctDepositoAccount_model->getAcctDeposito(),'deposito_id', 'deposito_name');
			$data['main_view']['corebranch']				= create_double($this->AcctDepositoAccount_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']					= 'AcctDepositoAccount/ListAcctDepositoAccountClosed_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filterClosedAcctDepositoAccount(){
			$data = array (
				"deposito_id"	=> $this->input->post('deposito_id',true),
				"branch_id"		=> $this->input->post('branch_id',true),
			);

			$this->session->set_userdata('filter-closedacctdepositoaccount',$data);
			redirect('deposito-account/get-closed');
		}

		public function reset_search_closed(){
			$this->session->unset_userdata('filter-closedacctdepositoaccount');
			redirect('deposito-account/get-closed');
		}

		public function getAcctSavingsAccountList(){
			$deposito_account_id = $this->uri->segment(3);
			$auth = $this->session->userdata('auth');
			$branch_id = '';
			$list = $this->AcctSavingsAccount_model->get_datatables($branch_id);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $savingsaccount) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $savingsaccount->savings_account_no;
	            $row[] = $savingsaccount->member_name;
	            $row[] = $savingsaccount->member_address;
	            $row[] = '<a href="'.base_url().'deposito-account/add-closed/'.$deposito_account_id.'/'.$savingsaccount->savings_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	            $data[] = $row;
	        }
	 
	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctSavingsAccount_model->count_all($branch_id),
	                        "recordsFiltered" => $this->AcctSavingsAccount_model->count_filtered($branch_id),
	                        "data" => $data,
	                );
	        echo json_encode($output);
		}

		public function addClosedAcctDepositoAccount(){
			$unique 				= $this->session->userdata('unique');
			$token 					= $this->session->userdata('acctdepositoaccounttoken-'.$unique['unique']);

			$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();

			if(empty($token)){
				$token = md5(date('Y-m-d H:i:s'));
				$this->session->set_userdata('acctdepositoaccounttoken-'.$unique['unique'], $token);
			}

			$deposito_accrual_last_balance = $this->AcctDepositoAccount_model->getAcctDepositoAccrualLastBalance($this->uri->segment(3));
			$acctdepositoaccount 		   = $this->AcctDepositoAccount_model->getAcctDepositoAccountDetail($this->uri->segment(3));

			$interest_total		 		   = $deposito_accrual_last_balance + $acctdepositoaccount['deposito_account_nisbah'];

			$data['main_view']['membergender']				= $this->configuration->MemberGender();
			$data['main_view']['memberidentity']			= $this->configuration->MemberIdentity();
			$data['main_view']['admmethod']					= $this->configuration->AdmMethod();
			$data['main_view']['acctbankaccount']			= create_double($this->AcctDepositoAccount_model->getAcctBankAccount(),'bank_account_id', 'bank_account_code');
			$data['main_view']['acctdepositoaccount']		= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($this->uri->segment(3));
			$data['main_view']['acctsavingsaccount']		= $this->AcctDepositoAccount_model->getAcctSavingsAccount_Detail($this->uri->segment(4));
			$data['main_view']['interest_total']			= $interest_total;
			$data['main_view']['content']					= 'AcctDepositoAccount/FormClosedAcctDepositoAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processClosedAcctDepositoAccount(){
			$auth = $this->session->userdata('auth');

			$data = array(
				'deposito_account_id'					=> $this->input->post('deposito_account_id', true),
				'deposito_account_penalty'				=> $this->input->post('deposito_account_penalty', true),
				'deposito_account_closed_date'			=> date('Y-m-d'),
				'deposito_account_status'				=> 1,
				'savings_account_id'					=> $this->input->post('savings_account_id', true),
				'deposito_account_closed_adm_amount'	=> $this->input->post('deposito_account_amount_adm', true),
				'deposito_account_closed_adm_method'	=> $this->input->post('adm_method', true),
				'adm_bank_account_id'					=> $this->input->post('adm_bank_account_id', true),
				'deposito_account_closed_token'			=> $this->input->post('deposito_account_closed_token', true),
			);

			if($data['deposito_account_closed_adm_method'] == 3){
				$deposito_account_amount = $this->input->post('deposito_account_amount', true) - $data['deposito_account_closed_adm_amount'];
			}else{
				$deposito_account_amount = $this->input->post('deposito_account_amount', true);
			}

			$amount_administration =  $this->input->post('deposito_account_amount_adm', true);

			$data_savings = array (
				'savings_id'						=> $this->input->post('savings_id', true),
				'member_id'							=> $this->input->post('member_id_savings', true),
				'savings_account_opening_balance'	=> $this->input->post('savings_account_last_balance', true),
				'savings_account_last_balance'		=> $this->input->post('savings_account_last_balance', true) + $this->input->post('deposito_account_amount', true),
			);	

			$deposito_accrual_last_balance = $this->AcctDepositoAccount_model->getAcctDepositoAccrualLastBalance($data['deposito_account_id']);
			$acctdepositoaccount 		   = $this->AcctDepositoAccount_model->getAcctDepositoAccountDetail($data['deposito_account_id']);

			$total_amount				   			= $this->input->post('deposito_account_amount', true);

			$deposito_account_closed_token 			= $this->AcctDepositoAccount_model->getDepositoAccountClosedToken($data['deposito_account_closed_token']);
			
			if($deposito_account_closed_token->num_rows()==0){
				if($this->AcctDepositoAccount_model->closedAcctDepositoAccountExtra($data)){
					$data_transfer = array (
						'branch_id'							=> $auth['branch_id'],
						'savings_transfer_mutation_date'	=> date('Y-m-d'),
						'savings_transfer_mutation_amount'	=> $deposito_account_amount,
						'operated_name'						=> 'SYS',
						'savings_transfer_mutation_token'	=> $data['deposito_account_closed_token'],
						'created_id'						=> $auth['user_id'],
						'created_on'						=> date('Y-m-d H:i:s'),
					);

					if($this->AcctSavingsTransferMutation_model->insertAcctSavingsTransferMutation($data_transfer)){
						$savings_transfer_mutation_id = $this->AcctSavingsTransferMutation_model->getSavingsTransferMutationID($data_transfer['created_on']);

						$data_transfer_to = array (
							'savings_transfer_mutation_id'			=> $savings_transfer_mutation_id,
							'savings_account_id'					=> $data['savings_account_id'],
							'savings_id'							=> $data_savings['savings_id'],
							'member_id'								=> $data_savings['member_id'],
							'branch_id'								=> $auth['branch_id'],
							'mutation_id'							=> 10,
							'savings_account_opening_balance'		=> $data_savings['savings_account_opening_balance'],
							'savings_transfer_mutation_to_amount'	=> $deposito_account_amount,
							'savings_account_last_balance'			=> $data_savings['savings_account_last_balance'],
							'savings_transfer_mutation_to_token'	=> $data['deposito_account_closed_token'].$savings_transfer_mutation_id,
						);

						if($this->AcctSavingsTransferMutation_model->insertAcctSavingsTransferMutationTo($data_transfer_to)){
							$transaction_module_code = "PDEP";

							$transaction_module_id 		= $this->AcctDepositoAccount_model->getTransactionModuleID($transaction_module_code);
							$acctdepositoaccount_last 	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($data['deposito_account_id']);

							$journal_voucher_period = date("Ym", strtotime($data['deposito_account_closed_date']));
							
							$data_journal = array(
								'branch_id'						=> $auth['branch_id'],
								'journal_voucher_period' 		=> $journal_voucher_period,
								'journal_voucher_date'			=> date('Y-m-d'),
								'journal_voucher_title'			=> 'PENUTUPAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
								'journal_voucher_description'	=> 'PENUTUPAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
								'transaction_module_id'			=> $transaction_module_id,
								'transaction_module_code'		=> $transaction_module_code,
								'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
								'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_no'],
								'journal_voucher_token' 		=> $data['deposito_account_closed_token'],
								'created_id' 					=> $auth['user_id'],
								'created_on' 					=> date('Y-m-d H:i:s'),
							);
							$this->AcctDepositoAccount_model->insertAcctJournalVoucher($data_journal);

							$journal_voucher_id 		= $this->AcctDepositoAccount_model->getJournalVoucherID($data_journal['created_id']);
							$preferencecompany 			= $this->AcctDepositoAccount_model->getPreferenceCompany();
							$account_id 				= $this->AcctDepositoAccount_model->getAccountID($acctdepositoaccount_last['deposito_id']);
							$account_id_default_status 	= $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);

							//Simpanan Berjangka
							$data_debit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
								'journal_voucher_amount'		=> ABS($total_amount),
								'journal_voucher_debit_amount'	=> ABS($total_amount),
								'journal_voucher_item_token' 	=> $data['deposito_account_closed_token'].$account_id,
								'account_id_status'				=> 0,
								'created_id' 					=> $auth['user_id'],
							);

							$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debit);

							$account_id = $this->AcctDepositoAccount_model->getAccountSavingsID($data_savings['savings_id']);

							$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);

							$data_credit = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
								'journal_voucher_amount'		=> ABS($deposito_account_amount),
								'journal_voucher_credit_amount'	=> ABS($deposito_account_amount),
								'account_id_default_status'		=> $account_id_default_status,
								'journal_voucher_item_token' 	=> $data['deposito_account_closed_token'].$account_id,
								'account_id_status'				=> 1,
								'created_id' 					=> $auth['user_id'],
							);
							$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);
							
							if($amount_administration > 0){
								if($data['deposito_account_closed_adm_method'] == 1){
									$data_debet = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $preferencecompany['account_cash_id'],
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $amount_administration,
										'journal_voucher_debit_amount'	=> $amount_administration,
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 0,
										'journal_voucher_item_token'	=> 'STR1'.$data['deposito_account_closed_token'].$amount_administration,
										'created_id' 					=> $auth['user_id']
									);
									$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);
								}else if($data['deposito_account_closed_adm_method'] == 2){
									$account_bank_id					= $this->AcctDepositoAccount_model->getAccountBankID($data['adm_bank_account_id']);
		
									$account_id_default_status 			= $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_bank_id);

									$data_debet = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $account_bank_id,
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $amount_administration,
										'journal_voucher_debit_amount'	=> $amount_administration,
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 0,
										'journal_voucher_item_token'	=> 'STR1'.$data['deposito_account_closed_token'].$amount_administration,
										'created_id' 					=> $auth['user_id']
									);
									$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);
								}
	
								$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();
	
								$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_bank_id']);
	
								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_mutation_adm_bank_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $amount_administration,
									'journal_voucher_credit_amount'	=> $amount_administration,
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> 'STR2'.$data['deposito_account_closed_token'].$preferencecompany['account_mutation_adm_bank_id'],
									'created_id' 					=> $auth['user_id']
								);
	
								$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);
							}

							if($data['deposito_account_penalty'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_cash_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['deposito_account_penalty'],
									'journal_voucher_debit_amount'	=> $data['deposito_account_penalty'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'STRP1'.$data['deposito_account_closed_token'].$data['deposito_account_penalty'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);
	
								$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();
	
								$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($preferencecompany['account_penalty_id']);
	
								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_penalty_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['deposito_account_penalty'],
									'journal_voucher_credit_amount'	=> $data['deposito_account_penalty'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> 'STRP2'.$data['deposito_account_closed_token'].$preferencecompany['account_penalty_id'],
									'created_id' 					=> $auth['user_id']
								);
								$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}
					}
					
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Penutupan Simpanan Berjangka Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
					$this->session->unset_userdata('acctdepositoaccounttoken-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account/print-validation-closed/'.$data['deposito_account_id']);
				}else{
					$this->session->set_userdata('addacctdepositoaccount',$data);
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Penutupan Simpanan Berjangka Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account/get-closed');
				}
			}else{
				$data_transfer = array (
					'branch_id'							=> $auth['branch_id'],
					'savings_transfer_mutation_date'	=> date('Y-m-d'),
					'savings_transfer_mutation_amount'	=> $deposito_account_amount,
					'operated_name'						=> 'SYS',
					'savings_transfer_mutation_token'	=> $data['deposito_account_closed_token'],
					'created_id'						=> $auth['user_id'],
					'created_on'						=> date('Y-m-d H:i:s'),
				);


				$savings_transfer_mutation_token 		= $this->AcctDepositoAccount_model->getAcctSavingsTransferMutationToken($data_transfer['savings_transfer_mutation_token']);
			
				if($savings_transfer_mutation_token->num_rows()==0){
					if($this->AcctSavingsTransferMutation_model->insertAcctSavingsTransferMutation($data_transfer)){
						$savings_transfer_mutation_id = $this->AcctSavingsTransferMutation_model->getSavingsTransferMutationID($data_transfer['created_on']);

						$data_transfer_to = array (
							'savings_transfer_mutation_id'				=> $savings_transfer_mutation_id,
							'savings_account_id'						=> $data['savings_account_id'],
							'savings_id'								=> $data_savings['savings_id'],
							'member_id'									=> $data_savings['member_id'],
							'branch_id'									=> $auth['branch_id'],
							'mutation_id'								=> 10,
							'savings_account_opening_balance'			=> $data_savings['savings_account_opening_balance'],
							'savings_transfer_mutation_to_amount'		=> $deposito_account_amount,
							'savings_account_last_balance'				=> $data_savings['savings_account_last_balance'],
							'savings_transfer_mutation_to_token'		=> $data['deposito_account_closed_token'].$savings_transfer_mutation_id,
						);

						$savings_transfer_mutation_to_token 			= $this->AcctDepositoAccount_model->getAcctSavingsTransferMutationToToken($data_transfer_to['savings_transfer_mutation_to_token']);
			
						if($savings_transfer_mutation_to_token->num_rows()==0){
							if($this->AcctSavingsTransferMutation_model->insertAcctSavingsTransferMutationTo($data_transfer_to)){
								$transaction_module_code = "PDEP";

								$transaction_module_id 		= $this->AcctDepositoAccount_model->getTransactionModuleID($transaction_module_code);
								$acctdepositoaccount_last 	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($data['deposito_account_id']);

									
								$journal_voucher_period = date("Ym", strtotime($data['deposito_account_closed_date']));
								
								$data_journal = array(
									'branch_id'						=> $auth['branch_id'],
									'journal_voucher_period' 		=> $journal_voucher_period,
									'journal_voucher_date'			=> date('Y-m-d'),
									'journal_voucher_title'			=> 'PENUTUPAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
									'journal_voucher_description'	=> 'PENUTUPAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
									'transaction_module_id'			=> $transaction_module_id,
									'transaction_module_code'		=> $transaction_module_code,
									'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
									'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_no'],
									'journal_voucher_token' 		=> $data['deposito_account_closed_token'],
									'created_id' 					=> $auth['user_id'],
									'created_on' 					=> date('Y-m-d H:i:s'),
								);

								$journal_voucher_token 	= $this->AcctDepositoAccount_model->getAcctJournalVoucherToken($data_journal['journal_voucher_token']);
					
								if($journal_voucher_token->num_rows()==0){
									$this->AcctDepositoAccount_model->insertAcctJournalVoucher($data_journal);
								}

								$journal_voucher_id 		= $this->AcctDepositoAccount_model->getJournalVoucherID($data_journal['created_id']);
								$preferencecompany 			= $this->AcctDepositoAccount_model->getPreferenceCompany();
								$account_id 				= $this->AcctDepositoAccount_model->getAccountID($acctdepositoaccount_last['deposito_id']);
								$account_id_default_status 	= $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);

								$data_debit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
									'journal_voucher_amount'		=> ABS($total_amount),
									'journal_voucher_debit_amount'	=> ABS($total_amount),
									'journal_voucher_item_token' 	=> $data['deposito_account_closed_token'].$account_id,
									'account_id_status'				=> 0,
									'created_id' 					=> $auth['user_id'],
								);

								$journal_voucher_item_token 	= $this->AcctDepositoAccount_model->getAcctJournalVoucherItemToken($data_debit['journal_voucher_item_token']);
					
								if($journal_voucher_item_token->num_rows()==0){
									$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debit);
								}

								$account_id = $this->AcctDepositoAccount_model->getAccountSavingsID($data_savings['savings_id']);

								$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);

								$data_credit = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
									'journal_voucher_amount'		=> ABS($deposito_account_amount),
									'journal_voucher_credit_amount'	=> ABS($deposito_account_amount),
									'journal_voucher_item_token' 	=> $data['deposito_account_closed_token'].$account_id,
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
								);

								$journal_voucher_item_token 	= $this->AcctDepositoAccount_model->getAcctJournalVoucherItemToken($data_credit['journal_voucher_item_token']);
					
								if($journal_voucher_item_token->num_rows()==0){
									$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);
								}
								
								if($amount_administration > 0){
									$data_debet = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $preferencecompany['account_cash_id'],
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $amount_administration,
										'journal_voucher_debit_amount'	=> $amount_administration,
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 0,
										'journal_voucher_item_token'	=> 'STR1'.$data['deposito_account_closed_token'].$amount_administration,
										'created_id' 					=> $auth['user_id']
									);
		
									$journal_voucher_item_token 	= $this->AcctDepositoAccount_model->getAcctJournalVoucherItemToken($data_debet['journal_voucher_item_token']);
						
									if($journal_voucher_item_token->num_rows()==0){
										$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);
									}
		
									$preferencecompany 			= $this->AcctDepositoAccount_model->getPreferenceCompany();
									$account_id_default_status 	= $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);
		
									$data_credit =array(
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $preferencecompany['account_mutation_adm_id'],
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $amount_administration,
										'journal_voucher_credit_amount'	=> $amount_administration,
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 1,
										'journal_voucher_item_token'	=> 'STR2'.$data['deposito_account_closed_token'].$preferencecompany['account_mutation_adm_id'],
										'created_id' 					=> $auth['user_id']
									);
		
									$journal_voucher_item_token 	= $this->AcctDepositoAccount_model->getAcctJournalVoucherItemToken($data_credit['journal_voucher_item_token']);
						
									if($journal_voucher_item_token->num_rows()==0){
										$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);
									}
								}
							}
						}
					}

					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Penutupan Simpanan Berjangka Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
					$this->session->unset_userdata('acctdepositoaccounttoken-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account/print-validation-closed/'.$data['deposito_account_id']);
				}
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Penutupan Simpanan Berjangka Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
					$this->session->unset_userdata('acctdepositoaccounttoken-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account/print-validation-closed/'.$data['deposito_account_id']);
			}
		}

		public function printValidationClosedAcctDepositoAccount(){
			$deposito_account_id 	= $this->uri->segment(3);
			$acctdepositoaccount	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($deposito_account_id);


			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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

			$tbl = "
			<br><br><br><br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"55%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['deposito_account_no']."</div></td>
			        <td width=\"40%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['member_name']."</div></td>
			        <td width=\"5%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['office_id']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"52%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['validation_on']."</div></td>
			        <td width=\"18%\"><div style=\"text-align: right; font-size:14px\">".$this->AcctDepositoAccount_model->getUsername($acctdepositoaccount['validation_id'])."</div></td>
			        <td width=\"30%\"><div style=\"text-align: right; font-size:14px\"> IDR &nbsp; ".number_format($acctdepositoaccount['deposito_account_amount'], 2)."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			if (ob_get_length() > 0){
				ob_clean();
			}
			// -----------------------------------------------------------------------------
			
			$filename = 'Validasi.pdf';
			$pdf->Output($filename, 'I');
		}

		//-----------------------------------------------------------------------------------------------------------------------------//

		public function listAcctDepositoAccount(){
			$sesi	= $this->session->userdata('filter-listacctdepositoaccount');
			$auth 	= $this->session->userdata('auth');
			if(!is_array($sesi)){
				$sesi['deposito_id']		= '';
				if($auth['branch_status'] == 0){
					$sesi['branch_id']		= $auth['branch_id'];
				} else {
					$sesi['branch_id']		= '';
				}
			}

			$data['main_view']['acctdepositoaccount']		= $this->AcctDepositoAccount_model->getDataAcctDepositoAccount($sesi['deposito_id'], $sesi['branch_id']);
			$data['main_view']['acctdeposito']				= create_double($this->AcctDepositoAccount_model->getAcctDeposito(),'deposito_id', 'deposito_name');
			$data['main_view']['corebranch']				= create_double($this->AcctDepositoAccount_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']			= 'AcctDepositoAccount/ListAddAcctDepositoAccount_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function filterListAcctDepositoAccount(){
			$data = array (
				"deposito_id"	=> $this->input->post('deposito_id',true),
				"branch_id"		=> $this->input->post('branch_id',true),
			);

			$this->session->set_userdata('filter-listacctdepositoaccount',$data);
			redirect('deposito-account/list');
		}

		public function addNewAcctDepositoAccount(){
			$auth 					= $this->session->userdata('auth'); 
			$unique 				= $this->session->userdata('unique');
			$deposito_account_id 	= $this->uri->segment(3);
			$token 					= $this->session->userdata('acctdepositoaccount-'.$unique['unique']);

			if(empty($token)){
				$token = md5(date('Y-m-d H:i:s'));
				$this->session->set_userdata('acctdepositoaccount-'.$unique['unique'], $token);
			}

			$data['main_view']['acctdepositoaccount']		= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($deposito_account_id);	
			$data['main_view']['acctdeposito']				= create_double($this->AcctDepositoAccount_model->getAcctDeposito(),'deposito_id', 'deposito_name');
			$data['main_view']['acctsavingsaccount']		= create_double($this->AcctDepositoAccount_model->getAcctSavingsAccount($auth['branch_id']),'savings_account_id', 'savings_account_no');
			$data['main_view']['membergender']				=$this->configuration->MemberGender();
			$data['main_view']['memberidentity'] 			= $this->configuration->MemberIdentity();

			$data['main_view']['content']			= 'AcctDepositoAccount/FormAddNewAcctDepositoAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function getAcctDepositoAccount_Detail(){
			$deposito_account_id 	= $this->input->post('deposito_account_id');

			$data 			= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Detail($deposito_account_id);
			$membergender	= $this->configuration->MemberGender();
			$memberidentity = $this->configuration->MemberIdentity();
			$city_name 		= $this->AcctDepositoAccount_model->getCityName($data['city_id']);
			$kecamatan_name = $this->AcctDepositoAccount_model->getKecamatanName($data['kecamatan_id']);

			$result = array();
			$result = array(
				"member_id"					=> $data['member_id'],
				"member_no"					=> $data['member_no'], 
				"member_date_of_birth" 		=> tgltoview($data['member_date_of_birth']), 
				"member_gender"				=> $membergender[$data['member_gender']],
				"member_address"			=> $data['member_address'],
				"city_name"					=> $city_name,
				"kecamatan_name"			=> $kecamatan_name,
				"member_job"				=> $data['member_job'],
				"identity_name"				=> $memberidentity[$data['identity_id']],
				"member_identity_no"		=> $data['member_identity_no'],
				"member_phone"				=> $data['member_phone'],
			);

			echo json_encode($result);		
		}

		public function processAddNewAcctDepositoAccount(){
			$auth = $this->session->userdata('auth');

			$data = array(
				'member_id'								=> $this->input->post('member_id', true),
				'deposito_id'							=> $this->input->post('deposito_id', true),
				'office_id'								=> $this->input->post('office_id', true),
				'branch_id'								=> $auth['branch_id'],
				'savings_account_id'					=> $this->input->post('savings_account_id', true),
				'deposito_account_date'					=> date('Y-m-d'),
				'deposito_account_due_date'				=> tgltodb($this->input->post('deposito_account_due_date', true)),
				'deposito_account_no'					=> $this->input->post('deposito_account_no', true),
				'deposito_account_serial_no'			=> $this->input->post('deposito_account_serial_no', true),
				'deposito_account_amount'				=> $this->input->post('deposito_account_amount', true),
				'deposito_account_nisbah'				=> $this->input->post('deposito_account_nisbah', true),
				'deposito_account_period'				=> $this->input->post('deposito_period', true),
				'deposito_account_token'				=> $this->input->post('deposito_period', true),
				'created_id'							=> $auth['user_id'],
				'created_on'							=> date('Y-m-d H:i:s'),
			);
			
			$deposito_account_token 					= $this->AcctDepositoAccount_model->getDepositoAccountToken($data['deposito_account_token']);

			if($deposito_account_token->num_rows()==0){
				if($this->AcctDepositoAccount_model->insertAcctDepositoAccount($data)){
					$deposito_account_id = $this->AcctDepositoAccount_model->getDepositoAccountID($data['created_on']);

					$date 	= date('d', strtotime($data['deposito_account_date']));
					$month 	= date('m', strtotime($data['deposito_account_date']));
					$year 	= date('Y', strtotime($data['deposito_account_date']));

					for ($i=1; $i<= $data['deposito_account_period']; $i++) { 
						$depositoprofitsharing = array ();

						$month = $month + 1;

						if($month == 13){
							$month = 01;
							$year = $year + 1;
						}

						$deposito_profit_sharing_due_date = $year.'-'.$month.'-'.$date;

						$depositoprofitsharing = array (
							'deposito_account_id'				=> $deposito_account_id,
							'deposito_id'						=> $data['deposito_id'],
							'deposito_account_nisbah'			=> $data['deposito_account_nisbah'],
							'member_id'							=> $data['member_id'],
							'deposito_profit_sharing_due_date'	=> $deposito_profit_sharing_due_date,
							'deposito_daily_average_balance'	=> $data['deposito_account_amount'],
							'deposito_account_last_balance'		=> $data['deposito_account_amount'],
							'savings_account_id'				=> $data['savings_account_id'],
							'deposito_profit_sharing_token'		=> $data['deposito_account_token'].$i,
						);
						$this->AcctDepositoAccount_model->insertAcctDepositoProfitSharing($depositoprofitsharing);
					}

					$transaction_module_code 	= "DEP";
					$transaction_module_id 		= $this->AcctDepositoAccount_model->getTransactionModuleID($transaction_module_code);
					$acctdepositoaccount_last 	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Last($data['created_on']);
					$journal_voucher_period 	= date("Ym", strtotime($data['deposito_account_date']));
					
					$data_journal = array(
						'branch_id'						=> $auth['branch_id'],
						'journal_voucher_period' 		=> $journal_voucher_period,
						'journal_voucher_date'			=> date('Y-m-d'),
						'journal_voucher_title'			=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
						'journal_voucher_description'	=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
						'transaction_module_id'			=> $transaction_module_id,
						'transaction_module_code'		=> $transaction_module_code,
						'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
						'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_no'],
						'journal_voucher_token' 		=> $data['deposito_account_token'],
						'created_id' 					=> $data['created_id'],
						'created_on' 					=> $data['created_on'],
					);
					
					$this->AcctDepositoAccount_model->insertAcctJournalVoucher($data_journal);

					$journal_voucher_id = $this->AcctDepositoAccount_model->getJournalVoucherID($data['created_id']);

					$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();

					$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

					$data_debet = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_cash_id'],
						'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
						'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
						'journal_voucher_debit_amount'	=> ABS($data['deposito_account_amount']),
						'account_id_default_status'		=> $account_id_default_status,
						'journal_voucher_item_token' 	=> $data['deposito_account_token'].$preferencecompany['account_cash_id'],
						'account_id_status'				=> 0,
						'created_id' 					=> $auth['user_id'],
					);

					$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);

					$account_id = $this->AcctDepositoAccount_model->getAccountID($data['deposito_id']);

					$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);

					$data_credit =array(
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $account_id,
						'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
						'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
						'journal_voucher_credit_amount'	=> ABS($data['deposito_account_amount']),
						'journal_voucher_item_token' 	=> $data['deposito_account_token'].$account_id,
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'created_id' 					=> $auth['user_id'],
					);

					$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);

					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Simpanan Berjangka Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
					$this->session->unset_userdata('acctdepositoaccounttoken-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account');
				}else{
					$this->session->set_userdata('addacctdepositoaccount',$data);
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Simpanan Berjangka Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('deposito-account');
				}
			}else{
				$deposito_account_id = $this->AcctDepositoAccount_model->getDepositoAccountID($data['created_on']);

				$date 	= date('d', strtotime($data['deposito_account_date']));
				$month 	= date('m', strtotime($data['deposito_account_date']));
				$year 	= date('Y', strtotime($data['deposito_account_date']));

				for ($i=1; $i<= $data['deposito_account_period']; $i++) { 
					$depositoprofitsharing = array ();

					$month = $month + 1;

					if($month == 13){
						$month = 01;
						$year = $year + 1;
					}

					$deposito_profit_sharing_due_date = $year.'-'.$month.'-'.$date;

					$depositoprofitsharing = array (
						'deposito_account_id'				=> $deposito_account_id,
						'deposito_id'						=> $data['deposito_id'],
						'deposito_account_nisbah'			=> $data['deposito_account_nisbah'],
						'member_id'							=> $data['member_id'],
						'deposito_profit_sharing_due_date'	=> $deposito_profit_sharing_due_date,
						'deposito_daily_average_balance'	=> $data['deposito_account_amount'],
						'deposito_account_last_balance'		=> $data['deposito_account_amount'],
						'savings_account_id'				=> $data['savings_account_id'],
						'deposito_profit_sharing_token'		=> $data['deposito_account_token'].$i,
					);

					$deposito_profit_sharing_token 			= $this->AcctDepositoAccount->getDepositoProfitSharingToken($depositoprofitsharing['deposito_profit_sharing_token']);

					if($deposito_profit_sharing_token->num_rows()==0){
						$this->AcctDepositoAccount_model->insertAcctDepositoProfitSharing($depositoprofitsharing);
					}

				}

				$transaction_module_code = "DEP";

				$transaction_module_id 		= $this->AcctDepositoAccount_model->getTransactionModuleID($transaction_module_code);
				$acctdepositoaccount_last 	= $this->AcctDepositoAccount_model->getAcctDepositoAccount_Last($data['created_on']);

				$journal_voucher_period = date("Ym", strtotime($data['deposito_account_date']));
				
				$data_journal = array(
					'branch_id'						=> $auth['branch_id'],
					'journal_voucher_period' 		=> $journal_voucher_period,
					'journal_voucher_date'			=> date('Y-m-d'),
					'journal_voucher_title'			=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
					'journal_voucher_description'	=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
					'transaction_module_id'			=> $transaction_module_id,
					'transaction_module_code'		=> $transaction_module_code,
					'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
					'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_no'],
					'journal_voucher_token' 		=> $data['deposito_account_token'],
					'created_id' 					=> $data['created_id'],
					'created_on' 					=> $data['created_on'],
				);

				$journal_voucher_token 					= $this->AcctDepositoAccount->getAcctJournalVoucherToken($data_journal['journal_voucher_token']);

				if($journal_voucher_token->num_rows()==0){				
					$this->AcctDepositoAccount_model->insertAcctJournalVoucher($data_journal);
				}

				$journal_voucher_id = $this->AcctDepositoAccount_model->getJournalVoucherID($data['created_id']);

				$preferencecompany = $this->AcctDepositoAccount_model->getPreferenceCompany();

				$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

					$data_debet = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_cash_id'],
						'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
						'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
						'journal_voucher_debit_amount'	=> ABS($data['deposito_account_amount']),
						'account_id_default_status'		=> $account_id_default_status,
						'journal_voucher_item_token' 	=> $data['deposito_account_token'].$preferencecompany['account_cash_id'],
						'account_id_status'				=> 0,
						'created_id' 					=> $auth['user_id'],
					);

					$journal_voucher_item_token 		= $this->AcctDepositoAccount->getAcctJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){	
						$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_debet);
					}

					$account_id = $this->AcctDepositoAccount_model->getAccountID($data['deposito_id']);

					$account_id_default_status = $this->AcctDepositoAccount_model->getAccountIDDefaultStatus($account_id);

					$data_credit =array(
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $account_id,
						'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
						'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
						'journal_voucher_credit_amount'	=> ABS($data['deposito_account_amount']),
						'journal_voucher_item_token' 	=> $data['deposito_account_token'].$account_id,
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'created_id' 					=> $auth['user_id'],
					);

					$journal_voucher_item_token 		= $this->AcctDepositoAccount->getAcctJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){	
						$this->AcctDepositoAccount_model->insertAcctJournalVoucherItem($data_credit);
					}
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>  
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Tambah Data Simpanan Berjangka Sukses
						</div> ";
				$sesi = $this->session->userdata('unique');
				$this->session->unset_userdata('addacctdepositoaccount-'.$sesi['unique']);
				$this->session->unset_userdata('acctdepositoaccounttoken-'.$sesi['unique']);
				$this->session->set_userdata('message',$msg);
				redirect('deposito-account');
			}
		}

		public function exportMasterDataAcctDepositoAccount(){	

			$acctdepositoaccount	= $this->AcctDepositoAccount_model->getExport();
			
			if($acctdepositoaccount->num_rows()!=0){
				$this->load->library('Excel');
				
				$this->excel->getProperties()->setCreator("SIS")
									 ->setLastModifiedBy("SIS")
									 ->setTitle("Master Data Simpanan Berjangka")
									 ->setSubject("")
									 ->setDescription("Master Data Simpanan Berjangka")
									 ->setKeywords("Master, Data, Simpanan, Berjangka")
									 ->setCategory("Master Data Simpanan Berjangka");
									 
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);			
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);			

				$this->excel->getActiveSheet()->mergeCells("B1:K1");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B3:K3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B3:K3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3:K3')->getFont()->setBold(true);	
				$this->excel->getActiveSheet()->setCellValue('B1',"Master Data Simpanan Berjangka");	
				
				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"Nama Anggota");
				$this->excel->getActiveSheet()->setCellValue('D3',"Jenis Simp Berjangka");
				$this->excel->getActiveSheet()->setCellValue('E3',"Jenis Perpanjangan");
				$this->excel->getActiveSheet()->setCellValue('F3',"No. SimKa");
				$this->excel->getActiveSheet()->setCellValue('G3',"No. seri");
				$this->excel->getActiveSheet()->setCellValue('H3',"Tgl Buka");
				$this->excel->getActiveSheet()->setCellValue('I3',"JT Tempo");
				$this->excel->getActiveSheet()->setCellValue('J3',"Nominal");
				$this->excel->getActiveSheet()->setCellValue('K3',"Bagi Hasil");
				
				$j=4;
				$no=0;
				
				foreach($acctdepositoaccount->result_array() as $key=>$val){
					if(is_numeric($key)){
						if($val['deposito_account_extra_type'] == '1'){
							$type_extra = 'ARO';
						}else{
							$type_extra = 'Manual';
						}
						$no++;
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->getStyle('B'.$j.':K'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$this->excel->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

						$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
						$this->excel->getActiveSheet()->setCellValue('C'.$j, $val['member_name']);
						$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['deposito_name']);
						$this->excel->getActiveSheet()->setCellValue('E'.$j, $type_extra);
						$this->excel->getActiveSheet()->setCellValueExplicit('F'.$j, $val['deposito_account_no']);
						$this->excel->getActiveSheet()->setCellValue('G'.$j, $val['deposito_account_serial_no']);
						$this->excel->getActiveSheet()->setCellValue('H'.$j, tgltoview($val['deposito_account_date']));
						$this->excel->getActiveSheet()->setCellValue('I'.$j, tgltoview($val['deposito_account_due_date']));	
						$this->excel->getActiveSheet()->setCellValue('J'.$j, number_format($val['deposito_account_amount'], 2));
						$this->excel->getActiveSheet()->setCellValue('K'.$j, $val['deposito_account_nisbah']);				
						
					}else{
						continue;
					}
					$j++;
				}
				$filename='Master Data Simpanan Berjangka.xls';
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="'.$filename.'"');
				header('Cache-Control: max-age=0');
							 
				$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
				if (ob_get_length() > 0){
					ob_end_clean();
				}
				$objWriter->save('php://output');
			}else{
				echo "Maaf data yang di eksport tidak ada !";
			}
		}

		public function function_state_add(){
			$unique 	= $this->session->userdata('unique');
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctdepositoaccount-'.$unique['unique']);
			$sessions['active_tab'] = $value;
			$this->session->set_userdata('addacctdepositoaccount-'.$unique['unique'],$sessions);
		}	
		
		public function generateAcctDepositoProfitSharing(){
			$acctdeposito = $this->AcctDepositoAccount_model->getAcctDepositoAccountAll();

			foreach($acctdeposito as $key => $val){
				
				$date 	= date('d', strtotime($val['deposito_account_date']));
				$month 	= date('m', strtotime($val['deposito_account_date']));
				$year 	= date('Y', strtotime($val['deposito_account_date']));

				for ($i=1; $i<= 100; $i++) { 
					$depositoprofitsharing = array ();

					$month = $month + 1;

					if($month == 13){
						$month = 01;
						$year = $year + 1;
					}

					$deposito_profit_sharing_due_date = $year.'-'.$month.'-'.$date;
					$date_before 	= new DateTime($deposito_profit_sharing_due_date);
					$status 		= 0;
					$deposito_profit_sharing_period = 0;
					$deposito_profit_sharing_date 	= null;
					$date_now = date('Y-m-d');
					$date_now = date('Y-m-d', strtotime($date_now . ' -11 day'));
					$date_now = new DateTime($date_now);

					if($date_before <= $date_now){
						$status = 1;
						$deposito_profit_sharing_period = $month.$year;
						$deposito_profit_sharing_date = $deposito_profit_sharing_due_date;
					}

					$depositoprofitsharing = array (
						'deposito_account_id'				=> $val['deposito_account_id'],
						'branch_id'							=> 2,
						'deposito_id'						=> $val['deposito_id'],
						'deposito_account_nisbah'			=> $val['deposito_account_nisbah'],
						'member_id'							=> $val['member_id'],
						'deposito_profit_sharing_due_date'	=> $deposito_profit_sharing_due_date,
						'deposito_daily_average_balance'	=> $val['deposito_account_amount'],
						'deposito_account_last_balance'		=> $val['deposito_account_amount'],
						'savings_account_id'				=> $val['savings_account_id'],
						'created_id'						=> 37,
						'created_on'						=> $val['created_on'],
						'deposito_profit_sharing_status'	=> $status,
						'deposito_profit_sharing_period'	=> $deposito_profit_sharing_period,
						'deposito_profit_sharing_date'		=> $deposito_profit_sharing_date,
					);

					$this->AcctDepositoAccount_model->insertAcctDepositoProfitSharing($depositoprofitsharing);

				}
			}
		}
	}
?>
<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctDebt extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('CoreMember_model');
			$this->load->model('AcctDebt_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$auth 		= $this->session->userdata('auth');
			$unique 	= $this->session->userdata('unique');
			$sesi		= $this->session->userdata('filter-acctdebt');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');				
			}
			$this->session->set_userdata('filter-acctdebt', $sesi);

			$this->session->unset_userdata('addAcctDebt-'.$unique['unique']);	
			$this->session->unset_userdata('acctdebttoken-'.$unique['unique']);
			$this->session->unset_userdata('acctdebttokenedit-'.$unique['unique']);

			$data['main_view']['acctdebt']				= $this->AcctDebt_model->getAcctDebt();
			// echo json_encode($data);
			// exit;
			$data['main_view']['content']				= 'AcctDebt/ListAcctDebt_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				"start_date" 	=> $this->input->post('start_date',true),
				"end_date" 		=> $this->input->post('end_date',true),
			);

			$this->session->set_userdata('filter-acctdebt',$data);
			redirect('debt');
		}

		public function reset_list(){
			$this->session->unset_userdata('filter-acctdebt');
			redirect('debt');
		}
		
		// public function getAcctDebtList(){
		// 	$auth 	= $this->session->userdata('auth');
		// 	$sesi	= $this->session->userdata('filter-acctdebt');
		// 	if(!is_array($sesi)){
		// 		$sesi['start_date']		= date('Y-m-d');
		// 		$sesi['end_date']		= date('Y-m-d');
		// 	}

		// 	$list = $this->AcctDebt_model->get_datatables_master($sesi['start_date'], $sesi['end_date']);

	    //     $data = array();
	    //     $no = $_POST['start'];
	    //     foreach ($list as $debt) {
	    //         $no++;
	    //         $row = array();
	    //         $row[] = $no;
	    //         $row[] = $debt->debt_no;
	    //         $row[] = $this->AcctDebt_model->getAcctDebtCategoryName($debt->debt_category_id);
	    //         $row[] = $this->AcctDebt_model->getCoreMemberNo($debt->member_id);
	    //         $row[] = $this->AcctDebt_model->getCoreMemberName($debt->member_id);
	    //         $row[] = date('d-m-Y', strtotime($debt->debt_date));
	    //         $row[] = number_format($debt->debt_amount, 2);
	    //         $row[] = $debt->debt_remark;
		// 		$row[] = '<a href="'.base_url().'debt/delete/'.$debt->debt_id.'" class="btn btn-xs red" role="button"><i class="fa fa-trash"></i> Hapus</a>';
	            
	    //         $data[] = $row;
	    //     }

	    //     $output = array(
		// 		"draw" => $_POST['draw'],
		// 		"recordsTotal" => $this->AcctDebt_model->count_all_master($sesi['start_date'], $sesi['end_date']),
		// 		"recordsFiltered" => $this->AcctDebt_model->count_filtered_master($sesi['start_date'], $sesi['end_date']),
		// 		"data" => $data,
		// 	);

	    //     echo json_encode($output);
		// }

		public function detailAcctDebt(){
			$auth 		= $this->session->userdata('auth');
			$debt_id 	= $this->uri->segment(3);

			$data['main_view']['debtdetail']		= $this->AcctDebt_model->getAcctDebt_Detail($debt_id);
			$data['main_view']['debtitem']			= $this->AcctDebt_model->getAcctDebtItem($debt_id);
			$data['main_view']['content']			= 'AcctDebt/DetailAcctDebt_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function addAcctDebt(){
			$auth 		= $this->session->userdata('auth');

			$data['main_view']['coremember']		= $this->AcctDebt_model->getCoreMemberDetail($this->uri->segment(3));
			$data['main_view']['acctdebtcategory']	= create_double($this->AcctDebt_model->getAcctDebtCategory(),'debt_category_id', 'debt_category_name');
			$data['main_view']['content']			= 'AcctDebt/FormAddAcctDebt_view';
			$this->load->view('MainPage_view',$data);
		}

		public function getListCoreMember(){
			$auth = $this->session->userdata('auth');

			$list = $this->CoreMember_model->get_datatables_status($auth['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $customers) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $customers->member_no;
	            $row[] = $customers->member_name;
	            $row[] = $customers->member_address;
	            $row[] = '<a href="'.base_url().'debt/add/'.$customers->member_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	            $data[] = $row;
	        }
	 
	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->CoreMember_model->count_all($auth['branch_id']),
	                        "recordsFiltered" => $this->CoreMember_model->count_filtered($auth['branch_id']),
	                        "data" => $data,
	                );
	        echo json_encode($output);
		}
		
		public function processAddAcctDebt(){
			$auth 		= $this->session->userdata('auth');
			
			$preferencecompany 		= $this->AcctDebt_model->getPreferenceCompany();
			$total 					= 0;

			$data = array (
				'member_id' 		=> $this->input->post('member_id' ,true),
				'debt_category_id' 	=> $this->input->post('debt_category_id' ,true),
				'debt_date' 		=> tgltodb($this->input->post('debt_date' ,true)),
				'debt_amount' 		=> $this->input->post('debt_amount' ,true),
				'debt_remark' 		=> $this->input->post('debt_remark' ,true),
				'debt_token' 		=> $this->input->post('debt_token' ,true),
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_id'		=> $auth['user_id']
			);

			
			$this->form_validation->set_rules('member_id', 'No Anggota', 'required');
			$this->form_validation->set_rules('debt_category_id', 'Kategori', 'required');
			$this->form_validation->set_rules('debt_date', 'Tanggal', 'required');
			$this->form_validation->set_rules('debt_amount', 'Jumlah', 'required');
			
			if($this->form_validation->run()==true){
				if($this->AcctDebt_model->insertAcctDebt($data)){
					$coremember 	= $this->AcctDebt_model->getCoreMemberDetail($data['member_id']);
					$debtcategory 	= $this->AcctDebt_model->getAcctDebtCategoryDetail($data['debt_category_id']); 
					$other_amount	= $coremember['member_account_other_debt'] + $data['debt_amount'];
					$total			= $coremember['member_account_receivable_amount'] + $data['debt_amount'];

					$dataanggota 	= array(
						'member_id' 						=> $data['member_id'],
						'member_account_other_debt' 		=> $other_amount,
						'member_account_receivable_amount'  => $total
					);

					$this->AcctDebt_model->updateCoreMemberAccountReceivableAmount($dataanggota);

					$journal_voucher_period 		= date("Ym", strtotime($data['debt_date']));
					$transaction_module_code 		= "PG";
					$transaction_module_id 			= $this->AcctDebt_model->getTransactionModuleID($transaction_module_code);
					
					$data_journal = array(
						'branch_id'							=> $auth['branch_id'],
						'journal_voucher_period' 			=> $journal_voucher_period,
						'journal_voucher_date'				=> date('Y-m-d'),
						'journal_voucher_title'				=> 'POTONG GAJI BARU',
						'journal_voucher_description'		=> 'POTONG GAJI BARU',
						'journal_voucher_token'				=> md5(rand()),
						'transaction_module_id'				=> $transaction_module_id,
						'transaction_module_code'			=> $transaction_module_code,
						'transaction_journal_id' 			=> $debt_id,
						'transaction_journal_no' 			=> $debt_no,
						'created_id' 						=> $data['created_id'],
						'created_on' 						=> date('Y-m-d'),
					);
					
					if($this->AcctDebt_model->insertAcctJournalVoucher($data_journal)){
						$journal_voucher_id 				= $this->AcctDebt_model->getJournalVoucherID($data['created_id']);
						
						$account_id_default_status 			= $this->AcctDebt_model->getAccountIDDefaultStatus($debtcategory['debet_account_id']);

						$data_debet = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $debtcategory['debet_account_id'],
							'journal_voucher_description'	=> 'POTONG GAJI BARU',
							'journal_voucher_amount'		=> $data['debt_amount'],
							'journal_voucher_debit_amount'	=> $data['debt_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$debtcategory['debet_account_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctDebt_model->insertAcctJournalVoucherItem($data_debet);

						$account_id_default_status 			= $this->AcctDebt_model->getAccountIDDefaultStatus($debtcategory['credit_account_id']);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $debtcategory['credit_account_id'],
							'journal_voucher_description'	=> 'POTONG GAJI BARU',
							'journal_voucher_amount'		=> $data['debt_amount'],
							'journal_voucher_credit_amount'	=> $data['debt_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$debtcategory['credit_account_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctDebt_model->insertAcctJournalVoucherItem($data_credit);
					}

					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Potong Gaji Sukses
							</div> ";
					$this->session->unset_userdata('addAcctDebt');
					$this->session->set_userdata('message',$msg);
					redirect('debt');
				}else{
					$this->session->set_userdata('addAcctDebt',$data);
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Potong Gaji Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('debt');
				}
			}else{
				$this->session->set_userdata('addAcctDebt',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('debt/add');
			}
		}

		public function deleteAcctDebt($debt_id){
			$acctdebt 		= $this->AcctDebt_model->getAcctDebt_Detail($debt_id);

			if($this->AcctDebt_model->deleteAcctDebt($acctdebt['debt_id'])){
				$coremember 	= $this->AcctDebt_model->getCoreMemberDetail($acctdebt['member_id']);
				$other_amount	= $coremember['member_account_other_debt'] - $acctdebt['debt_amount'];
				$total			= $coremember['member_account_receivable_amount'] - $acctdebt['debt_amount'];

				$dataanggota 	= array(
					'member_id' 						=> $acctdebt['member_id'],
					'member_account_other_debt' 		=> $other_amount,
					'member_account_receivable_amount'  => $total
				);
				$this->AcctDebt_model->updateCoreMemberAccountReceivableAmount($dataanggota);

				$journal_voucher_period 		= date("Ym", strtotime($data['debt_date']));
				$transaction_module_code 		= "JBPG";
				$transaction_module_id 			= $this->AcctDebt_model->getTransactionModuleID($transaction_module_code);
				
				$data_journal = array(
					'branch_id'							=> $auth['branch_id'],
					'journal_voucher_period' 			=> $journal_voucher_period,
					'journal_voucher_date'				=> date('Y-m-d'),
					'journal_voucher_title'				=> 'JURNAL BALIK POTONG GAJI',
					'journal_voucher_description'		=> 'JURNAL BALIK POTONG GAJI',
					'journal_voucher_token'				=> md5(rand()),
					'transaction_module_id'				=> $transaction_module_id,
					'transaction_module_code'			=> $transaction_module_code,
					'transaction_journal_id' 			=> $debt_id,
					'transaction_journal_no' 			=> $debt_no,
					'created_id' 						=> $data['created_id'],
					'created_on' 						=> date('Y-m-d'),
				);
				
				if($this->AcctDebt_model->insertAcctJournalVoucher($data_journal)){
					$journal_voucher_id 				= $this->AcctDebt_model->getJournalVoucherID($data['created_id']);
					
					$account_id_default_status 			= $this->AcctDebt_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

					$data_debet = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_cash_id'],
						'journal_voucher_description'	=> 'JURNAL BALIK POTONG GAJI',
						'journal_voucher_amount'		=> $data['debt_amount'],
						'journal_voucher_debit_amount'	=> $data['debt_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 0,
						'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_cash_id'],
						'created_id' 					=> $auth['user_id']
					);
					$this->AcctDebt_model->insertAcctJournalVoucherItem($data_debet);

					$account_id_default_status 			= $this->AcctDebt_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

					$data_credit = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_salary_payment_id'],
						'journal_voucher_description'	=> 'JURNAL BALIK POTONG GAJI',
						'journal_voucher_amount'		=> $data['debt_amount'],
						'journal_voucher_credit_amount'	=> $data['debt_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
						'created_id' 					=> $auth['user_id']
					);
					$this->AcctDebt_model->insertAcctJournalVoucherItem($data_credit);
				}

				$msg = "<div class='alert alert-success alert-dismissable'>  
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Hapus Potong Gaji Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('debt');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Hapus Potong Gaji Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('debt');
			}
		}
		
		public function importAcctDebt(){
			$auth 		= $this->session->userdata('auth');

			$data['main_view']['acctdebtcategory']	= create_double($this->AcctDebt_model->getAcctDebtCategory(),'debt_category_id', 'debt_category_name');
			$data['main_view']['acctdebttemp']		= $this->AcctDebt_model->getAcctDebtTemp();
			$data['main_view']['content']			= 'AcctDebt/FormImportAcctDebt_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function processImportAcctDebtTemp(){
			$auth 		= $this->session->userdata('auth');

			$this->AcctDebt_model->truncateAcctDebtTemp();

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
				redirect('debt/import');
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
					$rowData 			= $sheet->rangeToArray('A'.$row.':'.$highestColumn.$row, NULL, TRUE, FALSE);

					$member_id 			= $this->AcctDebt_model->getCoreMemberID($rowData[0][0]);
					$debt_category_id 	= $rowData[0][1];

					$data	= array (
						'member_id'				=> $member_id,
						'debt_category_id'		=> $debt_category_id,
						'debt_temp_date'		=> date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($rowData[0][2])),
						'debt_temp_amount'		=> $rowData[0][3],
						'debt_temp_remark'		=> $rowData[0][4],
					);

					if($data['member_id'] != ''){
						$this->AcctDebt_model->insertAcctDebtTemp($data);
					}
				}
				unlink($inputFileName);
				$msg = "<div class='alert alert-success'>
							Import Data Excel
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('debt/import');
			}
		}

		public function processImportAcctDebt(){
			$auth 				= $this->session->userdata('auth');
			$preferencecompany 	= $this->AcctDebt_model->getPreferenceCompany();
			$acctdebttemp 		= $this->AcctDebt_model->getAcctDebtTemp();

			foreach($acctdebttemp as $key => $val){
				$total 					= 0;
	
				$data = array (
					'member_id' 		=> $val['member_id'],
					'debt_category_id' 	=> $this->input->post('debt_category_id_'.$val['debt_temp_id'] ,true),
					'debt_date' 		=> tgltodb($val['debt_temp_date']),
					'debt_amount' 		=> $val['debt_temp_amount'],
					'debt_remark' 		=> $val['debt_temp_remark'],
					'debt_token' 		=> md5(rand()).$val['debt_temp_id'],
					'created_on'		=> date('Y-m-d H:i:s'),
					'created_id'		=> $auth['user_id']
				);

				if($this->AcctDebt_model->insertAcctDebt($data)){
					$coremember 	= $this->AcctDebt_model->getCoreMemberDetail($data['member_id']);
					$debtcategory 	= $this->AcctDebt_model->getAcctDebtCategoryDetail($data['debt_category_id']); 
					$other_amount	= $coremember['member_account_other_debt'] + $data['debt_amount'];
					$total			= $coremember['member_account_receivable_amount'] + $data['debt_amount'];

					$dataanggota 	= array(
						'member_id' 						=> $data['member_id'],
						'member_account_other_debt' 		=> $other_amount,
						'member_account_receivable_amount'  => $total
					);

					$this->AcctDebt_model->updateCoreMemberAccountReceivableAmount($dataanggota);

					$journal_voucher_period 		= date("Ym", strtotime($data['debt_date']));
					$transaction_module_code 		= "PG";
					$transaction_module_id 			= $this->AcctDebt_model->getTransactionModuleID($transaction_module_code);
					
					$data_journal = array(
						'branch_id'							=> $auth['branch_id'],
						'journal_voucher_period' 			=> $journal_voucher_period,
						'journal_voucher_date'				=> date('Y-m-d'),
						'journal_voucher_title'				=> 'POTONG GAJI BARU',
						'journal_voucher_description'		=> 'POTONG GAJI BARU',
						'journal_voucher_token'				=> md5(rand()).$val['debt_temp_id'],
						'transaction_module_id'				=> $transaction_module_id,
						'transaction_module_code'			=> $transaction_module_code,
						'transaction_journal_id' 			=> $debt_id,
						'transaction_journal_no' 			=> $debt_no,
						'created_id' 						=> $data['created_id'],
						'created_on' 						=> date('Y-m-d'),
					);
					
					if($this->AcctDebt_model->insertAcctJournalVoucher($data_journal)){
						$journal_voucher_id 				= $this->AcctDebt_model->getJournalVoucherID($data['created_id']);
						
						$account_id_default_status 			= $this->AcctDebt_model->getAccountIDDefaultStatus($debtcategory['debet_account_id']);

						$data_debet = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $debtcategory['debet_account_id'],
							'journal_voucher_description'	=> 'POTONG GAJI BARU',
							'journal_voucher_amount'		=> $data['debt_amount'],
							'journal_voucher_debit_amount'	=> $data['debt_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$debtcategory['debet_account_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctDebt_model->insertAcctJournalVoucherItem($data_debet);

						$account_id_default_status 			= $this->AcctDebt_model->getAccountIDDefaultStatus($debtcategory['credit_account_id']);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $debtcategory['credit_account_id'],
							'journal_voucher_description'	=> 'POTONG GAJI BARU',
							'journal_voucher_amount'		=> $data['debt_amount'],
							'journal_voucher_credit_amount'	=> $data['debt_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$debtcategory['credit_account_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctDebt_model->insertAcctJournalVoucherItem($data_credit);
					}
				}
			}

			$this->AcctDebt_model->truncateAcctDebtTemp();

			$msg = "<div class='alert alert-success alert-dismissable'>  
					<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
						Import Potong Gaji Baru Sukses
					</div> ";
			$this->session->unset_userdata('addAcctDebt');
			$this->session->set_userdata('message',$msg);
			redirect('debt');
		}
	}
?>
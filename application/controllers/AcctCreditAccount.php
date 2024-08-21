 <?php
	defined('BASEPATH') or exit('No direct script access allowed');
	define('FINANCIAL_MAX_ITERATIONS', 128);
	define('FINANCIAL_PRECISION', 1.0e-08);
	Class AcctCreditAccount extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('CoreMember_model');
			$this->load->model('Core_account_Officer_model');
			$this->load->model('Core_source_fund_model');
			$this->load->model('AcctDepositoAccount_model');
			$this->load->model('AcctCredit_model');
			$this->load->model('SalesInvoice_model');
			$this->load->model('AcctCreditAccount_model');
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
			$this->session->unset_userdata('acctcreditsaccounttoken-'.$unique['unique']);
			$this->session->unset_userdata('addarrayacctcreditsagunan-'.$sesi['unique']);

			
			$this->AcctCreditAccount_model->truncateAcctCreditsImport();

			$data['main_view']['acctcredits']	= create_double($this->AcctCreditAccount_model->getAcctCredits(),'credits_id', 'credits_name');
			$data['main_view']['corebranch']	= create_double($this->AcctCreditAccount_model->getCoreBranch(),'branch_id', 'branch_name');
			$data['main_view']['content']		= 'AcctCreditAccount/ListAcctCreditsAccount_view';
			$this->load->view('MainPage_view', $data);
		}

		public function filteracctcreditsaccount(){
			$data = array (
				'start_date'	=> tgltodb($this->input->post('start_date', true)),
				'end_date'		=> tgltodb($this->input->post('end_date', true)),
				'credits_id'	=> $this->input->post('credits_id', true),
				'branch_id'		=> $this->input->post('branch_id', true),
			);

			$this->session->set_userdata('filter-acctcreditsaccountlist', $data);
			redirect('credit-account');
		}

		public function reset(){
			$this->session->unset_userdata('filter-acctcreditsaccountlist');
			redirect('credit-account');
		}

		public function getAcctCreditsAccountList(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctcreditsaccountlist');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['credits_id']		='';
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}
			} else {
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}
			}

			$creditsapprovestatus 	= $this->configuration->CreditsApproveStatus();
			$creditsaccountstatus 	= $this->configuration->CreditsAccountStatus();
			$creditsaccountpayment	= $this->configuration->PaymentType();

			$list 	= $this->AcctCreditAccount_model->get_datatables_master($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']);
	        $data 	= array();
	        $no 	= $_POST['start'];

	        foreach ($list as $creditsaccount) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $creditsaccount->credits_account_serial;
	            $row[] = $creditsaccount->member_no;
	            $row[] = $creditsaccount->member_name;
	            $row[] = $creditsaccount->credits_name;
	            $row[] = $creditsaccountpayment[$creditsaccount->payment_type_id];
	            $row[] = $creditsaccount->source_fund_name;
	            $row[] = tgltoview($creditsaccount->credits_account_date);
	            $row[] = number_format($creditsaccount->credits_account_amount, 2);
	            $row[] = $creditsaccountstatus[$creditsaccount->credits_account_status];

				if($creditsaccount->credits_approve_status == 0){
					$row[] = '			    		
						<a href="'.base_url().'credit-account/approving/'.$creditsaccount->credits_account_id.'" class="btn btn-xs purple" role="button"><i class="fa fa-check"></i> Proses</a>
						<a href="'.base_url().'credit-account/reject/'.$creditsaccount->credits_account_id.'" class="btn btn-xs red" onClick="javascript:return confirm(\'Apakah Anda yakin akan pembatalkan perjanjian kredit ini ?\')"  role="button"><i class="fa fa-times"></i> Reject</a>';
				}else{
					$row[] = $creditsapprovestatus[$creditsaccount->credits_approve_status];
				}

				if($creditsaccount->credits_approve_status == 0){
					$row[] = '
						<a href="'.base_url().'credit-account/print-note/'.$creditsaccount->credits_account_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Kwitansi</a> &nbsp;
						<a href="'.base_url().'credit-account/process-print-akad/'.$creditsaccount->credits_account_id.'" class="btn btn-xs green" role="button"><i class="fa fa-print"></i> Akad</a>
						<a href="'.base_url().'credit-account/edit-date/'.$creditsaccount->credits_account_id.'" class="btn btn-xs green-jungle" role="button"><i class="fa fa-calendar"></i> Edit Tanggal & Asuransi</a>
						<a href="'.base_url().'credit-account/edit-payment-pref/'.$creditsaccount->credits_account_id.'" class="btn btn-xs purple" role="button"><i class="fa fa-money"></i> Edit Preferensi Angsuran</a>
						<a href="'.base_url().'credit-account/edit/'.$creditsaccount->credits_account_id.'" class="btn btn-xs green" role="button"><i class="fa fa-pencil"></i> Edit Pinjaman</a>
						<a href="'.base_url().'credit-account/print-schedule-credits-payment/'.$creditsaccount->credits_account_id.'" class="btn btn-xs yellow-lemon" role="button"><i class="fa fa-print"></i> Jadwal Angsuran</a>';
				}else if($creditsaccount->credits_approve_status == 1){
					$row[] = '
						<a href="'.base_url().'credit-account/print-note/'.$creditsaccount->credits_account_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Kwitansi</a> &nbsp;
						<a href="'.base_url().'credit-account/process-print-akad/'.$creditsaccount->credits_account_id.'" class="btn btn-xs green" role="button"><i class="fa fa-print"></i> Akad</a>
						<a href="'.base_url().'credit-account/edit-payment-pref/'.$creditsaccount->credits_account_id.'" class="btn btn-xs purple" role="button"><i class="fa fa-money"></i> Edit Preferensi Angsuran</a>
						<a href="'.base_url().'credit-account/edit/'.$creditsaccount->credits_account_id.'" class="btn btn-xs green" role="button"><i class="fa fa-pencil"></i> Edit Pinjaman</a>
						<a href="'.base_url().'credit-account/print-schedule-credits-payment/'.$creditsaccount->credits_account_id.'" class="btn btn-xs yellow-lemon" role="button"><i class="fa fa-print"></i> Jadwal Angsuran</a>';
				}else{
					$row[] = '
						<a href="'.base_url().'credit-account/print-note/'.$creditsaccount->credits_account_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Kwitansi</a> &nbsp;
						<a href="'.base_url().'credit-account/process-print-akad/'.$creditsaccount->credits_account_id.'" class="btn btn-xs green" role="button"><i class="fa fa-print"></i> Akad</a>
						<a href="'.base_url().'credit-account/print-schedule-credits-payment/'.$creditsaccount->credits_account_id.'" class="btn btn-xs yellow-lemon" role="button"><i class="fa fa-print"></i> Jadwal Angsuran</a>
						<a href="'.base_url().'credit-account/delete/'.$creditsaccount->credits_account_id.'" class="btn btn-xs red" role="button"><i class="fa fa-trash"></i> Hapus</a>';
				}

	            $data[] = $row;
	        }

	        $output = array(
				"draw" 				=> $_POST['draw'],
				"recordsTotal" 		=> $this->AcctCreditAccount_model->count_all_master($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']),
				"recordsFiltered" 	=> $this->AcctCreditAccount_model->count_filtered_master($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']),
				"data" 				=> $data,
			);
	        echo json_encode($output);
		}

		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addcreditaccount-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addcreditaccount-'.$unique['unique'],$sessions);
		}

		public function reset_data(){
			$unique 	= $this->session->userdata('unique');
			$sessions	= $this->session->unset_userdata('addcreditaccount-'.$unique['unique']);
			$this->session->unset_userdata('addarrayacctcreditsagunan-'.$unique['unique']);
			redirect('credit-account/add-form');
		}

		public function addform(){
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
			$data['main_view']['content']					= 'AcctCreditAccount/FormAddAcctCreditAccount_view';
			$this->load->view('MainPage_view',$data);
		}
		public function addformsales(){
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
			$data['main_view']['salesinvoice']				= $this->SalesInvoice_model->getSalesMember($this->uri->segment(3));
			$data['main_view']['memberacctcreditsaccount']	= $this->AcctCreditAccount_model->getMemberAcctCreditsAccount($this->uri->segment(3));
			// print_r($data['main_view']['salesinvoice']);
			// return 0;
			$data['main_view']['content']					= 'AcctCreditAccount/FormAddAcctCreditAccountSales_view';
			$this->load->view('MainPage_view',$data);
		}

		public function addmultiple(){
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
			$data['main_view']['paymenttype']				= $this->configuration->PaymentType();
			$data['main_view']['paymentpreference']			= $this->configuration->PaymentPreference();
			$data['main_view']['coreoffice']				= create_double($this->AcctCreditAccount_model->getCoreOffice(),'office_id', 'office_name');
			$data['main_view']['sumberdana']				= create_double($this->Core_source_fund_model->getData(),'source_fund_id', 'source_fund_name');
			$data['main_view']['acctsavingsaccount']		= create_double($this->AcctDepositoAccount_model->getAcctSavingsAccount($auth['branch_id']),'savings_account_id', 'savings_account_no');
			$data['main_view']['acctbankaccount']			= create_double($this->AcctCreditAccount_model->getBankAccount(),'bank_account_id', 'bank_account_name');
			$data['main_view']['creditid']					= create_double($this->AcctCreditAccount_model->getAcctCredits(),'credits_id', 'credits_name');
			$data['main_view']['coremember']				= $this->AcctCreditAccount_model->getAcctCreditImport();
			$data['main_view']['memberacctcreditsaccount']	= $this->AcctCreditAccount_model->getMemberAcctCreditsAccount($this->uri->segment(3));
			$data['main_view']['content']					= 'AcctCreditAccount/FormAddAcctCreditAccountMultiple_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function addArrayAcctCreditsImport(){
			$auth 		= $this->session->userdata('auth');

			$this->AcctCreditAccount_model->truncateAcctCreditsImport();

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
				redirect('credit-account/add-multiple');
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

					$member_id 	= $this->AcctCreditAccount_model->getCoreMemberID($rowData[0][0]);

					$data	= array (
						'member_id'	=> $member_id,
					);

					if($data['member_id'] != ''){
						$this->AcctCreditAccount_model->insertAcctCreditsImport($data);
					}
				}
				unlink($inputFileName);
				$msg = "<div class='alert alert-success'>                
							Import Data Excel
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('credit-account/add-multiple');
			}
		}

		public function editDateAcctCreditAccount(){
			$auth 				= $this->session->userdata('auth');
			$unique 			= $this->session->userdata('unique');
			$credits_account_id	= $this->uri->segment(3);

			$data['main_view']['acctcreditsaccount']	= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$data['main_view']['content']				= 'AcctCreditAccount/FormEditDateAcctCreditsAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processEditDateAcctCreditAccount(){
			$credits_account_amount_received_old = $this->input->post('credits_account_amount_received', true);
			$credits_account_insurance_old 		 = $this->input->post('credits_account_insurance_old', true);
			$credits_account_insurance 			 = $this->input->post('credits_account_insurance', true);
			$credits_account_amount_received 	 = $credits_account_amount_received_old + $credits_account_insurance_old - $credits_account_insurance;
			$daftaragunan 					     = $this->session->userdata('addarrayacctcreditsagunan-'.$sesi['unique']);

			// echo json_encode($daftaragunan);
			// exit;

			$data = array(
				'credits_account_id'				=> $this->input->post('credits_account_id', true),
				'credits_account_date' 				=> tgltodb($this->input->post('credits_account_date', true)),
				'credits_account_due_date' 			=> tgltodb($this->input->post('credits_account_due_date', true)),
				'credits_account_payment_date' 		=> tgltodb($this->input->post('credits_account_payment_date', true)),
				'credits_account_insurance' 		=> $credits_account_insurance,
				'credits_account_amount_received' 	=> $credits_account_amount_received,
			);

			if($this->AcctCreditAccount_model->updatedata($data, $data['credits_account_id'])){

				// if(!empty($daftaragunan)){
					foreach ($daftaragunan as $key => $val) {
						if($val['credits_agunan_type'] == 'Penerimaan'){
							$credits_agunan_type	= 1;
						}else if($val['credits_agunan_type'] == 'Deposito') {
							$credits_agunan_type 	= 2;
						}else if($val['credits_agunan_type'] == 'BPJS Ketenagakerjaan'){
							$credits_agunan_type 	= 3;
						}else {
							$credits_agunan_type 	= 4;
						}

						$dataagunan = array (
							'credits_account_id'						=> $data['credits_account_id'],
							'credits_agunan_type'						=> $credits_agunan_type,
							'credits_agunan_penerimaan_description'		=> $val['credits_agunan_penerimaan_description'],
							'credits_agunan_deposito_account_no'		=> $val['credits_agunan_deposito_account_no'],
							'credits_agunan_other_description'			=> $val['credits_agunan_other_description'],
						);
						$this->AcctCreditAccount_model->insertAcctCreditsAgunan($dataagunan);
					}
				// }

				$msg = "<div class='alert alert-success alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Edit Pinjaman Berhasil
						</div> ";
				$this->session->unset_userdata('addarrayacctcreditsagunan-'.$sesi['unique']);
				$this->session->set_userdata('message',$msg);
				$url='credit-account';
				redirect($url);
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Edit Pinjaman Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				$url='credit-account/edit/'.$data['credits_account_id'];
				redirect($url);
			}
		}

		public function editPaymentPreferenceAcctCreditAccount(){
			$auth 				= $this->session->userdata('auth');
			$unique 			= $this->session->userdata('unique');
			$credits_account_id	= $this->uri->segment(3);

			$data['main_view']['acctcreditsaccount']		= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$data['main_view']['paymentpreference']			= $this->configuration->PaymentPreference();
			$data['main_view']['content']					= 'AcctCreditAccount/FormEditPaymentPreferenceAcctCreditsAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processEditPaymentPreferenceAcctCreditAccount(){
			$data = array(
				'credits_account_id'	=> $this->input->post('credits_account_id', true),
				'payment_preference_id'	=> $this->input->post('payment_preference_id', true),
			);

			if($this->AcctCreditAccount_model->updatedata($data, $data['credits_account_id'])){
				$msg = "<div class='alert alert-success alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Edit Preferensi Angsuran Pinjaman Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				$url='credit-account';
				redirect($url);
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Edit Preferensi Angsuran Pinjaman Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				$url='credit-account/edit-payment-pref/'.$data['credits_account_id'];
				redirect($url);
			}
		}

		public function deleteAcctCreditAccount(){
			$credits_account_id	= $this->uri->segment(3);

			$data = array(
				'credits_account_id'		=> $credits_account_id,
				'data_state'			 	=> 1,
			);

			if($this->AcctCreditAccount_model->updatedata($data, $data['credits_account_id'])){
				$msg = "<div class='alert alert-success alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Hapus Pinjaman Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				$url = 'credit-account';
				redirect($url);
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Hapus Pinjaman Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				$url = 'credit-account';
				redirect($url);
			}
		}

		public function getCreditsAccountSerial(){
			$auth 					= $this->session->userdata('auth'); 
			$credits_id 			= $this->input->post('credits_id');
			$branchcode 			= $this->AcctCreditAccount_model->getBranchCode($auth['branch_id']);
			$credits_code 			= $this->AcctCreditAccount_model->getCreditsCode($credits_id);
			$lastcreditsaccountno 	= $this->AcctCreditAccount_model->getLastAccountCreditsNo($auth['branch_id'], $credits_id);

			if($lastcreditsaccountno->num_rows() <> 0){      
			   //jika kode ternyata sudah ada.      
			   $data = $lastcreditsaccountno->row_array();    
			   $kode = intval($data['last_credits_account_serial']) + 1;
			 } else {      
			   //jika kode belum ada      
			   $kode = 1;    
			}
			
			$kodemax 					= str_pad($kode, 5, "0", STR_PAD_LEFT);
			$new_credits_account_serial = $branchcode.$credits_code.$kodemax;

			$result = array ();
			$result = array (
				'credit_account_serial'	=> $new_credits_account_serial,
			);
			echo json_encode($result);		
		}

		public function memberlist(){
			$auth 	= $this->session->userdata('auth');
			$list 	= $this->CoreMember_model->get_datatables_status($auth['branch_id']);
			$data 	= array();
			$no 	= $_POST['start'];

			foreach ($list as $customers) {
					$no++;
					$row = array();
					$row[] = $no;
					$row[] = $customers->member_no;
					$row[] = $customers->member_name;
					$row[] = $customers->member_address;
					$row[] = '<a href="'.base_url().'credit-account/add-form/'.$customers->member_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
					$data[] = $row;
			}
	
			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->CoreMember_model->count_all_status($auth['branch_id']),
				"recordsFiltered" => $this->CoreMember_model->count_filtered_status($auth['branch_id']),
				"data" => $data,
			);
			echo json_encode($output);
		}
		public function memberlistsales(){
			$auth 	= $this->session->userdata('auth');
			$list 	= $this->CoreMember_model->get_datatables_status($auth['branch_id']);
			$data 	= array();
			$no 	= $_POST['start'];

			foreach ($list as $customers) {
					$no++;
					$row = array();
					$row[] = $no;
					$row[] = $customers->member_no;
					$row[] = $customers->member_name;
					$row[] = $customers->member_address;
					$row[] = '<a href="'.base_url().'credit-account/add-from-sales/'.$customers->member_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
					$data[] = $row;
			}
	
			$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->CoreMember_model->count_all_status($auth['branch_id']),
				"recordsFiltered" => $this->CoreMember_model->count_filtered_status($auth['branch_id']),
				"data" => $data,
			);
			echo json_encode($output);
		}

		public function processAddArrayAgunan(){
			$date = date('Ymdhis');
			$credits_agunan_type	= $this->input->post('tipe', true);

			$data_agunan = array(
				'record_id' 								=> $credits_agunan_type.$date,
				'credits_agunan_type' 						=> $this->input->post('tipe', true),
				'credits_agunan_penerimaan_description' 	=> $this->input->post('penerimaan_description', true),
				'credits_agunan_deposito_account_no' 		=> $this->input->post('deposito_account_no', true),
				'credits_agunan_other_description'			=> $this->input->post('other_description', true)
			);

			$unique 			= $this->session->userdata('unique');
			$session_name 		= $this->input->post('session_name',true);
			$dataArrayHeader	= $this->session->userdata('addarrayacctcreditsagunan-'.$unique['unique']);
			
			$dataArrayHeader[$data_agunan['record_id']] = $data_agunan;
			
			$this->session->set_userdata('addarrayacctcreditsagunan-'.$unique['unique'],$dataArrayHeader);
			
			$data_agunan['record_id'] 								= '';
			$data_agunan['credits_agunan_type'] 					= '';
			$data_agunan['credits_agunan_penerimaan_description'] 	= '';
			$data_agunan['credits_agunan_deposito_account_no'] 		= '';
			$data_agunan['credits_agunan_other_description'] 		= '';
		}

		public function addcreditaccount(){
			$auth 			= $this->session->userdata('auth');
			$sesi 			= $this->session->userdata('unique');
			$daftaragunan 	= $this->session->userdata('addarrayacctcreditsagunan-'.$sesi['unique']);

			$agunan_data 	= $this->session->userdata('agunan_data');
			$agunan 		= $this->session->userdata('agunan_key');
			$a 				= json_encode($agunan_data);

			$this->session->unset_userdata('agunan_data');
			$this->session->unset_userdata('agunan_key');

			$member_id 		= $this->input->post('member_id',true);
			if(empty($member_id)){
				$member_id 	= $this->uri->segment(3);
			}
			$data = array (
				"credits_account_date" 				=> tgltodb($this->input->post('credit_account_date',true)),
				"member_id"							=> $this->input->post('member_id',true),
				"office_id"							=> $this->input->post('office_id',true),
				"source_fund_id"					=> $this->input->post('sumberdana',true),
				"credits_id"						=> $this->input->post('credit_id',true),
				"branch_id"							=> $auth['branch_id'],
				"payment_preference_id"				=> $this->input->post('payment_preference_id',true),
				"payment_type_id"					=> $this->input->post('payment_type_id',true),
				"method_id"							=> $this->input->post('method_id',true),
				"bank_account_id"					=> $this->input->post('bank_account_id',true),
				"credits_payment_period"			=> $this->input->post('payment_period',true),
				"credits_account_period"			=> $this->input->post('credit_account_period',true),
				"credits_account_due_date"			=> tgltodb($this->input->post('credit_account_due_date',true)),
				"credits_account_amount"			=> $this->input->post('credits_account_last_balance_principal',true),
				"credits_account_interest"			=> $this->input->post('credit_account_interest',true),
				"credits_account_special"			=> $this->input->post('credit_account_special',true),
				"credits_account_adm_cost"			=> $this->input->post('credits_account_adm_cost',true),
				"credits_account_insurance"			=> $this->input->post('credits_account_insurance',true),
				"credits_account_discount"			=> $this->input->post('credits_account_discount'),
				"credits_account_remark"			=> $this->input->post('credits_account_remark',true),
				"credits_account_bank_name"			=> $this->input->post('credits_account_bank_name',true),
				"credits_account_bank_account"		=> $this->input->post('credits_account_bank_account',true),
				"credits_account_bank_owner"		=> $this->input->post('credits_account_bank_owner',true),
				"credits_account_amount_received"	=> $this->input->post('credit_account_amount_received',true),
				"credits_account_principal_amount"	=> $this->input->post('credits_account_principal_amount',true),
				"credits_account_interest_amount"	=> $this->input->post('credits_account_interest_amount',true),
				"credits_account_payment_amount"	=> $this->input->post('credit_account_payment_amount',true),
				"credits_account_last_balance"		=> $this->input->post('credits_account_last_balance_principal',true),
				"credits_account_payment_date"		=> tgltodb($this->input->post('credit_account_payment_to',true)),
				"savings_account_id"				=> $this->input->post('savings_account_id',true),
				"credits_account_token" 			=> $this->input->post('credits_account_token',true),
				"created_id"						=> $auth['user_id'],
				"created_on"						=> date('Y-m-d H:i:s'),
			);

			$this->form_validation->set_rules('credit_id', 'jenis Pinjaman', 'required');
			$this->form_validation->set_rules('credits_account_last_balance_principal', 'Pinjaman', 'required');
			$this->form_validation->set_rules('credit_account_interest', 'Bunga Per Bulan', 'required');
			$this->form_validation->set_rules('payment_type_id', 'Jenis Angsuran', 'required');
			$this->form_validation->set_rules('payment_period', 'Angsuran Tiap', 'required');
			$this->form_validation->set_rules('credit_account_period', 'Jangka Waktu', 'required');
			$this->form_validation->set_rules('office_id', 'Business Officer (BO)', 'required');
			$this->form_validation->set_rules('sumberdana', 'Sumber Dana', 'required');

			$credits_account_token 	= $this->AcctCreditAccount_model->getCreditsAccountToken($data['credits_account_token']);

			if($this->form_validation->run()==true){
				if($credits_account_token->num_rows()==0){
					if($this->AcctCreditAccount_model->insertAcctCreditAccount($data)){
						$acctcreditsaccount_last	= $this->AcctCreditAccount_model->getAcctCreditsAccount_Last($data['created_on']);
						
						if(!empty($daftaragunan)){
							foreach ($daftaragunan as $key => $val) {
								if($val['credits_agunan_type'] == 'Penerimaan'){
									$credits_agunan_type	= 1;
								}else if($val['credits_agunan_type'] == 'Deposito') {
									$credits_agunan_type 	= 2;
								}else if($val['credits_agunan_type'] == 'BPJS Ketenagakerjaan'){
									$credits_agunan_type 	= 3;
								}else {
									$credits_agunan_type 	= 4;
								}

								$dataagunan = array (
									'credits_account_id'						=> $acctcreditsaccount_last['credits_account_id'],
									'credits_agunan_type'						=> $credits_agunan_type,
									'credits_agunan_penerimaan_description'		=> $val['credits_agunan_penerimaan_description'],
									'credits_agunan_deposito_account_no'		=> $val['credits_agunan_deposito_account_no'],
									'credits_agunan_other_description'			=> $val['credits_agunan_other_description'],
								);
								$this->AcctCreditAccount_model->insertAcctCreditsAgunan($dataagunan);
							}
						}

						$auth = $this->session->userdata('auth');
						$msg  = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Pinjaman Sukses
								</div> ";
						$sesi = $this->session->userdata('unique');

						$this->session->unset_userdata('addarrayacctcreditsagunan-'.$sesi['unique']);
						$this->session->unset_userdata('addacctcreditaccount-'.$sesi['unique']);
						$this->session->unset_userdata('addcreditaccount-'.$sesi['unique']);
						$this->session->unset_userdata('acctcreditsaccounttoken-'.$sesi['unique']);
						$this->session->set_userdata('message',$msg);
						$url='credit-account/show-detail-data/'.$acctcreditsaccount_last['credits_account_id'].'/'.$data['payment_type_id'];
						redirect($url);
					}else{
						$this->session->set_userdata('addacctdepositoaccount',$data);
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Pinjaman Tidak Berhasil
								</div> ";
						$this->session->set_userdata('message',$msg);
						$url='credit-account/add-form/'.$member_id;
						redirect($url);
					}
				} else {
					$this->session->set_userdata('addcreditaccount',$data);
					$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
					$this->session->set_userdata('message',$msg);
					redirect('credit-account/add-form/'.$data['member_id']);
				}
				if($this->input->post('sales_invoice')){
					echo json_encode($this->input->post('sales_invoice'));
					foreach($this->input->post('sales_invoice')as$v){
						if(($v['add']??false)){
							$this->SalesInvoice_model->markSalesAsCredit($v['sales_invoice_id']);
						}
					}
				}
			}else{
				$this->session->set_userdata('addcreditaccount',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('credit-account/add-form/'.$data['member_id']);
			}
		}

		public function addcreditaccountimport(){
			$auth 			= $this->session->userdata('auth');
			$sesi 			= $this->session->userdata('unique');
			$daftaragunan 	= $this->session->userdata('addarrayacctcreditsagunan-'.$sesi['unique']);

			$agunan_data 	= $this->session->userdata('agunan_data');
			$agunan 		= $this->session->userdata('agunan_key');
			$a 				= json_encode($agunan_data);

			$this->session->unset_userdata('agunan_data');
			$this->session->unset_userdata('agunan_key');

			$acctcreditsimport = $this->AcctCreditAccount_model->getAcctCreditImport();
			
			$this->form_validation->set_rules('credit_id', 'jenis Pinjaman', 'required');
			$this->form_validation->set_rules('credits_account_last_balance_principal', 'Pinjaman', 'required');
			$this->form_validation->set_rules('credit_account_interest', 'Bunga Per Bulan', 'required');
			$this->form_validation->set_rules('payment_type_id', 'Jenis Angsuran', 'required');
			$this->form_validation->set_rules('payment_period', 'Angsuran Tiap', 'required');
			$this->form_validation->set_rules('credit_account_period', 'Jangka Waktu', 'required');
			$this->form_validation->set_rules('office_id', 'Business Officer (BO)', 'required');
			$this->form_validation->set_rules('sumberdana', 'Sumber Dana', 'required');
			
			if($this->form_validation->run()==true){
				foreach($acctcreditsimport as $key => $val){
					$data = array (
						"credits_account_date" 				=> tgltodb($this->input->post('credit_account_date',true)),
						"member_id"							=> $val['member_id'],
						"office_id"							=> $this->input->post('office_id',true),
						"source_fund_id"					=> $this->input->post('sumberdana',true),
						"credits_id"						=> $this->input->post('credit_id',true),
						"branch_id"							=> $auth['branch_id'],
						"payment_preference_id"				=> $this->input->post('payment_preference_id',true),
						"payment_type_id"					=> $this->input->post('payment_type_id',true),
						"method_id"							=> $this->input->post('method_id',true),
						"bank_account_id"					=> $this->input->post('bank_account_id',true),
						"credits_payment_period"			=> $this->input->post('payment_period',true),
						"credits_account_period"			=> $this->input->post('credit_account_period',true),
						"credits_account_due_date"			=> tgltodb($this->input->post('credit_account_due_date',true)),
						"credits_account_amount"			=> $this->input->post('credits_account_last_balance_principal',true),
						"credits_account_interest"			=> $this->input->post('credit_account_interest',true),
						"credits_account_special"			=> $this->input->post('credit_account_special',true),
						"credits_account_adm_cost"			=> $this->input->post('credits_account_adm_cost',true),
						"credits_account_insurance"			=> $this->input->post('credits_account_insurance',true),
						"credits_account_discount"			=> $this->input->post('credits_account_discount',true),
						"credits_account_remark"			=> $this->input->post('credits_account_remark',true),
						"credits_account_bank_name"			=> $this->input->post('credits_account_bank_name',true),
						"credits_account_bank_account"		=> $this->input->post('credits_account_bank_account',true),
						"credits_account_bank_owner"		=> $this->input->post('credits_account_bank_owner',true),
						"credits_account_amount_received"	=> $this->input->post('credit_account_amount_received',true),
						"credits_account_principal_amount"	=> $this->input->post('credits_account_principal_amount',true),
						"credits_account_interest_amount"	=> $this->input->post('credits_account_interest_amount',true),
						"credits_account_payment_amount"	=> $this->input->post('credit_account_payment_amount',true),
						"credits_account_last_balance"		=> $this->input->post('credits_account_last_balance_principal',true),
						"credits_account_payment_date"		=> tgltodb($this->input->post('credit_account_payment_to',true)),
						"savings_account_id"				=> $this->input->post('savings_account_id',true),
						"credits_account_token" 			=> $this->input->post('credits_account_token',true).$val['member_id'],
						"created_id"						=> $auth['user_id'],
						"created_on"						=> date('Y-m-d H:i:s'),
					);
		
					$credits_account_token 	= $this->AcctCreditAccount_model->getCreditsAccountToken($data['credits_account_token']);
		
					if($credits_account_token->num_rows()==0){
						if($this->AcctCreditAccount_model->insertAcctCreditAccount($data)){
							$acctcreditsaccount_last	= $this->AcctCreditAccount_model->getAcctCreditsAccount_Last($data['created_on']);
							
							if(!empty($daftaragunan)){
								foreach ($daftaragunan as $key => $val) {
									if($val['credits_agunan_type'] == 'Penerimaan'){
										$credits_agunan_type	= 1;
									}else if($val['credits_agunan_type'] == 'Deposito') {
										$credits_agunan_type 	= 2;
									}else if($val['credits_agunan_type'] == 'BPJS Ketenagakerjaan'){
										$credits_agunan_type 	= 3;
									}else {
										$credits_agunan_type 	= 4;
									}
	
									$dataagunan = array (
										'credits_account_id'						=> $acctcreditsaccount_last['credits_account_id'],
										'credits_agunan_type'						=> $credits_agunan_type,
										'credits_agunan_penerimaan_description'		=> $val['credits_agunan_penerimaan_description'],
										'credits_agunan_deposito_account_no'		=> $val['credits_agunan_deposito_account_no'],
										'credits_agunan_other_description'			=> $val['credits_agunan_other_description'],
									);
									$this->AcctCreditAccount_model->insertAcctCreditsAgunan($dataagunan);
								}
							}
						}else{
							$this->session->set_userdata('addacctdepositoaccount',$data);
							$msg = "<div class='alert alert-danger alert-dismissable'>
									<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
										Tambah Data Pinjaman Tidak Berhasil
									</div> ";
							$this->session->set_userdata('message',$msg);
							$url='credit-account/add-multiple';
							redirect($url);
						}
					}
				}
	
				$this->AcctCreditAccount_model->truncateAcctCreditsImport();
				
				$auth = $this->session->userdata('auth');
				$msg  = "<div class='alert alert-success alert-dismissable'>  
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Tambah Data Pinjaman Sukses
						</div> ";
				$sesi = $this->session->userdata('unique');

				$this->session->unset_userdata('addarrayacctcreditsagunan-'.$sesi['unique']);
				$this->session->unset_userdata('addacctcreditaccount-'.$sesi['unique']);
				$this->session->unset_userdata('addcreditaccount-'.$sesi['unique']);
				$this->session->unset_userdata('acctcreditsaccounttoken-'.$sesi['unique']);
				$this->session->set_userdata('message',$msg);
				$url='credit-account';
				redirect($url);
			}else{
				$this->session->set_userdata('addcreditaccount',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('credit-account/add-multiple');
			}
		}

		public function Approving(){
			$credits_account_id 	= $this->uri->segment(3);
			$unique 				= $this->session->userdata('unique');
			$token 					= $this->session->userdata('acctcreditsaccounttoken-'.$unique['unique']);

			if(empty($token)){
				$token = md5(date('Y-m-d H:i:s'));
				$this->session->set_userdata('acctcreditsaccounttoken-'.$unique['unique'], $token);
			}

			$data['main_view']['memberidentity']			= $this->configuration->MemberIdentity();
			$data['main_view']['approvalstatus']			= $this->configuration->ApprovalStatus();
			$data['main_view']['paymenttype']				= $this->configuration->PaymentType();
			$data['main_view']['methods']					= $this->configuration->AcquittanceMethodReal();
			$data['main_view']['acctbankaccount']			= create_double($this->AcctCreditAccount_model->getBankAccount(),'bank_account_id', 'bank_account_name');
			$data['main_view']['acctcreditsaccount']		= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$data['main_view']['content']					= 'AcctCreditAccount/FormApproveAcctCreditsAccount_view';
			
			$this->load->view('MainPage_view',$data);
		}

		public function processApprove(){
			$auth = $this->session->userdata('auth');

			$dataApprove = array (
				'credits_account_id'		=> $this->input->post('credits_account_id', true),
				'credits_account_token'		=> $this->input->post('credits_account_token', true),
				'credits_approve_status'	=> 1,
			);

			$data = array(
				'credits_account_id'				=> $this->input->post('credits_account_id', true),
				'credits_account_amount'			=> $this->input->post('credits_account_amount', true),
				'credits_account_last_balance'		=> $this->input->post('credits_account_amount', true),
				'credits_account_interest'			=> $this->input->post('credits_account_interest', true),
				'credits_account_adm_cost'			=> $this->input->post('credits_account_adm_cost', true),  
				'credits_account_special'			=> $this->input->post('credits_account_special', true),
				'credits_account_insurance'			=> $this->input->post('credits_account_insurance', true),
				'credits_account_discount'			=> $this->input->post('credits_account_discount', true),
				'credits_account_notaris'			=> $this->input->post('credits_account_notaris', true),
				'credits_account_amount_received'	=> $this->input->post('credits_account_amount_received', true),
				'credits_account_payment_amount'	=> $this->input->post('credits_account_payment_amount', true),
				'credits_account_principal_amount'	=> $this->input->post('credits_account_principal_amount', true),
				'credits_account_interest_amount'	=> $this->input->post('credits_account_interest_amount', true),
				'credits_account_date'				=> tgltodb($this->input->post('credits_account_date', true)),
				'credits_account_payment_date'		=> tgltodb($this->input->post('credits_account_payment_date', true)),
				'credits_account_due_date'			=> tgltodb($this->input->post('credits_account_due_date', true)),
				'credits_id'						=> $this->input->post('credits_id',true),
				'method_id'							=> $this->input->post('method_id',true),
				'bank_account_id'					=> $this->input->post('bank_account_id',true),
			);
			
			$this->form_validation->set_rules('credits_account_id','No. Perjanjian Kredit', 'required');
			$this->form_validation->set_rules('method_id','Metode', 'required');

			if($data['method_id'] == 2){
				$this->form_validation->set_rules('bank_account_id','Bank', 'required');
			}

			$transaction_module_code 				= 'PYB';
			$transaction_module_id 					= $this->AcctCreditAccount_model->getTransactionModuleID($transaction_module_code);
			$preferencecompany 						= $this->AcctCreditAccount_model->getPreferenceCompany();
			$preferenceinventory 					= $this->AcctCreditAccount_model->getPreferenceInventory();			
			$credits_account_token 					= $this->AcctCreditAccount_model->getCreditsAccountToken($dataApprove['credits_account_token']);
			$journal_voucher_period 				= date("Ym", strtotime($data['credits_account_date']));

			if($this->form_validation->run()==true){
				if($credits_account_token->num_rows()==0){
					if($this->AcctCreditAccount_model->updateAcctCreditAccount($data)){
						if($this->AcctCreditAccount_model->updateApprove($dataApprove)){
							$acctcreditsaccount_last = $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($dataApprove['credits_account_id']);	
							$auth = $this->session->userdata('auth');

							if($data['credits_id'] != 99){
								$data_journal = array(
									'branch_id'						=> $auth['branch_id'],
									'journal_voucher_period' 		=> $journal_voucher_period,
									'journal_voucher_date'			=> date('Y-m-d'),
									'journal_voucher_title'			=> 'PEMBIAYAAN '.$acctcreditsaccount_last['credits_name'].' '.$acctcreditsaccount_last['member_name'],
									'journal_voucher_description'	=> 'PEMBIAYAAN '.$acctcreditsaccount_last['credits_name'].' '.$acctcreditsaccount_last['member_name'],
									'journal_voucher_token'			=> $dataApprove['credits_account_token'],
									'transaction_module_id'			=> $transaction_module_id,
									'transaction_module_code'		=> $transaction_module_code,
									'transaction_journal_id' 		=> $acctcreditsaccount_last['credits_account_id'],
									'transaction_journal_no' 		=> $acctcreditsaccount_last['credits_account_serial'],
									'created_id'					=> $auth['user_id'],								
									'created_on' 					=> date('Y-m-d H:i:s'),
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);

								$journal_voucher_id 				= $this->AcctCreditAccount_model->getJournalVoucherID($data_journal['created_id']);

								$receivable_account_id				= $this->AcctCreditAccount_model->getReceivableAccountID($data['credits_id']);

								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($receivable_account_id);

								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $receivable_account_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['credits_account_amount'],
									'journal_voucher_debit_amount'	=> $data['credits_account_amount'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token' 	=> $dataApprove['credits_account_token'].$receivable_account_id,
									'created_id' 					=> $auth['user_id'],
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);

								$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
								$preferenceinventory 				= $this->AcctCreditAccount_model->getPreferenceInventory();	
								
								if($data['credits_account_insurance'] !='' && $data['credits_account_insurance'] > 0){
									$insurance_amount = $data['credits_account_insurance'];
								}else{
									$insurance_amount = 0;
								}
								if($data['credits_account_adm_cost'] != '' && $data['credits_account_adm_cost'] > 0){
									$adm_amount = $data['credits_account_adm_cost'];
								}else{
									$adm_amount = 0;
								}
								if($data['credits_id'] == 20){
									$discount_amount = $data['credits_account_discount'];
								}else{
									$discount_amount = 0;
								}

								$cash_amount = $data['credits_account_amount'] - $insurance_amount - $adm_amount - $discount_amount;
		
								if($data['method_id'] == 1){
									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

									$data_credit = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $preferencecompany['account_cash_id'],
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $cash_amount,
										'journal_voucher_credit_amount'	=> $cash_amount,
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 1,
										'journal_voucher_item_token'	=> $dataApprove['credits_account_token'].$preferencecompany['account_cash_id'],
										'created_id' 					=> $auth['user_id'],
									);
								}else if($data['method_id'] == 2){
									$account_id							= $this->AcctCreditAccount_model->getAccountBank($data['bank_account_id']);
									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($account_id);

									$data_credit = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $account_id,
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $cash_amount,
										'journal_voucher_credit_amount'	=> $cash_amount,
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 1,
										'journal_voucher_item_token'	=> $dataApprove['credits_account_token'].$account_id,
										'created_id' 					=> $auth['user_id'],
									);
								}else{
									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

									$data_credit = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $preferencecompany['account_salary_payment_id'],
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $cash_amount,
										'journal_voucher_credit_amount'	=> $cash_amount,
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 1,
										'journal_voucher_item_token'	=> $dataApprove['credits_account_token'].$preferencecompany['account_salary_payment_id'],
										'created_id' 					=> $auth['user_id'],
									);
								}
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);	

								if($data['credits_account_insurance'] !='' && $data['credits_account_insurance'] > 0){
									$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();

									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_insurance_cost_id']);

									$data_credit = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $preferencecompany['account_insurance_cost_id'],
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $data['credits_account_insurance'],
										'journal_voucher_credit_amount'	=> $data['credits_account_insurance'],
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 1,
										'journal_voucher_item_token'	=> $dataApprove['credits_account_token'].'INS'.$preferencecompany['account_insurance_cost_id'],
										'created_id' 					=> $auth['user_id'],
									);
									$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
								}

								if($data['credits_account_adm_cost'] != '' && $data['credits_account_adm_cost'] > 0){
									$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
									$preferenceinventory 				= $this->AcctCreditAccount_model->getPreferenceInventory();	

									if($data['credits_id'] == 3){
										$adm_account_id = 318;
									}else{
										$adm_account_id = $preferenceinventory['inventory_adm_id'];
									}

									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($adm_account_id);

									$data_credit = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $adm_account_id,
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $data['credits_account_adm_cost'],
										'journal_voucher_credit_amount'	=> $data['credits_account_adm_cost'],
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 1,
										'journal_voucher_item_token'	=> $dataApprove['credits_account_token'].'ADM'.$adm_account_id,
										'created_id' 					=> $auth['user_id'],
									);

									$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
								}

								if($data['credits_id'] == 20 && $data['credits_account_discount'] != '' && $data['credits_account_discount'] > 0){
									$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
									$preferenceinventory 				= $this->AcctCreditAccount_model->getPreferenceInventory();	

									$discount_account_id 				= $preferenceinventory['inventory_discount_id'];

									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($discount_account_id);

									$data_credit = array (
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $discount_account_id,
										'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
										'journal_voucher_amount'		=> $data['credits_account_discount'],
										'journal_voucher_credit_amount'	=> $data['credits_account_discount'],
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 1,
										'journal_voucher_item_token'	=> $dataApprove['credits_account_token'].'ADM'.$discount_account_id,
										'created_id' 					=> $auth['user_id'],
									);

									$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
								}
							}else{
								$data_journal = array(
									'company_id'                    => 1,
									'transaction_module_id'         => $transaction_module_id,
									'transaction_module_code'       => $transaction_module_code,
									'journal_voucher_period' 		=> $journal_voucher_period,
									'transaction_journal_no' 		=> $acctcreditsaccount_last['credits_account_serial'],
									'journal_voucher_date'			=> date('Y-m-d'),
									'journal_voucher_status'        => 1,
									'journal_voucher_title'			=> 'PEMBIAYAAN '.$acctcreditsaccount_last['credits_name'].' '.$acctcreditsaccount_last['member_name'],
									'journal_voucher_description'	=> 'PEMBIAYAAN '.$acctcreditsaccount_last['credits_name'].' '.$acctcreditsaccount_last['member_name'],
									'created_id'                    => $auth['user_id'],
									'updated_id'                    => $auth['user_id']
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherMinimarket($data_journal);
	
								$journal_voucher_id 				= $this->AcctCreditAccount_model->getJournalVoucherIDMinimarket($data_journal['created_id']);
								
								$receivable_account_id				= $this->AcctCreditAccount_model->getReceivableAccountID($data['credits_id']);

								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($receivable_account_id);
	
								$data_debet = array (
									'company_id'                    => 1,
									'journal_voucher_id'            => $journal_voucher_id,
									'account_id'                    => $receivable_account_id,
									'journal_voucher_amount'        => $data['credits_account_amount'],
									'journal_voucher_debit_amount'  => $data['credits_account_amount'],
									'account_id_default_status'     => $account_id_default_status,
									'account_id_status'             => 0,
									'created_id'                    => $auth['user_id'],
									'updated_id'                    => $auth['user_id'],
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_debet);
								
								if($data['credits_account_insurance'] !='' && $data['credits_account_insurance'] > 0){
									$insurance_amount = $data['credits_account_insurance'];
								}else{
									$insurance_amount = 0;
								}
								if($data['credits_account_adm_cost'] != '' && $data['credits_account_adm_cost'] > 0){
									$adm_amount = $data['credits_account_adm_cost'];
								}else{
									$adm_amount = 0;
								}
								if($data['credits_id'] == 20){
									$discount_amount = $data['credits_account_discount'];
								}else{
									$discount_amount = 0;
								}
								$cash_amount = $data['credits_account_amount'] - $insurance_amount - $adm_amount - $discount_amount;

								if($data['method_id'] == 1){
									$account_cash_id					= $preferencecompany['account_cash_id'];
									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_cash_id);
									
									$data_credit = array (
										'company_id'                    => 1,
										'journal_voucher_id'            => $journal_voucher_id,
										'account_id'                    => $account_cash_id,
										'journal_voucher_amount'        => $cash_amount,
										'journal_voucher_credit_amount' => $cash_amount,
										'account_id_default_status'     => $account_id_default_status,
										'account_id_status'             => 1,
										'created_id'                    => $auth['user_id'],
										'updated_id'                    => $auth['user_id']
									);
								}else if($data['method_id'] == 2){
									$account_id							= $this->AcctCreditAccount_model->getAccountBank($data['bank_account_id']);
									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_id);
									
									$data_credit = array (
										'company_id'                    => 1,
										'journal_voucher_id'            => $journal_voucher_id,
										'account_id'                    => $account_id,
										'journal_voucher_amount'        => $cash_amount,
										'journal_voucher_credit_amount' => $cash_amount,
										'account_id_default_status'     => $account_id_default_status,
										'account_id_status'             => 1,
										'created_id'                    => $auth['user_id'],
										'updated_id'                    => $auth['user_id']
									);
								}else{
									$account_salary_payment_id			= $preferencecompany['account_salary_payment_id'];
									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_salary_payment_id);
									
									$data_credit = array (
										'company_id'                    => 1,
										'journal_voucher_id'            => $journal_voucher_id,
										'account_id'                    => $account_salary_payment_id,
										'journal_voucher_amount'        => $cash_amount,
										'journal_voucher_credit_amount' => $cash_amount,
										'account_id_default_status'     => $account_id_default_status,
										'account_id_status'             => 1,
										'created_id'                    => $auth['user_id'],
										'updated_id'                    => $auth['user_id']
									);
								}
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);		
	
								if($data['credits_account_insurance'] !='' && $data['credits_account_insurance'] > 0){
									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_cash_id);
	
									$account_insurance_cost_id 			= $preferencecompany['account_insurance_cost_id'];
									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_insurance_cost_id);
	
									$data_credit = array (
										'company_id'                    => 1,
										'journal_voucher_id'            => $journal_voucher_id,
										'account_id'                    => $account_insurance_cost_id,
										'journal_voucher_amount'        => $data['credits_account_insurance'],
										'account_id_default_status'     => $account_id_default_status,
										'account_id_status'             => 1,
										'journal_voucher_credit_amount' => $data['credits_account_insurance'],
										'created_id'                    => $auth['user_id'],
										'updated_id'                    => $auth['user_id']
									);
									$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);
								}

								if($data['credits_account_adm_cost'] != '' && $data['credits_account_adm_cost'] > 0){
									$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
									$preferenceinventory 				= $this->AcctCreditAccount_model->getPreferenceInventory();	

									if($data['credits_id'] == 3){
										$adm_account_id = 318;
									}else{
										$adm_account_id = $preferenceinventory['inventory_adm_id'];
									}

									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($adm_account_id);

									$data_credit = array (
										'company_id'                    => 1,
										'journal_voucher_id'            => $journal_voucher_id,
										'account_id'                    => $adm_account_id,
										'journal_voucher_amount'        => $data['credits_account_adm_cost'],
										'journal_voucher_credit_amount' => $data['credits_account_adm_cost'],
										'account_id_default_status'     => $account_id_default_status,
										'account_id_status'             => 1,
										'created_id'                    => $auth['user_id'],
										'updated_id'                    => $auth['user_id']
									);

									$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);
								}

								if($data['credits_id'] == 20 && $data['credits_account_discount'] != '' && $data['credits_account_discount'] > 0){
									$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
									$preferenceinventory 				= $this->AcctCreditAccount_model->getPreferenceInventory();	

									$discount_account_id 				= $preferenceinventory['inventory_discount_id'];

									$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($discount_account_id);

									$data_credit = array (
										'company_id'                    => 1,
										'journal_voucher_id'            => $journal_voucher_id,
										'account_id'                    => $discount_account_id,
										'journal_voucher_amount'        => $data['credits_account_discount'],
										'journal_voucher_credit_amount' => $data['credits_account_discount'],
										'account_id_default_status'     => $account_id_default_status,
										'account_id_status'             => 1,
										'created_id'                    => $auth['user_id'],
										'updated_id'                    => $auth['user_id']
									);

									$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);
								}
							}

							$auth = $this->session->userdata('auth');
							$msg  = "<div class='alert alert-success alert-dismissable'>  
									<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
										Proses Persetujuan Berhasil
									</div> ";
							$sesi = $this->session->userdata('unique');
							
							$this->session->unset_userdata('addacctcreditaccount-'.$sesi['unique']);
							$this->session->unset_userdata('addcreditaccount-'.$sesi['unique']);
							$this->session->unset_userdata('acctcreditsaccounttoken-'.$sesi['unique']);
							$this->session->set_userdata('message',$msg);
							$url='credit-account';
							redirect($url);
						}else{
							$this->session->set_userdata('addacctdepositoaccount',$data);
							$msg = "<div class='alert alert-danger alert-dismissable'>
									<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>				
										Proses Persetujuan Tidak Berhasil
									</div> ";
							$this->session->set_userdata('message',$msg);
							$url = 'credit-account';
							redirect($url);
						}
					}
				}else{
					$acctcreditsaccount_last = $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($dataApprove['credits_account_id']);	
					$auth 					 = $this->session->userdata('auth');

					$data_journal = array(
						'branch_id'						=> $auth['branch_id'],
						'journal_voucher_period' 		=> $journal_voucher_period,
						'journal_voucher_date'			=> date('Y-m-d'),
						'journal_voucher_title'			=> 'PEMBIAYAAN '.$acctcreditsaccount_last['credits_name'].' '.$acctcreditsaccount_last['member_name'],
						'journal_voucher_description'	=> 'PEMBIAYAAN '.$acctcreditsaccount_last['credits_name'].' '.$acctcreditsaccount_last['member_name'],
						'journal_voucher_token'			=> $dataApprove['credits_account_token'],
						'transaction_module_id'			=> $transaction_module_id,
						'transaction_module_code'		=> $transaction_module_code,
						'transaction_journal_id' 		=> $acctcreditsaccount_last['credits_account_id'],
						'transaction_journal_no' 		=> $acctcreditsaccount_last['credits_account_serial'],
						'created_id'					=> $auth['user_id'],								
						'created_on' 					=> date('Y-m-d H:i:s'),
					);

					$journal_voucher_id 				= $this->AcctCreditAccount_model->getJournalVoucherID($data_journal['created_id']);
					$receivable_account_id				= $this->AcctCreditAccount_model->getReceivableAccountID($data['credits_id']);
					$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($receivable_account_id);

					$data_debet = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $receivable_account_id,
						'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
						'journal_voucher_amount'		=> $data['credits_account_amount'],
						'journal_voucher_debit_amount'	=> $data['credits_account_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 0,
						'journal_voucher_item_token' 	=> $data_journal['journal_voucher_token'].$receivable_account_id,
						'created_id' 					=> $auth['user_id'],
					);
					
					$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);
					}
					
					$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
								
					if($data['credits_account_insurance'] !='' && $data['credits_account_insurance'] > 0){
						$insurance_amount = $data['credits_account_insurance'];
					}else{
						$insurance_amount = 0;
					}
					if($data['credits_account_adm_cost'] != '' && $data['credits_account_adm_cost'] > 0){
						$adm_amount = $data['credits_account_adm_cost'];
					}else{
						$adm_amount = 0;
					}
					if($data['credits_id'] == 20){
						$discount_amount = $data['credits_account_discount'];
					}else{
						$discount_amount = 0;
					}

					$cash_amount = $data['credits_account_amount'] - $insurance_amount - $adm_amount - $discount_amount;

					if($data['method_id'] == 1){
						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);
						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_cash_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $cash_amount,
							'journal_voucher_credit_amount'	=> $cash_amount,
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data_journal['journal_voucher_token'].$preferencecompany['account_cash_id'],
							'created_id' 					=> $auth['user_id'],
						);
					}else if($data['method_id'] == 2){
						$account_id							= $this->AcctCreditAccount_model->getAccountBank($data['bank_account_id']);
						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($account_id);
						
						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_id,
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $cash_amount,
							'journal_voucher_credit_amount'	=> $cash_amount,
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data_journal['journal_voucher_token'].$account_id,
							'created_id' 					=> $auth['user_id'],
						);
					}else{
						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);
						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_salary_payment_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $cash_amount,
							'journal_voucher_credit_amount'	=> $cash_amount,
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data_journal['journal_voucher_token'].$preferencecompany['account_salary_payment_id'],
							'created_id' 					=> $auth['user_id'],
						);
					}

					$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
					}			

					if($data['credits_account_insurance'] != '' && $data['credits_account_insurance'] >0 ){
						$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();

						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_insurance_cost_id']);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_insurance_cost_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data['credits_account_insurance'],
							'journal_voucher_credit_amount'	=> $data['credits_account_insurance'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data_journal['journal_voucher_token'].'INS'.$preferencecompany['account_insurance_cost_id'],
							'created_id' 					=> $auth['user_id'],
						);

						$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}

					if($data['credits_account_adm_cost'] != '' && $data['credits_account_adm_cost'] > 0){
						$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
						$preferenceinventory 				= $this->AcctCreditAccount_model->getPreferenceInventory();

						if($data['credits_id'] == 3){
							$adm_account_id = 318;
						}else{
							$adm_account_id = $preferenceinventory['inventory_adm_id'];
						}

						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($adm_account_id);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $adm_account_id,
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data['credits_account_adm_cost'],
							'journal_voucher_credit_amount'	=> $data['credits_account_adm_cost'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data_journal['journal_voucher_token'].'ADM'.$adm_account_id,
							'created_id' 					=> $auth['user_id'],
						);

						$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}

					if($data['credits_id'] == 20 && $data['credits_account_discount'] != '' && $data['credits_account_discount'] > 0){
						$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
						$preferenceinventory 				= $this->AcctCreditAccount_model->getPreferenceInventory();

						$discount_account_id 				= $preferenceinventory['inventory_discount_id'];

						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($discount_account_id);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $discount_account_id,
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data['credits_account_discount'],
							'journal_voucher_credit_amount'	=> $data['credits_account_discount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data_journal['journal_voucher_token'].'ADM'.$discount_account_id,
							'created_id' 					=> $auth['user_id'],
						);

						$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}

					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					 	
								Proses Persetujuan Berhasil
							</div> ";
					$sesi = $this->session->userdata('unique');

					$this->session->unset_userdata('addarrayacctcreditsagunan-'.$sesi['unique']);
					$this->session->unset_userdata('addacctcreditaccount-'.$sesi['unique']);
					$this->session->unset_userdata('addcreditaccount-'.$sesi['unique']);
					$this->session->unset_userdata('acctcreditsaccounttoken-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					$url='credit-account';
					redirect($url);
				}
			}else{
				$this->session->set_userdata('addcreditaccount',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('credit-account/approving/'.$data['credits_account_id']);				
			}
		}

		public function rejectAcctCreditsAccount(){
			$credits_account_id = $this->uri->segment(3);
			$data = array (
				'credits_account_id'		=> $credits_account_id,
				'credits_approve_status'	=> 9,
			);

			if($this->AcctCreditAccount_model->updateAcctCreditAccount($data)){
				$this->session->set_userdata('addacctdepositoaccount',$data);
				$msg = "<div class='alert alert-success alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Proses Pembatalan Perjanjian Kredit Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				$url = 'credit-account';
				redirect($url);
			} else {
				$this->session->set_userdata('addacctdepositoaccount',$data);
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Proses Pembatalan Perjanjian Kredit Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				$url = 'credit-account';
				redirect($url);
			}
		}

		public function showdetaildata(){
			$auth 					= $this->session->userdata('auth'); 
			$credits_account_id 	= $this->uri->segment(3);
			$type 					= $this->uri->segment(4);

			if($type== '' && $type==1){
				$datapola 			= $this->flat($credits_account_id);
			} else if($type == 2){
				$datapola 			= $this->anuitas($credits_account_id);
			} else{
				$datapola 			= $this->slidingrate($credits_account_id);
			}

			$detaildata 			= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);

			$data['main_view']['memberidentity']			= $this->configuration->MemberIdentity();
			$data['main_view']['membergender']				= $this->configuration->MemberGender();
			$data['main_view']['acctcreditsaccount']		= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$data['main_view']['acctcreditsagunan']			= $this->AcctCreditAccount_model->getAcctCreditsAgunan_Detail($credits_account_id);
			$data['main_view']['coreoffice']				= create_double($this->AcctCreditAccount_model->getCoreOffice(),'office_id', 'office_name');
			$data['main_view']['sumberdana']				= create_double($this->Core_source_fund_model->getData(),'source_fund_id', 'source_fund_name');
			$data['main_view']['coremember']				= $this->CoreMember_model->getCoreMember_Detail($detaildata['member_id']);
			$data['main_view']['acctsavingsaccount']		= create_double($this->AcctDepositoAccount_model->getAcctSavingsAccount($auth['branch_id']),'savings_account_id', 'savings_account_no');
			$data['main_view']['creditid']					= create_double($this->AcctCredit_model->getData(),'credits_id', 'credits_name');
			$data['main_view']['creditaccount']				= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($this->uri->segment(3));
			$data['main_view']['datapola']					= $datapola;
			$data['main_view']['paymenttype']				= $this->configuration->PaymentType();
			$data['main_view']['paymentpreference']			= $this->configuration->PaymentPreference();

			$data['main_view']['content']					= 'AcctCreditAccount/FormSaveSuccessAcctCreditAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function printNoteAcctCreditAccount(){
			$auth 					= $this->session->userdata('auth');
			$credits_account_id 	= $this->uri->segment(3);
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();
			$acctcreditsaccount	 	= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);

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
			    	<td rowspan=\"2\" width=\"20%\">".$img."</td>
			        <td width=\"50%\"><div style=\"text-align: left; font-size:14px\">BUKTI PENCAIRAN PEMBIAYAAN</div></td>
			    </tr>
			    <tr>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');

			$tbl1 = "
			<div style=\"font-weight: bold;\">Telah dibayarkan kepada :</div>
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctcreditsaccount['member_name']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">No. Akad</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctcreditsaccount['credits_account_serial']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctcreditsaccount['member_address']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".numtotxt($acctcreditsaccount['credits_account_amount'])."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: PENCAIRAN PEMBIAYAAN</div></td>
			    </tr>
				<tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
			        <td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
			        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($acctcreditsaccount['credits_account_amount'], 2)."</div></td>
			    </tr>	
				<tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Biaya Asuransi</div></td>
			        <td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
			        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($acctcreditsaccount['credits_account_insurance'], 2)."</div></td>
			    </tr>	
				<tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Biaya Administrasi</div></td>
			        <td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
			        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($acctcreditsaccount['credits_account_adm_cost'], 2)."</div></td>
			    </tr>	
				<tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Terima Bersih</div></td>
			        <td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
			        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($acctcreditsaccount['credits_account_amount_received'], 2)."</div></td>
			    </tr>	
			</table>";

			$tbl2 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			    	<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctCreditAccount_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
			    </tr>
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: center;\">Penerima</div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
			    </tr>				
			</table>";

			$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			$filename = 'Kwitansi.pdf';
			$pdf->Output($filename, 'I');
		}

		public function AcctCreditAccountBook(){
			$auth = $this->session->userdata('auth');

			$data['main_view']['acctcredits']	= create_double($this->AcctCreditAccount_model->getAcctCredits(),'credits_id', 'credits_name');
			$data['main_view']['corebranch']	= create_double($this->AcctCreditAccount_model->getCoreBranch(),'branch_id', 'branch_name');
			$data['main_view']['content']		= 'AcctCreditAccount/ListBookAcctCreditsAccount_view';
			$this->load->view('MainPage_view', $data);
		}

		public function filteracctcreditsaccountbook(){
			$data = array (
				'start_date'	=> tgltodb($this->input->post('start_date', true)),
				'end_date'		=> tgltodb($this->input->post('end_date', true)),
				'credits_id'	=> $this->input->post('credits_id', true),
				'branch_id'		=> $this->input->post('branch_id', true),
			);

			$this->session->set_userdata('filter-acctcreditsaccountbooklist', $data);
			redirect('credit-account/book');
		}

		public function getAcctCreditsAccountBookList(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctcreditsaccountbooklist');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['credits_id']		='';
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}
			} else {
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}
			}
			
			$creditsapprovestatus = $this->configuration->CreditsApproveStatus();

			$list = $this->AcctCreditAccount_model->get_datatables_master($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $creditsaccount) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $creditsaccount->credits_account_serial;
	            $row[] = $creditsaccount->member_name;
	            $row[] = $creditsaccount->credits_name;
	            $row[] = $creditsaccount->source_fund_name;
	            $row[] = tgltoview($creditsaccount->credits_account_date);
	            $row[] = number_format($creditsaccount->credits_account_amount, 2);
	            $row[] = $creditsapprovestatus[$creditsaccount->credits_approve_status];
	     
	            if ($creditsaccount->credits_approve_status == 1){
			    	$row[] = '<a href="'.base_url().'credit-account/print-book//'.$creditsaccount->credits_account_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Cetak Cover</a>';
	            }else{
	            	$row[] ='';
	            }
	            $data[] = $row;
	        }
	 
	        $output = array(
				"draw" 				=> $_POST['draw'],
				"recordsTotal" 		=> $this->AcctCreditAccount_model->count_all_master($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']),
				"recordsFiltered" 	=> $this->AcctCreditAccount_model->count_filtered_master($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']),
				"data" 				=> $data,
			);
	        echo json_encode($output);
		}

		public function printBookAcctCreditAccount(){
			$auth 					= $this->session->userdata('auth');
			$credits_account_id 	= $this->uri->segment(3);
			$acctcreditsaccount	 	= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();

			$credits_account_payment_date = date('Y-m-d', strtotime("+1 months", strtotime($acctcreditsaccount['credits_account_date'])));

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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

			$resolution	= array(200, 200);
			$page 		= $pdf->AddPage('P', $resolution);

			$pdf->SetFont('helvetica', '', 8);

			// -----------------------------------------------------------------------------
			$base_url 	= base_url();
			$img 		= "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";
			$tbl1 		.= "
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
			";

			$tbl1 .= "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">NOMOR KONTRAK</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctcreditsaccount['credits_account_serial']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">JUMLAH PEMBIAYAAN</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".number_format($acctcreditsaccount['credits_account_amount'], 2)."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">TENOR</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctcreditsaccount['credits_account_period']." Bulan</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">ANGSURAN</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".number_format($acctcreditsaccount['credits_account_payment_amount'], 2)."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">TGL AKTIVASI</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".tgltoview($acctcreditsaccount['credits_account_date'])."</div></td>
			    </tr>
			     <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">JATUH TEMPO PERTAMA</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".tgltoview($credits_account_payment_date)."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">JATUH TEMPO TERAKHIR</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".tgltoview($acctcreditsaccount['credits_account_due_date'])."</div></td>
			    </tr>			
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">CABANG PENGAJUAN</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctcreditsaccount['branch_name']."</div></td>
			    </tr>	
			</table>";

			$pdf->writeHTML($tbl1, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			$filename = 'Kwitansi.pdf';
			$pdf->Output($filename, 'I');
		}

		public function detailAcctCreditsAccount(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-AcctCreditsAccount');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['branch_id']		= '';
				$sesi['credits_id']		= '';
			}

			$start_date = tgltodb($sesi['start_date']);
			$end_date 	= tgltodb($sesi['end_date']);

			$data['main_view']['corebranch']	= create_double($this->AcctCreditAccount_model->getCoreBranch(), 'branch_id', 'branch_name');
			$data['main_view']['acctcredits']	= create_double($this->AcctCreditAccount_model->getAcctCredits(), 'credits_id', 'credits_name');
			$data['main_view']['content']		= 'AcctCreditAccount/ListDetailAcctCreditsAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filterdetail(){
			$data = array (
				'start_date'	=> tgltodb($this->input->post('start_date',true)),
				'end_date'		=> tgltodb($this->input->post('end_date',true)),
				'branch_id'		=> $this->input->post('branch_id',true),
				'credits_id'	=> $this->input->post('credits_id',true),
			);
			$this->session->set_userdata('filter-AcctCreditsAccount', $data);
			redirect('credit-account/detail');
		}

		public function getAcctCreditsAccountDetailList(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-AcctCreditsAccount');

			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['credits_id']		='';
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}
			} else {
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}
			}

			$creditsapprovestatus 	= $this->configuration->CreditsApproveStatus();
			$list 					= $this->AcctCreditAccount_model->get_datatables_master($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']);

	        $data 	= array();
	        $no 	= $_POST['start'];
	        foreach ($list as $creditsaccount) {
	            $no++;
	            $row 	= array();
	            $row[] 	= $no;
	            $row[] 	= $creditsaccount->credits_account_serial;
	            $row[] 	= $creditsaccount->member_name;
	            $row[] 	= $creditsaccount->credits_name;
	            $row[] 	= $creditsaccount->source_fund_name;
	            $row[] 	= tgltoview($creditsaccount->credits_account_date);
	            $row[] 	= number_format($creditsaccount->credits_account_amount, 2);
	            $row[] 	= $creditsapprovestatus[$creditsaccount->credits_approve_status];

	    		if($creditsaccount->credits_approve_status == 1){
			   		$row[] = '
					<a href="'.base_url().'credit-account/show-detail/'.$creditsaccount->credits_account_id.'" class="btn btn-xs yellow-lemon" role="button"><i class="fa fa-bars"></i> Detail</a>
					<a href="'.base_url().'credit-account/print-pola-angsuran-credits/'.$creditsaccount->credits_account_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i>Pola Angsuran</a>';
			    }else{
			    	$row[]='';
			    }
	            $data[] = $row;
	        }

	        $output = array(
				"draw" 				=> $_POST['draw'],
				"recordsTotal" 		=> $this->AcctCreditAccount_model->count_all_master($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']),
				"recordsFiltered" 	=> $this->AcctCreditAccount_model->count_filtered_master($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']),
				"data" 				=> $data,
			);
	        echo json_encode($output);
		}
		
		public function reset_search(){
			$sesi= $this->session->userdata('filter-AcctCreditsAccount');
			$this->session->unset_userdata('filter-AcctCreditsAccount');
			redirect('credit-account/detail');
		}

		public function showdetail(){
			$credits_account_id 	= $this->uri->segment(3);

			$data['main_view']['memberidentity']		= $this->configuration->MemberIdentity();
			$data['main_view']['paymenttype']			= $this->configuration->PaymentType();
			$data['main_view']['methods']				= $this->configuration->AcquittanceMethod();
			$data['main_view']['acctcreditsaccount']	= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$data['main_view']['acctcreditspayment']	= $this->AcctCreditAccount_model->getAcctCreditsPayment_Detail($credits_account_id);
			$data['main_view']['content']				= 'AcctCreditAccount/FormDetailAcctCreditsAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processPrinting(){
			$credits_account_id		= $this->input->post('credits_account_id',true);
			$memberidentity			= $this->configuration->MemberIdentity();
			$acctcreditsaccount		= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$acctcreditspayment		= $this->AcctCreditAccount_model->getAcctCreditsPayment_Detail($credits_account_id);
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			
			$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(10, 10, 10, 10); 

			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 10);
			
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tblheader = "
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
				<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
					<tr>
						<td style=\"text-align:center;\" width=\"100%\">
							<div style=\"font-size:14px\";><b>HISTORI ANGSURAN PINJAMAN</b></div>
						</td>			
	 				</tr>
	 			</table>
	 			<br><br>
			";
				
			$pdf->writeHTML($tblheader, true, false, false, false, '');

			$tblmember = "
				<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
					<tr>
	 					<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Nama</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"80%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['member_name']."</b></div>
						</td>
									
	 				</tr>
					<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>No. Perjanjian Kredit</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"30%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_serial']."</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px;font-weight:bold\">
								Jangka Waktu
							</div>
						</td>
						
						<td style=\"text-align:left; \" width=\"30%\">
							<div style=\"font-size:12px;font-weight:bold\">
								: ".$acctcreditsaccount['credits_account_period']."
							</div>
						</td>			
	 				</tr>
	 				<tr>
	 					<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px;font-weight:bold\">
								Tanggal Realisasi
							</div>
						</td>

						<td style=\"text-align:left; \" width=\"30%\">
							<div style=\"font-size:12px;font-weight:bold\">
								: ".tgltoview($acctcreditsaccount['credits_account_date'])."
							</div>
	 					</td>
	 					<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px;font-weight:bold\">
								Pinjaman
							</div>
						</td>
						<td style=\"text-align:left; \" width=\"30%\">
							<div style=\"font-size:12px;font-weight:bold\">
								: ".nominal($acctcreditsaccount['credits_account_amount'])."
							</div>
	 					</td>
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px;font-weight:bold\">
								Alamat
							</div>
						</td>

						<td style=\"text-align:left; \" width=\"83%\">
							<div style=\"font-size:12px;font-weight:bold\">
								: ".$acctcreditsaccount['member_address']."
							</div>
	 					</td>
	 				</tr>
	 			</table>
	 			<br><br>
			";

			$pdf->writeHTML($tblmember, true, false, false, false, '');

			$tblpaymentheader = "
			<table id=\"items\" width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" border=\"1\">
				<tr>
					<td style=\"text-align:center;\" width=\"5%\">
						<div style=\"font-size:10px\">
							<b>No</b>
						</div>
					</td>
				
					<td style=\"text-align:center;\" width=\"10%\">
						<div style=\"font-size:10px\">
							<b>Tanggal Angsuran</b>
						</div>
					</td>
				
					<td style=\"text-align:center;\" width=\"15%\">
						<div style=\"font-size:10px\">
							<b>Angsuran Pokok</b>
						</div>
					</td>

					<td style=\"text-align:center;\" width=\"15%\">
						<div style=\"font-size:10px\">
							<b>Angsuran Bunga</b>
						</div>
					</td>

					<td style=\"text-align:center;\" width=\"15%\">
						<div style=\"font-size:10px\">
							<b>Saldo Pokok</b>
						</div>
					</td>

					<td style=\"text-align:center;\" width=\"15%\">
						<div style=\"font-size:10px\">
							<b>Saldo Bunga</b>
						</div>
					</td>

					<td style=\"text-align:center;\" width=\"10%\">
						<div style=\"font-size:10px\">
							<b>Sanksi Dibayarkan</b>
						</div>
					</td>
					<td style=\"text-align:center;\" width=\"15%\">
						<div style=\"font-size:10px\">
							<b>Akumulasi Sanksi</b>
						</div>
					</td>
				</tr>";

			$tblpaymentlist = "";
			$no 			= 1;

			foreach($acctcreditspayment as $key=>$val){
				$tblpaymentlist .= "
					<tr>
						<td style=\"text-align:center;\" width=\"5%\">
							<div style=\"font-size:10px\">
								".$no."
							</div>
						</td>

						<td style=\"text-align:left;\" width=\"10%\">
							<div style=\"font-size:10px\">
								".tgltoview($val['credits_payment_date'])."
							</div>
						</td>
					
						<td style=\"text-align:right;\" width=\"15%\">
							<div style=\"font-size:10px\">
								".nominal($val['credits_payment_principal'])."
							</div>
						</td>
					
						<td style=\"text-align:right;\" width=\"15%\">
							<div style=\"font-size:10px\">
								".nominal($val['credits_payment_interest'])."
							</div>
						</td>

						<td style=\"text-align:right;\" width=\"15%\">
							<div style=\"font-size:10px\">
								".nominal($val['credits_principal_last_balance'])."
							</div>
						</td>

						<td style=\"text-align:right;\" width=\"15%\">
							<div style=\"font-size:10px\">
								".nominal($val['credits_interest_last_balance'])."
							</div>
						</td>

						<td style=\"text-align:right;\" width=\"10%\">
							<div style=\"font-size:10px\">
								".nominal($val['credits_payment_fine'])."
							</div>
						</td>
						<td style=\"text-align:right;\" width=\"15%\">
							<div style=\"font-size:10px\">
								".nominal($val['credits_payment_fine_last_balance'])."
							</div>
						</td>
					</tr>";
				$no++;
			}

			$tblpaymentfooter = "
				</table>
			";

			$pdf->writeHTML($tblpaymentheader.$tblpaymentlist.$tblpaymentfooter, true, false, false, false, '');

			ob_clean();

			$filename = 'Histori_Angsuran_Pinjaman_'.$acctcreditsaccount['credits_account_serial'].'.pdf';
			$pdf->Output($filename, 'I');
		}

		public function creditlist(){
			$data['main_view']['content']	= 'AcctCreditAccount/Creditlist_view';
			$this->load->view('MainPage_view',$data);
		}

		public function creditajax(){
			$list 	= $this->AcctCreditAccount_model->get_datatables();
	        $data 	= array();
	        $no 	= $_POST['start'];
	        foreach ($list as $customers) {
	            $no++;
	            $row 	= array();
	            $row[] 	= $no;
	            $row[] 	= $customers->credits_account_serial;
	            $row[] 	= $customers->member_name;
	            $row[] 	= $customers->member_no;
	            $row[] 	= $customers->credits_account_date;
	            $row[] 	= $customers->credits_account_due_date;
	            $row[] 	= $customers->credits_account_period;
	            $row[] 	= $customers->credits_account_net_price;
	            $row[] 	= $customers->credits_account_sell_price;
	            $row[] 	= $customers->credits_account_margin;
	            $data[] = $row;
	        }
	 
	        $output = array(
				"draw" 				=> $_POST['draw'],
				"recordsTotal" 		=> $this->CoreMember_model->count_all(),
				"recordsFiltered" 	=> $this->CoreMember_model->count_filtered(),
				"data" 				=> $data,
			);
	        echo json_encode($output);
		}

		public function agunanadd(){
			$data 	= $this->session->userdata('agunan_data');
			$agunan = $this->session->userdata('agunan_key');
			if(!isset($agunan)){
				$agunan=1;
			}
			$new_key=$agunan+1;
			if($this->uri->segment(3)=="save"){
				$type=$this->input->post('tipe',true);
				if($type == 'Penerimaan'){
					$data[$new_key]=array (
						"tipe"						=> $this->input->post('tipe',true),
						"penerimaan_description"	=> $this->input->post('penerimaan_description',true),
					);
				}else if($type == 'Deposito'){
					$data[$new_key]=array (
						"tipe"					=> $this->input->post('tipe',true),
						"deposito_account_no"	=> $this->input->post('deposito_account_no',true),
					);
				}else{
					$data[$new_key]=array (
						"tipe"					=> $this->input->post('tipe',true),
						"other_description"		=> $this->input->post('other_description',true),
					);
				}
				
				$this->session->set_userdata('agunan_data',$data);
				$this->session->set_userdata('agunan_key',$new_key);
			}
			$kirim['data'] = $data;
			
			$this->load->view('AcctCreditAccount/FormAddAcctCreditAgunan',$kirim);
		}
		
		public function agunanview(){
			$credits_account_id 	= $this->uri->segment(3);
			$detaildata=$this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$this->load->view('AcctCreditAccount/FormShowCreditAgunan',$detaildata);
		}
		
		public function polaangsuran(){
			$id=$this->uri->segment(3);
			$type=$this->uri->segment(4);
			if($type== '' && $type==0){
				$datapola=$this->flat($id);
			}else{
				$datapola=$this->slidingrate($id);
			}
			$data['main_view']['creditaccount']		= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($this->uri->segment(3));
			$data['main_view']['datapola']			= $datapola;
			$data['main_view']['content']			= 'AcctCreditAccount/FormPolaAngsuran_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function angsuran(){
			$id=$this->uri->segment(3);
			$type=$this->uri->segment(4);
			if($type== '' && $type==0){
				$datapola=$this->flat($id);
			}else{
				$datapola=$this->slidingrate($id);
			}
			
			$creditaccount	= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($this->uri->segment(3));
			redirect('credit-account/show-detail-data/'.$id.'/'.$type,compact('datapola'));
		}
		
		public function cekPolaAngsuran(){
			$id		= $this->input->post('id_credit',true);
			$pola	= $this->input->post('pola_angsuran',true);
			$url	= 'credit-account/angsuran/'.$id.'/'.$pola;
			redirect($url);
		}
		
		public const EPSILON = 1e-6;

		private static function checkZero(float $value, float $epsilon = self::EPSILON): float
		{
			return \abs($value) < $epsilon ? 0.0 : $value;
		}
		
		public static function fv(float $rate, int $periods, float $payment, float $present_value, bool $beginning = false): float
		{
			$when = $beginning ? 1 : 0;
	
			if ($rate == 0) {
				$fv = -($present_value + ($payment * $periods));
				return self::checkZero($fv);
			}
	
			$initial  = 1 + ($rate * $when);
			$compound = \pow(1 + $rate, $periods);
			$fv       = - (($present_value * $compound) + (($payment * $initial * ($compound - 1)) / $rate));
	
			return self::checkZero($fv);
		}
		
		public static function pmt(float $rate, int $periods, float $present_value, float $future_value = 0.0, bool $beginning = false): float
		{
			$when = $beginning ? 1 : 0;
	
			if ($rate == 0) {
				return - ($future_value + $present_value) / $periods;
			}
	
			return - ($future_value + ($present_value * \pow(1 + $rate, $periods)))
				/
				((1 + $rate * $when) / $rate * (\pow(1 + $rate, $periods) - 1));
		}
		
		public static function ipmt(float $rate, int $period, int $periods, float $present_value, float $future_value = 0.0, bool $beginning = false): float
		{
			if ($period < 1 || $period > $periods) {
				return \NAN;
			}
	
			if ($rate == 0) {
				return 0;
			}
	
			if ($beginning && $period == 1) {
				return 0.0;
			}
	
			$payment = self::pmt($rate, $periods, $present_value, $future_value, $beginning);
			if ($beginning) {
				$interest = (self::fv($rate, $period - 2, $payment, $present_value, $beginning) - $payment) * $rate;
			} else {
				$interest = self::fv($rate, $period - 1, $payment, $present_value, $beginning) * $rate;
			}
	
			return self::checkZero($interest);
		}
		
		public static function ppmt(float $rate, int $period, int $periods, float $present_value, float $future_value = 0.0, bool $beginning = false): float
		{
			$payment = self::pmt($rate, $periods, $present_value, $future_value, $beginning);
			$ipmt    = self::ipmt($rate, $period, $periods, $present_value, $future_value, $beginning);
	
			return $payment - $ipmt;
		}

		public function flat($id){
			$credistaccount				= $this->AcctCreditAccount_model->getCreditsAccount_Detail($id);
			$total_credits_account 		= $credistaccount['credits_account_amount'];
			$credits_account_interest 	= $credistaccount['credits_account_interest'];
			$credits_account_period 	= $credistaccount['credits_account_period'];
			$installment_pattern		= array();
			$opening_balance			= $total_credits_account;

			for($i=1; $i<=$credits_account_period; $i++){
				if($credistaccount['credits_payment_period'] == 2){
					$a = $i * 7;
					$tanggal_angsuran							= date('d-m-Y', strtotime("+".$a." days", strtotime($credistaccount['credits_account_date'])));
				} else {
					$tanggal_angsuran							= date('d-m-Y', strtotime("+".$i." months", strtotime($credistaccount['credits_account_date'])));
				}
				$angsuran_pokok									= $credistaccount['credits_account_principal_amount'];				
				$angsuran_margin								= $credistaccount['credits_account_interest_amount'];				
				$angsuran 										= $angsuran_pokok + $angsuran_margin;
				$last_balance 									= $opening_balance - $angsuran_pokok;
				$installment_pattern[$i]['opening_balance']		= $opening_balance;
				$installment_pattern[$i]['ke'] 					= $i;
				$installment_pattern[$i]['tanggal_angsuran'] 	= $tanggal_angsuran;
				$installment_pattern[$i]['angsuran'] 			= $angsuran;
				$installment_pattern[$i]['angsuran_pokok']		= $angsuran_pokok;
				$installment_pattern[$i]['angsuran_bunga'] 		= $angsuran_margin;
				/*$installment_pattern[$i]['akumulasi_pokok'] 	= $totpokok;*/
				$installment_pattern[$i]['last_balance'] 		= $last_balance;
				$opening_balance 								= $last_balance;
			}
			return $installment_pattern;
		}

		public function slidingrate($id){
			$credistaccount				= $this->AcctCreditAccount_model->getCreditsAccount_Detail($id);
			$total_credits_account 		= $credistaccount['credits_account_amount'];
			$credits_account_interest 	= $credistaccount['credits_account_interest'];
			$credits_account_period 	= $credistaccount['credits_account_period'];
			$installment_pattern		= array();
			$opening_balance			= $total_credits_account;

			for($i=1; $i<=$credits_account_period; $i++){
				if($credistaccount['credits_payment_period'] == 2){
					$a = $i * 7;
					$tanggal_angsuran 							= date('d-m-Y', strtotime("+".$a." days", strtotime($credistaccount['credits_account_date'])));
				} else {
					$tanggal_angsuran 							= date('d-m-Y', strtotime("+".$i." months", strtotime($credistaccount['credits_account_date'])));
				}
				$angsuran_pokok									= $credistaccount['credits_account_amount']/$credits_account_period;				
				$angsuran_margin								= $opening_balance*$credits_account_interest/100;				
				$angsuran 										= $angsuran_pokok + $angsuran_margin;
				$last_balance 									= $opening_balance - $angsuran_pokok;
				$installment_pattern[$i]['opening_balance']		= $opening_balance;
				$installment_pattern[$i]['ke'] 					= $i;
				$installment_pattern[$i]['tanggal_angsuran'] 	= $tanggal_angsuran;
				$installment_pattern[$i]['angsuran'] 			= $angsuran;
				$installment_pattern[$i]['angsuran_pokok']		= $angsuran_pokok;
				$installment_pattern[$i]['angsuran_bunga'] 		= $angsuran_margin;
				$installment_pattern[$i]['last_balance'] 		= $last_balance;
				$opening_balance 								= $last_balance;
			}
			return $installment_pattern;
		}

		public function menurunharian($id){
			$credistaccount				= $this->AcctCreditAccount_model->getCreditsAccount_Detail($id);
			$total_credits_account 		= $credistaccount['credits_account_amount'];
			$credits_account_interest 	= $credistaccount['credits_account_interest'];
			$credits_account_period 	= $credistaccount['credits_account_period'];
			$installment_pattern		= array();
			$opening_balance			= $total_credits_account;
			
			return $installment_pattern;
		}
		
		public function rate1($nper, $pmt, $pv, $fv = 0.0, $type = 0, $guess = 0.1) {
			$rate = $guess;
			if (abs($rate) < FINANCIAL_PRECISION) {
				$y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
			} else {
				$f = exp($nper * log(1 + $rate));
				$y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
			}
			$y0 = $pv + $pmt * $nper + $fv;
			$y1 = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
			$i = $x0 = 0.0;
			$x1 = $rate;
			while ((abs($y0 - $y1) > FINANCIAL_PRECISION) && ($i < FINANCIAL_MAX_ITERATIONS)) {
				$rate = ($y1 * $x0 - $y0 * $x1) / ($y1 - $y0);
				$x0 = $x1;
				$x1 = $rate;
				if (abs($rate) < FINANCIAL_PRECISION) {
					$y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
				} else {
					$f = exp($nper * log(1 + $rate));
					$y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
				}
				$y0 = $y1;
				$y1 = $y;
				++$i;
			}
			return $rate;
		}

		public function rate4() {
			$nprest 	= $this->input->post('nprest', true);
			$vlrparc 	= $this->input->post('vlrparc', true);
			$vp 		= $this->input->post('vp', true);
			$guess 		= 0.25;
			$maxit 		= 100;
			$precision 	= 14;
			$check 		= 1;
			$guess 		= round($guess,$precision);
			for ($i=0 ; $i<$maxit ; $i++) {
				$divdnd 	= $vlrparc - ( $vlrparc * (pow(1 + $guess , -$nprest)) ) - ($vp * $guess);
				$divisor 	= $nprest * $vlrparc * pow(1 + $guess , (-$nprest - 1)) - $vp;
				$newguess 	= $guess - ( $divdnd / $divisor );
				$newguess 	= round($newguess, $precision);
				if ($newguess == $guess) {
					if($check == 1){
					echo $newguess;
					$check++;
					}
				} else {
					$guess = $newguess;
				}
			}
			echo null;
		}

		function rate3($nprest, $vlrparc, $vp, $guess = 0.25) {
			$maxit = 100;
			$precision = 14;
			$guess = round($guess,$precision);
			for ($i=0 ; $i<$maxit ; $i++) {
				$divdnd 	= $vlrparc - ( $vlrparc * (pow(1 + $guess , -$nprest)) ) - ($vp * $guess);
				$divisor 	= $nprest * $vlrparc * pow(1 + $guess , (-$nprest - 1)) - $vp;
				$newguess 	= $guess - ( $divdnd / $divisor );
				$newguess 	= round($newguess, $precision);
				if ($newguess == $guess) {
					return $newguess;
				} else {
					$guess = $newguess;
				}
			}
			return null;
		}
		
		public function anuitas($id){
			$creditsaccount = $this->AcctCreditAccount_model->getCreditsAccount_Detail($id);

			$pinjaman 		= $creditsaccount['credits_account_amount'];
			$bunga 			= $creditsaccount['credits_account_interest'] / 100;
			$period 		= $creditsaccount['credits_account_period'];

			$bungaA 		= pow((1 + $bunga), $period);
			$bungaB 		= pow((1 + $bunga), $period) - 1;
			$bAnuitas 		= ($bungaA / $bungaB);
			// $totangsuran 	= $pinjaman * $bunga * $bAnuitas;
			$totangsuran 	= round(($pinjaman*($bunga))+$pinjaman/$period);
			$rate			= $this->rate3($period, $totangsuran, $pinjaman);

			$sisapinjaman = $pinjaman;
			for ($i=1; $i <= $period ; $i++) {

				if($creditsaccount['credits_payment_period'] == 1){
					$tanggal_angsuran 	= date('d-m-Y', strtotime("+".$i." months", strtotime($creditsaccount['credits_account_date']))); 
				} else {
					$a = $i * 7;
					$tanggal_angsuran 	= date('d-m-Y', strtotime("+".$a." days", strtotime($creditsaccount['credits_account_date']))); 
				}
				
				$angsuranbunga 		= $sisapinjaman * $rate;
				$angsuranpokok 		= $totangsuran - $angsuranbunga;
				// $angsuranpokok		= $pinjaman * (($bunga)/(1-(1+$bunga)-$i));
				$sisapokok 			= $sisapinjaman - $angsuranpokok;

				$pola[$i]['ke']					= $i;
				$pola[$i]['tanggal_angsuran']	= $tanggal_angsuran;
				$pola[$i]['opening_balance']	= $sisapinjaman;
				$pola[$i]['angsuran']			= $totangsuran;
				$pola[$i]['angsuran_pokok']		= $angsuranpokok;
				$pola[$i]['angsuran_bunga']		= $angsuranbunga;
				$pola[$i]['last_balance']		= $sisapokok;

				$sisapinjaman = $sisapinjaman - $angsuranpokok;
			}
			return $pola;
		}
		
		function rate2($nper, $pmt, $pv, $fv = 0.0, $type = 0, $guess = 0.1) {
			$rate = $guess;
			if (abs($rate) < $this->FINANCIAL_PRECISION) {
				$y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
			} else {
				$f = exp($nper * log(1 + $rate));
				$y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
			}
			$y0 = $pv + $pmt * $nper + $fv;
			$y1 = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;

			// find root by secant method
			$i  = $x0 = 0.0;
			$x1 = $rate;
			while ((abs($y0 - $y1) > $this->FINANCIAL_PRECISION) && ($i < $this->FINANCIAL_MAX_ITERATIONS)) {
				$rate = ($y1 * $x0 - $y0 * $x1) / ($y1 - $y0);
				$x0 = $x1;
				$x1 = $rate;

				if (abs($rate) < $this->FINANCIAL_PRECISION) {
					$y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
				} else {
					$f = exp($nper * log(1 + $rate));
					$y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
				}

				$y0 = $y1;
				$y1 = $y;
				++$i;
			}
			return $rate;
		}  
		
		public function printPolaAngsuran(){
			$credits_account_id 	= $this->input->post('id_credit', true);
			$type					= $this->input->post('pola', true);
			if($type== '' && $type==1){
				$datapola=$this->flat($credits_account_id);
			}else if ($type==2){
				$datapola=$this->anuitas($credits_account_id);
			}else {
				$datapola=$this->slidingrate($credits_account_id);
			}

			$acctcreditsaccount		= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$paymenttype 			= $this->configuration->PaymentType();
			$paymentperiod 			= $this->configuration->CreditsPaymentPeriod();

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			
			$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(10, 10, 10, 10); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 9);

			// -----------------------------------------------------------------------------
			
			$tblheader = "
				<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
					<tr>
						<td style=\"text-align:center;\" width=\"100%\">
							<div style=\"font-size:14px\";><b>Pola Angsuran</b></div>
						</td>			
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>No. Pinjaman</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"40%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_serial']."</b></div>
						</td>	
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Alamat</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"40%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['member_address']."</b></div>
						</td>		
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Nama</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"40%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['member_name']."</b></div>
						</td>	
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Plafon</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"40%\">
							<div style=\"font-size:12px\";><b>: ".number_format($acctcreditsaccount['credits_account_amount'],2)."</b></div>
						</td>		
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Tipe Angsuran</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"40%\">
							<div style=\"font-size:12px\";><b>: ".$paymenttype[$acctcreditsaccount['payment_type_id']]."</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Jangka Waktu</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"40%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_period']." ".$paymentperiod[$acctcreditsaccount['credits_payment_period']]."</b></div>
						</td>			
	 				</tr>
	 			</table>
	 			<br><br>
			";
				
			$pdf->writeHTML($tblheader, true, false, false, false, '');

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
			    <tr>
			        <td width=\"5%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Ke</div></td>
			        <td width=\"12%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Tanggal Angsuran</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Saldo Pokok</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Pokok</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Bunga</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Total Angsuran</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Sisa Pokok</div></td>
			    </tr>				
			</table>";

			$no = 1;

			$tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">";
		
			foreach ($datapola as $key => $val) {
				$tbl3 .= "
					<tr>
				    	<td width=\"5%\"><div style=\"text-align: left;\">&nbsp; ".$val['ke']."</div></td>
				    	<td width=\"12%\"><div style=\"text-align: right;\">".tgltoview($val['tanggal_angsuran'], 2)." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['opening_balance'], 2)." &nbsp; </div></td>
				        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['angsuran_pokok'], 2)." &nbsp; </div></td>
				        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['angsuran_bunga'], 2)." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['angsuran'], 2)." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['last_balance'], 2)." &nbsp; </div></td>
				       	
				    </tr>
				";

				$no++;
				$totalpokok += $val['angsuran_pokok'];
				$totalmargin += $val['angsuran_bunga'];
				$total += $val['angsuran'];
			}

			$tbl4 = "
				<tr>
					<td colspan=\"3\"><div style=\"text-align: right;font-weight:bold\">Total</div></td>
					<td><div style=\"text-align: right;font-weight:bold\">".number_format($totalpokok, 2)."</div></td>
					<td><div style=\"text-align: right;font-weight:bold\">".number_format($totalmargin, 2)."</div></td>
					<td><div style=\"text-align: right;font-weight:bold\">".number_format($total, 2)."</div></td>
				</tr>							
			</table>";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			$filename = 'Pola_Angsuran_'.$acctcreditsaccount['credits_account_serial'].'.pdf';
			$pdf->Output($filename, 'I');
		}

		public function printScheduleCreditsPayment(){
			$credits_account_id 	= $this->uri->segment(3);
			$acctcreditsaccount		= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$paymenttype 			= $this->configuration->PaymentType();
			$paymentperiod 			= $this->configuration->CreditsPaymentPeriod();
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();			

			if($acctcreditsaccount['payment_type_id'] == '' || $acctcreditsaccount['payment_type_id'] == 1){
				$datapola=$this->flat($credits_account_id);
			}else if ($acctcreditsaccount['payment_type_id'] == 2){
				$datapola=$this->anuitas($credits_account_id);
			}else if($acctcreditsaccount['payment_type_id'] == 3){
				$datapola=$this->slidingrate($credits_account_id);
			}else if($acctcreditsaccount['payment_type_id'] == 4){
				$datapola=$this->menurunharian($credits_account_id);
			}
			
			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			
			$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(10, 10, 10, 10); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 9);
			
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tblheader = "
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
				<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
					<tr>
						<td style=\"text-align:center;\" width=\"100%\">
							<div style=\"font-size:14px\";><b>Jadwal Angsuran</b></div>
						</td>			
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>No. Pinjaman</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"45%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_serial']."</b></div>
						</td>

						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Jenis Pinjaman</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"50%\">
							<div style=\"font-size:12px\";><b>: ".$this->AcctCreditAccount_model->getAcctCreditsName($acctcreditsaccount['credits_id'])."</b></div>
						</td>		
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Nama</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"45%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['member_name']."</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Jangka Waktu</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"50%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_period']." ".$paymentperiod[$acctcreditsaccount['credits_payment_period']]."</b></div>
						</td>			
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Tipe Angsuran</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"45%\">
							<div style=\"font-size:12px\";><b>: ".$paymenttype[$acctcreditsaccount['payment_type_id']]."</b></div>
						</td>	
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Plafon</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"50%\">
							<div style=\"font-size:12px\";><b>: Rp.".number_format($acctcreditsaccount['credits_account_amount'])."</b></div>
						</td>			
	 				</tr>
	 			</table>
	 			<br><br>
			";
			
			$pdf->writeHTML($tblheader, true, false, false, false, '');

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
			    <tr>
			        <td width=\"5%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Ke</div></td>
			        <td width=\"12%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Tanggal Angsuran</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Saldo Pokok</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Pokok</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Bunga</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Total Angsuran</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Sisa Pokok</div></td>

			       
			    </tr>				
			</table>";

			$no 	= 1;
			$tbl2 	= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">";
		
			foreach ($datapola as $key => $val) {
				$roundAngsuran		= round($val['angsuran'],-3);
				$sisaRoundAngsuran 	= $val['angsuran'] - $roundAngsuran;
				$sumAngsuranBunga 	= $val['angsuran_bunga'] + $sisaRoundAngsuran;

				$tbl3 .= "
					<tr>
				    	<td width=\"5%\"><div style=\"text-align: left;\">&nbsp; ".$val['ke']."</div></td>
				    	<td width=\"12%\"><div style=\"text-align: right;\">".tgltoview($val['tanggal_angsuran'])." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['opening_balance'], 2)." &nbsp; </div></td>
				        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['angsuran_pokok'], 2)." &nbsp; </div></td>
				        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['angsuran_bunga'],2)." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['angsuran'],2)." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['last_balance'], 2)." &nbsp; </div></td>
				       	
				    </tr>	
				";

				$no++;
				$totalpokok 	+= $val['angsuran_pokok'];
				$totalmargin 	+= $val['angsuran_bunga'];
				$total 			+= $val['angsuran'];
			}

			$tbl4 = "
				<tr>
					<td colspan=\"3\"><div style=\"text-align: right;font-weight:bold\">Total</div></td>
					<td><div style=\"text-align: right;font-weight:bold\">".number_format($totalpokok, 2)."</div></td>
					<td><div style=\"text-align: right;font-weight:bold\">".number_format($totalmargin, 2)."</div></td>
					<td><div style=\"text-align: right;font-weight:bold\">".number_format($total, 2)."</div></td>
				</tr>							
			</table>";
			
			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			$filename = 'Jadwal_Angsuran_'.$acctcreditsaccount['credits_account_serial'].'.pdf';
			$pdf->Output($filename, 'I');
		}

		public function printScheduleCreditsPaymentMember(){
			$credits_account_id 	= $this->uri->segment(3);
			$acctcreditsaccount		= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$paymenttype 			= $this->configuration->PaymentType();
			$paymentperiod 			= $this->configuration->CreditsPaymentPeriod();
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();			

			if($acctcreditsaccount['payment_type_id'] == '' || $acctcreditsaccount['payment_type_id'] == 1){
				$datapola=$this->flat($credits_account_id);
			}else if ($acctcreditsaccount['payment_type_id'] == 2){
				$datapola=$this->anuitas($credits_account_id);
			}else if($acctcreditsaccount['payment_type_id'] == 3){
				$datapola=$this->slidingrate($credits_account_id);
			}else if($acctcreditsaccount['payment_type_id'] == 4){
				$datapola=$this->menurunharian($credits_account_id);
			}
			
			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			
			$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(10, 10, 10, 10); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 9);

			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tblheader = "
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
				<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
					<tr>
						<td style=\"text-align:center;\" width=\"100%\">
							<div style=\"font-size:14px\";><b>Jadwal Angsuran</b></div>
						</td>			
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>No. Pinjaman</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"45%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_serial']."</b></div>
						</td>

						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Jenis Pinjaman</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"50%\">
							<div style=\"font-size:12px\";><b>: ".$this->AcctCreditAccount_model->getAcctCreditsName($acctcreditsaccount['credits_id'])."</b></div>
						</td>		
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Nama</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"45%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['member_name']."</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Jangka Waktu</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"50%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_period']." ".$paymentperiod[$acctcreditsaccount['credits_payment_period']]."</b></div>
						</td>			
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Tipe Angsuran</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"45%\">
							<div style=\"font-size:12px\";><b>: ".$paymenttype[$acctcreditsaccount['payment_type_id']]."</b></div>
						</td>	
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Plafon</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"50%\">
							<div style=\"font-size:12px\";><b>: Rp.".number_format($acctcreditsaccount['credits_account_amount'])."</b></div>
						</td>			
	 				</tr>
	 			</table>
	 			<br><br>
			";
			
			$pdf->writeHTML($tblheader, true, false, false, false, '');

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
			    <tr>
			        <td width=\"5%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Ke</div></td>
			        <td width=\"12%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Tanggal Angsuran</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Saldo Pokok</div></td>
			    </tr>				
			</table>";

			$no = 1;

			$tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">";
		
			foreach ($datapola as $key => $val) {
				$tbl3 .= "
					<tr>
				    	<td width=\"5%\"><div style=\"text-align: left;\">&nbsp; ".$val['ke']."</div></td>
				    	<td width=\"12%\"><div style=\"text-align: right;\">".tgltoview($val['tanggal_angsuran'])." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['opening_balance'], 2)." &nbsp; </div></td>
				    </tr>
				";

				$no++;
				$totalpokok += $val['angsuran_pokok'];
				$totalmargin += $val['angsuran_bunga'];
				$total += $val['angsuran'];
			}

			$tbl4 = "						
			</table>";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			$filename = 'Jadwal_Angsuran_'.$acctcreditsaccount['credits_account_serial'].'.pdf';
			$pdf->Output($filename, 'I');
		}

		public function printPolaAngsuranCredits(){
			$credits_account_id 	= $this->uri->segment(3);
			$acctcreditsaccount		= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$paymenttype 			= $this->configuration->PaymentType();
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();		
			$paymentperiod 			= $this->configuration->CreditsPaymentPeriod();	

			if($acctcreditsaccount['payment_type_id'] == '' || $acctcreditsaccount['payment_type_id'] == 1){
				$datapola=$this->flat($credits_account_id);
			}else if ($acctcreditsaccount['payment_type_id'] == 2){
				$datapola=$this->anuitas($credits_account_id);
			}

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			
			$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(10, 10, 10, 10);

			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 9);

			// -----------------------------------------------------------------------------

			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tblheader = "
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
				<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
					<tr>
						<td style=\"text-align:center;\" width=\"100%\">
							<div style=\"font-size:14px\";><b>Pola Angsuran</b></div>
						</td>			
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>No. Pinjaman</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"45%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_serial']."</b></div>
						</td>

						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Jenis Pinjaman</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"50%\">
							<div style=\"font-size:12px\";><b>: ".$this->AcctCreditAccount_model->getAcctCreditsName($acctcreditsaccount['credits_id'])."</b></div>
						</td>		
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Nama</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"45%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['member_name']."</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Jangka Waktu</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"50%\">
							<div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_period']." ".$paymentperiod[$acctcreditsaccount['credits_payment_period']]."</b></div>
						</td>			
	 				</tr>
	 				<tr>
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Tipe Angsuran</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"45%\">
							<div style=\"font-size:12px\";><b>: ".$paymenttype[$acctcreditsaccount['payment_type_id']]."</b></div>
						</td>	
						<td style=\"text-align:left;\" width=\"20%\">
							<div style=\"font-size:12px\";><b>Plafon</b></div>
						</td>
						<td style=\"text-align:left;\" width=\"50%\">
							<div style=\"font-size:12px\";><b>: Rp.".number_format($acctcreditsaccount['credits_account_amount'])."</b></div>
						</td>			
	 				</tr>
	 			</table>
	 			<br><br>
			";
				
			$pdf->writeHTML($tblheader, true, false, false, false, '');

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
			    <tr>
			        <td width=\"5%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Ke</div></td>
			        <td width=\"12%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Tanggal Angsuran</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Saldo Pokok</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Pokok</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Bunga</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Total Angsuran</div></td>
			        <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Sisa Pokok</div></td>

			       
			    </tr>				
			</table>";

			$no = 1;

			$tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">";
		
			foreach ($datapola as $key => $val) {
				$tbl3 .= "
					<tr>
				    	<td width=\"5%\"><div style=\"text-align: left;\">&nbsp; ".$val['ke']."</div></td>
				    	<td width=\"12%\"><div style=\"text-align: right;\">".tgltoview($val['tanggal_angsuran'])." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['opening_balance'], 2)." &nbsp; </div></td>
				        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['angsuran_pokok'], 2)." &nbsp; </div></td>
				        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['angsuran_bunga'], 2)." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['angsuran'], 2)." &nbsp; </div></td>
				        <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['last_balance'], 2)." &nbsp; </div></td>
				       	
				    </tr>
				";

				$no++;
				$totalpokok += $val['angsuran_pokok'];
				$totalmargin += $val['angsuran_bunga'];
				$total += $val['angsuran'];
			}

			$tbl4 = "
				<tr>
					<td colspan=\"3\"><div style=\"text-align: right;font-weight:bold\">Total</div></td>
					<td><div style=\"text-align: right;font-weight:bold\">".number_format($totalpokok, 2)."</div></td>
					<td><div style=\"text-align: right;font-weight:bold\">".number_format($totalmargin, 2)."</div></td>
					<td><div style=\"text-align: right;font-weight:bold\">".number_format($total, 2)."</div></td>
				</tr>							
			</table>";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			$filename = 'Pola_Angsuran_'.$acctcreditsaccount['credits_account_serial'].'.pdf';
			$pdf->Output($filename, 'I');
		}
		
		public function processPrintingAkad(){
			$credits_account_id			= $this->uri->segment(3);
			$memberidentity				= $this->configuration->MemberIdentity();
			$dayname 					= $this->configuration->DayName();
			$monthname 					= $this->configuration->Month();
			$acctcreditsaccount			= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($credits_account_id);
			$acctcreditsagunan			= $this->AcctCreditAccount_model->getAcctCreditsAgunan_Detail($credits_account_id);
			$preferencecompany			= $this->AcctCreditAccount_model->getPreferenceCompany();
			$existedcreditsaccount		= $this->AcctCreditAccount_model->getExistedCreditsAccount($acctcreditsaccount['member_id'], $acctcreditsaccount['credits_account_id']);

			$date 	= date('d', (strtotime($acctcreditsaccount['credits_account_date'])));
			$day 	= date('D', (strtotime($acctcreditsaccount['credits_account_date'])));
			$month 	= date('m', (strtotime($acctcreditsaccount['credits_account_date'])));
			$year 	= date('Y', (strtotime($acctcreditsaccount['credits_account_date'])));

			$acctcreditsagunan 			= $this->AcctCreditAccount_model->getAcctCreditsAgunan_Detail($credits_account_id);

			$total_agunan = 0;
			foreach ($acctcreditsagunan as $key => $val) {
				if($val['credits_agunan_type'] == 1){
					$agunanbpkb[] = array (
						'credits_agunan_bpkb_nama'				=> $val['credits_agunan_bpkb_nama'],
						'credits_agunan_bpkb_nomor'				=> $val['credits_agunan_bpkb_nomor'],
						'credits_agunan_bpkb_no_mesin'			=> $val['credits_agunan_bpkb_no_mesin'],
						'credits_agunan_bpkb_no_rangka'			=> $val['credits_agunan_bpkb_no_rangka'],		
					);
				} else if($val['credits_agunan_type'] == 2){
					$agunansertifikat[] = array (
						'credits_agunan_shm_no_sertifikat'		=> $val['credits_agunan_shm_no_sertifikat'],
						'credits_agunan_shm_luas'				=> $val['credits_agunan_shm_luas'],
						'credits_agunan_shm_atas_nama'			=> $val['credits_agunan_shm_atas_nama'],
					);
				}else if($val['credits_agunan_type'] == 7){
					$agunanatmjamsostek[] = array (
						'credits_agunan_atmjamsostek_nomor'			=> $val['credits_agunan_atmjamsostek_nomor'],
						'credits_agunan_atmjamsostek_nama'			=> $val['credits_agunan_atmjamsostek_nama'],
						'credits_agunan_atmjamsostek_bank'			=> $val['credits_agunan_atmjamsostek_bank'],
						'credits_agunan_atmjamsostek_keterangan'	=> $val['credits_agunan_atmjamsostek_keterangan'],
					);
				}
				$total_agunan = $total_agunan + $val['credits_agunan_bpkb_taksiran'] + $val['credits_agunan_shm_taksiran'] + $val['credits_agunan_atmjamsostek_taksiran'];
			}

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			
			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);
			$pdf->SetMargins(20, 20, 20, 0); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 12);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 12);

			$paymenttype 			= $this->configuration->PaymentType();
			$akad_payment_period 	= $this->configuration->CreditsPaymentPeriodAkad();
			$monthname				= $this->configuration->Month();
			$dayList				= $this->configuration->DayList();
			$month 					= date('m', (strtotime($acctcreditsaccount['credits_account_date'])));
			$day 					= date('d', (strtotime($acctcreditsaccount['credits_account_date'])));
			$year 					= date('Y', (strtotime($acctcreditsaccount['credits_account_date'])));
			$month_due 				= date('m', (strtotime($acctcreditsaccount['credits_account_due_date'])));
			$day_due 				= date('d', (strtotime($acctcreditsaccount['credits_account_due_date'])));
			$year_due				= date('Y', (strtotime($acctcreditsaccount['credits_account_due_date'])));
			$total_administration	= $acctcreditsaccount['credits_account_provisi'] + $acctcreditsaccount['credits_account_komisi'] + $acctcreditsaccount['credits_account_insurance'] + $acctcreditsaccount['credits_account_materai'] + $acctcreditsaccount['credits_account_risk_reserve'] + $acctcreditsaccount['credits_account_stash'] + $acctcreditsaccount['credits_account_adm_cost'] + $acctcreditsaccount['credits_account_principal'];
			$pencairan				= $acctcreditsaccount['credits_account_amount'] - $total_administration;
			
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"300%\" height=\"300%\"/>";

			$tbl1 = "
				<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
					<table>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px; font-weight:bold\">KOPERASI MENJANGAN ENAM</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Badan Hukum No. 9297b/BH/PAD/KWK/II/1999</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Jl. Simongan 131 Semarang Telp (024) 76630034</div>
							</td>
						</tr>
					</table>
					<br>
					<br>
					<table>
						<tr>
							<td style=\"text-align:center;\" width=\"100%\">
								<div style=\"font-size:14px; font-weight:bold\"><u>PERJANJIAN PINJAMAN UANG</u></div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:center;\" width=\"100%\">
								<div style=\"font-size:14px; font-weight:bold\">No. : ".$acctcreditsaccount['credits_account_serial']."</div>
							</td>
						</tr>
					</table>
						<br>
						<br>
						<br>
					<table>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Yang bertandatangan dibawah ini saya :</div>
							</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">NAMA</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\">".$acctcreditsaccount['member_name']."</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">NO.AGT / BAGIAN</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\">".$acctcreditsaccount['member_no']." / ".$acctcreditsaccount['division_name']."</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">mengajukan permohonan pinjaman uang sebesar <b>Rp.".number_format($acctcreditsaccount['credits_account_amount'], 2)."</b></div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">(".numtotxt($acctcreditsaccount['credits_account_amount']).")</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">untuk Keperluan : ...........................</div>
							</td>
						</tr>
						<br>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Saya sanggup mengangsur selama ".$acctcreditsaccount['credits_account_period']." bulan dengan bunga ".number_format($acctcreditsaccount['credits_account_interest'], 2)."% ".$paymenttype[$acctcreditsaccount['payment_type_id']].".</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Besar angsuran per bulan Rp. ".number_format($acctcreditsaccount['credits_account_payment_amount'], 2)." (termasuk bunga)</div>
							</td>
						</tr>
						<br>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Dengan ini saya memberikan kuasa kepada Bendahara Gaji (SDM PT Phapros, TBK) untuk membayar angsuran melalui pemotongan gaji.</div>
							</td>
						</tr>
						<br>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Apabila terjadi Pemutusan Hubungan Kerja maka seluruh/sebagian Tunjangan, Pesangon, Tunjangan Hari Tua dan Segala Hak Keuangan lain dari PT. Phapros / Koperasi akan digunakan untuk Pembayaran Kewajiban kepada Koperasi sampai dengan dinyatakan LUNAS oleh Koperasi.</div>
							</td>
						</tr>
						<br>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:center;\" width=\"25%\">Ketua KME</td>
							<td style=\"text-align:left;\" width=\"30%\"></td>
							<td style=\"text-align:center;\" width=\"25%\">Semarang, ".date("d-m-Y")."</td>
							<td style=\"text-align:left;\" width=\"10%\"></td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"65%\"></td>
							<td style=\"text-align:center;\" width=\"25%\">Pemohon</td>
							<td style=\"text-align:left;\" width=\"10%\"></td>
						</tr>
						<br>
						<br>
						<br>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:center;\" width=\"25%\">...........................</td>
							<td style=\"text-align:left;\" width=\"30%\"></td>
							<td style=\"text-align:center;\" width=\"25%\">".$acctcreditsaccount['member_name']."</td>
							<td style=\"text-align:left;\" width=\"10%\"></td>
						</tr>
					</table>
				</table>
			";

			$tbl2 = "
				<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
					<table>
						<tr>
							<td style=\"text-align:center;\" width=\"100%\">
								<div style=\"font-size:12px; font-weight:bold\">KOPERASI MENJANGAN ENAM</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:center;\" width=\"100%\">
								<div style=\"font-size:12px;\">Jl. Simongan No. 131 Semarang</div>
							</td>
						</tr>
					</table>
					<br>
					<hr>
					<br>
					<table>
						<br>
						<br>
						<tr>
							<td style=\"text-align:center;\" width=\"100%\">
								<div style=\"font-size:14px; font-weight:bold\">PENGAJUAN PINJAMAN KREDIT BARANG</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:center;\" width=\"100%\">
								<div style=\"font-size:14px; font-weight:bold\">No. : ".$acctcreditsaccount['credits_account_serial']."</div>
							</td>
						</tr>
					</table>
						<br>
						<br>
					<table>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Saya yang bertandatangan dibawah ini :</div>
							</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">Nama</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\">".$acctcreditsaccount['member_name']."</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">Bagian</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\">".$acctcreditsaccount['division_name']."</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">No. Anggota</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\">".$acctcreditsaccount['member_no']."</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">Status</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>";
							if($acctcreditsaccount['member_active_status'] == 0){
								$tbl2 .= "<td style=\"text-align:left;\" width=\"63%\">Aktif</td>";
							}else{
								$tbl2 .= "<td style=\"text-align:left;\" width=\"63%\">Tidak Aktif</td>";
							}

						$tbl2 .= "
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">Alamat</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\">".$acctcreditsaccount['member_address']."</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Mengajukan pinjaman kredit barang berupa :</div>
							</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">Nama Barang</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\"></td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">Merk Barang</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\"></td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">Harga Barang</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\"></td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">Uang Muka</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\"></td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:left;\" width=\"25%\">Diangsur</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"63%\">X</td>
						</tr>
						<br>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Sisa pinjaman saya sebagai berikut :</div>
							</td>
						</tr>
					</table>
					<table width=\"100%\" cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td style=\"text-align:center;\" width=\"10%\">
								<div style=\"font-size:12px;\">No</div>
							</td>
							<td style=\"text-align:center;\" width=\"20%\">
								<div style=\"font-size:12px;\">Jenis Pinjaman</div>
							</td>
							<td style=\"text-align:center;\" width=\"20%\">
								<div style=\"font-size:12px;\">Angsuran / Bulan</div>
							</td>
							<td style=\"text-align:center;\" width=\"10%\">
								<div style=\"font-size:12px;\">Jml Pot</div>
							</td>
							<td style=\"text-align:center;\" width=\"10%\">
								<div style=\"font-size:12px;\">X</div>
							</td>
							<td style=\"text-align:center;\" width=\"20%\">
								<div style=\"font-size:12px;\">Sisa</div>
							</td>
							<td style=\"text-align:center;\" width=\"10%\">
								<div style=\"font-size:12px;\">Paraf</div>
							</td>
						</tr>";

						$no_ex			= 1;
						$jumlah_ang_ex 	= 0;
						$jumlah_pot_ex 	= 0;
						$jumlah_x_ex 	= 0;
						$jumlah_sisa_ex = 0;
						$datarow		= count($existedcreditsaccount)+1;
						
						foreach($existedcreditsaccount as $key => $val){
							$tbl2 .= "
							<tr>
								<td style=\"text-align:center;\" width=\"10%\">
									<div style=\"font-size:12px;\">".$no_ex."</div>
								</td>
								<td style=\"text-align:center;\" width=\"20%\">
									<div style=\"font-size:12px;\">".$acctcreditsaccount['credits_name']."</div>
								</td>
								<td style=\"text-align:right;\" width=\"20%\">
									<div style=\"font-size:12px;\">".number_format($acctcreditsaccount['credits_account_payment_amount'], 2)."</div>
								</td>
								<td style=\"text-align:right;\" width=\"10%\">
									<div style=\"font-size:12px;\">".$acctcreditsaccount['']."</div>
								</td>
								<td style=\"text-align:center;\" width=\"10%\">
									<div style=\"font-size:12px;\">".$acctcreditsaccount['credits_account_period']."</div>
								</td>
								<td style=\"text-align:right;\" width=\"20%\">
									<div style=\"font-size:12px;\">".number_format($acctcreditsaccount['credits_account_last_balance'], 2)."</div>
								</td>";
								if($no_ex == 1){
									$tbl2 .=" <td style=\"text-align:center;\" rowspan=\"".$datarow."\" width=\"10%\">
										<div style=\"font-size:12px;\">".$acctcreditsaccount['']."</div>
									</td>";
								}
							$tbl2 .=" </tr>";
							$no_ex++;
							$jumlah_ang_ex 	+= $acctcreditsaccount['credits_account_payment_amount'];
							$jumlah_pot_ex 	+= $acctcreditsaccount[''];
							$jumlah_x_ex 	+= $acctcreditsaccount[''];
							$jumlah_sisa_ex += $acctcreditsaccount['credits_account_last_balance'];
						}

						$tbl2 .= "
						<tr>
							<td style=\"text-align:center;\" width=\"10%\"></td>
							<td style=\"text-align:center;\" width=\"20%\">
								<div style=\"font-size:12px; font-weight:bold;\">Jumlah</div>
							</td>
							<td style=\"text-align:right;\" width=\"20%\">
								<div style=\"font-size:12px; font-weight:bold;\">".number_format($jumlah_ang_ex, 2)."</div>
							</td>
							<td style=\"text-align:center;\" width=\"10%\"></td>
							<td style=\"text-align:center;\" width=\"10%\"></td>
							<td style=\"text-align:right;\" width=\"20%\">
								<div style=\"font-size:12px; font-weight:bold;\">".number_format($jumlah_sisa_ex, 2)."</div>
							</td>
						</tr>
					</table>
					<table>
						<br>
						<br>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Saya bersedia mentaati ketentuan / peraturan yang berlaku.</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Demikian pengajuan pinjaman kredit barang ini saya buat, terima kasih.</div>
							</td>
						</tr>
						<br>
						<tr>
							<td style=\"text-align:left;\" width=\"67%\"></td>
							<td style=\"text-align:left;\" width=\"33%\">
								<div style=\"font-size:12px;\">Semarang, ".date('d-m-Y')."</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"48%\">
								<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\">
								<tr>
									<td style=\"text-align:center;\" width=\"30%\">
										<div style=\"font-size:12px;\">Pemohon</div>
									</td>
									<td style=\"text-align:center;\" width=\"40%\">
										<div style=\"font-size:12px;\">Mengetahui</div>
									</td>
									<td style=\"text-align:center;\" width=\"30%\">
										<div style=\"font-size:12px;\">Menyetujui</div>
									</td>
								</tr>
								<tr>
									<td style=\"height:60px !important;\"></td>
									<td style=\"height:60px !important;\"></td>
									<td style=\"height:60px !important;\"></td>
								</tr>
								<tr>
									<td style=\"text-align:center;\" width=\"30%\">
										<div style=\"font-size:12px;\">".$acctcreditsaccount['member_name']."</div>
									</td>
									<td style=\"text-align:center;\" width=\"40%\">
										<div style=\"font-size:12px;\">Manager</div>
									</td>
									<td style=\"text-align:center;\" width=\"30%\">
										<div style=\"font-size:12px;\">Pengurus</div>
									</td>
								</tr>
								</table>
							</td>
							<td style=\"text-align:left;\" width=\"4%\"></td>
							<td style=\"text-align:left;\" width=\"48%\">
								<div style=\"font-size:12px; font-weight:bold;\"><u>Note :</u></div>
							</td>
						</tr>
						<hr>
					</table>
					<table>
						<br>
						<br>
						<br>
						<tr>
							<td style=\"text-align:center;\" width=\"100%\">
								<div style=\"font-size:12px;\">TANDA TERIMA BARANG</div>
							</td>
						</tr>
						<br>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Telah terima barang berupa :</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"7%\"></td>
							<td style=\"text-align:left;\" width=\"18%\">
								<div style=\"font-size:12px;\">Nama Barang</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">
								<div style=\"font-size:12px;\">:</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"7%\"></td>
							<td style=\"text-align:left;\" width=\"18%\">
								<div style=\"font-size:12px;\">Merk Barang</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">
								<div style=\"font-size:12px;\">:</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"7%\"></td>
							<td style=\"text-align:left;\" width=\"18%\">
								<div style=\"font-size:12px;\">Harga Barang</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">
								<div style=\"font-size:12px;\">:</div>
							</td>
							<td style=\"text-align:left;\" width=\"73%\">
								<div style=\"font-size:12px;\">Rp.</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"7%\"></td>
							<td style=\"text-align:left;\" width=\"18%\">
								<div style=\"font-size:12px;\">Angsuran Per Bulan</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">
								<div style=\"font-size:12px;\">:</div>
							</td>
							<td style=\"text-align:left;\" width=\"73%\">
								<div style=\"font-size:12px;\">Rp.</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"7%\"></td>
							<td style=\"text-align:right;\" width=\"18%\">
								<div style=\"font-size:12px;\">Pokok </div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">
								<div style=\"font-size:12px;\">:</div>
							</td>
							<td style=\"text-align:left;\" width=\"73%\">
								<div style=\"font-size:12px;\">Rp.</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"7%\"></td>
							<td style=\"text-align:right;\" width=\"18%\">
								<div style=\"font-size:12px;\">Bunga </div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">
								<div style=\"font-size:12px;\">:</div>
							</td>
							<td style=\"text-align:left;\" width=\"73%\">
								<div style=\"font-size:12px;\">Rp.</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"7%\"></td>
							<td style=\"text-align:right;\" width=\"18%\">
								<div style=\"font-size:12px;\">Pot </div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">
								<div style=\"font-size:12px;\">:</div>
							</td>
							<td style=\"text-align:left;\" width=\"73%\">
								<div style=\"font-size:12px;\">..........X</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"70%\"></td>
							<td style=\"text-align:left;\" width=\"30%\">
								<div style=\"font-size:12px;\">Semarang, ".date('d-m-Y')."</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"70%\"></td>
							<td style=\"text-align:left;\" width=\"30%\">
								<div style=\"font-size:12px;\">Hormat saya,</div>
							</td>
						</tr>
						<br>
						<br>
						<br>
						<tr>
							<td style=\"text-align:left;\" width=\"70%\"></td>
							<td style=\"text-align:center;\" width=\"30%\">
								<div style=\"font-size:12px;\">(".$acctcreditsaccount['member_name'].")</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"70%\"></td>
							<td style=\"text-align:center;\" width=\"30%\">
								<div style=\"font-size:12px;\">Pemohon</div>
							</td>
						</tr>
					</table>
				</table>
			";

			$tbl3 = "
				<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
					<table>
						<tr>
			    			<td rowspan=\"3\" width=\"10%\">".$img."</td>
							<td style=\"text-align:left;\" width=\"85%\">
								<div style=\"font-size:12px; font-weight:bold\">KOPERASI</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"85%\">
								<div style=\"font-size:12px; font-weight:bold\">MENJANGAN ENAM</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:right;\" width=\"90%\">
								<div style=\"font-size:12px; font-weight:bold\">SIM / STNK</div>
							</td>
						</tr>
					</table>
					<br>
					<hr>
					<br>
						<br>
						<br>
						<br>
					<table>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"25%\">Nama</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"73%\">".$acctcreditsaccount['member_name']."</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"25%\">No. Anggota</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"73%\">".$acctcreditsaccount['member_no']."</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"25%\">Bagian</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"73%\">".$acctcreditsaccount['division_name']."</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"25%\">Tlp. Ext / Rmh / HP</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"73%\">".$acctcreditsaccount['member_phone']."</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"25%\">No. Polisi</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"73%\"></td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"25%\">Atas Nama</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"73%\"></td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"25%\">Potong (kali / bulan)</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"73%\">".$acctcreditsaccount['credits_account_period']."</td>
						</tr>
						<hr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Keperluan (lingkari) :</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"95%\">
								<div style=\"font-size:12px;\">1. Baru / Perpanjangan SIM : A / B / B1 / B Umum / C</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"95%\">
								<div style=\"font-size:12px;\">2. STNK Kendaraan Roda 2 / 4 : Perpanjangan / Mutasi / Balik Nama</div>
							</td>
						</tr>
						<hr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Berkas (lingkari) :</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">1. BPKB</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Asli / Copy</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">2. STNK</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Asli / Copy</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">3. KTP a.n. : </div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Asli / Copy</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">4. SIM : A / B / B1 / B Umum / C</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Asli / Copy</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">5. Pas Photo : </div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Asli / Copy</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">6. Faktur Penjualan : </div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Asli / Copy</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">7. Lain - lain</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> .........................................................................................</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"50%\"></td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> ..........................................................................................</div>
							</td>
						</tr>
						<hr>
						<tr>
							<td style=\"text-align:left;\" width=\"100%\">
								<div style=\"font-size:12px;\">Biaya (lingkari) :</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">1. Pajak</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Rp. ..................................................................................</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">2. Kendaraan</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Rp. ..................................................................................</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">3. Cek Fisik</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Rp. ..................................................................................</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">4. Acc : </div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Rp. ..................................................................................</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">5. Photo Copy</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Rp. ..................................................................................</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">6. Biaya Pengurusan</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Rp. ..................................................................................</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">7. Lain - lain</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Rp. ".number_format($acctcreditsaccount['credits_account_insurance'], 2)."</div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"5%\"></td>
							<td style=\"text-align:left;\" width=\"43%\">
								<div style=\"font-size:12px;\">8. Jasa Koperasi Menjangan Enam</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> <u>Rp. ".number_format($acctcreditsaccount['credits_account_adm_cost'], 2)."</u></div>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:left;\" width=\"24%\"></td>
							<td style=\"text-align:left;\" width=\"24%\">
								<div style=\"font-size:12px;\">Jumlah Biaya</div>
							</td>
							<td style=\"text-align:left;\" width=\"2%\">:</td>
							<td style=\"text-align:left;\" width=\"50%\">
								<div style=\"font-size:12px;\"> Rp. ".number_format(($acctcreditsaccount['credits_account_insurance']+$acctcreditsaccount['credits_account_adm_cost']), 2)."</div>
							</td>
						</tr>
					</table>
					<table>
						<br>
						<br>
						<br>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:left;\" width=\"60%\"></td>
							<td style=\"text-align:center;\" width=\"15%\">Semarang</td>
							<td style=\"text-align:left;\" width=\"25%\"></td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:center;\" width=\"25%\">Pemohon,</td>
							<td style=\"text-align:left;\" width=\"10%\"></td>
							<td style=\"text-align:center;\" width=\"25%\">Petugas,</td>
							<td style=\"text-align:left;\" width=\"20%\"></td>
							<td style=\"text-align:center;\" width=\"20%\">Kembali,</td>
						</tr>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:center;\" width=\"80%\"></td>
							<td style=\"text-align:left;\" width=\"20%\">Tgl: </td>
						</tr>
						<br>
						<br>
						<br>
						<tr style=\"font-size:12px;\">
							<td style=\"text-align:center;\" width=\"25%\">".$acctcreditsaccount['member_name']."</td>
						</tr>
					</table>
				</table>
			";

			if($acctcreditsaccount['credits_id'] == 1 || $acctcreditsaccount['credits_id'] == 4){
				$pdf->writeHTML($tbl1, true, false, false, false, '');
			}else if($acctcreditsaccount['credits_id'] == 2){
				$pdf->writeHTML($tbl2, true, false, false, false, '');
			}else if($acctcreditsaccount['credits_id'] == 3){
				$pdf->writeHTML($tbl3, true, false, false, false, '');
			}

			ob_clean();

			$filename = 'Akad_'.$credits_name.'_'.$acctcreditsaccount['member_name'].'.pdf';
			$pdf->Output($filename, 'I');
		}
	}
?>
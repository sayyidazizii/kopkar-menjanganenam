<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctSavings extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctSavings_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data['main_view']['acctsavings']				= $this->AcctSavings_model->getDataAcctSavings();
			$data['main_view']['savingsprofitsharing']		= $this->configuration->SavingsProfitSharing();	
			$data['main_view']['content']					= 'AcctSavings/ListAcctSavings_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function addAcctSavings(){
			$data['main_view']['savingsprofitsharing']		= $this->configuration->SavingsProfitSharing();
			$data['main_view']['accountstatus']				= $this->configuration->AccountStatus();
			$data['main_view']['kelompokperkiraan']			= $this->configuration->KelompokPerkiraan();
			$data['main_view']['acctaccount']				= create_double($this->AcctSavings_model->getAcctAccount(),'account_id', 'account_code');
			$data['main_view']['content']					= 'AcctSavings/FormAddAcctSavings_view';
			$this->load->view('MainPage_view',$data);
		}

		public function function_state_add(){
			$unique 	= $this->session->userdata('unique');
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctsavings-'.$unique['unique']);
			$sessions['active_tab'] = $value;
			$this->session->set_userdata('addacctsavings-'.$unique['unique'],$sessions);
		}
		
		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctsavings-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addacctsavings-'.$unique['unique'],$sessions);
		}

		public function reset_data(){
			$unique 	= $this->session->userdata('unique');
			$sessions	= $this->session->unset_userdata('addacctsavings-'.$unique['unique']);
			redirect('savings/add');
		}

		public function processAddAcctAccount(){
			$auth = $this->session->userdata('auth');
			$unique 	= $this->session->userdata('unique');

			$data = array(
				'account_code'		=> $this->input->post('account_code', true),
				'account_name'		=> $this->input->post('account_name', true),
				'account_type_id'	=> $this->input->post('account_type_id', true),
				'account_group'		=> $this->input->post('account_group', true),
				'created_id'		=> $auth['user_id'],
				'created_on'		=> date('Y-m-d H:i:s'),
			);
			
			if($this->AcctSavings_model->insertAcctAccount($data)){
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>  
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Tambah Data Perkiraan Sukses
						</div> ";

				$this->session->unset_userdata('addacctsavings-'.$unique['unique']);
				$this->session->set_userdata('message',$msg);
				$this->session->set_userdata('message',$msg);
				redirect('savings/add');
			}else{
				$this->session->set_userdata('addacctsavings',$data);
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Tambah Data Perkiraan Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('savings/add');
			}
		}
		
		public function processAddAcctSavings(){
			$auth = $this->session->userdata('auth');

			$data = array(
				'savings_code'				=> $this->input->post('savings_code', true),
				'savings_name'				=> $this->input->post('savings_name', true),
				'account_id'				=> $this->input->post('account_id', true),
				'account_basil_id'			=> $this->input->post('account_basil_id', true),
				'savings_profit_sharing'	=> $this->input->post('savings_profit_sharing', true),
				'savings_interest_rate'		=> $this->input->post('savings_interest_rate', true),
				'created_id'				=> $auth['user_id'],
				'created_on'				=> date('Y-m-d H:i:s'),
			);
			
			$this->form_validation->set_rules('savings_code', 'Kode Simpanan', 'required');
			$this->form_validation->set_rules('savings_name', 'Nama Simpanan', 'required');
			$this->form_validation->set_rules('account_id', 'Nomor Perkiraan Simpanan', 'required');
			$this->form_validation->set_rules('account_basil_id', 'Nomor Perkiraan Bunga Simpanan', 'required');
			
			if($this->form_validation->run()==true){
				if($this->AcctSavings_model->insertAcctSavings($data)){
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Kode Simpanan Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctsavings-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('savings/add');
				}else{
					$this->session->set_userdata('addacctsavings',$data);
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Kode Simpanan Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('savings/add');
				}
			}else{
				$this->session->set_userdata('addacctsavings',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('savings/add');
			}
		}
		
		public function editAcctSavings(){
			$data['main_view']['savingsprofitsharing']		= $this->configuration->SavingsProfitSharing();
			$data['main_view']['accountstatus']				= $this->configuration->AccountStatus();
			$data['main_view']['kelompokperkiraan']			= $this->configuration->KelompokPerkiraan();
			$data['main_view']['acctaccount']				= create_double($this->AcctSavings_model->getAcctAccount(),'account_id', 'account_code');
			$data['main_view']['acctsavings']				= $this->AcctSavings_model->getAcctSavings_Detail($this->uri->segment(3));
			$data['main_view']['content']					= 'AcctSavings/FormEditAcctSavings_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function processEditAcctSavings(){
			$data = array(
				'savings_id'				=> $this->input->post('savings_id', true),
				'savings_code'				=> $this->input->post('savings_code', true),
				'savings_name'				=> $this->input->post('savings_name', true),
				'account_id'				=> $this->input->post('account_id', true),
				'account_basil_id'			=> $this->input->post('account_basil_id', true),
				'savings_profit_sharing'	=> $this->input->post('savings_profit_sharing', true),
				'savings_interest_rate'		=> $this->input->post('savings_interest_rate', true),
			);
			
			$this->form_validation->set_rules('savings_code', 'Kode Simpanan', 'required');
			$this->form_validation->set_rules('savings_name', 'Nama Simpanan', 'required');
			$this->form_validation->set_rules('account_id', 'Nomor Perkiraan', 'required');
			
			if($this->form_validation->run()==true){
				if($this->AcctSavings_model->updateAcctSavings($data)){
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Kode Simpanan Sukses
							</div> ";

					$this->session->set_userdata('message',$msg);
					redirect('savings/edit/'.$data['savings_id']);
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Kode Simpanan Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('savings/edit/'.$data['savings_id']);
				}
			}else{
				$this->session->set_userdata('editmachine',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('savings/edit/'.$data['savings_id']);
			}				
		}
		
		public function deleteAcctSavings(){
			if($this->AcctSavings_model->deleteAcctSavings($this->uri->segment(3))){
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>                 
							Hapus Data Kode Simpanan Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('savings');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>                
							Hapus Data Kode Simpanan Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('savings');
			}
		}
	}
?>
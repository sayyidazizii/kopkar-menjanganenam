<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctDebtCategory extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctDebtCategory_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data['main_view']['acctdebtcategory']			= $this->AcctDebtCategory_model->getAcctDebtCategory();
			$data['main_view']['content']					= 'AcctDebtCategory/ListAcctDebtCategory_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function addAcctDebtCategory(){
			$data['main_view']['acctaccount']		= create_double($this->AcctDebtCategory_model->getAcctAccount(),'account_id', 'account_code');
			$listoperator = array(
				'+' => 'Menambah',
				'-'	=> 'Mengurang'
			);
			$data['main_view']['listoperator']		= $listoperator;
			$data['main_view']['content']			= 'AcctDebtCategory/FormAddAcctDebtCategory_view';
			$this->load->view('MainPage_view',$data);
		}

		public function function_state_add(){
			$unique 	= $this->session->userdata('unique');
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctdebtcategory-'.$unique['unique']);
			$sessions['active_tab'] = $value;
			$this->session->set_userdata('addacctdebtcategory-'.$unique['unique'],$sessions);
		}
		
		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctdebtcategory-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addacctdebtcategory-'.$unique['unique'],$sessions);
		}

		public function reset_data(){
			$unique 	= $this->session->userdata('unique');
			$sessions	= $this->session->unset_userdata('addacctdebtcategory-'.$unique['unique']);
			redirect('debt-category/add');
		}
		
		public function processAddAcctDebtCategory(){
			$auth = $this->session->userdata('auth');

			$data = array(
				'debt_category_code'		=> $this->input->post('debt_category_code', true),
				'debt_category_name'		=> $this->input->post('debt_category_name', true),
				'operator'					=> $this->input->post('operator', true),
				'debet_account_id'			=> $this->input->post('debet_account_id', true),
				'credit_account_id'			=> $this->input->post('credit_account_id', true),
				'created_id'				=> $auth['user_id'],
				'created_on'				=> date('Y-m-d H:i:s'),
			);
			
			$this->form_validation->set_rules('debt_category_code', 'Nama Kategori', 'required');
			$this->form_validation->set_rules('debt_category_name', 'Nama Kategori', 'required');
			$this->form_validation->set_rules('debet_account_id', 'Debit', 'required');
			$this->form_validation->set_rules('credit_account_id', 'Kredit', 'required');
			$this->form_validation->set_rules('operator', 'Operator', 'required');
			
			if($this->form_validation->run()==true){
				if($this->AcctDebtCategory_model->insertAcctDebtCategory($data)){
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Kategori Potong Gaji Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctdebtcategory-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('debt-category/add');
				}else{
					$this->session->set_userdata('addacctdebtcategory',$data);
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Kategori Potong Gaji Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('debt-category/add');
				}
			}else{
				$this->session->set_userdata('addacctdebtcategory',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('debt-category/add');
			}
		}
		
		public function editAcctDebtCategory(){
			$data['main_view']['acctaccount']		= create_double($this->AcctDebtCategory_model->getAcctAccount(),'account_id', 'account_code');
			$data['main_view']['acctdebtcategory']	= $this->AcctDebtCategory_model->getAcctDebtCategory_Detail($this->uri->segment(3));
			$listoperator = array(
				'+' => 'Menambah',
				'-'	=> 'Mengurang'
			);
			$data['main_view']['listoperator']		= $listoperator;
			$data['main_view']['content']			= 'AcctDebtCategory/FormEditAcctDebtCategory_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function processEditAcctDebtCategory(){
			$data = array(
				'debt_category_id'		=> $this->input->post('debt_category_id', true),
				'debt_category_code'	=> $this->input->post('debt_category_code', true),
				'debt_category_name'	=> $this->input->post('debt_category_name', true),
				'debet_account_id'		=> $this->input->post('debet_account_id', true),
				'credit_account_id'		=> $this->input->post('credit_account_id', true),
				'operator'				=> $this->input->post('operator', true),
			);
			
			$this->form_validation->set_rules('debt_category_code', 'Kode Kategori', 'required');
			$this->form_validation->set_rules('debt_category_name', 'Nama Kategori', 'required');
			$this->form_validation->set_rules('debet_account_id', 'Debit', 'required');
			$this->form_validation->set_rules('credit_account_id', 'Kredit', 'required');
			
			if($this->form_validation->run()==true){
				if($this->AcctDebtCategory_model->updateAcctDebtCategory($data)){
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Kategori Potong Gaji Sukses
							</div> ";

					$this->session->set_userdata('message',$msg);
					redirect('debt-category/edit/'.$data['debt_category_id']);
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Kategori Potong Gaji Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('debt-category/edit/'.$data['debt_category_id']);
				}
			}else{
				$this->session->set_userdata('editmachine',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('debt-category/edit/'.$data['debt_category_id']);
			}				
		}
		
		public function deleteAcctDebtCategory(){
			if($this->AcctDebtCategory_model->deleteAcctDebtCategory($this->uri->segment(3))){
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>                 
							Hapus Data Kategori Potong Gaji Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('debt-category');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>                
							Hapus Data Kategori Potong Gaji Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('debt-category');
			}
		}
	}
?>
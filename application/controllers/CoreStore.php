<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class CoreStore extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('CoreStore_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$sesi 	= $this->session->userdata('unique');

			$this->session->unset_userdata('addCoreStore-'.$sesi['unique']);
			$this->session->unset_userdata('editCoreStore-'.$sesi['unique']);

			$data['main_view']['corestore']			= $this->CoreStore_model->getCoreStore();
			$data['main_view']['content']			= 'CoreStore/ListCoreStore_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function addCoreStore(){
			$data['main_view']['corebranch']		= create_double($this->CoreStore_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']			= 'CoreStore/FormAddCoreStore_view';
			$this->load->view('MainPage_view',$data);
		}

		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addCoreStore-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addCoreStore-'.$unique['unique'],$sessions);
		}
		
		public function processAddCoreStore(){
			$auth 		= $this->session->userdata('auth');
			$sesi 		= $this->session->userdata('unique');

			$data = array(
				'store_code'				=> $this->input->post('store_code', true),
				'store_name'				=> $this->input->post('store_name', true),
				'store_address'				=> $this->input->post('store_address', true),
				'branch_id'					=> $this->input->post('branch_id', true),
				'created_id'				=> $auth['user_id'],
				'created_on'				=> date('Y-m-d H:i:s'),
			);
			
			$this->form_validation->set_rules('store_name', 'Nama', 'required');
			$this->form_validation->set_rules('store_code', 'Kode', 'required');
			$this->form_validation->set_rules('branch_id', 'Cabang', 'required');
			
			if($this->form_validation->run()==true){
					if($this->CoreStore_model->insertCoreStore($data)){
						$auth = $this->session->userdata('auth');
						$sesi = $this->session->userdata('unique');

						$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Toko Sukses
								</div> ";
						$this->session->unset_userdata('addCoreStore-'.$sesi['unique']);
						$this->session->set_userdata('message',$msg);
					} else {
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Toko Tidak Berhasil
								</div> ";
						$this->session->set_userdata('message',$msg);
						redirect('store/add');
					}
					redirect('store/add');
			}else{
				$this->session->set_userdata('addcorestore',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('store/add');
			}
		}
		
		public function editCoreStore(){
			$unique 							= $this->session->userdata('unique');
			$corestore 							= $this->CoreStore_model->getCoreStore_Detail($this->uri->segment(3));

			$data['main_view']['corestore']		= $corestore;
			$data['main_view']['corebranch']	= create_double($this->CoreStore_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']		= 'CoreStore/FormEditCoreStore_view';
			$this->load->view('MainPage_view',$data);
		}

		public function function_elements_edit(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('editCoreStore-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('editCoreStore-'.$unique['unique'],$sessions);
		}
		
		public function processEditCoreStore(){
			$auth		= $this->session->userdata('auth');
			$sesi 		= $this->session->userdata('unique');

			$data = array(
				'store_id'		=> $this->input->post('store_id', true),
				'store_code'	=> $this->input->post('store_code', true),
				'store_name'	=> $this->input->post('store_name', true),
				'store_address'	=> $this->input->post('store_address', true),
				'branch_id'		=> $this->input->post('branch_id', true),
			);
			
			$this->form_validation->set_rules('store_name', 'Nama', 'required');
			$this->form_validation->set_rules('store_code', 'Kode', 'required');
			$this->form_validation->set_rules('branch_id', 'Cabang', 'required');
			
			if($this->form_validation->run()==true){
				if($this->CoreStore_model->updateCoreStore($data)){
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Edit Data Toko Sukses
							</div> ";
					$this->session->unset_userdata('editCoreStore-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('store');
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Data Toko Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('store/edit/'.$data['store_id']);
				}
			}else{
				$this->session->set_userdata('editmachine',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('store/edit/'.$data['store_id']);
			}				
		}
		
		public function deleteCoreStore(){
			if($this->CoreStore_model->deleteCoreStore($this->uri->segment(3))){
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>                 
							Hapus Data Toko Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('store');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>                
							Hapus Data Toko Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('store');
			}
		}
	}
?>
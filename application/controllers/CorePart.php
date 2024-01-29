<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class CorePart extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('CorePart_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$sesi 	= $this->session->userdata('unique');

			$this->session->unset_userdata('addCorePart-'.$sesi['unique']);
			$this->session->unset_userdata('editCorePart-'.$sesi['unique']);

			$data['main_view']['corepart']		= $this->CorePart_model->getCorePart();
			$data['main_view']['content']			= 'CorePart/ListCorePart_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function addCorePart(){
			$data['main_view']['corebranch']		= create_double($this->CorePart_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']			= 'CorePart/FormAddCorePart_view';
			$this->load->view('MainPage_view',$data);
		}

		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addCorePart-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addCorePart-'.$unique['unique'],$sessions);
		}
		
		public function processAddCorePart(){
			$auth 		= $this->session->userdata('auth');
			$sesi 		= $this->session->userdata('unique');

			$data = array(
				'part_code'		=> $this->input->post('part_code', true),
				'part_name'		=> $this->input->post('part_name', true),
				'branch_id'		=> $this->input->post('branch_id', true),
				'created_id'	=> $auth['user_id'],
				'created_on'	=> date('Y-m-d H:i:s'),
			);
			
			$this->form_validation->set_rules('part_name', 'Nama', 'required');
			$this->form_validation->set_rules('part_code', 'Kode', 'required');
			$this->form_validation->set_rules('branch_id', 'Cabang', 'required');
			
			if($this->form_validation->run()==true){
					if($this->CorePart_model->insertCorePart($data)){
						$auth = $this->session->userdata('auth');
						$sesi = $this->session->userdata('unique');

						$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Bagian Sukses
								</div> ";
						$this->session->unset_userdata('addCorePart-'.$sesi['unique']);
						$this->session->set_userdata('message',$msg);
					} else {
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Bagian Tidak Berhasil
								</div> ";
						$this->session->set_userdata('message',$msg);
						redirect('part/add');
					}
					redirect('part/add');
			}else{
				$this->session->set_userdata('addcorepart',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('part/add');
			}
		}
		
		public function editCorePart(){
			$unique 								= $this->session->userdata('unique');
			$corepart 							= $this->CorePart_model->getCorePart_Detail($this->uri->segment(3));

			$data['main_view']['corepart']		= $corepart;
			$data['main_view']['corebranch']		= create_double($this->CorePart_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']			= 'CorePart/FormEditCorePart_view';
			$this->load->view('MainPage_view',$data);
		}

		public function function_elements_edit(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('editCorePart-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('editCorePart-'.$unique['unique'],$sessions);
		}
		
		public function processEditCorePart(){
			$auth		= $this->session->userdata('auth');
			$sesi 		= $this->session->userdata('unique');

			$data = array(
				'part_id'	=> $this->input->post('part_id', true),
				'part_name'	=> $this->input->post('part_name', true),
				'part_code'	=> $this->input->post('part_code', true),
				'branch_id'		=> $this->input->post('branch_id', true),
			);
			
			$this->form_validation->set_rules('part_name', 'Nama', 'required');
			$this->form_validation->set_rules('part_code', 'Kode', 'required');
			$this->form_validation->set_rules('branch_id', 'Cabang', 'required');
			
			if($this->form_validation->run()==true){
				if($this->CorePart_model->updateCorePart($data)){
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Edit Data Bagian Sukses
							</div> ";
					$this->session->unset_userdata('editCorePart-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('part');
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Data Bagian Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('part/edit/'.$data['part_id']);
				}
			}else{
				$this->session->set_userdata('editmachine',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('part/edit/'.$data['part_id']);
			}				
		}
		
		public function deleteCorePart(){
			if($this->CorePart_model->deleteCorePart($this->uri->segment(3))){
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>                 
							Hapus Data Bagian Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('part');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>                
							Hapus Data Bagian Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('part');
			}
		}
	}
?>
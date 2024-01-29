<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class CoreDivision extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('CoreDivision_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$sesi 	= $this->session->userdata('unique');

			$this->session->unset_userdata('addCoreDivision-'.$sesi['unique']);
			$this->session->unset_userdata('editCoreDivision-'.$sesi['unique']);

			$data['main_view']['coredivision']		= $this->CoreDivision_model->getCoreDivision();
			$data['main_view']['content']			= 'CoreDivision/ListCoreDivision_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function addCoreDivision(){
			$data['main_view']['corebranch']		= create_double($this->CoreDivision_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']			= 'CoreDivision/FormAddCoreDivision_view';
			$this->load->view('MainPage_view',$data);
		}

		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addCoreDivision-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addCoreDivision-'.$unique['unique'],$sessions);
		}
		
		public function processAddCoreDivision(){
			$auth 		= $this->session->userdata('auth');
			$sesi 		= $this->session->userdata('unique');

			$data = array(
				'division_code'				=> $this->input->post('division_code', true),
				'division_name'				=> $this->input->post('division_name', true),
				'branch_id'					=> $this->input->post('branch_id', true),
				'created_id'				=> $auth['user_id'],
				'created_on'				=> date('Y-m-d H:i:s'),
			);
			
			$this->form_validation->set_rules('division_name', 'Nama', 'required');
			$this->form_validation->set_rules('division_code', 'Kode', 'required');
			$this->form_validation->set_rules('branch_id', 'Cabang', 'required');
			
			if($this->form_validation->run()==true){
					if($this->CoreDivision_model->insertCoreDivision($data)){
						$auth = $this->session->userdata('auth');
						$sesi = $this->session->userdata('unique');

						$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Divisi Sukses
								</div> ";
						$this->session->unset_userdata('addCoreDivision-'.$sesi['unique']);
						$this->session->set_userdata('message',$msg);
					} else {
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Divisi Tidak Berhasil
								</div> ";
						$this->session->set_userdata('message',$msg);
						redirect('division/add');
					}
					redirect('division/add');
			}else{
				$this->session->set_userdata('addcoredivision',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('division/add');
			}
		}
		
		public function editCoreDivision(){
			$unique 								= $this->session->userdata('unique');
			$coredivision 							= $this->CoreDivision_model->getCoreDivision_Detail($this->uri->segment(3));

			$data['main_view']['coredivision']		= $coredivision;
			$data['main_view']['corebranch']		= create_double($this->CoreDivision_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']			= 'CoreDivision/FormEditCoreDivision_view';
			$this->load->view('MainPage_view',$data);
		}

		public function function_elements_edit(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('editCoreDivision-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('editCoreDivision-'.$unique['unique'],$sessions);
		}
		
		public function processEditCoreDivision(){
			$auth		= $this->session->userdata('auth');
			$sesi 		= $this->session->userdata('unique');

			$data = array(
				'division_id'	=> $this->input->post('division_id', true),
				'division_name'	=> $this->input->post('division_name', true),
				'division_code'	=> $this->input->post('division_code', true),
				'branch_id'		=> $this->input->post('branch_id', true),
			);
			
			$this->form_validation->set_rules('division_name', 'Nama', 'required');
			$this->form_validation->set_rules('division_code', 'Kode', 'required');
			$this->form_validation->set_rules('branch_id', 'Cabang', 'required');
			
			if($this->form_validation->run()==true){
				if($this->CoreDivision_model->updateCoreDivision($data)){
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Edit Data Divisi Sukses
							</div> ";
					$this->session->unset_userdata('editCoreDivision-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('division');
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Data Divisi Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('division/edit/'.$data['division_id']);
				}
			}else{
				$this->session->set_userdata('editmachine',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('division/edit/'.$data['division_id']);
			}				
		}
		
		public function deleteCoreDivision(){
			if($this->CoreDivision_model->deleteCoreDivision($this->uri->segment(3))){
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>                 
							Hapus Data Divisi Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('division');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>                
							Hapus Data Divisi Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('division');
			}
		}
	}
?>
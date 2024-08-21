<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class CoreDusun extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('CoreDusun_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$sesi	= 	$this->session->userdata('filter-coredusun');
			if(!is_array($sesi)){
				$sesi['kelurahan_id']		= '';
			}

			$data['main_view']['corecity']	= create_double($this->CoreDusun_model->getCoreCity(),'city_id','city_name');		
			$data['main_view']['coredusun']	= $this->CoreDusun_model->getDataCoreDusun($sesi['kelurahan_id']);
			$data['main_view']['content']	= 'CoreDusun/ListCoreDusun_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				"kelurahan_id" 	=> $this->input->post('kelurahan_id',true),
			);

			$this->session->set_userdata('filter-coredusun',$data);
			redirect('dusun');
		}

		
		public function addCoreDusun(){
			
			$data['main_view']['corecity']	= create_double($this->CoreDusun_model->getCoreCity(),'city_id','city_name');
			$data['main_view']['content']	= 'CoreDusun/FormAddCoreDusun_view';
			$this->load->view('MainPage_view',$data);
		}

		public function getCoreKecamatan(){
			$city_id 		= $this->input->post('city_id', true);
			
			$item = $this->CoreDusun_model->getCoreKecamatan($city_id);
			$data .= "<option value=''>--Choose One--</option>";
			foreach ($item as $mp){
				$data .= "<option value='$mp[kecamatan_id]'>$mp[kecamatan_name]</option>\n";	
			}
			echo $data;
		}

		public function getCoreKelurahan(){
			$kecamatan_id 		= $this->input->post('kecamatan_id', true);
			
			$item = $this->CoreDusun_model->getCoreKelurahan($kecamatan_id);
			$data .= "<option value=''>--Choose One--</option>";
			foreach ($item as $mp){
				$data .= "<option value='$mp[kelurahan_id]'>$mp[kelurahan_name]</option>\n";	
			}
			echo $data;
		}
		
		public function processAddCoreDusun(){
			$data = array(
				'kelurahan_id'	=> $this->input->post('kelurahan_id', true),
				'dusun_name'	=> $this->input->post('dusun_name', true),
			);
			
			$this->form_validation->set_rules('kelurahan_id', 'Kelurahan', 'required');
			$this->form_validation->set_rules('dusun_name', 'Name', 'required');
			
			if($this->form_validation->run()==true){
				if($this->CoreDusun_model->insertCoreDusun($data)){
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Dusun Sukses
							</div> ";
					$this->session->unset_userdata('addcoredusun');
					$this->session->set_userdata('message',$msg);
					redirect('dusun/add');
				}else{
					$this->session->set_userdata('addcoredusun',$data);
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Dusun Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('dusun/add');
				}
			}else{
				$this->session->set_userdata('addcoredusun',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('dusun/add');
			}
		}
		
		public function editCoreDusun(){
			$data['main_view']['corekelurahan']		= create_double($this->CoreDusun_model->getCoreKelurahan2(),'kelurahan_id','kelurahan_name');	
			$data['main_view']['coredusun']			= $this->CoreDusun_model->getCoreDusun_Detail($this->uri->segment(3));
			$data['main_view']['content']			= 'CoreDusun/FormEditCoreDusun_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function processEditCoreDusun(){
			$data = array(
				'dusun_id'					=> $this->input->post('dusun_id', true),
				'kelurahan_id'				=> $this->input->post('kelurahan_id', true),
				'dusun_name'				=> $this->input->post('dusun_name', true),
			);
			
			$this->form_validation->set_rules('kelurahan_id', 'Kelurahan', 'required');
			$this->form_validation->set_rules('dusun_name', 'Name', 'required');
			
			if($this->form_validation->run()==true){
				if($this->CoreDusun_model->updateCoreDusun($data)){
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Dusun Sukses
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('dusun/edit/'.$data['dusun_id']);
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Dusun Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('dusun/edit/'.$data['dusun_id']);
				}
			}else{
				$this->session->set_userdata('editmachine',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('dusun/edit/'.$data['dusun_id']);
			}				
		}
		
		public function deleteCoreDusun(){
			if($this->CoreDusun_model->deleteCoreDusun($this->uri->segment(3))){
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>                 
							Hapus Data Dusun Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('dusun');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>                
							Hapus Data Dusun Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('dusun');
			}
		}
	}
?>
<?php
// defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctAccount extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctAccount_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data['main_view']['acctaccount']			= $this->AcctAccount_model->getDataAcctAccount();
			$data['main_view']['kelompokperkiraan']		= $this->configuration->KelompokPerkiraan();
			$data['main_view']['accountstatus']			= $this->configuration->AccountStatus();	
			$data['main_view']['content']				= 'AcctAccount/ListAcctAccount_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function addAcctAccount(){
			$data['main_view']['kelompokperkiraan']		= $this->configuration->KelompokPerkiraan();
			$data['main_view']['accountstatus']			= $this->configuration->AccountStatus();
			$data['main_view']['content']				= 'AcctAccount/FormAddAcctAccount_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function processAddAcctAccount(){
			$auth = $this->session->userdata('auth');

			$data = array(
				'account_code'				=> $this->input->post('account_code', true),
				'account_name'				=> $this->input->post('account_name', true),
				'account_type_id'			=> $this->input->post('account_type_id', true),
				'account_group'				=> $this->input->post('account_group', true),
				'account_status'			=> $this->input->post('account_status', true),
				'account_default_status'	=> $this->input->post('account_status', true),
				'created_id'				=> $auth['user_id'],
				'created_on'				=> date('Y-m-d H:i:s'),
			);

			$this->form_validation->set_rules('account_code', 'Nomor Perkiraan', 'required|is_unique[acct_account.account_code]');
			$this->form_validation->set_rules('account_name', 'Nama Perkiraan', 'required');
			$this->form_validation->set_rules('account_group', 'Golongan Perkiraan', 'required');
			
			if($this->form_validation->run()==true){
				if($this->AcctAccount_model->insertAcctAccount($data)){
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Perkiraan Sukses
							</div> ";
					$this->session->unset_userdata('addacctsavings');
					$this->session->set_userdata('message',$msg);
					redirect('account/add');
				}else{
					$this->session->set_userdata('addacctsavings',$data);
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Perkiraan Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('account/add');
				}
			}else{
				$this->session->set_userdata('editmachine',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('account/add');
			}
		}
		
		public function editAcctAccount(){
			$data['main_view']['acctaccount']		= $this->AcctAccount_model->getAcctAccount_Detail($this->uri->segment(3));
			$data['main_view']['kelompokperkiraan']	= $this->configuration->KelompokPerkiraan();
			$data['main_view']['accountstatus']		= $this->configuration->AccountStatus();
			$data['main_view']['content']			= 'AcctAccount/FormEditAcctAccount_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function processEditAcctAccount(){
			$auth = $this->session->userdata('auth');

			$data = array(
				'account_id'				=> $this->input->post('account_id', true),
				'account_code'				=> $this->input->post('account_code', true),
				'account_name'				=> $this->input->post('account_name', true),
				'account_type_id'			=> $this->input->post('account_type_id', true),
				'account_group'				=> $this->input->post('account_group', true),
				'account_status'			=> $this->input->post('account_status', true),
				'account_default_status'	=> $this->input->post('account_status', true),
			);
			
			$this->form_validation->set_rules('account_name', 'Nama Perkiraan', 'required');
			$this->form_validation->set_rules('account_group', 'Golongan Perkiraan', 'required');

			if($data['account_code'] != $this->input->post('account_code_old', true)){
				$this->form_validation->set_rules('account_code', 'Nomor Perkiraan', 'required|is_unique[acct_account.account_code]');
			}else{
				$this->form_validation->set_rules('account_code', 'Nomor Perkiraan', 'required');
			}
			
			if($this->form_validation->run()==true){
				if($this->AcctAccount_model->updateAcctAccount($data)){
					$auth = $this->session->userdata('auth');
					$this->fungsi->set_log($auth['user_id'], $auth['username'],'1003','Application.machine.processMachinesupplier',$auth['username'],'edit machine');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Perkiraan Sukses
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('account/edit/'.$data['account_id']);
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Edit Perkiraan Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('account/edit/'.$data['account_id']);
				}
			}else{
				$this->session->set_userdata('editmachine',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('account/edit/'.$data['account_id']);
			}				
		}
		
		public function deleteAcctAccount(){
			if($this->AcctAccount_model->deleteAcctAccount($this->uri->segment(3))){
				$auth = $this->session->userdata('auth');
				$this->fungsi->set_log($auth['user_id'], $auth['username'],'1005','Application.machine.delete',$auth['username'],'Delete Account');
				$msg = "<div class='alert alert-success alert-dismissable'>                 
							Hapus Data Perkiraan Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('account');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>                
							Hapus Data Perkiraan Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('account');
			}
		}

		public function exportAcctAccount(){
			$acct_account = $this->AcctAccount_model->getDataAcctAccount();
			if(!empty($acct_account)){
				$this->load->library('Excel');
				
				$this->excel->getProperties()->setCreator("SIS")
									->setLastModifiedBy("SIS")
									->setTitle("Master Data Anggota")
									->setSubject("")
									->setDescription("Master Data Anggota")
									->setKeywords("Master, Data, Anggota")
									->setCategory("Master Data Anggota");
									
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(60);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
				
				$this->excel->getActiveSheet()->mergeCells("B1:F1");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B3:F3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B3:F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3:F3')->getFont()->setBold(true);	
				$this->excel->getActiveSheet()->setCellValue('B1',"Master Data Nomor Perkiraan");

				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"No Perkiraan");
				$this->excel->getActiveSheet()->setCellValue('D3',"Nama Perkiraan");
				$this->excel->getActiveSheet()->setCellValue('E3',"Golongan Perkiraan");
				$this->excel->getActiveSheet()->setCellValue('F3',"Saldo Normal");
				
				$j	= 4;
				$no	= 0;
				
				foreach($acct_account as $key=>$val){
					if(is_numeric($key)){
						$no++;
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->getStyle('B'.$j.':F'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						
						if( $val['account_code'] ==  $val['account_group']){
						$this->excel->getActiveSheet()->getStyle('B'.$j)->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C'.$j)->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D'.$j)->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E'.$j)->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('F'.$j)->getFont()->setBold(true);
						}

						$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
						$this->excel->getActiveSheet()->setCellValue('C'.$j, $val['account_code']);
						$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['account_name']);
						$this->excel->getActiveSheet()->setCellValue('E'.$j, $val['account_group']);
						if($val['account_default_status'] == 0){
							$this->excel->getActiveSheet()->setCellValue('F'.$j, "Debit");
						}else{
							$this->excel->getActiveSheet()->setCellValue('F'.$j, "Kredit");
						}
					}else{
						continue;
					}
					$j++;
				}
				
				$filename='Master Daftar Nomor Perkiraan.xls';
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

		public function importAcctAccount(){
			$auth 	= $this->session->userdata('auth');

			$data['main_view']['content']	= 'AcctAccount/FormImportAcctAccount_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processImportAcctAccount(){
			$auth 		= $this->session->userdata('auth');

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
				redirect('account');
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

					$data	= array (
						'account_code'				=> $rowData[0][0],
						'account_name'				=> $rowData[0][1],
						'account_group'				=> $rowData[0][2],
						'account_default_status'	=> $rowData[0][3],
						'account_status'			=> $rowData[0][4],
						'account_type_id'			=> $rowData[0][5],
						'created_id'				=> $auth['user_id'],
						'created_on'				=> date('Y-m-d H:i:s'),
					);

					$this->AcctAccount_model->insertAcctAccount($data);
				}
				unlink($inputFileName);
				$msg = "<div class='alert alert-success'>                
							Import No Perkiraan Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('account');
			}
		}

		public function generateAccountID(){
			$phu = $this->AcctAccount_model->getAccountCode();

			foreach($phu as $key => $val){
				$account_id = $this->AcctAccount_model->getAccountIDFromCode($val['account_code']);

				$data = array(
					'profit_loss_report_id' => $val['profit_loss_report_id'], 
					'account_code' 			=> $val['account_code'], 
					'account_id' 			=> $account_id, 
				);
				$test = $this->AcctAccount_model->getTest();

				print_r($test);
				// $this->AcctAccount_model->updateProfitLossReport($data);
			}
		}
	}
?>
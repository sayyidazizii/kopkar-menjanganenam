<?php ob_start(); ?>
<?php
	ini_set('memory_limit', '512M');
	defined('BASEPATH') OR exit('No direct script access allowed');


	Class PersonaliaReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('PersonaliaReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		public function index(){
			$corebranch 									= create_double_branch($this->PersonaliaReport_model->getCoreBranch(),'branch_id','branch_name');
			$corebranch[0] 									= 'Semua Cabang';
			ksort($corebranch);
			$data['main_view']['corebranch']				= $corebranch;
			$data['main_view']['acctsavings']				= create_double($this->PersonaliaReport_model->getAcctSavings(),'savings_pdfid','savings_name');
			$data['main_view']['kelompoklaporansimpanan']	= $this->configuration->KelompokLaporanSimpanan1();	
			$data['main_view']['month_name']				= $this->configuration->Month();	
			
			$data['main_view']['content']					= 'PersonaliaReport/ListPersonaliaReport_view';

			$this->load->view('MainPage_view',$data);
			// print_r($data['main_view']['month_now']	);exit;
		}
		public function viewreport(){
			$sesi = array (
				// "bulan_db"					=> $this->input->post('bulan_db', true),
				"branch_id"					=> $this->input->post('branch_id', true),
				"month_name"				=> $this->input->post('month_name', true),
				"member_name"				=> $this->input->post('member_name', true),
				
				// "start_date" 				=> tgltodb($this->input->post('start_date',true)),
				// "end_date" 					=> tgltodb($this->input->post('end_date',true)),
				"view"						=> $this->input->post('view',true),
			);
			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
			
		}
		public function getSavings($savings_id){

			$member_name		= $this->PersonaliaReport_model->getAcctCreditsAccount($month_name, $member_name);
			return $member_name;
		}
		
		public function export($sesi){	
			// print_r($sesi);exit;
			// $bulan_db			= $this->PersonaliaReport_model->getMonth1();	
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->PersonaliaReport_model->getPreferenceCompany();
		
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}
			
			
			$kelompoklaporansimpanan		 = $this->configuration->KelompokLaporanSimpanan1();
			$coremember 					 = $this->PersonaliaReport_model->getCoreMember();
			$savings 						 = $this->PersonaliaReport_model->getAcctSavingsAccount();			
			$credit							 = $this->PersonaliaReport_model->getCredit1($sesi['month_name']);
			// $datapersonalia 				 = $this->PersonaliaReport_model->getPersonaliaReport($preferencecompany['account_income_personalia_id']);
			// $totalpersonalia = 0;
			// print_r($credit);exit;
			
			
			
				$this->load->library('Excel');
				$this->excel->getProperties()->setCreator("CST FISRT")
									 ->setLastModifiedBy("CST FISRT")
									 ->setTitle("Laporan Personalia")
									 ->setSubject("")
									 ->setDescription("Laporan Personalia")
									 ->setKeywords("Laporan Personalia")
									 ->setCategory("Laporan Personalia");

						 
				$this->excel->getSheet(0)->setTitle('Anggota');
				$this->excel->createSheet(1)->setTitle('Tabungan');
				$this->excel->createSheet(2)->setTitle('Pinjaman');
				

				// Anggota----
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(11);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(22);
				
				$this->excel->getActiveSheet()->mergeCells("B1:G1");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B3:G3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B3:G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3:G3')->getFont()->setBold(true);
				
				$this->excel->getActiveSheet()->setCellValue('B1',"Laporan Anggota");
				
				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"No Anggota");
				$this->excel->getActiveSheet()->setCellValue('D3',"Nama Anggota");
				$this->excel->getActiveSheet()->setCellValue('E3',"Simpanan Pokok");
				$this->excel->getActiveSheet()->setCellValue('F3',"Simpanan Wajib");
				$this->excel->getActiveSheet()->setCellValue('G3',"Simpanan Khusus");
				$j=3;
			$no=0;
		
			// Anggota----
			foreach($coremember as $key=>$val){
			
				if(is_numeric($key)){
					$no++;
					$j++;
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->getStyle('B'.$j.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					
					
					$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
					$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j,$val['member_no'],PHPExcel_Cell_DataType::TYPE_STRING);
					$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
					$this->excel->getActiveSheet()->setCellValue('E'.$j, number_format($val['member_principal_savings'],2));
					$this->excel->getActiveSheet()->setCellValue('F'.$j, number_format($val['member_special_savings'],2));
					$this->excel->getActiveSheet()->setCellValue('G'.$j, number_format($val['member_mandatory_savings'],2));

				 }else{
					 	continue;
					 }
			}
					// Tabungan-----
					$this->excel->setActiveSheetIndex(1);
					$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
					$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
					
					
					$this->excel->getActiveSheet()->mergeCells("B1:F1");
					$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
					$this->excel->getActiveSheet()->getStyle('B3:F3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B3:F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('B3:F3')->getFont()->setBold(true);
					
					$this->excel->getActiveSheet()->setCellValue('B1',"Laporan Tabungan");
					
					$this->excel->getActiveSheet()->setCellValue('B3',"No");
					$this->excel->getActiveSheet()->setCellValue('C3',"No Rek");
					$this->excel->getActiveSheet()->setCellValue('D3',"Jenis Tabungan");
					$this->excel->getActiveSheet()->setCellValue('E3',"Nama Anggota");
					$this->excel->getActiveSheet()->setCellValue('F3',"Saldo");
					$j=3;
				$no=0;

			// tabungan
			foreach($savings as $key=>$val){
			
				if(is_numeric($key)){
					$no++;
					$j++;
					$this->excel->setActiveSheetIndex(1);
					$this->excel->getActiveSheet()->getStyle('B'.$j.':F'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				
					$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
					$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j,$val['savings_account_no'],PHPExcel_Cell_DataType::TYPE_STRING);
					$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['savings_name']);
					$this->excel->getActiveSheet()->setCellValue('E'.$j, $val['member_name']);
					$this->excel->getActiveSheet()->setCellValue('F'.$j, number_format($val['savings_account_last_balance'],2));
				 }else{
					 	continue;
					 }
			}
				
					// credit-----
					$this->excel->setActiveSheetIndex(2);
					$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
					$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
					
					
					$this->excel->getActiveSheet()->mergeCells("B1:G1");
					$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
					$this->excel->getActiveSheet()->getStyle('B3:G3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B3:G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('B3:G3')->getFont()->setBold(true);
					
					$this->excel->getActiveSheet()->setCellValue('B1',"Laporan Pinjaman");
					
					$this->excel->getActiveSheet()->setCellValue('B3',"No");
					$this->excel->getActiveSheet()->setCellValue('C3',"No Pinjaman");
					$this->excel->getActiveSheet()->setCellValue('D3',"Nama Anggota");
					$this->excel->getActiveSheet()->setCellValue('E3',"Outstanding");
					$this->excel->getActiveSheet()->setCellValue('F3',"Angsuran Pokok Bulan Sekarang");
					$this->excel->getActiveSheet()->setCellValue('G3',"Angsuran Bunga Bulan Sekarang");
					$j=3;
					$no=0;

				// credit
				foreach($credit  as $key=>$val){

				if(is_numeric($key)){
					$no++;	
					$j++;
					$this->excel->setActiveSheetIndex(2);
					$this->excel->getActiveSheet()->getStyle('B'.$j.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					
					$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
					$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j, $val['savings_account_no'],PHPExcel_Cell_DataType::TYPE_STRING);
					$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
					$this->excel->getActiveSheet()->setCellValue('E'.$j, number_format($val['credits_interest_last_balance'],2));
					$this->excel->getActiveSheet()->setCellValue('F'.$j, number_format($val['credits_payment_principal'],2));
					$this->excel->getActiveSheet()->setCellValue('G'.$j, number_format($val['credits_payment_interest'],2));
				 }else{
					 	continue;
					 }
					 
			}
			// print_r($coremember2);exit;
				$filename='Laporan Personalia.xls';
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="'.$filename.'"');
				header('Cache-Control: max-age=0');
							 
				$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
				ob_end_clean();
				$objWriter->save('php://output');
			
		}

	}
?>
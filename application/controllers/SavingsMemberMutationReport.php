<?php ob_start(); ?>
<?php
	ini_set('memory_limit', '512M');
	defined('BASEPATH') OR exit('No direct script access allowed');


	Class SavingsMemberMutationReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('SavingsMemberMutationReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$corebranch 									= create_double_branch($this->SavingsMemberMutationReport_model->getCoreBranch(),'branch_id','branch_name');
			$corebranch[0] 									= 'Semua Cabang';
			ksort($corebranch);
			$data['main_view']['corebranch']				= $corebranch;
			$data['main_view']['acctsavings']				= create_double($this->SavingsMemberMutationReport_model->getAcctSavings(),'savings_id','savings_name');
			$data['main_view']['kelompoklaporansimpanan']	= $this->configuration->KelompokLaporanSimpanan1();	
			$data['main_view']['content']					= 'SavingsMemberMutationReport/ListSavingsMemberMutationReport_view';
			$this->load->view('MainPage_view',$data);
		}
 
		public function viewreport(){
			$sesi = array (
				"branch_id"					=> $this->input->post('branch_id', true),
				"start_date" 				=> tgltodb($this->input->post('start_date',true)),
				"end_date" 					=> tgltodb($this->input->post('end_date',true)),
				"view"						=> $this->input->post('view',true),
			);

			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
		}

		public function processPrinting($sesi){
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->SavingsMemberMutationReport_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$datamutation 		= $this->SavingsMemberMutationReport_model->getSavingsMemberMutationReport($sesi['start_date'], $sesi['end_date'], $branch_id);
			
			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			// create new PDF document
			$pdf = new tcpdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// set font
			$pdf->SetFont('helvetica', 'B', 20);

			// add a page
			$pdf->AddPage();

			$pdf->SetFont('helvetica', '', 9);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl0 = "
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
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR MUTASI SIMP ANGGOTA ".$sesi['start_date']." s.d ".$sesi['end_date']."</div></td>
			    </tr>
			</table>
			<br>";

			$tbl1 = "
			<br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No</div></td>
					<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Anggota</div></td>
			        <td width=\"20%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
					<td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Bagian</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Tanggal Transaksi</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Sandi</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Simp Pokok</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Simp Wajib</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Simp Khusus</div></td>
			    </tr>";

			if(count($datamutation) > 0){
				$no = 1;
				foreach($datamutation as $key => $val){
					$tbl1 .= "
						<tr>
							<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">".$no."</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$val['member_no']."</div></td>
							<td width=\"20%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$val['member_name']."</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$val['division_name']."</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$val['transaction_date']."</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$val['mutation_name']."</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format($val['principal_savings_amount'], 2)."</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format($val['mandatory_savings_amount'], 2)."</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format($val['special_savings_amount'], 2)."</div></td>
						</tr>";
					$no++;
				}
			}else{
				$tbl1 .= "
					<tr>
						<td width=\"100%\" colspan =\"3\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Data Kosong</div></td>
					</tr>
				";
			}
				$tbl1 .= "</table>";
			

			$pdf->writeHTML($tbl0.$tbl1, true, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			//Close and output PDF document
			$filename = 'Laporan Nominatif Simpanan '.$kelompoklaporansimpanan.'.pdf';
			$pdf->Output($filename, 'I');

			//============================================================+
			// END OF FILE
			//============================================================+
		}

		public function export($sesi){	
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->SavingsMemberMutationReport_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$datamutation 		= $this->SavingsMemberMutationReport_model->getSavingsMemberMutationReport($sesi['start_date'], $sesi['end_date'], $branch_id);

			if(count($datamutation) !=0){
				$this->load->library('Excel');
				
				$this->excel->getProperties()->setCreator("CST FISRT")
									 ->setLastModifiedBy("CST FISRT")
									 ->setTitle("Laporan Data Mutasi Simp Anggota")
									 ->setSubject("")
									 ->setDescription("Laporan Data Mutasi Simp Anggota")
									 ->setKeywords("Laporan Data Mutasi Simp Anggota")
									 ->setCategory("Laporan Data Mutasi Simp Anggota");
									 
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
						
				$this->excel->getActiveSheet()->mergeCells("B1:J1");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B3:J3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B3:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3:J3')->getFont()->setBold(true);
				
				$this->excel->getActiveSheet()->setCellValue('B1',"Laporan Data Mutasi Simp Anggota");
				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"No Anggota");
				$this->excel->getActiveSheet()->setCellValue('D3',"Nama");
				$this->excel->getActiveSheet()->setCellValue('E3',"Bagian");
				$this->excel->getActiveSheet()->setCellValue('F3',"Tanggal Transaksi");
				$this->excel->getActiveSheet()->setCellValue('G3',"Sandi");
				$this->excel->getActiveSheet()->setCellValue('H3',"Simp Pokok");
				$this->excel->getActiveSheet()->setCellValue('I3',"Simp Wajib");
				$this->excel->getActiveSheet()->setCellValue('J3',"Simp Khusus");
				
				$no	= 1;
				$j	= 4;
				foreach($datamutation as $key3 => $val3){
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->getStyle('B'.$j.':J'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					

					$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
					$this->excel->getActiveSheet()->setCellValue('C'.$j, $val3['member_no']);
					$this->excel->getActiveSheet()->setCellValue('D'.$j, $val3['member_name']);
					$this->excel->getActiveSheet()->setCellValue('E'.$j, $val3['division_name']);
					$this->excel->getActiveSheet()->setCellValueExplicit('F'.$j, $val3['transaction_date'],PHPExcel_Cell_DataType::TYPE_STRING);								
					$this->excel->getActiveSheet()->setCellValue('G'.$j, $val3['mutation_name']);
					$this->excel->getActiveSheet()->setCellValue('H'.$j, number_format($val3['principal_savings_amount'],2));
					$this->excel->getActiveSheet()->setCellValue('I'.$j, number_format($val3['mandatory_savings_amount'],2));
					$this->excel->getActiveSheet()->setCellValue('J'.$j, number_format($val3['special_savings_amount'],2));

					$j++;
					$no++;
				}

				$filename='Laporan Mutasi Simp Anggota.xls';
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="'.$filename.'"');
				header('Cache-Control: max-age=0');
							 
				$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
				ob_end_clean();
				$objWriter->save('php://output');
			}else{
				echo "Maaf data yang di eksport tidak ada !";
			}
		}
	}
?>
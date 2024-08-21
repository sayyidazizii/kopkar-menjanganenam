<?php ob_start(); ?>
<?php
	ini_set('memory_limit', '512M');
	defined('BASEPATH') OR exit('No direct script access allowed');


	Class CreditsAccountMutationReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('CreditsAccountMutationReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$corebranch 									= create_double_branch($this->CreditsAccountMutationReport_model->getCoreBranch(),'branch_id','branch_name');
			$corebranch[0] 									= 'Semua Cabang';
			ksort($corebranch);
			$data['main_view']['corebranch']				= $corebranch;
			$data['main_view']['kelompoklaporansimpanan']	= $this->configuration->KelompokLaporanSimpanan1();	
			$data['main_view']['content']					= 'CreditsAccountMutationReport/ListCreditsAccountMutationReport_view';
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
			$preferencecompany 	= $this->CreditsAccountMutationReport_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}
			
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
			        <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR MUTASI PINJAMAN ".$sesi['start_date']." s.d ".$sesi['end_date']."</div></td>
			    </tr>
			</table>
			<br>";
			$pdf->writeHTML($tbl0, true, false, false, '');

			$datacredits		= $this->CreditsAccountMutationReport_model->getAcctCredits();
			foreach($datacredits as $key => $val){
				$tbl00 = "
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
					<tr>
						<td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">JENIS PINJAMAN : ".$val['credits_name']."</div></td>
					</tr>
				</table>
				";
				$pdf->writeHTML($tbl00, true, false, false, '');
				
				$tbl1 = "
					<br>
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
					<tr>
						<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No</div></td>
						<td width=\"12%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Anggota</div></td>
						<td width=\"18%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
						<td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Bagian</div></td>
						<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Pinjaman</div></td>
						<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Tanggal Angsuran</div></td>
						<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Angsuran Pokok</div></td>
						<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Angsuran Bunga</div></td>
						<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Total Angsuran</div></td>
					</tr>
				";

				$datamutation 		= $this->CreditsAccountMutationReport_model->getCreditsAccountMutationReport($sesi['start_date'], $sesi['end_date'], $val['credits_id']);

				if(count($datamutation) > 0){
					$no = 1;
					foreach($datamutation as $keyy => $vall){
						$tbl1 .= "
							<tr>
								<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">".$no."</div></td>
								<td width=\"12%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$vall['member_no']."</div></td>
								<td width=\"18%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$vall['member_name']."</div></td>
								<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$vall['division_name']."</div></td>
								<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$vall['credits_account_serial']."</div></td>
								<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">".$vall['credits_payment_date']."</div></td>
								<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format($vall['credits_payment_principal'])."</div></td>
								<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format($vall['credits_payment_interest'])."</div></td>
								<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format($vall['credits_payment_amount'])."</div></td>
							</tr>";
						$no++;
					}
				}else{
					$tbl1 .= "
						<tr>
							<td width=\"100%\" colspan =\"3\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Data Kosong</div></td>
						</tr>
					";
				}
					$tbl1 .= "</table>";
				

				$pdf->writeHTML($tbl1, true, false, false, '');

				$tbl00 = "
					<br pagebreak=\"true\"/>
				";
				$pdf->writeHTML($tbl00, true, false, false, '');
			}

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
			$preferencecompany 	= $this->CreditsAccountMutationReport_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$this->load->library('Excel');
			
			$this->excel->getProperties()->setCreator("CST FISRT")
									->setLastModifiedBy("CST FISRT")
									->setTitle("Laporan Data Mutasi Pinjaman")
									->setSubject("")
									->setDescription("Laporan Data Mutasi Pinjaman")
									->setKeywords("Laporan Data Mutasi Pinjaman")
									->setCategory("Laporan Data Mutasi Pinjaman");
									

			$datacredits	= $this->CreditsAccountMutationReport_model->getAcctCredits();
			$sheet_no 		= 0;
			foreach($datacredits as $key => $val){
				$this->excel->createSheet($sheet_no);
				$this->excel->setActiveSheetIndex($sheet_no);
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
				$this->excel->getActiveSheet()->getStyle('B2:C2')->getFont()->setBold(true);
				
				$this->excel->getActiveSheet()->setCellValue('B1',"Laporan Data Mutasi Pinjaman");
				$this->excel->getActiveSheet()->setCellValue('B2',"Jenis Pinjaman : ");
				$this->excel->getActiveSheet()->setCellValue('C2',$val['credits_name']);
				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"No Anggota");
				$this->excel->getActiveSheet()->setCellValue('D3',"Nama");
				$this->excel->getActiveSheet()->setCellValue('E3',"Bagian");
				$this->excel->getActiveSheet()->setCellValue('F3',"No Pinjaman");
				$this->excel->getActiveSheet()->setCellValue('G3',"Tanggal Angsuran");
				$this->excel->getActiveSheet()->setCellValue('H3',"Angsuran Pokok");
				$this->excel->getActiveSheet()->setCellValue('I3',"Angsuran Bunga");
				$this->excel->getActiveSheet()->setCellValue('J3',"Total Angsuran");
				
				$datamutation 		= $this->CreditsAccountMutationReport_model->getCreditsAccountMutationReport($sesi['start_date'], $sesi['end_date'], $val['credits_id']);

				if(count($datamutation) > 0){
					$no = 1;
					$j	= 4;
					foreach($datamutation as $keyy => $vall){
						$this->excel->setActiveSheetIndex($sheet_no);
						$this->excel->getActiveSheet()->getStyle('B'.$j.':J'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$this->excel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$this->excel->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						
	
						$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
						$this->excel->getActiveSheet()->setCellValue('C'.$j, $vall['member_no']);
						$this->excel->getActiveSheet()->setCellValue('D'.$j, $vall['member_name']);
						$this->excel->getActiveSheet()->setCellValue('E'.$j, $vall['division_name']);
						$this->excel->getActiveSheet()->setCellValue('F'.$j, $vall['credits_account_serial']);
						$this->excel->getActiveSheet()->setCellValueExplicit('G'.$j, $vall['credits_payment_date'],PHPExcel_Cell_DataType::TYPE_STRING);								
						$this->excel->getActiveSheet()->setCellValue('H'.$j, number_format($vall['credits_payment_principal'],2));
						$this->excel->getActiveSheet()->setCellValue('I'.$j, number_format($vall['credits_payment_interest'],2));
						$this->excel->getActiveSheet()->setCellValue('J'.$j, number_format($vall['credits_payment_amount'],2));
	
						$j++;
						$no++;
					}
				}
				$sheet_no++;
			}

			$filename='Laporan Mutasi Pinjaman.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
							
			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
			ob_end_clean();
			$objWriter->save('php://output');
		}
	}
?>
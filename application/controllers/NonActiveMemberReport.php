<?php ob_start(); ?>
<?php
	ini_set('memory_limit', '512M');
	defined('BASEPATH') OR exit('No direct script access allowed');


	Class NonActiveMemberReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('NonActiveMemberReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$corebranch 									= create_double_branch($this->NonActiveMemberReport_model->getCoreBranch(),'branch_id','branch_name');
			$corebranch[0] 									= 'Semua Cabang';
			ksort($corebranch);
			$data['main_view']['corebranch']				= $corebranch;
			$data['main_view']['month']						= $this->configuration->Month();	
			$data['main_view']['content']					= 'NonActiveMemberReport/ListNonActiveMemberReport_view';
			$this->load->view('MainPage_view',$data);
		}
 
		public function viewreport(){

			$sesi = array (
				"start_date"	=> $this->input->post('start_date', true),
				"end_date"		=> $this->input->post('end_date', true),
				"branch_id"		=> $this->input->post('branch_id', true),
				"view"			=> $this->input->post('view',true),
			);

			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
		}

		public function processPrinting($sesi){
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->NonActiveMemberReport_model->getPreferenceCompany();
			
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
			$pdf = new tcpdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 9);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
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
					<td width=\"100%\">
						<div style=\"text-align: center; font-size:14px; font-weight:bold\">LEPORAN ANGGOTA TIDAK AKTIF</div>
						<div style=\"text-align: center; font-size:12px;\">Per ".$sesi['start_date']." s/d ".$sesi['end_date']."</div>
					</td>
				</tr>
			</table>
			<br>
			<br>
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
			<tr>
				<td width=\"5%\" style=\"text-align: center;\">No</td>
				<td width=\"15%\" style=\"text-align: center;\">No Anggota</td>
				<td width=\"15%\" style=\"text-align: center;\">NIK Karyawan</td>
				<td width=\"45%\" style=\"text-align: center;\">Nama Anggota</td>
				<td width=\"20%\" style=\"text-align: center;\">Tanggal Tidak Aktif</td>
			</tr>
			";

			$coremember = $this->NonActiveMemberReport_model->getNonActiveMemberReport($sesi);
			$no 		= 1;

			if($coremember){
				foreach($coremember as $key => $val){
					$tbl .=" 
					<tr>
						<td width=\"5%\" style=\"text-align: center;\">".$no."</td>
						<td width=\"15%\" style=\"text-align: center;\">".$val['member_no']."</td>
						<td width=\"15%\" style=\"text-align: center;\">".$val['member_nik']."</td>
						<td width=\"45%\" style=\"text-align: left;\">".$val['member_name']."</td>
						<td width=\"20%\" style=\"text-align: center;\">".date('d-m-Y', strtotime($val['member_non_activate_date']))."</td>
					</tr>
					";
					$no++;
				}
			}else{
				$tbl .=" 
				<tr>
					<td colspan=\"6\" style=\"text-align: center;\">Data Kosong</td>
				</tr>
				";				
			}
			$tbl .= "</table>";

			$pdf->writeHTML($tbl, true, false, false, '');

			ob_clean();
			$filename = 'Laporan Anggota Tidak Aktif Per '.$date.'.pdf';
			$pdf->Output($filename, 'I');
		}

		public function export($sesi){	
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->NonActiveMemberReport_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$coremember = $this->NonActiveMemberReport_model->getNonActiveMemberReport($sesi);

			$this->load->library('Excel');
			$this->excel->getProperties()->setCreator("CST FISRT")
									->setLastModifiedBy("CST FISRT")
									->setTitle("Laporan Data Anggota Tidak Aktif")
									->setSubject("")
									->setDescription("Laporan Data Anggota Tidak Aktif")
									->setKeywords("Laporan Data Anggota Tidak Aktif")
									->setCategory("Laporan Data Anggota Tidak Aktif");
									
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

			$this->excel->getActiveSheet()->mergeCells("B2:F2");
			$this->excel->getActiveSheet()->mergeCells("B3:F3");
			$this->excel->getActiveSheet()->getStyle('B2:F5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B2:F3')->getFont()->setBold(true);
			
			$this->excel->getActiveSheet()->setCellValue('B2', "LAPORAN ANGGOTA TIDAK AKTIF");
			$this->excel->getActiveSheet()->setCellValue('B3', "Per ".$sesi['start_date']." s/d ".$sesi['end_date']);
			$this->excel->getActiveSheet()->setCellValue('B5', "No");
			$this->excel->getActiveSheet()->setCellValue('C5', "No Anggota");
			$this->excel->getActiveSheet()->setCellValue('D5', "NIK Karyawan");
			$this->excel->getActiveSheet()->setCellValue('E5', "Nama Anggota");
			$this->excel->getActiveSheet()->setCellValue('F5', "Tanggal Tidak Aktif");
			
			$row 	= 6;
			$no 	= 1;

			foreach($coremember as $key => $val){
				$this->excel->getActiveSheet()->setCellValue('B'.($row), $no);
				$this->excel->getActiveSheet()->setCellValue('C'.($row), $val['member_no']);
				$this->excel->getActiveSheet()->setCellValue('D'.($row), $val['member_nik']);
				$this->excel->getActiveSheet()->setCellValue('E'.($row), $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('F'.($row), date('d-m-Y', strtotime($val['member_non_activate_date'])));

				$row++;
				$no++;
			}
			
			$this->excel->getActiveSheet()->getStyle('B6:D'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('F6:F'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B5:F'.($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

			$this->excel->getActiveSheet()->mergeCells("B".($row).":D".($row));
			$this->excel->getActiveSheet()->mergeCells("E".($row).":F".($row));
			$this->excel->getActiveSheet()->getStyle('B'.($row).':F'.($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B'.($row).':F'.($row))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B'.($row), "TOTAL");
			$this->excel->getActiveSheet()->setCellValue('E'.($row), count($coremember));

			$filename='Laporan Anggota Tidak Aktif.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
							
			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
			ob_end_clean();
			$objWriter->save('php://output');
		}
	}
?>
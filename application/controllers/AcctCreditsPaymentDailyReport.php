<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctCreditsPaymentDailyReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctCreditsPaymentDailyReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}

		public function index(){
			$data['main_view']['corebranch']	= create_double($this->AcctCreditsPaymentDailyReport_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['coreoffice']	= create_double($this->AcctCreditsPaymentDailyReport_model->getCoreOffice(),'office_id','office_name');
			$data['main_view']['acctcredits']	= create_double($this->AcctCreditsPaymentDailyReport_model->getAcctCredits(),'credits_id','credits_name');
			$data['main_view']['content']		= 'AcctCreditsPaymentDailyReport/ListAcctCreditsPaymentDailyReport_view';
			$this->load->view('MainPage_view',$data);
		}

		public function viewreport(){
			$sesi = array (
				"start_date" 	=> tgltodb($this->input->post('start_date',true)),
				"end_date" 		=> tgltodb($this->input->post('end_date',true)),
				"office_id"		=> $this->input->post('office_id',true),
				"credits_id"	=> $this->input->post('credits_id',true),
				"branch_id"		=> $this->input->post('branch_id',true),
				"view"			=> $this->input->post('view',true),
			);

			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
		}

		public function processPrinting($sesi){
			$auth 	=	$this->session->userdata('auth');
			$preferencecompany = $this->AcctCreditsPaymentDailyReport_model->getPreferenceCompany();
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$memberpayment	= $this->AcctCreditsPaymentDailyReport_model->getMemberPayment($sesi['start_date'], $sesi['end_date'], $sesi['credits_id'], $branch_id);

			if(!empty($sesi['credits_id'])){
				$credits_name = strtoupper($this->AcctCreditsPaymentDailyReport_model->getCreditsName($sesi['credits_id']));
			}else{
				$credits_name = "PINJAMAN";
			}

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');

			$pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);
			$pdf->SetMargins(7, 7, 7, 7); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 8);

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
				        <td><div style=\"text-align: center; font-size:14px\">DAFTAR ANGSURAN ".$credits_name."</div></td>
				    </tr>
				    <tr>
				        <td><div style=\"text-align: center; font-size:10px\">".tgltoview($sesi['start_date'])." s.d. ".tgltoview($sesi['end_date'])."</div></td>
				    </tr>
				</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:9;\">No.</div></td>
			        <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">No. Anggota</div></td>
			        <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Nama</div></td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Bagian</div></td>
			        <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Jenis Pinjaman</div></td>
			        <td width=\"15%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Angsuran Pokok</div></td>
			         <td width=\"15%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Angsuran Bunga</div></td>
			         <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Sisa Pokok</div></td>
			    </tr>				
			</table>";

			$no 			= 1;
			$totalpokok 	= 0;
			$totalbunga 	= 0;
			$totalsisapokok	= 0;

			$tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

			if(!empty($memberpayment)){
				foreach ($memberpayment as $key => $val) {
					$tbl3 .= "
						<tr>
					    	<td width=\"3%\"><div style=\"text-align: left;\">".$no."</div></td>
					        <td width=\"12%\"><div style=\"text-align: left;\">".$val['member_no']."</div></td>
					        <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
					        <td width=\"10%\"><div style=\"text-align: left;\">".$val['part_name']."</div></td>
					        <td width=\"15%\"><div style=\"text-align: left;\">".$val['credits_name']."</div></td>
					       	<td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['total_principal'], 2)."</div></td>
					       	<td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['total_interest'], 2)."</div></td>
					       	<td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['total_last_principal'], 2)."</div></td>
					    </tr>
					";

					$totalpokok		+= $val['total_principal'];
					$totalbunga		+= $val['total_interest'];
					$totalsisapokok	+= $val['total_last_principal'];

					$no++;
				}
			} else {
				$tbl3 .= "";
			}

			$tbl4 = "
				<tr>
					<td colspan =\"4\"><div style=\"text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".$this->AcctCreditsPaymentDailyReport_model->getUserName($auth['user_id'])."</div></td>
					<td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-weight:bold;text-align:center\">Total </div></td>
					<td style=\"font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align:right\">".number_format($totalpokok, 2)."</div></td>
					<td style=\"font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align:right\">".number_format($totalbunga, 2)."</div></td>
					<td style=\"font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align:right\">".number_format($totalsisapokok, 2)."</div></td>
				</tr>
			</table>";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------

			$filename = 'DAFTAR TAGIHAN ANGSURAN PINJAMAN.pdf';
			$pdf->Output($filename, 'I');
		}

		public function export($sesi){	
			$auth = $this->session->userdata('auth');
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			if(!empty($sesi['credits_id'])){
				$credits_name = strtoupper($this->AcctCreditsPaymentDailyReport_model->getCreditsName($sesi['credits_id']));
			}else{
				$credits_name = "PINJAMAN";
			}

			$memberpayment		= $this->AcctCreditsPaymentDailyReport_model->getMemberPayment($sesi['start_date'], $sesi['end_date'], $sesi['credits_id'], $branch_id);

			if(count($memberpayment) !=''){
				$this->load->library('Excel');

				$this->excel->getProperties()->setCreator("CST FISRT")
				->setLastModifiedBy("CST FISRT")
				->setTitle("DAFTAR ANGSURAN PINJAMAN HARIAN")
				->setSubject("")
				->setDescription("DAFTAR ANGSURAN PINJAMAN HARIAN")
				->setKeywords("DAFTAR, ANGSURAN, PINJAMAN, HARIAN")
				->setCategory("DAFTAR ANGSURAN PINJAMAN HARIAN");

				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);	
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);	
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);	
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);	

				$this->excel->getActiveSheet()->mergeCells("B1:I1");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B3:I3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B3:I3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3:I3')->getFont()->setBold(true);
				$this->excel->getActiveSheet()->setCellValue('B1',"DAFTAR ANGSURAN ".$credits_name." ".$sesi['start_date'].' s.d. '.$sesi['end_date']);

				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"No. Anggota");
				$this->excel->getActiveSheet()->setCellValue('D3',"Nama");
				$this->excel->getActiveSheet()->setCellValue('E3',"Bagian");
				$this->excel->getActiveSheet()->setCellValue('F3',"Jenis Pinjaman");
				$this->excel->getActiveSheet()->setCellValue('G3',"Angsuran Pokok");
				$this->excel->getActiveSheet()->setCellValue('H3',"Angsuran Bunga");
				$this->excel->getActiveSheet()->setCellValue('I3',"Sisa Pokok");

				$no 			= 0;
				$totalpokok		= 0;
				$totalbunga		= 0;
				$totalsisapokok	= 0;
				$j				= 4;

				foreach($memberpayment as $key=>$val){
					$no++;
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->getStyle('B'.$j.':I'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
					$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j, $val['member_no']);
					$this->excel->getActiveSheet()->setCellValueExplicit('D'.$j, $val['member_name']);
					$this->excel->getActiveSheet()->setCellValue('E'.$j, $val['part_name']);
					$this->excel->getActiveSheet()->setCellValue('F'.$j, $val['credits_name']);
					$this->excel->getActiveSheet()->setCellValue('G'.$j, $val['total_principal']);
					$this->excel->getActiveSheet()->setCellValue('H'.$j, $val['total_interest']);
					$this->excel->getActiveSheet()->setCellValue('I'.$j, $val['total_last_principal']);

					$totalpokok 	+= $val['total_principal'];
					$totalbunga 	+= $val['total_interest'];
					$totalsisapokok += $val['total_last_principal'];
					$j++;
				}

				$i = $j;

				$this->excel->getActiveSheet()->getStyle('B'.$i.':I'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
				$this->excel->getActiveSheet()->getStyle('B'.$i.':I'.$i)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('F'.$j.':J'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->mergeCells('B'.$i.':F'.$i);
				$this->excel->getActiveSheet()->setCellValue('B'.$i, 'Total');

				$this->excel->getActiveSheet()->setCellValue('G'.$i, $totalpokok);
				$this->excel->getActiveSheet()->setCellValue('H'.$i, $totalbunga);
				$this->excel->getActiveSheet()->setCellValue('I'.$i, $totalsisapokok);

				$filename='DAFTAR ANGSURAN PINJAMAN'.$sesi['start_date'].' s.d. '.$sesi['end_date'].'.xls';
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
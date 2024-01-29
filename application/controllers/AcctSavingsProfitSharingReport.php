<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctSavingsProfitSharingReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctSavingsProfitSharingReport_model');
			$this->load->model('AcctSavingsAccount_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}

		public function index(){
			$data['main_view']['content']	= 'AcctSavingsProfitSharingReport/ListAcctSavingsProfitSharingReport_view';
			$this->load->view('MainPage_view',$data);
		}

		public function viewreport(){
			$sesi = array (
				"view"				=> $this->input->post('view',true),
			);

			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
		}

		public function processPrinting($sesi){
			$auth 	= $this->session->userdata('auth');
			$date 	= date('Y-m-d');
			$month 	= date('m', strtotime($date));
			$year 	= date('Y', strtotime($date));

			if($month == 1){
				$month 	= 12;
				$year 	= $year - 1;
			} else {
				$month 	= $month - 1;
				$year 	= $year;
			}

			$period = $month.$year;

			$acctsavingsprofitsharing 	= $this->AcctSavingsProfitSharingReport_model->getAcctSavingsProfitSharing($period);
			$preference					= $this->AcctSavingsProfitSharingReport_model->getPreferenceCompany();

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

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 9);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preference['logo_koperasi']."\" alt=\"\" width=\"950%\" height=\"300%\"/>";

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
			        <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR BUNGA TABUNGAN BULANAN</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			    	<td width=\"13%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">No</div></td>
			        <td width=\"10%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">No. Rek</div></td>
			        <td width=\"20%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Jenis Tabungan</div></td>
			        <td width=\"25%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Nama</div></td>
			        <td width=\"16%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Nominal</div></td>
			        <td width=\"16%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Saldo</div></td>
			    </tr>
			</table>";

			$no = 1;

			$tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
			foreach ($acctsavingsprofitsharing as $key => $val) {
				$tbl3 .= "
					<tr>
				    	<td width=\"13%\"><div style=\"text-align: center;\">$no</div></td>
				        <td width=\"10%\"><div style=\"text-align: left;\">".$val['savings_account_no']."</div></td>
				        <td width=\"20%\"><div style=\"text-align: left;\">".$val['savings_name']."</div></td>
				        <td width=\"25%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
				        <td width=\"16%\"><div style=\"text-align: right;\">".number_format($val['savings_profit_sharing_amount'], 2)."</div></td>
				        <td width=\"16%\"><div style=\"text-align: right;\">".number_format($val['savings_account_last_balance'], 2)."</div></td>
				    </tr>
				";

				$total += $val['savings_profit_sharing_amount'];
				$no++;
			}
			
			$tbl4 = "
				<tr>
					<td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".$this->AcctSavingsProfitSharingReport_model->getUserName($auth['user_id'])."</div></td>
					<td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
					<td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($total, 2)."</div></td>
					<td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"></div></td>
				</tr>
			</table>";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			$filename = 'Kwitansi.pdf';
			$pdf->Output($filename, 'I');
		}

		public function export($sesi){
			$auth 	= $this->session->userdata('auth');

			$date 	= date('Y-m-d');
			$month 	= date('m', strtotime($date));
			$year 	= date('Y', strtotime($date));

			if($month == 1){
				$month 	= 12;
				$year 	= $year - 1;
			} else {
				$month 	= $month - 1;
				$year 	= $year;
			}

			$period = $month.$year;

			$acctsavingsprofitsharing 	= $this->AcctSavingsProfitSharingReport_model->getAcctSavingsProfitSharing($period);
			$preference					= $this->AcctSavingsProfitSharingReport_model->getPreferenceCompany();

			$this->load->library('Excel');

			$this->excel->getProperties()->setCreator("KSU MANDIRI")
								 ->setLastModifiedBy("KSU MANDIRI")
								 ->setTitle("Laporan Bunga Tabungan Bulanan")
								 ->setSubject("")
								 ->setDescription("Laporan Bunga Tabungan Bulanan")
								 ->setKeywords("Laporan, Bunga, Tabungan, Bulanan")
								 ->setCategory("Laporan Bunga Tabungan Bulanan");

			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

			$this->excel->getActiveSheet()->mergeCells("B1:G1");
			$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$this->excel->getActiveSheet()->getStyle('B3:G3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B3:G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B3:G3')->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B1',"LAPORAN BUNGA TABUNGAN BULANAN");
			$this->excel->getActiveSheet()->setCellValue('B3',"No");
			$this->excel->getActiveSheet()->setCellValue('C3',"No. Rek");
			$this->excel->getActiveSheet()->setCellValue('D3',"Jenis");
			$this->excel->getActiveSheet()->setCellValue('E3',"Nama");
			$this->excel->getActiveSheet()->setCellValue('F3',"Nominal");
			$this->excel->getActiveSheet()->setCellValue('G3',"Saldo");

			$j				= 3;
			$no				= 0;
			$total_nominal 	= 0;
			$total_saldo 	= 0;

			foreach($acctsavingsprofitsharing as $key => $val){
				$no++;
				$j++;

				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getStyle('B'.$j.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
				$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j, $val['savings_account_no']);
				$this->excel->getActiveSheet()->setCellValueExplicit('D'.$j, $val['savings_name']);
				$this->excel->getActiveSheet()->setCellValueExplicit('E'.$j, $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('F'.$j, $val['savings_profit_sharing_amount']);
				$this->excel->getActiveSheet()->setCellValue('G'.$j, $val['savings_account_last_balance']);

				$total_nominal 	+= $val['savings_profit_sharing_amount'];
				$total_saldo 	+= $val['savings_account_last_balance'];
			}

			$this->excel->getActiveSheet()->mergeCells('B'.($j+1).':E'.($j+1));
			$this->excel->getActiveSheet()->getStyle('B'.($j+1).':G'.($j+1))->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('B'.($j+1).':G'.($j+1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

			$this->excel->getActiveSheet()->getStyle('B'.($j+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('F'.($j+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('G'.($j+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

			$this->excel->getActiveSheet()->setCellValue('B'.($j+1), 'TOTAL');
			$this->excel->getActiveSheet()->setCellValue('F'.($j+1), $total_nominal);
			$this->excel->getActiveSheet()->setCellValue('G'.($j+1), $total_saldo);

			$filename='Laporan Bunga Tabungan Bulanan.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');

			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
			ob_end_clean();
			$objWriter->save('php://output');
		}
	}
?>
<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctDepositoSimapanReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctDepositoSimapanReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data['main_view']['content']							= 'AcctDepositoSimapanReport/ListAcctDepositoSimapanReport_view';
			$this->load->view('MainPage_view',$data);
		}

		public function viewreport(){
			$sesi = array (
				"view"									=> $this->input->post('view',true),
			);
			
			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
		}

		public function processPrinting($sesi){
			$auth 	=	$this->session->userdata('auth'); 
			$preferencecompany = $this->AcctDepositoSimapanReport_model->getPreferenceCompany();

			$acctdepositoaccount	= $this->AcctDepositoSimapanReport_model->getAcctDepositoSimapanReport();
			$acctdeposito 			= $this->AcctDepositoSimapanReport_model->getAcctDeposito();

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
			$pdf->AddPage('L', array(400, 200));
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
			<br/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
				<tr>
					<td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR SIMPANAN MASA DEPAN</div></td>
				</tr>
				<tr>
					<td><div style=\"text-align: center; font-size:10px\">".date('M Y')."</div></td>
				</tr>
			</table>";

			$pdf->writeHTML($tbl0.$tbl, true, false, false, false, '');

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No.</div></td>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tgl Bunga</div></td>
			        <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No Sertifikat</div></td>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tgl Buka</div></td>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Jk Waktu (Bl)</div></td>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tgl Jt Tempo</div></td>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tgl Jt ARO</div></td>
			        <td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No Anggota</div></td>
			        <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
			        <td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bagian</div></td>
			        <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nominal</div></td>
			        <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bunga Pertahun</div></td>
			        <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bunga Perbulan</div></td>
			        <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">PPH 10%</div></td>
			    </tr>				
			</table>";

			$tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
			
			$no 				= 1;
			$totalnominal 		= 0;
			$totalbungatahun 	= 0;
			$totalbungabulan 	= 0;
			$totalpph 			= 0;
			foreach ($acctdepositoaccount as $key => $val) {
				$due_date = '';
				$aro_date = '';
				if($val['deposito_account_extra_type'] == 0){
					$due_date = $val['deposito_account_due_date'];
				}else{
					$aro_date = $val['deposito_account_due_date'];
				}

				$bungarupiah = $val['deposito_account_amount'] * $val['deposito_interest_rate'] / 12 / 100;

				$tbl3 .= "
				<tr>
					<td width=\"5%\"><div style=\"text-align: center;\">".$no."</div></td>
					<td width=\"5%\"><div style=\"text-align: center;\">".date('d', strtotime($val['deposito_account_date']))."</div></td>
					<td width=\"10%\"><div style=\"text-align: center;\">".$val['deposito_account_no']."</div></td>
					<td width=\"5%\"><div style=\"text-align: center;\">".tgltoview($val['deposito_account_date'])."</div></td>
					<td width=\"5%\"><div style=\"text-align: center;\">".$val['deposito_account_period']."</div></td>
					<td width=\"5%\"><div style=\"text-align: center;\">".tgltoview($due_date)."</div></td>
					<td width=\"5%\"><div style=\"text-align: center;\">".tgltoview($aro_date)."</div></td>
					<td width=\"5%\"><div style=\"text-align: center;\">".$val['member_no']."</div></td>
					<td width=\"10%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
					<td width=\"5%\"><div style=\"text-align: left;\">".$val['part_name']."</div></td>
					<td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['deposito_account_amount'], 2)."</div></td>
					<td width=\"10%\"><div style=\"text-align: center;\">".$val['deposito_interest_rate']."%</div></td>
					<td width=\"10%\"><div style=\"text-align: right;\">".number_format($bungarupiah, 2)."</div></td>
					<td width=\"10%\"><div style=\"text-align: right;\">".number_format($bungarupiah*10/100, 2)."</div></td>
				</tr>
				";

				$totalnominal 		+= $val['deposito_account_amount'];
				$totalbungatahun 	+= $val['deposito_interest_rate'];
				$totalbungabulan 	+= $bungarupiah;
				$totalpph 			+= ($bungarupiah*10/100);
				$no++;
			}

			$tbl4 = "
				<tr>
					<td colspan =\"9\"><div style=\"font-size:10;font-style:italic;text-align:left\">Printed : ".date('d-m-Y H:i:s')."  ".$this->AcctDepositoSimapanReport_model->getUserName($auth['user_id'])."</div></td>
					<td><div style=\"font-size:10;font-weight:bold;text-align:center\">Total</div></td>
					<td style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalnominal, 2)."</div></td>
					<td style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:center\">".number_format($totalbungatahun, 2)."%</div></td>
					<td style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalbungabulan, 2)."</div></td>
					<td style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalpph, 2)."</div></td>
				</tr>
			</table>";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			if (ob_get_length() > 0){
				ob_clean();
			}
			// -----------------------------------------------------------------------------
			
			$filename = 'Daftar Simpanan Masa Depan Berjangka.pdf';
			$pdf->Output($filename, 'I');
		}

		public function export($sesi){
			$auth 	=	$this->session->userdata('auth'); 

			$acctdepositoaccount	= $this->AcctDepositoSimapanReport_model->getAcctDepositoSimapanReport();
			$acctdeposito 			= $this->AcctDepositoSimapanReport_model->getAcctDeposito();

			if(count($acctdepositoaccount) !=0){
				$this->load->library('Excel');
				
				$this->excel->getProperties()->setCreator("CST FISRT")
									 ->setLastModifiedBy("CST FISRT")
									 ->setTitle("Daftar Simpanan Masa Depan")
									 ->setSubject("")
									 ->setDescription("Daftar Simpanan Masa Depan")
									 ->setKeywords("Laporan, Nominatif, Simpanan")
									 ->setCategory("Daftar Simpanan Masa Depan");
									 
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);		
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);		
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(40);		
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);		
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);		
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);		
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);		
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);		
				
				$this->excel->getActiveSheet()->mergeCells("B1:O1");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B3:O3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B3:O3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3:O3')->getFont()->setBold(true);
			
				$this->excel->getActiveSheet()->setCellValue('B1',"DAFTAR SIMPANAN MASA DEPAN");
				
				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"Tgl Bunga");
				$this->excel->getActiveSheet()->setCellValue('D3',"No Sertifikat");
				$this->excel->getActiveSheet()->setCellValue('E3',"Tanggal Buka");
				$this->excel->getActiveSheet()->setCellValue('F3',"Jangka Waktu (Bl)");
				$this->excel->getActiveSheet()->setCellValue('G3',"Tanggal Jatuh Tempo");
				$this->excel->getActiveSheet()->setCellValue('H3',"Tanggal Jatuh ARO");
				$this->excel->getActiveSheet()->setCellValue('I3',"No Anggota");
				$this->excel->getActiveSheet()->setCellValue('J3',"Nama");
				$this->excel->getActiveSheet()->setCellValue('K3',"Bagian");
				$this->excel->getActiveSheet()->setCellValue('L3',"Nominal");
				$this->excel->getActiveSheet()->setCellValue('M3',"Bunga Pertahun");
				$this->excel->getActiveSheet()->setCellValue('N3',"Bunga Perbulan");
				$this->excel->getActiveSheet()->setCellValue('O3',"PPH 10%");
				
				$j				 	= 4;
				$no				 	= 0;
				$totalnominal 		= 0;
				$totalbungatahun 	= 0;
				$totalbungabulan 	= 0;
				$totalpph 			= 0;
				foreach($acctdepositoaccount as $key=>$val){
					$due_date = '';
					$aro_date = '';
					if($val['deposito_account_extra_type'] == 0){
						$due_date = $val['deposito_account_due_date'];
					}else{
						$aro_date = $val['deposito_account_due_date'];
					}
	
					$bungarupiah = $val['deposito_account_amount'] * $val['deposito_interest_rate'] / 12 / 100;

					$no++;
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->getStyle('B'.$j.':I'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('L'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('M'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('N'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('O'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
					$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j, date('d', strtotime($val['deposito_acocunt_date'])));
					$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['deposito_account_no']);
					$this->excel->getActiveSheet()->setCellValue('E'.$j, $val['deposito_account_date']);
					$this->excel->getActiveSheet()->setCellValue('F'.$j, $val['deposito_account_period']);
					$this->excel->getActiveSheet()->setCellValue('G'.$j, $due_date);
					$this->excel->getActiveSheet()->setCellValue('H'.$j, $aro_date);
					$this->excel->getActiveSheet()->setCellValueExplicit('I'.$j, $val['member_no']);
					$this->excel->getActiveSheet()->setCellValue('J'.$j, $val['member_name']);
					$this->excel->getActiveSheet()->setCellValue('K'.$j, $val['part_name']);
					$this->excel->getActiveSheet()->setCellValueExplicit('L'.$j, $val['deposito_account_amount']);
					$this->excel->getActiveSheet()->setCellValueExplicit('M'.$j, $val['deposito_interest_rate']);
					$this->excel->getActiveSheet()->setCellValueExplicit('N'.$j, $bungarupiah);
					$this->excel->getActiveSheet()->setCellValueExplicit('O'.$j, $bungarupiah*10/100);
						
					$totalnominal 		+= $val['deposito_account_amount'];
					$totalbungatahun 	+= $val['deposito_interest_rate'];
					$totalbungabulan 	+= $bungarupiah;
					$totalpph 			+= ($bungarupiah*10/100);
					$j++;
				}

				$n = $j;

				$this->excel->getActiveSheet()->getStyle('B'.$n.':O'.$n)->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B'.$n.':O'.$n)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
				$this->excel->getActiveSheet()->getStyle('B'.$n.':O'.$n)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('L'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('M'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('N'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('O'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->mergeCells('B'.$n.':K'.$n);
				$this->excel->getActiveSheet()->setCellValue('B'.$n, 'Total');
				$this->excel->getActiveSheet()->setCellValueExplicit('L'.$n, $totalnominal);
				$this->excel->getActiveSheet()->setCellValueExplicit('M'.$n, $totalbungatahun);
				$this->excel->getActiveSheet()->setCellValueExplicit('N'.$n, $totalbungabulan);
				$this->excel->getActiveSheet()->setCellValueExplicit('O'.$n, $totalpph);
				
				$filename='Daftar Simpanan Masa Depan.xls';
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
	}
?>
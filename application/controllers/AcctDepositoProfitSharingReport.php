<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctDepositoProfitSharingReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctDepositoProfitSharingReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}

		public function index(){
			$data['main_view']['corebranch']	= create_double($this->AcctDepositoProfitSharingReport_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content'] 		= 'AcctDepositoProfitSharingReport/FormFilterAcctDepositoProfitSharingReport_view';
			$this->load->view('MainPage_view', $data);
		}

		public function viewreport(){
			$sesi = array (
				"start_date" 	=> tgltodb($this->input->post('start_date',true)),
				"end_date"		=> tgltodb($this->input->post('end_date', true)),
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

			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}
			
			$acctdepositoprofitsharing 	= $this->AcctDepositoProfitSharingReport_model->getAcctDepositoProfitSharing($sesi['start_date'], $sesi['end_date'], $branch_id);
			$preference					= $this->AcctDepositoProfitSharingReport_model->getPreferenceCompany();

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
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td><div style=\"text-align: left;font-size:12;\">DAFTAR BUNGA SIMP BERJANGKA BULAN INI</div></td>			       
			    </tr>						
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
			        <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Jatuh Tempo</div></td>
			        <td width=\"13%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Sertifikat</div></td>
			        <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Agt</div></td>
			        <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
			        <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bunga</div></td>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Pajak</div></td>
			        <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Saldo Deposito</div></td>
			        <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Saldo Tabungan</div></td>
			    </tr>				
			</table>";

			$tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

			$no 			= 1;
			$totalbunga 	= 0;
			$totalpajak 	= 0;
			$totaldeposito 	= 0;
			$totaltabungan 	= 0;

			foreach ($acctdepositoprofitsharing as $key => $val) {
				$tbl3 .= "
					<tr>
				    	<td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
				        <td width=\"12%\"><div style=\"text-align: left;\">".tgltoview($val['deposito_profit_sharing_due_date'])."</div></td>
				        <td width=\"13%\"><div style=\"text-align: left;\">".$val['deposito_account_no']."</div></td>
				        <td width=\"10%\"><div style=\"text-align: center;\">".$val['member_no']."</div></td>
				        <td width=\"15%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
				        <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['deposito_profit_sharing_amount'])."</div></td>
				        <td width=\"5%\"><div style=\"text-align: right;\">".number_format($val['deposito_profit_sharing_tax'])."</div></td>
				        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['deposito_account_last_balance'])."</div></td>
				        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['savings_account_last_balance'])."</div></td>
				    </tr>
				";

				$totalbunga 	+= $val['deposito_profit_sharing_amount'];
				$totalpajak 	+= $val['deposito_profit_sharing_tax'];
				$totaldeposito 	+= $val['deposito_account_last_balance'];
				$totaltabungan 	+= $val['savings_account_last_balance'];
				$no++;
			}

			$tbl4 = "
				<tr>
					<td colspan =\"4\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".$this->AcctDepositoProfitSharingReport_model->getUserName($auth['user_id'])."</div></td>
					<td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
					<td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalbunga)."</div></td>
					<td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalpajak)."</div></td>
					<td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totaldeposito)."</div></td>
					<td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totaltabungan)."</div></td>
				</tr>
			</table>";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------

			$filename = 'DAFTAR BUNGA SIMP BERJANGKA BULAN INI.pdf';
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

			$acctdepositoprofitsharing 	= $this->AcctDepositoProfitSharingReport_model->getAcctDepositoProfitSharing($sesi['start_date'], $sesi['end_date'], $branch_id);
			$preference					= $this->AcctDepositoProfitSharingReport_model->getPreferenceCompany();
			
			if(count($acctdepositoprofitsharing) !=0){
				$this->load->library('Excel');
				
				$this->excel->getProperties()->setCreator("CST FISRT")
									 ->setLastModifiedBy("CST FISRT")
									 ->setTitle("DAFTAR BUNGA SIMP BERJANGKA BULAN INI")
									 ->setSubject("")
									 ->setDescription("DAFTAR BUNGA SIMP BERJANGKA BULAN INI")
									 ->setKeywords("DAFTAR, BUNGA, SIMP BERJANGKA")
									 ->setCategory("DAFTAR BUNGA SIMP BERJANGKA BULAN INI");
									 
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
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

				$this->excel->getActiveSheet()->setCellValue('B1',"DAFTAR BUNGA SIMP BERJANGKA BULAN INI");
				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"Jatuh Tempo");
				$this->excel->getActiveSheet()->setCellValue('D3',"No. Simpanan Berjangka");
				$this->excel->getActiveSheet()->setCellValue('E3',"No Anggota");
				$this->excel->getActiveSheet()->setCellValue('F3',"Nama");
				$this->excel->getActiveSheet()->setCellValue('G3',"Bunga");
				$this->excel->getActiveSheet()->setCellValue('H3',"Pajak");
				$this->excel->getActiveSheet()->setCellValue('I3',"Saldo Deposito");
				$this->excel->getActiveSheet()->setCellValue('J3',"Saldo Tabungan");
				
				$no				= 0;
				$totalbunga 	= 0;
				$totalpajak 	= 0;
				$totaldeposito 	= 0;
				$totaltabungan 	= 0;
				$j				= 4;
				foreach($acctdepositoprofitsharing as $key=>$val){
					if(is_numeric($key)){
						$no++;
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->getStyle('B'.$j.':J'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$this->excel->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$this->excel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						$this->excel->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
						
						$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
						$this->excel->getActiveSheet()->setCellValue('C'.$j, tgltoview($val['deposito_profit_sharing_due_date']));
						$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['deposito_account_no']);
						$this->excel->getActiveSheet()->setCellValueExplicit('E'.$j, $val['member_no']);
						$this->excel->getActiveSheet()->setCellValue('F'.$j, $val['member_name']);
						$this->excel->getActiveSheet()->setCellValue('G'.$j, $val['deposito_profit_sharing_amount']);
						$this->excel->getActiveSheet()->setCellValue('H'.$j, $val['deposito_profit_sharing_tax']);
						$this->excel->getActiveSheet()->setCellValue('I'.$j, $val['deposito_account_last_balance']);
						$this->excel->getActiveSheet()->setCellValue('J'.$j, $val['savings_account_last_balance']);
			
						$totalbunga 	+= $val['deposito_profit_sharing_amount'];
						$totalpajak 	+= $val['deposito_profit_sharing_tax'];
						$totaldeposito 	+= $val['deposito_account_last_balance'];
						$totaltabungan 	+= $val['savings_account_last_balance'];
					}else{
						continue;
					}
					$j++;
				}

				$i = $j;

				$this->excel->getActiveSheet()->getStyle('B'.$i.':J'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
				$this->excel->getActiveSheet()->getStyle('B'.$i.':J'.$i)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->mergeCells('B'.$i.':F'.$i);
				$this->excel->getActiveSheet()->setCellValue('B'.$i, 'Total');
				$this->excel->getActiveSheet()->setCellValue('G'.$i, $totalbunga);
				$this->excel->getActiveSheet()->setCellValue('H'.$i, $totalpajak);
				$this->excel->getActiveSheet()->setCellValue('I'.$i, $totaldeposito);
				$this->excel->getActiveSheet()->setCellValue('J'.$i, $totaltabungan);
				
				$filename='DAFTAR BUNGA SIMP BERJANGKA BULAN INI.xls';
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
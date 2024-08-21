<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctSavingsSicantikReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctSavingsSicantikReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data['main_view']['content']	= 'AcctSavingsSicantikReport/ListAcctSavingsSicantikReport_view';
			$this->load->view('MainPage_view',$data);
		}

		public function viewreport(){
			$sesi = array (
				"view"	=> $this->input->post('view',true),
			);
			
			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
		}

		public function processPrinting($sesi){
			$auth 					= $this->session->userdata('auth'); 
			$preferencecompany 		= $this->AcctSavingsSicantikReport_model->getPreferenceCompany();
			$acctsavingsaccount		= $this->AcctSavingsSicantikReport_model->getAcctSavingsSicantikReport();
			$acctsavings 			= $this->AcctSavingsSicantikReport_model->getAcctSavings();

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
					<td><div style=\"text-align: center; font-size:14px;font-weight:bold\">REKAPITULASI SI CANTIK</div></td>
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
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Agt</div></td>
			        <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bagian</div></td>
			        <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Setoran Awal</div></td>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tempo</div></td>
			        <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nominal Kembali</div></td>
			        <td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tgl Jt. Tempo</div></td>
			        <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Saldo Awal</div></td>
			        <td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bulan Potong</div></td>
			        <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Pencairan</div></td>
			        <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Total Dana Masuk</div></td>
			        <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bunga</div></td>
			    </tr>				
			</table>";

			$tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
			
			$no 					= 1;
			$totalsetoranawal		= 0;
			$totalnominalkembali 	= 0;
			$totalsaldoawal 		= 0;
			$totalbbulanpotong 		= 0;
			$totalpencairan 		= 0;
			$totaldanamasuk 		= 0;
			$totaldanabunga 		= 0;
			foreach ($acctsavingsaccount as $key => $val) {
				$due_date = '';
				$aro_date = '';
				if($val['savings_account_extra_type'] == 0){
					$due_date = $val['savings_account_due_date'];
				}else{
					$aro_date = $val['savings_account_due_date'];
				}

				$bungarupiah = $val['savings_account_amount'] * $val['savings_interest_rate'] / 12 / 100;

				$tbl3 .= "
				<tr>
					<td width=\"5%\" ><div style=\"text-align: center;\">".$no."</div></td>
					<td width=\"5%\" ><div style=\"text-align: center;\">".$val['member_no']."</div></td>
					<td width=\"10%\" ><div style=\"text-align: left;\">".$val['member_name']."</div></td>
					<td width=\"5%\" ><div style=\"text-align: left;\">".$val['part_name']."</div></td>
					<td width=\"10%\" ><div style=\"text-align: right;\">".number_format($val['savings_account_first_deposit_amount'])."</div></td>
					<td width=\"5%\" ><div style=\"text-align: center;\"></div></td>
					<td width=\"10%\" ><div style=\"text-align: center;\"></div></td>
					<td width=\"5%\"><div style=\"text-align: center;\"></div></td>
					<td width=\"10%\"><div style=\"text-align: center;\"></div></td>
					<td width=\"5%\"><div style=\"text-align: right;\">250.000</div></td>
					<td width=\"10%\"><div style=\"text-align: right;\"></div></td>
					<td width=\"10%\"><div style=\"text-align: right;\"></div></td>
					<td width=\"10%\"><div style=\"text-align: right;\"></div></td>
				</tr>
				";

				$totalsetoranawal		+= $val['savings_account_first_deposit_amount'];
				$totalnominalkembali 	+= 0;
				$totalsaldoawal 		+= 0;
				$totalbbulanpotong 		+= 250000;
				$totalpencairan 		+= 0;
				$totaldanamasuk 		+= 0;
				$totaldanabunga 		+= 0;
				$no++;
			}

			$tbl4 = "
				<tr>
					<td width=\"25%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Total</div></td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalsetoranawal)."</div></td>
					<td width=\"5%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"></div></td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"></div></td>
					<td width=\"5%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"></div></td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">0</div></td>
					<td width=\"5%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalbbulanpotong)."</div></td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">0</div></td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">0</div></td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;font-weight:bold;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">0</div></td>
				</tr>
				<tr>
					<td colspan =\"9\"><div style=\"font-size:10;font-style:italic;text-align:left\">Printed : ".date('d-m-Y H:i:s')."  ".$this->AcctSavingsSicantikReport_model->getUserName($auth['user_id'])."</div></td>
				</tr>
				<tr>
					<td style=\"height:50px;\"></td>
				</tr>
				<tr>
					<td width=\"75%\"></td>
					<td width=\"25%\">Mengetahui,</td>
				</tr>
				<tr>
					<td style=\"height:50px;\"></td>
				</tr>
				<tr>
					<td width=\"75%\"></td>
					<td width=\"25%\" style=\"text-decoration:underline\">Yuli Risdianto</td>
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

			$acctsavingsaccount	= $this->AcctSavingsSicantikReport_model->getAcctSavingsSicantikReport();
			$acctsavings 		= $this->AcctSavingsSicantikReport_model->getAcctSavings();

			if(count($acctsavingsaccount) !=0){
				$this->load->library('Excel');
				
				$this->excel->getProperties()->setCreator("CST FISRT")
									 ->setLastModifiedBy("CST FISRT")
									 ->setTitle("Daftar Simpanan SiCantik")
									 ->setSubject("")
									 ->setDescription("Daftar Simpanan SiCantik")
									 ->setKeywords("Laporan, Nominatif, Simpanan")
									 ->setCategory("Daftar Simpanan SiCantik");
									 
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
				
				$this->excel->getActiveSheet()->mergeCells("B1:N1");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B3:N3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B3:N3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3:N3')->getFont()->setBold(true);
			
				$this->excel->getActiveSheet()->setCellValue('B1',"DAFTAR SIMPANAN MASA DEPAN");
				
				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"No Agt");
				$this->excel->getActiveSheet()->setCellValue('D3',"Nama");
				$this->excel->getActiveSheet()->setCellValue('E3',"Bagian");
				$this->excel->getActiveSheet()->setCellValue('F3',"Setoran Awal");
				$this->excel->getActiveSheet()->setCellValue('G3',"Tempo");
				$this->excel->getActiveSheet()->setCellValue('H3',"Nominal Kembali");
				$this->excel->getActiveSheet()->setCellValue('I3',"Tgl Jt Tempo");
				$this->excel->getActiveSheet()->setCellValue('J3',"Saldo Awal");
				$this->excel->getActiveSheet()->setCellValue('K3',"Bulan Potong");
				$this->excel->getActiveSheet()->setCellValue('L3',"Pencairan");
				$this->excel->getActiveSheet()->setCellValue('M3',"Total Dana Masuk");
				$this->excel->getActiveSheet()->setCellValue('N3',"Bunga");
				
				$j				 		= 4;
				$no				 		= 0;
				$totalsetoranawal		= 0;
				$totalnominalkembali 	= 0;
				$totalsaldoawal 		= 0;
				$totalbbulanpotong 		= 0;
				$totalpencairan 		= 0;
				$totaldanamasuk 		= 0;
				$totaldanabunga 		= 0;
				foreach($acctsavingsaccount as $key=>$val){
					$due_date = '';
					$aro_date = '';
					if($val['savings_account_extra_type'] == 0){
						$due_date = $val['savings_account_due_date'];
					}else{
						$aro_date = $val['savings_account_due_date'];
					}
	
					$bungarupiah = $val['savings_account_amount'] * $val['savings_interest_rate'] / 12 / 100;

					$no++;
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->getStyle('B'.$j.':N'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('L'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('M'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('N'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('O'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
					$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j, $val['member_no']);
					$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
					$this->excel->getActiveSheet()->setCellValue('E'.$j, $val['part_name']);
					$this->excel->getActiveSheet()->setCellValue('F'.$j, $val['savings_account_first_deposit_amount']);
					$this->excel->getActiveSheet()->setCellValue('G'.$j, '');
					$this->excel->getActiveSheet()->setCellValue('H'.$j, '');
					$this->excel->getActiveSheet()->setCellValue('I'.$j, '');
					$this->excel->getActiveSheet()->setCellValue('J'.$j, '');
					$this->excel->getActiveSheet()->setCellValue('K'.$j, 250000);
					$this->excel->getActiveSheet()->setCellValue('L'.$j, '');
					$this->excel->getActiveSheet()->setCellValue('M'.$j, '');
					$this->excel->getActiveSheet()->setCellValue('N'.$j, '');
					$this->excel->getActiveSheet()->setCellValue('O'.$j, '');
						
					$totalsetoranawal		= $val['savings_account_first_deposit_amount'];
					$totalnominalkembali 	= 0;
					$totalsaldoawal 		= 0;
					$totalbbulanpotong 		= 250000;
					$totalpencairan 		= 0;
					$totaldanamasuk 		= 0;
					$totaldanabunga 		= 0;
					$j++;
				}

				$n = $j;

				$this->excel->getActiveSheet()->getStyle('B'.$n.':N'.$n)->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B'.$n.':N'.$n)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
				$this->excel->getActiveSheet()->getStyle('B'.$n.':N'.$n)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('F'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('K'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->mergeCells('B'.$n.':E'.$n);
				$this->excel->getActiveSheet()->setCellValue('B'.$n, 'Total');
				$this->excel->getActiveSheet()->setCellValueExplicit('F'.$n, (int)$totalsetoranawal);
				$this->excel->getActiveSheet()->setCellValueExplicit('K'.$n, (int)$totalbbulanpotong);
				$this->excel->getActiveSheet()->setCellValue('J'.($n+3), 'Mengetahui,');
				$this->excel->getActiveSheet()->setCellValue('J'.($n+6), 'Yuli Risdianto');
				
				$filename='Daftar Simpanan SiCantik.xls';
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
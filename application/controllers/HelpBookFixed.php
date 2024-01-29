<?php ob_start(); ?>
<?php
	ini_set('memory_limit', '512M');
	defined('BASEPATH') OR exit('No direct script access allowed');


	Class HelpBookFixed extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('HelpBookFixed_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$corebranch 					= create_double_branch($this->HelpBookFixed_model->getCoreBranch(),'branch_id','branch_name');
			
			$data['main_view']['content']	= 'HelpBookFixed/HelpBookFixed_view';
			$this->load->view('MainPage_view',$data);
		}
 
		public function viewreport(){

			$sesi = array (
				"start_date"	=> $this->input->post('start_date', true),
				"end_date"		=> $this->input->post('end_date', true),
				"view"			=> $this->input->post('view',true),
			);

			if($sesi['view'] == 'pdf'){
				$this->print($sesi);
			}else{
				$this->export($sesi);
			}
		}

		public function print($sesi){
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->HelpBookFixed_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$data_debit 	= $this->HelpBookFixed_model->getHelpBookFixedDebit($sesi);

			$data_kredit 	= $this->HelpBookFixed_model->getHelpBookFixedKredit($sesi);

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

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 9);

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
			        <td width=\"100%\"><div style=\"text-align: center; font-size:14px; font-weight:bold\">BUKU PEMBANTU ASET TETAP</div></td>
			    </tr>
				<tr>
					<td width=\"100%\"><div style=\"text-align: center; font-size:12px;\">Per ".$sesi['start_date']." s.d ".$sesi['end_date']."</div></td>
				</tr>
			</table>
			<br>
			<br>
			<br>";

			$tbl .= "
			<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
				<tr style=\"background-color:#6fbf73;\">
					<td width=\"100%\" style=\"font-size:13px; font-weight:bold; text-align: center;\" colspan=\"5\">Debit (Pengambilan)</td>
				</tr>
				<tr style=\"background-color:#6fbf73;\">
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">No.</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">No Jurnal</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Tanggal</td>
					<td width=\"30%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Keterangan</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Jumlah</td>
				</tr>
			</table>
			";

			$total_debit	= 0;
			$no				= 1;

			foreach($data_debit as $key => $val){
				$tbl .= "
				<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
					<tr>
						<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".$no."</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: center;\">".$val['journal_voucher_no']."</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: left;\">".date('d-m-Y', strtotime($val['journal_voucher_date']))."</td>
						<td width=\"30%\" style=\"font-size:13px; text-align: left;\">".$val['journal_voucher_title']."</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: right;\">".number_format($val['journal_voucher_amount'], 2)."</td>
					</tr>
				</table>";

				$total_debit += $val['journal_voucher_amount'];
				$no++;
			}

			$tbl .= "
			<br>
			<br>
			<br>
			<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
				<tr style=\"background-color:#ffb64c;\">
					<td width=\"100%\" style=\"font-size:13px; font-weight:bold; text-align: center;\" colspan=\"5\">Kredit (Pengembalian)</td>
				</tr>
				<tr style=\"background-color:#ffb64c;\">
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">No.</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">No Jurnal</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Tanggal</td>
					<td width=\"30%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Keterangan</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Jumlah</td>
				</tr>
			</table>
			";

			$total_kredit	= 0;
			$no				= 1;

			foreach($data_kredit as $key => $val){
				$tbl .= "
				<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
					<tr>
						<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".$no."</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: center;\">".$val['journal_voucher_no']."</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: left;\">".date('d-m-Y', strtotime($val['journal_voucher_date']))."</td>
						<td width=\"30%\" style=\"font-size:13px; text-align: left;\">".$val['journal_voucher_title']."</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: right;\">".number_format($val['journal_voucher_amount'], 2)."</td>
					</tr>
				</table>";
				
				$total_kredit += $val['journal_voucher_amount'];
				$no++;
			}

			$tbl .= "
			<br>
			<br>
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"60%\"><div style=\"text-align: center; font-size:13px; font-weight:bold\"></div></td>
			        <td width=\"40%\"><div style=\"text-align: right; font-size:13px; font-weight:bold\"> Saldo Rp ".number_format(($total_debit - $total_kredit), 2)."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, '');

			ob_clean();
			$filename = 'Buku Pembantu - Hutang Lain - Lain.pdf';
			$pdf->Output($filename, 'I');
		}

		public function export($sesi){	
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->HelpBookFixed_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$data_debit 	= $this->HelpBookFixed_model->getHelpBookFixedDebit($sesi);

			$data_kredit 	= $this->HelpBookFixed_model->getHelpBookFixedKredit($sesi);

			$this->load->library('Excel');
			$this->excel->getProperties()->setCreator("CST FISRT")
									->setLastModifiedBy("CST FISRT")
									->setTitle("Buku Pembantu - Aset Tetap")
									->setSubject("")
									->setDescription("Buku Pembantu - Aset Tetap")
									->setKeywords("Buku Pembantu - Aset Tetap")
									->setCategory("Buku Pembantu - Aset Tetap");
									
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(30);

			$this->excel->getActiveSheet()->mergeCells("B2:K2");
			$this->excel->getActiveSheet()->mergeCells("B3:K3");
			$this->excel->getActiveSheet()->mergeCells("B7:B8");
			$this->excel->getActiveSheet()->mergeCells("C7:F7");
			$this->excel->getActiveSheet()->mergeCells("G7:J7");
			$this->excel->getActiveSheet()->mergeCells("K7:K8");
			$this->excel->getActiveSheet()->getStyle('B2:K8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B2:K8')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('I5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B2:K8')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('C7:F8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('42f590');
			$this->excel->getActiveSheet()->getStyle('G7:J8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f5bc42');
			$this->excel->getActiveSheet()->getStyle('B7:B8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('cccccc');
			$this->excel->getActiveSheet()->getStyle('K7:K8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('cccccc');
			
			$this->excel->getActiveSheet()->setCellValue('B2', "BUKU PEMBANTU - ASET TETAP");
			$this->excel->getActiveSheet()->setCellValue('B3', "Per ".$sesi['start_date']." s/d ".$sesi['end_date']);
			$this->excel->getActiveSheet()->setCellValue('I5', "Saldo");
			$this->excel->getActiveSheet()->setCellValue('B7', "No");
			$this->excel->getActiveSheet()->setCellValue('C7', "Debit (Pengambilan)");
			$this->excel->getActiveSheet()->setCellValue('C8', "No. Jurnal");
			$this->excel->getActiveSheet()->setCellValue('D8', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('E8', "Keterangan");
			$this->excel->getActiveSheet()->setCellValue('F8', "Jumlah");
			$this->excel->getActiveSheet()->setCellValue('G7', "Kredit (Pengembalian)");
			$this->excel->getActiveSheet()->setCellValue('G8', "No. Jurnal");
			$this->excel->getActiveSheet()->setCellValue('H8', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('I8', "Keterangan");
			$this->excel->getActiveSheet()->setCellValue('J8', "Jumlah");
			$this->excel->getActiveSheet()->setCellValue('K7', "Umur Piutang (Hari)");
			
			$total_debit	= 0;
			$total_kredit	= 0;
			$row 			= 9;
			$no				= 1;

			foreach($data_debit as $key => $val){
				$this->excel->getActiveSheet()->setCellValue('B'.($row), $no);
				$this->excel->getActiveSheet()->setCellValue('C'.($row), $val['journal_voucher_no']);
				$this->excel->getActiveSheet()->setCellValue('D'.($row), date('d-m-Y', strtotime($val['journal_voucher_date'])));
				$this->excel->getActiveSheet()->setCellValue('E'.($row), $val['journal_voucher_title']);
				$this->excel->getActiveSheet()->setCellValue('F'.($row), $val['journal_voucher_amount']);

				$datapelunasan = $this->HelpBookFixed_model->getHelpBookFixedPelunasan($sesi, $val['journal_voucher_id']);
				if($datapelunasan){ 	
					$this->excel->getActiveSheet()->setCellValue('B'.($row), $no);
					$this->excel->getActiveSheet()->setCellValue('G'.($row), $datapelunasan['journal_voucher_no']);
					$this->excel->getActiveSheet()->setCellValue('H'.($row), date('d-m-Y', strtotime($datapelunasan['journal_voucher_date'])));
					$this->excel->getActiveSheet()->setCellValue('I'.($row), $datapelunasan['journal_voucher_title']);
					$this->excel->getActiveSheet()->setCellValue('J'.($row), $datapelunasan['journal_voucher_amount']);

					$total_kredit += $datapelunasan['journal_voucher_amount'];

					$key = array_search($datapelunasan['journal_voucher_id'], array_column($data_kredit, 'journal_voucher_id'));
					unset($data_kredit[$key]);
					$data_kredit = array_values($data_kredit);
				}else{
					$date1 		= new DateTime();
					$date2 		= new DateTime($val['journal_voucher_date']);
					$interval 	= $date1->diff($date2);
					$this->excel->getActiveSheet()->setCellValue('K'.($row), $interval->d);
				}

				$total_debit += $val['journal_voucher_amount'];
				$row++;
				$no++;
			}

			foreach($data_kredit as $key => $val){
				$this->excel->getActiveSheet()->setCellValue('B'.($row), $no);
				$this->excel->getActiveSheet()->setCellValue('G'.($row), $val['journal_voucher_no']);
				$this->excel->getActiveSheet()->setCellValue('H'.($row), date('d-m-Y', strtotime($val['journal_voucher_date'])));
				$this->excel->getActiveSheet()->setCellValue('I'.($row), $val['journal_voucher_title']);
				$this->excel->getActiveSheet()->setCellValue('J'.($row), $val['journal_voucher_amount']);

				$total_kredit += $val['journal_voucher_amount'];
				$row++;
				$no++;
			}

			$this->excel->getActiveSheet()->getStyle('B9:D'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('G9:H'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('K9:K'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('E9:E'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('I9:I'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('F9:F'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('J9:J'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B7:K'.($row-1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('F'.($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('J'.($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

			$this->excel->getActiveSheet()->getStyle('B'.($row).':K'.($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B'.($row).':K'.($row))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('F'.($row), $total_debit);
			$this->excel->getActiveSheet()->setCellValue('J'.($row), $total_kredit);
			$this->excel->getActiveSheet()->setCellValue('J5', 'Rp '.number_format(($total_debit-$total_kredit), 2));

			$filename='Buku Pembantu - Aset Tetap.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
							
			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
			ob_end_clean();
			$objWriter->save('php://output');
		}
	}
?>
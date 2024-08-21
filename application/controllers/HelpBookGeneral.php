<?php ob_start(); ?>
<?php
	ini_set('memory_limit', '512M');
	defined('BASEPATH') OR exit('No direct script access allowed');

	Class HelpBookGeneral extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('HelpBookGeneral_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$corebranch 	= create_double_branch($this->HelpBookGeneral_model->getCoreBranch(),'branch_id','branch_name');
			$acctaccount 	= create_double($this->HelpBookGeneral_model->getAcctAccount(),'account_id','account_code');
			
			$data['main_view']['acctaccount']	= $acctaccount;
			$data['main_view']['content']		= 'HelpBookGeneral/HelpBookGeneral_view';
			$this->load->view('MainPage_view',$data);
		}
 
		public function viewreport(){
			$account_id		= $this->input->post('account_id', true);
			$account_name	= $this->HelpBookGeneral_model->getAccountName($account_id);
			
			$sesi = array (
				"start_date"	=> $this->input->post('start_date', true),
				"end_date"		=> $this->input->post('end_date', true),
				"view"			=> $this->input->post('view',true),
				"account_id"	=> $account_id,
				"account_name"	=> $account_name,
			);

			$this->form_validation->set_rules('account_id', 'No Perkiraan', 'required');

			if($this->form_validation->run()==true){
				if($sesi['view'] == 'pdf_whole'){
					$this->print_whole($sesi);
				}else if($sesi['view'] == 'excel_whole'){
					$this->export_whole($sesi);
				}else if($sesi['view'] == 'pdf_hang'){
					$this->print_hang($sesi);
				}else if($sesi['view'] == 'excel_hang'){
					$this->export_hang($sesi);
				}
			}else{
				$msg = validation_errors("<div class='alert alert-danger'>", "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button></div>");
				$this->session->set_userdata('message',$msg);
				redirect('help-book-general');
			}
		}

		public function print_whole($sesi){
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->HelpBookGeneral_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$data_plus 		= $this->HelpBookGeneral_model->getHelpBookGeneralPlus($sesi);
			$data_minus 	= $this->HelpBookGeneral_model->getHelpBookGeneralMinus($sesi);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');

			$pdf = new tcpdf('L', 'mm', array(400, 200), true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

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
			        <td width=\"100%\"><div style=\"text-align: center; font-size:14px; font-weight:bold\">BUKU PEMBANTU ".strtoupper($sesi['account_name'])."</div></td>
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
				<tr>
					<td width=\"50%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\" colspan=\"5\">Pengambilan</td>
					<td width=\"50%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\" colspan=\"5\">Pengembalian</td>
				</tr>
				<tr>
					<td width=\"5%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">No.</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">No Jurnal</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">Tanggal</td>
					<td width=\"15%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">Keterangan</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">Jumlah</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\">No Jurnal</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\">Tanggal</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\">Keterangan</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\">Jumlah</td>
				</tr>
			</table>";

			$total_minus	= 0;
			$no				= 1;

			$tbl .= "<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">";

			foreach($data_minus as $key => $val){
				$tbl .= "
				<tr>
					<td width=\"5%\" style=\"font-size:13px; text-align: center;\">".$no."</td>
					<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".$val['journal_voucher_no']."</td>
					<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".date('d-m-Y', strtotime($val['journal_voucher_date']))."</td>
					<td width=\"15%\" style=\"font-size:13px; text-align: left;\">".$val['journal_voucher_title']."</td>
					<td width=\"10%\" style=\"font-size:13px; text-align: right;\">".number_format($val['journal_voucher_amount'], 2)."</td>";

				$data_plus_val = $this->HelpBookGeneral_model->getHelpBookGeneralPlusVal($sesi, $val['journal_voucher_id']);

				if($data_plus_val){
					$counter = 0;
					foreach($data_plus_val as $keyy => $vall){
						if($counter == 0){
							$tbl .= "
								<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".$vall['journal_voucher_no']."</td>
								<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".date('d-m-Y', strtotime($vall['journal_voucher_date']))."</td>
								<td width=\"20%\" style=\"font-size:13px; text-align: left;\">".$vall['journal_voucher_title']."</td>
								<td width=\"10%\" style=\"font-size:13px; text-align: right;\">".number_format($vall['journal_voucher_amount'], 2)."</td>
							</tr>";
						}else{
							$tbl .= "
							<tr>
								<td width=\"5%\"></td>
								<td width=\"10%\"></td>
								<td width=\"10%\"></td>
								<td width=\"15%\"></td>
								<td width=\"10%\"></td>
								<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".$vall['journal_voucher_no']."</td>
								<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".date('d-m-Y', strtotime($vall['journal_voucher_date']))."</td>
								<td width=\"20%\" style=\"font-size:13px; text-align: left;\">".$vall['journal_voucher_title']."</td>
								<td width=\"10%\" style=\"font-size:13px; text-align: right;\">".number_format($vall['journal_voucher_amount'], 2)."</td>
							</tr>";
						}
						$counter 	+= 1;
						$total_plus += $vall['journal_voucher_amount'];
						$key = array_search($data_plus_val['journal_voucher_id'], array_column($data_plus, 'journal_voucher_id'));
						unset($data_plus[$key]);
						$data_plus = array_values($data_plus);
					}
				}else{
					$tbl .= "
						<td width=\"10%\"></td>
						<td width=\"10%\"></td>
						<td width=\"20%\"></td>
						<td width=\"10%\"></td>
					</tr>";
				}
				$total_minus += $val['journal_voucher_amount'];
				$no++;
			}

			foreach($data_plus as $key => $val){
				$tbl .= "
				<tr style=\"background-color:#fc8783;\">
					<td width=\"5%\"></td>
					<td width=\"10%\"></td>
					<td width=\"10%\"></td>
					<td width=\"15%\"></td>
					<td width=\"10%\"></td>
					<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".$val['journal_voucher_no']."</td>
					<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".date('d-m-Y', strtotime($val['journal_voucher_date']))."</td>
					<td width=\"20%\" style=\"font-size:13px; text-align: left;\">".$val['journal_voucher_title']."</td>
					<td width=\"10%\" style=\"font-size:13px; text-align: right;\">".number_format($val['journal_voucher_amount'], 2)."</td>
				</tr>";
				$total_plus += $val['journal_voucher_amount'];
			}

			$tbl .= "
			</table>
			<br>
			<br>
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: left; font-size:13px; font-style:italic; color:gray;\">".$auth['username'].' '.date("Y-m-d H:i:s")."</div></td>
			        <td width=\"30%\"><div style=\"text-align: center; font-size:13px; font-weight:bold\"></div></td>
			        <td width=\"40%\"><div style=\"text-align: right; font-size:13px; font-weight:bold\"> Saldo Rp ".number_format(($total_plus - $total_minus), 2)."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, '');

			ob_clean();
			$filename = 'Buku Pembantu '.$sesi['account_name'].'.pdf';
			$pdf->Output($filename, 'I');
		}

		public function export_whole($sesi){	
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->HelpBookGeneral_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$data_plus 		= $this->HelpBookGeneral_model->getHelpBookGeneralPlus($sesi);
			$data_minus 	= $this->HelpBookGeneral_model->getHelpBookGeneralMinus($sesi);

			$this->load->library('Excel');
			$this->excel->getProperties()->setCreator("CST FISRT")
			->setLastModifiedBy("CST FISRT")
			->setTitle("Buku Pembantu ".$sesi['account_name'])
			->setSubject("")
			->setDescription("Buku Pembantu ".$sesi['account_name'])
			->setKeywords("Buku Pembantu ".$sesi['account_name'])
			->setCategory("Buku Pembantu ".$sesi['account_name']);
									
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

			$this->excel->getActiveSheet()->mergeCells("B2:J2");
			$this->excel->getActiveSheet()->mergeCells("B3:J3");
			$this->excel->getActiveSheet()->mergeCells("B7:B8");
			$this->excel->getActiveSheet()->mergeCells("C7:F7");
			$this->excel->getActiveSheet()->mergeCells("G7:J7");
			$this->excel->getActiveSheet()->getStyle('B2:J8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B2:J8')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('I5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B2:J8')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('C7:F8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('42f590');
			$this->excel->getActiveSheet()->getStyle('G7:J8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f5bc42');
			$this->excel->getActiveSheet()->getStyle('B7:B8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('cccccc');
			
			$this->excel->getActiveSheet()->setCellValue('B2', "BUKU PEMBANTU ".strtoupper($sesi['account_name']));
			$this->excel->getActiveSheet()->setCellValue('B3', "Per ".$sesi['start_date']." s/d ".$sesi['end_date']);
			$this->excel->getActiveSheet()->setCellValue('I5', "Saldo");
			$this->excel->getActiveSheet()->setCellValue('B7', "No");
			$this->excel->getActiveSheet()->setCellValue('C7', "Pengambilan");
			$this->excel->getActiveSheet()->setCellValue('C8', "No. Jurnal");
			$this->excel->getActiveSheet()->setCellValue('D8', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('E8', "Keterangan");
			$this->excel->getActiveSheet()->setCellValue('F8', "Jumlah");
			$this->excel->getActiveSheet()->setCellValue('G7', "Pengembalian");
			$this->excel->getActiveSheet()->setCellValue('G8', "No. Jurnal");
			$this->excel->getActiveSheet()->setCellValue('H8', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('I8', "Keterangan");
			$this->excel->getActiveSheet()->setCellValue('J8', "Jumlah");
			
			$total_minus	= 0;
			$total_plus		= 0;
			$row 			= 9;
			$no				= 1;
			
			foreach($data_minus as $key => $val){
				$this->excel->getActiveSheet()->setCellValue('B'.($row), $no);
				$this->excel->getActiveSheet()->setCellValue('C'.($row), $val['journal_voucher_no']);
				$this->excel->getActiveSheet()->setCellValue('D'.($row), $val['journal_voucher_date']);
				$this->excel->getActiveSheet()->setCellValue('E'.($row), $val['journal_voucher_title']);
				$this->excel->getActiveSheet()->setCellValue('F'.($row), $val['journal_voucher_amount']);

				$data_plus_val = $this->HelpBookGeneral_model->getHelpBookGeneralPlusVal($sesi, $val['journal_voucher_id']);

				if($data_plus_val){
					$counter = 0;
					foreach($data_plus_val as $keyy => $vall){
						if($counter == 0){
							$this->excel->getActiveSheet()->setCellValue('G'.($row), $vall['journal_voucher_no']);
							$this->excel->getActiveSheet()->setCellValue('H'.($row), $vall['journal_voucher_date']);
							$this->excel->getActiveSheet()->setCellValue('I'.($row), $vall['journal_voucher_title']);
							$this->excel->getActiveSheet()->setCellValue('J'.($row), $vall['journal_voucher_amount']);
						}else{
							$row++;

							$this->excel->getActiveSheet()->setCellValue('G'.($row), $vall['journal_voucher_no']);
							$this->excel->getActiveSheet()->setCellValue('H'.($row), $vall['journal_voucher_date']);
							$this->excel->getActiveSheet()->setCellValue('I'.($row), $vall['journal_voucher_title']);
							$this->excel->getActiveSheet()->setCellValue('J'.($row), $vall['journal_voucher_amount']);
						}
						$counter++;
						$total_plus += $vall['journal_voucher_amount'];
						$key = array_search($data_plus_val['journal_voucher_id'], array_column($data_plus, 'journal_voucher_id'));
						unset($data_plus[$key]);
						$data_plus = array_values($data_plus);
					}
				}
				$total_minus += $val['journal_voucher_amount'];
				$row++;
				$no++;
			}

			foreach($data_plus as $key => $val){
				$no++;
				$this->excel->getActiveSheet()->setCellValue('G'.($row), $val['journal_voucher_no']);
				$this->excel->getActiveSheet()->setCellValue('H'.($row), $val['journal_voucher_date']);
				$this->excel->getActiveSheet()->setCellValue('I'.($row), $val['journal_voucher_title']);
				$this->excel->getActiveSheet()->setCellValue('J'.($row), $val['journal_voucher_amount']);
				$total_plus += $val['journal_voucher_amount'];
				$row++;
			}

			$this->excel->getActiveSheet()->getStyle('B9:D'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('G9:H'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('E9:E'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('I9:I'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('F9:F'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('J9:J'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B7:J'.($row-1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('F'.($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('J'.($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B'.($row).':J'.($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B'.($row).':J'.($row))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('F'.($row), $total_minus);
			$this->excel->getActiveSheet()->setCellValue('J'.($row), $total_plus);
			$this->excel->getActiveSheet()->setCellValue('J5', 'Rp '.number_format(($total_plus-$total_minus), 2));

			$filename='Buku Pembantu '.$sesi['account_name'].'.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
							
			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
			ob_end_clean();
			$objWriter->save('php://output');
		}

		public function print_hang($sesi){
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->HelpBookGeneral_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$data_plus 		= $this->HelpBookGeneral_model->getHelpBookGeneralPlus($sesi);
			$data_minus 	= $this->HelpBookGeneral_model->getHelpBookGeneralMinus($sesi);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');

			$pdf = new tcpdf('L', 'mm', array(400, 200), true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

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
			        <td width=\"100%\"><div style=\"text-align: center; font-size:14px; font-weight:bold\">BUKU PEMBANTU ".strtoupper($sesi['account_name'])." MENGGANTUNG</div></td>
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
				<tr>
					<td width=\"50%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\" colspan=\"5\">Pengambilan</td>
					<td width=\"50%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\" colspan=\"5\">Pengembalian</td>
				</tr>
				<tr>
					<td width=\"5%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">No.</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">No Jurnal</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">Tanggal</td>
					<td width=\"15%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">Keterangan</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#6fbf73;\">Jumlah</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\">No Jurnal</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\">Tanggal</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\">Keterangan</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center; background-color:#ffb64c;\">Jumlah</td>
				</tr>
			</table>";

			$total_minus	= 0;
			$no				= 1;

			$tbl .= "<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">";

			foreach($data_minus as $key => $val){
				$data_plus_val = $this->HelpBookGeneral_model->getHelpBookGeneralPlusVal($sesi, $val['journal_voucher_id']);
				
				foreach($data_plus_val as $keyy => $vall){
					$subtotal_plus += $vall['journal_voucher_amount'];
				}

				if($subtotal_plus != $val['journal_voucher_amount']){
					$tbl .= "
					<tr>
						<td width=\"5%\" style=\"font-size:13px; text-align: center;\">".$no."</td>
						<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".$val['journal_voucher_no']."</td>
						<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".date('d-m-Y', strtotime($val['journal_voucher_date']))."</td>
						<td width=\"15%\" style=\"font-size:13px; text-align: left;\">".$val['journal_voucher_title']."</td>
						<td width=\"10%\" style=\"font-size:13px; text-align: right;\">".number_format($val['journal_voucher_amount'], 2)."</td>";

					if($data_plus_val){
						$counter 		= 0;
						$subtotal_plus 	= 0;
						foreach($data_plus_val as $keyy => $vall){
							if($counter == 0){
								$tbl .= "
									<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".$vall['journal_voucher_no']."</td>
									<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".date('d-m-Y', strtotime($vall['journal_voucher_date']))."</td>
									<td width=\"20%\" style=\"font-size:13px; text-align: left;\">".$vall['journal_voucher_title']."</td>
									<td width=\"10%\" style=\"font-size:13px; text-align: right;\">".number_format($vall['journal_voucher_amount'], 2)."</td>
								</tr>";
							}else{
								$tbl .= "
								<tr>
									<td width=\"5%\"></td>
									<td width=\"10%\"></td>
									<td width=\"10%\"></td>
									<td width=\"15%\"></td>
									<td width=\"10%\"></td>
									<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".$vall['journal_voucher_no']."</td>
									<td width=\"10%\" style=\"font-size:13px; text-align: center;\">".date('d-m-Y', strtotime($vall['journal_voucher_date']))."</td>
									<td width=\"20%\" style=\"font-size:13px; text-align: left;\">".$vall['journal_voucher_title']."</td>
									<td width=\"10%\" style=\"font-size:13px; text-align: right;\">".number_format($vall['journal_voucher_amount'], 2)."</td>
								</tr>";
							}
							$counter 	+= 1;
							$total_plus += $vall['journal_voucher_amount'];
						}
					}else{
						$tbl .= "
							<td width=\"10%\"></td>
							<td width=\"10%\"></td>
							<td width=\"20%\"></td>
							<td width=\"10%\"></td>
						</tr>";
					}
					$total_minus += $val['journal_voucher_amount'];
					$no++;
				}
			}

			$tbl .= "
			</table>
			<br>
			<br>
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: left; font-size:13px; font-style:italic; color:gray;\">".$auth['username'].' '.date("Y-m-d H:i:s")."</div></td>
			        <td width=\"30%\"><div style=\"text-align: center; font-size:13px; font-weight:bold\"></div></td>
			        <td width=\"40%\"><div style=\"text-align: right; font-size:13px; font-weight:bold\"> Saldo Rp ".number_format(($total_plus - $total_minus), 2)."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, '');

			ob_clean();
			$filename = 'Buku Pembantu '.$sesi['account_name'].' Menggantung.pdf';
			$pdf->Output($filename, 'I');
		}

		public function export_hang($sesi){	
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->HelpBookGeneral_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$data_plus 		= $this->HelpBookGeneral_model->getHelpBookGeneralPlus($sesi);
			$data_minus 	= $this->HelpBookGeneral_model->getHelpBookGeneralMinus($sesi);

			$this->load->library('Excel');
			$this->excel->getProperties()->setCreator("CST FISRT")
			->setLastModifiedBy("CST FISRT")
			->setTitle("Buku Pembantu ".$sesi['account_name']." Menggantung")
			->setSubject("")
			->setDescription("Buku Pembantu ".$sesi['account_name']." Menggantung")
			->setKeywords("Buku Pembantu ".$sesi['account_name']." Menggantung")
			->setCategory("Buku Pembantu ".$sesi['account_name']." Menggantung");
									
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

			$this->excel->getActiveSheet()->mergeCells("B2:J2");
			$this->excel->getActiveSheet()->mergeCells("B3:J3");
			$this->excel->getActiveSheet()->mergeCells("B7:B8");
			$this->excel->getActiveSheet()->mergeCells("C7:F7");
			$this->excel->getActiveSheet()->mergeCells("G7:J7");
			
			$this->excel->getActiveSheet()->getStyle('B2:J8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B2:J8')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('I5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B2:J8')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('C7:F8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('42f590');
			$this->excel->getActiveSheet()->getStyle('G7:J8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('f5bc42');
			$this->excel->getActiveSheet()->getStyle('B7:B8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('cccccc');
			
			$this->excel->getActiveSheet()->setCellValue('B2', "BUKU PEMBANTU ".strtoupper($sesi['account_name'])." MENGGANTUNG");
			$this->excel->getActiveSheet()->setCellValue('B3', "Per ".$sesi['start_date']." s/d ".$sesi['end_date']);
			$this->excel->getActiveSheet()->setCellValue('I5', "Saldo");
			$this->excel->getActiveSheet()->setCellValue('B7', "No");
			$this->excel->getActiveSheet()->setCellValue('C7', "Pengambilan");
			$this->excel->getActiveSheet()->setCellValue('C8', "No. Jurnal");
			$this->excel->getActiveSheet()->setCellValue('D8', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('E8', "Keterangan");
			$this->excel->getActiveSheet()->setCellValue('F8', "Jumlah");
			$this->excel->getActiveSheet()->setCellValue('G7', "Pengembalian");
			$this->excel->getActiveSheet()->setCellValue('G8', "No. Jurnal");
			$this->excel->getActiveSheet()->setCellValue('H8', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('I8', "Keterangan");
			$this->excel->getActiveSheet()->setCellValue('J8', "Jumlah");
			
			$total_minus	= 0;
			$total_plus		= 0;
			$row 			= 9;
			$no				= 1;
			
			foreach($data_minus as $key => $val){
				$data_plus_val = $this->HelpBookGeneral_model->getHelpBookGeneralPlusVal($sesi, $val['journal_voucher_id']);
				
				$subtotal_plus	= 0;
				foreach($data_plus_val as $keyy => $vall){
					$subtotal_plus += $vall['journal_voucher_amount'];
				}

				if($subtotal_plus != $val['journal_voucher_amount']){
					$this->excel->getActiveSheet()->setCellValue('B'.($row), $no);
					$this->excel->getActiveSheet()->setCellValue('C'.($row), $val['journal_voucher_no']);
					$this->excel->getActiveSheet()->setCellValue('D'.($row), $val['journal_voucher_date']);
					$this->excel->getActiveSheet()->setCellValue('E'.($row), $val['journal_voucher_title']);
					$this->excel->getActiveSheet()->setCellValue('F'.($row), $val['journal_voucher_amount']);

					if($data_plus_val){
						$counter = 0;
						foreach($data_plus_val as $keyy => $vall){
							if($counter == 0){
								$this->excel->getActiveSheet()->setCellValue('G'.($row), $vall['journal_voucher_no']);
								$this->excel->getActiveSheet()->setCellValue('H'.($row), $vall['journal_voucher_date']);
								$this->excel->getActiveSheet()->setCellValue('I'.($row), $vall['journal_voucher_title']);
								$this->excel->getActiveSheet()->setCellValue('J'.($row), $vall['journal_voucher_amount']);
							}else{
								$row++;

								$this->excel->getActiveSheet()->setCellValue('G'.($row), $vall['journal_voucher_no']);
								$this->excel->getActiveSheet()->setCellValue('H'.($row), $vall['journal_voucher_date']);
								$this->excel->getActiveSheet()->setCellValue('I'.($row), $vall['journal_voucher_title']);
								$this->excel->getActiveSheet()->setCellValue('J'.($row), $vall['journal_voucher_amount']);
							}
							$counter++;
							$total_plus += $vall['journal_voucher_amount'];
							$key = array_search($data_plus_val['journal_voucher_id'], array_column($data_plus, 'journal_voucher_id'));
							unset($data_plus[$key]);
							$data_plus = array_values($data_plus);
						}
					}
					$total_minus += $val['journal_voucher_amount'];
					$row++;
					$no++;
				}
			}

			$this->excel->getActiveSheet()->getStyle('B9:D'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('G9:H'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('E9:E'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('I9:I'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('F9:F'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('J9:J'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B7:J'.($row-1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('F'.($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('J'.($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B'.($row).':J'.($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B'.($row).':J'.($row))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('F'.($row), $total_minus);
			$this->excel->getActiveSheet()->setCellValue('J'.($row), $total_plus);
			$this->excel->getActiveSheet()->setCellValue('J5', 'Rp '.number_format(($total_plus-$total_minus), 2));

			$filename='Buku Pembantu '.$sesi['account_name'].' Menggantung.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
							
			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
			ob_end_clean();
			$objWriter->save('php://output');
		}
	}
?>
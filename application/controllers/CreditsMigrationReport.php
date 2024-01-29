<?php
defined('BASEPATH') or exit('No direct script access allowed');
class CreditsMigrationReport extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Connection_model');
		$this->load->model('MainPage_model');
		$this->load->model('CreditsMigrationReport_model');
		$this->load->helper('sistem');
		$this->load->helper('url');
		$this->load->database('default');
		$this->load->library('configuration');
		$this->load->library('Fungsi');
		$this->load->library(array('PHPExcel', 'PHPExcel/IOFactory'));
	}

	public function index()
	{
		$auth 				= $this->session->userdata('auth');
		$acctcreditsaccount	= $this->CreditsMigrationReport_model->getCreditsMigrationReport();

		if (count($acctcreditsaccount) != 0) {
			$this->load->library('Excel');

			$this->excel->getProperties()->setCreator("CST FISRT")
				->setLastModifiedBy("CST FISRT")
				->setTitle("Laporan Migrasi Pinjaman")
				->setSubject("")
				->setDescription("Laporan Migrasi Pinjaman")
				->setKeywords("Laporan, Migrasi, Pinjaman")
				->setCategory("Laporan Migrasi Pinjaman");

			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(30);
			$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(30);
			$this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('U')->setWidth(20);

			$this->excel->getActiveSheet()->mergeCells("B1:S1");
			$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$this->excel->getActiveSheet()->getStyle('B3:S3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B3:S3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B3:S3')->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B1', "DAFTAR PINJAMAN");
			$this->excel->getActiveSheet()->setCellValue('B3', "No. Pinjaman");
			$this->excel->getActiveSheet()->setCellValue('C3', "No. Anggota");
			$this->excel->getActiveSheet()->setCellValue('D3', "Nama");
			$this->excel->getActiveSheet()->setCellValue('E3', "Jenis Pinjaman");
			$this->excel->getActiveSheet()->setCellValue('F3', "Jangka Waktu");
			$this->excel->getActiveSheet()->setCellValue('G3', "Tanggal Pinjam");
			$this->excel->getActiveSheet()->setCellValue('H3', "Jatuh Tempo");
			$this->excel->getActiveSheet()->setCellValue('I3', "Plafon");
			$this->excel->getActiveSheet()->setCellValue('J3', "Pokok Perbulan");
			$this->excel->getActiveSheet()->setCellValue('K3', "Jasa Perbulan");
			$this->excel->getActiveSheet()->setCellValue('L3', "Total Perbulan");
			$this->excel->getActiveSheet()->setCellValue('M3', "Suku Bunga");
			$this->excel->getActiveSheet()->setCellValue('N3', "Saldo Pokok");
			$this->excel->getActiveSheet()->setCellValue('O3', "Total Angsuran");
			$this->excel->getActiveSheet()->setCellValue('P3', "Sisa Angsuran");
			$this->excel->getActiveSheet()->setCellValue('Q3', "Tanggal Terakhir Angsuran");
			$this->excel->getActiveSheet()->setCellValue('R3', "Tanggal Angsur Berikutnya");
			$this->excel->getActiveSheet()->setCellValue('S3', "Preferensi Angsuran");

			$j 	= 4;
			$no = 0;
			foreach ($acctcreditsaccount as $key => $val) {
				$no++;
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':S' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('C' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('E' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('F' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('G' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('H' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('I' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('K' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('L' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('M' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('N' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('O' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('P' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('Q' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('R' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('S' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

				if($val['payment_preference_id'] == 1){
					$metode = "Manual";
				}else if($val['payment_preference_id'] == 2){
					$metode = "Auto Debet";
				}else if($val['payment_preference_id'] == 3){
					$metode = "Potong Gaji";
				}

				$this->excel->getActiveSheet()->setCellValueExplicit('B' . $j, $val['credits_account_serial']);
				$this->excel->getActiveSheet()->setCellValueExplicit('C' . $j, $val['member_no']);
				$this->excel->getActiveSheet()->setCellValue('D' . $j, $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('E' . $j, $val['credits_name']);
				$this->excel->getActiveSheet()->setCellValue('F' . $j, $val['credits_account_period']);
				$this->excel->getActiveSheet()->setCellValue('G' . $j, $val['credits_account_date']);
				$this->excel->getActiveSheet()->setCellValue('H' . $j, $val['credits_account_due_date']);
				$this->excel->getActiveSheet()->setCellValue('I' . $j, $val['credits_account_amount']);
				$this->excel->getActiveSheet()->setCellValue('J' . $j, $val['credits_account_principal_amount']);
				$this->excel->getActiveSheet()->setCellValue('K' . $j, $val['credits_account_interest_amount']);
				$this->excel->getActiveSheet()->setCellValue('L' . $j, $val['credits_account_payment_amount']);
				$this->excel->getActiveSheet()->setCellValue('M' . $j, $val['credits_account_interest']);
				$this->excel->getActiveSheet()->setCellValue('N' . $j, $val['credits_account_last_balance']);
				$this->excel->getActiveSheet()->setCellValue('O' . $j, $val['credits_account_payment_to']);
				$this->excel->getActiveSheet()->setCellValue('P' . $j, $val['credits_account_period'] - $val['credits_account_payment_to']);
				$this->excel->getActiveSheet()->setCellValue('Q' . $j, $val['credits_account_last_payment_date']);
				$this->excel->getActiveSheet()->setCellValue('R' . $j, $val['credits_account_payment_date']);
				$this->excel->getActiveSheet()->setCellValue('S' . $j, $metode);
				$j++;
			}

			$filename = 'Laporan Migrasi Pinjaman.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Cache-Control: max-age=0');

			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');
			ob_end_clean();
			$objWriter->save('php://output');
		} else {
			echo "Maaf data yang di eksport tidak ada !";
		}
	}

	public function processPrinting()
	{
		$auth 				= $this->session->userdata('auth');
		$preferencecompany	= $this->CreditsMigrationReport_model->getPreferenceCompany();
		$acctcreditsaccount	= $this->CreditsMigrationReport_model->getCreditsMigrationReport();

		require_once('tcpdf/config/tcpdf_config.php');
		require_once('tcpdf/tcpdf.php');

		$pdf =  new tcpdf('L', 'mm', array(610, 630), true, 'UTF-8', false);


		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetMargins(7, 7, 7, 7);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once(dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		// ---------------------------------------------------------

		$pdf->SetFont('helvetica', 'B', 25);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);

		// -----------------------------------------------------------------------------
		$base_url = base_url();
		$img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preferencecompany['logo_koperasi'] . "\" alt=\"\" width=\"950%\" height=\"950%\"/>";

		$tbl = "
		<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
			<tr>
				<td rowspan=\"2\" width=\"10%\">" . $img . "</td>
			</tr>
			<tr>
			</tr>
		</table>
		<br/>
		<br/>
		<br/>
		<br/>";

		$tbl .= "
		<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			<tr>
				<td><div style=\"text-align: center; font-size:14px\">DAFTAR PINJAMAN</div></td>
			</tr>
		</table>";

		$tbl .= "
			<br>
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
				<tr>
					<td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No. Pinjaman</div></td>
					<td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Anggota</div></td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Jenis Pinjaman</div></td>
					<td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Jangka Waktu</div></td>
					<td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Tgl Pinjam</div></td>
					<td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Jatuh Tempo</div></td>
					<td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Plafon</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Pokok Perbulan</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Jasa Perbulan</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Total Perbulan</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Suku Bunga</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Saldo Pokok</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Total Ags</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sisa Ags</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Tgl Trkhr Ags</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Tgl Ags Brktny</div></td>
					<td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Preferensi Ags</div></td>
				</tr>";

		$no = 1;

		if (!empty($acctcreditsaccount)) {
			foreach ($acctcreditsaccount as $key => $val) {
				if($val['payment_preference_id'] == 1){
					$metode = "Manual";
				}else if($val['payment_preference_id'] == 2){
					$metode = "Auto Debet";
				}else if($val['payment_preference_id'] == 3){
					$metode = "Potong Gaji";
				}
				$tbl .= "
					<tr>
						<td width=\"5%\"><div style=\"text-align: left;font-size:10;\">".$val['credits_account_serial']."</div></td>
						<td width=\"5%\"><div style=\"text-align: center;font-size:10;\">".$val['member_no']."</div></td>
						<td width=\"10%\"><div style=\"text-align: left;font-size:10;\">".$val['member_name']."</div></td>
						<td width=\"10%\"><div style=\"text-align: left;font-size:10;\">".$val['credits_name']."</div></td>
						<td width=\"5%\"><div style=\"text-align: right;font-size:10;\">".$val['credits_account_period']."</div></td>
						<td width=\"5%\"><div style=\"text-align: center;font-size:10;\">".$val['credits_account_date']."</div></td>
						<td width=\"5%\"><div style=\"text-align: center;font-size:10;\">".$val['credits_account_due_date']."</div></td>
						<td width=\"5%\"><div style=\"text-align: right;font-size:10;\">".number_format($val['credits_account_amount'])."</div></td>
						<td width=\"5%\"><div style=\"text-align: right;font-size:10;\">".number_format($val['credits_account_principal_amount'])."</div></td>
						<td width=\"5%\"><div style=\"text-align: right;font-size:10;\">".number_format($val['credits_account_interest_amount'])."</div></td>
						<td width=\"5%\"><div style=\"text-align: right;font-size:10;\">".number_format($val['credits_account_payment_amount'])."</div></td>
						<td width=\"5%\"><div style=\"text-align: right;font-size:10;\">".number_format($val['credits_account_interest'], 2)."</div></td>
						<td width=\"5%\"><div style=\"text-align: right;font-size:10;\">".number_format($val['credits_account_last_balance'])."</div></td>
						<td width=\"5%\"><div style=\"text-align: right;font-size:10;\">".$val['credits_account_payment_to']."</div></td>
						<td width=\"5%\"><div style=\"text-align: right;font-size:10;\">".(	$val['credits_account_period']-$val['credits_account_payment_to'])."</div></td>
						<td width=\"5%\"><div style=\"text-align: center;font-size:10;\">".$val['credits_account_last_payment_date']."</div></td>
						<td width=\"5%\"><div style=\"text-align: center;font-size:10;\">".$val['credits_account_payment_date']."</div></td>
						<td width=\"5%\"><div style=\"text-align: left;font-size:10;\">".$metode."</div></td>
					</tr>
				";
				$no++;
			}
		} else {
			$tbl .= "
			<tr>
				<td colspan =\"18\"><div style=\"font-size:10;text-align:left;font-style:italic\">Data Kosong</div></td>
			</tr>
			";
		}

		$tbl .= "
			<br>
			<tr>
				<td colspan =\"18\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : " . date('d-m-Y H:i:s') . "  " . $this->CreditsMigrationReport_model->getUserName($auth['user_id']) . "</div></td>
			</tr>
		</table>";

		$pdf->writeHTML($tbl, true, false, false, false, '');

		ob_clean();

		// -----------------------------------------------------------------------------

		$filename = 'Laporan_Nominatif_Pembiayaan.pdf';
		$pdf->Output($filename, 'I');
	}
}
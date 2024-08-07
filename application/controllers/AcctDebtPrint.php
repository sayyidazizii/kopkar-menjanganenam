<?php
defined('BASEPATH') or exit('No direct script access allowed');
class AcctDebtPrint extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Connection_model');
		$this->load->model('MainPage_model');
		$this->load->model('AcctDebtPrint_model');
		$this->load->model('AcctDebt_model');
		$this->load->model('CoreMember_model');
		$this->load->model('AcctSavingsSalaryMutation_model');
		$this->load->model('AcctSalaryPayment_model');
		$this->load->model('AcctCreditAccount_model');
		$this->load->helper('sistem');
		$this->load->helper('url');
		$this->load->database('default');
		$this->load->library('configuration');
		$this->load->library('fungsi');
		$this->load->library(array('PHPExcel', 'PHPExcel/IOFactory'));
	}

	public function index()
	{
		$data['main_view']['acctdebtcategory'] = create_double($this->AcctDebtPrint_model->getAcctDebtCategory(), 'debt_category_id', 'debt_category_name');
		$data['main_view']['corepart'] = create_double($this->AcctDebtPrint_model->getCorePart(), 'part_id', 'part_name');
		$data['main_view']['coredivision'] = create_double($this->AcctDebtPrint_model->getCoreDivision(), 'division_id', 'division_name');

		$data['main_view']['acctmutation']		= create_double($this->CoreMember_model->getAcctMutationSalary(),'mutation_id', 'mutation_name');
		$data['main_view']['acctaccount']		= create_double($this->CoreMember_model->getAcctAccount(),'account_id','account_code');
		
		//temp
		$data['main_view']['principal_savings'] = $this->CoreMember_model->getMemberPrincipalSavingsTemp();
		$data['main_view']['mandatory_savings'] = $this->CoreMember_model->getMemberMandatorySavingsTemp();
		$data['main_view']['savings_salary_mutation'] = $this->AcctSavingsSalaryMutation_model->getSavingsSalaryMutationTemp();
		$data['main_view']['salary_payments'] = $this->AcctSalaryPayment_model->getAcctCreditsPaymentsTemp();
		$data['main_view']['acct_debt'] = $this->AcctDebt_model->getAcctDebtTemporary();

		// echo json_encode($data['main_view']['salary_payment']);
		// exit;
		$data['main_view']['content'] = 'AcctDebtPrint/ListAcctDebtPrint_view';
		$this->load->view('MainPage_view', $data);
	}

	public function viewreport()
	{

		$sesi = array(
			"start_date" => tgltodb($this->input->post('start_date', true)),
			"end_date" => tgltodb($this->input->post('end_date', true)),
			"debt_category_id" => $this->input->post('debt_category_id', true),
			"part_id" => $this->input->post('part_id', true),
			"division_id" => $this->input->post('division_id', true),
			"view" => $this->input->post('view', true),
		);

		if ($sesi['view'] == 'pdf_category') {
			$this->printDebtCategory($sesi);
		} else if ($sesi['view'] == 'pdf_member') {
			$this->printDebtMember($sesi);
		} else if ($sesi['view'] == 'pdf_savings') {
			$this->printDebtSavings($sesi);
		} else if ($sesi['view'] == 'pdf_credits') {
			$this->printDebtCredits($sesi);
		} else if ($sesi['view'] == 'pdf_store') {
			$this->printDebtStore($sesi);
		} else if ($sesi['view'] == 'excel_category') {
			$this->exportDebtCategory($sesi);
		} else if ($sesi['view'] == 'excel_member') {
			$this->exportDebtMember($sesi);
		} else if ($sesi['view'] == 'excel_savings') {
			$this->exportDebtSavings($sesi);
		} else if ($sesi['view'] == 'excel_credits') {
			$this->exportDebtCredits($sesi);
		} else if ($sesi['view'] == 'excel_store') {
			$this->exportDebtStore($sesi);
		} else if ($sesi['view'] == 'pdf_recap') {
			$this->printDebtRecap($sesi);
		} else if ($sesi['view'] == 'excel_recap') {
			$this->exportDebtRecap($sesi);
		} else if ($sesi['view'] == 'pdf_simple') {
			$this->printDebtSimple($sesi);
		} else if ($sesi['view'] == 'excel_simple') {
			$this->exportDebtSimple($sesi);
		} else if ($sesi['view'] == 'pdf_simple_temp') {
			$this->printDebtSimpleTemp($sesi);
		} else if ($sesi['view'] == 'excel_simple_temp') {
			$this->exportDebtSimpleTemp($sesi);
		}
		else if ($sesi['view'] == 'submit_principal') {
			$this->submitSalaryPrincipalSavings();
		}
		else if ($sesi['view'] == 'submit_mandatory') {
			$this->submitSalaryMandatorySavings();
		}
		else if ($sesi['view'] == 'submit_salary_savings') {
			$this->submitAcctSavingsSalaryMutation();
		}
		else if ($sesi['view'] == 'submit_salary_payments') {
			$this->submitAcctSalaryPayments();
		}
		else if ($sesi['view'] == 'submit_acct_debt') {
			$this->submitAcctDebt();
		}
	}

	public function printDebtCategory($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebt = $this->AcctDebtPrint_model->getAcctDebt($sesi);
		$acctdebtcategory = $this->AcctDebtPrint_model->getAcctDebtCategory();

		if ($sesi['debt_category_id'] != '') {
			$debt_category_name = $this->AcctDebtPrint_model->getAcctDebtCategoryName($sesi['debt_category_id']);
			$kategori = " Kategori " . $debt_category_name;
		} else {
			$kategori = "";
		}

		require_once ('tcpdf/config/tcpdf_config.php');
		require_once ('tcpdf/tcpdf.php');

		$pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetMargins(7, 7, 7, 7);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);

		$base_url = base_url();
		$img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preferencecompany['logo_koperasi'] . "\" alt=\"\" width=\"800%\" height=\"800%\"/>";

		$tbl0 = "
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
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR POTONG GAJI PER " . $sesi['start_date'] . " s.d " . $sesi['end_date'] . $kategori . "</div></td>
			    </tr>
			</table>
			<br>";

		$tbl1 = "
			<br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No</div></td>
					<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No PG</div></td>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Agt</div></td>
			        <td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
					<td width=\"12%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Divisi</div></td>
					<td width=\"13%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Bagian</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Tanggal</div></td>
			        <td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Kategori</div></td>
			        <td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Jumlah</div></td>
			    </tr>";

		if (count($acctdebt) > 0) {
			$no = 1;
			$total = 0;
			foreach ($acctdebt as $key => $val) {
				$tbl1 .= "
						<tr>
							<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $no . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['debt_no'] . "</div></td>
							<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['member_no'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['member_name'] . "</div></td>
							<td width=\"12%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['division_name'] . "</div></td>
							<td width=\"13%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['part_name'] . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $val['debt_date'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['debt_category_name'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">" . number_format($val['debt_amount'], 2) . "</div></td>
						</tr>";
				$no++;
				$total += $val['debt_amount'];
				$subtotal[$val['debt_category_id']] += $val['debt_amount'];
			}
			foreach ($acctdebtcategory as $key => $val) {
				$tbl1 .= "
					<tr>
						<td width=\"85%\" colspan =\"7\" style=\"border: 1px solid black;\"><div style=\"text-align: left; font-size:10; font-weight:bold; \">Subtotal " . $val['debt_category_name'] . "</div></td>
						<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right; font-size:10; font-weight:bold; \">" . number_format($subtotal[$val['debt_category_id']], 2) . "</div></td>
					</tr>
					";
			}
			$tbl1 .= "
				<tr>
					<td width=\"85%\" colspan =\"7\" style=\"border: 1px solid black;\"><div style=\"text-align: left; font-size:10; font-weight:bold; \">Total</div></td>
					<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right; font-size:10; font-weight:bold; \">" . number_format($total, 2) . "</div></td>
				</tr>
				";
		} else {
			$tbl1 .= "
					<tr>
						<td width=\"100%\" colspan =\"8\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Data Kosong</div></td>
					</tr>
				";
		}
		$tbl1 .= "</table>";

		$pdf->writeHTML($tbl0 . $tbl1, true, false, false, '');

		ob_clean();
		$filename = 'Laporan Potong Gaji Per Kategori.pdf';
		$pdf->Output($filename, 'I');
	}

	public function exportDebtCategory($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebt = $this->AcctDebtPrint_model->getAcctDebt($sesi);
		$acctdebtcategory = $this->AcctDebtPrint_model->getAcctDebtCategory();

		if ($sesi['debt_category_id'] != '') {
			$debt_category_name = $this->AcctDebtPrint_model->getAcctDebtCategoryName($sesi['debt_category_id']);
			$kategori = " Kategori " . $debt_category_name;
		} else {
			$kategori = "";
		}

		if (count($acctdebt) != 0) {
			$this->load->library('Excel');

			$this->excel->getProperties()->setCreator("CST FISRT")
				->setLastModifiedBy("CST FISRT")
				->setTitle("Laporan Potong Gaji Per Kategori")
				->setSubject("")
				->setDescription("Laporan Potong Gaji Per Kategori")
				->setKeywords("Laporan Potong Gaji Per Kategori")
				->setCategory("Laporan Potong Gaji Per Kategori");

			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

			$this->excel->getActiveSheet()->mergeCells("B1:J1");
			$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$this->excel->getActiveSheet()->getStyle('B3:J3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B3:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B3:J3')->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Potong Gaji Per " . $sesi['start_date'] . " s.d. " . $sesi['end_date'] . $kategori);
			$this->excel->getActiveSheet()->setCellValue('B3', "No");
			$this->excel->getActiveSheet()->setCellValue('C3', "No PG");
			$this->excel->getActiveSheet()->setCellValue('D3', "No Anggota");
			$this->excel->getActiveSheet()->setCellValue('E3', "Nama");
			$this->excel->getActiveSheet()->setCellValue('F3', "Divisi");
			$this->excel->getActiveSheet()->setCellValue('G3', "Bagian");
			$this->excel->getActiveSheet()->setCellValue('H3', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('I3', "Kategori");
			$this->excel->getActiveSheet()->setCellValue('J3', "Jumlah");

			$no = 1;
			$j = 4;
			$total = 0;
			foreach ($acctdebt as $key => $val) {
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':J' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('H' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
				$this->excel->getActiveSheet()->setCellValue('C' . $j, $val['debt_no']);
				$this->excel->getActiveSheet()->setCellValue('D' . $j, $val['member_no']);
				$this->excel->getActiveSheet()->setCellValue('E' . $j, $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('F' . $j, $val['division_name']);
				$this->excel->getActiveSheet()->setCellValue('G' . $j, $val['part_name']);
				$this->excel->getActiveSheet()->setCellValue('H' . $j, $val['debt_date']);
				$this->excel->getActiveSheet()->setCellValue('I' . $j, $val['debt_category_name']);
				$this->excel->getActiveSheet()->setCellValue('J' . $j, number_format($val['debt_amount'], 2));

				$subtotal[$val['debt_category_id']] += $val['debt_amount'];
				$total += $val['debt_amount'];
				$j++;
				$no++;
			}

			foreach ($acctdebtcategory as $key => $val) {
				$this->excel->getActiveSheet()->mergeCells('B' . ($j) . ':I' . ($j));
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':J' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B' . ($j) . ':J' . ($j))->getFont()->setBold(true);

				$this->excel->getActiveSheet()->setCellValue('B' . $j, "Subtotal " . $val['debt_category_name']);
				$this->excel->getActiveSheet()->setCellValue('J' . $j, number_format($subtotal[$val['debt_category_id']], 2));

				$j++;
			}

			$this->excel->getActiveSheet()->mergeCells('B' . ($j) . ':I' . ($j));
			$this->excel->getActiveSheet()->getStyle('B' . $j . ':J' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B' . ($j) . ':J' . ($j))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B' . $j, "Total");
			$this->excel->getActiveSheet()->setCellValue('J' . $j, number_format($total, 2));

			$filename = 'Laporan Potong Gaji Per Kategori.xls';
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

	public function printDebtMember($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebtmember = $this->AcctDebtPrint_model->getAcctDebtMember($sesi);

		require_once ('tcpdf/config/tcpdf_config.php');
		require_once ('tcpdf/tcpdf.php');

		$pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetMargins(7, 7, 7, 7);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);

		$base_url = base_url();
		$img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preferencecompany['logo_koperasi'] . "\" alt=\"\" width=\"800%\" height=\"800%\"/>";

		$tbl0 = "
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
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR POTONG GAJI SIMPANAN ANGGOTA PER " . $sesi['start_date'] . " s.d " . $sesi['end_date'] . "</div></td>
			    </tr>
			</table>
			<br>";

		$tbl1 = "
			<br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No</div></td>
					<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Agt</div></td>
			        <td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
					<td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Divisi</div></td>
					<td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Bagian</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Tanggal</div></td>
			        <td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Simpanan Pokok</div></td>
			        <td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Simpanan Wajib</div></td>
			    </tr>";

		if (count($acctdebtmember) > 0) {
			$no = 1;
			$totalpokok = 0;
			$totalwajib = 0;
			foreach ($acctdebtmember as $key => $val) {
				$tbl1 .= "
						<tr>
							<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $no . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['member_no'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['member_name'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['division_name'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['part_name'] . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $val['transaction_date'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">" . number_format($val['principal_savings_amount'], 2) . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">" . number_format($val['mandatory_savings_amount'], 2) . "</div></td>
						</tr>";
				$no++;
				$totalpokok += $val['principal_savings_amount'];
				$totalwajib += $val['mandatory_savings_amount'];
			}
			$tbl1 .= "
					<tr>
						<td width=\"70%\" colspan =\"6\" style=\"border: 1px solid black;\"><div style=\"text-align: left; font-size:10; font-weight:bold;\">Total</div></td>
						<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right; font-size:10; font-weight:bold;\">" . number_format($totalpokok, 2) . "</div></td>
						<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right; font-size:10; font-weight:bold;\">" . number_format($totalwajib, 2) . "</div></td>
					</tr>
				";
		} else {
			$tbl1 .= "
					<tr>
						<td width=\"100%\" colspan =\"8\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Data Kosong</div></td>
					</tr>
				";
		}
		$tbl1 .= "</table>";

		$pdf->writeHTML($tbl0 . $tbl1, true, false, false, '');

		ob_clean();
		$filename = 'Laporan Potong Gaji Pinjaman.pdf';
		$pdf->Output($filename, 'I');
	}

	public function exportDebtMember($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebtmember = $this->AcctDebtPrint_model->getAcctDebtMember($sesi);

		if (count($acctdebtmember) != 0) {
			$this->load->library('Excel');

			$this->excel->getProperties()->setCreator("CST FISRT")
				->setLastModifiedBy("CST FISRT")
				->setTitle("Laporan Potong Gaji Simpanan Anggota")
				->setSubject("")
				->setDescription("Laporan Potong Gaji Simpanan Anggota")
				->setKeywords("Laporan Potong Gaji Simpanan Anggota")
				->setCategory("Laporan Potong Gaji Simpanan Anggota");

			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);

			$this->excel->getActiveSheet()->mergeCells("B1:I1");
			$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$this->excel->getActiveSheet()->getStyle('B3:I3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B3:I3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B3:I3')->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Potong Gaji Simpanan Anggota Per " . $sesi['start_date'] . " s.d. " . $sesi['end_date']);
			$this->excel->getActiveSheet()->setCellValue('B3', "No");
			$this->excel->getActiveSheet()->setCellValue('C3', "No Anggota");
			$this->excel->getActiveSheet()->setCellValue('D3', "Nama");
			$this->excel->getActiveSheet()->setCellValue('E3', "Divisi");
			$this->excel->getActiveSheet()->setCellValue('F3', "Bagian");
			$this->excel->getActiveSheet()->setCellValue('G3', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('H3', "Simpanan Pokok");
			$this->excel->getActiveSheet()->setCellValue('I3', "Simpanan Wajib");

			$no = 1;
			$j = 4;
			$totalpokok = 0;
			$totalwajib = 0;
			foreach ($acctdebtmember as $key => $val) {
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':I' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('H' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('I' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
				$this->excel->getActiveSheet()->setCellValueExplicit('C' . $j, $val['member_no']);
				$this->excel->getActiveSheet()->setCellValue('D' . $j, $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('E' . $j, $val['division_name']);
				$this->excel->getActiveSheet()->setCellValue('F' . $j, $val['part_name']);
				$this->excel->getActiveSheet()->setCellValue('G' . $j, $val['transaction_date']);
				$this->excel->getActiveSheet()->setCellValue('H' . $j, number_format($val['principal_savings_amount'], 2));
				$this->excel->getActiveSheet()->setCellValue('I' . $j, number_format($val['mandatory_savings_amount'], 2));

				$totalpokok += $val['principal_savings_amount'];
				$totalwajib += $val['mandatory_savings_amount'];
				$j++;
				$no++;
			}

			$this->excel->getActiveSheet()->mergeCells('B' . ($j) . ':G' . ($j));
			$this->excel->getActiveSheet()->getStyle('B' . $j . ':I' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('H' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('I' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B' . ($j) . ':G' . ($j))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B' . $j, "Total");
			$this->excel->getActiveSheet()->setCellValue('H' . $j, number_format($totalpokok, 2));
			$this->excel->getActiveSheet()->setCellValue('I' . $j, number_format($totalwajib, 2));

			$filename = 'Laporan Potong Gaji Simpanan Anggota.xls';
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

	public function printDebtSavings($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebtsavings = $this->AcctDebtPrint_model->getAcctDebtSavings($sesi);
		$acctsavings = $this->AcctDebtPrint_model->getAcctSavings();

		require_once ('tcpdf/config/tcpdf_config.php');
		require_once ('tcpdf/tcpdf.php');

		$pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetMargins(7, 7, 7, 7);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);

		$base_url = base_url();
		$img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preferencecompany['logo_koperasi'] . "\" alt=\"\" width=\"800%\" height=\"800%\"/>";

		$tbl0 = "
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
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR POTONG GAJI TABUNGAN PER " . $sesi['start_date'] . " s.d " . $sesi['end_date'] . "</div></td>
			    </tr>
			</table>
			<br>";

		$tbl1 = "
			<br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No</div></td>
					<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Tabungan</div></td>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Agt</div></td>
			        <td width=\"20%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
					<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Divisi</div></td>
					<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Bagian</div></td>
					<td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Jenis</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Tanggal</div></td>
			        <td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Jumlah</div></td>
			    </tr>";

		if (count($acctdebtsavings) > 0) {
			$no = 1;
			$total = 0;
			foreach ($acctdebtsavings as $key => $val) {
				$tbl1 .= "
						<tr>
							<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $no . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['savings_account_no'] . "</div></td>
							<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['member_no'] . "</div></td>
							<td width=\"20%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['member_name'] . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['division_name'] . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['part_name'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['savings_name'] . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $val['savings_cash_mutation_date'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">" . number_format($val['savings_cash_mutation_amount'], 2) . "</div></td>
						</tr>";
				$no++;
				$total += $val['savings_cash_mutation_amount'];
				$subtotal[$val['savings_id']] += $val['savings_cash_mutation_amount'];
			}
			foreach ($acctsavings as $key => $val) {
				$tbl1 .= "
					<tr>
						<td width=\"85%\" colspan =\"8\" style=\"border: 1px solid black;\"><div style=\"text-align: left; font-size:10; font-weight:bold; \">Subtotal " . $val['savings_name'] . "</div></td>
						<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right; font-size:10; font-weight:bold; \">" . number_format($subtotal[$val['savings_id']], 2) . "</div></td>
					</tr>
					";
			}
			$tbl1 .= "
				<tr>
					<td width=\"85%\" colspan =\"8\" style=\"border: 1px solid black;\"><div style=\"text-align: left; font-size:10; font-weight:bold; \">Total</div></td>
					<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right; font-size:10; font-weight:bold; \">" . number_format($total, 2) . "</div></td>
				</tr>
				";
		} else {
			$tbl1 .= "
					<tr>
						<td width=\"100%\" colspan =\"9\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Data Kosong</div></td>
					</tr>
				";
		}
		$tbl1 .= "</table>";

		$pdf->writeHTML($tbl0 . $tbl1, true, false, false, '');

		ob_clean();
		$filename = 'Laporan Potong Gaji Tabungan.pdf';
		$pdf->Output($filename, 'I');
	}

	public function exportDebtSavings($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebtsavings = $this->AcctDebtPrint_model->getAcctDebtSavings($sesi);
		$acctsavings = $this->AcctDebtPrint_model->getAcctSavings();

		if (count($acctdebtsavings) != 0) {
			$this->load->library('Excel');

			$this->excel->getProperties()->setCreator("CST FISRT")
				->setLastModifiedBy("CST FISRT")
				->setTitle("Laporan Potong Gaji Tabungan")
				->setSubject("")
				->setDescription("Laporan Potong Gaji Tabungan")
				->setKeywords("Laporan Potong Gaji Tabungan")
				->setCategory("Laporan Potong Gaji Tabungan");

			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);

			$this->excel->getActiveSheet()->mergeCells("B1:J1");
			$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$this->excel->getActiveSheet()->getStyle('B3:J3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B3:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B3:J3')->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Potong Gaji Per " . $sesi['start_date'] . " s.d. " . $sesi['end_date']);
			$this->excel->getActiveSheet()->setCellValue('B3', "No");
			$this->excel->getActiveSheet()->setCellValue('C3', "No Tabungan");
			$this->excel->getActiveSheet()->setCellValue('D3', "No Anggota");
			$this->excel->getActiveSheet()->setCellValue('E3', "Nama");
			$this->excel->getActiveSheet()->setCellValue('F3', "Divisi");
			$this->excel->getActiveSheet()->setCellValue('G3', "Bagian");
			$this->excel->getActiveSheet()->setCellValue('H3', "Jenis");
			$this->excel->getActiveSheet()->setCellValue('I3', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('J3', "Jumlah");

			$no = 1;
			$j = 4;
			$total = 0;
			foreach ($acctdebtsavings as $key => $val) {
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':J' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('C' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('H' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
				$this->excel->getActiveSheet()->setCellValue('C' . $j, $val['savings_account_no']);
				$this->excel->getActiveSheet()->setCellValueExplicit('D' . $j, $val['member_no']);
				$this->excel->getActiveSheet()->setCellValue('E' . $j, $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('F' . $j, $val['division_name']);
				$this->excel->getActiveSheet()->setCellValue('G' . $j, $val['part_name']);
				$this->excel->getActiveSheet()->setCellValue('H' . $j, $val['savings_name']);
				$this->excel->getActiveSheet()->setCellValue('I' . $j, $val['savings_cash_mutation_date']);
				$this->excel->getActiveSheet()->setCellValue('J' . $j, number_format($val['savings_cash_mutation_amount'], 2));

				$subtotal[$val['savings_id']] += $val['savings_cash_mutation_amount'];
				$total += $val['savings_cash_mutation_amount'];
				$j++;
				$no++;
			}

			foreach ($acctsavings as $key => $val) {
				$this->excel->getActiveSheet()->mergeCells('B' . ($j) . ':I' . ($j));
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':J' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B' . ($j) . ':J' . ($j))->getFont()->setBold(true);

				$this->excel->getActiveSheet()->setCellValue('B' . $j, "Subtotal " . $val['savings_name']);
				$this->excel->getActiveSheet()->setCellValue('J' . $j, number_format($subtotal[$val['savings_id']], 2));

				$j++;
			}

			$this->excel->getActiveSheet()->mergeCells('B' . ($j) . ':I' . ($j));
			$this->excel->getActiveSheet()->getStyle('B' . $j . ':J' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B' . ($j) . ':J' . ($j))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B' . $j, "Total");
			$this->excel->getActiveSheet()->setCellValue('J' . $j, number_format($total, 2));

			$filename = 'Laporan Potong Gaji Tabungan.xls';
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

	public function printDebtCredits($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebtcredits = $this->AcctDebtPrint_model->getAcctDebtCredits($sesi);
		$acctcredits = $this->AcctDebtPrint_model->getAcctCredits();

		require_once ('tcpdf/config/tcpdf_config.php');
		require_once ('tcpdf/tcpdf.php');

		$pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetMargins(7, 7, 7, 7);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);

		$base_url = base_url();
		$img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preferencecompany['logo_koperasi'] . "\" alt=\"\" width=\"800%\" height=\"800%\"/>";

		$tbl0 = "
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
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR POTONG GAJI PINJAMAN PER " . $sesi['start_date'] . " s.d " . $sesi['end_date'] . "</div></td>
			    </tr>
			</table>
			<br>";

		$tbl1 = "
			<br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No</div></td>
					<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Pinjaman</div></td>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Agt</div></td>
			        <td width=\"20%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
					<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Divisi</div></td>
					<td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Bagian</div></td>
					<td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Jenis</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Tanggal</div></td>
			        <td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Jumlah</div></td>
			    </tr>";

		if (count($acctdebtcredits) > 0) {
			$no = 1;
			$total = 0;
			foreach ($acctdebtcredits as $key => $val) {
				$tbl1 .= "
						<tr>
							<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $no . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['credits_account_serial'] . "</div></td>
							<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['member_no'] . "</div></td>
							<td width=\"20%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['member_name'] . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['division_name'] . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['part_name'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['credits_name'] . "</div></td>
							<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $val['credits_payment_date'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">" . number_format($val['credits_payment_amount'], 2) . "</div></td>
						</tr>";
				$no++;
				$total += $val['credits_payment_amount'];
				$subtotal[$val['credits_id']] += $val['credits_payment_amount'];
			}
			foreach ($acctcredits as $key => $val) {
				$tbl1 .= "
						<tr>
							<td width=\"85%\" colspan =\"8\" style=\"border: 1px solid black;\"><div style=\"text-align: left; font-size:10; font-weight:bold;\">Subtotal " . $val['credits_name'] . "</div></td>
							<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right; font-size:10; font-weight:bold;\">" . number_format($subtotal[$val['credits_id']], 2) . "</div></td>
						</tr>
					";
			}
			$tbl1 .= "
					<tr>
						<td width=\"85%\" colspan =\"8\" style=\"border: 1px solid black;\"><div style=\"text-align: left; font-size:10; font-weight:bold;\">Total</div></td>
						<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right; font-size:10; font-weight:bold;\">" . number_format($total, 2) . "</div></td>
					</tr>
				";
		} else {
			$tbl1 .= "
					<tr>
						<td width=\"100%\" colspan =\"9\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Data Kosong</div></td>
					</tr>
				";
		}
		$tbl1 .= "</table>";

		$pdf->writeHTML($tbl0 . $tbl1, true, false, false, '');

		ob_clean();
		$filename = 'Laporan Potong Gaji Pinjaman.pdf';
		$pdf->Output($filename, 'I');
	}

	public function exportDebtCredits($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebtcredits = $this->AcctDebtPrint_model->getAcctDebtCredits($sesi);
		$acctcredits = $this->AcctDebtPrint_model->getAcctCredits();

		if (count($acctdebtcredits) != 0) {
			$this->load->library('Excel');

			$this->excel->getProperties()->setCreator("CST FISRT")
				->setLastModifiedBy("CST FISRT")
				->setTitle("Laporan Potong Gaji Pinjaman")
				->setSubject("")
				->setDescription("Laporan Potong Gaji Pinjaman")
				->setKeywords("Laporan Potong Gaji Pinjaman")
				->setCategory("Laporan Potong Gaji Pinjaman");

			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);

			$this->excel->getActiveSheet()->mergeCells("B1:J1");
			$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$this->excel->getActiveSheet()->getStyle('B3:J3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B3:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B3:J3')->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Potong Gaji Per " . $sesi['start_date'] . " s.d. " . $sesi['end_date']);
			$this->excel->getActiveSheet()->setCellValue('B3', "No");
			$this->excel->getActiveSheet()->setCellValue('C3', "No Pinjaman");
			$this->excel->getActiveSheet()->setCellValue('D3', "No Anggota");
			$this->excel->getActiveSheet()->setCellValue('E3', "Nama");
			$this->excel->getActiveSheet()->setCellValue('F3', "Divisi");
			$this->excel->getActiveSheet()->setCellValue('G3', "Bagian");
			$this->excel->getActiveSheet()->setCellValue('H3', "Jenis");
			$this->excel->getActiveSheet()->setCellValue('I3', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('J3', "Jumlah");

			$no = 1;
			$j = 4;
			$total = 0;
			foreach ($acctdebtcredits as $key => $val) {
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':J' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('C' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('I' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
				$this->excel->getActiveSheet()->setCellValue('C' . $j, $val['credits_account_serial']);
				$this->excel->getActiveSheet()->setCellValueExplicit('D' . $j, $val['member_no']);
				$this->excel->getActiveSheet()->setCellValue('E' . $j, $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('F' . $j, $val['division_name']);
				$this->excel->getActiveSheet()->setCellValue('G' . $j, $val['part_name']);
				$this->excel->getActiveSheet()->setCellValue('H' . $j, $val['credits_name']);
				$this->excel->getActiveSheet()->setCellValue('I' . $j, $val['credits_payment_date']);
				$this->excel->getActiveSheet()->setCellValue('J' . $j, number_format($val['credits_payment_amount'], 2));

				$subtotal[$val['credits_id']] += $val['credits_payment_amount'];
				$total += $val['credits_payment_amount'];
				$j++;
				$no++;
			}

			foreach ($acctcredits as $key => $val) {
				$this->excel->getActiveSheet()->mergeCells('B' . ($j) . ':I' . ($j));
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':J' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B' . ($j) . ':J' . ($j))->getFont()->setBold(true);

				$this->excel->getActiveSheet()->setCellValue('B' . $j, "Subtotal " . $val['credits_name']);
				$this->excel->getActiveSheet()->setCellValue('J' . $j, number_format($subtotal[$val['credits_id']], 2));

				$j++;
			}

			$this->excel->getActiveSheet()->mergeCells('B' . ($j) . ':I' . ($j));
			$this->excel->getActiveSheet()->getStyle('B' . $j . ':J' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B' . ($j) . ':J' . ($j))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B' . $j, "Total");
			$this->excel->getActiveSheet()->setCellValue('J' . $j, number_format($total, 2));

			$filename = 'Laporan Potong Gaji Pinjaman.xls';
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

	public function printDebtStore($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebtstore = $this->AcctDebtPrint_model->getAcctDebtStore($sesi);

		require_once ('tcpdf/config/tcpdf_config.php');
		require_once ('tcpdf/tcpdf.php');

		$pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetMargins(7, 7, 7, 7);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);

		$base_url = base_url();
		$img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preferencecompany['logo_koperasi'] . "\" alt=\"\" width=\"800%\" height=\"800%\"/>";

		$tbl0 = "
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
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR POTONG GAJI TOKO PER " . $sesi['start_date'] . " s.d " . $sesi['end_date'] . "</div></td>
			    </tr>
			</table>
			<br>";

		$tbl1 = "
			<br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No</div></td>
					<td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Penjualan</div></td>
					<td width=\"5%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">No Agt</div></td>
			        <td width=\"20%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
					<td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Divisi</div></td>
					<td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Bagian</div></td>
			        <td width=\"10%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Tanggal</div></td>
			        <td width=\"15%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Jumlah</div></td>
			    </tr>";

		if (count($acctdebtstore) > 0) {
			$no = 1;
			$total = 0;
			foreach ($acctdebtstore as $key => $val) {
				$coremember = $this->AcctDebtPrint_model->getCoreMemberDetail($val['customer_id']);
				if ($coremember['part_id'] == $sesi['part_id'] && $coremember['division_id'] == $sesi['division_id']) {
					$tbl1 .= "
							<tr>
								<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $no . "</div></td>
								<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $val['sales_invoice_no'] . "</div></td>
								<td width=\"5%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $coremember['member_no'] . "</div></td>
								<td width=\"20%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $coremember['member_name'] . "</div></td>
								<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $coremember['division_name'] . "</div></td>
								<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">" . $coremember['part_name'] . "</div></td>
								<td width=\"10%\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">" . $val['sales_invoice_date'] . "</div></td>
								<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">" . number_format($val['total_amount'], 2) . "</div></td>
							</tr>";
					$no++;
					$total += $val['total_amount'];
				}
			}
			$tbl1 .= "
					<tr>
						<td width=\"85%\" colspan =\"7\" style=\"border: 1px solid black;\"><div style=\"text-align: left; font-size:10; font-weight:bold;\">Total</div></td>
						<td width=\"15%\" style=\"border: 1px solid black;\"><div style=\"text-align: right; font-size:10; font-weight:bold;\">" . number_format($total, 2) . "</div></td>
					</tr>
				";
		} else {
			$tbl1 .= "
					<tr>
						<td width=\"100%\" colspan =\"8\" style=\"border: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Data Kosong</div></td>
					</tr>
				";
		}
		$tbl1 .= "</table>";

		$pdf->writeHTML($tbl0 . $tbl1, true, false, false, '');

		ob_clean();
		$filename = 'Laporan Potong Gaji Pinjaman.pdf';
		$pdf->Output($filename, 'I');
	}

	public function exportDebtStore($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$acctdebtstore = $this->AcctDebtPrint_model->getAcctDebtStore($sesi);

		if (count($acctdebtstore) != 0) {
			$this->load->library('Excel');

			$this->excel->getProperties()->setCreator("CST FISRT")
				->setLastModifiedBy("CST FISRT")
				->setTitle("Laporan Potong Gaji Toko")
				->setSubject("")
				->setDescription("Laporan Potong Gaji Toko")
				->setKeywords("Laporan Potong Gaji Toko")
				->setCategory("Laporan Potong Gaji Toko");

			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);

			$this->excel->getActiveSheet()->mergeCells("B1:I1");
			$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$this->excel->getActiveSheet()->getStyle('B3:I3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B3:I3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B3:I3')->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Potong Gaji Toko Per " . $sesi['start_date'] . " s.d. " . $sesi['end_date']);
			$this->excel->getActiveSheet()->setCellValue('B3', "No");
			$this->excel->getActiveSheet()->setCellValue('C3', "No Penjualan");
			$this->excel->getActiveSheet()->setCellValue('D3', "No Anggota");
			$this->excel->getActiveSheet()->setCellValue('E3', "Nama");
			$this->excel->getActiveSheet()->setCellValue('F3', "Divisi");
			$this->excel->getActiveSheet()->setCellValue('G3', "Bagian");
			$this->excel->getActiveSheet()->setCellValue('H3', "Tanggal");
			$this->excel->getActiveSheet()->setCellValue('I3', "Jumlah");

			$no = 1;
			$j = 4;
			$total = 0;
			foreach ($acctdebtstore as $key => $val) {
				$coremember = $this->AcctDebtPrint_model->getCoreMemberDetail($val['customer_id']);
				if ($coremember['part_id'] == $sesi['part_id'] && $coremember['division_id'] == $sesi['division_id']) {
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->getStyle('B' . $j . ':I' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('C' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('H' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('I' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
					$this->excel->getActiveSheet()->setCellValue('C' . $j, $val['sales_invoice_no']);
					$this->excel->getActiveSheet()->setCellValue('D' . $j, $coremember['member_no']);
					$this->excel->getActiveSheet()->setCellValue('E' . $j, $coremember['member_name']);
					$this->excel->getActiveSheet()->setCellValue('F' . $j, $coremember['division_name']);
					$this->excel->getActiveSheet()->setCellValue('G' . $j, $coremember['part_name']);
					$this->excel->getActiveSheet()->setCellValue('H' . $j, $val['sales_invoice_date']);
					$this->excel->getActiveSheet()->setCellValue('I' . $j, number_format($val['total_amount'], 2));

					$total += $val['total_amount'];
					$j++;
					$no++;
				}
			}

			$this->excel->getActiveSheet()->mergeCells('B' . ($j) . ':H' . ($j));
			$this->excel->getActiveSheet()->getStyle('B' . $j . ':I' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('I' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B' . ($j) . ':I' . ($j))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B' . $j, "Total");
			$this->excel->getActiveSheet()->setCellValue('I' . $j, number_format($total, 2));

			$filename = 'Laporan Potong Gaji Toko.xls';
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

	public function printDebtRecap($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$coremember = $this->AcctDebtPrint_model->getCoreMember($sesi);
		$data = array();

		foreach ($coremember as $key => $val) {
			$total = 0;

			$debtcategory = $this->AcctDebtPrint_model->getMemberDebtCategory($sesi, $val['member_id']);
			$debtsavings = $this->AcctDebtPrint_model->getMemberDebtSavings($sesi, $val['member_id']);
			$debtcredits = $this->AcctDebtPrint_model->getMemberDebtCredits($sesi, $val['member_id']);
			$debtstore = $this->AcctDebtPrint_model->getMemberDebtStore($sesi, $val['member_id']);
			$debtprincipal = $this->AcctDebtPrint_model->getMemberDebtPrincipal($sesi, $val['member_id']);
			$debtmandatory = $this->AcctDebtPrint_model->getMemberDebtMandatory($sesi, $val['member_id']);

			if ($debtcategory || $debtsavings || $debtcredits || $debtstore || $debtprincipal || $debtmandatory) {
				$data[$val['member_id']]['member_no'] = $val['member_no'];
				$data[$val['member_id']]['member_name'] = $val['member_name'];
				$data[$val['member_id']]['division_name'] = $val['division_name'];
				$data[$val['member_id']]['part_name'] = $val['part_name'];
			}

			if ($debtcategory) {
				$data[$val['member_id']]['debtcategory'] = $debtcategory;
			}

			if ($debtsavings) {
				$data[$val['member_id']]['debtsavings'] = $debtsavings;
			}

			if ($debtcredits) {
				$data[$val['member_id']]['debtcredits'] = $debtcredits;
			}

			if ($debtstore) {
				$data[$val['member_id']]['debtstore'] = $debtstore;
			}

			if ($debtprincipal) {
				$data[$val['member_id']]['debtprincipal'] = $debtprincipal;
			}

			if ($debtmandatory) {
				$data[$val['member_id']]['debtmandatory'] = $debtmandatory;
			}
		}

		require_once ('tcpdf/config/tcpdf_config.php');
		require_once ('tcpdf/tcpdf.php');

		$pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetMargins(7, 7, 7, 7);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);

		$base_url = base_url();
		$img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preferencecompany['logo_koperasi'] . "\" alt=\"\" width=\"800%\" height=\"800%\"/>";

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
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"100%\"><div style=\"text-align: center; font-size:14px; font-weight:bold\">DAFTAR POTONG GAJI REKAP</div></td>
			    </tr>
				<tr>
					<td width=\"100%\"><div style=\"text-align: center; font-size:12px;\">Per " . $sesi['start_date'] . " s.d " . $sesi['end_date'] . "</div></td>
				</tr>
			</table>
			<br>
			<br>
			<br>
			";

		$pdf->writeHTML($tbl, true, false, false, '');

		foreach ($data as $key => $val) {
			$tbl = "
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
					<tr>
						<td width=\"5%\" style=\"font-size:13px; font-weight:bold\">Nama :</td>
						<td width=\"20%\" style=\"font-size:13px; font-weight:bold\">" . $val['member_name'] . "</td>
						<td width=\"10%\" style=\"font-size:13px; font-weight:bold\">No Anggota : </td>
						<td width=\"20%\" style=\"font-size:13px; font-weight:bold\">" . $val['member_no'] . "</td>
						<td width=\"5%\" style=\"font-size:13px; font-weight:bold\">Divisi : </td>
						<td width=\"20%\" style=\"font-size:13px; font-weight:bold\">" . $val['division_name'] . "</td>
						<td width=\"6%\" style=\"font-size:13px; font-weight:bold\">Bagian : </td>
						<td width=\"14%\" style=\"font-size:13px; font-weight:bold\">" . $val['part_name'] . "</td>
					</tr>
				</table>
				<br>
				<br>
				";

			$category_total = 0;
			if ($val['debtcategory']) {
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"100%\" colspan=\"3\" style=\"text-align: center; font-weight:bold; background-color:#000000; color:#ffffff\">KATEGORI</td>
						</tr>
						<tr>
							<td width=\"30%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Tanggal</td>
							<td width=\"35%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Kategori</td>
							<td width=\"35%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Jumlah</td>
						</tr>
					</table>
					";

				foreach ($val['debtcategory'] as $keyy => $vall) {
					$tbl .= "
						<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
							<tr>
								<td width=\"30%\" style=\"text-align: center;\">" . $vall['debt_date'] . "</td>
								<td width=\"35%\" style=\"text-align: left;\">" . $vall['debt_category_name'] . "</td>
								<td width=\"35%\" style=\"text-align: right;\">" . number_format($vall['debt_amount'], 2) . "</td>
							</tr>
						</table>
						";
					$category_total += $vall['debt_amount'];
				}
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"65%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Subtotal</td>
							<td width=\"35%\" style=\"text-align: right; font-weight:bold; background-color:#2196f3; color:#ffffff\">" . number_format($category_total, 2) . "</td>
						</tr>
					</table>
					";
			}

			$savings_total = 0;
			if ($val['debtsavings']) {
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"100%\" colspan=\"3\" style=\"text-align: center; font-weight:bold; background-color:#000000; color:#ffffff\">TABUNGAN</td>
						</tr>
						<tr>
							<td width=\"30%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Tanggal</td>
							<td width=\"35%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">No Tabungan</td>
							<td width=\"35%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Jumlah</td>
						</tr>
					</table>
					";

				foreach ($val['debtsavings'] as $keyy => $vall) {
					$tbl .= "
						<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
							<tr>
								<td width=\"30%\" style=\"text-align: center;\">" . $vall['savings_cash_mutation_date'] . "</td>
								<td width=\"35%\" style=\"text-align: left;\">" . $vall['savings_account_no'] . "</td>
								<td width=\"35%\" style=\"text-align: right;\">" . number_format($vall['savings_cash_mutation_amount'], 2) . "</td>
							</tr>
						</table>
						";
					$savings_total += $vall['savings_cash_mutation_amount'];
				}
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"65%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Subtotal</td>
							<td width=\"35%\" style=\"text-align: right; font-weight:bold; background-color:#2196f3; color:#ffffff\">" . number_format($savings_total, 2) . "</td>
						</tr>
					</table>
					";
			}

			$credits_total = 0;
			if ($val['debtcredits']) {
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"100%\" colspan=\"3\" style=\"text-align: center; font-weight:bold; background-color:#000000; color:#ffffff\">PINJAMAN</td>
						</tr>
						<tr>
							<td width=\"30%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Tanggal</td>
							<td width=\"20%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Jenis Pinjaman</td>
							<td width=\"15%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">No Pinjaman</td>
							<td width=\"35%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Jumlah</td>
						</tr>
					</table>
					";

				foreach ($val['debtcredits'] as $keyy => $vall) {
					$tbl .= "
						<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
							<tr>
								<td width=\"30%\" style=\"text-align: center;\">" . $vall['credits_payment_date'] . "</td>
								<td width=\"20%\" style=\"text-align: left;\">" . $vall['credits_name'] . "</td>
								<td width=\"15%\" style=\"text-align: left;\">" . $vall['credits_account_serial'] . "</td>
								<td width=\"35%\" style=\"text-align: right;\">" . number_format($vall['credits_payment_amount'], 2) . "</td>
							</tr>
						</table>
						";
					$credits_total += $vall['credits_payment_amount'];
				}
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"65%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Subtotal</td>
							<td width=\"35%\" style=\"text-align: right; font-weight:bold; background-color:#2196f3; color:#ffffff\">" . number_format($credits_total, 2) . "</td>
						</tr>
					</table>
					";
			}

			$store_total = 0;
			if ($val['debtstore']) {
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"100%\" colspan=\"3\" style=\"text-align: center; font-weight:bold; background-color:#000000; color:#ffffff\">TOKO</td>
						</tr>
						<tr>
							<td width=\"30%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Tanggal</td>
							<td width=\"35%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">No Penjualan</td>
							<td width=\"35%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Jumlah</td>
						</tr>
					</table>
					";

				foreach ($val['debtstore'] as $keyy => $vall) {
					$tbl .= "
						<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
							<tr>
								<td width=\"30%\" style=\"text-align: center;\">" . $vall['sales_invoice_date'] . "</td>
								<td width=\"35%\" style=\"text-align: left;\">" . $vall['sales_invoice_no'] . "</td>
								<td width=\"35%\" style=\"text-align: right;\">" . number_format($vall['total_amount'], 2) . "</td>
							</tr>
						</table>
						";
					$store_total += $vall['total_amount'];
				}
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"50%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Subtotal</td>
							<td width=\"50%\" style=\"text-align: right; font-weight:bold; background-color:#2196f3; color:#ffffff\">" . number_format($store_total, 2) . "</td>
						</tr>
					</table>
					";
			}

			$principal_total = 0;
			if ($val['debtprincipal']) {
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"100%\" colspan=\"3\" style=\"text-align: center; font-weight:bold; background-color:#000000; color:#ffffff\">SIMPANAN POKOK ANGGOTA</td>
						</tr>
						<tr>
							<td width=\"50%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Tanggal</td>
							<td width=\"50%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Jumlah</td>
						</tr>
					</table>
					";

				foreach ($val['debtprincipal'] as $keyy => $vall) {
					$tbl .= "
						<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
							<tr>
								<td width=\"50%\" style=\"text-align: center;\">" . $vall['transaction_date'] . "</td>
								<td width=\"50%\" style=\"text-align: right;\">" . number_format($vall['principal_savings_amount'], 2) . "</td>
							</tr>
						</table>
						";
					$principal_total += $vall['principal_savings_amount'];
				}
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"50%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Subtotal</td>
							<td width=\"50%\" style=\"text-align: right; font-weight:bold; background-color:#2196f3; color:#ffffff\">" . number_format($principal_total, 2) . "</td>
						</tr>
					</table>
					";
			}

			$mandatory_total = 0;
			if ($val['debtmandatory']) {
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"100%\" colspan=\"3\" style=\"text-align: center; font-weight:bold; background-color:#000000; color:#ffffff\">SIMPANAN WAJIB ANGGOTA</td>
						</tr>
						<tr>
							<td width=\"50%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Tanggal</td>
							<td width=\"50%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Jumlah</td>
						</tr>
					</table>
					";

				foreach ($val['debtmandatory'] as $keyy => $vall) {
					$tbl .= "
						<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
							<tr>
								<td width=\"50%\" style=\"text-align: center;\">" . $vall['transaction_date'] . "</td>
								<td width=\"50%\" style=\"text-align: right;\">" . number_format($vall['mandatory_savings_amount'], 2) . "</td>
							</tr>
						</table>
						";
					$mandatory_total += $vall['mandatory_savings_amount'];
				}
				$tbl .= "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
						<tr>
							<td width=\"50%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Subtotal</td>
							<td width=\"50%\" style=\"text-align: right; font-weight:bold; background-color:#2196f3; color:#ffffff\">" . number_format($mandatory_total, 2) . "</td>
						</tr>
					</table>
					";
			}

			$total = $category_total + $savings_total + $credits_total + $store_total + $principal_total + $mandatory_total;
			$tbl .= "
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
					<tr>
						<td width=\"50%\" style=\"text-align: center; font-weight:bold; background-color:#2196f3; color:#ffffff\">Total</td>
						<td width=\"50%\" style=\"text-align: right; font-weight:bold; background-color:#2196f3; color:#ffffff\">" . number_format($total, 2) . "</td>
					</tr>
				</table>
				";

			$tbl .= "<br pagebreak=\"true\"/>";

			$pdf->writeHTML($tbl, true, false, false, '');
		}

		ob_clean();
		$filename = 'Laporan Potong Gaji Rekap.pdf';
		$pdf->Output($filename, 'I');
	}

	public function exportDebtRecap($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$coremember = $this->AcctDebtPrint_model->getCoreMember($sesi);
		$data = array();

		foreach ($coremember as $key => $val) {
			$total = 0;

			$debtcategory = $this->AcctDebtPrint_model->getMemberDebtCategory($sesi, $val['member_id']);
			$debtsavings = $this->AcctDebtPrint_model->getMemberDebtSavings($sesi, $val['member_id']);
			$debtcredits = $this->AcctDebtPrint_model->getMemberDebtCredits($sesi, $val['member_id']);
			$debtstore = $this->AcctDebtPrint_model->getMemberDebtStore($sesi, $val['member_id']);
			$debtprincipal = $this->AcctDebtPrint_model->getMemberDebtPrincipal($sesi, $val['member_id']);
			$debtmandatory = $this->AcctDebtPrint_model->getMemberDebtMandatory($sesi, $val['member_id']);

			if ($debtcategory || $debtsavings || $debtcredits || $debtstore || $debtprincipal || $debtmandatory) {
				$data[$val['member_id']]['member_no'] = $val['member_no'];
				$data[$val['member_id']]['member_name'] = $val['member_name'];
				$data[$val['member_id']]['division_name'] = $val['division_name'];
				$data[$val['member_id']]['part_name'] = $val['part_name'];
			}

			if ($debtcategory) {
				$data[$val['member_id']]['debtcategory'] = $debtcategory;
			}

			if ($debtsavings) {
				$data[$val['member_id']]['debtsavings'] = $debtsavings;
			}

			if ($debtcredits) {
				$data[$val['member_id']]['debtcredits'] = $debtcredits;
			}

			if ($debtstore) {
				$data[$val['member_id']]['debtstore'] = $debtstore;
			}

			if ($debtprincipal) {
				$data[$val['member_id']]['debtprincipal'] = $debtprincipal;
			}

			if ($debtmandatory) {
				$data[$val['member_id']]['debtmandatory'] = $debtmandatory;
			}
		}

		$this->load->library('Excel');

		$this->excel->getProperties()->setCreator("CST FISRT")
			->setLastModifiedBy("CST FISRT")
			->setTitle("Laporan Potong Gaji Rekap")
			->setSubject("")
			->setDescription("Laporan Potong Gaji Rekap")
			->setKeywords("Laporan Potong Gaji Rekap")
			->setCategory("Laporan Potong Gaji Rekap");

		$this->excel->setActiveSheetIndex(0);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

		$this->excel->getActiveSheet()->mergeCells("B1:G1");
		$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);

		$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Potong Gaji Rekap Per " . $sesi['start_date'] . " s.d. " . $sesi['end_date']);

		$row = 3;
		foreach ($data as $key => $val) {
			$this->excel->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':I' . ($row))->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B' . $row, "Nama : ");
			$this->excel->getActiveSheet()->setCellValue('C' . $row, $val['member_name']);
			$this->excel->getActiveSheet()->setCellValue('D' . $row, "No Anggota : ");
			$this->excel->getActiveSheet()->setCellValue('E' . $row, $val['member_no']);
			$this->excel->getActiveSheet()->setCellValue('F' . $row, "Divisi : ");
			$this->excel->getActiveSheet()->setCellValue('G' . $row, $val['division_name']);
			$this->excel->getActiveSheet()->setCellValue('H' . $row, "Bagian : ");
			$this->excel->getActiveSheet()->setCellValue('I' . $row, $val['part_name']);

			$row += 2;

			$category_total = 0;
			if ($val['debtcategory']) {
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
				$this->excel->getActiveSheet()->getStyle('B' . ($row + 1) . ':G' . ($row + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':G' . ($row));
				$this->excel->getActiveSheet()->mergeCells('B' . ($row + 1) . ':C' . ($row + 1));
				$this->excel->getActiveSheet()->mergeCells('D' . ($row + 1) . ':E' . ($row + 1));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row + 1) . ':G' . ($row + 1));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "KATEGORI");
				$this->excel->getActiveSheet()->setCellValue('B' . ($row + 1), "Tanggal");
				$this->excel->getActiveSheet()->setCellValue('D' . ($row + 1), "Kategori");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row + 1), "Jumlah");

				$row += 2;

				foreach ($val['debtcategory'] as $keyy => $vall) {
					$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('D' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('F' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':C' . ($row));
					$this->excel->getActiveSheet()->mergeCells('D' . ($row) . ':E' . ($row));
					$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

					$this->excel->getActiveSheet()->setCellValue('B' . ($row), $vall['debt_date']);
					$this->excel->getActiveSheet()->setCellValue('D' . ($row), $vall['debt_category_name']);
					$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($vall['debt_amount'], 2));

					$category_total += $vall['debt_amount'];
					$row++;
				}

				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':E' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('F' . ($row) . ':G' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':E' . ($row));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "Subtotal");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($category_total, 2));
			}

			$savings_total = 0;
			if ($val['debtsavings']) {
				$row += 1;
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
				$this->excel->getActiveSheet()->getStyle('B' . ($row + 1) . ':G' . ($row + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':G' . ($row));
				$this->excel->getActiveSheet()->mergeCells('B' . ($row + 1) . ':C' . ($row + 1));
				$this->excel->getActiveSheet()->mergeCells('D' . ($row + 1) . ':E' . ($row + 1));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row + 1) . ':G' . ($row + 1));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "TABUNGAN");
				$this->excel->getActiveSheet()->setCellValue('B' . ($row + 1), "Tanggal");
				$this->excel->getActiveSheet()->setCellValue('D' . ($row + 1), "No Tabungan");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row + 1), "Jumlah");

				$row += 2;

				foreach ($val['debtsavings'] as $keyy => $vall) {
					$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('D' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('F' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':C' . ($row));
					$this->excel->getActiveSheet()->mergeCells('D' . ($row) . ':E' . ($row));
					$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

					$this->excel->getActiveSheet()->setCellValue('B' . ($row), $vall['savings_cash_mutation_date']);
					$this->excel->getActiveSheet()->setCellValueExplicit('D' . ($row), $vall['savings_account_no']);
					$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($vall['savings_cash_mutation_amount'], 2));

					$savings_total += $vall['savings_cash_mutation_amount'];
					$row++;
				}

				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':E' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('F' . ($row) . ':G' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':E' . ($row));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "Subtotal");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($savings_total, 2));
			}

			$credits_total = 0;
			if ($val['debtcredits']) {
				$row += 1;
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
				$this->excel->getActiveSheet()->getStyle('B' . ($row + 1) . ':G' . ($row + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':G' . ($row));
				$this->excel->getActiveSheet()->mergeCells('B' . ($row + 1) . ':C' . ($row + 1));
				// $this->excel->getActiveSheet()->mergeCells('D'.($row+1).':E'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row + 1) . ':G' . ($row + 1));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "PINJAMAN");
				$this->excel->getActiveSheet()->setCellValue('B' . ($row + 1), "Tanggal");
				$this->excel->getActiveSheet()->setCellValue('D' . ($row + 1), "Jenis Pinjaman");
				$this->excel->getActiveSheet()->setCellValue('E' . ($row + 1), "No Pinjaman");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row + 1), "Jumlah");

				$row += 2;

				foreach ($val['debtcredits'] as $keyy => $vall) {
					$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('D' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('F' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':C' . ($row));
					// $this->excel->getActiveSheet()->mergeCells('D'.($row).':E'.($row));
					$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

					$this->excel->getActiveSheet()->setCellValue('B' . ($row), $vall['credits_payment_date']);
					$this->excel->getActiveSheet()->setCellValue('D' . ($row), $vall['credits_name']);
					$this->excel->getActiveSheet()->setCellValue('E' . ($row), $vall['credits_account_serial']);
					$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($vall['credits_payment_amount'], 2));

					$credits_total += $vall['credits_payment_amount'];
					$row++;
				}

				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':E' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('F' . ($row) . ':G' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':E' . ($row));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "Subtotal");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($credits_total, 2));
			}

			$store_total = 0;
			if ($val['debtstore']) {
				$row += 1;
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
				$this->excel->getActiveSheet()->getStyle('B' . ($row + 1) . ':G' . ($row + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':G' . ($row));
				$this->excel->getActiveSheet()->mergeCells('B' . ($row + 1) . ':C' . ($row + 1));
				$this->excel->getActiveSheet()->mergeCells('D' . ($row + 1) . ':E' . ($row + 1));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row + 1) . ':G' . ($row + 1));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "TOKO");
				$this->excel->getActiveSheet()->setCellValue('B' . ($row + 1), "Tanggal");
				$this->excel->getActiveSheet()->setCellValue('D' . ($row + 1), "No Penjualan");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row + 1), "Jumlah");

				$row += 2;

				foreach ($val['debtstore'] as $keyy => $vall) {
					$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('D' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('F' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':C' . ($row));
					$this->excel->getActiveSheet()->mergeCells('D' . ($row) . ':E' . ($row));
					$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

					$this->excel->getActiveSheet()->setCellValue('B' . ($row), $vall['sales_invoice_date']);
					$this->excel->getActiveSheet()->setCellValue('D' . ($row), $vall['sales_invoice_no']);
					$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($vall['total_amount'], 2));

					$store_total += $vall['total_amount'];
					$row++;
				}

				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':E' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('F' . ($row) . ':G' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':E' . ($row));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "Subtotal");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($store_total, 2));
			}

			$principal_total = 0;
			if ($val['debtprincipal']) {
				$row += 1;
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
				$this->excel->getActiveSheet()->getStyle('B' . ($row + 1) . ':G' . ($row + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':G' . ($row));
				$this->excel->getActiveSheet()->mergeCells('B' . ($row + 1) . ':D' . ($row + 1));
				$this->excel->getActiveSheet()->mergeCells('E' . ($row + 1) . ':G' . ($row + 1));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "SIMPANAN POKOK ANGGOTA");
				$this->excel->getActiveSheet()->setCellValue('B' . ($row + 1), "Tanggal");
				$this->excel->getActiveSheet()->setCellValue('E' . ($row + 1), "Jumlah");

				$row += 2;

				foreach ($val['debtprincipal'] as $keyy => $vall) {
					$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('E' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':D' . ($row));
					$this->excel->getActiveSheet()->mergeCells('E' . ($row) . ':G' . ($row));

					$this->excel->getActiveSheet()->setCellValue('B' . ($row), $vall['transaction_date']);
					$this->excel->getActiveSheet()->setCellValue('E' . ($row), number_format($vall['principal_savings_amount'], 2));

					$principal_total += $vall['principal_savings_amount'];
					$row++;
				}

				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':E' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('F' . ($row) . ':G' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':E' . ($row));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "Subtotal");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($mandatory_total, 2));
			}

			$mandatory_total = 0;
			if ($val['debtmandatory']) {
				$row += 1;
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('000000');
				$this->excel->getActiveSheet()->getStyle('B' . ($row + 1) . ':G' . ($row + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row + 1))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':G' . ($row));
				$this->excel->getActiveSheet()->mergeCells('B' . ($row + 1) . ':D' . ($row + 1));
				$this->excel->getActiveSheet()->mergeCells('E' . ($row + 1) . ':G' . ($row + 1));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "SIMPANAN POKOK ANGGOTA");
				$this->excel->getActiveSheet()->setCellValue('B' . ($row + 1), "Tanggal");
				$this->excel->getActiveSheet()->setCellValue('E' . ($row + 1), "Jumlah");

				$row += 2;

				foreach ($val['debtmandatory'] as $keyy => $vall) {
					$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('E' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':D' . ($row));
					$this->excel->getActiveSheet()->mergeCells('E' . ($row) . ':G' . ($row));

					$this->excel->getActiveSheet()->setCellValue('B' . ($row), $vall['transaction_date']);
					$this->excel->getActiveSheet()->setCellValue('E' . ($row), number_format($vall['mandatory_savings_amount'], 2));

					$mandatory_total += $vall['mandatory_savings_amount'];
					$row++;
				}

				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':E' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('F' . ($row) . ':G' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
				$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->getColor()->setRGB('ffffff');

				$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':E' . ($row));
				$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

				$this->excel->getActiveSheet()->setCellValue('B' . ($row), "Subtotal");
				$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($mandatory_total, 2));
			}

			$row += 1;
			$total = $category_total + $savings_total + $credits_total + $store_total + $principal_total + $mandatory_total;
			$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':E' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('F' . ($row) . ':G' . ($row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2196f3');
			$this->excel->getActiveSheet()->getStyle('B' . ($row) . ':G' . ($row))->getFont()->getColor()->setRGB('ffffff');

			$this->excel->getActiveSheet()->mergeCells('B' . ($row) . ':E' . ($row));
			$this->excel->getActiveSheet()->mergeCells('F' . ($row) . ':G' . ($row));

			$this->excel->getActiveSheet()->setCellValue('B' . ($row), "Total");
			$this->excel->getActiveSheet()->setCellValue('F' . ($row), number_format($total, 2));

			$row += 3;
		}

		$filename = 'Laporan Potong Gaji Rekap.xls';
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = IOFactory::createWriter($this->excel, 'Excel5');
		ob_end_clean();
		$objWriter->save('php://output');
	}

	public function printDebtSimple($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$coremember = $this->AcctDebtPrint_model->getCoreMember($sesi);
		$data = array();

		// echo json_encode($preferencecompany);
		// exit;

		foreach ($coremember as $key => $val) {
			$total = 0;

			$debtcategory = $this->AcctDebtPrint_model->getMemberDebtCategory($sesi, $val['member_id']);
			$debtsavings = $this->AcctDebtPrint_model->getMemberDebtSavings($sesi, $val['member_id']);
			$debtcredits = $this->AcctDebtPrint_model->getMemberDebtCredits($sesi, $val['member_id']);
			$debtstore = $this->AcctDebtPrint_model->getMemberDebtStore($sesi, $val['member_id']);
			$debtmembersavings = $this->AcctDebtPrint_model->getMemberDebtMemberSavings($sesi, $val['member_id']);


			if ($debtcategory || $debtsavings || $debtcredits || $debtstore || $debtmembersavings) {
				$data[$val['member_id']]['member_no'] = $val['member_no'];
				$data[$val['member_id']]['member_nik'] = $val['member_nik'];
				$data[$val['member_id']]['member_name'] = $val['member_name'];
				$data[$val['member_id']]['division_name'] = $val['division_name'];
				$data[$val['member_id']]['part_name'] = $val['part_name'];
				$data[$val['member_id']]['member_company_specialities'] = $val['member_company_specialities'];
			}

			if ($debtcategory) {
				$data[$val['member_id']]['debtcategory'] = $debtcategory;
			}

			if ($debtsavings) {
				$data[$val['member_id']]['debtsavings'] = $debtsavings;
			}

			if ($debtcredits) {
				$data[$val['member_id']]['debtcredits'] = $debtcredits;
			}

			if ($debtstore) {
				$data[$val['member_id']]['debtstore'] = $debtstore;
			}
			if ($debtmembersavings) {
				$data[$val['member_id']]['debtmembersavings'] = $debtmembersavings;
			}
		}

		require_once ('tcpdf/config/tcpdf_config.php');
		require_once ('tcpdf/tcpdf.php');

		$pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetMargins(7, 7, 7, 7);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);

		$base_url = base_url();
		$img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preferencecompany['logo_koperasi'] . "\" alt=\"\" width=\"800%\" height=\"800%\"/>";

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
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"100%\"><div style=\"text-align: center; font-size:14px; font-weight:bold\">DAFTAR POTONG GAJI</div></td>
			    </tr>
				<tr>
					<td width=\"100%\"><div style=\"text-align: center; font-size:12px;\">Per " . $sesi['start_date'] . " s.d " . $sesi['end_date'] . "</div></td>
				</tr>
			</table>
			<br>
			<br>
			<br>
			";

		$tbl .= "
			<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
				<tr>
					<td width=\"5%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">No Agt</td>
					<td width=\"15%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">NIK</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Nama</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Unit</td>
					<td width=\"15%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Divisi</td>
					<td width=\"15%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Bagian</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Total</td>
				</tr>
			</table>
			";

		foreach ($data as $key => $val) {
			$category_total = 0;
			if ($val['debtcategory']) {
				foreach ($val['debtcategory'] as $keyy => $vall) {
					$category_total += $vall['debt_amount'];
				}
			}

			$savings_total = 0;
			if ($val['debtsavings']) {
				foreach ($val['debtsavings'] as $keyy => $vall) {
					$savings_total += $vall['savings_cash_mutation_amount'];
				}
			}

			$credits_total = 0;
			if ($val['debtcredits']) {
				foreach ($val['debtcredits'] as $keyy => $vall) {
					$credits_total += $vall['credits_payment_amount'];
				}
			}

			$store_total = 0;
			if ($val['debtstore']) {
				foreach ($val['debtstore'] as $keyy => $vall) {
					$store_total += $vall['total_amount'];
				}
			}

			$simpanan_pokok = 0;
			if ($val['debtmembersavings']) {
				foreach ($val['debtmembersavings'] as $keyy => $vall) {
					$simpanan_pokok += $vall['principal_savings_amount'];
				}
			}

			$simpanan_wajib = 0;
			if ($val['debtmembersavings']) {
				foreach ($val['debtmembersavings'] as $keyy => $vall) {
					$simpanan_wajib += $vall['mandatory_savings_amount'];
				}
			}


			$total = $category_total + $savings_total + $credits_total + $store_total + $simpanan_pokok + $simpanan_wajib;

			$tbl .= "
				<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
					<tr>
						<td width=\"5%\" style=\"font-size:13px; text-align: center;\">" . $val['member_no'] . "</td>
						<td width=\"15%\" style=\"font-size:13px; text-align: center;\">" . $val['member_nik'] . "</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: left;\">" . $val['member_name'] . "</td>
						<td width=\"10%\" style=\"font-size:13px; text-align: center;\">" . $val['member_company_specialities'] . "</td>
						<td width=\"15%\" style=\"font-size:13px; text-align: left;\">" . $val['division_name'] . "</td>
						<td width=\"15%\" style=\"font-size:13px; text-align: left;\">" . $val['part_name'] . "</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: right;\">" . number_format($total, 2) . "</td>
					</tr>
				</table>";
		}

		$pdf->writeHTML($tbl, true, false, false, '');

		ob_clean();
		$filename = 'Laporan Potong Gaji Rekap.pdf';
		$pdf->Output($filename, 'I');
	}

	public function exportDebtSimple($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$coremember = $this->AcctDebtPrint_model->getCoreMember($sesi);
		$data = array();

		foreach ($coremember as $key => $val) {
			$total = 0;

			$debtcategory = $this->AcctDebtPrint_model->getMemberDebtCategory($sesi, $val['member_id']);
			$debtsavings = $this->AcctDebtPrint_model->getMemberDebtSavings($sesi, $val['member_id']);
			$debtcredits = $this->AcctDebtPrint_model->getMemberDebtCredits($sesi, $val['member_id']);
			$debtstore = $this->AcctDebtPrint_model->getMemberDebtStore($sesi, $val['member_id']);
			$debtmembersavings = $this->AcctDebtPrint_model->getMemberDebtMemberSavings($sesi, $val['member_id']);


			if ($debtcategory || $debtsavings || $debtcredits || $debtstore || $debtmembersavings) {

				$data[$val['member_id']]['member_no'] = $val['member_no'];
				$data[$val['member_id']]['member_nik'] = $val['member_nik'];
				$data[$val['member_id']]['member_name'] = $val['member_name'];
				$data[$val['member_id']]['division_name'] = $val['division_name'];
				$data[$val['member_id']]['part_name'] = $val['part_name'];
				$data[$val['member_id']]['member_company_specialities'] = $val['member_company_specialities'];
			}

			if ($debtcategory) {
				$data[$val['member_id']]['debtcategory'] = $debtcategory;
			}

			if ($debtsavings) {
				$data[$val['member_id']]['debtsavings'] = $debtsavings;
			}

			if ($debtcredits) {
				$data[$val['member_id']]['debtcredits'] = $debtcredits;
			}

			if ($debtstore) {
				$data[$val['member_id']]['debtstore'] = $debtstore;
			}

			if ($debtmembersavings) {
				$data[$val['member_id']]['debtmembersavings'] = $debtmembersavings;
			}
		}

		$this->load->library('Excel');

		$this->excel->getProperties()->setCreator("CST FISRT")
			->setLastModifiedBy("CST FISRT")
			->setTitle("Laporan Potong Gaji Rekap")
			->setSubject("")
			->setDescription("Laporan Potong Gaji Rekap")
			->setKeywords("Laporan Potong Gaji Rekap")
			->setCategory("Laporan Potong Gaji Rekap");

		$this->excel->setActiveSheetIndex(0);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

		$this->excel->getActiveSheet()->mergeCells("B1:H1");
		$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);

		$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Potong Gaji Rekap Per " . $sesi['start_date'] . " s.d. " . $sesi['end_date']);

		$this->excel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('D3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('E3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B3:H3' . ($row))->getFont()->setBold(true);

		$this->excel->getActiveSheet()->setCellValue('B3', "No Anggota");
		$this->excel->getActiveSheet()->setCellValue('C3', "NIK");
		$this->excel->getActiveSheet()->setCellValue('D3', "Nama");
		$this->excel->getActiveSheet()->setCellValue('E3', "Unit");
		$this->excel->getActiveSheet()->setCellValue('F3', "Divisi");
		$this->excel->getActiveSheet()->setCellValue('G3', "Bagian");
		$this->excel->getActiveSheet()->setCellValue('H3', "Total");

		$row = 4;
		foreach ($data as $key => $val) {
			$this->excel->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

			$category_total = 0;
			if ($val['debtcategory']) {
				foreach ($val['debtcategory'] as $keyy => $vall) {
					$category_total += $vall['debt_amount'];
				}
			}

			$savings_total = 0;
			if ($val['debtsavings']) {
				foreach ($val['debtsavings'] as $keyy => $vall) {
					$savings_total += $vall['savings_cash_mutation_amount'];
				}
			}

			$credits_total = 0;
			if ($val['debtcredits']) {
				foreach ($val['debtcredits'] as $keyy => $vall) {
					$credits_total += $vall['credits_payment_amount'];
				}
			}

			$store_total = 0;
			if ($val['debtstore']) {
				foreach ($val['debtstore'] as $keyy => $vall) {
					$store_total += $vall['total_amount'];
				}
			}
			$simpanan_pokok = 0;
			if ($val['debtmembersavings']) {
				foreach ($val['debtmembersavings'] as $keyy => $vall) {
					$simpanan_pokok += $vall['principal_savings_amount'];
				}
			}

			$simpanan_wajib = 0;
			if ($val['debtmembersavings']) {
				foreach ($val['debtmembersavings'] as $keyy => $vall) {
					$simpanan_wajib += $vall['mandatory_savings_amount'];
				}
			}


			$total = $category_total + $savings_total + $credits_total + $store_total + $simpanan_pokok + $simpanan_wajib;


			$this->excel->getActiveSheet()->setCellValueExplicit('B' . ($row), $val['member_no']);
			$this->excel->getActiveSheet()->setCellValue('C' . ($row), $val['member_nik']);
			$this->excel->getActiveSheet()->setCellValue('D' . ($row), $val['member_name']);
			$this->excel->getActiveSheet()->setCellValue('E' . ($row), $val['member_company_specialities']);
			$this->excel->getActiveSheet()->setCellValue('F' . ($row), $val['division_name']);
			$this->excel->getActiveSheet()->setCellValue('G' . ($row), $val['part_name']);
			$this->excel->getActiveSheet()->setCellValue('H' . ($row), number_format($total, 2));

			$row += 1;
		}

		$this->excel->getActiveSheet()->getStyle('B3:H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

		$filename = 'Laporan Potong Gaji Rekap.xls';
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = IOFactory::createWriter($this->excel, 'Excel5');
		ob_end_clean();
		$objWriter->save('php://output');
	}


	public function printDebtSimpleTemp($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$coremember = $this->AcctDebtPrint_model->getCoreMember($sesi);
		$data = array();

		// $list = $this->AcctDebtPrint_model->getMemberDebtCategoryTemp($sesi, '');
		// echo json_encode($list);
		// exit;

		foreach ($coremember as $key => $val) {
			$total = 0;

			$debtcategory = $this->AcctDebtPrint_model->getMemberDebtCategoryTemp($sesi, $val['member_id']);
			$debtsavings = $this->AcctDebtPrint_model->getMemberDebtSavingsTemp($sesi, $val['member_id']);
			$debtcredits = $this->AcctDebtPrint_model->getMemberDebtCreditsTemp($sesi, $val['member_id']);
			$debtstore = $this->AcctDebtPrint_model->getMemberDebtStore($sesi, $val['member_id']);
			$debtmembersavings = $this->AcctDebtPrint_model->getMemberDebtMemberSavingsTemp($sesi, $val['member_id']);

			// echo json_encode($debtcategory);
			// exit;


			if ($debtcategory || $debtsavings || $debtcredits || $debtstore || $debtmembersavings) {
				$data[$val['member_id']]['member_no'] = $val['member_no'];
				$data[$val['member_id']]['member_nik'] = $val['member_nik'];
				$data[$val['member_id']]['member_name'] = $val['member_name'];
				$data[$val['member_id']]['division_name'] = $val['division_name'];
				$data[$val['member_id']]['part_name'] = $val['part_name'];
				$data[$val['member_id']]['member_company_specialities'] = $val['member_company_specialities'];
			}

			if ($debtcategory) {
				$data[$val['member_id']]['debtcategory'] = $debtcategory;
			}

			if ($debtsavings) {
				$data[$val['member_id']]['debtsavings'] = $debtsavings;
			}

			if ($debtcredits) {
				$data[$val['member_id']]['debtcredits'] = $debtcredits;
			}

			if ($debtstore) {
				$data[$val['member_id']]['debtstore'] = $debtstore;
			}
			if ($debtmembersavings) {
				$data[$val['member_id']]['debtmembersavings'] = $debtmembersavings;
			}
		}

		require_once ('tcpdf/config/tcpdf_config.php');
		require_once ('tcpdf/tcpdf.php');

		$pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetMargins(7, 7, 7, 7);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->SetFont('helvetica', 'B', 20);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 9);

		$base_url = base_url();
		$img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preferencecompany['logo_koperasi'] . "\" alt=\"\" width=\"800%\" height=\"800%\"/>";

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
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			        <td width=\"100%\"><div style=\"text-align: center; font-size:14px; font-weight:bold\">DAFTAR POTONG GAJI TEMPORARY</div></td>
			    </tr>
				<tr>
					<td width=\"100%\"><div style=\"text-align: center; font-size:12px;\">Per " . $sesi['start_date'] . " s.d " . $sesi['end_date'] . "</div></td>
				</tr>
			</table>
			<br>
			<br>
			<br>
			";

		$tbl .= "
			<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
				<tr>
					<td width=\"5%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">No Agt</td>
					<td width=\"15%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">NIK</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Nama</td>
					<td width=\"10%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Unit</td>
					<td width=\"15%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Divisi</td>
					<td width=\"15%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Bagian</td>
					<td width=\"20%\" style=\"font-size:13px; font-weight:bold; text-align: center;\">Total</td>
				</tr>
			</table>
			";

		foreach ($data as $key => $val) {
			$category_total = 0;
			if ($val['debtcategory']) {
				foreach ($val['debtcategory'] as $keyy => $vall) {
					$category_total += $vall['debt_amount'];
					$operator = $vall['operator'];
				}
			}

			$savings_total = 0;
			if ($val['debtsavings']) {
				foreach ($val['debtsavings'] as $keyy => $vall) {
					$savings_total += $vall['savings_cash_mutation_amount'];
				}
			}

			$credits_total = 0;
			if ($val['debtcredits']) {
				foreach ($val['debtcredits'] as $keyy => $vall) {
					$credits_total += $vall['credits_payment_amount'];
				}
			}

			$store_total = 0;
			if ($val['debtstore']) {
				foreach ($val['debtstore'] as $keyy => $vall) {
					$store_total += $vall['total_amount'];
				}
			}

			$simpanan_pokok = 0;
			if ($val['debtmembersavings']) {
				foreach ($val['debtmembersavings'] as $keyy => $vall) {
					$simpanan_pokok += $vall['principal_savings_amount'];
				}
			}

			$simpanan_wajib = 0;
			if ($val['debtmembersavings']) {
				foreach ($val['debtmembersavings'] as $keyy => $vall) {
					$simpanan_wajib += $vall['mandatory_savings_amount'];
				}
			}

			if($operator == '+'){
				$total =  $savings_total + $credits_total + $store_total + $simpanan_pokok + $simpanan_wajib + $category_total;
			}else{
				$total =  $savings_total + $credits_total + $store_total + $simpanan_pokok + $simpanan_wajib - $category_total;
			}
			


			$tbl .= "
				<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
					<tr>
						<td width=\"5%\" style=\"font-size:13px; text-align: center;\">" . $val['member_no'] . "</td>
						<td width=\"15%\" style=\"font-size:13px; text-align: center;\">" . $val['member_nik'] . "</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: left;\">" . $val['member_name'] . "</td>
						<td width=\"10%\" style=\"font-size:13px; text-align: center;\">" . $val['member_company_specialities'] . "</td>
						<td width=\"15%\" style=\"font-size:13px; text-align: left;\">" . $val['division_name'] . "</td>
						<td width=\"15%\" style=\"font-size:13px; text-align: left;\">" . $val['part_name'] . "</td>
						<td width=\"20%\" style=\"font-size:13px; text-align: right;\">" . number_format($total, 2) . "</td>
					</tr>
				</table>";
		}

		$pdf->writeHTML($tbl, true, false, false, '');

		ob_clean();
		$filename = 'Laporan Potong Gaji Rekap.pdf';
		$pdf->Output($filename, 'I');
	}

	public function exportDebtSimpleTemp($sesi)
	{
		$auth = $this->session->userdata('auth');
		$preferencecompany = $this->AcctDebtPrint_model->getPreferenceCompany();
		$coremember = $this->AcctDebtPrint_model->getCoreMember($sesi);
		$data = array();

		foreach ($coremember as $key => $val) {
			$total = 0;

			$debtcategory = $this->AcctDebtPrint_model->getMemberDebtCategory($sesi, $val['member_id']);
			$debtsavings = $this->AcctDebtPrint_model->getMemberDebtSavings($sesi, $val['member_id']);
			$debtcredits = $this->AcctDebtPrint_model->getMemberDebtCredits($sesi, $val['member_id']);
			$debtstore = $this->AcctDebtPrint_model->getMemberDebtStore($sesi, $val['member_id']);
			$debtmembersavings = $this->AcctDebtPrint_model->getMemberDebtMemberSavings($sesi, $val['member_id']);


			if ($debtcategory || $debtsavings || $debtcredits || $debtstore || $debtmembersavings) {

				$data[$val['member_id']]['member_no'] = $val['member_no'];
				$data[$val['member_id']]['member_nik'] = $val['member_nik'];
				$data[$val['member_id']]['member_name'] = $val['member_name'];
				$data[$val['member_id']]['division_name'] = $val['division_name'];
				$data[$val['member_id']]['part_name'] = $val['part_name'];
				$data[$val['member_id']]['member_company_specialities'] = $val['member_company_specialities'];
			}

			if ($debtcategory) {
				$data[$val['member_id']]['debtcategory'] = $debtcategory;
			}

			if ($debtsavings) {
				$data[$val['member_id']]['debtsavings'] = $debtsavings;
			}

			if ($debtcredits) {
				$data[$val['member_id']]['debtcredits'] = $debtcredits;
			}

			if ($debtstore) {
				$data[$val['member_id']]['debtstore'] = $debtstore;
			}

			if ($debtmembersavings) {
				$data[$val['member_id']]['debtmembersavings'] = $debtmembersavings;
			}
		}

		$this->load->library('Excel');

		$this->excel->getProperties()->setCreator("CST FISRT")
			->setLastModifiedBy("CST FISRT")
			->setTitle("Laporan Potong Gaji Rekap")
			->setSubject("")
			->setDescription("Laporan Potong Gaji Rekap")
			->setKeywords("Laporan Potong Gaji Rekap")
			->setCategory("Laporan Potong Gaji Rekap");

		$this->excel->setActiveSheetIndex(0);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

		$this->excel->getActiveSheet()->mergeCells("B1:H1");
		$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);

		$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Potong Gaji Rekap Per " . $sesi['start_date'] . " s.d. " . $sesi['end_date']);

		$this->excel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('D3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('E3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B3:H3' . ($row))->getFont()->setBold(true);

		$this->excel->getActiveSheet()->setCellValue('B3', "No Anggota");
		$this->excel->getActiveSheet()->setCellValue('C3', "NIK");
		$this->excel->getActiveSheet()->setCellValue('D3', "Nama");
		$this->excel->getActiveSheet()->setCellValue('E3', "Unit");
		$this->excel->getActiveSheet()->setCellValue('F3', "Divisi");
		$this->excel->getActiveSheet()->setCellValue('G3', "Bagian");
		$this->excel->getActiveSheet()->setCellValue('H3', "Total");

		$row = 4;
		foreach ($data as $key => $val) {
			$this->excel->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$this->excel->getActiveSheet()->getStyle('H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

			$category_total = 0;
			if ($val['debtcategory']) {
				foreach ($val['debtcategory'] as $keyy => $vall) {
					$category_total += $vall['debt_amount'];
				}
			}

			$savings_total = 0;
			if ($val['debtsavings']) {
				foreach ($val['debtsavings'] as $keyy => $vall) {
					$savings_total += $vall['savings_cash_mutation_amount'];
				}
			}

			$credits_total = 0;
			if ($val['debtcredits']) {
				foreach ($val['debtcredits'] as $keyy => $vall) {
					$credits_total += $vall['credits_payment_amount'];
				}
			}

			$store_total = 0;
			if ($val['debtstore']) {
				foreach ($val['debtstore'] as $keyy => $vall) {
					$store_total += $vall['total_amount'];
				}
			}
			$simpanan_pokok = 0;
			if ($val['debtmembersavings']) {
				foreach ($val['debtmembersavings'] as $keyy => $vall) {
					$simpanan_pokok += $vall['principal_savings_amount'];
				}
			}

			$simpanan_wajib = 0;
			if ($val['debtmembersavings']) {
				foreach ($val['debtmembersavings'] as $keyy => $vall) {
					$simpanan_wajib += $vall['mandatory_savings_amount'];
				}
			}


			$total = $category_total + $savings_total + $credits_total + $store_total + $simpanan_pokok + $simpanan_wajib;


			$this->excel->getActiveSheet()->setCellValueExplicit('B' . ($row), $val['member_no']);
			$this->excel->getActiveSheet()->setCellValue('C' . ($row), $val['member_nik']);
			$this->excel->getActiveSheet()->setCellValue('D' . ($row), $val['member_name']);
			$this->excel->getActiveSheet()->setCellValue('E' . ($row), $val['member_company_specialities']);
			$this->excel->getActiveSheet()->setCellValue('F' . ($row), $val['division_name']);
			$this->excel->getActiveSheet()->setCellValue('G' . ($row), $val['part_name']);
			$this->excel->getActiveSheet()->setCellValue('H' . ($row), number_format($total, 2));

			$row += 1;
		}

		$this->excel->getActiveSheet()->getStyle('B3:H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

		$filename = 'Laporan Potong Gaji Rekap.xls';
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = IOFactory::createWriter($this->excel, 'Excel5');
		ob_end_clean();
		$objWriter->save('php://output');
	}

	//simpan simp pokok potong gaji
	public function submitSalaryPrincipalSavings()
	{
		$auth = $this->session->userdata('auth');

		$salaryPrinsipalSavingsTemp = $this->CoreMember_model->getMemberPrincipalSavingsTemp();

		foreach ($salaryPrinsipalSavingsTemp as $key => $val) {
			
			 // Pastikan loop berjalan dengan benar
			// echo "<pre>";
			// echo print_r($val);
			// echo "</pre>";

			$username = $this->CoreMember_model->getUserName($auth['user_id']);

			$data = array(
				'member_id'								=> $val['member_id'],
				'branch_id'								=> $val['branch_id'],
				'member_name'							=> $val['member_name'],
				'mutation_id'							=> $val['mutation_id'],
				'member_address'						=> $val['member_address'],
				'city_id'								=> $val['city_id'],
				'kecamatan_id'							=> $val['kecamatan_id'],
				'kelurahan_id'							=> $val['kelurahan_id'],
				'member_character'						=> $val['member_character'],
				'member_principal_savings'				=> $val['member_principal_savings'],
				'last_balance' 							=> $val['last_balance'],
				'member_account_receivable_amount'		=> $val['member_account_receivable_amount'],
				'member_account_principal_debt'			=> $val['member_account_principal_debt'],
				'member_token_edit'						=> $val['member_token_edit'],
			);

			$data_member_update = array(
				'member_id'								=> $val['member_id'],
				'member_principal_savings'				=> $val['member_principal_savings'],
				'member_principal_savings_last_balance'	=> $val['last_balance'],
				'member_account_receivable_amount'		=> $val['member_account_receivable_amount'],
				'member_account_principal_debt'			=> $val['member_account_principal_debt'],
				'member_token_edit'						=> $val['member_token_edit'],
			);

			$total_cash_amount = $val['principal_savings_amount'];

			$member_token_edit = $this->CoreMember_model->getMemberTokenEdit($val['member_token_edit']);

			$this->CoreMember_model->updateCoreMember($data_member_update);

							$data_detail = array (
								'branch_id'						=> $auth['branch_id'],
								'member_id'						=> $val['member_id'],
								'mutation_id'					=> $val['mutation_id'],
								'transaction_date'				=> date('Y-m-d'),
								'principal_savings_amount'		=> $val['principal_savings_amount'],
								'operated_name'					=> $auth['username'],
								'savings_member_detail_token'	=> $val['member_token_edit'],
								'savings_member_detail_remark'	=> 'SETORAN SIMP POKOK POTONG GAJI ',
								'salary_status'					=> 1,
							); 

							// echo json_encode($data_detail);
							// exit;
							$this->CoreMember_model->insertAcctSavingsMemberDetail($data_detail);
							//ubah ke REAL
								$transaction_module_code 	= "AGT";

								$transaction_module_id 		= $this->CoreMember_model->getTransactionModuleID($transaction_module_code);
								$preferencecompany 			= $this->CoreMember_model->getPreferenceCompany();
								$coremember 				= $this->CoreMember_model->getCoreMember_Detail($val['member_id']);
									
								$journal_voucher_period 	= date("Ym", strtotime($coremember['member_register_date']));

								//-------------------------Jurnal Cabang----------------------------------------------------
								
								$data_journal_cabang = array(
									'branch_id'						=> $auth['branch_id'],
									'journal_voucher_period' 		=> $journal_voucher_period,
									'journal_voucher_date'			=> date('Y-m-d'),
									'journal_voucher_title'			=> 'MUTASI ANGGOTA POTONG GAJI '.$val['member_name'],
									'journal_voucher_description'	=> 'MUTASI ANGGOTA POTONG GAJI '.$val['member_name'],
									'journal_voucher_token'			=> $val['member_token_edit'].$auth['branch_id'],
									'transaction_module_id'			=> $transaction_module_id,
									'transaction_module_code'		=> $transaction_module_code,
									'transaction_journal_id' 		=> $val['member_id'],
									'transaction_journal_no' 		=> $val['member_no'],
									'created_id' 					=> $auth['user_id'],
									'created_on' 					=> date('Y-m-d H:i:s'),
								);
								
								$this->CoreMember_model->insertAcctJournalVoucher($data_journal_cabang);

								$journal_voucher_id 			= $this->CoreMember_model->getJournalVoucherID($auth['user_id']);

								$preferencecompany 				= $this->CoreMember_model->getPreferenceCompany();

								$account_id_default_status 	= $this->CoreMember_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_salary_payment_id'],
									'journal_voucher_description'	=> 'SETORAN SIMP POKOK POTONG GAJI '.$val['member_name'],
									'journal_voucher_amount'		=> $val['principal_savings_amount'],
									'journal_voucher_debit_amount'	=> $val['principal_savings_amount'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'created_id' 					=> $auth['user_id'],
									'journal_voucher_item_token'	=> $val['member_token_edit'].$preferencecompany['account_salary_payment_id'],
								);

								$this->CoreMember_model->insertAcctJournalVoucherItem($data_debet);

									$account_id = $this->CoreMember_model->getAccountID($preferencecompany['principal_savings_id']);

									$account_id_default_status = $this->CoreMember_model->getAccountIDDefaultStatus($account_id);

									$data_credit =array(
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $account_id,
										'journal_voucher_description'	=> 'SETORAN SIMP POKOK POTONG GAJI '.$val['member_name'],
										'journal_voucher_amount'		=> $val['principal_savings_amount'],
										'journal_voucher_credit_amount'	=> $val['principal_savings_amount'],
										'account_id_default_status'		=> $account_id_default_status,
										'account_id_status'				=> 1,
										'created_id' 					=> $auth['user_id'],
										'journal_voucher_item_token'	=> $val['member_token_edit'].$account_id,
									);

									$this->CoreMember_model->insertAcctJournalVoucherItem($data_credit);	

						$data_member = array(
							"acct_savings_member_detail_id" 	=> $val['acct_savings_member_detail_id'],
							"data_state" 						=> 1,
						);
						$this->CoreMember_model->updateDebtMemberSavingsTemp($data_member);
					
			
		}
		// endforeach

						$auth = $this->session->userdata('auth');
						$this->fungsi->set_log($auth['user_id'], $auth['username'],'1005','Application.CoreMember.processEditCoreMemberSavings',$auth['user_id'],'Edit  Member Savings');
						$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
									Tambah Simp Pokok Potong Gaji Sukses
								</div> ";

						$unique = $this->session->userdata('unique');
						$this->session->unset_userdata('coremembertokensalaryprincipal-'.$unique['unique']);
						$this->session->set_userdata('message',$msg);
						redirect('debt-print');

	}

	//hapus simp pokok potong gaji
	public function deleteSalaryPrincipal($acct_savings_member_detail_id)
	{
		$data_member = array(
			"data_state" 						=> 1,
		);

		$this->CoreMember_model->deleteDebtMemberSavingsTemp($acct_savings_member_detail_id,$data_member);

		$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
									Hapus Simp Pokok Potong Gaji Sukses
								</div> ";
		$this->session->set_userdata('message',$msg);
		redirect('debt-print');
	}

	//simpan simp wajib potong gaji
	public function submitSalaryMandatorySavings(){
		$auth 						= $this->session->userdata('auth');
		$username 					= $this->CoreMember_model->getUserName($auth['user_id']);
		$coremember 				= $this->CoreMember_model->getMemberMandatorySavingsTemp();
		$account_salary_id 			= $this->input->post('account_id', true);
		$mandatory_savings_total 	= 0;

		if(!$account_salary_id){
			$msg = "<div class='alert alert-danger alert-dismissable'> 
			<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
				No. Perkiraan Harus Diisi !
			</div> ";
			$this->session->set_userdata('message',$msg);
			redirect('debt-print');
			exit();
		}

		foreach($coremember as $key => $val){
			$data = array(
				'member_id'								=> $val['member_id'],
				'branch_id'								=> $auth['branch_id'],
				'member_name'							=> $val['member_name'],
				'member_address'						=> $val['member_address'],
				'city_id'								=> $val['city_id'],
				'kecamatan_id'							=> $val['kecamatan_id'],
				'kelurahan_id'							=> $val['kelurahan_id'],
				'member_character'						=> $val['member_character'],
				'member_mandatory_savings'				=> $val['member_mandatory_savings'],
				'member_mandatory_savings_last_balance'	=> $val['member_mandatory_savings_last_balance'],
				'member_token_edit'						=> $this->input->post('member_token_edit', true).$val['member_id'],
			);
			
			$total_cash_amount = $data['member_mandatory_savings'];

			$member_token_edit = $this->CoreMember_model->getMemberTokenEdit($data['member_token_edit']);
			
			if($member_token_edit->num_rows() == 0){
				//ubah ke real
				if($this->CoreMember_model->updateCoreMember($data)){
					if($data['member_mandatory_savings'] <> 0 || $data['member_mandatory_savings'] <> ''){
						$data_detail = array (
							'branch_id'						=> $auth['branch_id'],
							'member_id'						=> $val['member_id'],
							'mutation_id'					=> $this->input->post('mutation_id', true),
							'transaction_date'				=> date('Y-m-d'),
							'mandatory_savings_amount'		=> $val['member_mandatory_savings'],
							'operated_name'					=> $auth['username'],
							'savings_member_detail_remark'	=> $this->input->post('savings_member_detail_remark', true),
							'savings_member_detail_token'	=> $val['member_token_edit'],
							'salary_status'					=> 1,
							// 'salary_cut_type'				=> 2,

						);
						//real
						$this->CoreMember_model->insertAcctSavingsMemberDetail($data_detail);
					}
				}else{
				}
			} else {
				if($data['member_mandatory_savings'] <> 0 || $data['member_mandatory_savings'] <> ''){
					
					$data_detail = array (
						'branch_id'						=> $auth['branch_id'],
						'member_id'						=> $val['member_id'],
						'mutation_id'					=> $this->input->post('mutation_id', true),
						'transaction_date'				=> date('Y-m-d'),
						'mandatory_savings_amount'		=> $val['member_mandatory_savings'],
						'operated_name'					=> $auth['username'],
						'savings_member_detail_remark'	=> $this->input->post('savings_member_detail_remark', true),
						'savings_member_detail_token'	=> $val['member_token_edit'],
						'salary_status'					=> 1,
						// 'salary_cut_type'				=> 2,

					);

					$savings_member_detail_token = $this->CoreMember_model->getSavingsMemberDetailToken($data['member_token_edit']);

					if($savings_member_detail_token->num_rows() == 0){
						$this->CoreMember_model->insertAcctSavingsMemberDetail($data_detail);
					}
				}
			}
			$mandatory_savings_total += $val['member_mandatory_savings'];

			$data_member = array(
				"acct_savings_member_detail_id" 	=> $val['acct_savings_member_detail_id'],
				"data_state" 						=> 1,
			);
			$this->CoreMember_model->updateDebtMemberSavingsTemp($data_member);
		}

		//*JOURNAL----------------------------------------------------------------------------------------------------------------------
		if($mandatory_savings_total <> 0 || $mandatory_savings_total <> ''){
			$transaction_module_code 	= "AGT";

			$transaction_module_id 		= $this->CoreMember_model->getTransactionModuleID($transaction_module_code);
			$preferencecompany 			= $this->CoreMember_model->getPreferenceCompany();
			$coremember 				= $this->CoreMember_model->getCoreMember_Detail($data['member_id']);
				
			$journal_voucher_period 	= date("Ym", strtotime($coremember['member_register_date']));

			//-------------------------Jurnal Cabang----------------------------------------------------
			
			$data_journal_cabang = array(
				'branch_id'						=> $auth['branch_id'],
				'journal_voucher_period' 		=> $journal_voucher_period,
				'journal_voucher_date'			=> date('Y-m-d'),
				'journal_voucher_title'			=> 'MUTASI ANGGOTA POTONG GAJI ',
				'journal_voucher_description'	=> 'MUTASI ANGGOTA POTONG GAJI ',
				'journal_voucher_token'			=> $data['member_token_edit'].$auth['branch_id'],
				'transaction_module_id'			=> $transaction_module_id,
				'transaction_module_code'		=> $transaction_module_code,
				'transaction_journal_id' 		=> $coremember['member_id'],
				'transaction_journal_no' 		=> $coremember['member_no'],
				'created_id' 					=> $auth['user_id'],
				'created_on' 					=> date('Y-m-d H:i:s'),
			);

			if($this->CoreMember_model->insertAcctJournalVoucher($data_journal_cabang)){
				$journal_voucher_id 		= $this->CoreMember_model->getJournalVoucherID($auth['user_id']);
				$preferencecompany 			= $this->CoreMember_model->getPreferenceCompany();
				$account_id_default_status 	= $this->CoreMember_model->getAccountIDDefaultStatus($account_salary_id);

				$data_debet = array (
					'journal_voucher_id'			=> $journal_voucher_id,
					'account_id'					=> $account_salary_id,
					'journal_voucher_description'	=> 'SETORAN SIMP WAJIB POTONG GAJI ',
					'journal_voucher_amount'		=> $mandatory_savings_total,
					'journal_voucher_debit_amount'	=> $mandatory_savings_total,
					'account_id_default_status'		=> $account_id_default_status,
					'account_id_status'				=> 0,
					'created_id' 					=> $auth['user_id'],
					'journal_voucher_item_token'	=> $data['member_token_edit'].$account_salary_id,
				);

				$this->CoreMember_model->insertAcctJournalVoucherItem($data_debet);

				if($mandatory_savings_total <> 0 || $mandatory_savings_total <> ''){
					$account_id = $this->CoreMember_model->getAccountID($preferencecompany['mandatory_savings_id']);

					$account_id_default_status = $this->CoreMember_model->getAccountIDDefaultStatus($account_id);

					$data_credit =array(
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $account_id,
						'journal_voucher_description'	=> 'SETORAN POTONG GAJI ',
						'journal_voucher_amount'		=> $mandatory_savings_total,
						'journal_voucher_credit_amount'	=> $mandatory_savings_total,
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'created_id' 					=> $auth['user_id'],
						'journal_voucher_item_token'	=> $data['member_token_edit'].$account_id,
					);
					$this->CoreMember_model->insertAcctJournalVoucherItem($data_credit);	
				}
			}

			$auth = $this->session->userdata('auth');
			$this->fungsi->set_log($auth['user_id'], $auth['username'],'1005','Application.CoreMember.processEditCoreMemberSavings',$auth['user_id'],'Edit  Member Savings');
			$msg = "<div class='alert alert-success alert-dismissable'>  
					<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
						Tambah Simp Wajib Potong Gaji Sukses
					</div> ";

			$unique = $this->session->userdata('unique');
			$this->session->unset_userdata('coremembertokensalarymandatory-'.$unique['unique']);
			$this->session->set_userdata('message',$msg);
			redirect('debt-print');
		}else{
			$msg = "<div class='alert alert-danger alert-dismissable'> 
					<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
						Tambah Simp Wajib Potong Gaji Tidak Berhasil
					</div> ";
			$this->session->set_userdata('message',$msg);
			redirect('debt-print');
		}
	}

	//hapus simp wajib potong gaji
	public function deleteSalaryMandatory($acct_savings_member_detail_id)
	{	

		$member = $this->CoreMember_model->getSalaryMandatoryTempFirst($acct_savings_member_detail_id);

		// echo json_encode($member);
		// exit;

		$data_member = array(
			"member_id" 						=> $member['member_id'],
			"member_debet_preference" 			=> 1,
		);

		$this->CoreMember_model->updateCoreMember($data_member);

		$data_member = array(
			"data_state" 						=> 1,
		);

		$this->CoreMember_model->deleteDebtMemberSavingsTemp($acct_savings_member_detail_id,$data_member);

		$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
									Hapus Simp Wajib Potong Gaji Sukses
								</div> ";
		$this->session->set_userdata('message',$msg);
		redirect('debt-print');
	}

	//simpan tabungan potong gaji jurnal otomatis
	public function submitAcctSavingsSalaryMutation(){
		$auth 				= $this->session->userdata('auth');
		$token				= md5(rand());
		$acctsavingsaccount = $this->AcctSavingsSalaryMutation_model->getSavingsSalaryMutationTemp();

		// echo json_encode($acctsavingsaccount);
		// exit;

		foreach($acctsavingsaccount as $key => $val){
			$data = array(
				'savings_account_id'						=> $val['savings_account_id'],
				'mutation_id'								=> $val['mutation_id'],
				'savings_id'								=> $val['savings_id'],
				'member_id'									=> $val['member_id'],
				'branch_id'									=> $auth['branch_id'],
				'savings_cash_mutation_date'				=> $val['savings_cash_mutation_date'],
				'savings_cash_mutation_opening_balance'		=> $val['savings_cash_mutation_opening_balance'],
				'savings_cash_mutation_last_balance'		=> $val['savings_cash_mutation_last_balance'],
				'savings_cash_mutation_amount'				=> $val['savings_cash_mutation_amount'],
				'savings_cash_mutation_amount_adm'			=> 0,
				'savings_cash_mutation_remark'				=> '',
				'savings_cash_mutation_token'				=> $token.$val['savings_account_id'],
				'operated_name'								=> $auth['username'],
				'salary_payment_status'						=> 1,
				'created_id'								=> $auth['user_id'],
				'created_on'								=> date('Y-m-d H:i:s'),
			);

			$data_update = array(
				'savings_cash_mutation_id'					=> $val['savings_cash_mutation_id'],
			);

			$savings_cash_mutation_token 	= $this->AcctSavingsSalaryMutation_model->getSavingsCashMutationToken($data['savings_cash_mutation_token']);

			$transaction_module_code 		= "TTAB";
			$transaction_module_id 			= $this->AcctSavingsSalaryMutation_model->getTransactionModuleID($transaction_module_code);
			
			$journal_voucher_period 		= date("Ym", strtotime($data['savings_cash_mutation_date']));
			
			if($savings_cash_mutation_token->num_rows()==0){
				if($this->AcctSavingsSalaryMutation_model->insertAcctSavingsSalaryMutation($data)){
					$acctsavingscash_last 			= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutation_Last($data['created_id']);

					$data_journal = array(
						'branch_id'							=> $auth['branch_id'],
						'journal_voucher_period' 			=> $journal_voucher_period,
						'journal_voucher_date'				=> date('Y-m-d'),
						'journal_voucher_title'				=> 'MUTASI POTONG GAJI '.$acctsavingscash_last['member_name'],
						'journal_voucher_description'		=> 'MUTASI POTONG GAJI '.$acctsavingscash_last['member_name'],
						'journal_voucher_token'				=> $data['savings_cash_mutation_token'],
						'transaction_module_id'				=> $transaction_module_id,
						'transaction_module_code'			=> $transaction_module_code,
						'transaction_journal_id' 			=> $acctsavingscash_last['savings_cash_mutation_id'],
						'transaction_journal_no' 			=> $acctsavingscash_last['savings_account_no'],
						'created_id' 						=> $data['created_id'],
						'created_on' 						=> $data['created_on'],
					);
					
					$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucher($data_journal);

					$journal_voucher_id 					= $this->AcctSavingsSalaryMutation_model->getJournalVoucherID($data['created_id']);

					$preferencecompany 						= $this->AcctSavingsSalaryMutation_model->getPreferenceCompany();

					if($data['mutation_id'] == $preferencecompany['cash_deposit_id'] || $data['mutation_id'] == 14){

						//hardcode
						if($val['member_division_name'] == 'PABRIK' or $val['member_division_name'] == 'MARKETING' or $val['member_division_name'] == 'MARKETING STAFF'){
							
							$account_id_default_status_debit    = $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus(27);
							$account_preference 				= 27;
						
						}else if($val['member_division_name'] == 'APOTIK DAN PBF ME' or $val['member_division_name'] == 'KOPERASI'){
							
							$account_id_default_status_debit 	= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus(9);
							$account_preference 				= 9;
						}else{
							
							$account_id_default_status_debit 	= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus(9);
							$account_preference 				= 9;
						}

						// $account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);


						$data_debet = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_preference,
							// 'account_id'					=> $preferencecompany['account_salary_payment_id'],
							'journal_voucher_description'	=> 'SETORAN POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status_debit,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debet);

						$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

						$data_credit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_id,
							'journal_voucher_description'	=> 'SETORAN TABUNGAN POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);

						if($data['savings_cash_mutation_amount_adm'] > 0){
							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> 'STR1'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);

							$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

							$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_mutation_adm_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> 'STR2'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					} else if($data['mutation_id'] == 2){
						$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

						$data_debit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_id,
							'journal_voucher_description'	=> 'PENARIKAN POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debit);

						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_salary_payment_id'],
							'journal_voucher_description'	=> 'PENARIKAN POTONG GAJI '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);

						if($data['savings_cash_mutation_amount_adm'] > 0){
							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);

							$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

							$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_mutation_adm_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
						}

					} else if($data['mutation_id'] == 3){
						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

						$data_debet = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_salary_payment_id'],
							'journal_voucher_description'	=> 'KOREKSI KREDIT '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debet);

						$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

						$data_credit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_id,
							'journal_voucher_description'	=> 'KOREKSI KREDIT '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);

						if($data['savings_cash_mutation_amount_adm'] > 0){
							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);

							$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

							$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_mutation_adm_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
						}

					} else if($data['mutation_id'] == 4){
						$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

						$data_debit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_id,
							'journal_voucher_description'	=> 'KOREKSI DEBET '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debit);

						$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_salary_payment_id'],
							'journal_voucher_description'	=> 'KOREKSI DEBET '.$acctsavingscash_last['member_name'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
							'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);

						if($data['savings_cash_mutation_amount_adm'] > 0){
							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);

							$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

							$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_mutation_adm_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					} else {
						if($this->AcctSavingsSalaryMutation_model->closedAcctSavingsAccount($data['savings_account_id'])){
							$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

							$data_debit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> 'TUTUP REKENING '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debit);

							$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

							$data_credit = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_salary_payment_id'],
								'journal_voucher_description'	=> 'TUTUP REKENING '.$acctsavingscash_last['member_name'],
								'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
								'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
								'created_id' 					=> $auth['user_id']
							);

							$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);

							if($data['savings_cash_mutation_amount_adm'] > 0){
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_cash_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
									'created_id' 					=> $auth['user_id']
								);

								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);

								$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

								$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_mutation_adm_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
									'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
									'created_id' 					=> $auth['user_id']
								);

								$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}
						
					}

					$memberaccountdebt = $this->AcctSavingsSalaryMutation_model->getCoreMemberAccountReceivableAmount($data['member_id']);

					$member_account_receivable_amount = $memberaccountdebt['member_account_receivable_amount'] + $data['savings_cash_mutation_amount'];

					$member_account_savings_debt 	= $memberaccountdebt['member_account_savings_debt'] + $data['savings_cash_mutation_amount'];

					$data_member = array(
						"member_id" 						=> $data['member_id'],
						"member_account_receivable_amount" 	=> $member_account_receivable_amount,
						"member_account_savings_debt" 		=> $member_account_savings_debt,
					);

					$this->AcctSavingsSalaryMutation_model->updateCoreMember($data_member);

					$data_mutation = array(
						"savings_cash_mutation_id"			=> $data_update['savings_cash_mutation_id'],
						"data_state" 						=> 1,
					);

					$this->AcctSavingsSalaryMutation_model->updateSavingsSalaryMutation($data_mutation);
					
				}else{
				}
			} else {
				$acctsavingscash_last 			= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutation_Last($data['created_id']);
				
				$data_journal = array(
					'branch_id'							=> $auth['branch_id'],
					'journal_voucher_period' 			=> $journal_voucher_period,
					'journal_voucher_date'				=> date('Y-m-d'),
					'journal_voucher_title'				=> 'MUTASI POTONG GAJI '.$acctsavingscash_last['member_name'],
					'journal_voucher_description'		=> 'MUTASI POTONG GAJI '.$acctsavingscash_last['member_name'],
					'journal_voucher_token'				=> $data['savings_cash_mutation_token'],
					'transaction_module_id'				=> $transaction_module_id,
					'transaction_module_code'			=> $transaction_module_code,
					'transaction_journal_id' 			=> $acctsavingscash_last['savings_cash_mutation_id'],
					'transaction_journal_no' 			=> $acctsavingscash_last['savings_account_no'],
					'created_id' 						=> $data['created_id'],
					'created_on' 						=> $data['created_on'],
				);

				$journal_voucher_token 	= $this->AcctSavingsSalaryMutation_model->getJournalVoucherToken($data_journal['journal_voucher_token']);

				if($journal_voucher_token->num_rows()== 0){
					$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucher($data_journal);
				}

				$journal_voucher_id 	= $this->AcctSavingsSalaryMutation_model->getJournalVoucherID($data['created_id']);

				$preferencecompany 		= $this->AcctSavingsSalaryMutation_model->getPreferenceCompany();

				if($data['mutation_id'] == $preferencecompany['cash_deposit_id']){
					$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

					$data_debet = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_salary_payment_id'],
						'journal_voucher_description'	=> 'SETORAN TABUNGAN POTONG GAJI '.$acctsavingscash_last['member_name'],
						'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
						'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 0,
						'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
						'created_id' 					=> $auth['user_id']
					);

					$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debet);
					}

					$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

					$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

					$data_credit = array(
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $account_id,
						'journal_voucher_description'	=> 'SETORAN TABUNGAN POTONG GAJI '.$acctsavingscash_last['member_name'],
						'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
						'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
						'created_id' 					=> $auth['user_id']
					);

					$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);
					}

					if($data['savings_cash_mutation_amount_adm'] > 0){

						$data_debet = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_cash_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
							'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> 'STR1'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
							'created_id' 					=> $auth['user_id']
						);

						$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);
						}

						$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

						$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

						$data_credit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_mutation_adm_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
							'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> 'STR2'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
							'created_id' 					=> $auth['user_id']
						);

						$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}

				} else {	
					$account_id 						= $this->AcctSavingsSalaryMutation_model->getAccountID($data['savings_id']);

					$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($account_id);

					$data_debit =array(
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $account_id,
						'journal_voucher_description'	=> 'PENARIKAN POTONG GAJI '.$acctsavingscash_last['member_name'],
						'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
						'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 0,
						'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$account_id,
						'created_id' 					=> $auth['user_id']
					);

					$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_debit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_debit);
					}

					$account_id_default_status 			= $this->AcctSavingsSalaryMutation_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

					$data_credit = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_salary_payment_id'],
						'journal_voucher_description'	=> 'PENARIKAN POTONG GAJI '.$acctsavingscash_last['member_name'],
						'journal_voucher_amount'		=> $data['savings_cash_mutation_amount'],
						'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
						'created_id' 					=> $auth['user_id']
					);

					$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctSavingsSalaryMutation_model->insertAcctJournalVoucherItem($data_credit);
					}

					if($data['savings_cash_mutation_amount_adm'] > 0){

						$data_debet = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_cash_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
							'journal_voucher_debit_amount'	=> $data['savings_cash_mutation_amount_adm'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$data['savings_cash_mutation_amount_adm'],
							'created_id' 					=> $auth['user_id']
						);

						$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_debet);
						}

						$preferencecompany = $this->AcctSavingsAccount_model->getPreferenceCompany();

						$account_id_default_status = $this->AcctSavingsAccount_model->getAccountIDDefaultStatus($preferencecompany['account_mutation_adm_id']);

						$data_credit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_mutation_adm_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data['savings_cash_mutation_amount_adm'],
							'journal_voucher_credit_amount'	=> $data['savings_cash_mutation_amount_adm'],
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> 'PNR'.$data['savings_cash_mutation_token'].$preferencecompany['account_mutation_adm_id'],
							'created_id' 					=> $auth['user_id']
						);

						$journal_voucher_item_token 		= $this->AcctSavingsSalaryMutation_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctSavingsAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}
				}

				
				$memberaccountdebt = $this->AcctSavingsSalaryMutation_model->getCoreMemberAccountReceivableAmount($data['member_id']);

				$member_account_receivable_amount = $memberaccountdebt['member_account_receivable_amount'] + $data['savings_cash_mutation_amount'];

				$member_account_savings_debt 	= $memberaccountdebt['member_account_savings_debt'] + $data['savings_cash_mutation_amount'];

				$data_member = array(
					"member_id" 						=> $data['member_id'],
					"member_account_receivable_amount" 	=> $member_account_receivable_amount,
					"member_account_savings_debt" 		=> $member_account_savings_debt,
				);

				$this->AcctSavingsSalaryMutation_model->updateCoreMember($data_member);

			}
		}
				
		$auth = $this->session->userdata('auth');
		$msg = "<div class='alert alert-success alert-dismissable'>  
				<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
					Tambah Data Mutasi Simpanan Sukses
				</div> ";
		$sesi = $this->session->userdata('unique');
		$this->session->unset_userdata('addacctsavingscashmutation-'.$sesi['unique']);
		$this->session->unset_userdata('acctsavingscashmutationtoken-'.$sesi['unique']);
		$this->session->set_userdata('message',$msg);
		redirect('debt-print');
		$this->printNoteAcctSavingsSalaryMutationProcess($token);
	}

	//hapus tabungan potong gaji jurnal otomatis
	public function deleteAcctSavingsSalaryMutation($savings_cash_mutation_id)
	{
		$data_member = array(
			"data_state" 						=> 1,
		);
		$this->CoreMember_model->deleteDebtMemberSavingsTemp($savings_cash_mutation_id,$data_member);

		$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
									Hapus Tabungan Potong Gaji Sukses
								</div> ";
		$this->session->set_userdata('message',$msg);
		redirect('debt-print');
	}

	public function slidingrate($id){
		$credistaccount					= $this->AcctCreditAccount_model->getCreditsAccount_Detail($id);

		$total_credits_account 			= $credistaccount['credits_account_amount'];
		$credits_account_interest 		= $credistaccount['credits_account_interest'];
		$credits_account_period 		= $credistaccount['credits_account_period'];

		$installment_pattern			= array();
		$opening_balance				= $total_credits_account;

		for($i=1; $i<=$credits_account_period; $i++){
			
			if($credistaccount['credits_payment_period'] == 2){
				$a = $i * 7;

				$tanggal_angsuran 								= date('d-m-Y', strtotime("+".$a." days", strtotime($credistaccount['credits_account_date'])));

			} else {

				$tanggal_angsuran 								= date('d-m-Y', strtotime("+".$i." months", strtotime($credistaccount['credits_account_date'])));
			}
			
			$angsuran_pokok									= $credistaccount['credits_account_amount']/$credits_account_period;				

			$angsuran_margin								= $opening_balance*$credits_account_interest/100;				

			$angsuran 										= $angsuran_pokok + $angsuran_margin;

			$last_balance 									= $opening_balance - $angsuran_pokok;

			$installment_pattern[$i]['opening_balance']		= $opening_balance;
			$installment_pattern[$i]['ke'] 					= $i;
			$installment_pattern[$i]['tanggal_angsuran'] 	= $tanggal_angsuran;
			$installment_pattern[$i]['angsuran'] 			= $angsuran;
			$installment_pattern[$i]['angsuran_pokok']		= $angsuran_pokok;
			$installment_pattern[$i]['angsuran_bunga'] 		= $angsuran_margin;
			$installment_pattern[$i]['last_balance'] 		= $last_balance;
			
			$opening_balance 								= $last_balance;
		}
		
		return $installment_pattern;
		
	}

	public function anuitas($id){
		$creditsaccount 	= $this->AcctCreditAccount_model->getCreditsAccount_Detail($id);

		$pinjaman 	= $creditsaccount['credits_account_amount'];
		$bunga 		= $creditsaccount['credits_account_interest'] / 100;
		$period 	= $creditsaccount['credits_account_period'];



		$bungaA 		= pow((1 + $bunga), $period);
		$bungaB 		= pow((1 + $bunga), $period) - 1;
		$bAnuitas 		= ($bungaA / $bungaB);
		$totangsuran 	= round(($pinjaman*($bunga))+$pinjaman/$period);
		$rate			= $this->rate3($period, $totangsuran, $pinjaman);


		$sisapinjaman = $pinjaman;
		for ($i=1; $i <= $period ; $i++) {

			if($creditsaccount['credits_payment_period'] == 1){
				$tanggal_angsuran 	= date('d-m-Y', strtotime("+".$i." months", strtotime($creditsaccount['credits_account_date']))); 
			} else {
				$a = $i * 7;

				$tanggal_angsuran 	= date('d-m-Y', strtotime("+".$a." days", strtotime($creditsaccount['credits_account_date']))); 
			}
			
			$angsuranbunga 		= $sisapinjaman * $rate;
			$angsuranpokok 		= $totangsuran - $angsuranbunga;
			$sisapokok 			= $sisapinjaman - $angsuranpokok;

			$pola[$i]['ke']					= $i;
			$pola[$i]['tanggal_angsuran']	= $tanggal_angsuran;
			$pola[$i]['opening_balance']	= $sisapinjaman;
			$pola[$i]['angsuran']			= $totangsuran;
			$pola[$i]['angsuran_pokok']		= $angsuranpokok;
			$pola[$i]['angsuran_bunga']		= $angsuranbunga;
			$pola[$i]['last_balance']		= $sisapokok;

			$sisapinjaman = $sisapinjaman - $angsuranpokok;
		}
		return $pola;
	}

	public function printNoteAcctSavingsSalaryMutationProcess($token){
		$auth 						= $this->session->userdata('auth');
		$acctsavingscashmutation	= $this->AcctSavingsSalaryMutation_model->getAcctSavingsSalaryMutationByToken($token);
		$preferencecompany 			= $this->AcctSavingsSalaryMutation_model->getPreferenceCompany();

		require_once('tcpdf/config/tcpdf_config.php');
		require_once('tcpdf/tcpdf.php');

		$pdf = new tcpdf('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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

		$pdf->SetFont('helvetica', '', 12);

		// -----------------------------------------------------------------------------
		$base_url = base_url();
		
		$no = 1;
		foreach($acctsavingscashmutation as $key => $val){
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
				<tr>
					<td rowspan=\"2\" width=\"20%\">".$img."</td>
					<td width=\"40%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN TABUNGAN POTONG GAJI</div></td>
				</tr>
				<tr>
					<td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
				</tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			

			$tbl1 = "
			Telah diterima dari :
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: ".$val['member_name']."</div></td>
				</tr>
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">Bagian</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: ".$val['division_name']."</div></td>
				</tr>
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">Jenis Tabungan</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: ".$val['savings_name']."</div></td>
				</tr>
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">No. Rekening</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: ".$val['savings_account_no']."</div></td>
				</tr>
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: ".numtotxt($val['savings_cash_mutation_amount'])."</div></td>
				</tr>
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: SETORAN TABUNGAN POTONG GAJI</div></td>
				</tr>
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($val['savings_cash_mutation_amount'], 2)."</div></td>
				</tr>			
			</table>";

			$tbl2 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
				<tr>
					<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
					<td width=\"20%\"><div style=\"text-align: center;\"></div></td>
					<td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctSavingsSalaryMutation_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
				</tr>
				<br>
				<br>
				<br>
				<tr>
					<td width=\"30%\"><div style=\"text-align: center;\">".$paraf."</div></td>
					<td width=\"20%\"><div style=\"text-align: center;\"></div></td>
					<td width=\"30%\"><div style=\"text-align: center;\">".$preferencecompany['signature_name']."</div></td>
				</tr>				
			</table>
			";

			if($no % 3 == 0){
				$tbl2 .= "
					<br pagebreak=\"true\"/>
				";
			}

			$no++;

			$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');
		}



		ob_clean();

		$js ='';
		// -----------------------------------------------------------------------------
		
		$filename = 'Kwitansi_'.$keterangan.'_'.$acctsavingscashmutation['member_name'].'.pdf';

		$js .= 'print(true);';

		$pdf->IncludeJS($js);
		
		$pdf->Output($filename, 'I');

		//============================================================+
		// END OF FILE
		//============================================================+
	}

	//simpan angs potong gaji
	public function submitAcctSalaryPayments()
	{
			$auth 				= $this->session->userdata('auth');    
			$unique 			= $this->session->userdata('unique');
			$acctcreditspayments = $this->AcctSalaryPayment_model->getAcctCreditsPaymentsTemp();

			foreach($acctcreditspayments as $key => $val){
				$accountcredit	= $this->AcctCreditAccount_model->getDetailByID($val['credits_account_id']);

				 // Pastikan loop berjalan dengan benar
				// echo "<pre>";
				// echo print_r($val);
				// echo "</pre>";

				$data = array(
					'branch_id'									=> $auth['branch_id'],
					'member_id'									=> $val['member_id'],
					'salary_payment_status'						=> 1,
					'credits_id'								=> $val['credits_id'],
					'credits_account_id'						=> $val['credits_account_id'],
					'credits_account_serial'					=> $val['credits_account_serial'],
					'credits_payment_date'						=> $val['credits_payment_date'],
					'credits_payment_amount'					=> $val['credits_account_payment_amount'],
					'credits_payment_principal'					=> $val['credits_payment_principal'],
					'credits_payment_interest'					=> $val['credits_payment_interest'],
					'credits_principal_opening_balance'			=> $val['credits_account_last_balance'],
					'credits_principal_last_balance'			=> $val['credits_account_last_balance'] - $val['credits_payment_principal'],
					'credits_interest_opening_balance'			=> $val['credits_account_interest_last_balance'],
					'credits_interest_last_balance'				=> $val['credits_account_interest_last_balance'] + $val['credits_payment_interest'],
					'credits_payment_fine'						=> 0,
					'credits_account_payment_date'				=> $val['credits_account_payment_date'],
					'credits_payment_to'						=> $val['credits_payment_to'],
					'credits_payment_day_of_delay'				=> $val['credits_payment_day_of_delay'],
					'credits_payment_token'						=> $val['credits_payment_token'].$val['credits_account_id'],
					'created_id'								=> $auth['user_id'],
					'updated_id'								=> $auth['user_id'],
					'created_on'								=> date('Y-m-d H:i:s'),
				);

				$acctcreditsaccount 	= $this->AcctCreditAccount_model->getCreditsAccount_Detail2($val['credits_account_id']);
				$credits_account_status = 0;

				if($payment_type_id == 4){
					if($data['credits_principal_last_balance'] <= 0){
						$credits_account_status = 1;
					}
				}else{
					if($angsuran_ke == $total_angsuran){
						$credits_account_status = 1;
					}
				}

				$transaction_module_code 			= 'ANGS';
				$transaction_module_id 				= $this->AcctCreditAccount_model->getTransactionModuleID($transaction_module_code);
				$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
	
				$journal_voucher_period 			= date("Ym", strtotime($data['credits_payment_date']));
				
				$credits_payment_token 				= $this->AcctSalaryPayment_model->getCreditsPaymentToken($data['credits_payment_token']);
				

//!PEMBATAS------------------------------------------------------------------------------------------------------------------------------------

				// if($credits_payment_token->num_rows() == 0){
					//ubah ke real
					// if($this->AcctSalaryPayment_model->insert($data)){
						$this->AcctSalaryPayment_model->insert($data);
						$updatedata = array(
							"credits_account_last_balance" 					=> $data['credits_principal_last_balance'],
							"credits_account_last_payment_date"				=> $data['credits_payment_date'],
							"credits_account_payment_date"					=> $data['credits_payment_date'],
							"credits_account_payment_to"					=> $data['credits_payment_to'],
							"credits_account_interest_last_balance"			=> $data['credits_interest_last_balance'],
							"credits_account_status"						=> $credits_account_status,
							"credits_account_accumulated_fines"				=> 0,

						);
						//update
						$this->AcctCreditAccount_model->updatedata($data['credits_account_id'],$updatedata);

						$AcctSalaryPayment_last 							= $this->AcctSalaryPayment_model->AcctSalaryPaymentLast($data['created_id']);
						
						if($data['credits_id'] == 2 or $data['credits_id'] == 22 or $data['credits_id'] == 23){
//--------------------------masuk minimarket
							$data_journal = array(
								'company_id'                    => 1,
								'journal_voucher_status'        => 1,
								'journal_voucher_description'   => 'ANGSURAN VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
								'journal_voucher_title'         => 'ANGSURAN VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
								'transaction_module_id'         => $transaction_module_id,
								'transaction_module_code'       => $transaction_module_code,
								'journal_voucher_date'          => date('Y-m-d'),
								'transaction_journal_no'        => $AcctSalaryPayment_last['credits_account_serial'],
								'journal_voucher_period'        => date("Ym", strtotime($data['credits_payment_date'])),
								'updated_id'                    => $data['created_id'],
								'created_id'                    => $data['created_id']
							);
						
							$this->AcctCreditAccount_model->insertAcctJournalVoucherMinimarket($data_journal);
							
							$journal_voucher_id 				= $this->AcctCreditAccount_model->getJournalVoucherIDMinimarket($data['created_id']);

							$account_salary_payment_id 			= $preferencecompany['account_salary_payment_id'];
							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_salary_payment_id);

							$data_debet = array (
								'company_id'                    => 1,
								'journal_voucher_id'            => $journal_voucher_id,
								'account_id'                    => $account_salary_payment_id,
								'journal_voucher_amount'        => $data['credits_payment_amount'],
								'journal_voucher_debit_amount'  => $data['credits_payment_amount'],
								'account_id_default_status'     => $account_id_default_status,
								'account_id_status'             => 0,
								'updated_id'                    => $auth['user_id'],
								'created_id'                    => $auth['user_id']
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_debet);

							$receivable_account_id 				= $this->AcctCreditAccount_model->getReceivableAccountID($data['credits_id']);

							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($receivable_account_id);

							$data_credit = array (
								'company_id'                    => 1,
								'journal_voucher_id'            => $journal_voucher_id,
								'account_id'                    => $receivable_account_id,
								'journal_voucher_amount'        => $data['credits_payment_principal'],
								'journal_voucher_credit_amount' => $data['credits_payment_principal'],
								'account_id_default_status'     => $account_id_default_status,
								'account_id_status'             => 1,
								'updated_id'                    => $auth['user_id'],
								'created_id'                    => $auth['user_id']
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);

							$account_interest_id				= $preferencecompany['account_interest_id'];
							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_interest_id);

							$data_credit =array(
								'company_id'                    => 1,
								'journal_voucher_id'            => $journal_voucher_id,
								'account_id'                    => $account_interest_id,
								'journal_voucher_amount'        => $data['credits_payment_interest'],
								'journal_voucher_credit_amount' => $data['credits_payment_interest'],
								'account_id_default_status'     => $account_id_default_status,
								'account_id_status'             => 1,
								'updated_id'                    => $auth['user_id'],
								'created_id'                    => $auth['user_id']
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);

							if($data['credits_payment_fine'] > 0){
								$account_credits_payment_fine		= $preferencecompany['account_credits_payment_fine'];
								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($account_credits_payment_fine);

								$data_credit =array(
									'company_id'                    => 1,
									'journal_voucher_id'            => $journal_voucher_id,
									'account_id'                    => $account_credits_payment_fine,
									'journal_voucher_amount'        => $data['credits_payment_fine'],
									'journal_voucher_credit_amount' => $data['credits_payment_fine'],
									'account_id_default_status'     => $account_id_default_status,
									'account_id_status'             => 1,
									'updated_id'                    => $auth['user_id'],
									'created_id'                    => $auth['user_id']
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);
							}
//------------------------end masuk minimarket
							
						}else{
//------------------------masuk simpin
							$data_journal = array(
								'branch_id'						=> $auth['branch_id'],
								'journal_voucher_period' 		=> $journal_voucher_period,
								'journal_voucher_date'			=> date('Y-m-d'),
								'journal_voucher_title'			=> 'ANGSURAN VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
								'journal_voucher_description'	=> 'ANGSURAN VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
								'journal_voucher_token'			=> $data['credits_payment_token'],
								'transaction_module_id'			=> $transaction_module_id,
								'transaction_module_code'		=> $transaction_module_code,
								'transaction_journal_id' 		=> $AcctSalaryPayment_last['credits_payment_id'],
								'transaction_journal_no' 		=> $AcctSalaryPayment_last['credits_account_serial'],
								'created_id' 					=> $data['created_id'],
								'created_on' 					=> $data['created_on'],
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);

							$journal_voucher_id 				= $this->AcctCreditAccount_model->getJournalVoucherID($data['created_id']);

							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_salary_payment_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['credits_payment_amount'],
								'journal_voucher_debit_amount'	=> $data['credits_payment_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['credits_payment_token'].$preferencecompany['account_salary_payment_id'],
								'created_id' 					=> $auth['user_id'],
							);

							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);

							$receivable_account_id 				= $this->AcctCreditAccount_model->getReceivableAccountID($data['credits_id']);

							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($receivable_account_id);

							$data_credit = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $receivable_account_id,
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['credits_payment_principal'],
								'journal_voucher_credit_amount'	=> $data['credits_payment_principal'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['credits_payment_token'].$receivable_account_id,
								'created_id' 					=> $auth['user_id'],
							);

							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);

							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_interest_id']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_interest_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['credits_payment_interest'],
								'journal_voucher_credit_amount'	=> $data['credits_payment_interest'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> $data['credits_payment_token'].$preferencecompany['account_interest_id'],
								'created_id' 					=> $auth['user_id'],
							);

							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);

							if($data['credits_payment_fine'] > 0){

								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_credits_payment_fine']);

								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_credits_payment_fine'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['credits_payment_fine'],
									'journal_voucher_credit_amount'	=> $data['credits_payment_fine'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> $data['credits_payment_token'].$preferencecompany['account_credits_payment_fine'],
									'created_id' 					=> $auth['user_id'],
								);

								$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
//------------------------end masuk simpin

						}
						
						$memberaccountdebt = $this->AcctSalaryPayment_model->getCoreMemberAccountReceivableAmount($data['member_id']);

						$member_account_receivable_amount = $memberaccountdebt['member_account_receivable_amount'] + $data['credits_payment_amount'];

						if($data['credits_id'] == 2 or $data['credits_id'] == 22 or $data['credits_id'] == 23){
							$member_account_credits_debt 		= $memberaccountdebt['member_account_credits_debt'];
							$member_account_credits_store_debt 	= $memberaccountdebt['member_account_credits_store_debt'] + $data['credits_payment_amount'];
						}else{
							$member_account_credits_debt 		= $memberaccountdebt['member_account_credits_debt'] + $data['credits_payment_amount'];
							$member_account_credits_store_debt 	= $memberaccountdebt['member_account_credits_store_debt'];
						}

						$data_member = array(
							"member_id" 						=> $data['member_id'],
							"member_account_receivable_amount" 	=> $member_account_receivable_amount,
							"member_account_credits_debt" 		=> $member_account_credits_debt,
							"member_account_credits_store_debt" => $member_account_credits_store_debt,
						);
						$this->AcctSalaryPayment_model->updateCoreMember($data_member);
			}

			$auth = $this->session->userdata('auth');
			$msg = "<div class='alert alert-success alert-dismissable'>  
					<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
						Tambah Data Pembayaran Pinjaman Sukses
					</div> ";
			$sesi = $this->session->userdata('unique');
			$this->session->unset_userdata('addAcctSalaryPayment-'.$sesi['unique']);
			$this->session->unset_userdata('acctcreditspaymentcashtoken-'.$sesi['unique']);
			$this->session->set_userdata('message',$msg);
			redirect('debt-print');
			$this->printNoteSalaryPaymentProcess($this->input->post('credits_payment_token', true));
//------------------------end 
	}

	public function deleteAcctSalaryPayments($credits_payment_id)
	{
		$creditpaymenttemp = $this->AcctSalaryPayment_model->getAcctCreditsPaymentsTempFirst($credits_payment_id);

		// echo json_encode($creditpaymenttemp['credits_account_id']);
		// exit;

		$credit_account = array(
			"payment_preference_id"		=> 1,
		);
		//update
		$this->AcctCreditAccount_model->updatedata($credit_account,$creditpaymenttemp['credits_account_id']);
		// echo json_encode($query);
		// exit;
		$credit_payment_temp = array(
			"data_state"				=> 1,
		);	

		//update
		$this->AcctSalaryPayment_model->deleteSalaryPaymentTemp($creditpaymenttemp['credits_payment_id'],$credit_payment_temp);
	
		$msg = "<div class='alert alert-success alert-dismissable'>  
		<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
			Hapus Angsuran Potong Gaji Sukses
		</div> ";
		$this->session->set_userdata('message',$msg);
		redirect('debt-print');
	}

	public function submitAcctDebt()
	{
			$auth 				= $this->session->userdata('auth');
			$preferencecompany 	= $this->AcctDebt_model->getPreferenceCompany();
			$acctdebttemp 		= $this->AcctDebt_model->getAcctDebtTemporary();

			foreach($acctdebttemp as $key => $val){
				$total 					= 0;
	
				$data = array (
					'member_id' 		=> $val['member_id'],
					'debt_category_id' 	=> $val['debt_category_id'],
					'debt_date' 		=> $val['debt_date'],
					// 'debt_no' 			=> $val['debt_no'],
					'debt_amount' 		=> $val['debt_amount'],
					'debt_remark' 		=> $val['debt_remark'],
					'debt_token' 		=> md5(rand()).$val['debt_id'],
					'created_on'		=> date('Y-m-d H:i:s'),
					'created_id'		=> $auth['user_id']
				);

				$data_update = array (
					'debt_id' 		=> $val['debt_id'],
				);

				if($this->AcctDebt_model->insertAcctDebt($data)){
					$coremember 	= $this->AcctDebt_model->getCoreMemberDetail($data['member_id']);
					$debtcategory 	= $this->AcctDebt_model->getAcctDebtCategoryDetail($data['debt_category_id']); 
					$other_amount	= $coremember['member_account_other_debt'] + $data['debt_amount'];
					$total			= $coremember['member_account_receivable_amount'] + $data['debt_amount'];

					$dataanggota 	= array(
						'member_id' 						=> $data['member_id'],
						'member_account_other_debt' 		=> $other_amount,
						'member_account_receivable_amount'  => $total
					);

					$this->AcctDebt_model->updateCoreMemberAccountReceivableAmount($dataanggota);

					$journal_voucher_period 		= date("Ym", strtotime($data['debt_date']));
					$transaction_module_code 		= "PG";
					$transaction_module_id 			= $this->AcctDebt_model->getTransactionModuleID($transaction_module_code);
					
					$data_journal = array(
						'branch_id'							=> $auth['branch_id'],
						'journal_voucher_period' 			=> $journal_voucher_period,
						'journal_voucher_date'				=> date('Y-m-d'),
						'journal_voucher_title'				=> 'POTONG GAJI BARU'.$coremember['member_name'],
						'journal_voucher_description'		=> 'POTONG GAJI BARU'.$coremember['member_name'],
						'journal_voucher_token'				=> md5(rand()).$val['debt_id'],
						'transaction_module_id'				=> $transaction_module_id,
						'transaction_module_code'			=> $transaction_module_code,
						'transaction_journal_id' 			=> $debt_id,
						'transaction_journal_no' 			=> $debt_no,
						'created_id' 						=> $data['created_id'],
						'created_on' 						=> date('Y-m-d'),
					);
					
					if($this->AcctDebt_model->insertAcctJournalVoucher($data_journal)){
						$journal_voucher_id 				= $this->AcctDebt_model->getJournalVoucherID($data['created_id']);
						
						$account_id_default_status 			= $this->AcctDebt_model->getAccountIDDefaultStatus($debtcategory['debet_account_id']);

						$data_debet = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $debtcategory['debet_account_id'],
							'journal_voucher_description'	=> 'POTONG GAJI BARU'.$coremember['member_name'],
							'journal_voucher_amount'		=> $data['debt_amount'],
							'journal_voucher_debit_amount'	=> $data['debt_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$debtcategory['debet_account_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctDebt_model->insertAcctJournalVoucherItem($data_debet);

						$account_id_default_status 			= $this->AcctDebt_model->getAccountIDDefaultStatus($debtcategory['credit_account_id']);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $debtcategory['credit_account_id'],
							'journal_voucher_description'	=> 'POTONG GAJI BARU'.$coremember['member_name'],
							'journal_voucher_amount'		=> $data['debt_amount'],
							'journal_voucher_credit_amount'	=> $data['debt_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$debtcategory['credit_account_id'],
							'created_id' 					=> $auth['user_id']
						);

						$this->AcctDebt_model->insertAcctJournalVoucherItem($data_credit);
					}
				}
				$data_temp = array(
					"debt_id"							=> $data_update['debt_id'],
					"data_state" 						=> 1,
				);

				$this->AcctDebt_model->deleteAcctDebtTemporary($data_temp['debt_id']);
			}

			// $this->AcctDebt_model->truncateAcctDebtTemp();

			$msg = "<div class='alert alert-success alert-dismissable'>  
					<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
						Submit Potong Gaji Baru Sukses
					</div> ";
			$this->session->unset_userdata('addAcctDebt');
			$this->session->set_userdata('message',$msg);
			redirect('debt-print');
		
	}

	public function deleteAcctDebt($debt_id)
	{
		$data_debt = array(
			"data_state" 						=> 1,
		);
		$this->AcctDebt_model->deleteAcctDebtTemporary($debt_id,$data_debt);

		$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
									Hapus Potong Gaji Sukses
								</div> ";
		$this->session->set_userdata('message',$msg);
		redirect('debt-print');
	}
}
?>
<?php ob_start(); ?>
<?php
ini_set('memory_limit', '512M');
defined('BASEPATH') or exit('No direct script access allowed');


class PersonaliaReport extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Connection_model');
		$this->load->model('MainPage_model');
		$this->load->model('PersonaliaReport_model');
		$this->load->helper('sistem');
		$this->load->helper('url');
		$this->load->database('default');
		$this->load->library('configuration');
		$this->load->library('fungsi');
		$this->load->library(array('PHPExcel', 'PHPExcel/IOFactory'));
	}
	public function index()
	{
		$corebranch 									= create_double_branch($this->PersonaliaReport_model->getCoreBranch(), 'branch_id', 'branch_name');
		$corebranch[0] 									= 'Semua Cabang';
		ksort($corebranch);
		$data['main_view']['corebranch']				= $corebranch;
		$data['main_view']['acctsavings']				= create_double($this->PersonaliaReport_model->getAcctSavings(), 'savings_pdfid', 'savings_name');
		$data['main_view']['kelompoklaporansimpanan']	= $this->configuration->KelompokLaporanSimpanan1();
		$data['main_view']['month_name']				= $this->configuration->Month();
		$data['main_view']['content']					= 'PersonaliaReport/ListPersonaliaReport_view';

		$this->load->view('MainPage_view', $data);
		// print_r($data['main_view']['month_now']	);exit;
	}
	public function viewreport()
	{
		$sesi = array(
			"branch_id"					=> $this->input->post('branch_id', true),
			"branch_name"				=> $this->input->post('branch_name', true),
			"month_name"				=> $this->input->post('month_name', true),
			"year_period"				=> $this->input->post('year_period', true),
			"username"					=> $this->input->post('username', true),
			"member_name"				=> $this->input->post('member_name', true),
			// "start_date" 				=> tgltodb($this->input->post('start_date',true)),
			// "end_date" 					=> tgltodb($this->input->post('end_date',true)),
			"view"						=> $this->input->post('view', true),
		);
		if ($sesi['view'] == 'pdf') {
			$this->processPrinting($sesi);
		} else {
			$this->export($sesi);
		}
	}


	public function Month($month)
	{
		$month_name 		= array(
			'01'		=> "Januari",
			'02'		=> "Februari",
			'03'		=> "Maret",
			'04'		=> "April",
			'05'		=> "Mei",
			'06'		=> "Juni",
			'07'		=> "Juli",
			'08'		=> "Agustus",
			'09'		=> "September",
			'10'		=> "Oktober",
			'11'		=> "November",
			'12'		=> "Desember",
		);

		return $month_name[$month];
	}
	
	public function export($sesi)
	{
		// print_r($sesi);exit;
		$auth 				= $this->session->userdata('auth');
		$preferencecompany 	= $this->PersonaliaReport_model->getPreferenceCompany();

		if ($auth['branch_status'] == 1) {
			if ($sesi['branch_id'] == '' || $sesi['branch_id'] == 0) {
				$branch_id = '';
			} else {
				$branch_id = $sesi['branch_id'];
			}
		} else {
			$branch_id = $auth['branch_id'];
		}

		$user_id 						 = $auth['user_id'];
		$username						 =  $this->PersonaliaReport_model->getUsername($user_id);
		// $kelompoklaporansimpanan		 = $this->configuration->KelompokLaporanSimpanan1();
		$coremember 					 = $this->PersonaliaReport_model->getCoreMember($sesi['month_name'], $sesi['year_period']);
		$savings 						 = $this->PersonaliaReport_model->getAcctSavingsAccount($sesi['month_name'], $sesi['year_period']);

		$credits						 = $this->PersonaliaReport_model->getCreditDetail($sesi['month_name'], $sesi['year_period'],  $member_id);

		
		$monthname						 = $this->configuration->Month();
		// $datapersonalia 				 = $this->PersonaliaReport_model->getPersonaliaReport($preferencecompany['account_income_personalia_id']);
		// $totalpersonalia = 0;
		// print_r($credits);exit;
		// exit;



		$this->load->library('Excel');
		$this->excel->getProperties()->setCreator("CST FISRT")
			->setLastModifiedBy("CST FISRT")
			->setTitle("Laporan Personalia")
			->setSubject("")
			->setDescription("Laporan Personalia")
			->setKeywords("Laporan Personalia")
			->setCategory("Laporan Personalia");
		$this->excel->getSheet(0)->setTitle('Anggota');
		$this->excel->createSheet(1)->setTitle('Tabungan');
		$this->excel->createSheet(2)->setTitle('Pinjaman');
		// Anggota----
		$this->excel->setActiveSheetIndex(0);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(11);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(22);

		$this->excel->getActiveSheet()->mergeCells("B1:G1");
		$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
		$this->excel->getActiveSheet()->getStyle('B4:G4')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$this->excel->getActiveSheet()->getStyle('B4:G4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B4:G4')->getFont()->setBold(true);

		$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Anggota");

		$this->excel->getActiveSheet()->setCellValue('B4', "No");
		$this->excel->getActiveSheet()->setCellValue('C4', "No Anggota");
		$this->excel->getActiveSheet()->setCellValue('D4', "Nama Anggota");
		$this->excel->getActiveSheet()->setCellValue('E4', "Simpanan Pokok");
		$this->excel->getActiveSheet()->setCellValue('F4', "Simpanan Wajib");
		$this->excel->getActiveSheet()->setCellValue('G4', "Simpanan Khusus");
		$j = 3;
		$no = 0;
		$this->excel->getActiveSheet()->setCellValue('B2', "Periode : " . $this->Month($sesi['month_name']) . " " . " " . $sesi['year_period']);
		$this->excel->getActiveSheet()->setCellValue('B3', "Export Oleh : " . $username . " " . date(" H:i:s"));
		$j++;
		// Anggota----

		foreach ($coremember as $key => $val) {
			if (is_numeric($key)) {
				$no++;
				$j++;
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':G' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('C' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('E' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('F' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('G' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


				$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
				$this->excel->getActiveSheet()->setCellValueExplicit('C' . $j, $val['member_no'], PHPExcel_Cell_DataType::TYPE_STRING);
				$this->excel->getActiveSheet()->setCellValue('D' . $j, $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('E' . $j, number_format($val['member_principal_savings'], 2));
				$this->excel->getActiveSheet()->setCellValue('F' . $j, number_format($val['member_special_savings'], 2));
				$this->excel->getActiveSheet()->setCellValue('G' . $j, number_format($val['member_mandatory_savings'], 2));
			} else {
				continue;
			}
		}
		// Tabungan-----
		$this->excel->setActiveSheetIndex(1);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
		$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);


		$this->excel->getActiveSheet()->mergeCells("B1:F1");
		$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
		$this->excel->getActiveSheet()->getStyle('B4:F4')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$this->excel->getActiveSheet()->getStyle('B4:F4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B4:F4')->getFont()->setBold(true);

		$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Tabungan");

		$this->excel->getActiveSheet()->setCellValue('B4', "No");
		$this->excel->getActiveSheet()->setCellValue('C4', "No Rek");
		$this->excel->getActiveSheet()->setCellValue('D4', "Jenis Tabungan");
		$this->excel->getActiveSheet()->setCellValue('E4', "Nama Anggota");
		$this->excel->getActiveSheet()->setCellValue('F4', "Saldo");
		$j = 3;
		$no = 0;
		$this->excel->getActiveSheet()->setCellValue('B2', "Periode : " . $this->Month($sesi['month_name']) . " " . " " . $sesi['year_period']);
		$this->excel->getActiveSheet()->setCellValue('B3', "Export Oleh : " . $username . " " . date(" H:i:s"));
		$j++;
		// tabungan

		foreach ($savings as $key => $val) {

			if (is_numeric($key)) {
				$no++;
				$j++;
				$this->excel->setActiveSheetIndex(1);
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':F' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('C' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('E' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('F' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

				$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
				$this->excel->getActiveSheet()->setCellValueExplicit('C' . $j, $val['savings_account_no'], PHPExcel_Cell_DataType::TYPE_STRING);
				$this->excel->getActiveSheet()->setCellValue('D' . $j, $val['savings_name']);
				$this->excel->getActiveSheet()->setCellValue('E' . $j, $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('F' . $j, number_format($val['savings_account_last_balance'], 2));
			} else {
				continue;
			}
		}

		// credit-----
		$this->excel->setActiveSheetIndex(2);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
		$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
		$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);


		$this->excel->getActiveSheet()->mergeCells("B1:G1");
		$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
		$this->excel->getActiveSheet()->getStyle('B4:G4')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$this->excel->getActiveSheet()->getStyle('B4:G4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$this->excel->getActiveSheet()->getStyle('B4:G4')->getFont()->setBold(true);

		$this->excel->getActiveSheet()->setCellValue('B1', "Laporan Pinjaman");

		$this->excel->getActiveSheet()->setCellValue('B4', "No");
		$this->excel->getActiveSheet()->setCellValue('C4', "No Pinjaman");
		$this->excel->getActiveSheet()->setCellValue('D4', "Nama Anggota");
		$this->excel->getActiveSheet()->setCellValue('E4', "Outstanding");
		$this->excel->getActiveSheet()->setCellValue('F4', "Angsuran Pokok Bulan Sekarang");
		$this->excel->getActiveSheet()->setCellValue('G4', "Angsuran Bunga Bulan Sekarang");
		$j = 3;
		$no = 0;
		$this->excel->getActiveSheet()->setCellValue('B2', "Periode : " . $this->Month($sesi['month_name']) . " " . " " . $sesi['year_period']);
		$this->excel->getActiveSheet()->setCellValue('B3', "Export Oleh : " . $username . " " . date(" H:i:s"));
		$j++;
		// credit
		foreach ($credits  as $key => $val) {

			if (is_numeric($key)) {
				$no++;
				$j++;
				$this->excel->setActiveSheetIndex(2);
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':G' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('C' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('E' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('F' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('G' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

				$credits2			= $this->PersonaliaReport_model->getCreditPaymentInterest($sesi['month_name'], $sesi['year_period'], $val['credits_account_id']);

				if (empty($credits2)){
					$credits_payment_interest 	= 0;
					$credits_payment_principal 	= 0;
				} else {
					$credits_payment_interest 	= $credits2['credits_payment_interest'];
					$credits_payment_principal 	= $credits2['credits_payment_principal'];
				}

				$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
				$this->excel->getActiveSheet()->setCellValueExplicit('C' . $j, $val['credits_account_serial'], PHPExcel_Cell_DataType::TYPE_STRING);
				$this->excel->getActiveSheet()->setCellValue('D' . $j, $val['member_name']);
				$this->excel->getActiveSheet()->setCellValue('E' . $j, number_format($val['credits_account_interest_last_balance'], 2));
				$this->excel->getActiveSheet()->setCellValue('F' . $j, number_format($credits_payment_interest, 2));
				$this->excel->getActiveSheet()->setCellValue('G' . $j, number_format($credits_payment_principal, 2));
			} else {
				continue;
			}
		}
		// print_r($coremember2);exit;
		$filename = 'Laporan Personalia.xls';
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = IOFactory::createWriter($this->excel, 'Excel5');
		ob_end_clean();
		$objWriter->save('php://output');
	}
}
?>

<?php
defined('BASEPATH') or exit('No direct script access allowed');
class AcctCreditsAccountMasterData extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Connection_model');
		$this->load->model('MainPage_model');
		$this->load->model('AcctCreditsAccountMasterData_model');
		$this->load->model('CoreMember_model');
		$this->load->helper('sistem');
		$this->load->helper('url');
		$this->load->database('default');
		$this->load->library('configuration');
		$this->load->library('fungsi');
		$this->load->library(array('PHPExcel', 'PHPExcel/IOFactory'));
	}

	public function index()
	{
		$data['main_view']['corebranch']	= create_double($this->AcctCreditsAccountMasterData_model->getCoreBranch(), 'branch_id', 'branch_name');
		$data['main_view']['acctcredits']	= create_double($this->AcctCreditsAccountMasterData_model->getAcctCredits(), 'credits_id', 'credits_name');
		$data['main_view']['content']		= 'AcctCreditsAccountMasterData/ListAcctCreditsAccountMasterData_view';
		$this->load->view('MainPage_view', $data);
	}

	public function filter()
	{
		$data = array(
			'start_date'	=> tgltodb($this->input->post('start_date', true)),
			'end_date'		=> tgltodb($this->input->post('end_date', true)),
			"branch_id" 	=> $this->input->post('branch_id', true),
			"credits_id"	=> $this->input->post('credits_id', true)
		);

		$this->session->set_userdata('filter-masterdatacreditsaccount', $data);
		redirect('credits-account-master-data');
	}

	public function reset_search()
	{
		$this->session->unset_userdata('filter-masterdatacreditsaccount');
		redirect('credits-account-master-data');
	}

	public function getAcctCreditsAccountMasterDataList()
	{
		$auth = $this->session->userdata('auth');

		if ($auth['branch_status'] == 1) {
			$sesi	= 	$this->session->userdata('filter-masterdatacreditsaccount');
			if (!is_array($sesi)) {
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['branch_id']		= '';
				$sesi['credits_id']		= '';
			}
		} else {
			$sesi['start_date'] = $auth['start_date'];
			$sesi['end_date'] 	= $auth['end_date'];
			$sesi['branch_id']	= $auth['branch_id'];
			$sesi['credits_id']	= $auth['credits_id'];
		}

		$list = $this->AcctCreditsAccountMasterData_model->get_datatables($sesi['start_date'], $sesi['end_date'], $sesi['branch_id'], $sesi['credits_id']);

		foreach ($list as $key) {
			if (!empty($key->savings_account_id)) {
				$savings_account_no	= $this->AcctCreditsAccountMasterData_model->getAcctSavingsAccountNo($key->savings_account_id);
			} else {
				$savings_account_no = '';
			}
		}

		$membergender 	= $this->configuration->MemberGender();
		$memberidentity = $this->configuration->MemberIdentity();
		$memberjobtype 	= $this->configuration->WorkingType();
		$data 			= array();
		$no				= $_POST['start'];

		foreach ($list as $creditsaccount) {
			$keterangan = '';
			$agunan 	= $this->AcctCreditsAccountMasterData_model->getCreditsAccountAgunan($creditsaccount->credits_account_id);
			foreach($agunan as $keyy => $vall){
				$keterangan = $keterangan.$vall['credits_agunan_penerimaan_description'].' '.$vall['credits_agunan_deposito_account_no'].' '.$vall['credits_agunan_other_description'];
			}

			$no++;
			$row 	= array();
			$row[] 	= $no;
			$row[] 	= $creditsaccount->credits_account_serial;
			$row[] 	= $creditsaccount->credits_account_bank_account;
			$row[] 	= $creditsaccount->credits_account_bank_name;
			$row[] 	= $creditsaccount->credits_account_bank_owner;
			$row[] 	= $creditsaccount->member_no;
			$row[] 	= $creditsaccount->member_name;
			$row[] 	= $creditsaccount->credits_name;
			$row[] 	= $creditsaccount->credits_account_period;
			$row[] 	= $creditsaccount->credits_account_payment_to;
			$row[] 	= tgltoview($creditsaccount->credits_account_date);
			$row[] 	= $this->AcctCreditsAccountMasterData_model->getFirstPaymentDate($creditsaccount->credits_account_id);
			$row[] 	= tgltoview($creditsaccount->credits_account_due_date);
			$row[] 	= number_format($creditsaccount->credits_account_amount, 2);
			$row[] 	= number_format($creditsaccount->credits_account_amount, 2);
			$row[] 	= number_format($creditsaccount->credits_account_interest, 2);
			$row[] 	= number_format($creditsaccount->credits_account_principal_amount, 2);
			$row[] 	= number_format($creditsaccount->credits_account_interest_amount, 2);
			$row[] 	= number_format(($creditsaccount->credits_account_principal_amount+$creditsaccount->credits_account_interest_amount), 2);
			$row[] 	= number_format($creditsaccount->credits_account_last_balance, 2);
			$row[] 	= $creditsaccount->credits_account_remark;
			$row[] 	= $keterangan;
			$data[] = $row;
		}

		$output = array(
			"draw" 				=> $_POST['draw'],
			"recordsTotal" 		=> $this->AcctCreditsAccountMasterData_model->count_all($sesi['start_date'], $sesi['end_date'], $sesi['branch_id'], $sesi['credits_id']),
			"recordsFiltered" 	=> $this->AcctCreditsAccountMasterData_model->count_filtered($sesi['start_date'], $sesi['end_date'], $sesi['branch_id'], $sesi['credits_id']),
			"data"				=> $data,
		);

		echo json_encode($output);
	}

	public function exportAcctCreditsAccountMasterData()
	{
		$auth = $this->session->userdata('auth');

		if ($auth['branch_status'] == 1) {
			$sesi	= 	$this->session->userdata('filter-masterdatacreditsaccount');
			if (!is_array($sesi)) {
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['branch_id']		= '';
				$sesi['credits_id']		= '';
			}
		} else {
			$sesi['start_date'] = $auth['start_date'];
			$sesi['end_date'] 	= $auth['end_date'];
			$sesi['branch_id']	= $auth['branch_id'];
			$sesi['credits_id']	= $auth['credits_id'];
		}

		$acctcreditsaccount	= $this->AcctCreditsAccountMasterData_model->get_datatables($sesi['start_date'], $sesi['end_date'], $sesi['branch_id'], $sesi['credits_id']);
		$membergender 		= $this->configuration->MemberGender();
		$memberidentity 	= $this->configuration->MemberIdentity();
		$memberjobtype 		= $this->configuration->WorkingType();

		if (count($acctcreditsaccount) != 0) {
			$this->load->library('Excel');

			$this->excel->getProperties()->setCreator("SIS")
				->setLastModifiedBy("SIS")
				->setTitle("Master Data Pinjaman")
				->setSubject("")
				->setDescription("Master Data Pinjaman")
				->setKeywords("Master, Data, Pinjaman")
				->setCategory("Master Data Pinjaman");

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
			$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('W')->setWidth(30);

			$this->excel->getActiveSheet()->mergeCells("B1:W1");
			$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$this->excel->getActiveSheet()->getStyle('B3:W3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B3:W3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B3:W3')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->setCellValue('B1', "Master Data Pinjaman");

			$this->excel->getActiveSheet()->setCellValue('B3', "No");
			$this->excel->getActiveSheet()->setCellValue('C3', "No. Akad");
			$this->excel->getActiveSheet()->setCellValue('D3', "No. Rekening");
			$this->excel->getActiveSheet()->setCellValue('E3', "Bank");
			$this->excel->getActiveSheet()->setCellValue('F3', "a.n. Rekening");
			$this->excel->getActiveSheet()->setCellValue('G3', "No Anggota");
			$this->excel->getActiveSheet()->setCellValue('H3', "Nama Anggota");
			$this->excel->getActiveSheet()->setCellValue('I3', "Jenis Pinjaman");
			$this->excel->getActiveSheet()->setCellValue('J3', "Jangka Waktu");
			$this->excel->getActiveSheet()->setCellValue('K3', "Sudah Angsur (x)");
			$this->excel->getActiveSheet()->setCellValue('L3', "Tgl Pinjam");
			$this->excel->getActiveSheet()->setCellValue('M3', "Tgl Angsuran Pertama");
			$this->excel->getActiveSheet()->setCellValue('N3', "Tgl Jatuh Tempo");
			$this->excel->getActiveSheet()->setCellValue('O3', "Plafon");
			$this->excel->getActiveSheet()->setCellValue('P3', "Pokok");
			$this->excel->getActiveSheet()->setCellValue('Q3', "Margin");
			$this->excel->getActiveSheet()->setCellValue('R3', "Ang Pokok");
			$this->excel->getActiveSheet()->setCellValue('S3', "Ang Margin");
			$this->excel->getActiveSheet()->setCellValue('T3', "Jumlah Angsuran");
			$this->excel->getActiveSheet()->setCellValue('U3', "Sisa Pokok");
			$this->excel->getActiveSheet()->setCellValue('V3', "Keterangan");
			$this->excel->getActiveSheet()->setCellValue('W3', "Keterangan Agunan");

			$j 	= 4;
			$no = 0;

			foreach ($acctcreditsaccount as $key => $val) {
				$keterangan = '';
				$agunan 	= $this->AcctCreditsAccountMasterData_model->getCreditsAccountAgunan($val->credits_account_id);
				foreach($agunan as $keyy => $vall){
					$keterangan = $keterangan.$vall['credits_agunan_penerimaan_description'].' '.$vall['credits_agunan_deposito_account_no'].' '.$vall['credits_agunan_other_description'];
				}

				$no++;
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getStyle('B' . $j . ':W' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('C' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('E' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('F' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('G' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('H' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('I' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('J' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('K' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('L' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('M' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('N' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('O' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('P' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('Q' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('R' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('S' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('T' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('U' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('V' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('W' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

				$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
				$this->excel->getActiveSheet()->setCellValue('C' . $j, $val->credits_account_serial);
				$this->excel->getActiveSheet()->setCellValue('D' . $j, $val->credits_account_bank_account);
				$this->excel->getActiveSheet()->setCellValue('E' . $j, $val->credits_account_bank_name);
				$this->excel->getActiveSheet()->setCellValue('F' . $j, $val->credits_account_bank_owner);
				$this->excel->getActiveSheet()->setCellValueExplicit('G' . $j, $val->member_no);
				$this->excel->getActiveSheet()->setCellValue('H' . $j, $val->member_name);
				$this->excel->getActiveSheet()->setCellValue('I' . $j, $val->credits_name);
				$this->excel->getActiveSheet()->setCellValue('J' . $j, $val->credits_account_period);
				$this->excel->getActiveSheet()->setCellValue('K' . $j, $val->credits_account_payment_to);
				$this->excel->getActiveSheet()->setCellValue('L' . $j, tgltoview($val->credits_account_date));
				$this->excel->getActiveSheet()->setCellValue('M' . $j, $this->AcctCreditsAccountMasterData_model->getFirstPaymentDate($val->credits_account_id));
				$this->excel->getActiveSheet()->setCellValue('N' . $j, tgltoview($val->credits_account_due_date));
				$this->excel->getActiveSheet()->setCellValue('O' . $j, $val->credits_account_amount);
				$this->excel->getActiveSheet()->setCellValue('P' . $j, $val->credits_account_amount);
				$this->excel->getActiveSheet()->setCellValue('Q' . $j, $val->credits_account_interest);
				$this->excel->getActiveSheet()->setCellValue('R' . $j, $val->credits_account_principal_amount);
				$this->excel->getActiveSheet()->setCellValue('S' . $j, $val->credits_account_interest_amount);	
				$this->excel->getActiveSheet()->setCellValue('T' . $j, ($val->credits_account_principal_amount + $val->credits_account_interest_amount));	
				$this->excel->getActiveSheet()->setCellValue('U' . $j, $val->credits_account_last_balance);	
				$this->excel->getActiveSheet()->setCellValueExplicit('V' . $j, $val->credits_account_remark);	
				$this->excel->getActiveSheet()->setCellValueExplicit('W' . $j, $keterangan);	
				$j++;
			}
			
			$filename = 'Master Data Pinjaman.xls';
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

	public function function_state_add()
	{
		$unique 				= $this->session->userdata('unique');
		$value 					= $this->input->post('value', true);
		$sessions				= $this->session->userdata('addacctcreditsaccountmasterdata-' . $unique['unique']);
		$sessions['active_tab'] = $value;
		$this->session->set_userdata('addacctcreditsaccountmasterdata-' . $unique['unique'], $sessions);
	}
}

<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctDebtRepayment extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctDebtRepayment_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$auth 		= $this->session->userdata('auth');
			$unique 	= $this->session->userdata('unique');
			$sesi		= $this->session->userdata('filter-acctdebtrepayment');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');				
			}
			$this->session->set_userdata('filter-acctdebtrepayment', $sesi);

			$this->session->unset_userdata('addAcctDebtRepayment-'.$unique['unique']);	
			$this->session->unset_userdata('acctdebtrepaymenttoken-'.$unique['unique']);
			$this->session->unset_userdata('acctdebtrepaymenttokenedit-'.$unique['unique']);

			$this->AcctDebtRepayment_model->truncateAcctDataRepaymentItemTemp();

			$data['main_view']['acctdebtrepayment']			= $this->AcctDebtRepayment_model->getAcctDebtRepayment();
			$data['main_view']['content']					= 'AcctDebtRepayment/ListAcctDebtRepayment_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				"start_date" 	=> $this->input->post('start_date',true),
				"end_date" 		=> $this->input->post('end_date',true),
			);

			$this->session->set_userdata('filter-acctdebtrepayment',$data);
			redirect('debt-repayment');
		}

		public function reset_list(){
			$this->session->unset_userdata('filter-acctdebtrepayment');
			redirect('debt-repayment');
		}
		
		public function getAcctDebtRepaymentList(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctdebtrepayment');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
			}

			$list = $this->AcctDebtRepayment_model->get_datatables_master($sesi['start_date'], $sesi['end_date']);

	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $debtrepayment) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $debtrepayment->debt_repayment_no;
	            $row[] = date('d-m-Y', strtotime($debtrepayment->debt_repayment_date));
	            $row[] = number_format($debtrepayment->debt_repayment_amount, 2);
				$row[] = '<a href="'.base_url().'debt-repayment/detail/'.$debtrepayment->debt_repayment_id.'" class="btn btn-xs yellow-lemon" role="button"><i class="fa fa-bars"></i> Detail</a>';

	            $data[] = $row;
	        }

	        $output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->AcctDebtRepayment_model->count_all_master($sesi['start_date'], $sesi['end_date']),
				"recordsFiltered" => $this->AcctDebtRepayment_model->count_filtered_master($sesi['start_date'], $sesi['end_date']),
				"data" => $data,
			);

	        echo json_encode($output);
		}

		public function detailAcctDebtRepayment(){
			$auth 				= $this->session->userdata('auth');
			$debt_repayment_id 	= $this->uri->segment(3);

			$data['main_view']['debtrepaymentdetail']		= $this->AcctDebtRepayment_model->getAcctDebtRepayment_Detail($debt_repayment_id);
			$data['main_view']['debtrepaymentitem']			= $this->AcctDebtRepayment_model->getAcctDebtRepaymentItem($debt_repayment_id);
			$data['main_view']['content']					= 'AcctDebtRepayment/DetailAcctDebtRepayment_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function addAcctDebtRepayment(){
			$auth 	= $this->session->userdata('auth');

			$data['main_view']['acctdebtrepaymentitemtemp']	= $this->AcctDebtRepayment_model->getAcctDebtRepaymentItemTemp();
			$data['main_view']['content']					= 'AcctDebtRepayment/FormAddAcctDebtRepayment_view';
			$this->load->view('MainPage_view',$data);
		}

		public function addArrayAcctDebtRepayment(){
			$auth 		= $this->session->userdata('auth');

			$this->AcctDebtRepayment_model->truncateAcctDataRepaymentItemTemp();

			$fileName 	= $_FILES['excel_file']['name'];
			$fileSize 	= $_FILES['excel_file']['size'];
			$fileError 	= $_FILES['excel_file']['error'];
			$fileType 	= $_FILES['excel_file']['type'];

			$config['upload_path'] 		= './assets/';
            $config['file_name'] 		= $fileName;
            $config['allowed_types'] 	= 'xls|xlsx';
            $config['max_size']        	= 10000;

			$this->load->library('upload');
            $this->upload->initialize($config);

			if(! $this->upload->do_upload('excel_file') ){
				$msg = "<div class='alert alert-danger alert-dismissable'>                
					".$this->upload->display_errors('', '')."
					</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('debt-repayment/add');
			}else{
				$media 			= $this->upload->data('excel_file');
				$inputFileName 	= './assets/'.$config['file_name'];

				try {
					$inputFileType 	= IOFactory::identify($inputFileName);
					$objReader 		= IOFactory::createReader($inputFileType);
					$objPHPExcel 	= $objReader->load($inputFileName);
				} catch(Exception $e) {
					die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
				}

				$sheet 			= $objPHPExcel->getSheet(0);
				$highestRow 	= $sheet->getHighestRow();
				$highestColumn 	= $sheet->getHighestColumn();

				for ($row = 2; $row <= $highestRow; $row++){ 
					$rowData 	= $sheet->rangeToArray('A'.$row.':'.$highestColumn.$row, NULL, TRUE, FALSE);

					$member_id 	= $this->AcctDebtRepayment_model->getCoreMemberID($rowData[0][0]);

					$data	= array (
						'member_id'							=> $member_id,
						'debt_repayment_item_temp_amount'	=> $rowData[0][1],
					);

					if($data['member_id'] != ''){
						$this->AcctDebtRepayment_model->insertAcctDataRepaymentItemTemp($data);
					}
				}
				unlink($inputFileName);
				$msg = "<div class='alert alert-success'>                
							Import Data Excel
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('debt-repayment/add');
			}
		}
		
		public function processAddAcctDebtRepayment(){
			$auth 		= $this->session->userdata('auth');
			
			$preferencecompany 		= $this->AcctDebtRepayment_model->getPreferenceCompany();
			$acctdebtrepaymenttemp 	= $this->AcctDebtRepayment_model->getAcctDebtRepaymentItemTemp();
			$total 					= 0;
			foreach($acctdebtrepaymenttemp as $key => $val){
				$total += $val['debt_repayment_item_temp_amount'];
			}

			$data = array (
				'debt_repayment_date' 	=> date('Y-m-d'),
				'debt_repayment_amount' => $total,
				'created_id'			=> $auth['user_id']
			);

			if($this->AcctDebtRepayment_model->insertAcctDebtRepayment($data)){
				$debt_repayment_id = $this->AcctDebtRepayment_model->getAcctDebtRepaymentLast($auth['user_id']);
				$debt_repayment_no = $this->AcctDebtRepayment_model->getAcctDebtRepaymentNoLast($auth['user_id']);

				$journal_voucher_period 		= date("Ym", strtotime($data['debt_repayment_date']));
				$transaction_module_code 		= "PPG";
				$transaction_module_id 			= $this->AcctDebtRepayment_model->getTransactionModuleID($transaction_module_code);
				
				$data_journal = array(
					'branch_id'							=> $auth['branch_id'],
					'journal_voucher_period' 			=> $journal_voucher_period,
					'journal_voucher_date'				=> date('Y-m-d'),
					'journal_voucher_title'				=> 'PELUNASAN POTONG GAJI',
					'journal_voucher_description'		=> 'PELUNASAN POTONG GAJI',
					'journal_voucher_token'				=> md5(rand()),
					'transaction_module_id'				=> $transaction_module_id,
					'transaction_module_code'			=> $transaction_module_code,
					'transaction_journal_id' 			=> $debt_repayment_id,
					'transaction_journal_no' 			=> $debt_repayment_no,
					'created_id' 						=> $data['created_id'],
					'created_on' 						=> date('Y-m-d'),
				);
				
				if($this->AcctDebtRepayment_model->insertAcctJournalVoucher($data_journal)){
					$journal_voucher_id 				= $this->AcctDebtRepayment_model->getJournalVoucherID($data['created_id']);
					
					$account_id_default_status 			= $this->AcctDebtRepayment_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

					$data_debet = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_cash_id'],
						'journal_voucher_description'	=> 'PELUNASAN POTONG GAJI',
						'journal_voucher_amount'		=> $data['debt_repayment_amount'],
						'journal_voucher_debit_amount'	=> $data['debt_repayment_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 0,
						'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_cash_id'],
						'created_id' 					=> $auth['user_id']
					);

					$this->AcctDebtRepayment_model->insertAcctJournalVoucherItem($data_debet);

					$account_id_default_status 			= $this->AcctDebtRepayment_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);

					$data_credit = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_salary_payment_id'],
						'journal_voucher_description'	=> 'PELUNASAN POTONG GAJI',
						'journal_voucher_amount'		=> $data['debt_repayment_amount'],
						'journal_voucher_credit_amount'	=> $data['debt_repayment_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'journal_voucher_item_token'	=> $data['savings_cash_mutation_token'].$preferencecompany['account_salary_payment_id'],
						'created_id' 					=> $auth['user_id']
					);

					$this->AcctDebtRepayment_model->insertAcctJournalVoucherItem($data_credit);
				}

				foreach($acctdebtrepaymenttemp as $key => $val){
					$dataitem = array(
						'debt_repayment_id' 						=> $debt_repayment_id,
						'member_id' 								=> $val['member_id'],
						'debt_repayment_item_principal_amount' 		=> $this->input->post('principal_'.$val['member_id'] ,true),
						'debt_repayment_item_mandatory_amount' 		=> $this->input->post('mandatory_'.$val['member_id'] ,true),
						'debt_repayment_item_minimarket_amount' 	=> $this->input->post('minimarket_'.$val['member_id'] ,true),
						'debt_repayment_item_amount' 				=> $val['debt_repayment_item_temp_amount'],
						'created_id'								=> $auth['user_id'],
					);

					if($this->AcctDebtRepayment_model->insertAcctDebtRepaymentItem($dataitem)){
						$member_debt = $this->AcctDebtRepayment_model->getCoreMemberAccountReceivableAmount($val['member_id']);

						$datamember = array(
							'member_id' 						=> $val['member_id'],
							'member_account_principal_debt' 	=> $member_debt['member_account_principal_debt'] - $dataitem['debt_repayment_item_principal_amount'],
							'member_account_mandatory_debt' 	=> $member_debt['member_account_mandatory_debt'] - $dataitem['debt_repayment_item_mandatory_amount'],
							'member_account_minimarket_debt' 	=> $member_debt['member_account_minimarket_debt'] - $dataitem['debt_repayment_item_minimarket_amount'],
							'member_account_receivable_amount' 	=> $member_debt['member_account_receivable_amount'] - $val['debt_repayment_item_temp_amount'],
						);

						$this->AcctDebtRepayment_model->updateCoreMemberAccountReceivableAmount($datamember);
					}
				}

				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>  
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Tambah Pelunasan Piutang Potong Gaji Sukses
						</div> ";
				$this->session->unset_userdata('addAcctDebtRepayment');
				$this->session->set_userdata('message',$msg);
				redirect('debt-repayment');
			}else{
				$this->session->set_userdata('addAcctDebtRepayment',$data);
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Tambah Pelunasan Piutang Potong Gaji Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('debt-repayment');
			}
		}

		public function printMemberAccountReceivableAmount(){
			$auth 				= $this->session->userdata('auth');
			$preferencecompany 	= $this->AcctDebtRepayment_model->getPreferenceCompany();
			$coremember			= $this->AcctDebtRepayment_model->getCoreMember();


			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF('P', PDF_UNIT, array(340, 380), true, 'UTF-8', false);

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

			$pdf->SetFont('helvetica', '', 12);

			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			    	<td rowspan=\"2\" width=\"20%\">" .$img."</td>
			        <td width=\"50%\"><div style=\"text-align: left; font-size:14px\">DAFTAR PIUTANG POTONG GAJI</div></td>
			    </tr>
			    <tr>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">".date('d M Y')."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
			    <tr>
			        <td width=\"5%\"><div style=\"text-align: center;\">No</div></td>
			        <td width=\"10%\"><div style=\"text-align: center;\">No Anggota</div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\">Nama</div></td>
			        <td width=\"5%\"><div style=\"text-align: center;\">Status</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;\">Simp Pokok</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;\">Simp Wajib</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;\">Pinjaman Toko</div></td>
			        <td width=\"15%\"><div style=\"text-align: center;\">Total</div></td>
			    </tr>";

			$no = 1;
			foreach($coremember as $key => $val){
				if($val['member_account_receivable_status'] == 0){
					$status = "Aktif";
				}else{
					$status = "Diblokir";
				}
				$tbl1 .= "
				<tr>
					<td><div style=\"text-align: center;\">".$no."</div></td>
					<td><div style=\"text-align: left;\">".$val['member_no']."</div></td>
					<td><div style=\"text-align: left;\">".$val['member_name']."</div></td>
					<td><div style=\"text-align: left;\">".$status."</div></td>
					<td><div style=\"text-align: right;\">".number_format($val['member_account_principal_debt'], 2)."</div></td>
					<td><div style=\"text-align: right;\">".number_format($val['member_account_mandatory_debt_debt'], 2)."</div></td>
					<td><div style=\"text-align: right;\">".number_format($val['member_account_minimarket_debt'], 2)."</div></td>
					<td><div style=\"text-align: right;\">".number_format($val['member_account_receivable_amount'], 2)."</div></td>
				</tr>
				";
				$no++;
			}

			$tbl1 .="</table>";

			$pdf->writeHTML($tbl1, true, false, false, false, '');
			if (ob_get_length() > 0){
				ob_clean();	
			}
			
			$filename = 'DataPiutangPotongGajiAnggota.pdf';
			$pdf->Output($filename, 'I');
		}

		public function exportMemberAccountReceivableAmount(){
			$auth 				= $this->session->userdata('auth');
			$preferencecompany 	= $this->AcctDebtRepayment_model->getPreferenceCompany();
			$coremember			= $this->AcctDebtRepayment_model->getCoreMember();
			$user_id 			= $auth['user_id'];
			$username		    = $this->AcctDebtRepayment_model->getUsername($user_id);

			$this->load->library('Excel');

			$this->excel->getProperties()->setCreator("CST FISRT")
				->setLastModifiedBy("CST FISRT")
				->setTitle("DAFTAR PIUTANG POTONG GAJI")
				->setSubject("")
				->setDescription("DAFTAR PIUTANG POTONG GAJI")
				->setKeywords("DAFTAR PIUTANG POTONG GAJI")
				->setCategory("DAFTAR PIUTANG POTONG GAJI");

			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(11);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);

			$this->excel->getActiveSheet()->mergeCells("B1:I1");
			$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$this->excel->getActiveSheet()->getStyle('B4:I4')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B4:I4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B4:I4')->getFont()->setBold(true);

			$this->excel->getActiveSheet()->setCellValue('B1', "DAFTAR PIUTANG POTONG GAJI");

			$this->excel->getActiveSheet()->setCellValue('B4', "No");
			$this->excel->getActiveSheet()->setCellValue('C4', "No Anggota");
			$this->excel->getActiveSheet()->setCellValue('D4', "Nama");
			$this->excel->getActiveSheet()->setCellValue('E4', "Status");
			$this->excel->getActiveSheet()->setCellValue('F4', "Simpanan Pokok");
			$this->excel->getActiveSheet()->setCellValue('G4', "Simpanan Wajib");
			$this->excel->getActiveSheet()->setCellValue('H4', "Pinjaman Toko");
			$this->excel->getActiveSheet()->setCellValue('I4', "Total");
			$this->excel->getActiveSheet()->setCellValue('B3', "Export Oleh : " . $username . " " . date(" H:i:s"));
			$this->excel->getActiveSheet()->setCellValue('I3', "" .  date("d M Y"));

			$j = 3;
			$no = 0;
			$j++;

			foreach ($coremember as $key => $val) {
				if (is_numeric($key)) {
					if($val['member_account_receivable_status'] == 0){
						$status = "Aktif";
					}else{
						$status = "Diblokir";
					}

					$no++;
					$j++;
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->getStyle('B' . $j . ':I' . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$this->excel->getActiveSheet()->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$this->excel->getActiveSheet()->getStyle('C' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('D' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('E' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$this->excel->getActiveSheet()->getStyle('F' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('G' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('H' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$this->excel->getActiveSheet()->getStyle('I' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$this->excel->getActiveSheet()->setCellValue('B' . $j, $no);
					$this->excel->getActiveSheet()->setCellValueExplicit('C' . $j, $val['member_no'], PHPExcel_Cell_DataType::TYPE_STRING);
					$this->excel->getActiveSheet()->setCellValue('D' . $j, $val['member_name']);
					$this->excel->getActiveSheet()->setCellValue('E' . $j, $status);
					$this->excel->getActiveSheet()->setCellValue('F' . $j, number_format($val['member_account_principal_debt'], 2));
					$this->excel->getActiveSheet()->setCellValue('G' . $j, number_format($val['member_account_mandatory_debt'], 2));
					$this->excel->getActiveSheet()->setCellValue('H' . $j, number_format($val['member_account_minimarket_debt'], 2));
					$this->excel->getActiveSheet()->setCellValue('I' . $j, number_format($val['member_account_receivable_amount'], 2));
				} else {
					continue;
				}
			}

			$filename = 'DAFTAR PIUTANG POTONG GAJI.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $filename . '"');
			header('Cache-Control: max-age=0');

			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');
			ob_end_clean();
			$objWriter->save('php://output');
		}
	}
?>
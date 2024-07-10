<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctJournalVoucher extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctJournalVoucher_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctjournalvoucher');
			$unique = $this->session->userdata('unique');

			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['branch_id']		= $auth['branch_id'] ;	
			}else{
				if(!$sesi['branch_id']){
					$sesi['branch_id']	= $auth['branch_id'];	
				}
			}

			$this->session->unset_userdata('addacctjournalvoucher-'.$unique['unique']);
			$this->session->unset_userdata('addacctjournalvoucheritem-'.$unique['unique']);
			$this->session->unset_userdata('acctjournalvouchertoken-'.$unique['unique']);
			$this->session->unset_userdata('journal_voucher_id');
						
			$data['main_view']['acctaccount']			= create_double($this->AcctJournalVoucher_model->getAcctAccount(),'account_id','account_code');
			$data['main_view']['corebranch']			= create_double($this->AcctJournalVoucher_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['acctjournalvoucher']	= $this->AcctJournalVoucher_model->getAcctJournalVoucher($sesi['start_date'], $sesi['end_date'], $sesi['branch_id']);
			$data['main_view']['content']				= 'AcctJournalVoucher/ListAcctJournalVoucher_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				"start_date" 	=> tgltodb($this->input->post('start_date',true)),
				"end_date" 		=> tgltodb($this->input->post('end_date',true)),
				"branch_id" 	=> $this->input->post('branch_id',true),
			);

			$this->session->set_userdata('filter-acctjournalvoucher',$data);
			redirect('journal-voucher');
		}

		public function reset_search(){
			$this->session->unset_userdata('filter-acctjournalvoucher');
			redirect('journal-voucher');
		}

		public function addAcctJournalVoucher(){
			$sesi 					= $this->session->userdata('unique');
			$token 					= $this->session->userdata('acctjournalvouchertoken-'.$sesi['unique']);
			$journal_voucher_id 	= $this->session->userdata('journal_voucher_id');

			if(empty($token)){
				$token = md5(date('Y-m-d H:i:s'));
				$this->session->set_userdata('acctjournalvouchertoken-'.$sesi['unique'], $token);
			}

			$acctjournalvoucheritem = $this->session->userdata('addacctjournalvoucheritem-'.$sesi['unique']);
			if($acctjournalvoucheritem){
				$kredit = 0;
				$debit 	= 0;

				foreach($acctjournalvoucheritem as $key => $val){
					if($val['journal_voucher_status']==0){
						$debit  += $val['journal_voucher_amount'];
					} else {
						$kredit += $val['journal_voucher_amount'];
					}
				}

				if($debit == $kredit){
					$warning = "";
				}else{
					// $warning = "
					// <div class='alert alert-danger alert-dismissable'>  
					// 	<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
					// 	Debet dan Kredit Tidak Balance!
					// </div> ";
				}
			}else{
				$warning = "";
			}

			$data['main_view']['warning']				= $warning;
			$data['main_view']['accountstatus']			= $this->configuration->AccountStatus();
			$data['main_view']['journal_voucher_id']	= $this->AcctJournalVoucher_model->getJournalVoucherNo($journal_voucher_id);
			$data['main_view']['acctaccount']			= create_double($this->AcctJournalVoucher_model->getAcctAccount(),'account_id','account_code');
			$data['main_view']['content']				= 'AcctJournalVoucher/FormAddAcctJournalVoucher_view';
			$this->load->view('MainPage_view',$data);
		}

		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctjournalvoucher-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addacctjournalvoucher-'.$unique['unique'],$sessions);
		}

		public function reset_data(){
			$sesi 		= $this->session->userdata('unique');
			$this->session->unset_userdata('addacctjournalvoucher-'.$sesi['unique']);
			$this->session->unset_userdata('addacctjournalvoucheritem-'.$sesi['unique']);
			redirect('journal-voucher/add');
		}

		public function processAddArrayAcctjournalVoucher(){
			$date = date('YmdHis');
			$data_acctjournalvoucheritem = array(
				'record_id'								=> $date.$this->input->post('account_id', true),
				'account_id'							=> $this->input->post('account_id', true),
				'journal_voucher_status'				=> $this->input->post('journal_voucher_status', true),
				'journal_voucher_amount'				=> $this->input->post('journal_voucher_amount', true),
				'journal_voucher_description_item'		=> $this->input->post('journal_voucher_description_item', true),
			);

			$this->form_validation->set_rules('account_id', 'Account Name', 'required');
			
			if($this->form_validation->run()==true){
				$unique 			= $this->session->userdata('unique');
				$session_name 		= $this->input->post('session_name',true);
				$dataArrayHeader	= $this->session->userdata('addacctjournalvoucheritem-'.$unique['unique']);
				
				$dataArrayHeader[$data_acctjournalvoucheritem['record_id']] = $data_acctjournalvoucheritem;
				
				$this->session->set_userdata('addacctjournalvoucheritem-'.$unique['unique'],$dataArrayHeader);

				$data_acctjournalvoucheritem = $this->session->userdata('addacctjournalvoucher-'.$unique['unique']);
				
				$data_acctjournalvoucheritem['record_id']							= '';
				$data_acctjournalvoucheritem['account_id']							= '';
				$data_acctjournalvoucheritem['journal_voucher_status'] 				= '';
				$data_acctjournalvoucheritem['journal_voucher_amount'] 				= '';
				$data_acctjournalvoucheritem['journal_voucher_description_item'] 	= '';

				$this->session->set_userdata('addacctjournalvoucher-'.$unique['unique'],$data_acctjournalvoucheritem);
			}else{
				$msg = validation_errors("<div class='alert alert-danger'>", "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button></div>");
				$this->session->set_userdata('message',$msg);
			}
		}
		
		public function processAddAcctJournalVoucher(){
			$auth 				= $this->session->userdata('auth');
			$sesi 				= $this->session->userdata('unique');
			$journal_voucher_id = $this->session->userdata('journal_voucher_id');

			$acctjournalvoucheritem 	= $this->session->userdata('addacctjournalvoucheritem-'.$sesi['unique']);
			$journal_voucher_period 	= date("Ym", strtotime($this->input->post('journal_voucher_date', true)));
			$transaction_module_code 	= "JU";
			$transaction_module_id 		= $this->AcctJournalVoucher_model->getTransactionModuleID($transaction_module_code);

			if($journal_voucher_id){
				$data = array(
					'branch_id'						=> $auth['branch_id'],
					'journal_voucher_period' 		=> $journal_voucher_period,
					'journal_voucher_date'			=> tgltodb($this->input->post('journal_voucher_date', true)),
					'journal_voucher_title'			=> $this->input->post('journal_voucher_description', true),
					'journal_voucher_description'	=> $this->input->post('journal_voucher_description', true),
					'journal_voucher_token'			=> $this->input->post('journal_voucher_token', true),
					'transaction_module_id'			=> $transaction_module_id,
					'transaction_module_code'		=> $transaction_module_code,
					'repayment_status'				=> 2,
					'repayment_id'					=> $journal_voucher_id,
					'created_id'					=> $auth['user_id'],
					'created_on'					=> date('Y-m-d H:i:s'),
				);

				$updatedata = array(
					'journal_voucher_id' 	=> $journal_voucher_id,
					'repayment_status' 		=> 1,
				);
			}else{
				$data = array(
					'branch_id'						=> $auth['branch_id'],
					'journal_voucher_period' 		=> $journal_voucher_period,
					'journal_voucher_date'			=> tgltodb($this->input->post('journal_voucher_date', true)),
					'journal_voucher_title'			=> $this->input->post('journal_voucher_description', true),
					'journal_voucher_description'	=> $this->input->post('journal_voucher_description', true),
					'journal_voucher_token'			=> $this->input->post('journal_voucher_token', true),
					'transaction_module_id'			=> $transaction_module_id,
					'transaction_module_code'		=> $transaction_module_code,
					'created_id'					=> $auth['user_id'],
					'created_on'					=> date('Y-m-d H:i:s'),
				);
			}
			
			$this->form_validation->set_rules('journal_voucher_description', 'Uraian', 'required');

			$journal_voucher_token = $this->AcctJournalVoucher_model->getJournalVoucherToken($data['journal_voucher_token']);

			if($this->form_validation->run()==true){
				if(!empty($acctjournalvoucheritem)){
					if($journal_voucher_token->num_rows() == 0){
						if($this->AcctJournalVoucher_model->insertAcctJournalVoucher($data)){
							if($updatedata){
								$this->AcctJournalVoucher_model->updateJournalVoucherRepayment($updatedata);
							}

							$journal_voucher_id = $this->AcctJournalVoucher_model->getJournalVoucherID($data['created_id']);

							foreach ($acctjournalvoucheritem as $key => $val) {
								if($val['journal_voucher_status'] == 0){
									$data_debet =array(
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $val['account_id'],
										'journal_voucher_description'	=> $data['journal_voucher_description'],
										'journal_voucher_amount'		=> $val['journal_voucher_amount'],
										'journal_voucher_debit_amount'	=> $val['journal_voucher_amount'],
										'account_id_status'				=> 0,
										'journal_voucher_item_token'	=> $data['journal_voucher_token'].$val['record_id'],
									);
									$this->AcctJournalVoucher_model->insertAcctJournalVoucherItem($data_debet);
								} else {
									$data_credit =array(
										'journal_voucher_id'			=> $journal_voucher_id,
										'account_id'					=> $val['account_id'],
										'journal_voucher_description'	=> $data['journal_voucher_description'],
										'journal_voucher_amount'		=> $val['journal_voucher_amount'],
										'journal_voucher_credit_amount'	=> $val['journal_voucher_amount'],
										'account_id_status'				=> 1,
										'journal_voucher_item_token'	=> $data['journal_voucher_token'].$val['record_id'],
									);
									$this->AcctJournalVoucher_model->insertAcctJournalVoucherItem($data_credit);
								}
							}

							$data 	= '';
							$auth 	= $this->session->userdata('auth');
							$sesi 	= $this->session->userdata('unique');
							$msg 	= "
							<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Jurnal Umum Sukses
							</div> ";
							$this->session->set_userdata('addacctjournalvoucher-'.$sesi['unique'], $data);
							$this->session->set_userdata('addacctjournalvoucheritem-'.$sesi['unique'], $data);
							$this->session->set_userdata('acctjournalvouchertoken-'.$sesi['unique'], $data);
							$this->session->unset_userdata('addacctjournalvoucher-'.$sesi['unique']);
							$this->session->unset_userdata('addacctjournalvoucheritem-'.$sesi['unique']);
							$this->session->unset_userdata('acctjournalvouchertoken-'.$sesi['unique']);
							$this->session->set_userdata('message',$msg);
							redirect('journal-voucher');
						}else{
							$msg = "
							<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Jurnal Umum Tidak Berhasil
							</div> ";
							$this->session->set_userdata('message',$msg);
							$this->session->set_userdata('addacctjournalvoucher',$data);
							redirect('journal-voucher/add');
						}
					} else {
						$journal_voucher_id = $this->AcctJournalVoucher_model->getJournalVoucherID($data['created_id']);

						foreach ($acctjournalvoucheritem as $key => $val) {
							if($val['journal_voucher_status'] == 0){
								$data_debet =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $val['account_id'],
									'journal_voucher_description'	=> $data['journal_voucher_description'],
									'journal_voucher_amount'		=> $val['journal_voucher_amount'],
									'journal_voucher_debit_amount'	=> $val['journal_voucher_amount'],
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> $data['journal_voucher_token'].$val['account_id'],
								);

								$journal_voucher_item_token = $this->AcctJournalVoucher_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

								if($journal_voucher_item_token->num_rows() == 0){
									$this->AcctJournalVoucher_model->insertAcctJournalVoucherItem($data_debet);
								}
							} else {
								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $val['account_id'],
									'journal_voucher_description'	=> $data['journal_voucher_description'],
									'journal_voucher_amount'		=> $val['journal_voucher_amount'],
									'journal_voucher_credit_amount'	=> $val['journal_voucher_amount'],
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> $data['journal_voucher_token'].$val['account_id'],
								);

								$journal_voucher_item_token = $this->AcctJournalVoucher_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

								if($journal_voucher_item_token->num_rows() == 0){
									$this->AcctJournalVoucher_model->insertAcctJournalVoucherItem($data_credit);
								}
							}
						}

						$data 	= '';
						$auth 	= $this->session->userdata('auth');
						$sesi 	= $this->session->userdata('unique');
						$msg 	= "
						<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Tambah Data Jurnal Umum Sukses
						</div> ";
						$this->session->set_userdata('addacctjournalvoucher-'.$sesi['unique'], $data);
						$this->session->set_userdata('addacctjournalvoucheritem-'.$sesi['unique'], $data);
						$this->session->set_userdata('acctjournalvouchertoken-'.$sesi['unique'], $data);
						$this->session->unset_userdata('addacctjournalvoucher-'.$sesi['unique']);
						$this->session->unset_userdata('addacctjournalvoucheritem-'.$sesi['unique']);
						$this->session->unset_userdata('acctjournalvouchertoken-'.$sesi['unique']);
						$this->session->set_userdata('message',$msg);
						redirect('journal-voucher');
					}
				} else {
					$msg = "
					<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							No. Perkiraan Masih Kosong
					</div> ";
					$this->session->set_userdata('message',$msg);
					$this->session->set_userdata('addacctjournalvoucher',$data);
					redirect('journal-voucher/add');
				}
			}else{
				$this->session->set_userdata('addacctjournalvoucher',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('journal-voucher/add');
			}
		}
		
		public function deleteAcctJournalVoucher(){
			if($this->AcctJournalVoucher_model->deleteAcctJournalVoucher($this->uri->segment(3))){
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>                 
							Hapus Data Mutasi Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('journal-voucher');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>                
							Hapus Data Mutasi Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('journal-voucher');
			}
		}

		public function processPrinting(){
			$auth 						= $this->session->userdata('auth'); 
			$journal_voucher_id 		= $this->uri->segment(3);

			$preferencecompany 			= $this->AcctJournalVoucher_model->getPreferenceCompany();
			$acctjournalvoucher 		= $this->AcctJournalVoucher_model->getAcctJournalVoucher_Detail($journal_voucher_id);
			$acctjournalvoucheritem 	= $this->AcctJournalVoucher_model->getAcctJournalVoucherItem_Detail($journal_voucher_id);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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
			$pdf->SetFont('helvetica', '', 10);

			// -----------------------------------------------------------------------------
			
			$total_debet 	= 0;
			$total_kredit 	= 0;

			foreach ($acctjournalvoucheritem as $key => $val) {
				$total_debet 	+= $val['journal_voucher_debit_amount'];
				$total_kredit 	+= $val['journal_voucher_credit_amount'];
				$account_group	= $this->AcctJournalVoucher_model->getAccountGroup($val['account_id']);

				if($account_group == 11010100 || $account_group == 11010200){
					if($val['account_id_status'] == 0){
						$keterangan = "MASUK";
						$diterima 	= "Diterima dari";
						$ttd 		= "Penyetor";
					}else{
						$keterangan = "KELUAR";
						$diterima 	= "Diterima oleh";
						$ttd 		= "Penerima";
					}
				}
			}

			if($total_debet > $total_kredit){
				$nominal = $total_debet;
			}else{
				$nominal = $total_kredit;
			}

			$base_url = base_url();
			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
				<tr>
					<td width=\"70%\" style=\"font-weight: bold\">KOPERASI MENJANGAN ENAM</td>
					<td width=\"10%\">No. Bukti</td>
					<td width=\"2%\">:</td>
					<td width=\"18%\">".$acctjournalvoucher['proof_no']."</td>
				</tr>
				<tr>
				 	<td width=\"70%\">JL. SIMONGAN NO.131 SEMARANG Telp. 024.7607330</td>
					<td width=\"10%\">No. Jurnal</td>
					<td width=\"2%\">:	</td>
					<td width=\"18%\">".$acctjournalvoucher['journal_voucher_no']."</td>
			    </tr>
				<br/>
				<tr>
					<td width=\"100%\" align=\"center\" style=\"font-size: 18; font-weight: bold; text-decoration: underline\">BUKTI KAS / BANK ".$keterangan."</td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			
			$tbl1 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">".$diterima."</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: </div></td>
				</tr>
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">Jumlah Rp. </div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: ".number_format($nominal, 2)."</div></td>
				</tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".numtotxt($nominal)."</div></td>
			    </tr>
				<br/>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctjournalvoucher['journal_voucher_description']."</div></td>
			    </tr>
			</table>";

			$tbl2 = "
			<br>
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
				<tr>
					<td width=\"60%\">
						<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
							<tr>
								<td width=\"25%\" align=\"center\">Diperiksa oleh</td>
								<td width=\"25%\" align=\"center\">Disetujui oleh</td>
								<td width=\"25%\" align=\"center\">Diterima oleh</td>
								<td width=\"25%\" align=\"center\">Dibuku oleh</td>
							</tr>
						</table>
					</td>
					<td width=\"40%\" align=\"center\">
						Semarang, ".date("d-m-Y", strtotime($acctjournalvoucher['journal_voucher_date']))."
					</td>
				</tr>
				<tr>
					<td width=\"60%\">
						<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
							<tr>
								<td width=\"25%\" align=\"center\" height=\"50px\"></td>
								<td width=\"25%\" align=\"center\"></td>
								<td width=\"25%\" align=\"center\"></td>
								<td width=\"25%\" align=\"center\"></td>
							</tr>
						</table>
					</td>
					<td width=\"40%\" align=\"center\">
						".$ttd."
					</td>
				</tr>
				<tr>
					<td width=\"60%\"></td>
					<td width=\"40%\" align=\"center\">
						(.....................................)
					</td>
				</tr>
			</table>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
				<tr>
					<td width=\"10%\"><div style=\"text-align: center;font-weight: bold\">No. Rek</div></td>
					<td width=\"20%\"><div style=\"text-align: center;font-weight: bold\">Nama Rek</div></td>
					<td width=\"15%\"><div style=\"text-align: center;font-weight: bold\">Debet</div></td>
					<td width=\"15%\"><div style=\"text-align: center;font-weight: bold\">Kredit</div></td>
				</tr>
			";
			$no =1;
			foreach ($acctjournalvoucheritem as $key => $val) {
				$tbl3 .= "
					    <tr>
					        <td width=\"10%\"><div style=\"text-align: center;font-size:12px\">".$val['account_code']."</div></td>
					        <td width=\"20%\"><div style=\"text-align: left;font-size:12px\">".$val['account_name']."</div></td>
					        <td width=\"15%\"><div style=\"text-align: right;font-size:12px\">".($val['journal_voucher_debit_amount']==0?"":number_format($val['journal_voucher_debit_amount'] ,2))."</div></td>
					        <td width=\"15%\"><div style=\"text-align: right;font-size:12px\">".($val['journal_voucher_credit_amount']==0?"":number_format($val['journal_voucher_credit_amount'] ,2))."</div></td>
					    </tr>
				";
				$no++;
			}
			$tbl4 = "	
			</table>";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			$filename = 'Jurnal_'.$acctjournalvoucher['journal_voucher_no'].'_'.$acctjournalvoucher['journal_voucher_date'].'.pdf';
			
			$pdf->Output($filename, 'I');
		}

		public function processPrintingOld(){
			$auth 	=	$this->session->userdata('auth'); 
			
			$journal_voucher_id 		= $this->uri->segment(3);

			$preferencecompany 			= $this->AcctJournalVoucher_model->getPreferenceCompany();
			$acctjournalvoucher 		= $this->AcctJournalVoucher_model->getAcctJournalVoucher_Detail($journal_voucher_id);
			$acctjournalvoucheritem 	= $this->AcctJournalVoucher_model->getAcctJournalVoucherItem_Detail($journal_voucher_id);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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
			$pdf->SetFont('helvetica', '', 10);

			// -----------------------------------------------------------------------------

			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			    	<td rowspan=\"3\" width=\"30%\">".$img."</td>
			        <td><div style=\"text-align: center; font-size:14px;font-weight: bold\">JURNAL UMUM</div></td>
			    </tr>
			     <tr>
			        <td><div style=\"text-align: center; font-size:10px\">".$acctjournalvoucher['branch_name']."</div></td>
			    </tr>
			    <tr>
			        <td><div style=\"text-align: center; font-size:10px\">Jam : ".date('H:i:s')."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			
			$tbl1 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Tanggal Jurnal</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".tgltoview($acctjournalvoucher['journal_voucher_date'])."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">No. Jurnal</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctjournalvoucher['journal_voucher_no']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Uraian</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctjournalvoucher['journal_voucher_description']."</div></td>
			    </tr>		
			</table>";

			$tbl2 = "
			<br>
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
				<tr>
					<td width=\"5%\"><div style=\"text-align: center;font-weight: bold\">No.</div></td>
					<td width=\"40%\"><div style=\"text-align: center;font-weight: bold\">Perkiraan</div></td>
					<td width=\"20%\"><div style=\"text-align: center;font-weight: bold\">Debet</div></td>
					<td width=\"20%\"><div style=\"text-align: center;font-weight: bold\">Kredit</div></td>
				</tr>
			";
			$no =1;
			foreach ($acctjournalvoucheritem as $key => $val) {
				$tbl3 .= "
					    <tr>
					        <td width=\"5%\"><div style=\"text-align: center;font-size:12px\">".$no."</div></td>
					        <td width=\"40%\"><div style=\"text-align: left;font-size:12px\">(".$val['account_code'].") ".$val['account_name']."</div></td>
					        <td width=\"20%\"><div style=\"text-align: right;font-size:12px\">".number_format($val['journal_voucher_debit_amount'],2)."</div></td>
					        <td width=\"20%\"><div style=\"text-align: right;font-size:12px\">".number_format($val['journal_voucher_credit_amount'],2)."</div></td>
					    </tr>
				";
				$total_debet += $val['journal_voucher_debit_amount'];
				$total_kredit += $val['journal_voucher_credit_amount'];
				$no++;
			}
			$tbl4 = "
				<tr>
			        <td width=\"5%\"><div style=\"text-align: center;font-size:12px\"></div></td>
			        <td width=\"40%\"><div style=\"text-align: left;font-size:12px\"></div></td>
			        <td width=\"20%\"><div style=\"text-align: right;font-size:12px\"></div></td>
			        <td width=\"20%\"><div style=\"text-align: right;font-size:12px\"></div></td>
			    </tr>		
			</table>

			<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
				<tr>
					<td colspan=\"2\" width=\"45%\"></td>
					<td width=\"20%\"><div style=\"text-align: right;font-weight:bold\">".number_format($total_debet, 2)."</div></td>
					<td width=\"20%\"><div style=\"text-align: right;font-weight:bold\">".number_format($total_kredit, 2)."</div></td>
				</tr>
			</table>";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			$filename = 'Jurnal_'.$acctjournalvoucher['journal_voucher_no'].'_'.$acctjournalvoucher['journal_voucher_date'].'.pdf';
			
			$pdf->Output($filename, 'I');
		}
		
		public function repaymentAcctJournalVoucher($journal_voucher_id){
			$this->session->set_userdata('journal_voucher_id', $journal_voucher_id);

			redirect('journal-voucher/add');
		}
	}
?>
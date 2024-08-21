<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctCashPayments extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('MainPage_model');
			$this->load->model('AcctCashPayment_model');
			$this->load->model('AcctCreditAccount_model');
			$this->load->model('AcctSavingsAccount_model');
			$this->load->model('AcctSavingsCashMutation_model');
			$this->load->model('AcctSavingsTransferMutation_model');
			$this->load->model('CoreMember_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi'); 
			// $this->load->library('../controllers/AcctCreditAccount');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}

		public function indAcctCashPayment(){
			$auth 	= $this->session->userdata('auth');
			$unique = $this->session->userdata('unique');
			$this->session->unset_userdata('acctcreditspaymentcashtoken-'.$unique['unique']);

			$data['main_view']['acctcredits']	= create_double($this->AcctCreditAccount_model->getAcctCredits(),'credits_id', 'credits_name');
			$data['main_view']['corebranch']	= create_double($this->AcctCreditAccount_model->getCoreBranch(),'branch_id', 'branch_name');
			$data['main_view']['content']		= 'AcctCashPayment/ListAcctCashPayment_view';
			$this->load->view('MainPage_view', $data);
		}

		public function filteracctcashpayment(){
			$data = array (  
				'start_date'	=> tgltodb($this->input->post('start_date', true)),
				'end_date'		=> tgltodb($this->input->post('end_date', true)),
				'credits_id'	=> $this->input->post('credits_id', true),
				'branch_id'		=> $this->input->post('branch_id', true),
			);

			$this->session->set_userdata('filter-acctcashpayment', $data);
			redirect('cash-payments/ind-cash-payment');
		}

		public function reset(){
			$this->session->unset_userdata('filter-acctcashpayment');
			redirect('cash-payments/ind-cash-payment');
		}

		public function getAcctCashPayment(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctcashpayment');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['credits_id']		='';
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}
			} else {
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}
			}

			$list = $this->AcctCashPayment_model->get_datatables($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id'], 0);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $cashpayment) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $cashpayment->credits_account_serial;
	            $row[] = $cashpayment->member_name;
	            $row[] = $cashpayment->credits_name;
	            $row[] = tgltoview($cashpayment->credits_payment_date);
	            $row[] = number_format($cashpayment->credits_payment_principal, 2);
	            $row[] = number_format($cashpayment->credits_payment_interest, 2);
	            $row[] = number_format($cashpayment->credits_payment_fine, 2);
			    $row[] = '<a href="'.base_url().'cash-payments/print-note/'.$cashpayment->credits_payment_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Kwitansi</a>';
	            $data[] = $row;
	        }

	        $output = array(
	                        "draw" 				=> $_POST['draw'],
	                        "recordsTotal" 		=> $this->AcctCashPayment_model->count_all($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id'], 0),
	                        "recordsFiltered" 	=> $this->AcctCashPayment_model->count_filtered($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id'], 0),
	                        "data" 				=> $data,
	                );
	        //output to json format
	        echo json_encode($output);
		}

		function rate3($nprest, $vlrparc, $vp, $guess = 0.25) {
			$maxit = 100;
			$precision = 14;
			$guess = round($guess,$precision);
			for ($i=0 ; $i<$maxit ; $i++) {
				$divdnd = $vlrparc - ( $vlrparc * (pow(1 + $guess , -$nprest)) ) - ($vp * $guess);
				$divisor = $nprest * $vlrparc * pow(1 + $guess , (-$nprest - 1)) - $vp;
				$newguess = $guess - ( $divdnd / $divisor );
				$newguess = round($newguess, $precision);
				if ($newguess == $guess) {
					return $newguess;
				} else {
					$guess = $newguess;
				}
			}
			return null;
		}
		
		public function anuitas($id){
			$creditsaccount 	= $this->AcctCreditAccount_model->getCreditsAccount_Detail($id);

			$pinjaman 	= $creditsaccount['credits_account_amount'];
			$bunga 		= $creditsaccount['credits_account_interest'] / 100;
			$period 	= $creditsaccount['credits_account_period'];



			$bungaA 		= pow((1 + $bunga), $period);
			$bungaB 		= pow((1 + $bunga), $period) - 1;
			$bAnuitas 		= ($bungaA / $bungaB);
			// $totangsuran 	= $pinjaman * $bunga * $bAnuitas;
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
				// $angsuranpokok		= $pinjaman * (($bunga)/(1-(1+$bunga)-$i));
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

		public function addAcctCashPayment(){
			$credits_account_id 	= $this->uri->segment(3);

			$unique = $this->session->userdata('unique');
			$token 	= $this->session->userdata('acctcreditspaymentcashtoken-'.$unique['unique']);

			$token = md5(date('Y-m-d H:i:s'));
			$this->session->set_userdata('acctcreditspaymentcashtoken-'.$unique['unique'], $token);

			$accountcredit	= $this->AcctCreditAccount_model->getDetailByID($credits_account_id);

			if($accountcredit['payment_type_id'] == 2){
				$anuitas = $this->anuitas($accountcredit['credits_account_id']);
				$data['main_view']['anuitas']			= $anuitas;
			}

			if($accountcredit['payment_type_id'] == 3){
				$slidingrate 	= $this->slidingrate($accountcredit['credits_account_id']);
				$angsuranke 	= substr($accountcredit['credits_account_payment_to'], -1) + 1;
				$payment_amount = $slidingrate[$angsuranke]['angsuran_bunga'] + $slidingrate[$angsuranke]['angsuran_pokok'];
				$data['main_view']['slidingrate']			= $slidingrate;
			}

			if($accountcredit['payment_type_id'] == 4){
				$last_pokok		= $this->AcctCashPayment_model->getAcctCreditsPaymentsPokokLast($accountcredit['credits_account_id']);
				$last_payment	= $this->AcctCashPayment_model->getAcctCreditsPaymentsLast($accountcredit['credits_account_id']);
				if($last_pokok){
					$start_date 		= tgltodb($last_pokok['credits_payment_date']);
					$end_date 			= date('Y-m-d', strtotime("+1 months", strtotime($start_date)));
					$date1				= new DateTime($last_pokok['credits_payment_date']);
					$date2				= new DateTime($end_date);
					$date3				= new DateTime(date('Y-m-d'));
					$interval_month		= $date1->diff($date2);
					$interval_payments	= $date1->diff($date3);
					if($last_payment){
						$date4 				= new DateTime($last_payment['credits_payment_date']);
						$interval_payments	= $date4->diff($date3);
					}
					$interest_month 	= $accountcredit['credits_account_last_balance'] * $accountcredit['credits_account_interest']/100;
					$angsuran_bunga 	= $interest_month / $interval_month->days * $interval_payments->days;
				}else{
					$start_date 		= tgltodb($accountcredit['credits_account_date']);
					$end_date 			= date('Y-m-d', strtotime("+1 months", strtotime($start_date)));
					$date1				= new DateTime($accountcredit['credits_account_date']);
					$date2				= new DateTime($end_date);
					$date3				= new DateTime(date('Y-m-d'));
					$interval_month		= $date1->diff($date2);
					$interval_payments	= $date1->diff($date3);
					if($last_payment){
						$date4 				= new DateTime($last_payment['credits_payment_date']);
						$interval_payments	= $date4->diff($date3);
					}
					$interest_month 	= $accountcredit['credits_account_last_balance'] * $accountcredit['credits_account_interest']/100;
					$angsuran_bunga 	= $interest_month / $interval_month->days * $interval_payments->days;
				}

				$data['main_view']['angsuran_bunga_menurunharian']		=  $angsuran_bunga;
			}
			
			$data['main_view']['accountcredit']			= $accountcredit;
			$data['main_view']['detailpayment']			= $this->AcctCashPayment_model->getDataByIDCredit($credits_account_id);
			$data['main_view']['content']				= 'AcctCashPayment/FormAddAcctCashPayment2_view';
			$this->load->view('MainPage_view',$data);
		} 
		
		public function getDetailPayment(){
			$credits_account_id 				= $this->input->get('credits_account_id');
			
			$data['detailpayment']				= $this->AcctCashPayment_model->getDataByIDCredit($credits_account_id);
			$data['content']					= 'AcctCashPayment/ListPaymentByCreditAccount_view';
			$this->load->view('AcctCashPayment/ListPaymentByCreditAccount_view',$data);
		}

		public function akadlisttunai(){
			$auth 	= $this->session->userdata('auth');
			$list 	= $this->AcctCreditAccount_model->get_datatables($auth['branch_id']);
	        $data 	= array();
	        $no 	= $_POST['start'];
	        foreach ($list as $customers) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $customers->credits_account_serial;
	            $row[] = $customers->member_name;
	            $row[] = $customers->member_no;
	            $row[] = tgltoview($customers->credits_account_date);
	            $row[] = tgltoview($customers->credits_account_due_date);
	             $row[] = '<a href="'.base_url().'cash-payments/add/'.$customers->credits_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	    
	            $data[] = $row;
	        }
	 
	        $output = array(
				"draw" 				=> $_POST['draw'],
				"recordsTotal" 		=> $this->AcctCreditAccount_model->count_all($auth['branch_id']),
				"recordsFiltered" 	=> $this->AcctCreditAccount_model->count_filtered($auth['branch_id']),
				"data" 				=> $data,
			);
	        echo json_encode($output);
			
		}

		public function processAddAcctCashPayment(){
			$unique 			= $this->session->userdata('unique');
			$auth 				= $this->session->userdata('auth');

			$total_angsuran 	= $this->input->post('jangka_waktu', true);
			$angsuran_ke 		= $this->input->post('credits_payment_to', true);
			$angsuran_tiap 		= $this->input->post('credits_payment_period', true);
			$payment_type_id	= $this->input->post('payment_type_id', true);
			
			if($angsuran_ke < $total_angsuran){
				if($angsuran_tiap == 1){
					$credits_account_payment_date_old 	= tgltodb($this->input->post('credits_account_payment_date'));
					$credits_account_payment_date 		= date('Y-m-d', strtotime("+1 months", strtotime($credits_account_payment_date_old)));
				} else {
					$credits_account_payment_date_old 	= tgltodb($this->input->post('credits_account_payment_date'));
					$credits_account_payment_date 		= date('Y-m-d', strtotime("+1 weeks", strtotime($credits_account_payment_date_old)));
				}
			}

			$data = array(
				'branch_id'									=> $auth['branch_id'],
				'member_id'									=> $this->input->post('member_id', true),
				'credits_id'								=> $this->input->post('credits_id', true),
				'credits_account_id'						=> $this->input->post('credits_account_id', true),
				'credits_payment_date'						=> date('Y-m-d'),
				'credits_payment_amount'					=> $this->input->post('angsuran_total', true),
				'credits_payment_principal'					=> $this->input->post('angsuran_pokok', true),
				'credits_payment_interest'					=> $this->input->post('angsuran_interest', true),
				'credits_others_income'						=> $this->input->post('others_income', true),
				'credits_principal_opening_balance'			=> $this->input->post('sisa_pokok_awal', true),
				'credits_principal_last_balance'			=> $this->input->post('sisa_pokok_awal', true) - $this->input->post('angsuran_pokok', true),
				'credits_interest_opening_balance'			=> $this->input->post('sisa_bunga_awal', true),
				'credits_interest_last_balance'				=> $this->input->post('sisa_bunga_awal', true) + $this->input->post('angsuran_interest', true),				
				'credits_payment_fine'						=> $this->input->post('credits_payment_fine', true),
				'credits_account_payment_date'				=> $credits_account_payment_date,
				'credits_payment_to'						=> $this->input->post('credits_payment_to', true),
				'credits_payment_day_of_delay'				=> $this->input->post('credits_payment_day_of_delay', true),
				'credits_payment_token'						=> $this->input->post('credits_payment_token', true),
				'created_id'								=> $auth['user_id'],
				'created_on'								=> date('Y-m-d H:i:s'),
			);

			$acctcreditsaccount 	= $this->AcctCreditAccount_model->getCreditsAccount_Detail2($data['credits_account_id']);
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

			$member_mandatory_savings = $this->input->post('member_mandatory_savings', true);
			
			$this->form_validation->set_rules('angsuran_pokok', 'Pembayaran Pokok', 'required');

			$transaction_module_code 	= 'ANGS';
			$transaction_module_id 		= $this->AcctCreditAccount_model->getTransactionModuleID($transaction_module_code);
			$preferencecompany 			= $this->AcctCreditAccount_model->getPreferenceCompany();

			$journal_voucher_period 	= date("Ym", strtotime($data['credits_payment_date']));
			
			$credits_payment_token 		= $this->AcctCashPayment_model->getCreditsPaymentToken($data['credits_payment_token']);

			if($this->form_validation->run()==true){
				if($credits_payment_token->num_rows() == 0){
					if($this->AcctCashPayment_model->insert($data)){
						$updatedata = array(
							"credits_account_last_balance" 					=> $data['credits_principal_last_balance'],
							"credits_account_last_payment_date"				=> $data['credits_payment_date'],
							"credits_account_interest_last_balance"			=> $data['credits_interest_last_balance'],
							"credits_account_payment_date"					=> $credits_account_payment_date,
							"credits_account_payment_to"					=> $data['credits_payment_to'],
							"credits_account_accumulated_fines"				=> $this->input->post('credits_account_accumulated_fines', true),
							'credits_account_status'						=> $credits_account_status,
						);

						$this->AcctCreditAccount_model->updatedata($updatedata,$data['credits_account_id']);

						if($member_mandatory_savings > 0 && $member_mandatory_savings != ''){

							$data_detail = array (
								'branch_id'						=> $auth['branch_id'],
								'member_id'						=> $data['member_id'],
								'mutation_id'					=> 1,
								'transaction_date'				=> date('Y-m-d'),
								'mandatory_savings_amount'		=> $member_mandatory_savings,
								'operated_name'					=> $auth['username'],
								'savings_member_detail_token'	=> $data['credits_payment_token'],
							);

							$this->AcctCashPayment_model->insertAcctSavingsMemberDetail($data_detail);
						}

						$acctcashpayment_last 				= $this->AcctCashPayment_model->AcctCashPaymentLast($data['created_id']);

						if($data['credits_id'] != 99){
							$data_journal = array(
								'branch_id'						=> $auth['branch_id'],
								'journal_voucher_period' 		=> $journal_voucher_period,
								'journal_voucher_date'			=> date('Y-m-d'),
								'journal_voucher_title'			=> 'ANGSURAN TUNAI '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
								'journal_voucher_description'	=> 'ANGSURAN TUNAI '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
								'journal_voucher_token'			=> $data['credits_payment_token'],
								'transaction_module_id'			=> $transaction_module_id,
								'transaction_module_code'		=> $transaction_module_code,
								'transaction_journal_id' 		=> $acctcashpayment_last['credits_payment_id'],
								'transaction_journal_no' 		=> $acctcashpayment_last['credits_account_serial'],
								'created_id' 					=> $data['created_id'],
								'created_on' 					=> $data['created_on'],
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);

							$journal_voucher_id 				= $this->AcctCreditAccount_model->getJournalVoucherID($data['created_id']);
							$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
							
							if($data['credits_others_income']!='' && $data['credits_others_income'] > 0){
								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_others_income_id']);
		
								$data_credit = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_others_income_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data['credits_others_income'],
									'journal_voucher_credit_amount'	=> $data['credits_others_income'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> $data['credits_payment_token'].$preferencecompany['account_others_income_id'],
									'created_id' 					=> $auth['user_id'],
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
							}

							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

							$data_debet = array (
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_cash_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data['credits_payment_amount'],
								'journal_voucher_debit_amount'	=> $data['credits_payment_amount'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 0,
								'journal_voucher_item_token'	=> $data['credits_payment_token'].$preferencecompany['account_cash_id'],
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
								'created_id' 					=> $auth['user_id']
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
								'created_id' 					=> $auth['user_id']
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);

							if($data['credits_payment_fine'] > 0){
								$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
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

							if($member_mandatory_savings > 0 && $member_mandatory_savings != ''){
								$preferencecompany 	= $this->AcctCreditAccount_model->getPreferenceCompany();
								$savings_id 		= $preferencecompany['mandatory_savings_id'];
								$account_id 		= $this->AcctCreditAccount_model->getAccountIdFromSavings($savings_id);
								$account_id_default_status = $this->AcctCreditAccount_model->getAccountIDDefaultStatus($account_id);

								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_id,
									'journal_voucher_description'	=> 'SETORAN TUNAI '.$acctcashpayment_last['member_name'],
									'journal_voucher_amount'		=> $member_mandatory_savings,
									'journal_voucher_credit_amount'	=> $member_mandatory_savings,
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> $data['credits_payment_token'].$account_id,
									'created_id' 					=> $auth['user_id'],
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}else{
							$data_journal = array(
								'company_id'                    => 1,
								'journal_voucher_status'        => 1,
								'journal_voucher_description'   => 'ANGSURAN TUNAI '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
								'journal_voucher_title'         => 'ANGSURAN TUNAI '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
								'transaction_module_id'         => $transaction_module_id,
								'transaction_module_code'       => $transaction_module_code,
								'journal_voucher_date'          => date('Y-m-d'),
								'transaction_journal_no'        => $acctcashpayment_last['credits_account_serial'],
								'journal_voucher_period'        => $journal_voucher_period,
								'updated_id'                    => $data['created_id'],
								'created_id'                    => $data['created_id']
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucherMinimarket($data_journal);

							$journal_voucher_id 				= $this->AcctCreditAccount_model->getJournalVoucherIDMinimarket($data['created_id']);
							
							if($data['credits_others_income']!='' && $data['credits_others_income'] > 0){
								$account_others_income_id 			= 001;
								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_others_income_id);
		
								$data_credit = array (
									'company_id'                    => 1,
									'journal_voucher_id'            => $journal_voucher_id,
									'account_id'                    => $account_others_income_id,
									'journal_voucher_amount'        => $data['credits_others_income'],
									'journal_voucher_credit_amount' => $data['credits_others_income'],
									'account_id_default_status'     => $account_id_default_status,
									'account_id_status'             => 1,
									'updated_id'                    => $auth['user_id'],
									'created_id'                    => $auth['user_id']
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);
							}

							$account_cash_id 					= 001;
							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_cash_id);

							$data_debet = array (
								'company_id'                    => 1,
								'journal_voucher_id'            => $journal_voucher_id,
								'account_id'                    => $account_cash_id,
								'journal_voucher_amount'        => $data['credits_payment_amount'],
								'journal_voucher_debit_amount'  => $data['credits_payment_amount'],
								'account_id_default_status'     => $account_id_default_status,
								'account_id_status'             => 0,
								'updated_id'                    => $auth['user_id'],
								'created_id'                    => $auth['user_id']
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_debet);

							$receivable_account_id 				= 001;
							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($receivable_account_id);

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

							$account_interest_id				= 001;
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
								$account_credits_payment_fine		= 001;
								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_credits_payment_fine);

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

							if($member_mandatory_savings > 0 && $member_mandatory_savings != ''){
								$savings_id 		= $preferencecompany['mandatory_savings_id'];
								$account_id 		= $this->AcctCreditAccount_model->getAccountIdFromSavings($savings_id);
								$account_id_default_status = $this->AcctCreditAccount_model->getAccountIDDefaultStatusMinimarket($account_id);

								$data_credit =array(
									'company_id'                    => 1,
									'journal_voucher_id'            => $journal_voucher_id,
									'account_id'                    => $account_id,
									'journal_voucher_amount'        => $member_mandatory_savings,
									'journal_voucher_credit_amount' => $member_mandatory_savings,
									'account_id_default_status'     => $account_id_default_status,
									'account_id_status'             => 1,
									'updated_id'                    => $auth['user_id'],
									'created_id'                    => $auth['user_id']
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);
							}
						}
						
						$auth = $this->session->userdata('auth');
						$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Pembayaran Pinjaman Sukses
								</div> ";
						$sesi = $this->session->userdata('unique');
						$this->session->unset_userdata('addacctcashpayment-'.$sesi['unique']);
						$this->session->unset_userdata('acctcreditspaymentcashtoken-'.$sesi['unique']);
						$this->session->set_userdata('message',$msg);
						
						echo $acctcashpayment_last['credits_payment_id'];
					}else{
						$this->session->set_userdata('addacctcashpayment-'.$unique['unique'],$data);
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Pembayaran Pinjaman Tidak Berhasil
								</div> ";
						$this->session->set_userdata('message',$msg);
						redirect('cash-payments/add');
					}
				} else {
					$acctcashpayment_last 				= $this->AcctCashPayment_model->AcctCashPaymentLast($data['created_id']);

					$data_journal = array(
						'branch_id'						=> $auth['branch_id'],
						'journal_voucher_period' 		=> $journal_voucher_period,
						'journal_voucher_date'			=> date('Y-m-d'),
						'journal_voucher_title'			=> 'ANGSURAN TUNAI '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
						'journal_voucher_description'	=> 'ANGSURAN TUNAI '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
						'journal_voucher_token'			=> $data['credits_payment_token'],
						'transaction_module_id'			=> $transaction_module_id,
						'transaction_module_code'		=> $transaction_module_code,
						'transaction_journal_id' 		=> $acctcashpayment_last['credits_payment_id'],
						'transaction_journal_no' 		=> $acctcashpayment_last['credits_account_serial'],
						'created_id' 					=> $data['created_id'],
						'created_on' 					=> $data['created_on'],
					);
					
					$journal_voucher_token 				= $this->AcctCreditAccount_model->getJournalVoucherToken($data_journal['journal_voucher_token']);

					if($journal_voucher_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);
					}

					$journal_voucher_id 				= $this->AcctCreditAccount_model->getJournalVoucherID($data['created_id']);
					$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();


					if($data['credits_others_income']!='' && $data['credits_others_income'] > 0){

						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_others_income_id']);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_others_income_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data['credits_others_income'],
							'journal_voucher_credit_amount'	=> $data['credits_others_income'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['credits_payment_token'].$preferencecompany['account_others_income_id'],
							'created_id' 					=> $auth['user_id'],
						);

						$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);
	
						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}

					}

					$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

					$data_debet = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_cash_id'],
						'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
						'journal_voucher_amount'		=> $data['credits_payment_amount'],
						'journal_voucher_debit_amount'	=> $data['credits_payment_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 0,
						'journal_voucher_item_token'	=> $data['credits_payment_token'].$preferencecompany['account_cash_id'],
						'created_id' 					=> $auth['user_id'],
					);

					$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);
					}

					$receivable_account_id 				= $this->AcctCreditAccount_model->getReceivableAccountID($data['credits_id']);

					$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($receivable_account_id);

					$data_credit = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $receivable_account_id,
						'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
						'journal_voucher_amount'		=> $data['credits_payment_amount'],
						'journal_voucher_credit_amount'	=> $data['credits_payment_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'journal_voucher_item_token'	=> $data['credits_payment_token'].$receivable_account_id,
						'created_id' 					=> $auth['user_id'],
					);

					$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
					}

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

					$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
					}

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

						$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}

					if($member_mandatory_savings > 0 && $member_mandatory_savings != ''){
	
						$account_id = $this->CoreMember_model->getAccountID($preferencecompany['mandatory_savings_id']);

						$account_id_default_status = $this->AcctCreditAccount_model->getAccountIDDefaultStatus($account_id);

						$data_credit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_id,
							'journal_voucher_description'	=> 'SETORAN TUNAI '.$acctcashpayment_last['member_name'],
							'journal_voucher_amount'		=> $member_mandatory_savings,
							'journal_voucher_credit_amount'	=> $member_mandatory_savings,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> $data['credits_payment_token'].$account_id,
							'created_id' 					=> $auth['user_id'],
						);

						$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}


					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Pembayaran Pinjaman Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctcashpayment-'.$sesi['unique']);
					$this->session->unset_userdata('acctcreditspaymentcashtoken-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					
					// redirect('cash-payments/print-note/'.$acctcashpayment_last['credits_payment_id']);
					echo $acctcashpayment_last['credits_payment_id'];
					
				}
				
				
			}else{
				$this->session->set_userdata('addacctcashpayment-'.$unique['unique'],$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('cash-payments/add');
				
			}
		}

		public function printNoteCashPayment(){
			$auth = $this->session->userdata('auth');
			$credits_payment_id 	= $this->uri->segment(3);
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();
			$acctcreditspayment	 	= $this->AcctCashPayment_model->getAcctCreditspayment_Detail($credits_payment_id);


			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			// create new PDF document
			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7); // put space of 10 on top

			// set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			// set some language-dependent strings (optional)
			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			// set font
			$pdf->SetFont('helvetica', 'B', 20);

			// add a page
			$pdf->AddPage();

			/*$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);*/

			$pdf->SetFont('helvetica', '', 10);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			    	<td rowspan=\"2\" width=\"20%\">".$img."</td>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN ANGSURAN</div></td>
			    </tr>
			    <tr>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			
			if(substr($acctcreditspayment['credits_payment_to'], -1) == "*"){
				$keperluan = ": CICILAN ANGSURAN PEMBIAYAAN KE ".$acctcreditspayment['credits_payment_to'];
			}else{
				$keperluan = ": ANGSURAN PEMBIAYAAN KE ".$acctcreditspayment['credits_payment_to'];
			}

			$tbl1 = "
			Telah diterima dari :
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['member_name']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">No. Akad</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['credits_account_serial']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['member_address']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: ".numtotxt($acctcreditspayment['credits_payment_amount'])."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Keterangan</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">".$keperluan."</div></td>
			    </tr>
			     <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctcreditspayment['credits_payment_amount'], 2)."</div></td>
			    </tr>				
			</table>";

			$tbl2 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			    	<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctCreditAccount_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
			    </tr>
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: center;\">Penyetor</div></td>
			        <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
			    </tr>				
			</table>";

			$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			//Close and output PDF document
			$filename = 'Kwitansi.pdf';
			$pdf->Output($filename, 'I');

			//============================================================+
			// END OF FILE
			//============================================================+
		}

		public function indCashLessPayment(){
			$auth = $this->session->userdata('auth');

			$data['main_view']['acctcredits']	= create_double($this->AcctCreditAccount_model->getAcctCredits(),'credits_id', 'credits_name');
			$data['main_view']['corebranch']	= create_double($this->AcctCreditAccount_model->getCoreBranch(),'branch_id', 'branch_name');
			$data['main_view']['content']		= 'AcctCashPayment/ListAcctNonCashPayment_view';
			$this->load->view('MainPage_view', $data);
		}

		public function filteracctcashlesspayment(){
			$data = array (
				'start_date'	=> tgltodb($this->input->post('start_date', true)),
				'end_date'		=> tgltodb($this->input->post('end_date', true)),
				'credits_id'	=> $this->input->post('credits_id', true),
				'branch_id'		=> $this->input->post('branch_id', true),
			);

			$this->session->set_userdata('filter-acctcashpaymentless', $data);
			redirect('cash-payments/ind-cash-less-payment');
		}

		public function reset_cashless(){
			$this->session->unset_userdata('filter-acctcashpaymentless');
			redirect('cash-payments/ind-cash-less-payment');
		}

		public function getAcctCashLessPayment(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctcashpaymentless');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['credits_id']		='';
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}
			} else {
				if($auth['branch_status'] == 1){
					$sesi['branch_id']	= '';
				}
				if($auth['branch_status'] == 0){
					$sesi['branch_id']	= $auth['branch_id'];
				}

				/*print_r(" Sesi");*/
			}

			$list = $this->AcctCashPayment_model->get_datatables($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id'], 1);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $cashpayment) {
	            $no++;
	            	 $row = array();
		            $row[] = $no;
		            $row[] = $cashpayment->credits_account_serial;
		            $row[] = $cashpayment->member_name;
		            $row[] = $cashpayment->credits_name;
		            $row[] = $this->AcctCashPayment_model->getSavingsAccountNO($cashpayment->savings_account_id);
		            $row[] = tgltoview($cashpayment->credits_payment_date);
		            $row[] = number_format($cashpayment->credits_payment_principal, 2);
		            $row[] = number_format($cashpayment->credits_payment_interest, 2);
		            $row[] = number_format($cashpayment->credits_payment_fine, 2);
				    $row[] = '<a href="'.base_url().'cash-payments/print-note-less/'.$cashpayment->credits_payment_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Kwitansi</a>';
		            $data[] = $row;
	        }

	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctCashPayment_model->count_all($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id'], 1),
	                        "recordsFiltered" => $this->AcctCashPayment_model->count_filtered($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id'], 1),
	                        "data" => $data,
	                );
	        //output to json format
	        echo json_encode($output);
		}

		public function addCashlessPayment(){	
			$id3 	= $this->uri->segment(3);
			$id4 	= $this->uri->segment(4);
			$unique = $this->session->userdata('unique');
			$token 	= $this->session->userdata('acctcreditspaymentcashtoken-'.$unique['unique']);

			$token = md5(date('Y-m-d H:i:s'));
			$this->session->set_userdata('acctcreditspaymentlesstoken-', $token);

			$data['main_view']['credit_account'] = "";
			$data['main_view']['saving_account'] = "";

				$creditaccount 	= $this->AcctCreditAccount_model->getDetailByID($id3);

				if($creditaccount['payment_type_id'] == 2){
					$anuitas = $this->anuitas($creditaccount['credits_account_id']);
					$data['main_view']['anuitas']		= $anuitas;
				}

				if($creditaccount['payment_type_id'] == 3){
					$slidingrate 	= $this->slidingrate($creditaccount['credits_account_id']);
					$angsuranke 	= substr($creditaccount['credits_account_payment_to'], -1) + 1;
					$payment_amount = $slidingrate[$angsuranke]['angsuran_bunga'] + $slidingrate[$angsuranke]['angsuran_pokok'];

					$data['main_view']['slidingrate']	= $slidingrate;
				}

				if($creditaccount['payment_type_id'] == 4){
					$last_pokok		= $this->AcctCashPayment_model->getAcctCreditsPaymentsPokokLast($creditaccount['credits_account_id']);
					$last_payment	= $this->AcctCashPayment_model->getAcctCreditsPaymentsLast($creditaccount['credits_account_id']);
					if($last_pokok){
						$start_date 		= tgltodb($last_pokok['credits_payment_date']);
						$end_date 			= date('Y-m-d', strtotime("+1 months", strtotime($start_date)));
						$date1				= new DateTime($last_pokok['credits_payment_date']);
						$date2				= new DateTime($end_date);
						$date3				= new DateTime(date('Y-m-d'));
						$interval_month		= $date1->diff($date2);
						$interval_payments	= $date1->diff($date3);
						if($last_payment){
							$date4 				= new DateTime($last_payment['credits_payment_date']);
							$interval_payments	= $date4->diff($date3);
						}
						$interest_month 	= $creditaccount['credits_account_last_balance'] * $creditaccount['credits_account_interest']/100;
						$angsuran_bunga 	= $interest_month / $interval_month->days * $interval_payments->days;
					}else{
						$start_date 		= tgltodb($creditaccount['credits_account_date']);
						$end_date 			= date('Y-m-d', strtotime("+1 months", strtotime($start_date)));
						$date1				= new DateTime($creditaccount['credits_account_date']);
						$date2				= new DateTime($end_date);
						$date3				= new DateTime(date('Y-m-d'));
						$interval_month		= $date1->diff($date2);
						$interval_payments	= $date1->diff($date3);
						if($last_payment){
							$date4 				= new DateTime($last_payment['credits_payment_date']);
							$interval_payments	= $date4->diff($date3);
						}
						$interest_month 	= $creditaccount['credits_account_last_balance'] * $creditaccount['credits_account_interest']/100;
						$angsuran_bunga 	= $interest_month / $interval_month->days * $interval_payments->days;
					}
	
					$data['main_view']['angsuran_bunga_menurunharian']		=  $angsuran_bunga;
				}
				$data['main_view']['credit_account'] 	= $creditaccount;
			if($id4 != ""){
				$data['main_view']['saving_account'] 	= $this->AcctSavingsAccount_model->getAcctSavingsAccount_Detail($id4);
			}
			$data['main_view']['detailpayment']			= $this->AcctCashPayment_model->getDataByIDCredit($id3);
			$data['main_view']['content']				= 'AcctCashPayment/FormAddCashPaymentNon_view';
			$this->load->view('MainPage_view',$data);
		}

		public function simpananlist(){
			$auth 		= $this->session->userdata('auth');

			$list 		= $this->AcctSavingsAccount_model->get_datatables($auth['branch_id']);
			$data 		= array();
			$segment3 	= $this->uri->segment(3);
			$segment4 	= $this->uri->segment(4);
			$no 		= $_POST['start'];

			foreach ($list as $customers) {
				$no++;
				$row = array();
				$row[] = $no;
				$row[] = $customers->member_no;
				$row[] = $customers->member_name;
				$row[] = $customers->savings_account_no;
				$row[] = '<a href="'.base_url().'cash-payments/add-cash-less/'.$segment3.'/'.$customers->savings_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
			
				$data[] = $row;
			}
	
			$output = array(
							"draw" 				=> $_POST['draw'],
							"recordsTotal" 		=> $this->AcctSavingsAccount_model->count_all($auth['branch_id']),
							"recordsFiltered" 	=> $this->AcctSavingsAccount_model->count_filtered($auth['branch_id']),
							"data" 				=> $data,
					);
			//output to json format
			echo json_encode($output);
		}		

		public function akadlist(){
			$auth = $this->session->userdata('auth');
			$list = $this->AcctCreditAccount_model->get_datatables($auth['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
			$segment3=$this->uri->segment(3);
			$segment4=$this->uri->segment(4);
	        foreach ($list as $customers) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $customers->credits_account_serial;
	            $row[] = $customers->member_name;
	            $row[] = $customers->member_no;
	            $row[] = tgltoview($customers->credits_account_date);
	            $row[] = tgltoview($customers->credits_account_due_date);
	             $row[] = '<a href="'.base_url().'cash-payments/add-cash-less/'.$customers->credits_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	    
	            $data[] = $row;
	        }
	 
	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctCreditAccount_model->count_all($auth['branch_id']),
	                        "recordsFiltered" => $this->AcctCreditAccount_model->count_filtered($auth['branch_id']),
	                        "data" => $data,
	                );
	        echo json_encode($output);
			
		}

		public function getCreditAccountDetail(){
			$credits_account_id 	= $this->input->post('credits_account_id');

			// $credits_account_id = 18;
			
			$data 			= $this->AcctCreditAccount_model->getDetailByID($credits_account_id);

			if($data['payment_type_id'] == 1){
				$angsuranpokok 		= $data['credits_account_principal_amount'];
				$angsuranbunga 	 	= $data['credits_account_interest_amount'];
			} else if($data['payment_type_id'] == 2){
				$angsuranbunga 	 	= ($data['credits_account_last_balance'] * $data['credits_account_interest']) /100;
				$angsuranpokok 		= $data['credits_account_payment_amount'] - $angsuranbunga;
			}

			// print_r($data['credits_account_last_balance']);

			$result = array();
			$result = array(
				"member_id"						=> $data['member_id'],
				"credits_id"					=> $data['credits_id'],
				"pembiayaan" 					=> $data['member_name'], 
				"member_name"					=> $data['member_name'],
				"member_address"				=> $data['member_address'],
				"city_name"						=> $data['city_name'],
				"kecamatan_name"				=> $data['kecamatan_name'],
				"identity_name"					=> $data['identity_name'],
				"member_identity_no"			=> $data['member_identity_no'],
				"jangka_waktu"					=> $data['credits_account_period'],
				"jatuh_tempo"					=> $data['credits_account_due_date'],
				"tanggal_realisasi"				=> $data['credits_account_date'],
				"payment_amount"				=> $data['credits_account_payment_amount'],
				"sisa_pokok"					=> $data['credits_account_last_balance_principal'],
				"sisa_interest"					=> $data['credits_account_last_balance_interest'],
				"jumlah_angsuran"				=> $data['credits_account_payment_amount'],
				"angsuran_pokok"				=> $angsuranpokok,
				"angsuran_interest"				=> $angsuranbunga,
				"saldo_piutang"					=> $data['credits_account_last_balance_principal']+$data['credits_account_last_balance_interest'],
			);
			echo json_encode($result);		
		}

		public function AcctCashPaymentsProcess(){
			$unique 								= $this->session->userdata('unique');
			$auth 									= $this->session->userdata('auth');
			$norek 									= $this->input->post('savings_account_id');
			$pokok 									= $this->input->post('credits_payment_principal');
			$interest 								= $this->input->post('credits_payment_interest');
			$others_income 							= $this->input->post('others_income',true);
			$id_pinjaman 							= $this->input->post('credits_account_id',true);
			$total 									= $this->input->post('total',true);
			$credits_account_temp_installment 		= $this->input->post('credits_account_temp_installment',true);
			$simpanan 								= $this->AcctSavingsAccount_model->getAcctSavingsAccount_Detail($norek);
			$pinjaman 								= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($id_pinjaman);
			$last_balance 							= $pinjaman['credits_account_last_balance']-$pokok;
			$member_mandatory_savings 				= $this->input->post('member_mandatory_savings', true);
			$payment_type_id						= $this->input->post('payment_type_id', true);

			if($simpanan['savings_account_last_balance'] < $total){
				$auth 	= $this->session->userdata('auth');
				$msg 	= "<div class='alert alert-danger alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tabungan tidak cukup
							</div> "; 
				$sesi = $this->session->userdata('unique');
				$this->session->unset_userdata('addCashlessPayment-'.$sesi['unique']);
				$this->session->set_userdata('message',$msg);
				echo 'error';
				return false;
			}

			$total_angsuran 		= $this->input->post('jangka_waktu', true);
			$angsuran_ke 			= $this->input->post('credits_payment_to', true);
			$angsuran_tiap 			= $this->input->post('credits_payment_period', true);

			if($angsuran_ke < $total_angsuran){
				if($angsuran_tiap == 1){
					$credits_account_payment_date_old 	= tgltodb($this->input->post('credits_account_payment_date'));
					$credits_account_payment_date 		= date('Y-m-d', strtotime("+1 months", strtotime($credits_account_payment_date_old)));
				} else {
					$credits_account_payment_date_old 	= tgltodb($this->input->post('credits_account_payment_date'));
					$credits_account_payment_date 		= date('Y-m-d', strtotime("+1 weeks", strtotime($credits_account_payment_date_old)));
				}
				
			}

			$data_cash = array(
				'branch_id'									=> $auth['branch_id'],
				'member_id'									=> $simpanan['member_id'],
				'credits_id'								=> $this->input->post('credits_id', true),
				'credits_account_id'						=> $this->input->post('credits_account_id', true),
				'savings_account_id'						=> $this->input->post('savings_account_id', true),
				'credits_payment_date'						=> date('Y-m-d'),
				'credits_payment_amount'					=> $total,
				'credits_payment_principal'					=> $pokok,
				'credits_payment_interest'					=> $interest,
				'credits_others_income'						=> $others_income,
				'credits_principal_opening_balance'			=> $pinjaman['credits_account_last_balance'],
				'credits_principal_last_balance'			=> $last_balance,
				'credits_interest_opening_balance'			=> $this->input->post('sisa_bunga_awal', true),				
				'credits_interest_last_balance'				=> $this->input->post('sisa_bunga_awal', true) + $this->input->post('credits_payment_interest'),
				'credits_account_payment_date'				=> $credits_account_payment_date,
				'credits_payment_to'						=> $this->input->post('credits_payment_to', true),
				'credits_payment_day_of_delay'				=> $this->input->post('credits_payment_day_of_delay', true),
				'credits_payment_fine'						=> $this->input->post('credits_payment_fine', true),
				'credits_payment_type'						=> 1,
				'credits_payment_token'						=> $this->input->post('credits_payment_token', true),
				'created_id'								=> $auth['user_id'],
				'created_on'								=> date('Y-m-d H:i:s'),
			);
			
			$acctcreditsaccount 	= $this->AcctCreditAccount_model->getCreditsAccount_Detail2($data_cash['credits_account_id']);
			$credits_account_status = 0;

			if($payment_type_id == 4){
				if($data_cash['credits_principal_last_balance'] <= 0){
					$credits_account_status = 1;
				}
			}else{
				if($angsuran_ke == $total_angsuran){
					$credits_account_status = 1;
				}
			}

			$this->form_validation->set_rules('savings_account_id', 'No. Rekening Simpanan', 'required');
			$this->form_validation->set_rules('credits_payment_principal', 'Pembayaran Pokok', 'required');

			$transaction_module_code 	= 'ANGS';
			$transaction_module_id 		= $this->AcctCreditAccount_model->getTransactionModuleID($transaction_module_code);
			$preferencecompany 			= $this->AcctCreditAccount_model->getPreferenceCompany();
			$credits_payment_token 		= $this->AcctCashPayment_model->getCreditsPaymentToken($data_cash['credits_payment_token']);

			if($this->form_validation->run()==true){
				if($credits_payment_token->num_rows() == 0){
					if($this->AcctCashPayment_model->insert($data_cash)){
						$updatedata = array(
							"credits_account_last_balance" 					=> $data_cash['credits_principal_last_balance'],
							"credits_account_last_payment_date"				=> $data_cash['credits_payment_date'],
							"credits_account_payment_date"					=> $credits_account_payment_date,
							"credits_account_payment_to"					=> $this->input->post('credits_payment_to', true),
							"credits_account_interest_last_balance"			=> $data_cash['credits_interest_last_balance'],
							"credits_account_accumulated_fines"				=> $this->input->post('credits_account_accumulated_fines', true),
							"credits_account_status"						=> $credits_account_status,
						);
						$this->AcctCreditAccount_model->updatedata($updatedata,$data_cash['credits_account_id']);

						$update_saving = array(
							"savings_account_last_balance" => $simpanan['savings_account_last_balance'] - $total
						);
						$this->AcctSavingsAccount_model->updatedata($update_saving,$norek);

						$last_balance 	= ($simpanan['savings_account_last_balance']) - $total;
						$mutasi_data 	=array(
							"savings_account_id" 					=> $norek,
							"savings_id" 							=> $simpanan['savings_id'],
							"member_id" 							=> $simpanan['member_id'],
							"branch_id" 							=> $auth['branch_id'],
							"mutation_id" 							=> 4,
							"savings_cash_mutation_date" 			=> date('Y-m-d'),
							"savings_cash_mutation_opening_balance" => $simpanan['savings_account_last_balance'],
							"savings_cash_mutation_last_balance" 	=> $last_balance,
							"savings_cash_mutation_amount" 			=> $total,
							"savings_cash_mutation_remark"	 		=> "Pembayaran Kredit No.".$this->input->post('credits_account_serial',true),
							"savings_cash_mutation_token"			=> "LB".$data_cash['credits_payment_token'],
						);

						$this->AcctSavingsCashMutation_model->insertAcctSavingsCashMutation($mutasi_data);

						if($data_cash['credits_payment_fine'] > 0){
							$last_balance_after_fine 	= $last_balance - $data_cash['credits_payment_fine'];
							$mutasi_data 	=array(
								"savings_account_id" 					=> $norek,
								"savings_id" 							=> $simpanan['savings_id'],
								"member_id" 							=> $simpanan['member_id'],
								"branch_id" 							=> $auth['branch_id'],
								"mutation_id" 							=> 4,
								"savings_cash_mutation_date" 			=> date('Y-m-d'),
								"savings_cash_mutation_opening_balance" => $last_balance,
								"savings_cash_mutation_last_balance" 	=> $last_balance_after_fine,
								"savings_cash_mutation_amount" 			=> $data_cash['credits_payment_fine'],
								"savings_cash_mutation_remark"	 		=> "Pembayaran Denda Atas Kredit No.".$this->input->post('credits_account_serial',true),
								"savings_cash_mutation_token"			=> "PF".$data_cash['credits_payment_token'],
							);
							$this->AcctSavingsCashMutation_model->insertAcctSavingsCashMutation($mutasi_data);
						}

						if($member_mandatory_savings > 0 && $member_mandatory_savings != ''){
							$data_detail = array (
								'branch_id'						=> $auth['branch_id'],
								'member_id'						=> $data_cash['member_id'],
								'mutation_id'					=> 1,
								'transaction_date'				=> date('Y-m-d'),
								'mandatory_savings_amount'		=> $member_mandatory_savings,
								'operated_name'					=> $auth['username'],
								'savings_member_detail_token'	=> $data_cash['credits_payment_token'],
							);
							$this->AcctCashPayment_model->insertAcctSavingsMemberDetail($data_detail);
						}

						$acctcashpayment_last 	= $this->AcctCashPayment_model->AcctCashPaymentLast($data_cash['created_id']);
							
						$journal_voucher_period = date("Ym", strtotime($data_cash['credits_payment_date']));
						
						$data_journal = array(
							'branch_id'						=> $data_cash['branch_id'],
							'journal_voucher_period' 		=> $journal_voucher_period,
							'journal_voucher_date'			=> date('Y-m-d'),
							'journal_voucher_title'			=> 'ANGSURAN DEBET '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
							'journal_voucher_description'	=> 'ANGSURAN DEBET '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
							'transaction_module_id'			=> $transaction_module_id,
							'transaction_module_code'		=> $transaction_module_code,
							'transaction_journal_id' 		=> $acctcashpayment_last['credits_payment_id'],
							'transaction_journal_no' 		=> $acctcashpayment_last['credits_account_serial'],
							'created_id' 					=> $data_cash['created_id'],
							'created_on' 					=> $data_cash['created_on'],  
							'journal_voucher_token' 		=> $data_cash['credits_payment_token'],  
						);
						$this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);

						$journal_voucher_id 		= $this->AcctCreditAccount_model->getJournalVoucherID($data_cash['created_id']);
						$savingsaccount_id 			= $this->AcctCashPayment_model->getSavingsAccountID($mutasi_data['savings_id']);
						$account_id_default_status 	= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($savingsaccount_id);

						$data_debet = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $savingsaccount_id,
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data_cash['credits_payment_amount'],
							'journal_voucher_debit_amount'	=> $data_cash['credits_payment_amount'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'created_id' 					=> $auth['user_id'],
							'journal_voucher_token'			=> "SV".$data_cash['credits_payment_token'],
						);
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);

						$receivable_account_id 		= $this->AcctCreditAccount_model->getReceivableAccountID($acctcashpayment_last['credits_id']);

						$account_id_default_status 	= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($receivable_account_id);

						$data_credit = array (
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $receivable_account_id,
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data_cash['credits_payment_principal'],
							'journal_voucher_credit_amount'	=> $data_cash['credits_payment_principal'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'created_id' 					=> $auth['user_id'],
							'journal_voucher_token'			=> "RA".$data_cash['credits_payment_token'],
						);
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);

						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_interest_id']);

						$data_credit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_interest_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data_cash['credits_payment_interest'],
							'journal_voucher_credit_amount'	=> $data_cash['credits_payment_interest'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'created_id' 					=> $auth['user_id'],
							'journal_voucher_token'			=> "JS".$data_cash['credits_payment_token'],

						);
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);

						if($data_cash['credits_others_income'] > 0){
							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_others_income_id']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_others_income_id'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data_cash['credits_others_income'],
								'journal_voucher_credit_amount'	=> $data_cash['credits_others_income'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'created_id' 					=> $auth['user_id'],
								'journal_voucher_token'			=> "OI".$data_cash['credits_payment_token'],

							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}

						if($data_cash['credits_payment_fine'] > 0){
							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_credits_payment_fine']);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $preferencecompany['account_credits_payment_fine'],
								'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
								'journal_voucher_amount'		=> $data_cash['credits_payment_fine'],
								'journal_voucher_credit_amount'	=> $data_cash['credits_payment_fine'],
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'created_id' 					=> $auth['user_id'],
								'journal_voucher_token'			=> "PFC".$data_cash['credits_payment_token'],
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
						
						if($member_mandatory_savings > 0 && $member_mandatory_savings != ''){
							$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
							$savings_id 						= $preferencecompany['mandatory_savings_id'];
							$account_id 						= $this->AcctCreditAccount_model->getAccountIdFromSavings($savings_id);
							$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($account_id);

							$data_credit =array(
								'journal_voucher_id'			=> $journal_voucher_id,
								'account_id'					=> $account_id,
								'journal_voucher_description'	=> 'DEBET TABUNGAN '.$acctcashpayment_last['member_name'],
								'journal_voucher_amount'		=> $member_mandatory_savings,
								'journal_voucher_credit_amount'	=> $member_mandatory_savings,
								'account_id_default_status'		=> $account_id_default_status,
								'account_id_status'				=> 1,
								'journal_voucher_item_token'	=> "PL".$data_cash['credits_payment_token'].$account_id,
								'created_id' 					=> $auth['user_id'],
							);
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}

						$auth = $this->session->userdata('auth');
						$msg  = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Pembayaran Pinjaman Sukses
								</div> ";
						$sesi = $this->session->userdata('unique');
						$this->session->unset_userdata('addCashlessPayment-'.$sesi['unique']);
						$this->session->set_userdata('message',$msg);
						echo $acctcashpayment_last['credits_payment_id'];
					}else{
						$this->session->set_userdata('addacctcashpayment-'.$unique['unique'],$data_cash);
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Pembayaran Pinjaman Tidak Berhasil
								</div> ";
						echo 'error';
						return false;
					}	
				}else{
					$last_balance 	= ($simpanan['savings_account_last_balance']) - $total;
					$mutasi_data 	=array(
						"savings_account_id" 					=> $norek,
						"savings_id" 							=> $simpanan['savings_id'],
						"member_id" 							=> $simpanan['member_id'],
						"branch_id" 							=> $auth['branch_id'],
						"mutation_id" 							=> 4,
						"savings_cash_mutation_date" 			=> date('Y-m-d'),
						"savings_cash_mutation_opening_balance" => $simpanan['savings_account_last_balance'],
						"savings_cash_mutation_last_balance" 	=> $last_balance,
						"savings_cash_mutation_amount" 			=> $total,
						"savings_cash_mutation_remark"	 		=> "Pembayaran Kredit No.".$this->input->post('credits_account_serial',true),
						"savings_cash_mutation_token"			=> "LB".$data_cash['credits_payment_token'],
					);

					$token = $this->AcctSavingsCashMutation_model->getSavingsCashMutationToken("LB".$data_cash['credits_payment_token']);
					if($token->num_rows() == 0){
						$this->AcctSavingsCashMutation_model->insertAcctSavingsCashMutation($mutasi_data);
					}

					if($data_cash['credits_payment_fine'] > 0){
						$last_balance_after_fine 	= $last_balance - $data_cash['credits_payment_fine'];
						$mutasi_data 	=array(
							"savings_account_id" 					=> $norek,
							"savings_id" 							=> $simpanan['savings_id'],
							"member_id" 							=> $simpanan['member_id'],
							"branch_id" 							=> $auth['branch_id'],
							"mutation_id" 							=> 4,
							"savings_cash_mutation_date" 			=> date('Y-m-d'),
							"savings_cash_mutation_opening_balance" => $last_balance,
							"savings_cash_mutation_last_balance" 	=> $last_balance_after_fine,
							"savings_cash_mutation_amount" 			=> $data_cash['credits_payment_fine'],
							"savings_cash_mutation_remark"	 		=> "Pembayaran Denda Atas Kredit No.".$this->input->post('credits_account_serial',true),
							"savings_cash_mutation_token"			=> "PF".$data_cash['credits_payment_token'],
						);

						$token = $this->AcctSavingsCashMutation_model->getSavingsCashMutationToken("PF".$data_cash['credits_payment_token']);
						if($token->num_rows() == 0){
							$this->AcctSavingsCashMutation_model->insertAcctSavingsCashMutation($mutasi_data);
						}
					}

					if($member_mandatory_savings > 0 && $member_mandatory_savings != ''){
						$data_detail = array (
							'branch_id'						=> $auth['branch_id'],
							'member_id'						=> $data_cash['member_id'],
							'mutation_id'					=> 1,
							'transaction_date'				=> date('Y-m-d'),
							'mandatory_savings_amount'		=> $member_mandatory_savings,
							'operated_name'					=> $auth['username'],
							'savings_member_detail_token'	=> $data_cash['credits_payment_token'],
						);
						$token = $this->AcctSavingsCashMutation_model->getCreditsPaymentToken($data_cash['credits_payment_token']);
						if($token->num_rows() == 0){
							$this->AcctCashPayment_model->insertAcctSavingsMemberDetail($data_detail);
						}
					}

					$acctcashpayment_last 	= $this->AcctCashPayment_model->AcctCashPaymentLast($data_cash['created_id']);
						
					$journal_voucher_period = date("Ym", strtotime($data_cash['credits_payment_date']));
					
					$data_journal = array(
						'branch_id'						=> $data_cash['branch_id'],
						'journal_voucher_period' 		=> $journal_voucher_period,
						'journal_voucher_date'			=> date('Y-m-d'),
						'journal_voucher_title'			=> 'ANGSURAN DEBET '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
						'journal_voucher_description'	=> 'ANGSURAN DEBET '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
						'transaction_module_id'			=> $transaction_module_id,
						'transaction_module_code'		=> $transaction_module_code,
						'transaction_journal_id' 		=> $acctcashpayment_last['credits_payment_id'],
						'transaction_journal_no' 		=> $acctcashpayment_last['credits_account_serial'],
						'created_id' 					=> $data_cash['created_id'],
						'created_on' 					=> $data_cash['created_on'],  
						'journal_voucher_token' 		=> $data_cash['credits_payment_token'],  
					);

					
				
					$journal_voucher_token 				= $this->AcctCreditAccount_model->getJournalVoucherToken($data_journal['journal_voucher_token']);

					if($journal_voucher_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);
					}

					$journal_voucher_id 		= $this->AcctCreditAccount_model->getJournalVoucherID($data_cash['created_id']);

					$savingsaccount_id 			= $this->AcctCashPayment_model->getSavingsAccountID($mutasi_data['savings_id']);

					$account_id_default_status 	= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($savingsaccount_id);

					$data_debet = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $savingsaccount_id,
						'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
						'journal_voucher_amount'		=> $data_cash['credits_payment_amount'],
						'journal_voucher_debit_amount'	=> $data_cash['credits_payment_amount'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 0,
						'created_id' 					=> $auth['user_id'],
						'journal_voucher_item_token'	=> "SV".$data_cash['credits_payment_token'],
					);
					$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);
					}

					$receivable_account_id 		= $this->AcctCreditAccount_model->getReceivableAccountID($acctcashpayment_last['credits_id']);

					$account_id_default_status 	= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($receivable_account_id);

					$data_credit = array (
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $receivable_account_id,
						'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
						'journal_voucher_amount'		=> $data_cash['credits_payment_principal'],
						'journal_voucher_credit_amount'	=> $data_cash['credits_payment_principal'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'created_id' 					=> $auth['user_id'],
						'journal_voucher_token'			=> "RA".$data_cash['credits_payment_token'],
					);
					$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
					}

					$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_interest_id']);

					$data_credit =array(
						'journal_voucher_id'			=> $journal_voucher_id,
						'account_id'					=> $preferencecompany['account_interest_id'],
						'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
						'journal_voucher_amount'		=> $data_cash['credits_payment_interest'],
						'journal_voucher_credit_amount'	=> $data_cash['credits_payment_interest'],
						'account_id_default_status'		=> $account_id_default_status,
						'account_id_status'				=> 1,
						'created_id' 					=> $auth['user_id'],
						'journal_voucher_item_token'	=> "JS".$data_cash['credits_payment_token'],

					);

					$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
					}

					if($data_cash['credits_others_income'] > 0){

						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_others_income_id']);

						$data_credit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_others_income_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data_cash['credits_others_income'],
							'journal_voucher_credit_amount'	=> $data_cash['credits_others_income'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'created_id' 					=> $auth['user_id'],
							'journal_voucher_item_token'	=> "OI".$data_cash['credits_payment_token'],
						);
						$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}

					if($data_cash['credits_payment_fine'] > 0){

						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($savingsaccount_id);

						$data_debit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $savingsaccount_id,
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data_cash['credits_payment_fine'],
							'journal_voucher_debit_amount'	=> $data_cash['credits_payment_fine'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'created_id' 					=> $auth['user_id'],
							'journal_voucher_item_token'	=> "PF".$data_cash['credits_payment_token'],

						);
						$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_debit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debit);
						}

						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_credits_payment_fine']);

						$data_credit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_credits_payment_fine'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data_cash['credits_payment_fine'],
							'journal_voucher_credit_amount'	=> $data_cash['credits_payment_fine'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'created_id' 					=> $auth['user_id'],
							'journal_voucher_item_token'			=> "PFC".$data_cash['credits_payment_token'],
						);

						$$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}

					}
					
					if($member_mandatory_savings > 0 && $member_mandatory_savings != ''){
						$preferencecompany 					= $this->AcctCreditAccount_model->getPreferenceCompany();
				
						$savings_id = $preferencecompany['mandatory_savings_id'];
						$account_id = $this->AcctCreditAccount_model->getAccountIdFromSavings($savings_id);
						$account_id_default_status = $this->AcctCreditAccount_model->getAccountIDDefaultStatus($account_id);

						$data_credit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $account_id,
							'journal_voucher_description'	=> 'DEBET TABUNGAN '.$acctcashpayment_last['member_name'],
							'journal_voucher_amount'		=> $member_mandatory_savings,
							'journal_voucher_credit_amount'	=> $member_mandatory_savings,
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 1,
							'journal_voucher_item_token'	=> "PL".$data_cash['credits_payment_token'].$account_id,
							'created_id' 					=> $auth['user_id'],
						);

						$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_credit['journal_voucher_item_token']);

						if($journal_voucher_item_token->num_rows()==0){
							$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Pembayaran Pinjaman Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addCashlessPayment-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					// redirect('cash-payments/add-cash-less');
					echo $acctcashpayment_last['credits_payment_id'];
				}
			}else{
				$this->session->set_userdata('addacctcashpayment-'.$unique['unique'],$data_cash);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('cash-payments/add-cash-less');
			}
		}

		public function printNoteCashLessPayment(){
			$auth = $this->session->userdata('auth');
			$credits_payment_id 	= $this->uri->segment(3);
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();
			$acctcreditspayment	 	= $this->AcctCashPayment_model->getAcctCreditspayment_Detail($credits_payment_id);


			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');

			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7); // put space of 10 on top

			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			// set font
			$pdf->SetFont('helvetica', 'B', 20);

			// add a page
			$pdf->AddPage();

			$pdf->SetFont('helvetica', '', 10);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			    	<td rowspan=\"2\" width=\"20%\">".$img."</td>
			        <td width=\"50%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN ANGSURAN DEBET</div></td>
			    </tr>
			    <tr>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			
			if(substr($acctcreditspayment['credits_payment_to'], -1) == "*"){
				$keperluan = ": CICILAN ANGSURAN PEMBIAYAAN KE ".$acctcreditspayment['credits_payment_to'];
			}else{
				$keperluan = ": ANGSURAN PEMBIAYAAN KE ".$acctcreditspayment['credits_payment_to'];
			}
			$tbl1 = "
			Telah diterima dari :
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['member_name']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">No. Akad</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['credits_account_serial']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">No. Rek. Simpanan</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: ".$this->AcctCashPayment_model->getSavingsAccountNO($acctcreditspayment['savings_account_id'])."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['member_address']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: ".numtotxt($acctcreditspayment['credits_payment_amount'])."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Keterangan</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">".$keperluan."</div></td>
			    </tr>
			     <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
			        <td width=\"50%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctcreditspayment['credits_payment_amount'], 2)."</div></td>
			    </tr>				
			</table>";

			$tbl2 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			    	<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctCreditAccount_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
			    </tr>
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: center;\">Penyetor</div></td>
			        <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
			    </tr>				
			</table>";

			$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			//Close and output PDF document
			$filename = 'Kwitansi.pdf';
			$pdf->Output($filename, 'I');

			//============================================================+
			// END OF FILE
			//============================================================+
		}

		public function reset_data(){
			$unique 	= $this->session->userdata('unique');
			$sessions	= $this->session->unset_userdata('addacctcashpayment-'.$unique['unique']);
			redirect('cash-payments/process-add');
		}
		
		public function historyPayment(){
			$auth 	= $this->session->userdata('auth');
			$id3=$this->uri->segment(3);
			$data['main_view']['credit_account']="";
			if($id3 != ""){
				$data['main_view']['credit_account'] = $this->AcctCreditAccount_model->getDetailByID($id3);
			}
			$data['main_view']['acctcreditspayment']		=$this->AcctCreditAccount_model->getAcctCreditsPayment_Detail($id3);
			$data['main_view']['content']					= 'AcctCashPayment/ListHistoryAcctCreditsPayment_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function creditList(){
			$auth = $this->session->userdata('auth');
			$list = $this->AcctCreditAccount_model->get_datatables($auth['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
			$segment3=$this->uri->segment(3);
	        foreach ($list as $customers) {
	            $no++;
	            $row = array();
	            $row[] = $customers->credits_account_serial;
	            $row[] = $customers->member_name;
	            $row[] = $customers->member_no;
	             $row[] = '<a href="'.base_url().'cash-payments/history-payment/'.$customers->credits_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	    
	            $data[] = $row;
	        }
	 
	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctCreditAccount_model->count_all($auth['branch_id']),
	                        "recordsFiltered" => $this->AcctCreditAccount_model->count_filtered($auth['branch_id']),
	                        "data" => $data,
	                );
	        echo json_encode($output);
			
		}
	}
?>
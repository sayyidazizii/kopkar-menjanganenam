<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctSalaryPayments extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('MainPage_model');
			$this->load->model('AcctSalaryPayment_model');
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
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}

		public function indAcctSalaryPayment(){
			$auth 	= $this->session->userdata('auth');
			$unique = $this->session->userdata('unique');
			$this->session->unset_userdata('acctcreditspaymentcashtoken-'.$unique['unique']);

			$data['main_view']['acctcredits']	= create_double($this->AcctCreditAccount_model->getAcctCredits(),'credits_id', 'credits_name');
			$data['main_view']['corebranch']	= create_double($this->AcctCreditAccount_model->getCoreBranch(),'branch_id', 'branch_name');
			$data['main_view']['content']		= 'AcctSalaryPayment/ListAcctSalaryPayment_view';
			$this->load->view('MainPage_view', $data);
		}

		public function filterAcctSalaryPayment(){
			$data = array (
				'start_date'	=> tgltodb($this->input->post('start_date', true)),
				'end_date'		=> tgltodb($this->input->post('end_date', true)),
				'credits_id'	=> $this->input->post('credits_id', true),
				'branch_id'		=> $this->input->post('branch_id', true),
			);

			$this->session->set_userdata('filter-AcctSalaryPayment', $data);
			redirect('salary-payments/ind-salary-payment');
		}

		public function reset(){
			$this->session->unset_userdata('filter-AcctSalaryPayment');
			redirect('salary-payments/ind-salary-payment');
		}

		public function getAcctSalaryPayment(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-AcctSalaryPayment');
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

			$list = $this->AcctSalaryPayment_model->get_datatables($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']);
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
			    $row[] = '<a href="'.base_url().'salary-payments/print-note/'.$cashpayment->credits_payment_id.'" class="btn btn-xs blue" role="button"><i class="fa fa-print"></i> Kwitansi</a>';
	            $data[] = $row;
	        }

	        $output = array(
	                        "draw" 				=> $_POST['draw'],
	                        "recordsTotal" 		=> $this->AcctSalaryPayment_model->count_all($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']),
	                        "recordsFiltered" 	=> $this->AcctSalaryPayment_model->count_filtered($sesi['start_date'] , $sesi['end_date'], $sesi['credits_id'], $sesi['branch_id']),
	                        "data" 				=> $data,
	                );
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

		public function getDetailPayment(){
			$credits_account_id 				= $this->input->get('credits_account_id');
			
			$data['detailpayment']				= $this->AcctSalaryPayment_model->getDataByIDCredit($credits_account_id);
			$data['content']					= 'AcctSalaryPayment/ListPaymentByCreditAccount_view';
			$this->load->view('AcctSalaryPayment/ListPaymentByCreditAccount_view',$data);
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
	             $row[] = '<a href="'.base_url().'salary-payments/add/'.$customers->credits_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	    
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

		public function addAcctSalaryPayment(){	
			$unique 			= $this->session->userdata('unique');
			$token 				= $this->session->userdata('acctcreditspaymentcashtoken-'.$unique['unique']);
			$acctcreditsaccount = $this->AcctSalaryPayment_model->getAcctCreditsAccount();
			$token 				= md5(date('Y-m-d H:i:s'));

			$this->session->set_userdata('acctcreditspaymentcashtoken-', $token);

			foreach($acctcreditsaccount as $key => $val){
				$accountcredit	= $this->AcctCreditAccount_model->getDetailByID($val['credits_account_id']);

				if($accountcredit['payment_type_id'] == 2){
					$anuitas = $this->anuitas($accountcredit['credits_account_id']);
					$data['main_view']['anuitas'] = $anuitas;
				}
	
				if($accountcredit['payment_type_id'] == 3){
					$slidingrate 	= $this->slidingrate($accountcredit['credits_account_id']);
					$angsuranke 	= substr($accountcredit['credits_account_payment_to'], -1) + 1;
					$payment_amount = $slidingrate[$angsuranke]['angsuran_bunga'] + $slidingrate[$angsuranke]['angsuran_pokok'];
	
					$data['main_view']['slidingrate']	= $slidingrate;
				}
	
				if($accountcredit['payment_type_id'] == 4){
					$last_pokok		= $this->AcctSalaryPayment_model->getAcctCreditsPaymentsPokokLast($accountcredit['credits_account_id']);
					$last_payment	= $this->AcctSalaryPayment_model->getAcctCreditsPaymentsLast($accountcredit['credits_account_id']);
					if($last_pokok){
						$start_date 			= tgltodb($last_pokok['credits_payment_date']);
						$end_date 				= date('Y-m-d', strtotime("+1 months", strtotime($start_date)));
						$date1					= new DateTime($last_pokok['credits_payment_date']);
						$date2					= new DateTime($end_date);
						$date3					= new DateTime(date('Y-m-d'));
						$interval_month			= $date1->diff($date2);
						$interval_payments		= $date1->diff($date3);
						if($last_payment){
							$date4 				= new DateTime($last_payment['credits_payment_date']);
							$interval_payments	= $date4->diff($date3);
						}
						$interest_month 		= $accountcredit['credits_account_last_balance'] * $accountcredit['credits_account_interest']/100;
						$angsuran_bunga 		= $interest_month / $interval_month->days * $interval_payments->days;
					}else{
						$start_date 			= tgltodb($accountcredit['credits_account_date']);
						$end_date 				= date('Y-m-d', strtotime("+1 months", strtotime($start_date)));
						$date1					= new DateTime($accountcredit['credits_account_date']);
						$date2					= new DateTime($end_date);
						$date3					= new DateTime(date('Y-m-d'));
						$interval_month			= $date1->diff($date2);
						$interval_payments		= $date1->diff($date3);
						if($last_payment){
							$date4 				= new DateTime($last_payment['credits_payment_date']);
							$interval_payments	= $date4->diff($date3);
						}
						$interest_month 		= $accountcredit['credits_account_last_balance'] * $accountcredit['credits_account_interest']/100;
						$angsuran_bunga 		= $interest_month / $interval_month->days * $interval_payments->days;
					}
	
					$data['main_view']['angsuran_bunga_menurunharian']	= $angsuran_bunga;
				}
				$acctcreditsaccount[$key]['accountcredit'] = $accountcredit;
			}

			$data['main_view']['acctcreditsaccount']	= $acctcreditsaccount;

			// echo json_encode($data);
			// exit;
			$data['main_view']['content']				= 'AcctSalaryPayment/FormAddAcctSalaryPayment_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processAddAcctSalaryPayment(){
			$auth 				= $this->session->userdata('auth');    
			$unique 			= $this->session->userdata('unique');
			$acctcreditsaccount = $this->AcctSalaryPayment_model->getAcctCreditsAccount();

			foreach($acctcreditsaccount as $key => $val){
				$accountcredit	= $this->AcctCreditAccount_model->getDetailByID($val['credits_account_id']);

				if($accountcredit['payment_type_id'] == 2){
					$anuitas = $this->anuitas($accountcredit['credits_account_id']);
				}
	
				if($accountcredit['payment_type_id'] == 3){
					$slidingrate 	= $this->slidingrate($accountcredit['credits_account_id']);
					$angsuranke 	= substr($accountcredit['credits_account_payment_to'], -1) + 1;
					$payment_amount = $slidingrate[$angsuranke]['angsuran_bunga'] + $slidingrate[$angsuranke]['angsuran_pokok'];
				}

				if($accountcredit['payment_type_id'] == 4){
					$last_pokok		= $this->AcctSalaryPayment_model->getAcctCreditsPaymentsPokokLast($accountcredit['credits_account_id']);
					$last_payment	= $this->AcctSalaryPayment_model->getAcctCreditsPaymentsLast($accountcredit['credits_account_id']);
					if($last_pokok){
						$start_date 						= tgltodb($last_pokok['credits_payment_date']);
						$end_date 							= date('Y-m-d', strtotime("+1 months", strtotime($start_date)));
						$date1								= new DateTime($last_pokok['credits_payment_date']);
						$date2								= new DateTime($end_date);
						$date3								= new DateTime(date('Y-m-d'));
						$interval_month						= $date1->diff($date2);
						$interval_payments					= $date1->diff($date3);
						if($last_payment){			
							$date4 							= new DateTime($last_payment['credits_payment_date']);
							$interval_payments				= $date4->diff($date3);
						}			
						$interest_month 					= $accountcredit['credits_account_last_balance'] * $accountcredit['credits_account_interest']/100;			
						$angsuran_bunga 					= $interest_month / $interval_month->days * $interval_payments->days;
					}else{			
						$start_date 						= tgltodb($accountcredit['credits_account_date']);
						$end_date 							= date('Y-m-d', strtotime("+1 months", strtotime($start_date)));
						$date1								= new DateTime($accountcredit['credits_account_date']);
						$date2								= new DateTime($end_date);
						$date3								= new DateTime(date('Y-m-d'));
						$interval_month						= $date1->diff($date2);
						$interval_payments					= $date1->diff($date3);
						if($last_payment){			
							$date4 							= new DateTime($last_payment['credits_payment_date']);
							$interval_payments				= $date4->diff($date3);
						}			
						$interest_month 					= $accountcredit['credits_account_last_balance'] * $accountcredit['credits_account_interest']/100;
						$angsuran_bunga_menurunharian 		= $interest_month / $interval_month->days * $interval_payments->days;
					}
				}
				$credits_payment_date = date('Y-m-d');

				$date1 = date_create($credits_payment_date);
				$date2 = date_create($accountcredit['credits_account_payment_date']);

				if($date1 > $date2){
					$interval                       = $date1->diff($date2);
					$credits_payment_day_of_delay   = $interval->days;
				} else {
					$credits_payment_day_of_delay 	= 0;
				}

				$saldobunga = $accountcredit['credits_account_interest_last_balance'] + $accountcredit['credits_account_interest_amount'] ;
				
				$credits_payment_fine_amount 		= (($accountcredit['credits_account_payment_amount'] * $accountcredit['credits_fine']) / 100 ) * $credits_payment_day_of_delay;
				$credits_account_accumulated_fines 	= $accountcredit['credits_account_accumulated_fines'] + $credits_payment_fine_amount;

				if(strpos($accountcredit['credits_account_payment_to'], ',') == true ||strpos($accountcredit['credits_account_payment_to'], '*') == true ){
					$angsuranke 					= substr($accountcredit['credits_account_payment_to'], -1) + 1;
					}else{
						$angsuranke 				= $accountcredit['credits_account_payment_to'] + 1;
					}

				if($accountcredit['payment_type_id'] == 1){
					$angsuranpokok 		= $accountcredit['credits_account_principal_amount'];
					$angsuranbunga 	 	= $accountcredit['credits_account_payment_amount'] - $angsuranpokok;
				} else if($accountcredit['payment_type_id'] == 2){
					$angsuranpokok 		= $anuitas[$angsuranke]['angsuran_pokok'];
					$angsuranbunga 	 	= $accountcredit['credits_account_payment_amount'] - $angsuranpokok;
				} else if($accountcredit['payment_type_id'] == 3){
					$angsuranpokok 		= $slidingrate[$angsuranke]['angsuran_pokok'];
					$angsuranbunga 	 	= $accountcredit['credits_account_payment_amount'] - $angsuranpokok;
				} else if($accountcredit['payment_type_id'] == 4){
					$angsuranpokok		= 0;
					$angsuranbunga		= $angsuran_bunga_menurunharian;
				}

				$total_angsuran 						= $accountcredit['credits_account_period'];
				$angsuran_ke 							= $angsuranke;
				$angsuran_tiap							= $accountcredit['credits_payment_period'];
				$payment_type_id						= $accountcredit['payment_type_id'];

				if($angsuran_ke < $total_angsuran){
					if($angsuran_tiap == 1){
						$credits_account_payment_date_old 	= tgltodb($accountcredit['credits_account_payment_date']);
						$credits_account_payment_date 		= date('Y-m-d', strtotime("+1 months", strtotime($credits_account_payment_date_old)));
					} else {
						$credits_account_payment_date_old 	= tgltodb($accountcredit['credits_account_payment_date']);
						$credits_account_payment_date 		= date('Y-m-d', strtotime("+1 weeks", strtotime($credits_account_payment_date_old)));
					}
				}

				$data = array(
					'branch_id'									=> $auth['branch_id'],
					'member_id'									=> $accountcredit['member_id'],
					'salary_payment_status'						=> 1,
					'credits_id'								=> $accountcredit['credits_id'],
					'credits_account_id'						=> $accountcredit['credits_account_id'],
					'credits_payment_date'						=> date('Y-m-d'),
					'credits_payment_amount'					=> $accountcredit['credits_account_payment_amount'],
					'credits_payment_principal'					=> $angsuranpokok,
					'credits_payment_interest'					=> $angsuranbunga,
					'credits_principal_opening_balance'			=> $accountcredit['credits_account_last_balance'],
					'credits_principal_last_balance'			=> $accountcredit['credits_account_last_balance'] - $angsuranpokok,
					'credits_interest_opening_balance'			=> $accountcredit['credits_account_interest_last_balance'],
					'credits_interest_last_balance'				=> $accountcredit['credits_account_interest_last_balance'] + $angsuranbunga,
					'credits_payment_fine'						=> 0,
					'credits_account_payment_date'				=> $credits_account_payment_date,
					'credits_payment_to'						=> $angsuranke,
					'credits_payment_day_of_delay'				=> $credits_payment_day_of_delay,
					'credits_payment_token'						=> $this->input->post('credits_payment_token', true).$accountcredit['credits_account_id'],
					'created_id'								=> $auth['user_id'],
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

				if($credits_payment_token->num_rows() == 0){
					if($this->AcctSalaryPayment_model->insertTemp($data)){
						$updatedata = array(
							"credits_account_last_balance" 					=> $data['credits_principal_last_balance'],
							"credits_account_last_payment_date"				=> $data['credits_payment_date'],
							"credits_account_payment_date"					=> $credits_account_payment_date,
							"credits_account_payment_to"					=> $data['credits_payment_to'],
							"credits_account_interest_last_balance"			=> $data['credits_interest_last_balance'],
							"credits_account_status"						=> $credits_account_status,
							"credits_account_accumulated_fines"				=> 0,

						);
						//skip (temporary)
						// $this->AcctCreditAccount_model->updatedata($updatedata,$data['credits_account_id']);

						$AcctSalaryPayment_last 							= $this->AcctSalaryPayment_model->AcctSalaryPaymentLast($data['created_id']);
						
						if($data['credits_id'] != 99){
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
							// $this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);

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

							// $this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);

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

							// $this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);

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

							// $this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);

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

								// $this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
							}
						}else{
							$data_journal = array(
								'company_id'                    => 1,
								'journal_voucher_status'        => 1,
								'journal_voucher_description'   => 'ANGSURAN VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
								'journal_voucher_title'         => 'ANGSURAN VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
								'transaction_module_id'         => $transaction_module_id,
								'transaction_module_code'       => $transaction_module_code,
								'journal_voucher_date'          => date('Y-m-d'),
								'transaction_journal_no'        => $AcctSalaryPayment_last['credits_account_serial'],
								'journal_voucher_period'        => $journal_voucher_period,
								'updated_id'                    => $data['created_id'],
								'created_id'                    => $data['created_id']
							);
							// $this->AcctCreditAccount_model->insertAcctJournalVoucherMinimarket($data_journal);

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
							// $this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_debet);

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
							// $this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);

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
							// $this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);

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
								// $this->AcctCreditAccount_model->insertAcctJournalVoucherItemMinimarket($data_credit);
							}
						}
						
						$memberaccountdebt = $this->AcctSalaryPayment_model->getCoreMemberAccountReceivableAmount($data['member_id']);

						$member_account_receivable_amount = $memberaccountdebt['member_account_receivable_amount'] + $data['credits_payment_amount'];

						if($data['credits_id'] == 999){
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
						// $this->AcctSalaryPayment_model->updateCoreMember($data_member);
					}else{
						$this->session->set_userdata('addAcctSalaryPayment-'.$unique['unique'],$data);
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Data Pembayaran Pinjaman Tidak Berhasil
								</div> ";
						$this->session->set_userdata('message',$msg);
						redirect('salary-payments/add');
					}
				} else {
					$AcctSalaryPayment_last 				= $this->AcctSalaryPayment_model->AcctSalaryPaymentLast($data['created_id']);

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
					
					$journal_voucher_token 				= $this->AcctCreditAccount_model->getJournalVoucherToken($data_journal['journal_voucher_token']);

					if($journal_voucher_token->num_rows()==0){
						// $this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);
					}

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

					$journal_voucher_item_token 		= $this->AcctCreditAccount_model->getJournalVoucherItemToken($data_debet['journal_voucher_item_token']);

					if($journal_voucher_item_token->num_rows()==0){
						// $this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);
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
						// $this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
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
						// $this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
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
							// $this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
						}
					}

					$memberaccountdebt 					= $this->AcctSalaryPayment_model->getCoreMemberAccountReceivableAmount($data['member_id']);

					$member_account_receivable_amount 	= $memberaccountdebt['member_account_receivable_amount'] + $data['credits_payment_amount'];

					if($data['credits_id'] == 999){
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

					// $this->AcctSalaryPayment_model->updateCoreMember($data_member);
				}
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
			// redirect('salary-payments/ind-salary-payment');
			$this->printNoteSalaryPaymentProcess($this->input->post('credits_payment_token', true));
		}

		public function printNoteSalaryPayment(){
			$auth = $this->session->userdata('auth');
			$credits_payment_id 	= $this->uri->segment(3);
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();
			$acctcreditspayment	 	= $this->AcctSalaryPayment_model->getAcctCreditspayment_Detail($credits_payment_id);


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

			$pdf->SetFont('helvetica', 'B', 20);

			$pdf->AddPage();

			$pdf->SetFont('helvetica', '', 10);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
			    	<td rowspan=\"2\" width=\"20%\">".$img."</td>
			        <td width=\"50%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN ANGSURAN VIA POTONG GAJI</div></td>
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
			        <td width=\"70%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['member_name']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Bagian</div></td>
			        <td width=\"70%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['division_name']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">No. Akad</div></td>
			        <td width=\"70%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['credits_account_serial']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Jenis Pinjaman</div></td>
			        <td width=\"70%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['credits_name']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
			        <td width=\"70%\"><div style=\"text-align: left;\">: ".numtotxt($acctcreditspayment['credits_payment_amount'])."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Keterangan</div></td>
			        <td width=\"70%\"><div style=\"text-align: left;\">".$keperluan."</div></td>
			    </tr>
			     <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
			        <td width=\"70%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctcreditspayment['credits_payment_amount'], 2)."</div></td>
			    </tr>				
			</table>";

			$tbl2 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			    	<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctCreditAccount_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
			    </tr>
				<br>
				<br>
				<br>
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
			    </tr>				
			</table>";

			$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');

			ob_clean();

			$filename = 'Kwitansi.pdf';
			$pdf->Output($filename, 'I');
		}

		public function printNoteSalaryPaymentProcess($token){
			$auth 					= $this->session->userdata('auth');
			$preferencecompany 		= $this->AcctCreditAccount_model->getPreferenceCompany();
			$acctcreditspayment	 	= $this->AcctSalaryPayment_model->getAcctCreditsPaymentToken($token);

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

			$pdf->SetFont('helvetica', 'B', 20);

			$pdf->AddPage();

			$pdf->SetFont('helvetica', '', 10);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$no = 1;
			foreach($acctcreditspayment as $key => $val){
				$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

				$tbl = "
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
					<tr>
						<td rowspan=\"2\" width=\"20%\">".$img."</td>
						<td width=\"50%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN ANGSURAN VIA POTONG GAJI</div></td>
					</tr>
					<tr>
						<td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
					</tr>
				</table>";

				$pdf->writeHTML($tbl, true, false, false, false, '');
				
				if(substr($val['credits_payment_to'], -1) == "*"){
					$keperluan = ": CICILAN ANGSURAN PEMBIAYAAN KE ".$val['credits_payment_to'];
				}else{
					$keperluan = ": ANGSURAN PEMBIAYAAN KE ".$val['credits_payment_to'];
				}

				$tbl1 = "
				Telah diterima dari :
				<br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
						<td width=\"70%\"><div style=\"text-align: left;\">: ".$val['member_name']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Bagian</div></td>
						<td width=\"70%\"><div style=\"text-align: left;\">: ".$val['division_name']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">No. Akad</div></td>
						<td width=\"70%\"><div style=\"text-align: left;\">: ".$val['credits_account_serial']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Jenis Pinjaman</div></td>
						<td width=\"70%\"><div style=\"text-align: left;\">: ".$val['credits_name']."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
						<td width=\"70%\"><div style=\"text-align: left;\">: ".numtotxt($val['credits_payment_amount'])."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Keterangan</div></td>
						<td width=\"70%\"><div style=\"text-align: left;\">".$keperluan."</div></td>
					</tr>
					<tr>
						<td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
						<td width=\"70%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($val['credits_payment_amount'], 2)."</div></td>
					</tr>				
				</table>";

				$tbl2 = "
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
					<tr>
						<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"10%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"30%\"><div style=\"text-align: center;\">".$this->AcctCreditAccount_model->getBranchCity($auth['branch_id']).", ".date('d-m-Y')."</div></td>
					</tr>
					<br>
					<br>
					<br>
					<tr>
						<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"10%\"><div style=\"text-align: center;\"></div></td>
						<td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
					</tr>				
				</table>
				<br>
				<br>
				<br>
				<br>
				<br>";

				if($no % 3 == 0){
					$tbl2 .= "<br pagebreak=\"true\"/>";
				}

				$no++;
				
				$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');
			}

			ob_clean();

			$filename = 'Kwitansi.pdf';
			$pdf->Output($filename, 'I');
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
	             $row[] = '<a href="'.base_url().'salary-payments/add-cash-less-payment/'.$customers->credits_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	    
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

			$data 			= $this->AcctCreditAccount_model->getDetailByID($credits_account_id);

			if($data['payment_type_id'] == 1){
				$angsuranpokok 		= $data['credits_account_principal_amount'];
				$angsuranbunga 	 	= $data['credits_account_interest_amount'];
			} else if($data['payment_type_id'] == 2){
				$angsuranbunga 	 	= ($data['credits_account_last_balance'] * $data['credits_account_interest']) /100;
				$angsuranpokok 		= $data['credits_account_payment_amount'] - $angsuranbunga;
			}

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

		public function AcctSalaryPaymentsProcess(){
			$auth 									= $this->session->userdata('auth');
			$unique 								= $this->session->userdata('unique');
			$norek 									= $this->input->post('savings_account_id');
			$pokok 									= $this->input->post('credits_payment_principal');
			$interest 								= $this->input->post('credits_payment_interest');
			$id_pinjaman 							= $this->input->post('credits_account_id');
			$total 									= $this->input->post('total',true);
			$simpanan 								= $this->AcctSavingsAccount_model->getAcctSavingsAccount_Detail($norek);
			$pinjaman 								= $this->AcctCreditAccount_model->getAcctCreditsAccount_Detail($id_pinjaman);
			$last_balance 							= $pinjaman['credits_account_last_balance']-$pokok;
			$credits_account_temp_installment 		= $this->input->post('credits_account_temp_installment',true);
			
			$total_angsuran 			= $this->input->post('jangka_waktu', true);
			$angsuran_ke 				= $this->input->post('credits_payment_to', true);
			$angsuran_tiap 				= $this->input->post('credits_payment_period', true);
			$angsuran_seharusnya  		= $this->input->post('credits_payment_principal_actualy', true);
			$temp_cicilan 				= 0; 
			$credits_payment_to			= "";
			$jumlah_angsuran_kali_ini 	= 0;

			$pembayaran_angsuran_bulan_ini =$pokok+$credits_account_temp_installment;
				$temp_cicilan = $pembayaran_angsuran_bulan_ini;
				$credits_payment_to = $angsuran_ke.'*';
				$credits_account_status = 0;
				$credits_account_payment_date 	= tgltodb($this->input->post('credits_account_payment_date'));

			if($simpanan['savings_account_last_balance'] < $total){
				$auth 	= $this->session->userdata('auth');
				$msg 	= "<div class='alert alert-danger alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tabungan tidak cukup
							</div> ";
				$sesi = $this->session->userdata('unique');
				$this->session->unset_userdata('addCashlessPayment-'.$sesi['unique']);
				$this->session->set_userdata('message',$msg);
				redirect('salary-payments/add-cash-less-payment');
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
				'credits_principal_opening_balance'			=> $pinjaman['credits_account_last_balance'],
				'credits_principal_last_balance'			=> $last_balance,
				'credits_account_payment_date'				=> $credits_account_payment_date,
				'credits_payment_to'						=> $credits_payment_to,
				'credits_payment_day_of_delay'				=> $this->input->post('credits_payment_day_of_delay', true),
				'credits_payment_fine'						=> $this->input->post('credits_payment_fine', true),
				'credits_payment_type'						=> 1,
				'created_id'								=> $auth['user_id'],
				'created_on'								=> date('Y-m-d H:i:s'),
			);

			$this->form_validation->set_rules('savings_account_id', 'No. Rekening Simpanan', 'required');
			$this->form_validation->set_rules('credits_payment_principal', 'Pembayaran Pokok', 'required');


			$transaction_module_code 	= 'ANGS';
			$transaction_module_id 		= $this->AcctCreditAccount_model->getTransactionModuleID($transaction_module_code);
			$preferencecompany 			= $this->AcctCreditAccount_model->getPreferenceCompany();

			if($this->form_validation->run()==true){
				if($this->AcctSalaryPayment_model->insert($data_cash)){
					$updatedata = array(
						"credits_account_last_balance" 					=> $data_cash['credits_principal_last_balance'],
						"credits_account_last_payment_date"				=> $data_cash['credits_payment_date'],
						"credits_account_payment_date"					=> $credits_account_payment_date,
						"credits_account_payment_to"					=> $data_cash['credits_payment_to'],
						"credits_account_accumulated_fines"				=> $this->input->post('credits_account_accumulated_fines', true),
						'credits_account_temp_installment'				=> $temp_cicilan,

					);
					$this->AcctCreditAccount_model->updatedata($updatedata,$data_cash['credits_account_id']);

					$update_saving = array(
						"savings_account_last_balance" => $simpanan['savings_account_last_balance'] - $total
					);

					$this->AcctSavingsAccount_model->updatedata($update_saving,$norek);

					$last_balance 	= $simpanan['savings_account_last_balance'] - $total;
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
					);

					$this->AcctSavingsCashMutation_model->insertAcctSavingsCashMutation($mutasi_data);

					$AcctSalaryPayment_last 	= $this->AcctSalaryPayment_model->AcctSalaryPaymentLast($data_cash['created_id']);
						
					$journal_voucher_period = date("Ym", strtotime($data_cash['credits_payment_date']));
					
					$data_journal = array(
						'branch_id'						=> $data_cash['branch_id'],
						'journal_voucher_period' 		=> $journal_voucher_period,
						'journal_voucher_date'			=> date('Y-m-d'),
						'journal_voucher_title'			=> 'ANGSURAN NON VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
						'journal_voucher_description'	=> 'ANGSURAN NON VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
						'transaction_module_id'			=> $transaction_module_id,
						'transaction_module_code'		=> $transaction_module_code,
						'transaction_journal_id' 		=> $AcctSalaryPayment_last['credits_payment_id'],
						'transaction_journal_no' 		=> $AcctSalaryPayment_last['credits_account_serial'],
						'created_id' 					=> $data_cash['created_id'],
						'created_on' 					=> $data_cash['created_on'],
					);

					$this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);

					$journal_voucher_id 		= $this->AcctCreditAccount_model->getJournalVoucherID($data_cash['created_id']);

					$savingsaccount_id 			= $this->AcctSalaryPayment_model->getSavingsAccountID($mutasi_data['savings_id']);

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
					);

					$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);

					$receivable_account_id 		= $this->AcctCreditAccount_model->getReceivableAccountID($AcctSalaryPayment_last['credits_id']);

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
					);

					$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);

					if($data_cash['credits_payment_fine'] > 0){
						$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_cash_id']);

						$data_debit =array(
							'journal_voucher_id'			=> $journal_voucher_id,
							'account_id'					=> $preferencecompany['account_cash_id'],
							'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
							'journal_voucher_amount'		=> $data_cash['credits_payment_fine'],
							'journal_voucher_debit_amount'	=> $data_cash['credits_payment_fine'],
							'account_id_default_status'		=> $account_id_default_status,
							'account_id_status'				=> 0,
							'created_id' 					=> $auth['user_id'],
						);

						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debit);

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
						);

						$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
					}
					
					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Pembayaran Pinjaman Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addCashlessPayment-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('salary-payments/add-cash-less-payment');
				}else{
					$this->session->set_userdata('addAcctSalaryPayment-'.$unique['unique'],$data_cash);
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Data Pembayaran Pinjaman Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('salary-payments/add-cash-less-payment');
				}	
			}else{
				$this->session->set_userdata('addAcctSalaryPayment-'.$unique['unique'],$data_cash);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('salary-payments/add-cash-less-payment');
			}
		}

		public function reset_data(){
			$unique 	= $this->session->userdata('unique');
			$sessions	= $this->session->unset_userdata('addAcctSalaryPayment-'.$unique['unique']);
			redirect('salary-payments/process-add');
		}

		public function historyPayment(){
			$auth 	= $this->session->userdata('auth');
			$id3=$this->uri->segment(3);
			$data['main_view']['credit_account']="";
			if($id3 != ""){
				$data['main_view']['credit_account'] = $this->AcctCreditAccount_model->getDetailByID($id3);
			}
			$data['main_view']['acctcreditspayment']		=$this->AcctCreditAccount_model->getAcctCreditsPayment_Detail($id3);
			$data['main_view']['content']					= 'AcctSalaryPayment/ListHistoryAcctCreditsPayment_view';
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
	             $row[] = '<a href="'.base_url().'salary-payments/history-payment/'.$customers->credits_account_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	    
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
<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctDebtCutOff extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctDebtCutOff_model');
			$this->load->model('AcctCreditAccount_model');
			$this->load->model('AcctSalaryPayment_model');
			$this->load->model('AcctSavingsTransferMutation_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$debt_cut_off_month 	= $this->AcctDebtCutOff_model->getAcctDebtCutOffMonth();
			$debt_cut_off_year		= $this->AcctDebtCutOff_model->getAcctDebtCutOffYear();
			$debt_cut_off_month += 1;
			if($debt_cut_off_month == 13){
				$debt_cut_off_month = 1;
				$debt_cut_off_year += 1;
			}
			$data['main_view']['months']					= $this->configuration->Month();
			$data['main_view']['debt_cut_off_month']		= $debt_cut_off_month;
			$data['main_view']['debt_cut_off_year']			= $debt_cut_off_year;
			$data['main_view']['content']					= 'AcctDebtCutOff/ListAcctDebtCutOff_view';
			$this->load->view('MainPage_view',$data);
		}
		
		public function processAddDebtCutOff(){
			$auth				= $this->session->userdata('auth');
			$preferencecompany 	= $this->AcctCreditAccount_model->getPreferenceCompany();

			$data = array(
				'debt_cut_off_date' 	=> date('Y-m-d'),
				'debt_cut_off_month' 	=> $this->input->post('debt_cut_off_month'),
				'debt_cut_off_year' 	=> $this->input->post('debt_cut_off_year'),
				'created_id'			=> $auth['user_id'],
				'created_on'			=> date('Y-m-d H:i:s'),
			);

			$this->form_validation->set_rules('debt_cut_off_month', 'Bulan', 'required');
			$this->form_validation->set_rules('debt_cut_off_year', 'Tahun', 'required');

			if($this->form_validation->run()==true){
				if($this->AcctDebtCutOff_model->insertAcctDebtCutOff($data)){
					$debt_cut_off_id	= $this->AcctDebtCutOff_model->getAcctDebtCutOffID($data['created_id']);
					$acctcreditsaccount = $this->AcctDebtCutOff_model->getAcctCreditsAccount();

					foreach($acctcreditsaccount as $key => $val){
						$date_now			 	= new DateTime(date('Y-'.$data['debt_cut_off_month'].'-d'));
						$credits_account_date 	= new DateTime($val['credits_account_date']);
						$payment_to 			= $date_now->diff($credits_account_date);
						$payment_to 			= $payment_to->m;

						$date1 					= date_create($credits_payment_date);
						$date2 					= date_create($accountcredit['credits_account_payment_date']);
		
						if($date1 > $date2){
							$interval                       = $date1->diff($date2);
							$credits_payment_day_of_delay   = $interval->days;
						} else {
							$credits_payment_day_of_delay 	= 0;
						}
						
						if($angsuran_tiap == 1){
							$credits_account_payment_date_old 	= tgltodb($val['credits_account_payment_date']);
							$credits_account_payment_date 		= date('Y-m-d', strtotime("+1 months", strtotime($credits_account_payment_date_old)));
						} else {
							$credits_account_payment_date_old 	= tgltodb($val['credits_account_payment_date']);
							$credits_account_payment_date 		= date('Y-m-d', strtotime("+1 weeks", strtotime($credits_account_payment_date_old)));
						}

						if($payment_to > $val['credits_account_payment_to']){
							$data_credits_payment = array(
								'credits_account_id' 					=> $val['credits_account_id'],
								'credits_id' 							=> $val['credits_id'],
								'member_id' 							=> $val['member_id'],
								'branch_id' 							=> 2,
								'salary_payment_status' 				=> 1,
								'credits_payment_date' 					=> date('Y-m-d'),
								'credits_payment_amount'				=> $val['credits_account_payment_amount'],
								'credits_payment_principal'				=> $val['credits_account_principal_amount'],
								'credits_payment_interest'				=> $val['credits_account_interest_amount'],
								'credits_principal_opening_balance'		=> $val['credits_account_last_balance'],
								'credits_principal_last_balance'		=> $val['credits_account_last_balance'] - $val['credits_account_principal_amount'],
								'credits_interest_opening_balance'		=> $val['credits_account_interest_last_balance'],
								'credits_interest_last_balance'			=> $val['credits_account_interest_last_balance'] + $val['credits_account_interest_amount'],
								'credits_payment_fine'					=> 0,
								'credits_account_payment_date'			=> $credits_account_payment_date,
								'credits_payment_to'					=> $val['credits_account_payment_to']+1,
								'credits_payment_day_of_delay'			=> $credits_payment_day_of_delay,
								'credits_payment_token'					=> md5(rand()).$val['credits_account_id'],
								'created_id'							=> $auth['user_id'],
								'created_on'							=> date('Y-m-d H:i:s'),
							);

							//! CreditsPayment--------------------------------------------------------------------------------------------------

							if($this->AcctDebtCutOff_model->insertAcctCreditsPayment($data_credits_payment)){
								$credits_account_status = 0;
				
								if($payment_type_id == 4){
									if($data_credits_payment['credits_principal_last_balance'] <= 0){
										$credits_account_status = 1;
									}
								}else{
									if(($val['credits_account_payment_to']+1) == $val['credits_account_period']){
										$credits_account_status = 1;
									}
								}

								//! Update CreditsAccount---------------------------------------------------------------------------------------

								$updatedata = array(
									"credits_account_id"		 			=> $val['credits_account_id'],
									"credits_account_last_balance" 			=> $data_credits_payment['credits_principal_last_balance'],
									"credits_account_last_payment_date"		=> $data_credits_payment['credits_payment_date'],
									"credits_account_payment_date"			=> $credits_account_payment_date,
									"credits_account_payment_to"			=> $data_credits_payment['credits_payment_to'],
									"credits_account_interest_last_balance"	=> $data_credits_payment['credits_interest_last_balance'],
									"credits_account_status"				=> $credits_account_status,
									"credits_account_accumulated_fines"		=> 0,
								);
								$this->AcctDebtCutOff_model->updateAcctCreditsAccount($updatedata);

								//! Jurnal------------------------------------------------------------------------------------------------------

								$AcctSalaryPayment_last		= $this->AcctSalaryPayment_model->AcctSalaryPaymentLast($data_credits_payment['created_id']);
								$transaction_module_code 	= 'ANGS';
								$transaction_module_id 		= $this->AcctCreditAccount_model->getTransactionModuleID($transaction_module_code);
								$journal_voucher_period 	= date("Ym", strtotime($data['credits_payment_date']));

								$data_journal = array(
									'branch_id'						=> $auth['branch_id'],
									'journal_voucher_period' 		=> $journal_voucher_period,
									'journal_voucher_date'			=> date('Y-m-d'),
									'journal_voucher_title'			=> 'ANGSURAN VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
									'journal_voucher_description'	=> 'ANGSURAN VIA POTONG GAJI '.$AcctSalaryPayment_last['credits_name'].' '.$AcctSalaryPayment_last['member_name'],
									'journal_voucher_token'			=> $data_credits_payment['credits_payment_token'],
									'transaction_module_id'			=> $transaction_module_id,
									'transaction_module_code'		=> $transaction_module_code,
									'transaction_journal_id' 		=> $AcctSalaryPayment_last['credits_payment_id'],
									'transaction_journal_no' 		=> $AcctSalaryPayment_last['credits_account_serial'],
									'created_id' 					=> $data_credits_payment['created_id'],
									'created_on' 					=> $data_credits_payment['created_on'],
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucher($data_journal);
		
								$journal_voucher_id 				= $this->AcctCreditAccount_model->getJournalVoucherID($data_credits_payment['created_id']);
		
								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_salary_payment_id']);
		
								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_salary_payment_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data_credits_payment['credits_payment_amount'],
									'journal_voucher_debit_amount'	=> $data_credits_payment['credits_payment_amount'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
									'journal_voucher_item_token'	=> $data_credits_payment['credits_payment_token'].$preferencecompany['account_salary_payment_id'],
									'created_id' 					=> $auth['user_id'],
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_debet);
		
								$receivable_account_id 				= $this->AcctCreditAccount_model->getReceivableAccountID($data_credits_payment['credits_id']);
		
								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($receivable_account_id);
		
								$data_credit = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $receivable_account_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data_credits_payment['credits_payment_principal'],
									'journal_voucher_credit_amount'	=> $data_credits_payment['credits_payment_principal'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> $data_credits_payment['credits_payment_token'].$receivable_account_id,
									'created_id' 					=> $auth['user_id'],
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
		
								$account_id_default_status 			= $this->AcctCreditAccount_model->getAccountIDDefaultStatus($preferencecompany['account_interest_id']);
		
								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $preferencecompany['account_interest_id'],
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $data_credits_payment['credits_payment_interest'],
									'journal_voucher_credit_amount'	=> $data_credits_payment['credits_payment_interest'],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
									'journal_voucher_item_token'	=> $data_credits_payment['credits_payment_token'].$preferencecompany['account_interest_id'],
									'created_id' 					=> $auth['user_id'],
								);
								$this->AcctCreditAccount_model->insertAcctJournalVoucherItem($data_credit);
								
								$memberaccountdebt = $this->AcctSalaryPayment_model->getCoreMemberAccountReceivableAmount($data_credits_payment['member_id']);
		
								$member_account_receivable_amount = $memberaccountdebt['member_account_receivable_amount'] + $data_credits_payment['credits_payment_amount'];
		
								if($data_credits_payment['credits_id'] == 999){
									$member_account_credits_debt 		= $memberaccountdebt['member_account_credits_debt'];
									$member_account_credits_store_debt 	= $memberaccountdebt['member_account_credits_store_debt'] + $data_credits_payment['credits_payment_amount'];
								}else{
									$member_account_credits_debt 		= $memberaccountdebt['member_account_credits_debt'] + $data_credits_payment['credits_payment_amount'];
									$member_account_credits_store_debt 	= $memberaccountdebt['member_account_credits_store_debt'];
								}
		
								$data_member = array(
									"member_id" 						=> $data_credits_payment['member_id'],
									"member_account_receivable_amount" 	=> $member_account_receivable_amount,
									"member_account_credits_debt" 		=> $member_account_credits_debt,
									"member_account_credits_store_debt" => $member_account_credits_store_debt,
								);
								$this->AcctSalaryPayment_model->updateCoreMember($data_member);

								//! Tambah Penjualan Toko---------------------------------------------------------------------------------------

								$data_toko = array(
									'customer_id'               => $val['member_id'],
									'voucher_id'                => null,
									'voucher_amount'            => 0,
									'voucher_no'                => null,
									'sales_invoice_date'        => date('Y-m-d'),
									'sales_payment_method'      => 2,
									'subtotal_item'             => 0,
									'subtotal_amount'           => $val['credits_account_payment_amount'],
									'discount_percentage_total' => 0,
									'discount_amount_total'     => 0,
									'total_amount'              => $val['credits_account_payment_amount'],
									'paid_amount'               => 0,
									'change_amount'             => 0,
									'company_id'               	=> 2,
									'created_id'                => $auth['user_id'],
									'updated_id'                => $auth['user_id']
								);

								if($this->AcctDebtCutOff_model->insertSalesInvoiceStore($data_toko)){
									//! Jurnal Toko---------------------------------------------------------------------------------------------
									$transaction_module_code 	= 'PJL';
									$transaction_module_id  	= $this->AcctDebtCutOff_model->getTransactionModuleIDStore($transaction_module_code);
									$transaction_module_name  	= $this->AcctDebtCutOff_model->getTransactionModuleNameStore($transaction_module_code);
									$sales_invoice_no   		= $this->AcctDebtCutOff_model->getSalesInvoiceNoStore($data_toko['created_id']);

									$data_journal_toko = array(
										'company_id'                    => 2,
										'journal_voucher_status'        => 1,
										'journal_voucher_description'   => $transaction_module_name,
										'journal_voucher_title'         => $transaction_module_name,
										'transaction_module_id'         => $transaction_module_id,
										'transaction_module_code'       => $transaction_module_code,
										'journal_voucher_date'          => $data_toko['sales_invoice_date'],
										'transaction_journal_no'        => $sales_invoice_no,
										'journal_voucher_period'        => date('Ym'),
										'updated_id'                    => $auth['user_id'],
										'created_id'                    => $auth['user_id']
									);

									if($this->AcctDebtCutOff_model->insertAcctJournalVoucherStore($data_journal_toko)){
										$journal_voucher_id 	= $this->AcctDebtCutOff_model->getAcctJournalVoucherStore($data_journal_toko['created_id']);
										
										$account_setting_name 	= 'sales_cash_receivable_account';
										$account_id 			= $this->AcctDebtCutOff_model->getAccountIdStore($account_setting_name);
										$account_setting_status = $this->AcctDebtCutOff_model->getAccountSettingStatusStore($account_setting_name);
										$account_default_status = $this->AcctDebtCutOff_model->getAccountDefaultStatusStore($account_id);

										if ($account_setting_status == 0){
											$debit_amount = $data_toko['total_amount'];
											$credit_amount = 0;
										} else {
											$debit_amount = 0;
											$credit_amount = $data_toko['total_amount'];
										}

										$journal_debit = array(
											'company_id'                    => 2,
											'journal_voucher_id'            => $journal_voucher_id,
											'account_id'                    => $account_id,
											'journal_voucher_amount'        => $data_toko['total_amount'],
											'account_id_default_status'     => $account_default_status,
											'account_id_status'             => $account_setting_status,
											'journal_voucher_debit_amount'  => $debit_amount,
											'journal_voucher_credit_amount' => $credit_amount,
											'updated_id'                    => $auth['user_id'],
											'created_id'                    => $auth['user_id']
										);
										$this->AcctDebtCutOff_model->insertAcctJournalVoucherItemStore($journal_debit);

										$account_setting_name 	= 'sales_receivable_account';
										$account_id 			= $this->AcctDebtCutOff_model->getAccountIdStore($account_setting_name);
										$account_setting_status = $this->AcctDebtCutOff_model->getAccountSettingStatusStore($account_setting_name);
										$account_default_status = $this->AcctDebtCutOff_model->getAccountDefaultStatusStore($account_id);

										if ($account_setting_status == 0){
											$debit_amount 	= $data_toko['total_amount'];
											$credit_amount 	= 0;
										} else {
											$debit_amount 	= 0;
											$credit_amount 	= $data_toko['total_amount'];
										}

										$journal_credit = array(
											'company_id'                    => 2,
											'journal_voucher_id'            => $journal_voucher_id,
											'account_id'                    => $account_id,
											'journal_voucher_amount'        => $data_toko['total_amount'],
											'account_id_default_status'     => $account_default_status,
											'account_id_status'             => $account_setting_status,
											'journal_voucher_debit_amount'  => $debit_amount,
											'journal_voucher_credit_amount' => $credit_amount,
											'updated_id'                    => $auth['user_id'],
											'created_id'                    => $auth['user_id']
										);
										$this->AcctDebtCutOff_model->insertAcctJournalVoucherItemStore($journal_credit);
									}
								}

								//! DebtCutOffItem----------------------------------------------------------------------------------------------

								$data_item = array(
									'debt_cut_off_id' 			=> $debt_cut_off_id,
									'member_id' 				=> $val['member_id'],
									'credits_account_id' 		=> $val['credits_account_id'],
									'debt_cut_off_item_amount' 	=> $val['credits_account_payment_amount'],
									'credits_payment_to' 		=> $val['credits_account_payment_to']+1,
									'created_id'				=> $auth['user_id'],
									'created_on'				=> date('Y-m-d H:i:s'),
								);

								$this->AcctDebtCutOff_model->insertAcctDebtCutOffItem($data_item);
							}
						}
					}

					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Proses Cut Off Potong Gaji Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctdebtcutoff-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('debt-cut-off');
				}else{
					$this->session->set_userdata('addacctdebtcutoff',$data);
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Proses Cut Off Potong Gaji Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('debt-cut-off');
				}
			}else{
				$this->session->set_userdata('addacctdebtcutoff',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('debt-cut-off');
			}
		}
	}
?>
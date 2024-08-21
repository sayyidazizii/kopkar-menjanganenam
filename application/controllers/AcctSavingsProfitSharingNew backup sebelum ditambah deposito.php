<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	ini_set('memory_limit', '256M');
	ini_set('max_execution_time', 600);
	Class AcctSavingsProfitSharingNew extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctSavingsProfitSharingNew_model');
			$this->load->model('AcctDailyAverageBalanceCalculate_model');
			$this->load->model('AcctSavingsIndex_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data['main_view']['month']				= $this->configuration->Month();

			$data['main_view']['acctsavings']		= create_double($this->AcctSavingsProfitSharingNew_model->getAcctSavings(), 'savings_id', 'savings_name');
			$data['main_view']['content']			= 'AcctSavingsProfitSharing/ListAcctSavingsProfitSharingNew_view';
			$this->load->view('MainPage_view',$data);
		}

		public function recalculate(){
			$auth = $this->session->userdata('auth');

			$periode 	= $this->uri->segment(3);
			$data 		= array (
				'created_id'		=> $auth['user_id'],
				'branch_id'			=> $auth['branch_id'],
				'periode'			=> $periode
			);

			$step5 	= $this->AcctSavingsProfitSharingNew_model->getDataLogStep5($data);

			if(empty($step5)){
				if($this->AcctSavingsProfitSharingNew_model->deleteDataLog($data)){
					redirect('savings-profit-sharing');
				} else {
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Proses Hitung Ulang Gagal
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('savings-profit-sharing/list-data');
				}
			} else {
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Hitung Ulang Gagal, Basil Sudah Diproses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('savings-profit-sharing/list-data');
			}
		}
		
		public function processAddAcctSavingsProfitSharing(){
			$auth = $this->session->userdata('auth');
			$sesi = $this->session->userdata('unique');

			$data = array (
				'month_period' 	=> $this->input->post('month_period', true),
				'year_period'	=> $this->input->post('year_period', true),
				'saldo_minimal'	=> $this->input->post('savings_account_minimum', true),
			);

			$this->form_validation->set_rules('month_period', 'Periode Bulan', 'required');
			$this->form_validation->set_rules('year_period', 'Periode Tahun', 'required');

			if($this->form_validation->run()==true){
				$savings_profit_sharing_period 	= $data['month_period'].$data['year_period'];

				$last_date 	= date('t', strtotime($data['month_period']));
				$date 		= $data['year_period'].'-'.$data['month_period'].'-'.$last_date;

				//---------------Step 1 Create Table-----------------------------//

				$log_step1 	= array (
					'branch_id'			=> $auth['branch_id'],
					'created_id'		=> $auth['user_id'],
					'created_on'		=> date('Y-m-d'),
					'periode'			=> $savings_profit_sharing_period,
					'step'				=> 1,
				);

				$path 		= 'assets/';
				$table 		= 'table_temp.sql';
				$file 		= file_get_contents($path.$table);

				$data_log_step1 = $this->AcctSavingsProfitSharingNew_model->getDataLogStep1($log_step1);

				if(empty($data_log_step1)){
					$this->AcctSavingsProfitSharingNew_model->insertDataLogStep1($log_step1, $file);		
					$this->AcctSavingsProfitSharingNew_model->truncateAcctSavingsProfitSharingTotalTemp();		
				} else {
					if($data_log_step1['status_process'] == 0){
						$this->AcctSavingsProfitSharingNew_model->createTable($log_step1, $file);
						$this->AcctSavingsProfitSharingNew_model->truncateAcctSavingsProfitSharingTotalTemp();		
					}
				}

				//----------------Step 2 Insert SRH-------------------//
				$log_step2 = array (
					'branch_id'			=> $auth['branch_id'],
					'created_id'		=> $auth['user_id'],
					'created_on'		=> date('Y-m-d'),
					'periode'			=> $savings_profit_sharing_period,
					'step'				=> 2,
				);

				$data_log_step2 = $this->AcctSavingsProfitSharingNew_model->getDataLogStep2($log_step2);
				
				if(empty($data_log_step2)){
					$acctsavingsaccountforsrh 		= $this->AcctSavingsProfitSharingNew_model->getAcctSavingsAccountfoSRH($auth['branch_id']);

					if(!empty($acctsavingsaccountforsrh)){
						foreach ($acctsavingsaccountforsrh as $key => $val) {
								$yesterday_transaction_date = $this->AcctSavingsProfitSharingNew_model->getYesterdayTransactionDate($val['savings_account_id']);

								$last_balance_SRH 			= $this->AcctSavingsProfitSharingNew_model->getLastBalanceSRH($val['savings_account_id']);

								if(empty($last_balance_SRH)){
									$last_balance_SRH = 0;
								}

								$last_date 	= date('t', strtotime($data['month_period']));
								$date 		= $data['year_period'].'-'.$data['month_period'].'-'.$last_date;

								$date1 		= date_create($date);
								$date2 		= date_create($yesterday_transaction_date);

								$interval     = $date1->diff($date2);
                                $range_date   = $interval->days;

								if($range_date == 0){
									$range_date = 1;
								}

								$daily_average_balance = ($last_balance_SRH * $range_date) / $last_date;

								$dataacctsavingsaccountdetail[] = array (
									'savings_account_id'				=> $val['savings_account_id'],
									'branch_id'							=> $val['branch_id'],
									'savings_id'						=> $val['savings_id'],
									'member_id'							=> $val['member_id'],
									'today_transaction_date'			=> tgltodb($date),
									'yesterday_transaction_date'		=> $yesterday_transaction_date,
									'transaction_code'					=> 'Penutupan Akhir Bulan',
									'opening_balance'					=> $last_balance_SRH,
									'last_balance'						=> $last_balance_SRH,
									'daily_average_balance'				=> $daily_average_balance,
									'operated_name'						=> 'SYSTEM',
								);

								$daily_average_balance_total = $this->AcctSavingsProfitSharingNew_model->getDailyAverageBalanceTotal($val['savings_account_id'], $data['month_period'], $data['year_period']);

								$data_savings[] = array (
									'savings_account_id'					=> $val['savings_account_id'],
									'branch_id'								=> $val['branch_id'],
									'savings_id'							=> $val['savings_id'],
									'savings_account_daily_average_balance' => $daily_average_balance_total + $daily_average_balance,
								);
						}
					}

					$log_step2 = array (
						'branch_id'			=> $auth['branch_id'],
						'created_id'		=> $auth['user_id'],
						'created_on'		=> date('Y-m-d'),
						'periode'			=> $savings_profit_sharing_period,
						'step'				=> 2,
						'total_account'		=> count($dataacctsavingsaccountdetail),
					);

					if($this->AcctSavingsProfitSharingNew_model->insertDataLogStep($log_step2)){
						if($this->AcctSavingsProfitSharingNew_model->insertAcctSavingsAccountDetail($dataacctsavingsaccountdetail, $log_step2)){
							$this->AcctSavingsProfitSharingNew_model->insertAcctSavingsAccountTemp($data_savings);
						}
					}
				}
				$last_date 	= date('t', strtotime($data['month_period']));
				$date 		= $data['year_period'].'-'.$data['month_period'].'-'.$last_date;

				//----------------Step 4 Insert Basil----------------------------------//
				$log_step4 = array (
					'branch_id'			=> $auth['branch_id'],
					'created_id'		=> $auth['user_id'],
					'created_on'		=> date('Y-m-d'),
					'periode'			=> $savings_profit_sharing_period,
					'step'				=> 4,
				);

				$data_log_step4 = $this->AcctSavingsProfitSharingNew_model->getDataLogStep4($log_step4);	

				if(empty($data_log_step4)){	
					$savings_account_balance_minimum 		= $data['saldo_minimal'];
					$acctsavingsaccount 					= $this->AcctSavingsProfitSharingNew_model->getAcctSavingsAccountforBasil($auth['branch_id'], $savings_account_balance_minimum);

					$member_id = -1;
					$arraydata[] = array();
					foreach($acctsavingsaccount as $krey => $vral){
						if($vral['savings_id'] == 35){
							$profitsharing	= $vral['savings_account_interest_rate'];
						}else{
							$profitsharing	= $this->AcctSavingsProfitSharingNew_model->getSavingsInterestRate($vral['savings_id']);
						}

						$savings_account_daily_average_balance 	= $vral['savings_account_last_balance'];
						$savings_interest_temp_amount 			= ($savings_account_daily_average_balance * ($profitsharing / 12)) / 100;
						$savings_account_last_balance 			= $vral['savings_account_last_balance'] + $savings_interest_temp_amount;
						$preferencecompany						= $this->AcctSavingsProfitSharingNew_model->getPreferenceCompany();

						if($savings_interest_temp_amount > $preferencecompany['tax_minimum_amount']){
							$savings_tax_temp_amount = $savings_interest_temp_amount * $preferencecompany['tax_percentage'] / 100;
						}else{
							$savings_tax_temp_amount = 0;
						}

						$savings_account_last_balance -= $savings_tax_temp_amount;

						if($member_id != $vral['member_id']){
							if($tempdata['savings_interest_temp_amount'] > $preferencecompany['tax_minimum_amount']){
								$savings_tax_temp_amount = $tempdata['savings_interest_temp_amount'] * $preferencecompany['tax_percentage'] / 100;
							}else{
								$savings_tax_temp_amount = 0;
							}

							$tempdata['savings_tax_temp_amount'] = $savings_tax_temp_amount;

							if($member_id != -1){
								array_push($arraydata, $tempdata);
							}

							$tempdata = array(
								'savings_account_id'						=> $vral['savings_account_id'],
								'branch_id'									=> $vral['branch_id'],
								'savings_id'								=> $vral['savings_id'],
								'member_id'									=> $vral['member_id'],
								'savings_profit_sharing_temp_date'			=> tgltodb($date),
								'savings_daily_average_balance_minimum'		=> $savings_account_balance_minimum,
								'savings_daily_average_balance'				=> $savings_account_daily_average_balance,
								'savings_profit_sharing_temp_amount'		=> $savings_interest_temp_amount,
								'savings_profit_sharing_temp_period'		=> $savings_profit_sharing_period,
								'savings_tax_temp_amount'					=> $savings_tax_temp_amount,
								'savings_interest_temp_amount'				=> $savings_interest_temp_amount,
								'savings_account_last_balance'				=> $savings_account_last_balance,
								'savings_profit_sharing_temp_token'			=> $savings_profit_sharing_period.$vral['savings_account_id'],
								'operated_name'								=> 'SYSTEM',
								'created_id'								=> $auth['user_id'],
								'created_on'								=> date('Y-m-d H:i:s'),
							);
						}else{
							$tempdata = array(
								'savings_account_id'						=> $vral['savings_account_id'],
								'branch_id'									=> $vral['branch_id'],
								'savings_id'								=> $vral['savings_id'],
								'member_id'									=> $vral['member_id'],
								'savings_profit_sharing_temp_date'			=> tgltodb($date),
								'savings_daily_average_balance_minimum'		=> $savings_account_balance_minimum,
								'savings_daily_average_balance'				=> $savings_account_daily_average_balance,
								'savings_profit_sharing_temp_amount'		=> $tempdata['savings_profit_sharing_temp_amount'] + $savings_interest_temp_amount,
								'savings_profit_sharing_temp_period'		=> $savings_profit_sharing_period,
								'savings_tax_temp_amount'					=> $tempdata['savings_tax_temp_amount'] + $savings_tax_temp_amount,
								'savings_interest_temp_amount'				=> $tempdata['savings_interest_temp_amount'] + $savings_interest_temp_amount,
								'savings_account_last_balance'				=> $tempdata['savings_account_last_balance'] + $savings_account_last_balance,
								'savings_profit_sharing_temp_token'			=> $savings_profit_sharing_period.$vral['savings_account_id'],
								'operated_name'								=> 'SYSTEM',
								'created_id'								=> $auth['user_id'],
								'created_on'								=> date('Y-m-d H:i:s'),
							);
						}
						$member_id = $vral['member_id'];
					}

					if($tempdata['savings_interest_temp_amount'] > $preferencecompany['tax_minimum_amount']){
						$savings_tax_temp_amount = $tempdata['savings_interest_temp_amount'] * $preferencecompany['tax_percentage'] / 100;
					}else{
						$savings_tax_temp_amount = 0;
					}

					$tempdata['savings_tax_temp_amount'] = $savings_tax_temp_amount;
					array_push($arraydata, $tempdata);
					$this->AcctSavingsProfitSharingNew_model->insertAcctSavingsProfitSharingTotalTemp(array_filter($arraydata), $log_step4);

					$no = 1;
					foreach ($acctsavingsaccount as $k => $v) {
						if($vral['savings_id'] == 35){
							$profitsharing	= $v['savings_account_interest_rate'];
						}else{
							$profitsharing	= $this->AcctSavingsProfitSharingNew_model->getSavingsInterestRate($v['savings_id']);
						}

						$savings_account_daily_average_balance 	= $v['savings_account_last_balance'];
						$savings_interest_temp_amount 			= ($savings_account_daily_average_balance * ($profitsharing / 12)) / 100;
						$savings_account_last_balance 			= $v['savings_account_last_balance'] + $savings_interest_temp_amount;
						$preferencecompany						= $this->AcctSavingsProfitSharingNew_model->getPreferenceCompany();

						if($savings_interest_temp_amount > $preferencecompany['tax_minimum_amount']){
							$savings_tax_temp_amount = $savings_interest_temp_amount * $preferencecompany['tax_percentage'] / 100;
						}else{
							$savings_tax_temp_amount = 0;
						}

						$savings_account_last_balance -= $savings_tax_temp_amount;

						$dataacctsavingsprofitsharing[] = array (
							'savings_account_id'						=> $v['savings_account_id'],
							'branch_id'									=> $v['branch_id'],
							'savings_id'								=> $v['savings_id'],
							'member_id'									=> $v['member_id'],
							'savings_profit_sharing_temp_date'			=> tgltodb($date),
							'savings_daily_average_balance_minimum'		=> $savings_account_balance_minimum,
							'savings_daily_average_balance'				=> $savings_account_daily_average_balance,
							'savings_profit_sharing_temp_amount'		=> $savings_interest_temp_amount,
							'savings_profit_sharing_temp_period'		=> $savings_profit_sharing_period,
							'savings_tax_temp_amount'					=> $savings_tax_temp_amount,
							'savings_interest_temp_amount'				=> $savings_interest_temp_amount,
							'savings_account_last_balance'				=> $savings_account_last_balance,
							'savings_profit_sharing_temp_token'			=> $savings_profit_sharing_period.$v['savings_account_id'],
							'operated_name'								=> 'SYSTEM',
							'created_id'								=> $auth['user_id'],
							'created_on'								=> date('Y-m-d H:i:s'),
						);

						$no++;
					}

					$log_step4 = array (
						'branch_id'			=> $auth['branch_id'],
						'created_id'		=> $auth['user_id'],
						'created_on'		=> date('Y-m-d'),
						'periode'			=> $savings_profit_sharing_period,
						'step'				=> 4,
						'total_account'		=> count($dataacctsavingsprofitsharing),
					);	
			
					if($this->AcctSavingsProfitSharingNew_model->insertDataLogStep($log_step4)){
						$this->AcctSavingsProfitSharingNew_model->insertAcctSavingsProfitSharingTemp($dataacctsavingsprofitsharing, $log_step4);
					}
				}

				redirect('savings-profit-sharing/list-data');
			} else {
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('savings-profit-sharing');
			}			
		}

		public function listdata(){
			$auth = $this->session->userdata('auth');

			$data['main_view']['monthname']							= $this->configuration->Month();

			$data['main_view']['acctsavingsprofitsharingtemp']		= $this->AcctSavingsProfitSharingNew_model->getAcctSavingsProfitSharingTemp($auth['branch_id']);
			$data['main_view']['acctsavingsprofitsharingtotaltemp']	= $this->AcctSavingsProfitSharingNew_model->getAcctSavingsProfitSharingTotalTemp($auth['branch_id']);

			$data['main_view']['content']							= 'AcctSavingsProfitSharing/ListDataAcctSavingsProfitSharingNew_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processUpdateAcctSavingsProfitSharing(){
			$auth 				= $this->session->userdata('auth');
			$preferencecompany	= $this->AcctSavingsProfitSharingNew_model->getPreferenceCompany();

			$periode 	= $this->AcctSavingsProfitSharingNew_model->getPeriode($auth);

			$log_step5 	= array (
				'branch_id'			=> $auth['branch_id'],
				'created_id'		=> $auth['user_id'],
				'created_on'		=> date('Y-m-d'),
				'periode'			=> $periode,
				'step'				=> 5,
			);

			$data_log_step5 = $this->AcctSavingsProfitSharingNew_model->getDataLogStep5($log_step5);

			$dataperiod = array (
				'period'		=> $periode,
				'created_id'	=> $auth['user_id'],
				'created_on'	=> date('Y-m-d'),
			);

			if(empty($data_log_step5)){	
				if($this->AcctSavingsProfitSharingNew_model->insertDataLogStep($log_step5)){

					$this->AcctSavingsProfitSharingNew_model->insertSystemPeriodLog($dataperiod);
					if($this->AcctSavingsProfitSharingNew_model->insertAcctSavingsProfitSharingFix()){
						$this->AcctSavingsProfitSharingNew_model->insertAcctSavingsProfitSharingTotalFix();

						$corebranch = $this->AcctSavingsProfitSharingNew_model->getCoreBranch();

						foreach ($corebranch as $key => $vCB) {
							$savings_profit_sharing_amount = $this->AcctSavingsProfitSharingNew_model->getTotalSavingsProfitSharing($vCB['branch_id']);

							$data_transfer = array (
								'branch_id'									=> $vCB['branch_id'],
								'savings_transfer_mutation_date'			=> date('Y-m-d'),
								'savings_transfer_mutation_amount'			=> $savings_profit_sharing_amount,
								'operated_name'								=> 'SYSTEM',
								'created_id'								=> $auth['user_id'],
								'created_on'								=> date('Y-m-d H:i:s'),
							);

							if($this->AcctSavingsProfitSharingNew_model->insertAcctSavingsTransferMutation($data_transfer)){
								$savings_transfer_mutation_id = $this->AcctSavingsProfitSharingNew_model->getSavingsTranferMutationID($data_transfer['created_id']);

								$this->AcctSavingsProfitSharingNew_model->insertAcctSavingsTransferMutationTo($savings_transfer_mutation_id, $vCB['branch_id']);
							}

							$acctsavings 			= $this->AcctSavingsProfitSharingNew_model->getAcctSavings();
							$acctdepositoaccount 	= $this->AcctSavingsProfitSharingNew_model->getAcctDepositoAccount();

							//-------------------------------------Jurnal--------------------------------------------------------//
							foreach ($acctsavings as $key => $val) {
								$totalsavingsprofitsharing 	= $this->AcctSavingsProfitSharingNew_model->getSubTotalSavingsProfitSharing($val['savings_id'], $vCB['branch_id']);

								$transaction_module_code 	= "BS";

								$transaction_module_id 		= $this->AcctSavingsProfitSharingNew_model->getTransactionModuleID($transaction_module_code);
	
								$journal_voucher_period 	= $periode;

								$data_journal 				= array(
									'branch_id'						=> $vCB['branch_id'],
									'journal_voucher_period' 		=> $journal_voucher_period,
									'journal_voucher_date'			=> date('Y-m-d'),
									'journal_voucher_title'			=> 'JASA SIMPANAN '.$val['savings_name'].' PERIODE '.$journal_voucher_period,
									'journal_voucher_description'	=> 'JASA SIMPANAN '.$val['savings_name'].' PERIODE '.$journal_voucher_period,
									'transaction_module_id'			=> $transaction_module_id,
									'transaction_module_code'		=> $transaction_module_code,
									'created_id' 					=> $auth['user_id'],
									'created_on' 					=> date('Y-m-d H:i:s'),
								);

								$this->AcctSavingsProfitSharingNew_model->insertAcctJournalVoucher($data_journal);

								$journal_voucher_id 			= $this->AcctSavingsProfitSharingNew_model->getJournalVoucherID($data_journal['created_id']);

								$account_basil_id 				= $this->AcctSavingsProfitSharingNew_model->getAccountBasilID($val['savings_id']);

								$account_id_default_status 		= $this->AcctSavingsProfitSharingNew_model->getAccountIDDefaultStatus($account_basil_id);

								$data_debet = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_basil_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $totalsavingsprofitsharing,
									'journal_voucher_debit_amount'	=> $totalsavingsprofitsharing,
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
								);

								$this->AcctSavingsProfitSharingNew_model->insertAcctJournalVoucherItem($data_debet);

								$account_id 					= $this->AcctSavingsProfitSharingNew_model->getAccountID($val['savings_id']);

								$account_id_default_status 		= $this->AcctSavingsProfitSharingNew_model->getAccountIDDefaultStatus($account_id);

								$data_credit =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $totalsavingsprofitsharing,
									'journal_voucher_credit_amount'	=> $totalsavingsprofitsharing,
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
								);

								$this->AcctSavingsProfitSharingNew_model->insertAcctJournalVoucherItem($data_credit);
							}

							$savingstax = array();
							foreach($acctsavings as $key => $val){
								$savingstax[$val['savings_id']] = 0;
							}

							$savingsprofitsharingtotaltemp = $this->AcctSavingsProfitSharingNew_model->getSavingsProfitSharingTotalTemp();

							foreach($savingsprofitsharingtotaltemp as $key => $val){
								$totaltax 		= $val['savings_tax_temp_amount'];
								$savingsaccount = $this->AcctSavingsProfitSharingNew_model->getAcctSavingsAccount($val['member_id']);
								foreach($savingsaccount as $keyy => $vall){
									if(($vall['savings_account_last_balance'] - $totaltax) >= 0){
										$savings_account_last_balance = $vall['savings_account_last_balance'] - $totaltax;
										$savingstax[$vall['savings_id']] += $totaltax; 
										//insertmutasi

										$data_transfer = array (
											'branch_id'									=> $vCB['branch_id'],
											'savings_transfer_mutation_date'			=> date('Y-m-d'),
											'savings_transfer_mutation_amount'			=> $totaltax,
											'operated_name'								=> 'SYSTEM',
											'created_id'								=> $auth['user_id'],
											'created_on'								=> date('Y-m-d H:i:s'),
										);

										if($this->AcctSavingsProfitSharingNew_model->insertAcctSavingsTransferMutation($data_transfer)){
											$savings_transfer_mutation_id = $this->AcctSavingsProfitSharingNew_model->getSavingsTranferMutationID($data_transfer['created_id']);

											$data_mutation_from = array(
												'savings_transfer_mutation_id' 			=> $savings_transfer_mutation_id, 
												'savings_account_id' 					=> $vall['savings_account_id'], 
												'savings_id' 							=> $vall['savings_id'], 
												'branch_id' 							=> $vCB['branch_id'], 
												'member_id' 							=> $vall['member_id'], 
												'mutation_id' 							=> $preferencecompany['account_savings_transfer_from_id'], 
												'savings_transfer_mutation_from_amount' => $totaltax, 
												'savings_account_last_balance'			=> $savings_account_last_balance,											
											);

											$this->AcctSavingsProfitSharingNew_model->insertAcctSavingsTransferMutationFrom($data_mutation_from);
										}
										$totaltax 	-= $totaltax; 
									}else{
										$savings_account_last_balance = $vall['savings_account_last_balance'] - $vall['savings_account_last_balance'];
										$savingstax[$vall['savings_id']] += $vall['savings_account_last_balance']; 
										//insertmutasi

										$data_transfer = array (
											'branch_id'									=> $vCB['branch_id'],
											'savings_transfer_mutation_date'			=> date('Y-m-d'),
											'savings_transfer_mutation_amount'			=> $vall['savings_account_last_balance'],
											'operated_name'								=> 'SYSTEM',
											'created_id'								=> $auth['user_id'],
											'created_on'								=> date('Y-m-d H:i:s'),
										);

										if($this->AcctSavingsProfitSharingNew_model->insertAcctSavingsTransferMutation($data_transfer)){
											$savings_transfer_mutation_id = $this->AcctSavingsProfitSharingNew_model->getSavingsTranferMutationID($data_transfer['created_id']);

											$data_mutation_from = array(
												'savings_transfer_mutation_id' 			=> $savings_transfer_mutation_id, 
												'savings_account_id' 					=> $vall['savings_account_id'], 
												'savings_id' 							=> $vall['savings_id'], 
												'branch_id' 							=> $vCB['branch_id'], 
												'member_id' 							=> $vall['member_id'], 
												'mutation_id' 							=> 15, 
												'savings_transfer_mutation_from_amount' => $vall['savings_account_last_balance'], 
												'savings_account_last_balance'			=> $savings_account_last_balance,											
											);

											$this->AcctSavingsProfitSharingNew_model->insertAcctSavingsTransferMutationFrom($data_mutation_from);
										}
										$totaltax 	-= $vall['savings_account_last_balance']; 
									}
								}
							}

							foreach ($acctsavings as $key => $val) {
								$transaction_module_code 	= "PS";

								$transaction_module_id 		= $this->AcctSavingsProfitSharingNew_model->getTransactionModuleID($transaction_module_code);

								$journal_voucher_period 	= $periode;

								$data_journal 				= array(
									'branch_id'						=> $vCB['branch_id'],
									'journal_voucher_period' 		=> $journal_voucher_period,
									'journal_voucher_date'			=> date('Y-m-d'),
									'journal_voucher_title'			=> 'PAJAK SIMPANAN '.$val['savings_name'].' PERIODE '.$journal_voucher_period,
									'journal_voucher_description'	=> 'PAJAK SIMPANAN '.$val['savings_name'].' PERIODE '.$journal_voucher_period,
									'transaction_module_id'			=> $transaction_module_id,
									'transaction_module_code'		=> $transaction_module_code,
									'created_id' 					=> $auth['user_id'],
									'created_on' 					=> date('Y-m-d H:i:s'),
								);

								$this->AcctSavingsProfitSharingNew_model->insertAcctJournalVoucher($data_journal);

								$journal_voucher_id 			= $this->AcctSavingsProfitSharingNew_model->getJournalVoucherID($data_journal['created_id']);

								$account_tax_id 				= $preferencecompany['account_savings_tax_id'];

								$account_id_default_status 		= $this->AcctSavingsProfitSharingNew_model->getAccountIDDefaultStatus($account_tax_id);

								$data_credit = array (
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_tax_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $savingstax[$val['savings_id']],
									'journal_voucher_credit_amount'	=> $savingstax[$val['savings_id']],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 1,
								);

								$this->AcctSavingsProfitSharingNew_model->insertAcctJournalVoucherItem($data_credit);

								$account_id 					= $this->AcctSavingsProfitSharingNew_model->getAccountID($val['savings_id']);

								$account_id_default_status 		= $this->AcctSavingsProfitSharingNew_model->getAccountIDDefaultStatus($account_id);

								$data_debet =array(
									'journal_voucher_id'			=> $journal_voucher_id,
									'account_id'					=> $account_id,
									'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
									'journal_voucher_amount'		=> $savingstax[$val['savings_id']],
									'journal_voucher_debit_amount'	=> $savingstax[$val['savings_id']],
									'account_id_default_status'		=> $account_id_default_status,
									'account_id_status'				=> 0,
								);

								$this->AcctSavingsProfitSharingNew_model->insertAcctJournalVoucherItem($data_debet);
							}
						}
					}
					$msg = "<div class='alert alert-success alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Basil Selesai Diproses
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('savings-profit-sharing');
				}
			} else {
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Basil Sudah Selesai Diproses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('savings-profit-sharing');
			}
		}

		public function SyncronizeData(){
			$auth = $this->session->userdata('auth');
			$sesi	= 	$this->session->userdata('filter-acctsavingsmonitor');
			if(!is_array($sesi)){
				$sesi['start_date']		= date('Y-m-d');
				$sesi['end_date']		= date('Y-m-d');
				$sesi['savings_account_id'] 	= '';
			}

			if(!empty($sesi['savings_account_id'])){
				$datalog = array (
					'savings_syncronize_log_date' 		=> date('Y-m-d'),
					'savings_syncronize_log_start_date'	=> $sesi['start_date'],
					'savings_syncronize_log_end_date'	=> $sesi['end_date'],
					'savings_account_id'				=> $sesi['savings_account_id'],
					'branch_id'							=> $auth['branch_id'],
					'created_id'						=> $auth['user_id'],
					'created_on'						=> date('Y-m-d H:i:s'),
				);

				if($this->AcctSavingsPrintSavingsMonitor_model->insertAcctSavingsSyncronizeLog($datalog)){
					$opening_balance 			= $this->AcctSavingsPrintSavingsMonitor_model->getOpeningBalance($datalog['savings_account_id'], $datalog['savings_syncronize_log_start_date']);

					if(!is_array($opening_balance)){
						$opening_date 			= $this->AcctSavingsPrintSavingsMonitor_model->getLastDate($datalog['savings_account_id'], $datalog['savings_syncronize_log_start_date']);
						$opening_balance 		= $this->AcctSavingsPrintSavingsMonitor_model->getLastBalance($datalog['savings_account_id'], $opening_date);
					}

					$acctsavingsaccountdetail 	= $this->AcctSavingsPrintSavingsMonitor_model->getAcctSavingsAccountDetail($datalog['savings_account_id'], $datalog['savings_syncronize_log_start_date'], $datalog['savings_syncronize_log_end_date']);

					foreach ($acctsavingsaccountdetail as $key => $val) {
						$last_balance = ($opening_balance + $val['mutation_in']) - $val['mutation_out'];

						$newdata = array (
							'savings_account_detail_id'		=> $val['savings_account_detail_id'],
							'savings_account_id'			=> $val['savings_account_id'],
							'opening_balance'				=> $opening_balance,
							'last_balance'					=> $last_balance,
						);

						$opening_balance = $last_balance;

						if($this->AcctSavingsPrintSavingsMonitor_model->updateAcctSavingsAccountDetail($newdata)){
							$msg = "<div class='alert alert-success alert-dismissable'>
									<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>	               
											Syncronize Data Sukses
										</div> ";
							$this->session->set_userdata('message',$msg);
							continue;
						} else {
							$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>	               
									Syncronize Data Gagal
								</div> ";
							$this->session->set_userdata('message',$msg);
							redirect('savings-print-mutation/monitor-savings-mutation');
							break;
						}
					}
					redirect('savings-print-savings-monitor/monitor-savings-mutation');
				} else {
					$msg = "<div class='alert alert-danger alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Syncronize Data Gagal
							</div> ";
					$sesi = $this->session->userdata('unique');
					redirect('savings-print-savings-monitor/monitor-savings-mutation');
				}

			} else {
				$msg = "<div class='alert alert-danger alert-dismissable'>  
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							No. Rekening Simpanan Masih Kosong
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('savings-print-savings-monitor/monitor-savings-mutation');
			}
		}
	}	
?>
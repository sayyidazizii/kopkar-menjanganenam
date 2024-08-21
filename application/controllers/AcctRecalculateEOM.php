<?php defined('BASEPATH') OR exit('No direct script access allowed');
	/*ini_set('memory_limit', '512M');*/

	Class AcctRecalculateEOM extends CI_Controller{
		public function __construct(){
			parent::__construct();

			$this->load->model('MainPage_model');
			$this->load->model('Connection_model');
			$this->load->model('AcctRecalculateEOM_model');
			$this->load->model('AcctProfitLossReportNew1_model');
			$this->load->library('configuration');
			$this->load->helper('sistem');
			$this->load->database('default');
		}
		
		public function index(){
			$data['main_view']['monthlist']			= $this->configuration->Month();
			$data['main_view']['preferencecompany'] = $this->AcctRecalculateEOM_model->getPreferenceCompany();
			$data['main_view']['corebranch']		= create_double($this->AcctRecalculateEOM_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']			= 'AcctRecalculate/AcctRecalculate_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processAcctRecalculateEOM(){
			$preferencecompany = $this->AcctRecalculateEOM_model->getPreferenceCompany();
			$auth = $this->session->userdata('auth');

			// $data = array (
			// 	"month_period" 	    => $this->input->post('month_period',true),
			// 	"year_period" 		=> $this->input->post('year_period',true),
			// 	"branch_id" 		=> $this->input->post('branch_id',true),
			// );

			$data = array (
				"branch_id" 		=> $this->uri->segment(3),
				//kode dibawah untuk backdate
				// "month_period" 	    => $this->uri->segment(4),
				// "year_period" 		=> $this->uri->segment(5),
				"month_period" 	    => date('m'),
				"year_period" 		=> date('Y'),
			);

			// print_r($data);exit;

			if($auth['branch_status'] == 1){
				if(empty($data['branch_id'])){
					$data['branch_id'] = $auth['branch_id'];
				}
			} else {
				$data['branch_id'] = $auth['branch_id'];
			}

			$data_recalculate_log = array (
				"branch_id"			=> $data['branch_id'],
				"month_period"		=> $data['month_period'],
				"year_period"		=> $data['year_period'],
				"created_id"		=> $auth['user_id'],
				"created_on"		=> date("Y-m-d H:i:s"),
			);

			//print_r($data);exit;

			if($this->AcctRecalculateEOM_model->insertAcctRecalculateEOMLog($data_recalculate_log)){
				$acctaccount 	= $this->AcctRecalculateEOM_model->getAcctAccount();

				foreach ($acctaccount as $key => $val){
					$opening_balance_old 	= $this->AcctRecalculateEOM_model->getAccountOpeningBalance($val['account_id'], $data);
					
					if(empty($opening_balance_old)){
						$opening_balance_old = 0;
					}

					$total_mutation_in 		= $this->AcctRecalculateEOM_model->getTotalAccountIn($val['account_id'], $data);
					$total_mutation_out 	= $this->AcctRecalculateEOM_model->getTotalAccountOut($val['account_id'], $data);

					if(empty($total_mutation_in)){
						$total_mutation_in 	= 0;
					}

					if(empty($total_mutation_out)){
						$total_mutation_out = 0;
					}

					$last_balance 			= $total_mutation_in - $total_mutation_out;
					$opening_balance_new 	= $opening_balance_old + $last_balance;

					$next_month = $data['month_period'] + 1;

					if($next_month == 13){
						$next_month = '01';
						$next_year 	= $data['year_period'] + 1;
					} else {
						if($next_month < 10){
							$next_month = '0'.$next_month;
						} else {
							$next_month = $next_month;
						}
						
						$next_year 	= $data['year_period'];
					}

					$month_check = date('m');
					$year_check = date('Y');
					if($month_check == 1){
						if($val['account_id'] == $preferencecompany['account_shu_last_year']){
							$shulastyear = $this->AcctRecalculateEOM_model->getSHULastYear($year_check);
							$opening_balance_new = $shulastyear;
						}
					}
					
					$data_account_opening_balance[$key] = array (
						'branch_id'				=> $data['branch_id'],
						'account_id'			=> $val['account_id'],
						'month_period'			=> $next_month,
						'year_period'			=> $next_year,
						'opening_balance'		=> $opening_balance_new
					);

					$data_account_mutation[$key] = array (
						'branch_id'				=> $data['branch_id'],
						'account_id'			=> $val['account_id'],
						'month_period'			=> $data['month_period'],
						'year_period'			=> $data['year_period'],
						'mutation_in_amount'	=> $total_mutation_in,
						'mutation_out_amount'	=> $total_mutation_out,
						'last_balance'			=> $last_balance
					);
				}

				$check_data_account_opening_balance = $this->AcctRecalculateEOM_model->getCheckAcctAccountOpeningBalance($next_month, $next_year, $data['branch_id']);
				$check_data_account_mutation 		= $this->AcctRecalculateEOM_model->getCheckAcctAccountMutation($data);

				$data_state = false;
				if($check_data_account_opening_balance->num_rows() == 0){
					if($check_data_account_mutation->num_rows() == 0){
						$data_state = true;	
					} else {
						if($this->AcctRecalculateEOM_model->deleteAcctAccountMutation($data)){
							$data_state = true;
						} else {
							$data_state = false;
						}
					}
					
				} else{
					if($this->AcctRecalculateEOM_model->deleteAcctAccountOpeningBalance($next_month, $next_year, $data['branch_id'])){
						if($check_data_account_mutation->num_rows() == 0){
							$data_state = true;	
						} else {
							if($this->AcctRecalculateEOM_model->deleteAcctAccountMutation($data)){
								$data_state = true;
							} else {
								$data_state = false;
							}
						}
					} else {
						$data_state = false;
					}
				}

				if($data_state == true){
					if($this->AcctRecalculateEOM_model->insertAcctAccountOpeningBalance($data_account_opening_balance)){
						if($this->AcctRecalculateEOM_model->insertAcctAccountMutation($data_account_mutation)){

							$preference_company 			= $this->AcctProfitLossReportNew1_model->getPreferenceCompany();
							$acctprofitlossreport_top		= $this->AcctProfitLossReportNew1_model->getAcctProfitLossReportNew1_Top($data['profit_loss_report_format']);
							$acctprofitlossreport_bottom	= $this->AcctProfitLossReportNew1_model->getAcctProfitLossReportNew1_Bottom($data['profit_loss_report_format']);
							$branch_name 					= $this->AcctProfitLossReportNew1_model->getBranchName($data['branch_id']);

							switch ($data['month_period_start']) {
								case '01':
									$month_name1 = "Januari";
									break;
								case '02':
									$month_name1 = "Februari";
									break;
								case '03':
									$month_name1 = "Maret";
									break;
								case '04':
									$month_name1 = "April";
									break;
								case '05':
									$month_name1 = "Mei";
									break;
								case '06':
									$month_name1 = "Juni";
									break;
								case '07':
									$month_name1 = "Juli";
									break;
								case '08':
									$month_name1 = "Agustus";
									break;
								case '09':
									$month_name1 = "September";
									break;
								case '10':
									$month_name1 = "Oktober";
									break;
								case '11':
									$month_name1 = "November";
									break;
								case '12':
									$month_name1 = "Desember";
									break;
								
								default:
									# code...
									break;
							}

							switch ($data['month_period_end']) {
								case '01':
									$month_name2 = "Januari";
									break;
								case '02':
									$month_name2 = "Februari";
									break;
								case '03':
									$month_name2 = "Maret";
									break;
								case '04':
									$month_name2 = "April";
									break;
								case '05':
									$month_name2 = "Mei";
									break;
								case '06':
									$month_name2 = "Juni";
									break;
								case '07':
									$month_name2 = "Juli";
									break;
								case '08':
									$month_name2 = "Agustus";
									break;
								case '09':
									$month_name2 = "September";
									break;
								case '10':
									$month_name2 = "Oktober";
									break;
								case '11':
									$month_name2 = "November";
									break;
								case '12':
									$month_name2 = "Desember";
									break;
								
								default:
									# code...
									break;
							}

							if ($data['profit_loss_report_type'] == 1){
								$period = $month_name1."-".$month_name2." ".$data['year_period'];
							} else {
								$period = $data['year_period'];
							}

							$minus_month= mktime(0, 0, 0, date($data['month_period'])-1);
							$month = date('m', $minus_month);

							if($month == 12){
								$year = $data['year_period'] - 1;
							} else {
								$year = $data['year_period'];
							}

							$grand_total_all = 0;
							$shu_sebelum_lain_lain = 0;

												foreach ($acctprofitlossreport_top as $keyTop => $valTop) {

													if($valTop['report_type'] == 3){
														$account_subtotal 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($valTop['account_id'], $data['month_period'], $data['month_period'], $data['year_period'], 2, $data['branch_id']);
													
														$account_amount[$valTop['report_no']] = $account_subtotal;
													} else {
													}

													if($valTop['report_type'] == 4){
														if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
															$report_formula 		= explode('#', $valTop['report_formula']);
															$report_operator 		= explode('#', $valTop['report_operator']);
															$total_account_amount	= 0;

															for($i = 0; $i < count($report_formula); $i++){
																if($report_operator[$i] == '-'){
																	if($total_account_amount == 0 ){
																		$total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
																	} else {
																		$total_account_amount = $total_account_amount - $account_amount[$report_formula[$i]];
																	}
																} else if($report_operator[$i] == '+'){
																	if($total_account_amount == 0){
																		$total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
																	} else {
																		$total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
																	}
																}
															}
															$tblitem_top4 = "
																<tr>
																	<td><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
																	<td style=\"text-align:right;\"><div style='font-weight:".$report_bold."'>".number_format($total_account_amount, 2)."</div></td>
																</tr>";
														} else {
															$tblitem_top4 = "";
														}
													} else {
														$tblitem_top4 = "";
													}

													if($valTop['report_type'] == 5){
														if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
															$report_formula 		= explode('#', $valTop['report_formula']);
															$report_operator 		= explode('#', $valTop['report_operator']);
															$total_account_amount	= 0;

															for($i = 0; $i < count($report_formula); $i++){
																if($report_operator[$i] == '-'){
																	if($total_account_amount == 0 ){
																		$total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
																	} else {
																		$total_account_amount = $total_account_amount - $account_amount[$report_formula[$i]];
																	}
																} else if($report_operator[$i] == '+'){
																	if($total_account_amount == 0){
																		$total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
																	} else {
																		$total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
																	}
																}
															}
													
														} 
													} 


													if($valTop['report_type'] == 6){
														if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
															$report_formula 	= explode('#', $valTop['report_formula']);
															$report_operator 	= explode('#', $valTop['report_operator']);

															$grand_total_account_amount1	= 0;
															for($i = 0; $i < count($report_formula); $i++){
																if($report_operator[$i] == '-'){
																	if($grand_total_account_amount1 == 0 ){
																		$grand_total_account_amount1 = $grand_total_account_amount1 + $account_amount[$report_formula[$i]];
																	} else {
																		$grand_total_account_amount1 = $grand_total_account_amount1 - $account_amount[$report_formula[$i]];
																	}
																} else if($report_operator[$i] == '+'){
																	if($grand_total_account_amount1 == 0){
																		$grand_total_account_amount1 = $grand_total_account_amount1 + $account_amount[$report_formula[$i]];
																	} else {
																		$grand_total_account_amount1 = $grand_total_account_amount1 + $account_amount[$report_formula[$i]];
																	}
																}
															}
														} 
													} 

													if($valTop['report_type'] == 7){
														$shu_sebelum_lain_lain = $total_account_amount - $grand_total_account_amount1;
													} 

													if($valTop['report_type'] == 8){
														if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
															$report_formula 		= explode('#', $valTop['report_formula']);
															$report_operator 		= explode('#', $valTop['report_operator']);
															$pendapatan_biaya_lain	= 0;

															for($i = 0; $i < count($report_formula); $i++){
																if($report_operator[$i] == '-'){
																	if($pendapatan_biaya_lain == 0 ){
																		$pendapatan_biaya_lain = $pendapatan_biaya_lain + $account_amount[$report_formula[$i]];
																	} else {
																		$pendapatan_biaya_lain = $pendapatan_biaya_lain - $account_amount[$report_formula[$i]];
																	}
																} else if($report_operator[$i] == '+'){
																	if($pendapatan_biaya_lain == 0){
																		$pendapatan_biaya_lain = $pendapatan_biaya_lain + $account_amount[$report_formula[$i]];
																	} else {
																		$pendapatan_biaya_lain = $pendapatan_biaya_lain + $account_amount[$report_formula[$i]];
																	}
																}
															}
														} 
													} 
												}

								$shu = $shu_sebelum_lain_lain + $pendapatan_biaya_lain;

								// return $shu;

							$data_profit_loss = array (
								"branch_id"				=> $data['branch_id'],
								"profit_loss_amount"	=> $shu,
								"month_period"			=> $data['month_period'],
								"year_period"			=> $data['year_period']

							);

							$chechk_profit_loss = $this->AcctRecalculateEOM_model->getCheckProfitLoss($data_profit_loss);

							if($chechk_profit_loss->num_rows() == 0){
								if($this->AcctRecalculateEOM_model->insertAcctProfitLoss($data_profit_loss)){
									
									$msg = "<div class='alert alert-success alert-dismissable'>
											<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
												Proses SHU Sukses
											</div> ";
									$this->session->set_userdata('message',$msg);
									redirect('AcctProfitLossReportNew1');
								} else {
									
									$msg = "<div class='alert alert-danger alert-dismissable'>
											<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
												Proses SHU Gagal
											</div> ";
									$this->session->set_userdata('message',$msg);
									redirect('AcctProfitLossReportNew1');
								}
							} else {
								if($this->AcctRecalculateEOM_model->deleteAcctProfitLoss($data_profit_loss)){
									if($this->AcctRecalculateEOM_model->insertAcctProfitLoss($data_profit_loss)){
										
										$msg = "<div class='alert alert-success alert-dismissable'>
												<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
													Proses SHU Sukses
												</div> ";
										$this->session->set_userdata('message',$msg);
										redirect('AcctProfitLossReportNew1');
									} else {
										
										$msg = "<div class='alert alert-danger alert-dismissable'>
												<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
													Proses SHU Gagal
												</div> ";
										$this->session->set_userdata('message',$msg);
										redirect('AcctProfitLossReportNew1');
									}
								} else {
									
									$msg = "<div class='alert alert-danger alert-dismissable'>
											<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
												Proses SHU Gagal
											</div> ";
									$this->session->set_userdata('message',$msg);
									redirect('AcctProfitLossReportNew1');
								}
							}
						} else {
							
							$msg = "<div class='alert alert-danger alert-dismissable'>
									<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
										Proses SHU Gagal
									</div> ";
							$this->session->set_userdata('message',$msg);
							redirect('AcctProfitLossReportNew');
						}

					} else {
						
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Proses SHU Gagal
								</div> ";
						$this->session->set_userdata('message',$msg);
						redirect('AcctProfitLossReportNew');
					}
				} else {
					
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Proses SHU Gagal
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('AcctProfitLossReportNew');
				}

			} else {
			
				$msg = "<div class='alert alert-danger alert-dismissable'>
						<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
							Proses SHU Gagal
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('AcctProfitLossReportNew');
			}
		}
	}
?>

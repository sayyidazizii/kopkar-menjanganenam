<?php 
	defined('BASEPATH') or exit('No direct script access allowed');
	ob_start();?>
<?php
	Class AcctProfitLossReportNew1 extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctProfitLossReportNew1_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-AcctProfitLossReportNew1');

			$day 	= date("d");
			$month 	= date("m");
			$year 	= date("Y");

			if(!is_array($sesi)){
				$sesi['month_period_start']						= '';
				$sesi['month_period_end']						= '';
				$sesi['year_period']							= $year;
				$sesi['profit_loss_report_type']				= 1;
				$sesi['profit_loss_report_format']				= 3;
				$sesi['branch_id']								= $auth['branch_id'];
			}

			$data['main_view']['corebranch']					= create_double($this->AcctProfitLossReportNew1_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['acctprofitlossreport_top']		= $this->AcctProfitLossReportNew1_model->getAcctProfitLossReportNew1_Top($sesi['profit_loss_report_format']);
			$data['main_view']['acctprofitlossreport_bottom']	= $this->AcctProfitLossReportNew1_model->getAcctProfitLossReportNew1_Bottom($sesi['profit_loss_report_format']);
			$data['main_view']['monthlist']						= $this->configuration->Month();
			$data['main_view']['profitlossreporttype']			= $this->configuration->ProfitLossReportType();
			$data['main_view']['profitlossreportformat']		= $this->configuration->ProfitLossReportFormat();
			$data['main_view']['content']						= 'AcctProfitLossReport/AcctProfitLossReportNew1_view';

			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				"month_period_start" 		=> $this->input->post('month_period_start',true),
				"month_period_end" 			=> $this->input->post('month_period_end',true),
				"year_period" 				=> $this->input->post('year_period',true),
				"profit_loss_report_type" 	=> $this->input->post('profit_loss_report_type',true),
				"profit_loss_report_format"	=> $this->input->post('profit_loss_report_format',true),
				"branch_id"					=> $this->input->post('branch_id',true),
			);

			$this->session->set_userdata('filter-AcctProfitLossReportNew1',$data);
			redirect('profit-loss');
		}

		public function processPrinting(){
			$auth 	= $this->session->userdata('auth');

			$data = $this->session->userdata('filter-AcctProfitLossReportNew1');
			if(!is_array($data)){
				$data['month_period_start']			= date('m');
				$data['month_period_end']			= date('m');
				$data['year_period']				= date('Y');
				$data['profit_loss_report_type'] 	= 1;
				$data['profit_loss_report_format'] 	= 3;
				$data['branch_id']					= $auth['branch_id'];
			}
			$preference_company 			= $this->AcctProfitLossReportNew1_model->getPreferenceCompany();
			$acctprofitlossreport_top		= $this->AcctProfitLossReportNew1_model->getAcctProfitLossReportNew1_Top($data['profit_loss_report_format']);
			$acctprofitlossreport_bottom	= $this->AcctProfitLossReportNew1_model->getAcctProfitLossReportNew1_Bottom($data['profit_loss_report_format']);
			$branch_name 					= $this->AcctProfitLossReportNew1_model->getBranchName($data['branch_id']);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF(P, PDF_UNIT, 'F4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(6, 6, 6, 6);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 8);

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

			$tbl = "
				<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\">
				    <tr>
				        <td colspan=\"5\"><div style=\"text-align: center; font-size:10px\">LAPORAN PERHITUNGAN SHU <br> ".$preference_company['company_name']." <br> ".$branch_name." <br> Periode ".$period."</div></td>
				    </tr>
				</table>
			";

			$pdf->writeHTML($tbl, true, false, false, false, '');

			$minus_month= mktime(0, 0, 0, date($data['month_period'])-1);
			$month = date('m', $minus_month);

			if($month == 12){
				$year = $data['year_period'] - 1;
			} else {
				$year = $data['year_period'];
			}

			$grand_total_all = 0;
			$shu_sebelum_lain_lain = 0;

			$tblHeader = "
			<table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\">";
		        $tblheader_top = "
		        	<tr>
		        		<td width=\"10%\"></td>
		        		<td width=\"80%\" style=\"border-top:1px black solid;border-left:1px black solid;border-right:1px black solid\">	
		        			<table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\">";	
			        			$tblitem_top = "";
			        			foreach ($acctprofitlossreport_top as $keyTop => $valTop) {
									if($valTop['report_tab'] == 0){
										$report_tab = ' ';
									} else if($valTop['report_tab'] == 1){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									} else if($valTop['report_tab'] == 2){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									} else if($valTop['report_tab'] == 3){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									}

									if($valTop['report_bold'] == 1){
										$report_bold = 'bold';
									} else {
										$report_bold = 'normal';
									}									

									if($valTop['report_type'] == 1){
										$tblitem_top1 = "
											<tr>
												<td colspan=\"2\" style='width: 100%'><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
											</tr>";
									} else {
										$tblitem_top1 = "";
									}

									if($valTop['report_type'] == 2){
										$tblitem_top2 = "
											<tr>
												<td style=\"width: 73%\"><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
												<td style=\"width: 25%\"><div style='font-weight:".$report_bold."'></div></td>
											</tr>";
									} else {
										$tblitem_top2 = "";
									}									

									if($valTop['report_type'] == 3){
										$account_subtotal 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($valTop['account_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

										$tblitem_top3 = "
											<tr>
												<td style=\"width: 73%\"><div style='font-weight:".$report_bold."'>".$report_tab."(".$valTop['account_code'].") ".$valTop['account_name']."</div> </td>
												<td style=\"text-align:right;width: 25%\">".number_format($account_subtotal, 2)."</td>
											</tr>";
										$account_amount[$valTop['report_no']] = $account_subtotal;
									} else {
										$tblitem_top3 = "";
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
											$tblitem_top5 = "
												<tr>
													<td><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
													<td style=\"text-align:right;\"><div style='font-weight:".$report_bold."'>".number_format($total_account_amount, 2)."</div></td>
												</tr>";
										} else {
											$tblitem_top5 = "";
										}
									} else {
										$tblitem_top5 = "";
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
											// if($valTop['category_type'] == 1){
											// 	$grand_total_all += $grand_total_account_amount1;
											// }
										} else {
										}
									} else {
									}

									if($valTop['report_type'] == 7){
										$shu_sebelum_lain_lain = $total_account_amount - $grand_total_account_amount1;
										
											$tblitem_top7 = "
												<tr>
													<td><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
													<td style=\"text-align:right;\"><div style='font-weight:".$report_bold."'>".number_format($shu_sebelum_lain_lain, 2)."</div></td>
												</tr>";
									} else {
										$tblitem_top7 = "";
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
											$tblitem_top8 = "
												<tr>
													<td><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
													<td style=\"text-align:right;\"><div style='font-weight:".$report_bold."'>".number_format($pendapatan_biaya_lain, 2)."</div></td>
												</tr>";
										} else {
											$tblitem_top8 = "";
										}
									} else {
										$tblitem_top8 = "";
									}

									$tblitem_top .= $tblitem_top1.$tblitem_top2.$tblitem_top3.$tblitem_top4.$tblitem_top5.$tblitem_top6.$tblitem_top7.$tblitem_top8;

								}
		        $tblfooter_top	= "
		        		</table>
		        	</td>
		        	<td width=\"10%\"></td>
		        </tr>";

				$tblheader_bottom = "
					<tr>
						<td width=\"10%\"></td>
			        	<td width=\"80%\" style=\"border-bottom:1px black solid;border-left:1px black solid;border-right:1px black solid\">	
			        		<table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\">";		
			        			$tblitem_bottom = "";
			        			foreach ($acctprofitlossreport_bottom as $keyBottom => $valBottom) {
									if($valBottom['report_tab'] == 0){
										$report_tab = ' ';
									} else if($valBottom['report_tab'] == 1){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									} else if($valBottom['report_tab'] == 2){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									} else if($valBottom['report_tab'] == 3){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									}

									if($valBottom['report_bold'] == 1){
										$report_bold = 'bold';
									} else {
										$report_bold = 'normal';
									}									

									if($valBottom['report_type'] == 1){
										$tblitem_bottom1 = "
											<tr>
												<td colspan=\"2\"><div style=\"font-weight:".$report_bold."\">".$report_tab."".$valBottom['account_name']."</div></td>
											</tr>";
									} else {
										$tblitem_bottom1 = "";
									}

									if($valBottom['report_type'] == 2){
										$tblitem_bottom2 = "
											<tr>
												<td style=\"width: 73%\"><div style=\"font-weight:".$report_bold."\">".$report_tab."".$valBottom['account_name']."</div></td>
												<td style=\"width: 25%\"><div style=\"font-weight:".$report_bold."\"></div></td>
											</tr>";
									} else {
										$tblitem_bottom2 = "";
									}									

									if($valBottom['report_type']	== 3){
										$account_subtotal 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($valBottom['account_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

										$tblitem_bottom3 = "
										<tr>
											<td style=\"width: 73%\"><div style=\"font-weight:".$report_bold."\">".$report_tab."(".$valBottom['account_code'].") ".$valBottom['account_name']."</div> </td>
											<td style=\"text-align:right;width: 25%\">".number_format($account_subtotal, 2)."</td>
										</tr>";

										$account_amount[$valBottom['report_no']] = $account_subtotal;
									} else {
										$tblitem_bottom3 = "";
									}

									if($valBottom['report_type'] == 5){
										if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
											$report_formula 		= explode('#', $valBottom['report_formula']);
											$report_operator 		= explode('#', $valBottom['report_operator']);
											$total_account_amount2	= 0;

											for($i = 0; $i < count($report_formula); $i++){
												if($report_operator[$i] == '-'){
													if($total_account_amount2 == 0 ){
														$total_account_amount2 = $total_account_amount2 + $account_amount[$report_formula[$i]];
													} else {
														$total_account_amount2 = $total_account_amount2 - $account_amount[$report_formula[$i]];
													}
												} else if($report_operator[$i] == '+'){
													if($total_account_amount2 == 0){
														$total_account_amount2 = $total_account_amount2 + $account_amount[$report_formula[$i]];
													} else {
														$total_account_amount2 = $total_account_amount2 + $account_amount[$report_formula[$i]];
													}
												}
											}
											$tblitem_bottom5 = "
												<tr>
													<td><div style=\"font-weight:".$report_bold."\">".$report_tab."".$valBottom['account_name']."</div></td>
													<td style=\"text-align:righr;\"><div style=\"font-weight:".$report_bold."\">".number_format($total_account_amount2, 2)."</div></td>
												</tr>";
										} else {
											$tblitem_bottom5 = "";
										}
									} else {
										$tblitem_bottom5 = "";
									}

									$tblitem_bottom .= $tblitem_bottom1.$tblitem_bottom2.$tblitem_bottom3.$tblitem_bottom5;

									if($valBottom['report_type'] == 6){
										if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
											$report_formula 				= explode('#', $valBottom['report_formula']);
											$report_operator 				= explode('#', $valBottom['report_operator']);
											$grand_total_account_amount2	= 0;

											for($i = 0; $i < count($report_formula); $i++){
												if($report_operator[$i] == '-'){
													if($grand_total_account_amount2 == 0 ){
														$grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
													} else {
														$grand_total_account_amount2 = $grand_total_account_amount2 - $account_amount[$report_formula[$i]];
													}
												} else if($report_operator[$i] == '+'){
													if($grand_total_account_amount2 == 0){
														$grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
													} else {
														$grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
													}
												}
											}
											
											if($valBottom['category_type'] == 1){
												$grand_total_all += $grand_total_account_amount2;
											}
										} else {
										}
									} else {
									}
								}

		       	$tblfooter_bottom = "
		       			</table>
		        	</td>
		        	<td width=\"10%\"></td>
		        </tr>";

				$shu = $grand_total_all;
				
				$income_tax 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($preference_company['account_income_tax_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

				$shu = $shu_sebelum_lain_lain + $pendapatan_biaya_lain;

			$tblFooter = "
			    <tr>
			    	<td width=\"10%\"></td>
			    	<td style=\"border:1px black solid;\">
			    		<table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\">
							<tr>
								<td style=\"width: 75%\"><div style=\"font-weight:bold;font-size:14px\">SHU BERJALAN</div></td>
								<td style=\"width: 23%; text-align:right;\"><div style=\"font-weight:bold; font-size:14px\">".number_format($shu, 2)."</div></td>
							</tr>
							
			    		</table>
			    	</td>
			    	<td width=\"10%\"></td>
			    </tr>
			</table>";

			$table = $tblHeader.$tblheader_top.$tblitem_top.$tblfooter_top.$tblheader_bottom.$tblitem_bottom.$tblfooter_bottom.$tblFooter;

			$pdf->writeHTML($table, true, false, false, false, '');

			ob_clean();

			$filename = 'Laporan Rugi Laba.pdf';
			$pdf->Output($filename, 'I');
		}

		public function exportAcctProfitLossReportNew1(){
			$auth = $this->session->userdata('auth');
			$data = $this->session->userdata('filter-AcctProfitLossReportNew1');
			if(!is_array($data)){
				$data['month_period_start']			= date('m');
				$data['month_period_end']			= date('m');
				$data['year_period']				= date("Y");
				$data['profit_loss_report_type'] 	= 1;
				$data['profit_loss_report_format'] 	= 3;
				$data['branch_id']					= $auth['branch_id'];
			}
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

			$grand_total_all = 0;

			if(!empty($acctprofitlossreport_top)){
				$this->load->library('Excel');
				
				$this->excel->getProperties()->setCreator("SIS Integrated System")
									 ->setLastModifiedBy("SIS Integrated System")
									 ->setTitle("Laporan Perhitungan SHU")
									 ->setSubject("")
									 ->setDescription("Laporan Perhitungan SHU")
									 ->setKeywords("SHU, Perhitungan, Laporan")
									 ->setCategory("Laporan Perhitungan SHU");
									 
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				$this->excel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(70);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				
				$this->excel->getActiveSheet()->mergeCells("B1:C1");
				$this->excel->getActiveSheet()->mergeCells("B2:C2");
				$this->excel->getActiveSheet()->mergeCells("B3:C3");
				$this->excel->getActiveSheet()->mergeCells("B4:C4");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B2')->getFont()->setBold(true)->setSize(12);

				$this->excel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3')->getFont()->setBold(true)->setSize(12);
				
				$this->excel->getActiveSheet()->setCellValue('B1',"Laporan Perhitungan SHU ");	
				$this->excel->getActiveSheet()->setCellValue('B2',$preference_company['company_name']);	
				$this->excel->getActiveSheet()->setCellValue('B3',$branch_name);	
				$this->excel->getActiveSheet()->setCellValue('B4',"Periode ".$period);	
				
				$j 				= 5;
				$no 			= 0;
				$grand_total 	= 0;
				
				foreach($acctprofitlossreport_top as $keyTop => $valTop){
					if(is_numeric($keyTop)){
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->getStyle('B'.$j.':C'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('A'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

						if($valTop['report_tab'] == 0){
							$report_tab = ' ';
						} else if($valTop['report_tab'] == 1){
							$report_tab = '     ';
						} else if($valTop['report_tab'] == 2){
							$report_tab = '          ';
						} else if($valTop['report_tab'] == 3){
							$report_tab = '               ';
						}

						if($valTop['report_bold'] == 1){
							$this->excel->getActiveSheet()->getStyle('B'.$j)->getFont()->setBold(true);	
							$this->excel->getActiveSheet()->getStyle('C'.$j)->getFont()->setBold(true);	
						} else {
						
						}

						if($valTop['report_type'] == 1){
							$this->excel->getActiveSheet()->mergeCells("B".$j.":C".$j."");
							$this->excel->getActiveSheet()->setCellValue('B'.$j, $valTop['account_name']);

							$j++;
						}
						
						if($valTop['report_type']	== 2){
							$this->excel->getActiveSheet()->setCellValue('B'.$j, $valTop['account_name']);

							$j++;
						}

						if($valTop['report_type']	== 3){
							$account_subtotal 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($valTop['account_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

							$this->excel->getActiveSheet()->setCellValue('A'.$j, $valTop['account_code']);
							$this->excel->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valTop['account_name']);
							$this->excel->getActiveSheet()->setCellValue('C'.$j, $account_subtotal);

							$account_amount[$valTop['report_no']] = $account_subtotal;

							$j++;
						}

						if($valTop['report_type'] == 4){
							if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
								$report_formula 	= explode('#', $valTop['report_formula']);
								$report_operator 	= explode('#', $valTop['report_operator']);

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

								$this->excel->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valTop['account_name']);
								$this->excel->getActiveSheet()->setCellValue('C'.$j, $total_account_amount);
								$j++;
							}
						}

						if($valTop['report_type'] == 5){
							if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
								$report_formula 	= explode('#', $valTop['report_formula']);
								$report_operator 	= explode('#', $valTop['report_operator']);

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

								$this->excel->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valTop['account_name']);
								$this->excel->getActiveSheet()->setCellValue('C'.$j, $total_account_amount);
								$j++;
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

								// if($valTop['category_type'] == 1){
								// 	$grand_total_all += $grand_total_account_amount1;
								// }

								$this->excel->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valTop['account_name']);
								$this->excel->getActiveSheet()->setCellValue('C'.$j, $grand_total_account_amount1);
								$j++;
							}
						}


						if($valTop['report_type'] == 7){
							$shu_sebelum_lain_lain = $total_account_amount - $grand_total_account_amount1;

								$this->excel->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valTop['account_name']);
								$this->excel->getActiveSheet()->setCellValue('C'.$j, $shu_sebelum_lain_lain);
								$j++;
						}


						if($valTop['report_type'] == 8){
							if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
								$report_formula 	= explode('#', $valTop['report_formula']);
								$report_operator 	= explode('#', $valTop['report_operator']);

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

								$this->excel->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valTop['account_name']);
								$this->excel->getActiveSheet()->setCellValue('C'.$j, $pendapatan_biaya_lain);
								$j++;
							}
						}

					}else{
						continue;
					}
				}

				$j--;

				// foreach($acctprofitlossreport_bottom as $keyBottom => $valBottom){
				// 	if(is_numeric($keyTop)){
				// 		$this->excel->setActiveSheetIndex(0);
				// 		$this->excel->getActiveSheet()->getStyle('B'.$j.':C'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				// 		$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				// 		$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				// 		if($valBottom['report_tab'] == 0){
				// 			$report_tab = ' ';
				// 		} else if($valBottom['report_tab'] == 1){
				// 			$report_tab = '     ';
				// 		} else if($valBottom['report_tab'] == 2){
				// 			$report_tab = '          ';
				// 		} else if($valBottom['report_tab'] == 3){
				// 			$report_tab = '               ';
				// 		}

				// 		if($valBottom['report_bold'] == 1){
				// 			$this->excel->getActiveSheet()->getStyle('B'.$j)->getFont()->setBold(true);	
				// 			$this->excel->getActiveSheet()->getStyle('C'.$j)->getFont()->setBold(true);	
				// 		} else {
						
				// 		}

				// 		if($valBottom['report_type'] == 1){
				// 			$this->excel->getActiveSheet()->mergeCells("B".$j.":C".$j."");
				// 			$this->excel->getActiveSheet()->setCellValue('B'.$j, $valBottom['account_name']);
				// 		}
						
				// 		if($valBottom['report_type']	== 2){
				// 			$this->excel->getActiveSheet()->setCellValue('B'.$j, $valBottom['account_name']);
				// 		}

				// 		if($valBottom['report_type']	== 3){
				// 			$account_subtotal 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($valBottom['account_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

				// 			$this->excel->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valBottom['account_name']);
				// 			$this->excel->getActiveSheet()->setCellValue('C'.$j, $account_subtotal);

				// 			$account_amount[$valBottom['report_no']] = $account_subtotal;
				// 		}

				// 		if($valBottom['report_type'] == 5){
				// 			if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
				// 				$report_formula 	= explode('#', $valBottom['report_formula']);
				// 				$report_operator 	= explode('#', $valBottom['report_operator']);

				// 				$total_account_amount	= 0;
				// 				for($i = 0; $i < count($report_formula); $i++){
				// 					if($report_operator[$i] == '-'){
				// 						if($total_account_amount == 0 ){
				// 							$total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
				// 						} else {
				// 							$total_account_amount = $total_account_amount - $account_amount[$report_formula[$i]];
				// 						}
				// 					} else if($report_operator[$i] == '+'){
				// 						if($total_account_amount == 0){
				// 							$total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
				// 						} else {
				// 							$total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
				// 						}
				// 					}
				// 				}

				// 				$this->excel->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valBottom['account_name']);
				// 				$this->excel->getActiveSheet()->setCellValue('C'.$j, $total_account_amount);
				// 			}
				// 		}

				// 		if($valBottom['report_type'] == 6){
				// 			if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
				// 				$report_formula 	= explode('#', $valBottom['report_formula']);
				// 				$report_operator 	= explode('#', $valBottom['report_operator']);

				// 				$grand_total_account_amount2	= 0;
				// 				for($i = 0; $i < count($report_formula); $i++){
				// 					if($report_operator[$i] == '-'){
				// 						if($grand_total_account_amount2 == 0 ){
				// 							$grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
				// 						} else {
				// 							$grand_total_account_amount2 = $grand_total_account_amount2 - $account_amount[$report_formula[$i]];
				// 						}
				// 					} else if($report_operator[$i] == '+'){
				// 						if($grand_total_account_amount2 == 0){
				// 							$grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
				// 						} else {
				// 							$grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
				// 						}
				// 					}
				// 				}

				// 				if($valBottom['category_type'] == 1){
				// 					$grand_total_all += $grand_total_account_amount2;
				// 				}

				// 				$this->excel->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valBottom['account_name']);
				// 				$this->excel->getActiveSheet()->setCellValue('C'.$j, $grand_total_account_amount2);
				// 			}
				// 		}
				// 	}else{
				// 		continue;
				// 	}
				// 	$j++;
				// }

				$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				$this->excel->getActiveSheet()->getStyle('B'.$j.':C'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle("B".($j-3).":C".$j)->getFont()->setBold(true);	

				// $shu = $grand_total_all;
				$shu = $shu_sebelum_lain_lain + $pendapatan_biaya_lain;
				
				// $income_tax 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($preference_company['account_income_tax_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

				$this->excel->getActiveSheet()->setCellValue('B'.($j+1), "SHU TAHUN BERJALAN");
				$this->excel->getActiveSheet()->setCellValue('C'.($j+1), $shu);

				// $this->excel->getActiveSheet()->setCellValue('B'.($j-2), "SHU SEBELUM PAJAK");
				// $this->excel->getActiveSheet()->setCellValue('C'.($j-2), $shu);
				// $this->excel->getActiveSheet()->setCellValue('B'.($j-1), "PAJAK PENGHASILAN");
				// $this->excel->getActiveSheet()->setCellValue('C'.($j-1), $income_tax);
				// $this->excel->getActiveSheet()->setCellValue('B'.$j, "SHU SETELAH PAJAK");
				// $this->excel->getActiveSheet()->setCellValue('C'.$j, $shu - $income_tax);

				$i = $j + 2;

				$this->excel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$this->excel->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$this->excel->getActiveSheet()->setCellValue('C'.$i, $this->AcctProfitLossReportNew1_model->getBranchCity($data['branch_id']).", ".date('d-m-Y'));

				$k = $i + 2;

				$this->excel->getActiveSheet()->getStyle('B'.$k)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('C'.$k)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				$this->excel->getActiveSheet()->setCellValue('B'.$k, "Yang Melaporkan");
				$this->excel->getActiveSheet()->setCellValue('C'.$k, "Manajer");

				$l = $k + 6;

				$this->excel->getActiveSheet()->getStyle('B'.$l)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('C'.$l)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle("B".$l.":C".$l)->getFont()->setBold(true);	

				$this->excel->getActiveSheet()->setCellValue('B'.$l, "ADMIN");
				$this->excel->getActiveSheet()->setCellValue('C'.$l, strtoupper($this->AcctProfitLossReportNew1_model->getBranchManager($data['branch_id'])));

				$filename='Laporan Rugi Laba '.$period.'.xls';
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="'.$filename.'"');
				header('Cache-Control: max-age=0');
							 
				$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
				ob_end_clean();
				$objWriter->save('php://output');
			}else{
				echo "Maaf data yang di eksport tidak ada !";
			}
		}

		public function getProfitLossAmount(){

			$auth 	= $this->session->userdata('auth');

			$data = $this->session->userdata('filter-AcctProfitLossReportNew1');
			if(!is_array($data)){
				$data['month_period_start']			= date('m');
				$data['month_period_end']			= date('m');
				$data['year_period']				= date('Y');
				$data['profit_loss_report_type'] 	= 1;
				$data['profit_loss_report_format'] 	= 3;
				$data['branch_id']					= $auth['branch_id'];
			}
			$preference_company 			= $this->AcctProfitLossReportNew1_model->getPreferenceCompany();
			$acctprofitlossreport_top		= $this->AcctProfitLossReportNew1_model->getAcctProfitLossReportNew1_Top($data['profit_loss_report_format']);
			$acctprofitlossreport_bottom	= $this->AcctProfitLossReportNew1_model->getAcctProfitLossReportNew1_Bottom($data['profit_loss_report_format']);
			$branch_name 					= $this->AcctProfitLossReportNew1_model->getBranchName($data['branch_id']);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF(P, PDF_UNIT, 'F4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(6, 6, 6, 6);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 8);

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

			$tbl = "
				<table cellspacing=\"0\" cellpadding=\"5\" border=\"0\">
				    <tr>
				        <td colspan=\"5\"><div style=\"text-align: center; font-size:10px\">LAPORAN PERHITUNGAN SHU <br> ".$preference_company['company_name']." <br> ".$branch_name." <br> Periode ".$period."</div></td>
				    </tr>
				</table>
			";

			$pdf->writeHTML($tbl, true, false, false, false, '');

			$minus_month= mktime(0, 0, 0, date($data['month_period'])-1);
			$month = date('m', $minus_month);

			if($month == 12){
				$year = $data['year_period'] - 1;
			} else {
				$year = $data['year_period'];
			}

			$grand_total_all = 0;
			$shu_sebelum_lain_lain = 0;

			$tblHeader = "
			<table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\">";
		        $tblheader_top = "
		        	<tr>
		        		<td width=\"10%\"></td>
		        		<td width=\"80%\" style=\"border-top:1px black solid;border-left:1px black solid;border-right:1px black solid\">	
		        			<table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\">";	
			        			$tblitem_top = "";
			        			foreach ($acctprofitlossreport_top as $keyTop => $valTop) {
									if($valTop['report_tab'] == 0){
										$report_tab = ' ';
									} else if($valTop['report_tab'] == 1){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									} else if($valTop['report_tab'] == 2){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									} else if($valTop['report_tab'] == 3){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									}

									if($valTop['report_bold'] == 1){
										$report_bold = 'bold';
									} else {
										$report_bold = 'normal';
									}									

									if($valTop['report_type'] == 1){
										$tblitem_top1 = "
											<tr>
												<td colspan=\"2\" style='width: 100%'><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
											</tr>";
									} else {
										$tblitem_top1 = "";
									}

									if($valTop['report_type'] == 2){
										$tblitem_top2 = "
											<tr>
												<td style=\"width: 73%\"><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
												<td style=\"width: 25%\"><div style='font-weight:".$report_bold."'></div></td>
											</tr>";
									} else {
										$tblitem_top2 = "";
									}									

									if($valTop['report_type'] == 3){
										$account_subtotal 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($valTop['account_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

										$tblitem_top3 = "
											<tr>
												<td style=\"width: 73%\"><div style='font-weight:".$report_bold."'>".$report_tab."(".$valTop['account_code'].") ".$valTop['account_name']."</div> </td>
												<td style=\"text-align:right;width: 25%\">".number_format($account_subtotal, 2)."</td>
											</tr>";
										$account_amount[$valTop['report_no']] = $account_subtotal;
									} else {
										$tblitem_top3 = "";
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
											$tblitem_top5 = "
												<tr>
													<td><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
													<td style=\"text-align:right;\"><div style='font-weight:".$report_bold."'>".number_format($total_account_amount, 2)."</div></td>
												</tr>";
										} else {
											$tblitem_top5 = "";
										}
									} else {
										$tblitem_top5 = "";
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
											// if($valTop['category_type'] == 1){
											// 	$grand_total_all += $grand_total_account_amount1;
											// }
										} else {
										}
									} else {
									}

									if($valTop['report_type'] == 7){
										$shu_sebelum_lain_lain = $total_account_amount - $grand_total_account_amount1;
										
											$tblitem_top7 = "
												<tr>
													<td><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
													<td style=\"text-align:right;\"><div style='font-weight:".$report_bold."'>".number_format($shu_sebelum_lain_lain, 2)."</div></td>
												</tr>";
									} else {
										$tblitem_top7 = "";
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
											$tblitem_top8 = "
												<tr>
													<td><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
													<td style=\"text-align:right;\"><div style='font-weight:".$report_bold."'>".number_format($pendapatan_biaya_lain, 2)."</div></td>
												</tr>";
										} else {
											$tblitem_top8 = "";
										}
									} else {
										$tblitem_top8 = "";
									}

									$tblitem_top .= $tblitem_top1.$tblitem_top2.$tblitem_top3.$tblitem_top4.$tblitem_top5.$tblitem_top6.$tblitem_top7.$tblitem_top8;

								}
		        $tblfooter_top	= "
		        		</table>
		        	</td>
		        	<td width=\"10%\"></td>
		        </tr>";

				$tblheader_bottom = "
					<tr>
						<td width=\"10%\"></td>
			        	<td width=\"80%\" style=\"border-bottom:1px black solid;border-left:1px black solid;border-right:1px black solid\">	
			        		<table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\">";		
			        			$tblitem_bottom = "";
			        			foreach ($acctprofitlossreport_bottom as $keyBottom => $valBottom) {
									if($valBottom['report_tab'] == 0){
										$report_tab = ' ';
									} else if($valBottom['report_tab'] == 1){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									} else if($valBottom['report_tab'] == 2){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									} else if($valBottom['report_tab'] == 3){
										$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
									}

									if($valBottom['report_bold'] == 1){
										$report_bold = 'bold';
									} else {
										$report_bold = 'normal';
									}									

									if($valBottom['report_type'] == 1){
										$tblitem_bottom1 = "
											<tr>
												<td colspan=\"2\"><div style=\"font-weight:".$report_bold."\">".$report_tab."".$valBottom['account_name']."</div></td>
											</tr>";
									} else {
										$tblitem_bottom1 = "";
									}

									if($valBottom['report_type'] == 2){
										$tblitem_bottom2 = "
											<tr>
												<td style=\"width: 73%\"><div style=\"font-weight:".$report_bold."\">".$report_tab."".$valBottom['account_name']."</div></td>
												<td style=\"width: 25%\"><div style=\"font-weight:".$report_bold."\"></div></td>
											</tr>";
									} else {
										$tblitem_bottom2 = "";
									}									

									if($valBottom['report_type']	== 3){
										$account_subtotal 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($valBottom['account_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

										$tblitem_bottom3 = "
										<tr>
											<td style=\"width: 73%\"><div style=\"font-weight:".$report_bold."\">".$report_tab."(".$valBottom['account_code'].") ".$valBottom['account_name']."</div> </td>
											<td style=\"text-align:right;width: 25%\">".number_format($account_subtotal, 2)."</td>
										</tr>";

										$account_amount[$valBottom['report_no']] = $account_subtotal;
									} else {
										$tblitem_bottom3 = "";
									}

									if($valBottom['report_type'] == 5){
										if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
											$report_formula 		= explode('#', $valBottom['report_formula']);
											$report_operator 		= explode('#', $valBottom['report_operator']);
											$total_account_amount2	= 0;

											for($i = 0; $i < count($report_formula); $i++){
												if($report_operator[$i] == '-'){
													if($total_account_amount2 == 0 ){
														$total_account_amount2 = $total_account_amount2 + $account_amount[$report_formula[$i]];
													} else {
														$total_account_amount2 = $total_account_amount2 - $account_amount[$report_formula[$i]];
													}
												} else if($report_operator[$i] == '+'){
													if($total_account_amount2 == 0){
														$total_account_amount2 = $total_account_amount2 + $account_amount[$report_formula[$i]];
													} else {
														$total_account_amount2 = $total_account_amount2 + $account_amount[$report_formula[$i]];
													}
												}
											}
											$tblitem_bottom5 = "
												<tr>
													<td><div style=\"font-weight:".$report_bold."\">".$report_tab."".$valBottom['account_name']."</div></td>
													<td style=\"text-align:righr;\"><div style=\"font-weight:".$report_bold."\">".number_format($total_account_amount2, 2)."</div></td>
												</tr>";
										} else {
											$tblitem_bottom5 = "";
										}
									} else {
										$tblitem_bottom5 = "";
									}

									$tblitem_bottom .= $tblitem_bottom1.$tblitem_bottom2.$tblitem_bottom3.$tblitem_bottom5;

									if($valBottom['report_type'] == 6){
										if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
											$report_formula 				= explode('#', $valBottom['report_formula']);
											$report_operator 				= explode('#', $valBottom['report_operator']);
											$grand_total_account_amount2	= 0;

											for($i = 0; $i < count($report_formula); $i++){
												if($report_operator[$i] == '-'){
													if($grand_total_account_amount2 == 0 ){
														$grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
													} else {
														$grand_total_account_amount2 = $grand_total_account_amount2 - $account_amount[$report_formula[$i]];
													}
												} else if($report_operator[$i] == '+'){
													if($grand_total_account_amount2 == 0){
														$grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
													} else {
														$grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
													}
												}
											}
											
											if($valBottom['category_type'] == 1){
												$grand_total_all += $grand_total_account_amount2;
											}
										} else {
										}
									} else {
									}
								}

		       	$tblfooter_bottom = "
		       			</table>
		        	</td>
		        	<td width=\"10%\"></td>
		        </tr>";

				$shu = $grand_total_all;
				
				$income_tax 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($preference_company['account_income_tax_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

				$shu = $shu_sebelum_lain_lain + $pendapatan_biaya_lain;


			// $table = $tblHeader.$tblheader_top.$tblitem_top.$tblfooter_top.$tblheader_bottom.$tblitem_bottom.$tblfooter_bottom.$tblFooter;

			// $pdf->writeHTML($table, true, false, false, false, '');

			// ob_clean();

			// $filename = 'Laporan Rugi Laba.pdf';
			// $pdf->Output($filename, 'I');

				echo $shu;
		}

		//update mutation
		public function updateMutationAmount() {
			$auth = $this->session->userdata('auth');
			
			$month = '06';
			$next_month = '07';
			$year  = '2024';

			$data = array (
				"branch_id" => 2,
				"month_period" => $month,
				"year_period" => $year,
			);

			// Dapatkan semua ID rekening tabungan yang perlu diproses
			$account_ids = $this->AcctProfitLossReportNew1_model->getAllAccountIds($month, $year);

			// Debug: Tampilkan semua account_ids yang diambil
			echo "<pre>";
			print_r($account_ids);
			echo "</pre>";

			foreach ($account_ids as $account_id) {
				// Ambil saldo akhir bulan sebelumnya
				$previous_last_balance = $this->AcctProfitLossReportNew1_model->getLastBalanceFromPreviousMonth($account_id, $month, $year);
				
				// Debug: Tampilkan saldo akhir bulan sebelumnya
				echo "<pre>";
				echo "Previous Last Balance for Account ID $account_id: " . $previous_last_balance;
				echo "</pre>";

				$mutation_in_amount = isset($previous_last_balance) ? $previous_last_balance : 0;

				// Dapatkan detail semua rekening tabungan untuk bulan berikutnya
				$acctsavingsaccountdetailAll = $this->AcctProfitLossReportNew1_model->getAcctAccountDetailAll($account_id, $next_month, $year);

				// Debug: Tampilkan data yang diambil dari getAcctAccountDetailAll
				echo "<pre>";
				echo "Data retrieved for Account ID $account_id for month $next_month and year $year:";
				print_r($acctsavingsaccountdetailAll);
				echo "</pre>";

				foreach ($acctsavingsaccountdetailAll as $key => $val) {
					// Hitung saldo terakhir untuk iterasi saat ini
					$last_balance = ($mutation_in_amount + $val['last_balance']);

					// Hanya update jika last_balance lebih dari 0.00 (baik positif maupun negatif)
					if ($val['mutation_in_amount'] != 0.00 && $val['mutation_in_amount'] != 0.00) {
						// Debug: Tampilkan data yang akan di-update
						echo "<pre>";
						echo "Data to be updated for Account ID $val[account_id]:";
						print_r([
							'account_id' => $val['account_id'],
							'last_balance' => $last_balance,
							'month_period' => $next_month,
							'year_period' => $year
						]);
						echo "</pre>";

						// Siapkan data untuk memperbarui saldo pembukaan dan mutasi
						$newdata = array(
							'account_id' => $val['account_id'],
							'last_balance' => $last_balance,
							'month_period' => $next_month,
							'year_period' => $year,
						);

						// Debug: Tampilkan data update sebelum melakukan update ke database
						echo "<pre>";
						echo "New data prepared for update:";
						print_r($newdata);
						echo "</pre>";

						// Perbarui saldo terakhir
						$this->AcctProfitLossReportNew1_model->updateMutation($newdata);

						// Update saldo awal untuk iterasi berikutnya
						$mutation_in_amount = $last_balance;
					}
				}
			}
		}

	}
?>

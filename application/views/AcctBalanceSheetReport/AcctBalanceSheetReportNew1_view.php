<style>
  
	th{
		font-size:14px  !important;
		font-weight: bold !important;
		text-align:center !important;
		margin : 0 auto;
		vertical-align:middle !important;
	}
	td{
		font-size:12px  !important;
		font-weight: normal !important;
	}

	.flexigrid div.pDiv input {
		vertical-align:middle !important;
	}
	
	.flexigrid div.pDiv div.pDiv2 {
		margin-bottom: 10px !important;
	}
	

</style>
<script>
	base_url = '<?php echo base_url();?>';
</script>
			<!-- BEGIN PAGE TITLE & BREADCRUMB-->
<div class="page-bar">
	<ul class="page-breadcrumb">
		<li>
			<a href="<?php echo base_url();?>">
				Home
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>AcctBalanceSheetReportNew1">
				Laporan Neraca
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>

<?php
	$auth 		= $this->session->userdata('auth');
	$data 		= $this->session->userdata('filter-AcctBalanceSheetReportNew1');
	$year_now 	=	date('Y');
	if(!is_array($data)){
		$data['month_period']			= date('m');
		$data['year_period']			= $year_now;
		$data['branch_id']				= $auth['branch_id'];
	}

	if($auth['branch_status'] == 1){
		if(empty($data['branch_id'])){
			$data['branch_id'] = $auth['branch_id'];
		}
	} else {
		$data['branch_id'] = $auth['branch_id'];
	}
	
	for($i = ($year_now-2); $i<($year_now+2); $i++){
		$year[$i] = $i;
	} 
?> 
			<!-- END PAGE TITLE & BREADCRUMB-->

<?php echo form_open('AcctBalanceSheetReportNew1/filter',array('id' => 'myform', 'class' => '')); ?>
<div class="row">
	<div class="col-md-12">	
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Laporan Neraca
				</div>
			</div>
			<div class="portlet-body">
				<!-- BEGIN FORM-->
				<div class="form-body form">
				<?php
					echo $this->session->userdata('message');
					$this->session->unset_userdata('message');
				?>
				<?php if($auth['branch_status'] == 1) { ?>
				<div class="row">
					<div class = "col-md-3">
						<div class="form-group form-md-line-input">
								<?php
									echo form_dropdown('month_period', $monthlist,set_value('month_period',$data['month_period']),'id="month_period" class="form-control select2me"');
								?>
								<label>Periode
									<span class="required">
										*
									</span>
								</label>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group form-md-line-input">
							<?php
								echo form_dropdown('year_period', $year,set_value('year_period',$data['year_period']),'id="year_period" class="form-control select2me" ');
							?>
							<label></label>
						</div>
					</div>
					<div class = "col-md-6">
						<div class="form-group form-md-line-input">
							<?php echo form_dropdown('branch_id', $corebranch, set_value('branch_id',$data['branch_id']),'id="branch_id" class="form-control select2me"');?>
							<label class="control-label">Cabang
								<span class="required">
									*
								</span>
							</label>
						</div>
					</div>
					<div class = "col-md-6">
						<div class="form-group form-md-line-input">
							<button type="submit" class="btn green-jungle"><i class="fa fa-search"></i> Cari</button>
							<label class="control-label">
							</label>
						</div>
					</div>
				</div>
				<?php } else {?>
				<div class="row">
					<div class = "col-md-3">
						<div class="form-group form-md-line-input">
								<?php
									echo form_dropdown('month_period', $monthlist,set_value('month_period',$data['month_period']),'id="month_period" class="form-control select2me"');
								?>
								<label>Periode
									<span class="required">
										*
									</span>
								</label>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group form-md-line-input">
							<?php
								echo form_dropdown('year_period', $year,set_value('year_period',$data['year_period']),'id="year_period" class="form-control select2me" ');
							?>
							<label></label>
						</div>
					</div>
					<div class = "col-md-6">
						<div class="form-group form-md-line-input">
							<button type="submit" class="btn green-jungle"><i class="fa fa-search"></i> Cari</button>
							<label class="control-label">
							</label>
						</div>
					</div>
				</div>
				<?php  } ?>
<?php echo form_close(); ?>
<div class="row">
	<div class="col-md-12">	
		<div class="portlet box blue">
			<div class="portlet-body">
				<!-- BEGIN FORM-->
				<div class="form-body form">
					
						<br>
						<?php
							echo form_open('AcctBalanceSheetReportNew1/processPrinting'); 

							$preferencecompany = $this->AcctBalanceSheetReportNew1_model->getPreferenceCompany();

							$day 	= date('d');
							$month 	= $data['month_period'];
							$year 	= $data['year_period'];

							if($month == 12){
								$last_month 	= 01;
								$last_year 		= $year + 1;
							} else {
								$last_month 	= $month + 1;
								$last_year 		= $year;
							}
						
							switch ($month) {
								case '01':
									$month_name = "Januari";
									break;
								case '02':
									$month_name = "Februari";
									break;
								case '03':
									$month_name = "Maret";
									break;
								case '04':
									$month_name = "April";
									break;
								case '05':
									$month_name = "Mei";
									break;
								case '06':
									$month_name = "Juni";
									break;
								case '07':
									$month_name = "Juli";
									break;
								case '08':
									$month_name = "Agustus";
									break;
								case '09':
									$month_name = "September";
									break;
								case '10':
									$month_name = "Oktober";
									break;
								case '11':
									$month_name = "November";
									break;
								case '12':
									$month_name = "Desember";
									break;
								
								default:
									# code...
									break;
							}

							$period = $day." ".$month_name." ".$year;
							
						?>
						<table class="table table-bordered table-advance table-hover">
							<thead>
								<tr>
									<td colspan='2' style='text-align:center;'>
										<div style='font-weight:bold'>LAPORAN NERACA</div>
									</td>
								</tr>
								<tr>
									<td colspan='2' style='text-align:center;'>
										<div style='font-weight:bold'>
											<?php
												echo $preferencecompany['company_name'];
											?>	
										</div>
									</td>
								</tr>
								<tr>
									<td colspan='2' style='text-align:center;'>
										<div style='font-weight:bold'>Periode 
											<?php
												echo $period;
											?>
										</div>
									</td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style='width: 50%'>
										<table class="table table-bordered table-advance table-hover">
											<?php
											$grand_total_account_amount1 = 0;
											$grand_total_account_amount2 = 0;
												foreach ($acctbalancesheetreport_left as $key => $val) {
													if($val['report_tab1'] == 0){
														$report_tab1 = ' ';
													} else if($val['report_tab1'] == 1){
														$report_tab1 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
													} else if($val['report_tab1'] == 2){
														$report_tab1 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
													} else if($val['report_tab1'] == 3){
														$report_tab1 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
													}

													if($val['report_bold1'] == 1){
														$report_bold1 = 'bold';
													} else {
														$report_bold1 = 'normal';
													}

													echo "
														<tr>";

														if($val['report_type1'] == 1){
															echo "
																<td colspan='2'><div style='font-weight:".$report_bold1."'>".$report_tab1."".$val['account_name1']."</div></td>
																";
														}
														
													echo "
														</tr>";

													echo "
														<tr>";

														if($val['report_type1']	== 2){
															echo "
																<td style='width: 75%'><div style='font-weight:".$report_bold1."'>".$report_tab1."".$val['account_name1']."</div></td>
																<td style='width: 25%'><div style='font-weight:".$report_bold1."'></div></td>
																";
														}
															
													echo "
														</tr>";

													echo "
														<tr>";

														if($val['report_type1']	== 3){
															$last_balance1 	= $this->AcctBalanceSheetReportNew1_model->getLastBalance($val['account_id1'], $data['branch_id'], $last_month, $last_year);

															echo "
																<td><div style='font-weight:".$report_bold1."'>".$report_tab1."(".$val['account_code1'].") ".$val['account_name1']."</div> </td>
																<td style='text-align:right'><div style='font-weight:".$report_bold1."'>".number_format($last_balance1, 2)."</div></td>
															";

															$account_amount1_top[$val['report_no']] = $last_balance1;
														}

													echo "
														</tr>";

													echo "
														<tr>";
														if($val['report_type1']	== 10){
															$last_balance10 	= $this->AcctBalanceSheetReportNew1_model->getLastBalance($val['account_id1'], $data['branch_id'], $last_month, $last_year);

															$account_amount10_top[$val['report_no']] = $last_balance10;
														}	
													echo "
														</tr>";
													echo "
														<tr>";

														if($val['report_type1'] == 11){
															if(!empty($val['report_formula1']) && !empty($val['report_operator1'])){
																$report_formula1 	= explode('#', $val['report_formula1']);
																$report_operator1 	= explode('#', $val['report_operator1']);

																$total_account_amount10	= 0;
																for($i = 0; $i < count($report_formula1); $i++){
																	if($report_operator1[$i] == '-'){
																		if($total_account_amount10 == 0 ){
																			$total_account_amount10 = $total_account_amount10 + $account_amount10_top[$report_formula1[$i]];
																		} else {
																			$total_account_amount10 = $total_account_amount10 - $account_amount10_top[$report_formula1[$i]];
																		}
																	} else if($report_operator1[$i] == '+'){
																		if($total_account_amount10 == 0){
																			$total_account_amount10 = $total_account_amount10 + $account_amount10_top[$report_formula1[$i]];
																		} else {
																			$total_account_amount10 = $total_account_amount10 + $account_amount10_top[$report_formula1[$i]];
																		}
																	}
																	
																}

																$grand_total_account_amount1 = $grand_total_account_amount1 + $total_account_amount10;

																echo "
																	<td><div style='font-weight:".$report_bold1."'>".$report_tab1."".$val['account_name1']."</div></td>
																	<td style='text-align:right'><div style='font-weight:".$report_bold1."'>".number_format($total_account_amount10, 2)."</div></td>
																	";
															}
														}

													echo "			
														</tr>";

													echo "
														<tr>";

														if($val['report_type1']	== 7){
															$last_balance1 	= $this->AcctBalanceSheetReportNew1_model->getLastBalance($val['account_id1'], $data['branch_id'], $last_month, $last_year);

															echo "
																<td><div style='font-weight:".$report_bold1."'>".$report_tab1."(".$val['account_code1'].") ".$val['account_name1']."</div> </td>
																<td style='text-align:right'><div style='font-weight:".$report_bold1."'>( ".number_format($last_balance1, 2)." )</div></td>
															";

															$account_amount1_top[$val['report_no']] = $last_balance1;
														}

													echo "
														</tr>";

													echo "
														<tr>";

														if($val['report_type1'] == 5){
															if(!empty($val['report_formula1']) && !empty($val['report_operator1'])){
																$report_formula1 	= explode('#', $val['report_formula1']);
																$report_operator1 	= explode('#', $val['report_operator1']);

																$total_account_amount1	= 0;
																for($i = 0; $i < count($report_formula1); $i++){
																	if($report_operator1[$i] == '-'){
																		if($total_account_amount1 == 0 ){
																			$total_account_amount1 = $total_account_amount1 + $account_amount1_top[$report_formula1[$i]];
																		} else {
																			$total_account_amount1 = $total_account_amount1 - $account_amount1_top[$report_formula1[$i]];
																		}
																	} else if($report_operator1[$i] == '+'){
																		if($total_account_amount1 == 0){
																			$total_account_amount1 = $total_account_amount1 + $account_amount1_top[$report_formula1[$i]];
																		} else {
																			$total_account_amount1 = $total_account_amount1 + $account_amount1_top[$report_formula1[$i]];
																		}
																	}
																}

																$grand_total_account_amount1 = $grand_total_account_amount1 + $total_account_amount1;

																echo "
																	<td><div style='font-weight:".$report_bold1."'>".$report_tab1."".$val['account_name1']."</div></td>
																	<td style='text-align:right'><div style='font-weight:".$report_bold1."'>".number_format($total_account_amount1+$total_account_amount10, 2)."</div></td>
																	";
															}
															
														}

													echo "			
														</tr>";

													echo "
														<tr>";

														if($val['report_type1'] == 6){

															if(!empty($val['report_formula1']) && !empty($val['report_operator1'])){

																$grand_total_account_name1 = $val['account_name1'];
															}
														}

													echo "			
														</tr>";	
												}
											?>
										</table>
									</td>
									<td style='width: 50%'>
										<table class="table table-bordered table-advance table-hover">
											<?php
												foreach ($acctbalancesheetreport_right as $key => $val) {
													if($val['report_tab2'] == 0){
														$report_tab2 = ' ';
													} else if($val['report_tab2'] == 1){
														$report_tab2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
													} else if($val['report_tab2'] == 2){
														$report_tab2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
													} else if($val['report_tab2'] == 3){
														$report_tab2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
													}

													if($val['report_bold2'] == 1){
														$report_bold2 = 'bold';
													} else {
														$report_bold2 = 'normal';
													}

													echo "
														<tr>";

														if($val['report_type2'] == 1){
															echo "
																<td colspan='2'><div style='font-weight:".$report_bold2."'>".$report_tab2."".$val['account_name2']."</div></td>
																";
														}
														
													echo "
														</tr>";

													echo "
														<tr>";

														if($val['report_type2']	== 2){
															echo "
																<td style='width: 75%'><div style='font-weight:".$report_bold2."'>".$report_tab2."".$val['account_name2']."</div></td>
																<td style='width: 25%'><div style='font-weight:".$report_bold2."'></div></td>
																";
														}
															
													echo "
														</tr>";

													echo "
														<tr>";

														if($val['report_type2']	== 3){
															$last_balance2 	= $this->AcctBalanceSheetReportNew1_model->getLastBalance($val['account_id2'], $data['branch_id'], $last_month, $last_year);

															echo "
																<td><div style='font-weight:".$report_bold2."'>".$report_tab2."(".$val['account_code2'].") ".$val['account_name2']."</div> </td>
																<td style='text-align:right'><div style='font-weight:".$report_bold2."'>".number_format($last_balance2, 2)."</div></td>
															";

															$account_amount2_bottom[$val['report_no']] = $last_balance2;
														}

													echo "
														</tr>";

														echo "
															<tr>";
															if($val['report_type2']	== 10){
																$last_balance210 	= $profitlossamount;
																// $shutahunberjalan 	= $this->AcctBalanceSheetReportNew1_model->getSHUTahunBerjalan($val['account_id2'], $data['branch_id'], $month, $year);
																// foreach($shutahunberjalan as $keyshu => $valshu){
																// 		$last_balance210 = $last_balance210 + $valshu['mutation_in_amount'] - $valshu['mutation_out_amount'];
																// 	// echo  "<p>".$val['account_name2'] .":" . $last_balance210."</p>";
																// }
																echo "
																<td><div style='font-weight:".$report_bold2."'>".$report_tab2."(".$val['account_code2'].") ".$val['account_name2']."</div> </td>
																<td style='text-align:right'><div style='font-weight:".$report_bold2."'>".number_format($last_balance210, 2)."</div></td>
															";
																
																$account_amount210_top[$val['report_no']] = $last_balance210;
															}	
														echo "
															</tr>";
														echo "
															<tr>";
	
															if($val['report_type2'] == 11){
																if(!empty($val['report_formula2']) && !empty($val['report_operator2'])){
																	$report_formula2 	= explode('#', $val['report_formula2']);
																	$report_operator2 	= explode('#', $val['report_operator2']);
	
																	$total_account_amount210	= 0;
																	for($i = 0; $i < count($report_formula2); $i++){
																		if($report_operator2[$i] == '-'){
																			if($total_account_amount210 == 0 ){
																				$total_account_amount210 = $total_account_amount210 + $account_amount210_top[$report_formula2[$i]];
																			} else {
																				$total_account_amount210 = $total_account_amount210 - $account_amount210_top[$report_formula2[$i]];
																			}
																		} else if($report_operator2[$i] == '+'){
																			if($total_account_amount210 == 0){
																				$total_account_amount210 = $total_account_amount210 + $account_amount210_top[$report_formula2[$i]];
																			} else {
																				$total_account_amount210 = $total_account_amount210 + $account_amount210_top[$report_formula2[$i]];
																			}
																		}
																		
																	}
	
																	$grand_total_account_amount2 = $grand_total_account_amount2 + $total_account_amount210;
	
																	echo "
																		<td><div style='font-weight:".$report_bold2."'>".$report_tab2."".$val['account_name2']."</div></td>
																		<td style='text-align:right'><div style='font-weight:".$report_bold2."'>".number_format($total_account_amount210, 2)."</div></td>
																		";
																}
															}
	
														echo "			
															</tr>";

													echo "
														<tr>";

														if($val['report_type2']	==8){
															$sahu_tahun_lalu = $this->AcctBalanceSheetReportNew1_model->getSHUTahunLalu($data['branch_id'], $month, $year);

															if(empty($sahu_tahun_lalu)){
																$sahu_tahun_lalu = 0;
															}

															echo "
																<td><div style='font-weight:".$report_bold2."'>".$report_tab2."(".$val['account_code2'].") ".$val['account_name2']."</div> </td>
																<td style='text-align:right'><div style='font-weight:".$report_bold2."'>".number_format($sahu_tahun_lalu, 2)."</div></td>
															";

															$account_amount2_bottom[$val['report_no']] = $sahu_tahun_lalu;
														}
															
													echo "
														</tr>";

													echo "
														<tr>";

														if($val['report_type2']	== 7){
															$profit_loss = $profitlossamount;
															// echo($profit_loss);
															if(empty($profit_loss)){
																$profit_loss = 0;
															}

															echo "
																<td><div style='font-weight:".$report_bold2."'>".$report_tab2."(".$val['account_code2'].") ".$val['account_name2']."</div> </td>
																<td style='text-align:right'><div style='font-weight:".$report_bold2."'>".number_format($profit_loss, 2)."</div></td>
															";

															$account_amount2_bottom[$val['report_no']] = $profit_loss;
														}
													echo "
														</tr>";

													echo "
														<tr>";
														if($val['report_type2'] == 5){
															if(!empty($val['report_formula2']) && !empty($val['report_operator2'])){
																$report_formula2 	= explode('#', $val['report_formula2']);
																$report_operator2 	= explode('#', $val['report_operator2']);

																$total_account_amount2	= 0;
																for($i = 0; $i < count($report_formula2); $i++){
																	if($report_operator2[$i] == '-'){
																		if($total_account_amount2 == 0 ){
																			$total_account_amount2 = $total_account_amount2 + $account_amount2_bottom[$report_formula2[$i]];
																		} else {
																			$total_account_amount2 = $total_account_amount2 - $account_amount2_bottom[$report_formula2[$i]];
																		}
																	} else if($report_operator2[$i] == '+'){
																		if($total_account_amount2 == 0){
																			$total_account_amount2 = $total_account_amount2 + $account_amount2_bottom[$report_formula2[$i]];
																		} else {
																			$total_account_amount2 = $total_account_amount2 + $account_amount2_bottom[$report_formula2[$i]];
																		}
																	}
																	
																}

																$grand_total_account_amount2 = $grand_total_account_amount2 + $total_account_amount2;

																echo "
																	<td><div style='font-weight:".$report_bold2."'>".$report_tab2."".$val['account_name2']."</div></td>
																	<td style='text-align:right'><div style='font-weight:".$report_bold2."'>".number_format($total_account_amount2+$total_account_amount210, 2)."</div></td>
																	";
															}	
														}
													echo "			
														</tr>";

													echo "
														<tr>";

														if($val['report_type2'] == 6){
															if(!empty($val['report_formula2']) && !empty($val['report_operator2'])){

																$grand_total_account_name2 = $val['account_name2'];
															}	
														}

													echo "			
														</tr>";	
												}
											?>
										</table>
									</td>
								</tr>
								<tr>
									<td style='width: 50%'>
										<table class="table table-bordered table-advance table-hover">
											<tr>
												<?php
													echo "
														<td style=\"width: 70%\"><div style=\"font-weight:".$report_bold1.";font-size:14px\">".$report_tab1."".$grand_total_account_name1."</div>
														</td>
														<td style=\"width: 28%; text-align:right;\"><div style=\"font-weight:".$report_bold1."; font-size:14px\">".number_format($grand_total_account_amount1, 2)."</div>
														</td>
													";
												?>
											</tr>
										</table>
									</td>
									<td style='width: 50%'>
										<table class="table table-bordered table-advance table-hover">
											<tr>
												<?php 
													echo "
														<td style=\"width: 70%\"><div style=\"font-weight:".$report_bold2.";font-size:14px\">".$report_tab2."".$grand_total_account_name2."</div></td>
														<td style=\"width: 28%; text-align:right;\"><div style=\"font-weight:".$report_bold2."; font-size:14px\">".number_format($grand_total_account_amount2, 2)."</div></td>
													";
												?>
											</tr>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					<div class="row">
						<div class="col-md-12 " style="text-align  : right !important;">
							<input type="submit" name="Preview" id="Preview" value="Preview" class="btn blue" title="Preview">
							<a href='javascript:void(window.open("<?php echo base_url(); ?>AcctBalanceSheetReportNew1/exportAcctBalanceSheetReportNew1","_blank","top=100,left=200,width=300,height=300"));' title="Export to Excel" class="btn blue"><i class="fa fa-print"></i> Export Data  </a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<!-- </div> -->

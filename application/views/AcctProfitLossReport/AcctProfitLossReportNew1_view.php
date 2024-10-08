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

	function recalculate(){
		var branch_id 		= $("#branch_id").val();
        var month_period 	= $("#month_period_start").val();
        var year_period 	= $("#year_period").val();

		if (confirm("Apakah Anda yakin ingin memproses SHU?")) {
			document.location = base_url + "AcctRecalculateEOM/processAcctRecalculateEOM/" + branch_id + "/" + month_period + "/" + year_period;
		}
	}
</script>

<div class="page-bar">
	<ul class="page-breadcrumb">
		<li>
			<a href="<?php echo base_url();?>">
				Home
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>AcctProfitLossReportNew1">
				Laporan Perhitungan SHU
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>

<?php
	$auth 		= $this->session->userdata('auth');
	$data 		= $this->session->userdata('filter-AcctProfitLossReportNew1');
	$year_now 	= date('Y');

	if(!is_array($data)){
		$data['month_period_start']			= date('m');
		$data['month_period_end']			= date('m');
		$data['year_period']				= $year_now;
		$data['profit_loss_report_type'] 	= 1;
		$data['profit_loss_report_format'] 	= 3;
		$data['branch_id']					= $auth['branch_id'];
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

	$grand_total_all = 0;
	$shu_sebelum_lain_lain = 0;
?> 

<?php echo form_open('AcctProfitLossReportNew1/filter',array('id' => 'myform', 'class' => '')); ?>
<div class="row">
	<div class="col-md-12">	
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Laporan Laba Rugi
				</div>
			</div>
			<div class="portlet-body">
				<div class="form-body form">
				<?php
					echo $this->session->userdata('message');
					$this->session->unset_userdata('message');
				?>
					<div class = "row">
					<div class="col-md-4">
							<div class="form-group form-md-line-input">
								<?php
									echo form_dropdown('month_period_start', $monthlist,set_value('month_period_start',$data['month_period_start']),'id="month_period_start" class="form-control select2me"');
								?>
								<label>Periode Awal</label>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group form-md-line-input">
								<?php
									echo form_dropdown('month_period_end', $monthlist,set_value('month_period_end',$data['month_period_end']),'id="month_period_end" class="form-control select2me"');
								?>
								<label>Periode Akhir</label>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group form-md-line-input">
								<?php
									echo form_dropdown('year_period', $year,set_value('year_period',$data['year_period']),'id="year_period" class="form-control select2me" ');
								?>
								<label></label>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group form-md-line-input">
								<?php
									echo form_dropdown('profit_loss_report_type', $profitlossreporttype, set_value('profit_loss_report_type', $data['profit_loss_report_type']),'id="profit_loss_report_type" class="form-control select2me"');
								?>
								<label>Rugi Laba</label>
							</div>
						</div>
						<?php if($auth['branch_status'] == 1) { ?>
							<div class = "col-md-4">
								<div class="form-group form-md-line-input">
									<?php echo form_dropdown('branch_id', $corebranch, set_value('branch_id',$data['branch_id']),'id="branch_id" class="form-control select2me"');?>
									<label class="control-label">Cabang
										<span class="required">
											*
										</span>
									</label>
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="form-actions right">
						<button type="button" name="Proses" value="Proses SHU" class="btn green-jungle" onClick="recalculate();" title="Proses SHU"> Proses SHU </button>
						<input type="submit" name="Find" value="Find" class="btn green" title="Search Data">
					</div>
<?php echo form_close(); ?>
<div class="row">
	<div class="col-md-12">	
		<div class="portlet box blue">
			<div class="portlet-body">
				<div class="form-body form">
					<br>
						<?php
							echo form_open('AcctProfitLossReportNew1/processPrinting'); 

							$preferencecompany = $this->AcctProfitLossReportNew1_model->getPreferenceCompany();

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
						?>
						<div class = "row">
							<div class = "col-md-2">
							</div>
							<div class = "col-md-8">
								<table class="table table-bordered table-advance table-hover">
									<thead>
										<tr>
											<td colspan='2' style='text-align:center;'>
												<div style='font-weight:bold'>LAPORAN LABA RUGI
												</div>
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
												<div style='font-weight:bold'>Period 
													<?php
														echo $period;
													?>
												</div>
											</td>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<table class="table table-bordered table-advance table-hover">
													<?php
														foreach ($acctprofitlossreport_top as $key => $val) {
															if($val['report_tab'] == 0){
																$report_tab = ' ';
															} else if($val['report_tab'] == 1){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															} else if($val['report_tab'] == 2){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															} else if($val['report_tab'] == 3){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															} else if($val['report_tab'] == 4){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															} else if($val['report_tab'] == 5){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															} else if($val['report_tab'] == 6){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															} else if($val['report_tab'] == 7){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															} else if($val['report_tab'] == 8){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															}

															if($val['report_bold'] == 1){
																$report_bold = 'bold';
															} else {
																$report_bold = 'normal';
															}

															echo "
																<tr>";

																if($val['report_type'] == 1){
																	echo "
																		<td colspan='2'><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																	";
																}
																
															echo "
																</tr>";
															echo "
																<tr>";

																if($val['report_type']	== 2){
																	echo "
																		<td style='width: 75%'><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																		<td style='width: 25%'><div style='font-weight:".$report_bold."'></div></td>
																	";
																}
																	
															echo "
																</tr>";
															echo "
																<tr>";

																if($val['report_type']	== 3){
																	$account_subtotal 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($val['account_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

																	echo "
																		<td><div style='font-weight:".$report_bold."'>".$report_tab."(".$val['account_code'].") ".$val['account_name']."</div> </td>
																		<td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($account_subtotal, 2)."</div></td>
																	";

																	$account_amount[$val['report_no']] = $account_subtotal;
																}

															echo "
																</tr>";

																echo "
																<tr>";

																if($val['report_type'] == 4){
																	if(!empty($val['report_formula']) && !empty($val['report_operator'])){
																		$report_formula 	= explode('#', $val['report_formula']);
																		$report_operator 	= explode('#', $val['report_operator']);

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

																		echo "
																			<td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																			<td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($total_account_amount, 2)."</div></td>
																		";
																}
															}

															echo "			
																</tr>";
															

															echo "
																<tr>";

																if($val['report_type'] == 5){
																	if(!empty($val['report_formula']) && !empty($val['report_operator'])){
																		$report_formula 	= explode('#', $val['report_formula']);
																		$report_operator 	= explode('#', $val['report_operator']);

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

																		echo "
																			<td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																			<td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($total_account_amount, 2)."</div></td>
																		";
																}
															}

															echo "			
																</tr>";
			
															if($val['report_type'] == 6){
																if(!empty($val['report_formula']) && !empty($val['report_operator'])){
																	$report_formula 	= explode('#', $val['report_formula']);
																	$report_operator 	= explode('#', $val['report_operator']);

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
																	
																	// if($val['category_type'] == 1){
																	// 	$grand_total_all += $grand_total_account_amount1;
																	// }

																	echo "
																		<td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																		<td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($grand_total_account_amount1, 2)."</div></td>
																	";
																}
															}

															if($val['report_type'] == 7){
																	$shu_sebelum_lain_lain = $total_account_amount - $grand_total_account_amount1;
																	echo "
																		<td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																		<td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($shu_sebelum_lain_lain , 2)."</div></td>
																	";
															}

															echo "
																<tr>";

																if($val['report_type'] == 8){
																	if(!empty($val['report_formula']) && !empty($val['report_operator'])){
																		$report_formula 	= explode('#', $val['report_formula']);
																		$report_operator 	= explode('#', $val['report_operator']);

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

																		echo "
																			<td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																			<td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($pendapatan_biaya_lain, 2)."</div></td>
																		";
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
											<td>
												<table class="table table-bordered table-advance table-hover">
													<?php
														foreach ($acctprofitlossreport_bottom as $key => $val) {
															if($val['report_tab'] == 0){
																$report_tab = ' ';
															} else if($val['report_tab'] == 1){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															} else if($val['report_tab'] == 2){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															} else if($val['report_tab'] == 3){
																$report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
															}

															if($val['report_bold'] == 1){
																$report_bold = 'bold';
															} else {
																$report_bold = 'normal';
															}

															echo "
																<tr>
															";

																if($val['report_type'] == 1){
																	echo "
																		<td colspan='2'><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																	";
																}
																
															echo "
																</tr>
															";

															echo "
																<tr>
															";

																if($val['report_type']	== 2){
																	echo "
																		<td style='width: 75%'><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																		<td style='width: 25%'><div style='font-weight:".$report_bold."'></div></td>
																	";
																}
																	
															echo "
																</tr>
															";

															echo "
																<tr>
															";

																if($val['report_type']	== 3){
																	$account_subtotal 	= $this->AcctProfitLossReportNew1_model->getAccountAmount($val['account_id'], $data['month_period_start'], $data['month_period_end'], $data['year_period'], $data['profit_loss_report_type'], $data['branch_id']);

																	echo "
																		<td><div style='font-weight:".$report_bold."'>".$report_tab."(".$val['account_code'].") ".$val['account_name']."</div> </td>
																		<td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($account_subtotal, 2)."</div></td>
																	";

																	$account_amount[$val['report_no']] = $account_subtotal;
																}

															echo "
																</tr>";

															echo "
																<tr>";

															if($val['report_type'] == 5){
																if(!empty($val['report_formula']) && !empty($val['report_operator'])){
																	$report_formula 	= explode('#', $val['report_formula']);
																	$report_operator 	= explode('#', $val['report_operator']);

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

																	echo "
																		<td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																		<td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($total_account_amount, 2)."</div></td>
																	";
																}
															}

															echo "			
																</tr>
															";
			
															if($val['report_type'] == 6){
																if(!empty($val['report_formula']) && !empty($val['report_operator'])){
																	$report_formula 	= explode('#', $val['report_formula']);
																	$report_operator 	= explode('#', $val['report_operator']);

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
																	
																	if($val['category_type'] == 1){
																		$grand_total_all += $grand_total_account_amount2;
																	}

																	echo "
																		<td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
																		<td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($grand_total_account_amount2, 2)."</div></td>
																	";
																}
															}
														}
													?>
												</table>
											</td>
										</tr>
										<tr>	
											<td>
												<table class="table table-bordered table-advance table-hover">
												<tr>
														<td style="width: 70%">
															<div style='font-weight:bold; font-size:16px'>
																SHU TAHUN BERJALAN
															</div>
														</td >
														<td style="width: 25%; text-align:right" >
															<div style='font-weight:bold; font-size:16px'>
																<?php
																	$shu = $shu_sebelum_lain_lain + $pendapatan_biaya_lain;
																	echo number_format($shu, 2);
																?>	
															</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class = "col-md-2">
							</div>
						</div>
					<div class="row">
						<div class="col-md-12 " style="text-align  : right !important;">
							<input type="submit" name="Preview" id="Preview" value="Preview" class="btn blue" title="Preview">
							<a href='javascript:void(window.open("<?php echo base_url(); ?>AcctProfitLossReportNew1/exportAcctProfitLossReportNew1","_blank","top=100,left=200,width=300,height=300"));' title="Export to Excel" class="btn blue"><i class="fa fa-print"></i> Export Data  </a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
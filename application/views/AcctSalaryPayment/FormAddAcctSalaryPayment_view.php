<?php error_reporting(0);?>
<style>
	th, td {
  		padding: 2px;
  		font-size: 13px;
	}

	input:focus { 
  		background-color: 42f483;
	}

	.table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th{
    	padding: 2px;
    	vertical-align: top;
    	border-top: 1px solid #e7ecf1;
	}
	.table td, .table th {
	    font-size: 12px;
	}

	input:-moz-read-only { /* For Firefox */
  		background-color: #e7ecf1; 
	}

	input:read-only {
  		background-color: #e7ecf1;
	}
</style>
<script>
	base_url = '<?php echo base_url();?>';
</script>

<div class="page-bar">
	<ul class="page-breadcrumb">
		<li>
			<i class="fa fa-home"></i>
			<a href="<?php echo base_url();?>">
				Beranda
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>salary-payments/add">
				Tambah Pembayaran Pinjaman via Potong Gaji 
			</a>
		</li>
	</ul>
</div>
<?php echo form_open('salary-payments/process-add',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
	$sesi 			= $this->session->userdata('unique');
	$token 			= $this->session->userdata('acctcreditspaymentcashtoken-'.$unique['unique']);
?>
<?php
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');
?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			 <div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Form Tambah
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>salary-payments/ind-salary-payment" class="btn btn-default btn-sm">
							<i class="fa fa-angle-left"></i>
							<span class="hidden-480">
								Kembali
							</span>
						</a>
					</div>
				</div>
					<div class="portlet-body">
						<div class="form-body">
							<div class="row">
								<div class="col-md-12">
									<table class="table table-striped table-bordered table-hover table-full-width" id="sample_3">
										<thead>
											<tr>
												<th style="text-align:center" width="5%">No</th>
												<th style="text-align:center" width="10%">No Perjanjian Kredit</th>
												<th style="text-align:center" width="10%">No Anggota</th>
												<th style="text-align:center" width="15%">Nama Anggota</th>
												<th style="text-align:center" width="15%">Jenis Pinjaman</th>
												<th style="text-align:center" width="15%">Angsuran Pokok</th>
												<th style="text-align:center" width="15%">Angsuran Bunga</th>
												<th style="text-align:center" width="15%">Subtotal Angsuran</th>
											</tr>
										</thead>
										<tbody>
											<?php 
											$no=1;
											$payment_total_amount = 0;

											if(empty($acctcreditsaccount)){
												echo "<tr><td align='center' colspan='8'> Data Kosong !</td></tr>";
											} else {
												foreach ($acctcreditsaccount as $key=>$val){ 
//!PEMBATAS-------------------------------------------------------------------------------------------------------------------------------------
													$credits_payment_date = date('Y-m-d');
													$date1 = date_create($credits_payment_date);
													$date2 = date_create($val['accountcredit']['credits_account_payment_date']);

													if($date1 > $date2){
														$interval                       = $date1->diff($date2);
														$credits_payment_day_of_delay   = $interval->days;
													} else {
														$credits_payment_day_of_delay 	= 0;
													}

													$saldobunga = $val['accountcredit']['credits_account_interest_last_balance'] + $val['accountcredit']['credits_account_interest_amount'] ;
													
													$credits_payment_fine_amount 		= (($val['accountcredit']['credits_account_payment_amount'] * $val['accountcredit']['credits_fine']) / 100 ) * $credits_payment_day_of_delay;
													$credits_account_accumulated_fines 	= $val['accountcredit']['credits_account_accumulated_fines'] + $credits_payment_fine_amount;

													if(strpos($val['accountcredit']['credits_account_payment_to'], ',') == true ||strpos($val['accountcredit']['credits_account_payment_to'], '*') == true ){
														$angsuranke 					= substr($val['accountcredit']['credits_account_payment_to'], -1) + 1;
														}else{
															$angsuranke 				= $val['accountcredit']['credits_account_payment_to'] + 1;
														}

													if($val['accountcredit']['payment_type_id'] == 1){
														$angsuranpokok 		= $val['accountcredit']['credits_account_principal_amount'];
														$angsuranbunga 	 	= $val['accountcredit']['credits_account_payment_amount'] - $angsuranpokok;
													} else if($val['accountcredit']['payment_type_id'] == 2){
														$angsuranpokok 		= $anuitas[$angsuranke]['angsuran_pokok'];
														$angsuranbunga 	 	= $val['accountcredit']['credits_account_payment_amount'] - $angsuranpokok;
													} else if($val['accountcredit']['payment_type_id'] == 3){
														$angsuranpokok 		= $slidingrate[$angsuranke]['angsuran_pokok'];
														$angsuranbunga 	 	= $val['accountcredit']['credits_account_payment_amount'] - $angsuranpokok;
													} else if($val['accountcredit']['payment_type_id'] == 4){
														$angsuranpokok		= 0;
														$angsuranbunga		= $angsuran_bunga_menurunharian;
													}
//!PEMBATAS-------------------------------------------------------------------------------------------------------------------------------------
													echo"
														<tr>
															<td style='text-align:center'>".$no."</td>
															<td>".$val['credits_account_serial']."</td>
															<td>".$val['member_no']."</td>
															<td>".$val['member_name']."</td>
															<td>".$val['credits_name']."</td>
															<td style='text-align:right'>".number_format($angsuranpokok, 2)."</td>
															<td style='text-align:right'>".number_format($angsuranbunga, 2)."</td>
															<td style='text-align:right'>".number_format($angsuranpokok+$angsuranbunga, 2)."</td>
														</tr>
													";
													$no++;
													$payment_total_amount += $angsuranpokok+$angsuranbunga;
												}
											} ?>
										</tbody>
									</table>
									<hr>
									<table width="40%" align="right" style="margin-top:20px">
										<tr>
											<td width="35%">Total Angsuran</td>
											<td width="5%"></td>
											<td width="60%">
												 <input type="hidden" class="easyui-textbox" name="credits_payment_token" id="credits_payment_token" value="<?php echo $token;?>" readonly/>
												<input type="text" class="easyui-textbox" name="member_principal_savings_view" id="member_principal_savings_view" value="<?php echo number_format($payment_total_amount, 2) ?>" autocomplete="off" style="width: 100%" readonly/>
												<input type="hidden" class="easyui-textbox" name="member_principal_savings" id="member_principal_savings" value="<?php echo $payment_total_amount ?>"  autocomplete="off" />
											</td>
										</tr>
									</table>
								</div>
							</div>
							 
							<div class="row" style="margin-top:60px">
								<div class="col-md-12" style='text-align:right'>
									<button type="submit" name="Save" value="Save" id="Save" class="btn green-jungle" title="Simpan Data" ><i class="fa fa-check"> Simpan</i></button>
								</div>	
							</div>
						</div>
					</div>
				
			 </div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>

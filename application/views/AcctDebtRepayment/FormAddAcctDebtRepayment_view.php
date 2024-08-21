<style>
	th, td {
	  padding: 3px;
	  font-size: 13px;
	}
	input:focus { 
	  background-color: 42f483;
	}
	.custom{

		margin: 0px; padding-top: 0px; padding-bottom: 0px; height: 50px; line-height: 50px; width: 50px;

	}
	.textbox .textbox-text{
		font-size: 13px;


	}
	input:read-only {
		background-color: f0f8ff;
	}
</style>
<script>
	base_url = '<?php echo base_url();?>';

	// $(document).ready(function() {
	// 	$('#excel_file').filebox({
	// 		onChange: function(value) {
	// 			console.log('tes');
	// 			$.ajax({
	// 				type: "POST",
	// 				url : "<?php echo site_url('debt-repayment/add-array');?>",
	// 				data: {
	// 						'value'			: value,
	// 						'session_name' 	: "addarraydebtrepayment-"
	// 					},
	// 				success: function(msg){
	// 					window.location.reload();
	// 				}
	// 			});
	// 		}
	// 	})
	// });

	function toRp(number) {
		var number = number.toString(),
			rupiah = number.split('.')[0],
			cents = (number.split('.')[1] || '') + '00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1,')
			.split('').reverse().join('');
		return rupiah + '.' + cents.slice(0, 2);
	}

	function calculateDebtAllocated(member_id){
		var principal 		= document.getElementById("principal_"+member_id).value;
		var mandatory 		= document.getElementById("mandatory_"+member_id).value;
		var minimarket 		= document.getElementById("minimarket_"+member_id).value;

		var allocated		= parseInt(principal)+parseInt(mandatory)+parseInt(minimarket);
		document.getElementById("allocation_"+member_id).value = allocated;
		document.getElementById("allocation_view_"+member_id).value = toRp(allocated);
	}
	
</script>
<?php echo form_open_multipart('debt-repayment/add-array',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
	$sesi 	= $this->session->userdata('unique');
	$data 	= $this->session->userdata('addacctdebtrepayment-'.$sesi['unique']);
	$auth 	= $this->session->userdata('auth');
	$token 	= $this->session->userdata('acctdebtrepaymenttoken-'.$sesi['unique']);

?>

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
			<a href="<?php echo base_url();?>debt-repayment">
				Daftar Pelunasan Piutang Potong Gaji
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>debt-repayment/add">
				Tambah Pelunasan Piutang Potong Gaji 
			</a>
		</li>
	</ul>
</div>

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
						Form Tambah Pelunasan Piutang Potong Gaji
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>debt-repayment" class="btn btn-default btn-sm">
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

							<input type="hidden" class="form-control" name="debt_repayment_token" id="debt_repayment_token" value="<?php echo $token;?>" readonly/>

							<div class="col-md-6">
								<table width="100%">
									<tr>
										<td width="35%">File Excel</td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="" accept=".xlsx, .xls, .csv" class="easyui-filebox" name="excel_file" id="excel_file" style="width: 60%"/>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12" style='text-align:right'>
								<button type="submit" name="process" value="process" id="process" class="btn green-jungle" title="Proses Data">Proses</i></button>
							</div>	
						</div>
					</div>
			 	</div>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
<?php echo form_open_multipart('debt-repayment/process-add',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			<div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Daftar File Excel
					</div>
				</div>
				<div class="portlet-body">
					<div class="form-body">
						<div class="row">
						<table class="table table-striped table-bordered table-hover table-full-width" id="myDataTable">
							<thead>
								<tr>
									<th width="5%" style="text-align: center;">No</th>
									<th width="25%" style="text-align: center;">No Anggota</th>
									<th width="35%" style="text-align: center;">Nama Anggota</th>
									<th width="35%" style="text-align: center;">Jumlah Potong Gaji</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$no = 1;
								if(empty($acctdebtrepaymentitemtemp)){
									echo "
										<tr>
											<td colspan='4' align='center'>Data Kosong</td>
										</tr>
									";
								} else {
									foreach($acctdebtrepaymentitemtemp as $key => $val){
										$member_account_receivable_amount = $this->AcctDebtRepayment_model->getMemberAccountReceivableAmount($val['member_id']);
										if($member_account_receivable_amount != $val['debt_repayment_item_temp_amount']){
								?>
										<tr>
											<th width="5%" style="text-align: center;"><?php echo $no; ?></th>
											<th width="25%" style="text-align: left;"><?php echo $this->AcctDebtRepayment_model->getCoreMemberNo($val['member_id'])?></th>	
											<th width="35%" style="text-align: left;"><?php echo $this->AcctDebtRepayment_model->getCoreMemberName($val['member_id'])?></th>
											<th width="35%" style="text-align: right;"><?php echo number_format($val['debt_repayment_item_temp_amount'], 2) ?></th>
										</tr>
										<tr>
											<td></td>
											<td></td>
											<td>Total Alokasi</td>
											<th>
												<input type="hidden" class="form-control" name="allocation_<?php echo $val['member_id']?>" id="allocation_<?php echo $val['member_id']?>" autocomplete="off" value="0" style="text-align:right;" readonly/>
												<input type="text" class="form-control" name="allocation_view_<?php echo $val['member_id']?>" id="allocation_view_<?php echo $val['member_id']?>" autocomplete="off" value="0" style="text-align:right;" readonly/>
											</th>
										</tr>
										<tr>
											<td></td>
											<td></td>
											<td>Simp Pokok</td>
											<td>
												<input type="text" class="form-control" name="principal_<?php echo $val['member_id']?>" id="principal_<?php echo $val['member_id']?>" autocomplete="off" value="0" style="text-align:right;" onChange="calculateDebtAllocated(<?php echo $val['member_id']?>)"/>
											</td>
										</tr>
										<tr>
											<td></td>
											<td></td>
											<td>Simp Wajib</td>
											<td>
												<input type="text" class="form-control" name="mandatory_<?php echo $val['member_id']?>" id="mandatory_<?php echo $val['member_id']?>" autocomplete="off" value="0" style="text-align:right;" onChange="calculateDebtAllocated(<?php echo $val['member_id']?>)"/>
											</td>
										</tr>
										<tr>
											<td></td>
											<td></td>
											<td>Pinjaman Toko</td>
											<td>
												<input type="text" class="form-control" name="minimarket_<?php echo $val['member_id']?>" id="minimarket_<?php echo $val['member_id']?>" autocomplete="off" value="0" style="text-align:right;" onChange="calculateDebtAllocated(<?php echo $val['member_id']?>)"/>
											</td>
										</tr>
								<?php 
										}else{
											$memberdebtamount = $this->AcctDebtRepayment_model->getMemberDebtAmount($val['member_id']);
								?>
										<tr>
											<th width="5%" style="text-align: center;"><?php echo $no; ?></th>
											<th width="25%" style="text-align: left;"><?php echo $this->AcctDebtRepayment_model->getCoreMemberNo($val['member_id'])?></th>	
											<th width="35%" style="text-align: left;"><?php echo $this->AcctDebtRepayment_model->getCoreMemberName($val['member_id'])?></th>
											<th width="35%" style="text-align: right;"><?php echo number_format($val['debt_repayment_item_temp_amount'], 2) ?></th>
										</tr>
										<tr style='display:none'>
											<td></td>
											<td></td>
											<td>Total Alokasi</td>
											<th>
												<input type="hidden" class="form-control" name="allocation_<?php echo $val['member_id']?>" id="allocation_<?php echo $val['member_id']?>" autocomplete="off" value="<?php echo $memberdebtamount['member_account_receivable_amount'] ?>" style="text-align:right;" readonly/>
												<input type="text" class="form-control" name="allocation_view_<?php echo $val['member_id']?>" id="allocation_view_<?php echo $val['member_id']?>" autocomplete="off" value="<?php echo $memberdebtamount['member_account_receivable_amount'] ?>" style="text-align:right;" readonly/>
											</th>
										</tr>
										<tr style='display:none'>
											<td></td>
											<td></td>
											<td>Simp Pokok</td>
											<td>
												<input type="text" class="form-control" name="principal_<?php echo $val['member_id']?>" id="principal_<?php echo $val['member_id']?>" autocomplete="off" value="<?php echo $memberdebtamount['member_account_principal_debt'] ?>" style="text-align:right;" onChange="calculateDebtAllocated(<?php echo $val['member_id']?>)"/>
											</td>
										</tr>
										<tr style='display:none'>
											<td></td>
											<td></td>
											<td>Simp Wajib</td>
											<td>
												<input type="text" class="form-control" name="mandatory_<?php echo $val['member_id']?>" id="mandatory_<?php echo $val['member_id']?>" autocomplete="off" value="<?php echo $memberdebtamount['member_account_mandatory_debt'] ?>" style="text-align:right;" onChange="calculateDebtAllocated(<?php echo $val['member_id']?>)"/>
											</td>
										</tr>
										<tr style='display:none'>
											<td></td>
											<td></td>
											<td>Pinjaman Toko</td>
											<td>
												<input type="text" class="form-control" name="minimarket_<?php echo $val['member_id']?>" id="minimarket_<?php echo $val['member_id']?>" autocomplete="off" value="<?php echo $memberdebtamount['member_account_minimarket_debt'] ?>" style="text-align:right;" onChange="calculateDebtAllocated(<?php echo $val['member_id']?>)"/>
											</td>
										</tr>
								<?php 	}
										$no++;
									}
							 	} 
								?>
							</tbody>
						</table>
						</div>
						<div class="row">
							<div class="col-md-12" style='text-align:right'>
								<button type="submit" name="Save" value="Save" id="Save" class="btn green-jungle" title="Simpan Data"><i class="fa fa-check"> Simpan</i></button>
							</div>	
						</div>
					</div>
			 	</div>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>

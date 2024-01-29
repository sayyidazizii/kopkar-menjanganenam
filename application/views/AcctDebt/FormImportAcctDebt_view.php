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
	base_url 			= '<?php echo base_url();?>';
	var loop_amount 	= 1;

	function toRp(number) {
		var number = number.toString(),
			rupiah = number.split('.')[0],
			cents = (number.split('.')[1] || '') + '00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1,')
			.split('').reverse().join('');
		return rupiah + '.' + cents.slice(0, 2);
	}
</script>
<?php echo form_open_multipart('debt/process-import-temp', array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
	$sesi 	= $this->session->userdata('unique');
	$data 	= $this->session->userdata('importacctdebt-'.$sesi['unique']);
	$auth 	= $this->session->userdata('auth');
	$token 	= md5(rand());
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
			<a href="<?php echo base_url();?>debt">
				Daftar Potong Gaji
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>debt/add">
				Import Potong Gaji 
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
						Form Import Potong Gaji
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>debt" class="btn btn-default btn-sm">
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
							<input type="hidden" class="form-control" name="debt_token" id="debt_token" value="<?php echo $token;?>" readonly/>
							<div class="col-md-6">
								<table width="100%">
									<tr>
										<td width="35%">File Excel</td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="" accept=".xlsx, .xls, .csv" class="easyui-filebox" name="excel_file" id="excel_file" style="width: 80%"/>
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
<?php echo form_open_multipart('debt/process-import', array('id' => 'myform', 'class' => 'horizontal-form')); ?>
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
									<th width="10%" style="text-align: center;">No Anggota</th>
									<th width="18%" style="text-align: center;">Nama Anggota</th>
									<th width="18%" style="text-align: center;">Kategori</th>
									<th width="15%" style="text-align: center;">Tanggal</th>
									<th width="15%" style="text-align: center;">Jumlah Potong Gaji</th>
									<th width="19%" style="text-align: center;">Keterangan</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$no = 1;
								if(empty($acctdebttemp)){
									echo "
										<tr>
											<td colspan='7' align='center'>Data Kosong</td>
										</tr>
									";
								} else {
									foreach($acctdebttemp as $key => $val){
								?>
									<tr>
										<td align='center'>
											<?php echo $no ?>
										</td>
										<td align='left'>
											<input type="text" class="form-control" name="member_name_<?php echo $val['debt_temp_id']?>" id="member_name_<?php echo $val['debt_temp_id']?>" autocomplete="off" value="<?php echo $this->AcctDebt_model->getCoreMemberNo($val['member_id']) ?>" style="text-align:left;" readonly/>
											<input type="hidden" class="form-control" name="member_id_<?php echo $val['debt_temp_id']?>" id="member_id_<?php echo $val['debt_temp_id']?>" autocomplete="off" value="<?php echo $val['member_id'] ?>" style="text-align:left;" readonly/>
										</td>
										<td align='left'>
											<input type="text" class="form-control" name="member_name_<?php echo $val['debt_temp_id']?>" id="member_name_<?php echo $val['debt_temp_id']?>" autocomplete="off" value="<?php echo $this->AcctDebt_model->getCoreMemberName($val['member_id']) ?>" style="text-align:left;" readonly/>
										</td>
										<td align='left'>
											<?php 
												echo form_dropdown('debt_category_id_'.$val['debt_temp_id'], $acctdebtcategory, set_value('debt_category_id_'.$val['debt_temp_id'], $val['debt_category_id']), 'id="debt_category_id_"'.$val['debt_temp_id'].' class="form-control select2me"');
											?>
										</td>
										<td align='center'>
											<input type="text" class="form-control" name="debt_temp_date_<?php echo $val['debt_temp_id']?>" id="debt_temp_date_<?php echo $val['debt_temp_id']?>" autocomplete="off" value="<?php echo $val['debt_temp_date'] ?>" style="text-align:center;" readonly/>
										</td>
										<td align='right'>
											<input type="text" class="form-control" name="debt_temp_amount_view_<?php echo $val['debt_temp_id']?>" id="debt_temp_amount_view_<?php echo $val['debt_temp_id']?>" autocomplete="off" value="<?php echo nominal($val['debt_temp_amount'], 2) ?>" style="text-align:right;" readonly/>
											<input type="hidden" class="form-control" name="debt_temp_amount_<?php echo $val['debt_temp_id']?>" id="debt_temp_amount_<?php echo $val['debt_temp_id']?>" autocomplete="off" value="<?php echo $val['debt_temp_amount'] ?>" style="text-align:right;" readonly/>
										</td>
										<td align='left'>
											<input type="text" class="form-control" name="debt_temp_remark_<?php echo $val['debt_temp_id']?>" id="debt_temp_remark_<?php echo $val['debt_temp_id']?>" autocomplete="off" value="<?php echo $val['debt_temp_remark'] ?>" style="text-align:left;" readonly/>
										</td>
									</tr>
								<?php $no++;
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
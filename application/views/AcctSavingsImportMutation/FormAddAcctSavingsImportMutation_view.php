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
		var savings 		= document.getElementById("savings_"+member_id).value;
		var credits 		= document.getElementById("credits_"+member_id).value;
		var credits_store 	= document.getElementById("credits_store_"+member_id).value;
		var minimarket 		= document.getElementById("minimarket_"+member_id).value;
		var uniform 		= document.getElementById("uniform_"+member_id).value;

		var allocated		= parseInt(principal)+parseInt(savings)+parseInt(credits)+parseInt(credits_store)+parseInt(minimarket)+parseInt(uniform);
		document.getElementById("allocation_"+member_id).value = allocated;
		document.getElementById("allocation_view_"+member_id).value = toRp(allocated);
	}

	function function_elements_add(name, value){
		$.ajax({
				type: "POST",
				url : "<?php echo site_url('savings-import-mutation/elements-add');?>",
				data : {'name' : name, 'value' : value},
				success: function(msg){
			}
		});
	}

	$(document).ready(function(){
		var sessiondata = <?php echo json_encode($sessiondata); ?>;
		console.log(sessiondata);
		$('#mutation_id').combobox({
			onChange: function(value){
				function_elements_add('mutation_id', value);
			}
		})

		$('#method_id').combobox({
        onChange: function(value){
            function_elements_add('method_id', value);
			console.log(sessiondata);

            if (value == 2) {
                document.getElementById("bank_dropdown").style.display = "contents";
            } else {
                document.getElementById("bank_dropdown").style.display = "none";
            }
            
			}
		});

		$('#bank_account_id').combobox({
			onChange: function(value){
				function_elements_add('bank_account_id', value);
			}
		});
	});
</script>
<?php echo form_open_multipart('savings-import-mutation/add-array',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
	$sesi 	= $this->session->userdata('unique');
	$auth 	= $this->session->userdata('auth');
	$token 	= $this->session->userdata('acctsavingsimportmutationtoken-'.$sesi['unique']);
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
			<a href="<?php echo base_url();?>savings-import-mutation">
				Daftar Import Tabungan
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>savings-import-mutation/add">
				Tambah Import Tabungan 
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
						Form Tambah Import Tabungan
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>savings-import-mutation" class="btn btn-default btn-sm">
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
							<div class="col-md-6">
								<table width="100%">
									<tr>
										<td width="10%">Sandi <span class="required" style="color : red">*</span></td>
										<td width="5%">:</td>
										<td width="60%"><?php echo form_dropdown('mutation_id', $mutationtype, set_value('mutation_id', $sessiondata['mutation_id']),'id="mutation_id" name="mutation_id" class="easyui-combobox" style="width: 60%"');?></td>
									</tr>
								</table>
							</div>
							<div class="col-md-6">
								<table width="100%">
									<tr>
										<td width="35%">Metode <span class="required" style="color : red">*</span></td>
										<td width="5%">:</td>
										<td width="60%"><?php echo form_dropdown('method_id', $methods, set_value('method_id', $sessiondata['method_id']),'id="method_id" name="method_id" class="easyui-combobox" style="width: 60%"');?></td>
									</tr>
								</table>
							</div>
							<div class="col-md-6">
								<table width="100%">
									<tr name="bank_dropdown" id="bank_dropdown">
										<td width="10%">Bank <span class="required" style="color : red">*</span></td>
										<td width="5%">:</td>
										<td width="60%"><?php echo form_dropdown('bank_account_id', $acctbankaccount, set_value('bank_account_id', $sessiondata['bank_account_id']),'id="bank_account_id" name="bank_account_id" class="easyui-combobox"');?></td>
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
<?php echo form_open_multipart('savings-import-mutation/process-add',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
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
									<th width="20%" style="text-align: center;">No Anggota</th>
									<th width="25%" style="text-align: center;">Nama Anggota</th>
									<th width="25%" style="text-align: center;">No Rekening</th>
									<th width="25%" style="text-align: center;">Jumlah Import Tabungan</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$no = 1;
								if(empty($acctsavingsimportmutation)){
									echo "
										<tr>
											<td colspan='5' align='center'>Data Kosong</td>
										</tr>
									";
								} else {
									foreach($acctsavingsimportmutation as $key => $val){
								?>
										<tr>
											<td width="5%" style="text-align: center;"><?php echo $no; ?></td>
											<td width="20%" style="text-align: left;"><?php echo $this->AcctSavingsImportMutation_model->getCoreMemberNo($val['member_id'])?></td>	
											<td width="25%" style="text-align: left;"><?php echo $this->AcctSavingsImportMutation_model->getCoreMemberName($val['member_id'])?></td>
											<td width="25%" style="text-align: left;"><?php echo $this->AcctSavingsImportMutation_model->getAcctSavingsAccountNo($val['savings_account_id'])?></td>
											<td width="25%" style="text-align: right;"><?php echo number_format($val['savings_import_mutation_amount'], 2) ?></td>
										</tr>
								<?php 
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

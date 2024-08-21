<script>
	base_url = '<?php echo base_url();?>';
	mappia = "	<?php 
					$site_url = 'debt-category/add';
					echo site_url($site_url); 
				?>";

	function function_elements_add(name, value){
		$.ajax({
				type: "POST",
				url : "<?php echo site_url('debt-category/elements-add');?>",
				data : {'name' : name, 'value' : value},
				success: function(msg){
			}
		});
	}
	
	function function_state_add(value){
		$.ajax({
				type: "POST",
				url : "<?php echo site_url('debt-category/state-add');?>",
				data : {'value' : value},
				success: function(msg){
			}
		});
	}

	function reset_data(){
		document.location = "<?php echo base_url();?>debt-category/add";
	}
</script>
<?php echo form_open('debt-category/process-add',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
	$sesi 	= $this->session->userdata('unique');
	$data 	= $this->session->userdata('addacctdebtcategory-'.$sesi['unique']);
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
			<a href="<?php echo base_url();?>debt-category">
				Daftar Kategori Potong Gaji
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>debt-category/add">
				Tambah Kategori Potong Gaji 
			</a>
		</li>
	</ul>
</div>

<h3 class="page-title">
	Form Tambah Kategori Potong Gaji
</h3>
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
						<a href="<?php echo base_url();?>debt-category" class="btn btn-default btn-sm">
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
							<div class="col-md-6">
								<div class="form-group form-md-line-input">
									<input type="text" class="form-control" name="debt_category_code" id="debt_category_code" autocomplete="off" value="<?php echo set_value('debt_category_code',$data['debt_category_code']);?>" onChange="function_elements_add(this.name, this.value);"/>
									
									<label class="control-label">Kode Kategori<span class="required">*</span></label>
								</div>
							</div>				
							<div class="col-md-6">
								<div class="form-group form-md-line-input">
									<input type="text" class="form-control" name="debt_category_name" id="debt_category_name" autocomplete="off" value="<?php echo set_value('debt_category_name',$data['debt_category_name']);?>" onChange="function_elements_add(this.name, this.value);"/>
									
									<label class="control-label">Nama Kategori<span class="required">*</span></label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group form-md-line-input">
									<?php 
										echo form_dropdown('debet_account_id', $acctaccount, set_value('debet_account_id', $data['debet_account_id']), 'id="debet_account_id" class="form-control select2me" onChange="function_elements_add(this.name, this.value);"');
									?>

									<label class="control-label">Debit<span class="required">*</span></label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group form-md-line-input">
									<?php 
										echo form_dropdown('credit_account_id', $acctaccount, set_value('credit_account_id', $data['credit_account_id']), 'id="credit_account_id" class="form-control select2me" onChange="function_elements_add(this.name, this.value);"');
									?>

									<label class="control-label">Kredit<span class="required">*</span></label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group form-md-line-input">
									<?php 
										echo form_dropdown('operator', $listoperator, set_value('operator', $data['operator']), 'id="operator" class="form-control select2me" onChange="function_elements_add(this.name, this.value);"');
									?>

									<label class="control-label">Operator<span class="required">*</span></label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12" style='text-align:right'>
								<button type="reset" name="Reset" value="Reset" class="btn btn-danger" onClick="reset_data();"><i class="fa fa-times"> Batal</i></button>
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
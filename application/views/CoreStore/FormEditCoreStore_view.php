<script>
	base_url = '<?php echo base_url();?>';
	function ulang(){
		document.getElementById("store_code").value 			= "<?php echo $corestore['store_code'] ?>";
		document.getElementById("store_name").value 			= "<?php echo $corestore['store_name'] ?>";
	}

</script>
<?php echo form_open('store/process-edit',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
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
			<a href="<?php echo base_url();?>store">
				Daftar Toko
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>store/edit/"<?php $this->uri->segment(3); ?>>
				Edit Toko 
			</a>
		</li>
	</ul>
</div>
<h3 class="page-title">
	Form Edit Toko 
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
						Form Edit
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>store" class="btn btn-default btn-sm">
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
							<div class="col-md-4">
								<div class="form-group form-md-line-input">
									<?php echo form_dropdown('branch_id', $corebranch, set_value('branch_id',$corestore['branch_id']),'id="branch_id" class="form-control select2me"');?>
									<label class="control-label">Cabang<span class="required">*</span></label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group form-md-line-input">
									<input type="text" class="form-control" name="store_code" id="store_code" value="<?php echo set_value('store_code',$corestore['store_code']);?>"/>
									<label class="control-label">Kode Toko<span class="required">*</span></label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group form-md-line-input">
									<input type="text" class="form-control" name="store_name" id="store_name" value="<?php echo set_value('store_name',$corestore['store_name']);?>"/>
									<label class="control-label">Nama Toko<span class="required">*</span></label>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group form-md-line-input">
									<input type="text" class="form-control" name="store_address" id="store_address" value="<?php echo set_value('store_address',$corestore['store_address']);?>"/>
									<label class="control-label">Alamat Toko</label>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-12" style='text-align:right'>
								<button type="reset" name="Reset" value="Reset" class="btn btn-danger" onClick="ulang();"><i class="fa fa-times"> Batal</i></button>
								<button type="submit" name="Save" value="Save" class="btn green-jungle" title="Simpan Data"><i class="fa fa-check"> Simpan </i></button>
							</div>	
						</div>	
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<input type="hidden" class="form-control" name="store_id" id="store_id" placeholder="id" value="<?php echo set_value('store_id',$corestore['store_id']);?>"/>
<?php echo form_close(); ?>
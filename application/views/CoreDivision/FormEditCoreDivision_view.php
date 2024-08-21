<script>
	base_url = '<?php echo base_url();?>';
	function ulang(){
		document.getElementById("division_code").value 			= "<?php echo $coredivision['division_code'] ?>";
		document.getElementById("division_name").value 			= "<?php echo $coredivision['division_name'] ?>";
	}

</script>
<?php echo form_open('division/process-edit',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
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
			<a href="<?php echo base_url();?>division">
				Daftar Divisi
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>division/edit/"<?php $this->uri->segment(3); ?>>
				Edit Divisi 
			</a>
		</li>
	</ul>
</div>
<h3 class="page-title">
	Form Edit Divisi 
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
						<a href="<?php echo base_url();?>division" class="btn btn-default btn-sm">
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
									<?php echo form_dropdown('branch_id', $corebranch, set_value('branch_id',$coredivision['branch_id']),'id="branch_id" class="form-control select2me"');?>
									<label class="control-label">Cabang<span class="required">*</span></label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group form-md-line-input">
									<input type="text" class="form-control" name="division_code" id="division_code" value="<?php echo set_value('division_code',$coredivision['division_code']);?>"/>
									<label class="control-label">Kode Divisi<span class="required">*</span></label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group form-md-line-input">
									<input type="text" class="form-control" name="division_name" id="division_name" value="<?php echo set_value('division_name',$coredivision['division_name']);?>"/>
									<label class="control-label">Nama Divisi<span class="required">*</span></label>
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
<input type="hidden" class="form-control" name="division_id" id="division_id" placeholder="id" value="<?php echo set_value('division_id',$coredivision['division_id']);?>"/>
<?php echo form_close(); ?>
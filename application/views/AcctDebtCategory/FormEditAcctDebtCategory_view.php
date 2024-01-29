<script>
	base_url = '<?php echo base_url();?>';
	mappia = "	<?php 
					$site_url = 'debt-category/add/';
					echo site_url($site_url); 
				?>";
</script>
<?php echo form_open('debt-category/process-edit',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
	$sesi 	= $this->session->userdata('unique');
	$data 	= $this->session->userdata('editacctdebtcategory-'.$sesi['unique']);
?>
<div class="page-bar">
	<ul class="page-breadcrumb">
		<li>
			<i class="fa fa-home"></i>
			<a href="<?php echo base_url();?>">
				Home
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
			<a href="<?php echo base_url();?>debt-category/edit/"<?php $this->uri->segment(3); ?>>
				Edit Kategori Potong Gaji 
			</a>
		</li>
	</ul>
</div>
		<!-- END PAGE TITLE & BREADCRUMB-->
<h3 class="page-title">
	Form Edit Kategori Potong Gaji 
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
									<input type="text" class="form-control" name="debt_category_code" id="debt_category_code" value="<?php echo set_value('debt_category_code',$acctdebtcategory['debt_category_code']);?>">
									<label class="control-label">Kode Kategori
										<span class="required">
											*
										</span>
									</label>
								</div>
							</div>		
							<div class="col-md-6">
								<div class="form-group form-md-line-input">
									<input type="text" class="form-control" name="debt_category_name" id="debt_category_name" value="<?php echo set_value('debt_category_name',$acctdebtcategory['debt_category_name']);?>">
									<label class="control-label">Nama Kategori
										<span class="required">
											*
										</span>
									</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group form-md-line-input">
									<?php echo form_dropdown('debet_account_id', $acctaccount, set_value('debet_account_id',$acctdebtcategory['debet_account_id']),'id="debet_account_id" class="form-control select2me" ');?>
									<label class="control-label">Debit<span class="required">*</span></label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group form-md-line-input">
									<?php echo form_dropdown('credit_account_id', $acctaccount, set_value('credit_account_id',$acctdebtcategory['credit_account_id']),'id="credit_account_id" class="form-control select2me" ');?>
									<label class="control-label">Kredit<span class="required">*</span></label>
								</div>
							</div>
						</div>

						<input type="hidden" class="form-control" name="debt_category_id" id="debt_category_id" placeholder="id" value="<?php echo set_value('debt_category_id',$acctdebtcategory['debt_category_id']);?>"/>

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

<div class="modal fade bs-modal-lg" id="modalcoabaru" tabindex="-1" role="basic" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Nomor Perkiraan Baru</h4>
			</div>
			
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group form-md-line-input">
							<input type="text" class="form-control" id="account_code" name="account_code" autocomplete="off">
							<label class="control-label">Nomor Perkiraan</label>
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group form-md-line-input">
							<input type="text" class="form-control" id="account_name" name="account_name" autocomplete="off">
							<label class="control-label">Nama Perkiraan</label>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group form-md-line-input">
							<input type="text" class="form-control" id="account_group" name="account_group" autocomplete="off">
							<label class="control-label">Golongan Perkiraan</label>
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group form-md-line-input">
							<?php echo form_dropdown('account_status', $accountstatus, set_value('account_status'),'id="account_status" class="form-control select2me"');?>
							<label class="control-label"></label>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group form-md-line-input">
							<?php echo form_dropdown('account_type_id', $kelompokperkiraan, set_value('account_type_id'),'id="account_type_id" class="form-control select2me"');?>
							<label class="control-label">Kelompok Perkiraan</label>
						</div>
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn red" data-dismiss="modal"><i class="fa fa-times"> Tutup</i></button>
				<button type="button" class="btn green-jungle" onClick='processAddAcctAccount();'><i class="fa fa-check"> Simpan</i></button>
			</div>
		</div>
	</div>
</div>

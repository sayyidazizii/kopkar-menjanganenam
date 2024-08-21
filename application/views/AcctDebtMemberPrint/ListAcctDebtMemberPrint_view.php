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
</style>

<script type="text/javascript">
	base_url = '<?php echo base_url();?>';
</script>

<div class="row-fluid">
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
				<a href="<?php echo base_url();?>debt-member-print">
					Daftar Cetak Slip Potong Gaji
				</a>
				<i class="fa fa-angle-right"></i>
			</li>
		</ul>
	</div>
</div>

<h3 class="page-title">
	Daftar Cetak Slip Potong Gaji <small>Kelola Cetak Slip Potong Gaji</small>
</h3>
<?php
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');

	$auth = $this->session->userdata('auth');
	$sesi = $this->session->userdata('filter-acctdebt');

	if(!is_array($sesi)){
		$sesi['start_date']		= date('Y-m-d');
		$sesi['end_date']		= date('Y-m-d');	
	}

	$start_date = $sesi['start_date'];
	$end_date 	= $sesi['end_date'];
?>	
<?php	echo form_open('debt-member-print/viewreport',array('id' => 'myform', 'class' => '')); ?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Cetak Slip Potong Gaji
				</div>
			</div>
			<div class="portlet-body">
				<div class="form-body form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group form-md-line-input">
								<?php 
									echo form_dropdown('division_id', $coredivision, set_value('division_id', $data['division_id']), 'id="division_id" class="form-control select2me"');
								?>
								<label class="control-label">Divisi</label>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-actions right">
							<button type="submit" class="btn green-jungle" id="view" name="view" value="pdf"><i class="fa fa-file-pdf-o"></i> Cetak Data</button>
						</div>	
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
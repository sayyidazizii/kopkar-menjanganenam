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
				<a href="<?php echo base_url();?>debt-print">
					Daftar Cut Off Potong Gaji
				</a>
				<i class="fa fa-angle-right"></i>
			</li>
		</ul>
	</div>

	<h3 class="page-title">
		Daftar Cut Off Potong Gaji <small>Kelola Cut Off Potong Gaji</small>
	</h3>
<?php
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');

	$auth = $this->session->userdata('auth');

	$sesi=$this->session->userdata('filter-acctdebt');

	if(!is_array($sesi)){
		
		$sesi['start_date']		= date('Y-m-d');
		$sesi['end_date']		= date('Y-m-d');	
	}

	$start_date = $sesi['start_date'];
	$end_date 	= $sesi['end_date'];
?>	
<?php	echo form_open('debt-cut-off/process-add',array('id' => 'myform', 'class' => '')); ?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Cut Off Potong Gaji
				</div>
			</div>
			<div class="portlet-body">
				<div class="form-body form">
					 <div class = "row">
						<div class = "col-md-4">
							<div class="form-group form-md-line-input">
								<?php 
									echo form_dropdown('debt_cut_off_month', $months, set_value('debt_cut_off_month', $debt_cut_off_month), 'id="debt_cut_off_month" class="form-control select2me" disabled');
								?>
								<input type="hidden" class="form-control" name="debt_cut_off_month" id="debt_cut_off_month" value="<?php echo set_value('debt_cut_off_month',$debt_cut_off_month);?>">
								<label class="control-label">Bulan
									<span class="required">
										*
									</span>
								</label>
							</div>
						</div>
						<div class = "col-md-4">
							<div class="form-group form-md-line-input">
								<input type="text" class="form-control" name="debt_cut_off_year" id="debt_cut_off_year" value="<?php echo set_value('debt_cut_off_year',$debt_cut_off_year);?>" readonly>
								<label class="control-label">Tahun
									<span class="required">
										*
									</span>
								</label>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="form-actions right">
							<button type="submit" class="btn green-jungle">Proses</button>
						</div>	
					</div>
				</div>
			</div>	
		</div>
	</div>
</div>
<?php echo form_close(); ?>
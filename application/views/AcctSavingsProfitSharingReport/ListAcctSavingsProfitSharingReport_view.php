<style>
	th, td {
	  padding: 3px;
	}
	input:focus { 
	  background-color: 42f483;
	}
</style>

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
			<a href="<?php echo base_url();?>AcctSavingsProfitSharingReport">
				Daftar Bagi Hasil Simpanan
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Daftar Bagi Hasil Simpanan
				</div>
			</div>
			<div class="portlet-body">
				<?php
					echo form_open('AcctSavingsProfitSharingReport/viewreport'); 
				?>
				<div class="portlet-body">
					<div class="form-body">
						<div class="row">
							<div class="col-md-12 " style="text-align  : center !important;">
								<button name="view" id="view" value="pdf" class="btn blue"><span class="glyphicon glyphicon-eye-open"></span> Preview Data</button>
								<button name="view" id="view" value="excel" class="btn blue"><span class="glyphicon glyphicon-print"></span> Export Data</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
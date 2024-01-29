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
				Daftar Cetak Potong Gaji
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>

<h3 class="page-title">
	Daftar Cetak Potong Gaji <small>Kelola Cetak Potong Gaji</small>
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
<?php	echo form_open('debt-print/viewreport',array('id' => 'myform', 'class' => '')); ?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Cetak Potong Gaji
				</div>
			</div>
			<div class="portlet-body">
				<div class="form-body form">
					 <div class = "row">
						<div class = "col-md-4">
							<div class="form-group form-md-line-input">
								<input class="form-control form-control-inline input-medium date-picker" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" value="<?php echo tgltoview($start_date);?>"/>
								<label class="control-label">Tanggal Awal
									<span class="required">
										*
									</span>
								</label>
							</div>
						</div>

						<div class = "col-md-4">
							<div class="form-group form-md-line-input">
								<input class="form-control form-control-inline input-medium date-picker" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" value="<?php echo tgltoview($end_date);?>"/>
								<label class="control-label">Tanggal Akhir
									<span class="required">
										*
									</span>
								</label>
							</div>
						</div>

						<div class = "col-md-4">
							<div class="form-group form-md-line-input">
								<?php 
									echo form_dropdown('debt_category_id', $acctdebtcategory, set_value('debt_category_id', $data['debt_category_id']), 'id="debt_category_id" class="form-control select2me"');
								?>
								<label class="control-label">Kategori</label>
							</div>
						</div>

						<div class = "col-md-6">
							<div class="form-group form-md-line-input">
								<?php 
									echo form_dropdown('part_id', $corepart, set_value('part_id', $data['part_id']), 'id="part_id" class="form-control select2me"');
								?>
								<label class="control-label">Bagian</label>
							</div>
						</div>

						<div class = "col-md-6">
							<div class="form-group form-md-line-input">
								<?php 
									echo form_dropdown('division_id', $coredivision, set_value('division_id', $data['division_id']), 'id="division_id" class="form-control select2me"');
								?>
								<label class="control-label">Divisi</label>
							</div>
						</div>
					</div>

					<div style="text-align: center !important">
						<div class="row">
							<div class="form-actions">
								<button type="submit" class="btn green" id="view" name="view" value="pdf_category"><i class="fa fa-file-pdf-o"></i> PDF Kategori</button>
								<button type="submit" class="btn green" id="view" name="view" value="pdf_member"><i class="fa fa-file-pdf-o"></i> PDF Simpanan Agt</button>
								<button type="submit" class="btn green" id="view" name="view" value="pdf_savings"><i class="fa fa-file-pdf-o"></i> PDF Tabungan</button>
								<button type="submit" class="btn green" id="view" name="view" value="pdf_credits"><i class="fa fa-file-pdf-o"></i> PDF Pinjaman</button>
								<button type="submit" class="btn green" id="view" name="view" value="pdf_store"><i class="fa fa-file-pdf-o"></i> PDF Toko</button>
							</div>	
						</div>
						<div class="row">
							<div class="form-actions">
								<button type="submit" class="btn green-jungle" id="view" name="view" value="excel_category"><i class="fa fa-file-excel-o"></i> Excel Kategori</button>
								<button type="submit" class="btn green-jungle" id="view" name="view" value="excel_member"><i class="fa fa-file-excel-o"></i> Excel Simpanan Agt</button>
								<button type="submit" class="btn green-jungle" id="view" name="view" value="excel_savings"><i class="fa fa-file-excel-o"></i> Excel Tabungan</button>
								<button type="submit" class="btn green-jungle" id="view" name="view" value="excel_credits"><i class="fa fa-file-excel-o"></i> Excel Pinjaman</button>
								<button type="submit" class="btn green-jungle" id="view" name="view" value="excel_store"><i class="fa fa-file-excel-o"></i> Excel Toko</button>
							</div>	
						</div>
						<div class="row">
							<div class="form-actions">
								<button type="submit" class="btn yellow" id="view" name="view" value="pdf_recap"><i class="fa fa-file-pdf-o"></i> PDF Rekap</button>
								<button type="submit" class="btn yellow" id="view" name="view" value="excel_recap"><i class="fa fa-file-excel-o"></i> Excel Rekap</button>
							</div>	
						</div>
						<div class="row">
							<div class="form-actions">
								<button type="submit" class="btn purple" id="view" name="view" value="pdf_simple"><i class="fa fa-file-pdf-o"></i> PDF Simple</button>
								<button type="submit" class="btn purple" id="view" name="view" value="excel_simple"><i class="fa fa-file-excel-o"></i> Excel Simple</button>
							</div>	
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
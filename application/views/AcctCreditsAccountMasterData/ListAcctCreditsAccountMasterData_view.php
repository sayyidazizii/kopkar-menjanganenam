<style>
	th {
		font-size: 14px !important;
		font-weight: bold !important;
		text-align: center !important;
		margin: 0 auto;
		vertical-align: middle !important;
	}

	td {
		font-size: 12px !important;
		font-weight: normal !important;
	}
</style>
<script type="text/javascript">
	base_url = "<?php echo base_url(); ?>"

	function reset_search() {
		document.location = base_url = "credits-account-master-data/reset-search";
	}
</script>
<div class="row-fluid">
	<div class="page-bar">
		<ul class="page-breadcrumb">
			<li>
				<i class="fa fa-home"></i>
				<a href="<?php echo base_url(); ?>">
					Beranda
				</a>
				<i class="fa fa-angle-right"></i>
			</li>
			<li>
				<a href="<?php echo base_url(); ?>credits-account-master-data">
					Master Data Pinjaman
				</a>
				<i class="fa fa-angle-right"></i>
			</li>
		</ul>
	</div>

	<?php
	$auth = $this->session->userdata('auth');

	if ($auth['branch_status'] == 1) {
		$sesi = $this->session->userdata('filter-masterdatacreditsaccount');

		if (!is_array($sesi)) {
			$sesi['start_date']	= date('Y-m-d');
			$sesi['end_date']	= date('Y-m-d');
			$sesi['branch_id']	= '';
			$sesi['credits_id']	= '';
		}
	?>
		<div class="row">
			<div class="col-md-12">
				<div class="portlet box blue">
					<div class="portlet-title">
						<div class="caption">
							Master Data Pinjaman
						</div>
						<div class="actions" style="margin-right: 10px;">
							<a href='javascript:void(window.open("<?php echo base_url(); ?>credits-account-master-data/export","_blank","top=100,left=200,width=300,height=300"));' title="Export to Excel" class="btn btn-default btn-sm">
								<i class="fa fa-download"></i>
								<span class="hidden-480">
									Export Master Data Pinjaman
								</span>
							</a>
						</div>
					</div>
					<div class="portlet-body">
						<div class="form-body form">
							<?php echo form_open('credits-account-master-data/filter', array('id' => 'myform', 'class' => ''));
							$start_date	= $sesi['start_date'];
							$end_date	= $sesi['end_date'];
							?>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group form-md-line-input">
										<input class="form-control form-control-inline input-medium date-picker" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" value="<?php echo tgltoview($start_date); ?>" autocomplete="off" />
										<label class="control-label">Tanggal Awal
											<span class="required">
												*
											</span>
										</label>
									</div>
								</div>

								<div class="col-md-3">
									<div class="form-group form-md-line-input">
										<input class="form-control form-control-inline input-medium date-picker" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" value="<?php echo tgltoview($end_date); ?>" autocomplete="off" />
										<label class="control-label">Tanggal Akhir
											<span class="required">
												*
											</span>
										</label>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group form-md-line-input">
										<?php echo form_dropdown('branch_id', $corebranch, set_value('branch_id', $sesi['branch_id']), 'id="branch_id" class="form-control select2me"'); ?>
										<label class="control-label">Cabang
											<span class="required">
												*
											</span>
										</label>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group form-md-line-input">
										<?php echo form_dropdown('credits_id', $acctcredits, set_value('credits_id', $sesi['credits_id']), 'id="credits_id" class="form-control select2me"'); ?>
										<label class="control-label">Jenis Pinjaman
											<span class="required">
												*
											</span>
										</label>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="form-actions right">
									<button type="button" class="btn red" onClick="reset_search();"><i class="fa fa-times"></i> Batal</button>
									<button type="submit" class="btn green-jungle"><i class="fa fa-search"></i> Cari</button>
								</div>
							</div>
						<?php echo form_close();
	} else { ?>
		<div class="row">
			<div class="col-md-12">
				<div class="portlet box blue">
	<?php } ?>
							<div class="portlet-body">
								<div class="form-body">
									<table class="table table-striped table-bordered table-hover table-full-width" id="myDataTable">
										<thead>
											<tr>
												<th width="5%">No</th>
												<th width="10%">Nomor Akad</th>
												<th width="10%">Nomor Rekening</th>
												<th width="10%">Bank</th>
												<th width="10%">a.n. Rekening</th>
												<th width="5%">No Agt</th>
												<th width="10%">Nama Anggota</th>
												<th width="10%">Jenis Pinjaman</th>
												<th width="10%">Jk Wkt</th>
												<th width="10%">Sdh Ang</th>
												<th width="10%">Tgl Pinjam</th>
												<th width="10%">Tgl Ang 1</th>
												<th width="10%">Tgl Jth Tempo</th>
												<th width="10%">Plafon</th>
												<th width="10%">Pokok</th>
												<th width="10%">Margin</th>
												<th width="10%">Ang Pokok</th>
												<th width="10%">Ang Margin</th>
												<th width="10%">Jumlah Angsuran</th>
												<th width="10%">Sisa Pokok</th>
												<th width="10%">Keterangan</th>
												<th width="20%">Keterangan Agunan</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
								<div class="row">
								</div>
							</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var table;

	$(document).ready(function() {
		$.fn.dataTable.ext.errMode = 'throw';

		table = $('#myDataTable').DataTable({
			"processing": true,
			"serverSide": true,
			"pageLength": 5,
			"order": [],
			"ajax": {
				"url": "<?php echo site_url('credits-account-master-data/get-list') ?>",
				"type": "POST"
			},
			"columnDefs": [{
				"targets": [0],
				"orderable": false,
			}, ],
		});
	});
</script>
<?php echo form_close(); ?>
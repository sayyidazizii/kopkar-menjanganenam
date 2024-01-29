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
			<a href="<?php echo base_url();?>savings-import-mutation">
				Import Tabungan
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>

<h3 class="page-title">
	Import Tabungan <small>Kelola Import Tabungan</small>
</h3>
<?php
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');

	$auth = $this->session->userdata('auth');

	$sesi=$this->session->userdata('filter-acctsavingsimportmutation');

	if(!is_array($sesi)){
		$sesi['start_date']		= date('Y-m-d');
		$sesi['end_date']		= date('Y-m-d');	
	}

	$start_date = $sesi['start_date'];
	$end_date 	= $sesi['end_date'];
?>	
<?php	echo form_open('savings-import-mutation/filter',array('id' => 'myform', 'class' => '')); ?>

<script type="text/javascript">
		base_url = '<?php echo base_url();?>';

	function reset_search(){
		document.location = base_url+"savings-import-mutation/reset-list";
	}

</script>
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Pencarian
				</div>
				<div class="tools">
					<a href="javascript:;" class='expand'></a>
				</div>
			</div>
			<div class="portlet-body display-hide">
				<div class="form-body form">
					 <div class = "row">
						<div class = "col-md-6">
							<div class="form-group form-md-line-input">
								<input class="form-control form-control-inline input-medium date-picker" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" value="<?php echo tgltoview($start_date);?>"/>
								<label class="control-label">Tanggal Awal
									<span class="required">
										*
									</span>
								</label>
							</div>
						</div>

						<div class = "col-md-6">
							<div class="form-group form-md-line-input">
								<input class="form-control form-control-inline input-medium date-picker" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" value="<?php echo tgltoview($end_date);?>"/>
								<label class="control-label">Tanggal Akhir
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
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Daftar
				</div>
				<div class="actions">
					<a href="<?php echo base_url();?>savings-import-mutation/add" class="btn btn-default btn-sm">
						<i class="fa fa-plus"></i>
						<span class="hidden-480">
							Tambah Import Tabungan Baru
						</span>
					</a>
				</div>
			</div>
			<div class="portlet-body">
				<h4 style="font-weight: bold;">Tunai</h4>
				<div class="form-body">
					<table class="table table-striped table-bordered table-hover table-full-width" id="myDataTable">
					<thead>
						<tr>
							<th width="5%">No</th>
							<th width="15%">Nama Anggota</th>
							<th width="10%">Jenis Tabungan</th>
							<th width="10%">No Rekening</th>
							<th width="10%">Jenis Mutasi</th>
							<th width="10%">Tanggal Mutasi</th>
							<th width="10%">Jumlah</th>
							<th width="20%">Keterangan</th>
							<th width="10%">Aksi</th>
						</tr>
					</thead>
					<tbody></tbody>
					</table>
				</div>
			</div>
			
			<div class="portlet-body">
				<h4 style="font-weight: bold;">Bank</h4>
				<div class="form-body">
					<table class="table table-striped table-bordered table-hover table-full-width" id="myDataTable2">
					<thead>
						<tr>
							<th width="5%">No</th>
							<th width="15%">Nama Anggota</th>
							<th width="10%">Jenis Tabungan</th>
							<th width="5%">No Rekening</th>
							<th width="10%">Jenis Mutasi</th>
							<th width="10%">Bank</th>
							<th width="10%">Tanggal Mutasi</th>
							<th width="10%">Jumlah</th>
							<th width="15%">Keterangan</th>
							<th width="10%">Aksi</th>
						</tr>
					</thead>
					<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- END EXAMPLE TABLE PORTLET-->
	</div>
</div>
<script type="text/javascript">
 
var table;
 
$(document).ready(function() {
    table = $('#myDataTable').DataTable({ 
        "processing": true,
        "serverSide": true,
        "pageLength": 5,
        "order": [],
        "ajax": {
            "url": "<?php echo site_url('savings-import-mutation/get-list')?>",
            "type": "POST"
        },
        "columnDefs": [
        { 
            "targets": [0, 5, 8],
            // "orderable": false,
			"className" : "text-center",
        },
		{ 
			targets: [1, 2, 3, 7], 
			className: "text-left",
		},
		{ 
			targets: [6], 
			className: "text-right",
		},
        ],
    });
 
    table = $('#myDataTable2').DataTable({ 
        "processing": true,
        "serverSide": true,
        "pageLength": 5,
        "order": [],
        "ajax": {
            "url": "<?php echo site_url('savings-import-mutation/get-list-bank')?>",
            "type": "POST"
        },
        "columnDefs": [
        { 
            "targets": [0, 5, 8],
            // "orderable": false,
			"className" : "text-center",
        },
		{ 
			targets: [1, 2, 3, 7], 
			className: "text-left",
		},
		{ 
			targets: [6], 
			className: "text-right",
		},
        ],
    });
});
</script>
<?php echo form_close(); ?>
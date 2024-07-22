<script src="<?php echo base_url(); ?>assets/global/scripts/moment.js" type="text/javascript"></script>
<style>
	th,
	td {
		padding: 3px;
	}

	td {
		font-size: 12px;
	}

	input:focus {
		background-color: 42f483;
	}

	.custom {
		margin: 0px;
		padding-top: 0px;
		padding-bottom: 0px;
	}

	.textbox .textbox-text {
		font-size: 10px;
	}

	input:read-only {
		background-color: f0f8ff;
	}
</style>
<script>
    function function_elements_add(name, value) {
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('credit-account/add-function-element'); ?>",
			data: {
				'name': name,
				'value': value
			},
			success: function(msg) {}
		});
	}

    function confirmDelete() {
		return confirm('Are you sure you want to reset the data? This action cannot be undone.');
	}
</script>
<?php echo form_open_multipart('migration/add-credits-account-array', array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
$sesi 	= $this->session->userdata('unique');
$data 	= $this->session->userdata('addcreditsaccountmigration-' . $sesi['unique']);
$token 	= $this->session->userdata('acctcreditsaccountmigrationtoken-' . $sesi['unique']);

?>

<?php if($this->session->userdata('message')) { ?>
    <?php echo $this->session->userdata('message'); ?>
    <?php $this->session->unset_userdata('message'); ?>
<?php } ?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet">
			<div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Form Migrasi Credits
					</div>
					<div class="actions">
						<a href="<?php echo base_url() ?>migration" class="btn btn-default btn-sm">
							<i class="fa fa-angle-left"></i>
							<span class="hidden-480">
								Kembali
							</span>
						</a>
					</div>
				</div>
				<div class="portlet-body">
					<div class="col-md-4">
						<table style="width: 100%;" border="0" padding:"0">
							<tbody>
								<tr>
								<td width="35%">File Excel</td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="" accept=".xlsx, .xls, .csv" class="easyui-filebox" name="excel_file" id="excel_file" style="width: 60%"/>
										</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-md-8">
						
					</div>
					<div class="row">
						<div class="col-md-12" style='text-align:right'>
							<button type="submit" name="Save" value="Save" id="Save" class="btn green-jungle" title="Simpan Data">Import</i></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
<?php echo form_open_multipart('migration/update-credits-account-amount',array('id' => 'myform', 'class' => 'horizontal-form')); ?>

<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			<div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Daftar File Excel
					</div>
				</div>
				<div class="portlet-body">
					<div class="form-body">
						<div class="row">
						<table class="table table-striped table-bordered table-hover table-full-width" id="myDataTable">
							<thead>
								<tr>
								    <th width="5%">No</th>
									<th width="15%">no_pinjaman</th>
									<th width="15%">no_agt</th>
									<th width="15%">member_id</th>
									<th width="15%">nama</th>
									<th width="15%">jns_pinjm</th>
									<th width="15%">credits_id</th>
									<th width="15%">jk_waktu</th>
									<th width="15%">tgl_pinjm</th>
									<th width="15%">jt_tempo</th>
									<th width="15%">plafon</th>
									<th width="15%">pokok_perbulan</th>
									<th width="15%">total_perbulan</th>
									<th width="15%">sk_bng</th>
									<th width="15%">sld_pokok</th>
									<th width="15%">total_angsur</th>
									<th width="15%">sisa_angsuran</th>
									<th width="15%">tgl_trkhir_angsur</th>
									<th width="15%">tgl_angsur_brktny</th>
									<th width="15%">preferensi_angsuran</th>
									<th width="15%">payment_preference_id</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$no = 1;
								
									foreach($creditsaccount as $key => $val){
								?>
										<tr>
											<td width="5%" style="text-align: center;"><?php echo $no; ?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['no_pinjaman']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['no_agt']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['member_id']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['nama']?></td>
                                            <td width="25%" style="text-align: left;"><?php echo $val['jns_pinjm']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['credits_id']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['jk_waktu']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['tgl_pinjm']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['jt_tempo']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['plafon']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['pokok_perbulan']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['total_perbulan']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['sk_bng']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['sld_pokok']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['total_angsur']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['sisa_angsuran']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['tgl_trkhir_angsur']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['tgl_angsur_brktny']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['preferensi_angsuran']?></td>
											<td width="25%" style="text-align: left;"><?php echo $val['payment_preference_id']?></td>
										</tr>
								<?php 
										$no++;
									}
								?>
							</tbody>
						</table>
						</div>
						<div class="row">
                        <table style="width: 100%;" border="0" padding="0">
						</table>
							<div class="col-md-12" style='text-align:right'>
								<a href="delete-deposito-account-old" name="delete" value="delete" id="delete" class="btn red" title="Reset Data" onclick="return confirmDelete();"><i class="fa fa-trash"> Reset Data</i></a>
								<button type="submit" name="Save" value="Save" id="Save" class="btn green-jungle" title="Simpan Data" onclick="return confirmDelete();"><i class="fa fa-save"> Simpan</i></button>
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
	$(document).ready(function() {
        $('#myDataTable').DataTable(); // Inisialisasi DataTables
    });
</script>
<script type="text/javascript">
	function myformatter(date) {
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();
		return (d < 10 ? ('0' + d) : d) + '-' + (m < 10 ? ('0' + m) : m) + '-' + y;
	}

	function myparser(s) {
		if (!s) return new Date();
		var ss = (s.split('-'));
		var y = parseInt(ss[0], 10);
		var m = parseInt(ss[1], 10);
		var d = parseInt(ss[2], 10);
		if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
			return new Date(d, m - 1, y);
		} else {
			return new Date();
		}
	}
</script>
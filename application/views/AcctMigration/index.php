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
</script>
<?php
$sesi = $this->session->userdata('unique');
$data = $this->session->userdata('addcreditaccount-' . $sesi['unique']);
$token = $this->session->userdata('acctcreditsaccounttoken-' . $sesi['unique']);
?>

<?php if ($this->session->userdata('message')) { ?>
	<?php echo $this->session->userdata('message'); ?>
	<?php $this->session->unset_userdata('message'); ?>
<?php } ?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet">
			<div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Migrasi 
					</div>
					<div class="actions">
						
					</div>
				</div>
				<div class="portlet-body">
					<div class="row">
						<div class="col-md-4">
							<div class="card mb-4 ">
								<div class="card-body">
									<h5 class="card-title">Data Migrasi Laba rugi</h5>
									<p class="card-text">terakhir migrasi :
								</p>
									<a href="<?php echo base_url() ?>migration/add-profit-loss"  class="btn green-jungle" title="Simpan Data"><i class="fa fa-check"> update</i></a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mb-4 ">
								<div class="card-body">
									<h5 class="card-title">Data Migrasi Neraca</h5>
									<p class="card-text">terakhir migrasi :
								</p>
									<a href="<?php echo base_url() ?>migration/add-balance-sheet"  class="btn green-jungle" title="Simpan Data"><i class="fa fa-check"> update</i></a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mb-4 ">
								<div class="card-body">
									<h5 class="card-title">Data Migrasi Tabungan</h5>
									<p class="card-text">terakhir migrasi :
								</p>
									<a href="<?php echo base_url() ?>migration/add-savings-account"  class="btn green-jungle" title="Simpan Data"><i class="fa fa-check"> update</i></a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mb-4 ">
								<div class="card-body">
									<h5 class="card-title">Data Migrasi Sicantik</h5>
									<p class="card-text">terakhir migrasi :
								</p>
									<a href="<?php echo base_url() ?>migration/add-sicantik"  class="btn green-jungle" title="Simpan Data"><i class="fa fa-check"> update</i></a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mb-4 ">
								<div class="card-body">
									<h5 class="card-title">Data Migrasi Deposito</h5>
									<p class="card-text">terakhir migrasi :
								</p>
									<a href="<?php echo base_url() ?>migration/add-deposito-account"  class="btn green-jungle" title="Simpan Data"><i class="fa fa-check"> update</i></a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mb-4 ">
								<div class="card-body">
									<h5 class="card-title">Generate Deposito</h5>
									<p class="card-text">terakhir migrasi :
								</p>
									<a href="<?php echo base_url() ?>deposito-account/form-generate-profit"  class="btn green-jungle" title="Simpan Data"><i class="fa fa-check"> generate</i></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>

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

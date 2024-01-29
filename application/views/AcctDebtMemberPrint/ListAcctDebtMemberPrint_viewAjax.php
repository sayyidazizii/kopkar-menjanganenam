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
	.veryfront {
		background-color:rgba(192,192,192, 0.5);
		position:absolute;
		top:0;
		right:0;
		bottom:0;
		left:0;
		margin: auto;
		z-index: 6;
		text-align: center;
	}
	.loader {
		margin: 0;
		position: absolute;
		top: 40%;
		left: 45%;
		-ms-transform: translate(-50%, -50%);
		transform: translate(-50%, -50%);
		height: 100%;
		border: 16px solid #000000;
		border-radius: 50%;
		border-top: 16px solid #0a98ec;
		width: 120px;
		height: 120px;
		-webkit-animation: spin 2s linear infinite; /* Safari */
		animation: spin 2s linear infinite;
	}
	/* Safari */
	@-webkit-keyframes spin {
		0% { -webkit-transform: rotate(0deg); }
		100% { -webkit-transform: rotate(360deg); }
	}

	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}
</style>

<script type="text/javascript">
	base_url = '<?php echo base_url();?>';
	
	function print() {
		console.log('masuk');
		var division_id = document.getElementById("division_id").value;
		var x 			= document.getElementById("veryfront");
		
		console.log('1');
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('debt-member-print/viewreport'); ?>",
			data: {
				'division_id': division_id,
			},
			success: function(msg) {
				console.log(msg);
				x.style.display = "none";
				// let dataBase64 = msg;
				// let ventanaPDF = window.open("", "_blank", "width=450, height=650, nodeIntegration=no, modal");
				// let contenidoFinalVentana = '<embed width=100% height=100% type="application/pdf" src="data:application/pdf;base64,' + escape(dataBase64) + '"></embed>';
				// ventanaPDF.document.write(contenidoFinalVentana);
			},
			error: function (jqXHR, textStatus, errorThrown) { 
				x.style.display = "none";
			}
		});
	}
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
<div class="veryfront" id="veryfront" style="display: none">
	<div class="loader"></div>
	<div class="loader-msg"><h1><b>Loading...</b></h1></div>
</div>
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
							<a class="btn green-jungle" id="view" name="view" value="pdf" onclick="print()" ><i class="fa fa-file-pdf-o"></i> Cetak Data</a>
						</div>	
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
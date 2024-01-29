<style type="text/css">
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
		border-top: 16px solid #c776dc;
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
<script>
	base_url 	= '<?php echo base_url();?>';
	mappia 		= "	<?php 
		$site_url = 'wa-broadcast/add/';
		echo site_url($site_url); 
	?>";

	function saveBroadcast(){
		var broadcast_title		= document.getElementById("broadcast_title").value;
		var broadcast_message	= document.getElementById("broadcast_message").value;
		var broadcast_link		= document.getElementById("broadcast_link").value;
  		var x = document.getElementById("veryfront");
		x.style.display = "block";

		$.ajax({
			type: "POST",
			url : "<?php echo site_url('wa-broadcast/process-add');?>",
			data : {
				'broadcast_title' 	: broadcast_title,
				'broadcast_message' : broadcast_message,
				'broadcast_link' 	: broadcast_link,
			},
			success: function(msg){
				$('#message').html(msg);
				x.style.display = "none";
			},
        	error: function (jqXHR, textStatus, errorThrown) { 
				x.style.display = "none";
			}
		});
		
	}
</script>
<?php echo form_open('wa-broadcast/process-add',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
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
			<a href="<?php echo base_url();?>wa-broadcast">
				Daftar Pengumuman Anggota
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>wa-broadcast/add">
				Tambah Pengumuman Anggota 
			</a>
		</li>
	</ul>
</div>

<h3 class="page-title">
	Form Tambah Pengumuman Anggota
</h3>
<?php
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');
?>
<div id="message"></div>
<div class="veryfront" id="veryfront" style="display: none">
	<div class="loader"></div>
	<div class="loader-msg"><h1><b>Mohon tunggu, pesan sedang dikirim</b></h1></div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			 <div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Form Tambah
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>wa-broadcast" class="btn btn-default btn-sm">
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
									<input type="text" class="form-control" name="broadcast_title" id="broadcast_title" autocomplete="off" value=""/>
									<label class="control-label">Judul<span class="required">*</span></label>
								</div>
							</div>
						</div>
						<div class="row">		
							<div class="col-md-12">
								<div class="form-group form-md-line-input">
									<textarea class="form-control" rows="3" name="broadcast_message" id="broadcast_message"></textarea>
									<label class="control-label">Pesan<span class="required">*</span>
									</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group form-md-line-input">
									<input type="text" class="form-control" name="broadcast_link" id="broadcast_link" autocomplete="off" value=""/>
									<label class="control-label">Link</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12" style='text-align:right'>
								<button type="reset" name="Reset" value="Reset" class="btn btn-danger" onClick="reset_data();"><i class="fa fa-times"> Batal</i></button>
								<a id="Save" class="btn green-jungle" title="Simpan Data" onclick="saveBroadcast()"><i class="fa fa-check"> Simpan</i></a>
							</div>	
						</div>
					</div>
				</div>
			 </div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
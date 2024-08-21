<script>
	base_url = '<?php echo base_url();?>';
	mappia = "	<?php 
					$site_url = 'wa-broadcast/add/';
					echo site_url($site_url); 
				?>";
				
	$(document).ready(function(){
        var img = <?php echo $img; ?>;

        if(!img || img==''){
            img = '';
        }

        code = img;
        console.log(code);
        
        if(code.qrcode != null){
            document.getElementById("qr_image").src = code.qrcode;
            document.getElementById("success").style.display = "none";
            console.log(1);
        }else{
            document.getElementById("reloadqr").style.display = "none";
            console.log(2);
        }
    });

	function reloadQR(){
		location.reload();
	}

	function reloadAPI(){
		$.ajax({
			type: "POST",
			url : "{{route('reload-scan-qr')}}",
			dataType: "html",
			data: {
				'_token' : '{{csrf_token()}}',
			},
			success: function(return_data){ 
				location.reload();
			},
			error: function(data)
			{
				console.log(data);

			}
		});
	}
	
	$.ajax({
		type: 'post',
		data: dataString,       
		url: '<?php echo base_url();?>wa-reload',
		success: function (results) {
			location.reload();
		}
	});
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
			<a href="<?php echo base_url();?>wa-scan">
				Scan Kode QR
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>

<h3 class="page-title">
	Scan Kode QR
</h3>
<?php
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');
?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			 <div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Scan Kode QR
					</div>
				</div>
				<div class="portlet-body">
					<div class="form-body">
						<div class="row">						
							<div class="col-md-12">
       			 				<center><img src="assets/layouts/layout/img/success.png" id="qr_image" width="200px" height="200px"/>
							</div>
						</div>

						<br>
						<div class="row">
							<div class="col-md-12" style='text-align:center'>
								<button type="button" class="btn btn-primary" id="reloadqr" onclick="reloadQR()">Minta Ulang Kode</button>
								<button type="button" class="btn btn-success" id="success">WA Berhasil Terkoneksi</button>
							</div>	
						</div>
					</div>
				</div>
			 </div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
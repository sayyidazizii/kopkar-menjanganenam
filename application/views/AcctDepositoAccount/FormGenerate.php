<style>
	th, td {
	  padding: 2px;
	  font-size: 14px;
	}
	input:focus { 
	  background-color: 42f483;
	}
	input:read-only {
		background-color: f0f8ff;
	}
</style>    
<script>
	base_url = '<?php echo base_url();?>';

	var loop = 1;

	$(document).ready(function(){
		$('#deposito_id').textbox({
		   collapsible:false,
		   minimizable:false,
		   maximizable:false,
		   closable:false
		});
		
		$('#deposito_id').textbox('textbox').focus();
	});

	function toRp(number) {
		var number = number.toString(), 
		rupiah = number.split('.')[0], 
		cents = (number.split('.')[1] || '') +'00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1,')
			.split('').reverse().join('');
		return rupiah + '.' + cents.slice(0, 2);
	}

	$(document).ready(function(){
		 $('#deposito_id').combobox({
			  onChange: function(value){
			  	var deposito_id 	= +document.getElementById("deposito_id").value;

			  

			   $.post(base_url + 'deposito-account/get-deposite-account-no',
				{deposito_id: deposito_id},
                function(data){	
                var obj = $.parseJSON(data);
                	console.log(obj);	   
                	$("#deposito_period").textbox('setValue',obj['deposito_period']);
				   	$("#deposito_account_no").textbox('setValue',obj['deposito_account_no']);
				   	$("#deposito_account_serial_no").textbox('setValue',obj['deposito_account_serial_no']);
				   	$("#deposito_account_due_date").textbox('setValue',obj['deposito_account_due_date']);
				   	$("#deposito_account_nisbah").textbox('setValue',obj['deposito_account_nisbah']);
				},
				
				)
			  }
			})
	});


	$(document).ready(function(){
		$('#deposito_account_amount_view').textbox({
			onChange: function(value){
				console.log(value);
				console.log(loop);
				if(loop == 0){
					loop= 1;
					return;
				}
				if(loop ==1){
					loop =0;
					var tampil = toRp(value);
				$('#deposito_account_amount').textbox('setValue', value);
				$('#deposito_account_amount_view').textbox('setValue', tampil);

				}else{
					loop=1;
					return;
				}
			
			}
		});
	});
</script>

<form method="POST" action="generate-profit">
		<!-- BEGIN PAGE TITLE & BREADCRUMB-->
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
			<a href="<?php echo base_url();?>deposito-account">
				Daftar Rekening Simpanan Berjangka
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>deposito-account/form-generate-profit">
            Generate Profit Rekening Simpanan Berjangka
			</a>
		</li>
	</ul>
</div>
		<!-- END PAGE TITLE & BREADCRUMB-->
<?php
// print_r($data);
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');
?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			 <div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Form Generate Profit Rekening Simpanan Berjangka
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>deposito-account" class="btn btn-default btn-sm">
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
							<div class="col-md-1"></div>
							<div class="col-md-5">
								<table width="100%">
									
								</table>
							</div>
							<div class="col-md-1"></div>
							<div class="col-md-5">
								<table width="100%">
									<tr>
										<td width="35%"></td>
										<td width="5%"></td>
										<td width="60%" align="right">
											<button type="button" class="btn red" onClick="reset_data();"><i class="fa fa-times"></i> Batal</button>
											<button type="submit" class="btn green-jungle"><i class="fa fa-check"></i> Generate</button>
										</td>
									</tr>
								</table>
							</div>
						</div>					
					</div>
				</div>
			 </div>
		</div>
	</div>
</div>
</form>

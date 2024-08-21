<style>
	th, td {
	  padding: 3px;
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
		$('#member_mandatory_savings_view').textbox({
			onChange: function(value){
				if(loop == 0){
					loop= 1;
					return;
				}
				if(loop ==1){
					loop =0;
					var tampil = toRp(value);
					$('#member_mandatory_savings').textbox('setValue', value);
					$('#member_mandatory_savings_view').textbox('setValue', tampil);
				}else{
					loop=1;
					return;
				}
			}
		});
	});

</script>
<?php echo form_open('member/process-edit-mandatory-savings',array('id' => 'myform', 'class' => 'horizontal-form')); 

$unique = $this->session->userdata('unique');
$token 	= $this->session->userdata('coremembertokensalarymandatory-'.$unique['unique']);
?>

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
			<a href="<?php echo base_url();?>member">
				Daftar Anggota
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>member/salary-mandatory-savings" ?>>
				Edit Simpanan Wajib Anggota 
			</a>
		</li>
	</ul>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			<div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Form Simpanan Wajib Anggota
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>member/salary-mandatory-savings" class="btn btn-default btn-sm">
							<i class="fa fa-angle-left"></i>
							<span class="hidden-480">
								Kembali
							</span>
						</a>
					</div>
				</div>
				<div class="portlet-body form">
					<div class="form-body">
						<?php
							echo $this->session->userdata('message');
							$this->session->unset_userdata('message');
						?>
						<div class="row">
							<div class="col-md-12">
								<table width="40%" align="left">
									<tr>
										<td width="35%">Simpanan Wajib</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_mandatory_savings_view" id="member_mandatory_savings_view" value="" autocomplete="off" style="width: 100%"/>
											<input type="hidden" class="easyui-textbox" name="member_mandatory_savings" id="member_mandatory_savings" value=""  autocomplete="off" />
										</td>
									</tr>
								</table>
								<table width="100%" style="margin-top:20px">
									<tr>
										<td width="35%"></td>
										<td width="5%"></td>
										<td width="60%" align="right">
											<button type="submit" class="btn green-jungle"><i class="fa fa-check"></i> Simpan</button>
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
<?php echo form_close(); ?>
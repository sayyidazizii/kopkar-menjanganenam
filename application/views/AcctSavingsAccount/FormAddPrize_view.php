<script>
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
		$('#savings_account_prize_amount_view').textbox({
			onChange: function(value){
				if(loop == 0){
					loop= 1;
					return;
				}
				if(loop ==1){
					loop =0;
					var tampil = toRp(value);
					$('#savings_account_prize_amount').textbox('setValue', value);
					$('#savings_account_prize_amount_view').textbox('setValue', tampil);
				}
			}
		});
	});
</script>
<style>
th, td {
  padding: 3px;
}
td {
  font-size: 12px;
}
input:focus { 
  background-color: 42f483;
}
.custom{

margin: 0px; padding-top: 0px; padding-bottom: 0px; 

}
.textbox .textbox-text{
font-size: 12px;


}
input:read-only {
		background-color: f0f8ff;
	}
</style>
<script type="text/javascript">
</script>
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
			<a href="<?php echo base_url();?>savings-account">
				Daftar Tabungan
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>savings-account/edit-mutation-pref/<?php echo $this->uri->segment(3);?>">
				Tambah Hadiah Tabungan
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>
<?php
	echo form_open('savings-account/process-add-prize');
?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			 <div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Form Tambah Hadiah Tabungan
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>savings-account" class="btn btn-default btn-sm">
							<i class="fa fa-angle-left"></i>
							<span class="hidden-480">
								Kembali
							</span>
						</a>
					</div>
				</div>
			
				<div class="portlet-body">
					<div class="row">
						<div class="col-md-5">
							<table style="width: 100%;" border="0" padding="0">
								<tr>
									<td width="35%">No. Rek Tabungan</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="savings_account_no" readonly id="savings_account_no" value="<?php echo $acctsavingsaccount['savings_account_no']; ?>" style="width: 100%"/>

										<input type="hidden" name="savings_account_id" readonly id="savings_account_id" value="<?php echo $acctsavingsaccount['savings_account_id']; ?>"/>

									</td>
								</tr>
								<tr>
									<td width="35%">Nama Anggota</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="member_name" readonly id="member_name" value="<?php echo $acctsavingsaccount['member_name']; ?>" style="width: 100%"/>
									</td>
								</tr>
								
								<tr>
									<td width="35%">No. Identitas</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="member_identity_no" readonly id="member_identity_no" value="<?php echo $acctsavingsaccount['member_identity_no']; ?>" style="width: 100%"/>

									</td>
								</tr>
								<tr>
									<td width="35%">Nama Hadiah</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="savings_account_prize_name" id="savings_account_prize_name" type="text" class="easyui-textbox" value="" style="width: 100%">
									</td>
								</tr>
								<tr>
									<td width="35%">Harga Hadiah</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="savings_account_prize_amount_view" id="savings_account_prize_amount_view" type="text" class="easyui-textbox" value="" style="width: 100%">
										<input name="savings_account_prize_amount" id="savings_account_prize_amount" type="hidden" class="easyui-textbox" value="" style="width: 100%">
									</td>
								</tr>
							</table>
							<br>
							<br>
							<div class="row">
								<div class="col-md-12" style='text-align:right'>
									<button type="submit" name="Save" value="Save" id="Save" class="btn green-jungle" title="Simpan Data"><i class="fa fa-check"> Simpan</i></button>
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
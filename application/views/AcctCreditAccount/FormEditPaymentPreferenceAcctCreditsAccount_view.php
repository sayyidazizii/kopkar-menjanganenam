<script src="<?php echo base_url();?>assets/global/scripts/moment.js" type="text/javascript"></script>
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
			<a href="<?php echo base_url();?>credit-account">
				Daftar Pinjaman
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>credit-account/edit-mutation-pref/<?php echo $this->uri->segment(3);?>">
				Edit Preferensi Angsuran Pinjaman
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>
<?php
	echo form_open('credit-account/process-edit-payment-pref'); 
	if(substr($acctcreditsaccount['credits_account_payment_to'], -1) == '*'){
		$angsuranke = $acctcreditsaccount['credits_account_payment_to'];
	}else{
		$angsuranke = substr($acctcreditsaccount['credits_account_payment_to'], -1);
	}
	$member_address = $acctcreditsaccount['member_address']." ".$acctcreditsaccount['kecamatan_name']." ".$acctcreditsaccount['city_name']." ".$acctcreditsaccount['province_name'];
?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			 <div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Form Edit Preferensi Angsuran Pinjaman
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>credit-account" class="btn btn-default btn-sm">
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
									<td width="35%">No. Perjanjian Pinjaman</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="credits_account_serial" readonly id="credits_account_serial" value="<?php echo $acctcreditsaccount['credits_account_serial']; ?>" style="width: 100%"/>

										<input type="hidden" name="credits_account_id" readonly id="credits_account_id" value="<?php echo $acctcreditsaccount['credits_account_id']; ?>"/>

									</td>
								</tr>
								<tr>
									<td width="35%">Nama Anggota</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="member_name" readonly id="member_name" value="<?php echo $acctcreditsaccount['member_name']; ?>" style="width: 100%"/>
									</td>
								</tr>
								
								<tr>
									<td width="35%">No. Identitas</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="member_identity_no" readonly id="member_identity_no" value="<?php echo $acctcreditsaccount['member_identity_no']; ?>" style="width: 100%"/>

									</td>
								</tr>
								<tr>
									<td width="35%">Jenis Pinjaman</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="credits_name" id="credits_name" type="text" class="easyui-textbox" value="<?php echo $acctcreditsaccount['credits_name'];?>" style="width: 100%" readonly>
									</td>
								</tr>
								<tr>
									<td width="35%">Preferensi Angsuran</td>
									<td width="5%"> : </td>
									<td width="60%">
										<?php
										echo form_dropdown('payment_preference_id', $paymentpreference, set_value('payment_preference_id', $acctcreditsaccount['payment_preference_id']), 'id="payment_preference_id" class="easyui-combobox"');
										?>
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
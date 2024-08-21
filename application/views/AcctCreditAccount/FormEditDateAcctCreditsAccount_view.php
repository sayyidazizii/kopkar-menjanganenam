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
	var loop = 1;

	function myformatter(date){
		var y = date.getFullYear();
		var m = date.getMonth()+1;
		var d = date.getDate();
		return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
	}

	function myparser(s){
		if (!s) return new Date();
		var ss = (s.split('-'));
		var y = parseInt(ss[0],10);
		var m = parseInt(ss[1],10);
		var d = parseInt(ss[2],10);
		if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
			return new Date(d,m-1,y);
		} else {
			return new Date();
		}
	}
	
	$(document).ready(function(){	
		$('#credits_account_date').datebox({
			onChange: function(value){
				var name   		= 'credits_account_date';

				var angsuran 	= document.getElementById("credits_payment_period").value;
				var period 		= document.getElementById("credits_account_period").value;
				var date2 		= document.getElementById("credits_account_date").value;
				var day2 		= date2.substring(0, 2);
				var month2 		= date2.substring(3, 5);
				var year2 		= date2.substring(6, 10);
				var date 		= year2 + '-' + month2 + '-' + day2;
				var date1		= new Date(date);

				if(angsuran == 1){
					var a 		= moment(date1); 
					var b 		= a.add(period, 'month'); 

					var testDate = new Date(date);
					var tmp2 = testDate.setMonth(testDate.getMonth() + 1);
					var date_first = testDate.toISOString();
					var day2 = date_first.substring(8, 10);
					var month2 = date_first.substring(5, 7);
					var year2 = date_first.substring(0, 4);
					var first = day2 + '-' + month2 + '-' + year2;
					
					$('#credits_account_due_date').textbox('setValue',b.format('DD-MM-YYYY'));
					$('#credits_account_payment_date').textbox('setValue',first);
				}else{
					var week 		= period * 7;
					var testDate 	= new Date(date1);
					var tmp 		= testDate.setDate(testDate.getDate() + week);
					var date_tmp 	= testDate.toISOString();
					var day 		= date_tmp.substring(8, 10);
					var month 		= date_tmp.substring(5, 7);
					var year 		= date_tmp.substring(0, 4); 
					var name 		= 'credit_account_due_date';
					var value 		= day + '-' + month + '-' + year;
					
					var testDate2 = new Date(date1);
					var tmp2 = testDate2.setDate(testDate2.getDate() + 7);
					var date_first = testDate2.toISOString();
					var day2 = date_first.substring(8, 10);
					var month2 = date_first.substring(5, 7);
					var year2 = date_first.substring(0, 4);
					var first = day2 + '-' + month2 + '-' + year2;

					$('#credits_account_due_date').textbox('setValue',value);
					$('#credits_account_payment_date').textbox('setValue',first);
				}

			}
		});
	
		$('#credits_account_insurance_view').textbox({
			onChange: function(value) {
				if (loop == 0) {
					loop = 1;
					return;
				}
				if (loop == 1) {
					loop = 0;
					$('#credits_account_insurance').textbox('setValue', value);
					$('#credits_account_insurance_view').textbox('setValue', toRp(value));
				} else {
					loop = 1;
					return;
				}
			}
		});
	});

	function toRp(number) {
		var number = number.toString(),
			rupiah = number.split('.')[0],
			cents = (number.split('.')[1] || '') + '00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1,')
			.split('').reverse().join('');
		return rupiah + '.' + cents.slice(0, 2);
	}
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
			<a href="<?php echo base_url();?>credit-account/edit-date/<?php echo $this->uri->segment(3);?>">
				Edit Tanggal Pinjaman
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>
<?php
	echo form_open('credit-account/process-edit-date'); 
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
						Form Edit Tanggal
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
									<td width="35%">Tanggal Realisasi</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input type="text" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser" name="credits_account_date" id="credits_account_date" autocomplete="off" onChange="duedatecalc(this);" value="<?php echo tgltoview($acctcreditsaccount['credits_account_date']); ?>" readonly/>
									</td>
								</tr>
								<tr>
									<td width="35%">Biaya Asuransi</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="credits_account_insurance_view" id="credits_account_insurance_view" value="<?php echo number_format($acctcreditsaccount['credits_account_insurance'], 2);?>" type="text" class="easyui-textbox" style="width: 100%">
										<input name="credits_account_insurance" id="credits_account_insurance" value="<?php echo $acctcreditsaccount['credits_account_insurance'];?>" type="hidden" class="easyui-textbox" style="width: 100%">
										<input name="credits_account_insurance_old" id="credits_account_insurance_old" value="<?php echo $acctcreditsaccount['credits_account_insurance'];?>" type="hidden" class="easyui-textbox" style="width: 100%">
										<input name="credits_account_amount_received" id="credits_account_amount_received" value="<?php echo $acctcreditsaccount['credits_account_amount_received'];?>" type="hidden" class="easyui-textbox" style="width: 100%">
									</td>
								</tr>
								<tr>
									<td width="35%">Jangka Waktu</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="credits_account_period" id="credits_account_period" value="<?php echo $acctcreditsaccount['credits_account_period'];?>" type="text" class="easyui-textbox" style="width: 100%" readonly>
										<input name="credits_payment_period" id="credits_payment_period" value="<?php echo $acctcreditsaccount['credits_payment_period'];?>" type="hidden" class="easyui-textbox" style="width: 100%">
									</td>
								</tr>
								<tr>
									<td width="35%">Tanggal Jatuh Tempo</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="credits_account_due_date" id="credits_account_due_date" value="<?php echo tgltoview($acctcreditsaccount['credits_account_due_date']); ?>" type="text" class="easyui-textbox" style="width: 100%" readonly>
										<input name="credits_account_payment_date" id="credits_account_payment_date" value="<?php echo tgltoview($acctcreditsaccount['credits_account_payment_date']); ?>" type="hidden" class="easyui-textbox" style="width: 100%" readonly>
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
						<div class="col-md-5">
						<style>
	th, td {
	  padding: 3px;
	  font-size: 13px;
	}
	input:focus { 
	  background-color: 42f483;
	}
	.custom{

		margin: 0px; padding-top: 0px; padding-bottom: 0px; height: 50px; line-height: 50px; width: 50px;

	}
	.textbox .textbox-text{
		font-size: 13px;


	}
	input:read-only {
		background-color: f0f8ff;
	}
</style>
<script>
	base_url = '<?= base_url()?>';
	mappia = "	<?php 
					$id = $this->uri->segment(3);
					$site_url = 'credit-account/edit/'.$id;
					echo site_url($site_url); 
				?>";

	function toRp(number) {
		var number = number.toString(), 
		rupiah = number.split('.')[0], 
		cents = (number.split('.')[1] || '') +'00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1,')
			.split('').reverse().join('');
		return rupiah + '.' + cents.slice(0, 2);
	}

	$(document).on('change','#bpkb_taksiran_view',function(event){
		bpkb_taksiran_view				= $('#bpkb_taksiran_view')[0].value;	
		
		document.getElementById('bpkb_taksiran_view').value	= toRp(bpkb_taksiran_view);
		document.getElementById('bpkb_taksiran').value		= bpkb_taksiran_view;
		
	});

	$(document).on('change','#bpkb_gross_view',function(event){
		bpkb_gross_view				= $('#bpkb_gross_view')[0].value;	
		
		document.getElementById('bpkb_gross_view').value	= toRp(bpkb_gross_view);
		document.getElementById('bpkb_gross').value			= bpkb_gross_view;
		
	});

	$(document).on('change','#shm_taksiran_view',function(event){
		shm_taksiran_view				= $('#shm_taksiran_view')[0].value;	
		
		document.getElementById('shm_taksiran_view').value	= toRp(shm_taksiran_view);
		document.getElementById('shm_taksiran').value		= shm_taksiran_view;
		
	});

	function formupdate(data){
		if(data.value != ''){
				if(data.value == "Penerimaan"){
				document.getElementById("penerimaan").style.display 	= "block";
				document.getElementById("deposito").style.display 		= "none";
				document.getElementById("other").style.display 			= "none";
			}else if(data.value == "Deposito"){
				document.getElementById("penerimaan").style.display 	= "none";
				document.getElementById("deposito").style.display 		= "block";
				document.getElementById("other").style.display 			= "none";
			}else{
				document.getElementById("penerimaan").style.display 	= "none";
				document.getElementById("deposito").style.display 		= "none";
				document.getElementById("other").style.display 			= "block";
			}
		}
	}

	function processAddArrayAgunan(){
		var tipe					= document.getElementById("tipe_agunan").value;
		var penerimaan_description	= document.getElementById("penerimaan_description").value;
		var deposito_account_no		= document.getElementById("deposito_account_no").value;
		var other_description 		= document.getElementById("other_description").value;

			$('#offspinwarehouse').css('display', 'none');
			$('#onspinspinwarehouse').css('display', 'table-row');
			  $.ajax({
			  type: "POST",
			  url : "<?php echo site_url('credit-account/process-add-array-agunan');?>",
			  data: {
					'tipe' 						: tipe,	
					'penerimaan_description' 	: penerimaan_description,
					'deposito_account_no' 		: deposito_account_no,
					'other_description'			: other_description,
					'session_name' 				: "addarrayacctcreditsagunan-"
				},
			  success: function(msg){
			   window.location.replace(mappia);
			 }
			});
	}
</script>

		<!-- <?php echo form_open('credit-account/process-add-array-agunan',array('id' => 'myform', 'class' => 'horizontal-form')); ?> -->
		<div class="form-body">
			<table style="width: 100%;" border="0" padding:"0">
				<tbody  id="tipe" style="display:block" >
					<tr>
						<td>Pilih Tipe</td>
						<td> : </td>
						<td> <select name="tipe" id="tipe_agunan" class="form-control" onchange="formupdate(this)">
							<option value="">Select</option>
							<option value="Penerimaan">Penerimaan Anggota Dari Perusahaan</option>
							<option value="Deposito">Deposito</option>
							<option value="Lain-Lain">Lain-Lain</option>
							</select>
						</td>
					</tr>
				</tbody>
				<tbody  id="penerimaan" style="display:none">
					<tr>
						<td>Keterangan</td>
						<td> : </td>
						<td><input type="text" class="form-control" name="penerimaan_description" id="penerimaan_description" autocomplete="off" />
						</td>
					</tr>
				</tbody>
				<tbody  id="deposito" style="display:none">
					<tr>
						<td>No Deposito</td>
						<td> : </td>
						<td><input type="text" class="form-control" name="deposito_account_no" id="deposito_account_no" autocomplete="off" />
						</td>
					</tr>
				</tbody>
				<tbody  id="other" style="display:none">
					<tr>
						<td>Keterangan</td>
						<td> : </td>
						<td><input type="text" class="form-control" name="other_description" id="other_description" autocomplete="off" />
						</td>
					</tr>
				</tbody>
			</table>
		<div class="row">
			<div class="col-md-12" style='text-align:left'>
				<input type="button" name="add2" id="buttonAddArrayInvtGoodsReceivedNote" value="Add" class="btn green-jungle" title="Simpan Data" onClick="processAddArrayAgunan();">
			</div>	
		</div>
		<!-- <?php echo form_close(); ?> -->

		<?php 
			$sesi = $this->session->userdata('unique');
			$daftaragunan = $this->session->userdata('addarrayacctcreditsagunan-'.$sesi['unique']);
		?>

		<table class="table table-striped table-hover">
			<tr>
				<th style="text-align:center">No</th>
				<th style="text-align:center">Type</th>
				<th style="text-align:center">Keterangan</th>
			</tr>
			<?php 
				$no = 1;
				if(empty($daftaragunan)){
					echo "
						<tr>
							<td align=\"center\" colspan=\"3\">Tidak Ada Agunan</td>
						</tr>
					";
				} else {
					foreach ($daftaragunan as $key => $val) {
						if($val['credits_agunan_type'] == "Penerimaan"){
							echo "
								<tr>
									<td>$no</td>
									<td>".$val['credits_agunan_type']."</td>
									<td>Keterangan : ".$val['credits_agunan_penerimaan_description']."</td>
								</tr>
							";
						} else if($val['credits_agunan_type'] == "Deposito"){
							echo "
								<tr>
									<td>$no</td>
									<td>".$val['credits_agunan_type']."</td>
									<td>No Deposito : ".$val['credits_agunan_deposito_account_no']."</td>
								</tr>
							";
						}else{
							echo "
								<tr>
									<td>$no</td>
									<td>".$val['credits_agunan_type']."</td>
									<td>Keterangan : ".$val['credits_agunan_other_description']."</td>
								</tr>
							";
						}
						$no++;
					}
				}
			?>
		</table>


						</div>	
					</div>
				 </div>
			</div>
		</div>
	</div>
</div>

<?php echo form_close(); ?>
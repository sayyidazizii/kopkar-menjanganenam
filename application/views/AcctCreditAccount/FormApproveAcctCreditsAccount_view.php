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
<script src="<?php echo base_url();?>assets/global/scripts/moment.js" type="text/javascript"></script>
<script type="text/javascript">
	var loop_principal 	= 1;
	var loop_margin 	= 1;

	function myformatter(date){
		var y = date.getFullYear();
		var m = date.getMonth()+1;
		var d = date.getDate();
		return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
	}

	function change_method(name, value) {
		if(value == 2){
			document.getElementById("bank_container").style.display = "contents";
		}else{
			document.getElementById("bank_container").style.display = "none";
		}
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

	function toRp(number) {
		var number = number.toString(),
			rupiah = number.split('.')[0],
			cents = (number.split('.')[1] || '') + '00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1,')
			.split('').reverse().join('');
		return rupiah + '.' + cents.slice(0, 2);
	}

	function angsuranflat() {
		var bunga = document.getElementById("credits_account_interest").value;
		var jangka = document.getElementById("credits_account_period").value;
		var pembiayaan = document.getElementById("credits_account_amount").value;

		var persbunga = parseFloat(bunga) / 100;

		if (pembiayaan == '') {
			var totalangsuran = 0;
			var angsuranpokok = 0;
			var angsuranbunga2 = 0;
		} else {
			var angsuranpokok 	= Math.ceil(pembiayaan / jangka);
			var angsuranbunga 	= Math.floor((pembiayaan * bunga) / 100);
			var totalangsuran 	= angsuranpokok + angsuranbunga;
			var angsuranbunga2 	= totalangsuran - angsuranpokok;
		}

		$('#credits_account_payment_amount').textbox('setValue', totalangsuran);
		$('#credits_account_principal_amount').textbox('setValue', angsuranpokok);
		$('#credits_account_interest_amount').textbox('setValue', angsuranbunga2);

		$('#credits_account_payment_amount_view').textbox('setValue', toRp(totalangsuran));
		$('#credits_account_principal_amount_view').textbox('setValue', toRp(angsuranpokok));
		$('#credits_account_interest_amount_view').textbox('setValue', toRp(angsuranbunga2));

	}

	function rate3(nprest, vlrparc, vp) {
		var guess = 0.25;
		var maxit = 100;
		var precision = 14;
		var guess = Math.round(guess, precision);
		for (var i = 0; i < maxit; i++) {
			var divdnd = vlrparc - (vlrparc * (Math.pow(1 + guess, -nprest))) - (vp * guess);
			var divisor = nprest * vlrparc * Math.pow(1 + guess, (-nprest - 1)) - vp;
			var newguess = guess - (divdnd / divisor);
			var newguess = Math.round(newguess, precision);
			if (newguess == guess) {
				return newguess;
			} else {
				guess = newguess;
			}
		}
		return null;
	}

	function angsurananuitas() {
		var bunga 		= document.getElementById("credits_account_interest").value;
		var jangka 		= document.getElementById("credits_account_period").value;
		var pembiayaan 	= document.getElementById("credits_account_amount").value;
		var persbunga 	= bunga / 100;

		if (pembiayaan == '') {
			var totalangsuran = 0;
			var angsuranpokok = 0;
			var angsuranbunga2 = 0;
		} else {
			if (bunga == 0) {
				var totalangsuran = 0;
				var angsuranpokok = 0;
				var angsuranbunga2 = 0;
			} else {
				var bungaA = Math.pow((1 + parseFloat(persbunga)), jangka);
				var bungaB = Math.pow((1 + parseFloat(persbunga)), jangka) - 1;
				var bungaC = bungaA / bungaB;
				var totalangsuran = pembiayaan * persbunga * bungaC;
				var angsuranbunga2 = (pembiayaan * bunga) / 100;
				var angsuranpokok = totalangsuran - angsuranbunga2;
				var totangsuran = Math.round((pembiayaan * (persbunga)) + pembiayaan / jangka);

				$.ajax({
					type: "POST",
					url: "<?php echo site_url('credit-account/rate4'); ?>",
					data: {
						'nprest': jangka,
						'vlrparc': totangsuran,
						'vp': pembiayaan
					},
					success: function(rate) {
						console.log(rate);
						var angsuranbunga2 = pembiayaan * rate;
						var angsuranpokok = totangsuran - angsuranbunga2;
						var totalangsuran = angsuranbunga2 + angsuranpokok;

						$('#credits_account_payment_amount').textbox('setValue', totalangsuran);
						$('#credits_account_principal_amount').textbox('setValue', angsuranpokok);
						$('#credits_account_interest_amount').textbox('setValue', angsuranbunga2);

						$('#credits_account_payment_amount_view').textbox('setValue', toRp(totalangsuran));
						$('#credits_account_principal_amount_view').textbox('setValue', toRp(angsuranpokok));
						$('#credits_account_interest_amount_view').textbox('setValue', toRp(angsuranbunga2));
					}
				});
			}
		}
	}

	function receivedamount() {
		var pinjaman 	= document.getElementById("credits_account_amount").value;
		var insurance 	= document.getElementById("credits_account_insurance").value;
		var adm		 	= document.getElementById("credits_account_adm_cost").value;

		if (insurance == '') {
			insurance = 0;
		}
		if (adm == '') {
			adm = 0;
		}

		var terima_bersih = parseFloat(pinjaman) - parseFloat(insurance) - parseFloat(adm);

		$('#credits_account_amount_received').textbox('setValue', terima_bersih);
		$('#credits_account_amount_received_view').textbox('setValue', toRp(terima_bersih));

		var name = 'credits_account_amount_received';
		var name2 = 'credits_account_amount_received_view';
	}
	
	$(document).ready(function(){
		var method_id 	= document.getElementById("method_id").value;

		if(method_id == 2){
			document.getElementById("bank_container").style.display = "contents";
		}else{
			document.getElementById("bank_container").style.display = "none";
		}

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
					var name 		= 'credits_account_due_date';
					var value 		= day + '-' + month + '-' + year;
					
					var testDate2 	= new Date(date1);
					var tmp2 		= testDate2.setDate(testDate2.getDate() + 7);
					var date_first 	= testDate2.toISOString();
					var day2 		= date_first.substring(8, 10);
					var month2 		= date_first.substring(5, 7);
					var year2 		= date_first.substring(0, 4);
					var first 		= day2 + '-' + month2 + '-' + year2;

					$('#credits_account_due_date').textbox('setValue',value);
					$('#credits_account_payment_date').textbox('setValue',first);
				}
			}
		});

		$('#credits_account_amount_view').textbox({
			onChange: function(value){
				var name 			= 'credits_account_amount';
				var name2 			= 'credits_account_amount_view';
				var payment_type_id = +document.getElementById("payment_type_id").value;
				var credits_id 		= document.getElementById("credits_id").value;

				if (loop_principal == 0) {
					loop_principal = 1;
					return;
				}
				if (loop_principal == 1) {
					loop_principal = 0;
					var tampil = toRp(value);
					$('#credits_account_amount').textbox('setValue', value);
					$('#credits_account_amount_view').textbox('setValue', tampil);

					if (payment_type_id == 1) {
						angsuranflat();
					} else if (payment_type_id == 2) {
						angsurananuitas();
					} else if (payment_type_id == 3) {
						angsuranflat();
					} else if (payment_type_id == 4) {
						angsuranflat();
					}
					receivedamount();
				} else {
					loop_principal = 1;
					return;
				}
			}
		});

		$('#credits_account_interest_view').textbox({
			onChange: function(value) {
				var name = 'credits_account_interest';
				var name2 = 'credits_account_interest_view';
				var payment_type_id = +document.getElementById("payment_type_id").value;

				if (loop_margin == 0) {
					loop_margin = 1;
					return;
				}

				if (loop_margin == 1) {
					loop_margin = 0;
					var tampil = value;
					$('#credits_account_interest').textbox('setValue', value);
					$('#credits_account_interest_view').textbox('setValue', tampil);

					if (payment_type_id == 1) {
						angsuranflat();
					} else if (payment_type_id == 2) {
						angsurananuitas();
					} else if (payment_type_id == 3) {
						angsuranflat();
					} else if (payment_type_id == 4) {
						angsuranflat();
					}
				} else {
					loop_margin = 1;
					return;
				}
			}
		});
	});
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
			<a href="<?php echo base_url();?>credit-account/detail">
				Daftar Pinjaman
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>credit-account/approving/<?php echo $this->uri->segment(3);?>">
				Persetujuan Pinjaman
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>
<?php
	echo form_open('credit-account/process-approve',array('id' => 'myform', 'class' => 'horizontal-form'));
	$sesi 	= $this->session->userdata('unique');

	$token 	= $this->session->userdata('acctcreditsaccounttoken-'.$sesi['unique']);

	$member_address = $acctcreditsaccount['member_address']." ".$acctcreditsaccount['kecamatan_name']." ".$acctcreditsaccount['city_name']." ".$acctcreditsaccount['province_name'];
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');
?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			 <div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Form Persetujuan
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
									<td width="35%">No. Perjanjian Kredit</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="credits_account_serial" readonly id="credits_account_serial" value="<?php echo $acctcreditsaccount['credits_account_serial']; ?>" style="width: 100%"/>

										<input type="hidden" name="credits_account_id" readonly id="credits_account_id" value="<?php echo $acctcreditsaccount['credits_account_id']; ?>"/>

										<input type="hidden" name="credits_id" readonly id="credits_id" value="<?php echo $acctcreditsaccount['credits_id']; ?>"/>
										
										<input type="hidden" class="easyui-textbox" name="credits_account_token" id="credits_account_token" autocomplete="off" value="<?php echo $token;?>"/>
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
									<td width="35%">Alamat Anggota</td>
									<td width="5%"> : </td>
									<td width="60%">
										<textarea class="easyui-textarea" row="3" name="member_address" id="member_address" style="width: 100%" readonly><?php echo $member_address; ?></textarea>
									</td>
								</tr>
								
								<tr>
									<td width="35%">No. Identitas</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="member_identity_no" readonly id="member_identity_no" value="<?php echo $acctcreditsaccount['member_identity_no']; ?>" style="width: 100%"/>
										<input class="easyui-textbox" type="hidden" name="member_id" readonly id="member_id" value="<?php echo $acctcreditsaccount['member_id']; ?>" style="width: 100%"/>
										<input class="easyui-textbox" type="hidden" name="member_mandatory_savings_last_balance" readonly id="member_mandatory_savings_last_balance" value="<?php echo $acctcreditsaccount['member_mandatory_savings_last_balance']; ?>" style="width: 100%"/>
										<input class="easyui-textbox" type="hidden" name="member_special_savings_last_balance" readonly id="member_special_savings_last_balance" value="<?php echo $acctcreditsaccount['member_special_savings_last_balance']; ?>" style="width: 100%"/>
									</td>
								</tr>
								<tr>
									<td width="35%">Jenis Pinjaman</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="credits_name" id="credits_name" type="text" class="easyui-textbox" value="<?php echo $acctcreditsaccount['credits_name'];?>" style="width: 100%" readonly>
									</td>
								</tr>
								<?php 
								if($acctcreditsaccount['credits_id'] == 999){
								?>
								<tr>
									<td width="35%">Toko</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="store_name" id="store_name" type="text" class="easyui-textbox" value="<?php echo $acctcreditsaccount['store_name'];?>" style="width: 100%" readonly>
									</td>
								</tr>
								<?php
								}
								?>
								<tr>
									<td width="35%">Tanggal Realisasi</td>
									<td width="5%"> : </td>
									<td width="60%">
										<!-- <input name="credits_account_date" id="credits_account_date" value="<?php echo tgltoview($acctcreditsaccount['credits_account_date']); ?>" type="text" class="easyui-textbox" style="width: 100%"> -->

										<input type="text" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser" name="credits_account_date" id="credits_account_date" autocomplete="off" onChange="duedatecalc(this);" value="<?php echo tgltoview($acctcreditsaccount['credits_account_date']); ?>" />
									</td>
								</tr>
								<tr>
									<td width="35%">Jangka Waktu</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="credits_account_period" id="credits_account_period" value="<?php echo $acctcreditsaccount['credits_account_period'];?>" type="text" class="easyui-textbox" style="width: 100%" readonly>
										<input name="credits_payment_period" id="credits_payment_period" value="<?php echo $acctcreditsaccount['credits_payment_period'];?>" type="hidden" class="easyui-textbox" style="width: 100%" readonly>
									</td>
								</tr>
								<tr>
									<td width="35%">Tanggal Jatuh Tempo</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="credits_account_payment_date" id="credits_account_payment_date" value="<?php echo tgltoview($acctcreditsaccount['credits_account_payment_date']); ?>" type="hidden" class="easyui-textbox" style="width: 100%" readonly>
										<input name="credits_account_due_date" id="credits_account_due_date" value="<?php echo tgltoview($acctcreditsaccount['credits_account_due_date']); ?>" type="text" class="easyui-textbox" style="width: 100%" readonly>
									</td>
								</tr>
							</table>
						</div>
						<div class="col-md-1"></div>
						<div class="col-md-5">
							<table style="width: 100%;" border="0" padding="0">
								<tr>
									<td width="35%">Metode <span class="required" style="color : red">*</span></td>
									<td width="5%"> : </td>
									<td width="60%">
										<?php echo form_dropdown('method_id', $methods, set_value('method_id', $acctcreditsaccount['method_id']), 'id="method_id" class="form-control select2me" onChange="change_method(this.name, this.value);" '); ?>
									</td>
								</tr>
								<tr id="bank_container" name="bank_container" style="display:none;">
									<td width="35%">Bank <span class="required" style="color : red">*</span></td>
									<td width="5%"> : </td>
									<td width="60%">
										<?php echo form_dropdown('bank_account_id', $acctbankaccount, set_value('bank_account_id', $acctcreditsaccount['bank_account_id']), 'id="bank_account_id" class="form-control select2me" readonly'); ?>
									</td>
								</tr>
								<tr>
									<td width="35%">Jumlah Pinjaman</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input type="text" class="easyui-textbox" name="credits_account_amount_view" id="credits_account_amount_view" autocomplete="off" value="<?php echo number_format($acctcreditsaccount['credits_account_amount'], 2); ?>" style="width: 100%" />
										<input type="hidden" class="easyui-textbox" name="credits_account_amount" id="credits_account_amount" autocomplete="off" value="<?php echo $acctcreditsaccount['credits_account_amount']; ?>" />
									</td>
								</tr>
								<tr>
									<td width="35%">Prosentase Bunga </td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="credits_account_interest_view" id="credits_account_interest_view" value="<?php echo number_format($acctcreditsaccount['credits_account_interest'], 2); ?>" style="width: 100%" />
										<input class="easyui-textbox" type="hidden" name="credits_account_interest" id="credits_account_interest" value="<?php echo $acctcreditsaccount['credits_account_interest']; ?>" style="width: 100%"/>
									</td>
								</tr>
								<tr>
									<td width="35%">Jenis Angsuran</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input name="payment_type_name" id="payment_type_name" type="text" class="easyui-textbox" value="<?php echo $paymenttype[$acctcreditsaccount['payment_type_id']];?>" style="width: 100%" readonly>
										<input name="payment_type_id" id="payment_type_id" type="hidden" class="easyui-textbox" value="<?php echo $acctcreditsaccount['payment_type_id'];?>" style="width: 100%" readonly>
										<input name="credits_id" id="credits_id" type="hidden" class="easyui-textbox" value="<?php echo $acctcreditsaccount['credits_id'];?>" style="width: 100%" readonly>
									</td>
								</tr>
								<tr>
									<td width="35%">Angsuran Pokok</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="credits_account_principal_amount_view" readonly id="credits_account_principal_amount_view" value="<?php echo number_format($acctcreditsaccount['credits_account_principal_amount'], 2); ?>" style="width: 100%"/>
										<input class="easyui-textbox" type="hidden" name="credits_account_principal_amount" readonly id="credits_account_principal_amount" value="<?php echo $acctcreditsaccount['credits_account_principal_amount']; ?>" style="width: 100%"/>
									</td>
								</tr>
								<tr>
									<td width="35%">Angsuran Bunga</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="credits_account_interest_amount_view" readonly id="credits_account_interest_amount_view" value="<?php echo number_format($acctcreditsaccount['credits_account_interest_amount'], 2); ?>" style="width: 100%"/>
										<input class="easyui-textbox" type="hidden" name="credits_account_interest_amount" readonly id="credits_account_interest_amount" value="<?php echo $acctcreditsaccount['credits_account_interest_amount']; ?>" style="width: 100%"/>
									</td>
								</tr>
								<tr>
									<td width="35%">Jumlah Angsuran</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="credits_account_payment_amount_view" readonly id="credits_account_payment_amount_view" value="<?php echo number_format($acctcreditsaccount['credits_account_payment_amount'], 2); ?>" style="width: 100%"/>
										<input class="easyui-textbox" type="hidden" name="credits_account_payment_amount" readonly id="credits_account_payment_amount" value="<?php echo $acctcreditsaccount['credits_account_payment_amount']; ?>" style="width: 100%"/>
									</td>
								</tr>
								<tr>
									<td>
										<input type="hidden" class="easyui-textbox" name="credits_account_adm_cost" id="credits_account_adm_cost" autocomplete="off" value="<?php echo $acctcreditsaccount['credits_account_adm_cost']; ?>"/>
										
										<input type="hidden" class="easyui-textbox" name="credits_account_provisi" id="credits_account_provisi" autocomplete="off" value="<?php echo $acctcreditsaccount['credits_account_provisi']; ?>"/>
										
										<input type="hidden" class="easyui-textbox" name="credits_account_komisi" id="credits_account_komisi" autocomplete="off" value="<?php echo $acctcreditsaccount['credits_account_komisi']; ?>"/>

										<input type="hidden" class="easyui-textbox" name="credits_account_insurance" id="credits_account_insurance" autocomplete="off" value="<?php echo set_value('credits_account_insurance',$acctcreditsaccount['credits_account_insurance']);?>"/>
										
										<input type="hidden" class="easyui-textbox" name="credits_account_discount" id="credits_account_discount" autocomplete="off" value="<?php echo set_value('credits_account_discount',$acctcreditsaccount['credits_account_discount']);?>"/>

										<input type="hidden" class="easyui-textbox" name="credits_account_materai" id="credits_account_materai" autocomplete="off" value="<?php echo set_value('credits_account_materai',$acctcreditsaccount['credits_account_materai']);?>"/>

										<input type="hidden" class="easyui-textbox" name="credits_account_risk_reserve" id="credits_account_risk_reserve" autocomplete="off" value="<?php echo set_value('credits_account_risk_reserve',$acctcreditsaccount['credits_account_risk_reserve']);?>"/>

										<input type="hidden" class="easyui-textbox" name="credits_account_stash" id="credits_account_stash" autocomplete="off" value="<?php echo set_value('credits_account_stash',$acctcreditsaccount['credits_account_stash']);?>"/>

										<input type="hidden" class="easyui-textbox" name="credits_account_special" id="credits_account_special" autocomplete="off" value="<?php echo set_value('credits_account_special',$acctcreditsaccount['credits_account_special']);?>"/>
									</td>
								</tr>
								<tr>
									<td>										
										<input type="hidden" class="easyui-textbox" name="credits_account_notaris" id="credits_account_notaris" autocomplete="off" value="<?php echo set_value('credits_account_notaris',$acctcreditsaccount['credits_account_notaris']);?>"/>
									</td>
								</tr>
								<tr>
									<td>										
										<input type="hidden" class="easyui-textbox" name="credits_account_amount_received" id="credits_account_amount_received" autocomplete="off" value="<?php echo set_value('credits_account_amount_received',$acctcreditsaccount['credits_account_amount_received']);?>"/>
									</td>
								
									<input type="hidden" class="easyui-textbox" name="credits_account_notaris" id="credits_account_notaris" autocomplete="off" value="<?php echo set_value('credits_account_notaris',$acctcreditsaccount['credits_account_notaris']);?>"/>
								</tr>
								<tr>
									<td width="35%"></td>
									<td width="5%"></td>
									<td width="60%">
									<div class="row">
										<div class="col-md-12 " style="text-align  : right !important;">
											<input type="submit" name="Simpan" id="Simpan" value="Simpan" class="btn blue" title="Simpan">
										</div>
									</div>
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
<?php echo form_close(); ?>
<style>
	th, td {
	  padding: 3px;
	}
	input:focus { 
	  background-color: 42f483;
	}
</style>

<script>
	var loop = 1;
	base_url = '<?php echo base_url();?>';

	$(document).ready(function(){
		$('#mutation_id').textbox({
		   collapsible:false,
		   minimizable:false,
		   maximizable:false,
		   closable:false
		});

		$('#mutation_id').textbox('clear').textbox('textbox').focus();
	});

	function function_elements_add(name, value){
		$.ajax({
				type: "POST",
				url : "<?php echo site_url('savings-bank-mutation/elements-add');?>",
				data : {'name' : name, 'value' : value},
				success: function(msg){
			}
		});
	}

	function reset_data(){
		document.location = base_url+"savings-bank-mutation/reset-data";
	}

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
		 $('#mutation_id').combobox({
			  onChange: function(value){
			  	var mutation_id 	= +document.getElementById("mutation_id").value;

			  

				$.post(base_url + 'savings-bank-mutation/get-mutation',
				{mutation_id: mutation_id},
					function(data){	
					var obj = $.parseJSON(data)		   
						$('#mutation_function').textbox('setValue',obj);
					},
				)
			  }
			})
	});

	function calculateSavings(){
		var savings_bank_mutation_amount					= $('#savings_bank_mutation_amount').val();
		var savings_bank_mutation_opening_balance			= $('#savings_bank_mutation_opening_balance').val();	
		var mutation_function								= $('#mutation_function').val();	
		var adm_method										= $('#adm_method').val();	
		var savings_bank_mutation_amount_adm				= $('#savings_bank_mutation_amount_adm').val();	

		var savings_bank_mutation_last_balance;

		if(mutation_function == '+'){
			savings_bank_mutation_last_balance = parseFloat(savings_bank_mutation_opening_balance) + parseFloat(savings_bank_mutation_amount);
		} else if(mutation_function == '-'){
			savings_bank_mutation_last_balance = parseFloat(savings_bank_mutation_opening_balance) - parseFloat(savings_bank_mutation_amount);
		} else {
			alert("Sandi masih kosong");
				return false;
		}

		if(adm_method == 3){
			savings_bank_mutation_last_balance -= savings_bank_mutation_amount_adm;
		}
		
		$('#savings_bank_mutation_last_balance_view').textbox('setValue', toRp(savings_bank_mutation_last_balance));
		$('#savings_bank_mutation_last_balance').textbox('setValue', savings_bank_mutation_last_balance);
	}

	$(document).ready(function(){
		$('#savings_bank_mutation_amount_view').textbox({
			onChange: function(value){
				if(loop == 0){
					loop = 1;
					return;
				}
				if(loop == 1){
					loop = 0;
					var tampil = toRp(value);
					$('#savings_bank_mutation_amount').textbox('setValue', value);
					$('#savings_bank_mutation_amount_view').textbox('setValue', tampil);

					calculateSavings();
				} else {
					loop = 1;
					return;
				}
			}
		});
	});

	$(document).ready(function(){
		$('#savings_bank_mutation_amount_adm_view').textbox({
			onChange: function(value){
				var name   	= 'savings_bank_mutation_amount_adm';
				var name2   = 'savings_bank_mutation_amount_adm_view';

				if(loop == 0){
					loop= 1;
					return;
				}
				if(loop ==1){
					loop =0;
					var tampil = toRp(value);
				$('#savings_bank_mutation_amount_adm').textbox('setValue', value);
				$('#savings_bank_mutation_amount_adm_view').textbox('setValue', tampil);
				
				function_elements_add(name, value);
				function_elements_add(name2, tampil);
				}else{
					loop=1;
					return;
				}
			}
		});

		$('#adm_method').combobox({
			onChange: function(value){
				calculateSavings();
			}
		});
	});
</script>
<?php echo form_open('savings-bank-mutation/process-add',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
	$sesi 	= $this->session->userdata('unique');
	$data 	= $this->session->userdata('acctsavingsbankmutation-'.$sesi['unique']);
	$token 	= $this->session->userdata('acctsavingsbankmutationtoken-'.$sesi['unique']);
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
			<a href="<?php echo base_url();?>savings-bank-mutation">
				Daftar Tabungan Bank
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>savings-bank-mutation/add">
				Tambah Tabungan Bank
			</a>
		</li>
	</ul>
</div>
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
						Form Tambah
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>savings-bank-mutation" class="btn btn-default btn-sm">
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
									<tr>
										<td width="35%">No. Rekening <span class="required" style="color : red">*</span></td>
										<td width="5%"> : </td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="savings_account_no" id="savings_account_no" value="<?php echo $acctsavingsaccount['savings_account_no'];?>" style="width:60%" readonly/> <a href="#" role="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#memberlist">Cari No Rek</a>
											<input type="hidden" class="easyui-textbox" name="savings_account_id" id="savings_account_id" value="<?php echo $acctsavingsaccount['savings_account_id'];?>"/>
										</td>
									</tr>
									<tr>
										<td width="35%">Jenis Tabungan</td>
										<td width="5%"> : </td>
										<td width="60%"><input type="text" class="easyui-textbox" name="savings_name" id="savings_name" value="<?php echo $acctsavingsaccount['savings_name'];?>" style="width:100%" readonly/></td>
									</tr>
									<tr>
										<td width="35%">Nama Anggota</td>
										<td width="5%"> : </td>
										<td width="60%"><input type="text" class="easyui-textbox" name="member_name" id="member_name" value="<?php echo $acctsavingsaccount['member_name'];?>" style="width:100%" readonly/></td>
									</tr>
									<tr>
										<td width="35%">Alamat</td>
										<td width="5%"> : </td>
										<td width="60%"><textarea rows="2" name="member_address" id="member_address" class="easyui-textarea" style="width:100%"  readonly><?php echo $acctsavingsaccount['member_address'];?></textarea></td>
									</tr>
									<tr>
										<td width="35%">Kabupaten</td>
										<td width="5%"> : </td>
										<td width="60%"><input type="text" class="easyui-textbox" style="width: 100%" name="city_name" id="city_name" value="<?php echo $acctsavingsaccount['city_name'];?>" readonly/></td>
									</tr>
									<tr>
										<td width="35%">Kecamatan</td>
										<td width="5%"> : </td>
										<td width="60%"><input type="text" class="easyui-textbox" style="width: 100%" name="kecamatan_name" id="kecamatan_name" value="<?php echo $acctsavingsaccount['kecamatan_name'];?>" readonly/></td>
									</tr>
									<tr>
										<td width="35%">Identitas</td>
										<td width="5%"> : </td>
										<td width="60%"><input type="text" class="easyui-textbox" name="member_identity" id="member_identity" value="<?php if($acctsavingsaccount['member_identity'] != ''){ echo $memberidentity[$acctsavingsaccount['member_identity']];} else { echo '';}?>" style="width: 100%" readonly/></td>
									</tr>
									<tr>
										<td width="35%">No. Identitas</td>
										<td width="5%"> : </td>
										<td width="60%"><input type="text" class="easyui-textbox" style="width: 100%" name="member_identity_no" id="member_identity_no" value="<?php echo $acctsavingsaccount['member_identity_no'];?>" readonly/></td>
									</tr>
								</table>
							</div>
							<div class="col-md-1"></div>
							<div class="col-md-5">
								<table width="100%">
									<tr>
										<td width="35%">Bank Transfer <span class="required" style="color : red">*</span></td>
										<td width="5%"> : </td>
										<td width="60%"><?php echo form_dropdown('bank_account_id', $acctbankaccount, set_value('bank_account_id',$data['bank_account_id']),'id="bank_account_id" class="easyui-combobox" style="width:100%"');?></td>
									</tr>
									<tr>
										<td width="35%">Sandi <span class="required" style="color : red">*</span></td>
										<td width="5%"> : </td>
										<td width="60%"><?php echo form_dropdown('mutation_id', $acctmutation, set_value('mutation_id',$data['mutation_id']),'id="mutation_id" class="easyui-combobox" style="width:100%" ');?></td>
									</tr>

									<input type="hidden" class="easyui-textbox" name="mutation_function" id="mutation_function" autocomplete="off" readonly/>

									<input type="hidden" class="easyui-textbox" name="member_id" id="member_id" value="<?php echo $acctsavingsaccount['member_id'];?>" readonly/>
									<input type="hidden" class="easyui-textbox" name="savings_id" id="savings_id" value="<?php echo $acctsavingsaccount['savings_id'];?>" readonly/>
									<tr>
										<td width="35%">Saldo Lama</td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" style="width: 100%" name="savings_account_last_balance" id="savings_account_last_balance" value="<?php echo number_format($acctsavingsaccount['savings_account_last_balance'], 2);?>" readonly/>
											<input type="hidden" class="easyui-textbox" name="savings_bank_mutation_opening_balance" id="savings_bank_mutation_opening_balance" value="<?php echo $acctsavingsaccount['savings_account_last_balance'];?>" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Jumlah Transaksi (Rp) <span class="required" style="color : red">*</span></td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" style="width: 100%" name="savings_bank_mutation_amount_view" id="savings_bank_mutation_amount_view" autocomplete="off" value="<?php echo set_value('savings_bank_mutation_amount',$data['savings_bank_mutation_amount']);?>"/>
											<input type="hidden" class="easyui-textbox" name="savings_bank_mutation_amount" id="savings_bank_mutation_amount" autocomplete="off" value="<?php echo set_value('savings_bank_mutation_amount',$data['savings_bank_mutation_amount']);?>"/>
										</td>
									</tr>
									<tr>
										<td width="35%">Biaya Adm (Rp)<span class="required">*</span></td>
										<td width="5%">:</td>
										<td width="60%"><input type="text" class="easyui-textbox" name="savings_bank_mutation_amount_adm_view" id="savings_bank_mutation_amount_adm_view" autocomplete="off" style="width: 100%" value="<?php echo set_value('savings_bank_mutation_amount_adm_view',$data['savings_bank_mutation_amount_adm_view']);?>" /><input type="hidden" class="easyui-textbox" name="savings_bank_mutation_amount_adm" id="savings_bank_mutation_amount_adm" autocomplete="off" value="<?php echo set_value('savings_bank_mutation_amount_adm',$data['savings_bank_mutation_amount_adm']);?>"/></td>
									</tr>
									<tr>
										<td width="35%">Metode Adm</td>
										<td width="5%"> : </td>
										<td width="60%"><?php echo form_dropdown('adm_method', $admmethod, set_value('adm_method',$data['adm_method']),'id="adm_method" class="easyui-combobox" style="width:100%" ');?></td>
									</tr>
									<tr>
										<td width="35%">Tanggal Transaksi</td>
										<td width="5%">:</td>
										<td width="60%"><input type="text" class="easyui-textbox" style="width: 100%" name="savings_bank_mutation_date" id="savings_bank_mutation_date" autocomplete="off" value="<?php echo date('d-m-Y'); ?>" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Saldo Baru</td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" style="width: 100%" name="savings_bank_mutation_last_balance_view" id="savings_bank_mutation_last_balance_view" autocomplete="off" readonly />
											<input type="hidden" class="easyui-textbox" name="savings_bank_mutation_last_balance" id="savings_bank_mutation_last_balance" autocomplete="off" readonly />
										</td>
									</tr>
									<tr>
										<td width="35%">Keterangan</td>
										<td width="5%">:</td>
										<td width="60%">
											<textarea rows="2" name="savings_bank_mutation_remark" id="savings_bank_mutation_remark" class="easyui-textarea" style="width: 100%"></textarea>
											<input type="hidden" class="easyui-textbox" name="savings_bank_mutation_token" id="savings_bank_mutation_token" autocomplete="off" value="<?php echo $token; ?>" />
										</td>
									</tr>
									<tr>
										<td width="35%"></td>
										<td width="5%"></td>
										<td width="60%" align="right">
											<button type="button" class="btn red" onClick="reset_data();"><i class="fa fa-times"></i> Batal</button>
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
<div id="memberlist" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Daftar Anggota</h4>
      </div>
      <div class="modal-body">
		<table id="myDataTable" class="table table-striped table-bordered table-hover table-full-width">
			<thead>
		    	<tr>
		        	<th>No</th>
		        	<th>No Rek</th>
		            <th>Nama Anggota</th>
		            <th>Alamat</th>
		            <th>Action</th>
		        </tr>
		    </thead>
		    <tbody></tbody>
		</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
 
var table;
 
$(document).ready(function() {
    table = $('#myDataTable').DataTable({ 
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "pageLength": 5,
        "order": [], //Initial no order.
        "ajax": {
            "url": "<?php echo site_url('savings-bank-mutation/get-list-savings-account')?>",
            "type": "POST"
        },
        "columnDefs": [
        { 
            "targets": [ 0 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        ],
    });
});
</script>

<script type="text/javascript">
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
</script>

<?php echo form_close(); ?>

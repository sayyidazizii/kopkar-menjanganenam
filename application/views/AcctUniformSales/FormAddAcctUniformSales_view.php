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
	input:disabled {
		background-color: f0f8ff;
	}

</style>

<script>
	var loop = 1;
	base_url = '<?php echo base_url();?>';

	function function_elements_add(name, value){
		$.ajax({
				type: "POST",
				url : "<?php echo site_url('uniform-sales/elements-add');?>",
				data : {'name' : name, 'value' : value},
				success: function(msg){
			}
		});
	}

	function reset_data(){
		document.location = base_url+"uniform-sales/add";
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

			  

			   $.post(base_url + 'uniform-sales/get-mutation-function',
				{mutation_id: mutation_id},
	            function(data){	
	            var obj = $.parseJSON(data)		   
	            	console.log(data);
	            	$('#mutation_function').textbox('setValue',obj);
					$('#savings_cash_mutation_amount_view').textbox('readonly',false); 
					$('#savings_cash_mutation_amount').textbox('setValue', 0);
					$('#savings_cash_mutation_amount_view').textbox('setValue', 0);
				},
				
				)
			  }
			})
	});

</script>
<?php echo form_open('uniform-sales/process-add',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
	$sesi 	= $this->session->userdata('unique');
	$data 	= $this->session->userdata('addacctuniformsales-'.$sesi['unique']);
	$token 	= $this->session->userdata('acctuniformsalestoken-'.$sesi['unique']);
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
			<a href="<?php echo base_url();?>uniform-sales">
				Daftar Mutasi Tabungan
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>uniform-sales/add">
				Tambah Mutasi Tunai Tabungan 
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
						<a href="<?php echo base_url();?>uniform-sales" class="btn btn-default btn-sm">
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
							<div class="col-md-11">
								<table width="100%">
									<tr>
										<td width="35%">No. Anggota</td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_name" id="member_name" value="<?php echo $coremember['member_no'];?>" style="width: 60%" readonly autofocus/> 
											<a href="#" role="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#memberlist">Cari Anggota</a>
										</td>
									</tr>
									<tr>
										<td width="35%">Nama Anggota</td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_name" id="member_name" autocomplete="off" style="width: 70%" value="<?php echo set_value('member_name',$coremember['member_name']);?>" readonly/>
											<input type="hidden" class="easyui-textbox" name="member_id" id="member_id" autocomplete="off" style="width: 70%" value="<?php echo set_value('member_id',$coremember['member_id']);?>" readonly/>
											<input type="hidden" class="easyui-textbox" name="uniform_sales_token" id="uniform_sales_token" autocomplete="off" style="width: 70%" value="<?php echo set_value('uniform_sales_token',$token);?>" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Tanggal<span class="required">*</span></td>
										<td width="5%">:</td>
										<td width="60%"><input type="text" class="easyui-datebox date-picker" name="uniform_sales_date" id="uniform_sales_date" value="<?php echo $data['uniform_sales_date'];?>" style="width: 70%"/></td>
									</tr>
									<tr>
										<td width="35%">Jenis Pembayaran<span class="required">*</span></td>
										<td width="5%">:</td>
										<td width="60%">
											<?php
											echo form_dropdown('uniform_sales_payment_type', $paymenttype, set_value('uniform_sales_payment_type', 1), 'id="uniform_sales_payment_type" class="easyui-combobox" style="width:70%"');
											?>
										</td>
									</tr>
									<tr>
										<td width="35%">Ukuran<span class="required">*</span></td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="uniform_sales_size" id="uniform_sales_size" autocomplete="off" style="width: 70%" value="<?php echo set_value('uniform_sales_size',$data['uniform_sales_size']);?>" />
										</td>
									</tr>
									<tr>
										<td width="35%">Harga<span class="required">*</span></td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="uniform_sales_price" id="uniform_sales_price" autocomplete="off" style="width: 70%" value="<?php echo set_value('uniform_sales_price',$data['uniform_sales_price']);?>" />
										</td>
									</tr>
									<tr>
										<td width="35%">Keterangan</td>
										<td width="5%">:</td>
										<td width="60%">
											<textarea type="text" class="" name="uniform_sales_remark" id="uniform_sales_remark" autocomplete="off" style="width: 70%"></textarea>
										</td>
									</tr>
									<tr>
										<td width="35%"></td>
										<td width="5%"></td>
										<td width="60%" align="right">
											<button type="button" class="btn red" onClick="reset_data();"><i class="fa fa-times"></i> Batal</button>
											<button type="submit" class="btn green-jungle"><i class="fa fa-save"></i> Simpan</button>
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
		        	<th>No Anggota</th>
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
        "processing": true, 
        "serverSide": true,
        "pageLength": 5,
        "order": [],
        "ajax": {
            "url": "<?php echo site_url('uniform-sales/get-list-core-member')?>",
            "type": "POST"
        },
        "columnDefs": [
        { 
            "targets": [ 0 ],
            "orderable": false,
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

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
	base_url 			= '<?php echo base_url();?>';
	var loop_amount 	= 1;

	function toRp(number) {
		var number = number.toString(),
			rupiah = number.split('.')[0],
			cents = (number.split('.')[1] || '') + '00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1,')
			.split('').reverse().join('');
		return rupiah + '.' + cents.slice(0, 2);
	}

	function calculateDebtAllocated(member_id){
		var principal 		= document.getElementById("principal_"+member_id).value;
		var savings 		= document.getElementById("savings_"+member_id).value;
		var credits 		= document.getElementById("credits_"+member_id).value;
		var credits_store 	= document.getElementById("credits_store_"+member_id).value;
		var minimarket 		= document.getElementById("minimarket_"+member_id).value;
		var uniform 		= document.getElementById("uniform_"+member_id).value;

		var allocated		= parseInt(principal)+parseInt(savings)+parseInt(credits)+parseInt(credits_store)+parseInt(minimarket)+parseInt(uniform);
		document.getElementById("allocation_"+member_id).value = allocated;
		document.getElementById("allocation_view_"+member_id).value = toRp(allocated);
	}

	$(document).ready(function(){
		$('#debt_amount_view').textbox({
			onChange: function(value) {
				if (loop_amount == 0) {
					loop_amount = 1;
					return;
				}

				if (loop_amount == 1) {
					loop_amount = 0;
					var tampil = toRp(value);
					$('#debt_amount').textbox('setValue', value);
					$('#debt_amount_view').textbox('setValue', tampil);
				} else {
					loop_amount = 1;
					return;
				}
			}
		});
	});
	
</script>
<?php echo form_open_multipart('debt/process-add',array('id' => 'myform', 'class' => 'horizontal-form')); ?>
<?php
	$sesi 	= $this->session->userdata('unique');
	$data 	= $this->session->userdata('addacctdebt-'.$sesi['unique']);
	$auth 	= $this->session->userdata('auth');
	$token 	= md5(rand());
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
			<a href="<?php echo base_url();?>debt">
				Daftar Potong Gaji
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>debt/add">
				Tambah Potong Gaji 
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
						Form Tambah Potong Gaji
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>debt" class="btn btn-default btn-sm">
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
							<input type="hidden" class="form-control" name="debt_token" id="debt_token" value="<?php echo $token;?>" readonly/>
							<div class="col-md-6">
								<table width="100%">
									<tr>
										<td width="35%">No. Anggota<span class="required" style="color: red !important">*</span></td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_no" id="member_no" value="<?php echo $coremember['member_no'];?>" style="width: 60%" readonly autofocus/> <a href="#" role="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#memberlist">Cari Anggota</a>
											<input type="hidden" class="easyui-textbox" name="member_id" id="member_id" style="width: 60%" value="<?php echo $coremember['member_id'];?>" readonly>
										</td>
									</tr>
									<tr>
										<td width="35%">Nama Anggota</td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_name" id="member_name" style="width: 60%" value="<?php echo $coremember['member_name'];?>" readonly>
										</td>
									</tr>
									<tr>
										<td width="35%">Alamat</td>
										<td width="5%">:</td>
										<td width="60%">
											<textarea class="form-control" id="member_address" name="member_address" readonly><?php echo $coremember['member_address'];?></textarea>
										</td>
									</tr>
									<tr>
										<td width="35%">Kategori<span class="required" style="color: red !important">*</span></td>
										<td width="5%">:</td>
										<td width="60%">
											<?php 
												echo form_dropdown('debt_category_id', $acctdebtcategory, set_value('debt_category_id', $data['debt_category_id']), 'id="debt_category_id" class="form-control select2me"');
											?>
										</td>
									</tr>
									<tr>
										<td width="35%">Tanggal<span class="required" style="color: red !important">*</span></td>
										<td width="5%">:</td>
										<td width="60%">
											<input class="easyui-datebox" data-options="formatter:myformatter,parser:myparser" type="text" name="debt_date" id="debt_date" onChange="function_elements_add(this.name, this.value);" value="<?php echo tgltoview($data['debt_date']);?>" style="width: 60%"/>
										</td>
									</tr>
									<tr>
										<td width="35%">Jumlah<span class="required" style="color: red !important">*</span></td>
										<td width="5%">:</td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="debt_amount_view" id="debt_amount_view" style="width: 60%">
											<input type="hidden" class="easyui-textbox" name="debt_amount" id="debt_amount">
										</td>
									</tr>
									<tr>
										<td width="35%">Keterangan</td>
										<td width="5%">:</td>
										<td width="60%">
											<textarea class="form-control" id="debt_remark" name="debt_remark"></textarea>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12" style='text-align:right'>
								<button type="submit" name="process" value="process" id="process" class="btn green-jungle" title="Proses Data">Proses</i></button>
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
							<th>Member No</th>
							<th>Member Nama</th>
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

	var table;

	$(document).ready(function() {
		table = $('#myDataTable').DataTable({ 
			"processing": true,
			"serverSide": true,
			"pageLength": 5,
			"order": [],
			"ajax": {
				"url": "<?php echo site_url('debt/get-list-member')?>",
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
<?php echo form_close(); ?>
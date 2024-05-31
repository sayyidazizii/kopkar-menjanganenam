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

	function toRp(number) {
		var number = number.toString(), 
		rupiah = number.split('.')[0], 
		cents = (number.split('.')[1] || '') +'00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1,')
			.split('').reverse().join('');
		return rupiah + '.' + cents.slice(0, 2);
	}

</script>
<?php echo form_open('savings-salary-mutation/process-add',array('id' => 'myform', 'class' => 'horizontal-form')); 

$unique = $this->session->userdata('unique');
$token 	= $this->session->userdata('acctsavingscashmutationtoken-'.$unique['unique']);
?>

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
			<a href="<?php echo base_url();?>member">
				Daftar Anggota
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>savings-salary-mutation" ?>>
				Tambah Mutasi Tabungan Potong Gaji 
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
						Form Mutasi Tabungan Potong Gaji
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>savings-salary-mutation" class="btn btn-default btn-sm">
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
								<table class="table table-striped table-bordered table-hover table-full-width" id="sample_3">
									<thead>
										<tr>
											<th style="text-align:center" width="5%">No</th>
											<th style="text-align:center" width="10%">No Rek Tabungan</th>
											<th style="text-align:center" width="15%">Jenis Tabungan</th>
											<th style="text-align:center" width="10%">No Anggota</th>
											<th style="text-align:center" width="20%">Nama Anggota</th>
											<th style="text-align:center" width="20%">Bagian</th>
											<th style="text-align:center" width="15%">Mutasi Tabungan</th>
										</tr>
									</thead>
									<tbody>
										<?php 
										$no=1;
										$savings_amount_total = 0;
										if(empty($acctsavingsaccount)){
											echo "<tr><td align='center' colspan='5'> Data Kosong !</td></tr>";
										} else {
											foreach ($acctsavingsaccount as $key=>$val){ 
												echo"
													<tr>
														<td style='text-align:center'>".$no."</td>
														<td>".$val['savings_account_no']."</td>
														<td>".$val['savings_name']."</td>
														<td>".$val['member_no']."</td>
														<td>".$val['member_name']."</td>
														<td>".$val['division_name']."</td>
														<td style='text-align:right'>".number_format($val['savings_account_deposit_amount'], 2)."</td>
													</tr>
												";
												$no++;
												$savings_amount_total += $val['savings_account_deposit_amount'];
											}
										} ?>
									</tbody>
								</table>
								<hr>
								<table width="40%" align="right">
									<tr>
										<td width="35%">Sandi</td>
										<td width="2%"></td>
										<td width="60%">
											<?php echo form_dropdown('mutation_id', $acctmutation, set_value('mutation_id', 14),'id="mutation_id" class="easyui-combobox" style="width:70%" readonly');?>
											
										</td>
									</tr>
										<input type="hidden" class="easyui-textbox" name="mutation_function" id="mutation_function" value="+" autocomplete="off" readonly/>

									
									<tr>
										<td width="35%">Total Mutasi Tabungan</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_principal_savings_view" id="member_principal_savings_view" value="<?php echo number_format($savings_amount_total, 2) ?>" autocomplete="off" style="width: 100%" readonly/>
											<input type="hidden" class="easyui-textbox" name="member_principal_savings" id="member_principal_savings" value="<?php echo $savings_amount_total ?>"  autocomplete="off" />
										</td>
									</tr>
								</table>

								<input type="hidden" class="form-control" name="member_token_edit" id="member_token_edit" placeholder="id" value="<?php echo $token;?>"/>
								
								<table width="100%" style="margin-top:180px">
									<tr>
										<td width="35%"></td>
										<td width="5%"></td>
										<td width="60%" align="right">
											<a href="<?php echo base_url()?>savings-salary-mutation/print-all" class="btn btn-primary">Cetak Pdf</a>
											<button type="button" class="btn green-jungle" data-toggle="modal" data-target="#myModal2">Simpan</button>
											<!-- <button type="submit" class="btn green-jungle"><i class="fa fa-check"></i> Simpan</button> -->
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



<div id ="myModal2" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal Content -->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notifikasi</h4>
            </div>
            <div class="modal-body">
               <p class="text-dark"> Apakah Ingin Menyimpan Data ini ?</p> <p class="text-danger"> 
					Setelah disimpan akan membuat jurnal otomatis. </p>
            </div>
            <div class="modal-footer">
                 <button type="submit" class="btn green-jungle" >Ya</button>
                 <button type="reset" class="btn btn-default" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
<?php echo form_close(); ?>
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
<?php echo form_open('member/process-add-salary-mandatory',array('id' => 'myform', 'class' => 'horizontal-form')); 

$unique = $this->session->userdata('unique');
$token 	= $this->session->userdata('coremembertokensalarymandatory-'.$unique['unique']);
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
			<a href="<?php echo base_url();?>member/salary-mandatory-savings" ?>>
				Tambah Simpanan Wajib Potong Gaji 
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
						Form Simpanan Wajib Potong Gaji
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>member/edit-mandatory-savings" class="btn btn-default btn-sm">
							<i class="fa fa-edit"></i>
							<span class="hidden-480">
								Edit Simpanan Wajib
							</span>
						</a>
						<a href="<?php echo base_url();?>member" class="btn btn-default btn-sm">
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
											<th style="text-align:center" width="25%">No Anggota</th>
											<th style="text-align:center" width="25%">Nama Anggota</th>
											<th style="text-align:center" width="20%">Bagian</th>
											<th style="text-align:center" width="25%">Simpanan Wajib</th>
										</tr>
									</thead>
									<tbody>
										<?php 
										$no=1;
										$principal_savings_total_amount = 0;
										if(empty($coremember)){
											echo "<tr><td align='center' colspan='5'> Data Kosong !</td></tr>";
										} else {
											foreach ($coremember as $key=>$val){ 
												echo"
													<tr>
														<td style='text-align:center'>".$no."</td>
														<td>".$val['member_no']."</td>
														<td>".$val['member_name']."</td>
														<td>".$val['division_name']."</td>
														<td style='text-align:right'>".number_format($val['member_principal_savings'], 2)."</td>
													</tr>
												";
												$no++;
												$principal_savings_total_amount += $val['member_principal_savings'];
											}
										} ?>
									</tbody>
								</table>
								<hr>
								<table width="40%" align="right">
									<tr>
										<td width="35%">No. Perkiraan<span class="required"> *</span></td>
										<td width="5%">:</td>
										<td width="60%">
											<?php echo form_dropdown('account_id', $acctaccount,set_value('account_id',$data['account_id']),'id="account_id" class="easyui-combobox" style="width:100%"');?>
										</td>
									</tr>
									<tr>
										<td width="35%">Sandi</td>
										<td width="2%"></td>
										<td width="60%">
											<?php echo form_dropdown('mutation_id', $acctmutation, set_value('mutation_id', 14),'id="mutation_id" class="easyui-combobox" style="width:70%" readonly');?>
										</td>
									</tr>
										<input type="hidden" class="easyui-textbox" name="mutation_function" id="mutation_function" value="+" autocomplete="off" readonly/>
									<tr>
										<td width="35%">Total Simpanan Wajib</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_principal_savings_view" id="member_principal_savings_view" value="<?php echo number_format($mandatory_savings_total_amount, 2) ?>" autocomplete="off" style="width: 100%" readonly/>
											<input type="hidden" class="easyui-textbox" name="member_principal_savings" id="member_principal_savings" value="<?php echo $mandatory_savings_total_amount ?>"  autocomplete="off" />
										</td>
									</tr>
									<tr>
										<td width="35%">Keterangan</td>
										<td width="5%">:</td>
										<td width="60%">
											<textarea rows="3" name="savings_member_detail_remark" id="savings_member_detail_remark" class="easyui-textarea"  style="width:100%;"></textarea>
										</td>
									</tr>
								</table>

								<input type="hidden" class="form-control" name="member_token_edit" id="member_token_edit" placeholder="id" value="<?php echo $token;?>"/>
								
								<table width="100%" style="margin-top:180px">
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
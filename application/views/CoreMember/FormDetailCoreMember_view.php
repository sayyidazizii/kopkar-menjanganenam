<style>
	th, td {
	  padding: 3px;
	}
	input:focus { 
	  background-color: 42f483;
	}
</style>

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
			<a href="<?php echo base_url();?>member/detail/"<?php echo $coremember['member_id'] ?>>
				Detail Anggota 
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
						Form Detail
					</div>
					<div class="actions">
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
						<div class="row">
							<div class="col-md-1"></div>
							<div class="col-md-5">
								<table width="100%">
									<tr>
										<td width="35%">No. Anggota</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_no" id="member_no" autocomplete="off" value="<?php echo set_value('member_no', $coremember['member_no']);?>" style="width: 60%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">NIK Karyawan</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_nik" id="member_nik" autocomplete="off" value="<?php echo set_value('member_nik', $coremember['member_nik']);?>" style="width: 60%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Nama Anggota</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_name" id="member_name" autocomplete="off" value="<?php echo set_value('member_name', $coremember['member_name']);?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Jenis Kelamin</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_gender" id="member_gender" autocomplete="off" value="<?php echo $membergender[$coremember['member_gender']];?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Divisi</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="division_name" id="division_name" autocomplete="off" value="<?php echo $this->CoreMember_model->getDivisionName($coremember['division_id']);?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Bagian</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="division_name" id="division_name" autocomplete="off" value="<?php echo $this->CoreMember_model->getPartName($coremember['part_id']);?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Provinsi</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="province_name" id="province_name" autocomplete="off" value="<?php echo $coremember['province_name'];?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Kabupaten</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="city_name" id="city_name" autocomplete="off" value="<?php echo $coremember['city_name'];?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Kecamatan</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="kecamatan_name" id="kecamatan_name" autocomplete="off" value="<?php echo $coremember['kecamatan_name'];?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Kode Pos</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_postal_code" id="member_postal_code" autocomplete="off" value="<?php echo $coremember['member_postal_code'];?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Alamat</td>
										<td width="5%"></td>
										<td width="60%"><textarea rows="3" name="member_address" id="member_address" class="easyui-textarea" style="width: 100%" disabled><?php echo $coremember['member_address'];?></textarea></td>
									</tr>
								</table>
							</div>
							<div class="col-md-1"></div>
							<div class="col-md-5">
								<table>
									<tr>
										<td width="35%">Tempat Lahir</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_place_of_birth" id="member_place_of_birth" autocomplete="off" value="<?php echo $coremember['member_place_of_birth'];?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Tanggal Lahir</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_date_of_birth" id="member_date_of_birth" autocomplete="off" value="<?php echo tgltoview($coremember['member_date_of_birth']);?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Tanggal Masuk</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_register_date" id="member_register_date" autocomplete="off" value="<?php echo tgltoview($coremember['member_register_date']);?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<?php 
									if($coremember['member_active_status'] == 0){
										$member_non_activate_date = '';
									}else{
										$member_non_activate_date = $coremember['member_non_activate_date'];
									}
									?>
									<tr>
										<td width="35%">Tanggal Non Aktif</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_non_activate_date" id="member_non_activate_date" autocomplete="off" value="<?php echo tgltoview($member_non_activate_date);?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">No. HP</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_phone" id="member_phone" autocomplete="off" value="<?php echo $coremember['member_phone'];?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">No. Identitas</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_identity_no" id="member_identity_no" autocomplete="off" value="<?php echo $coremember['member_identity_no'];?>" style="width: 100%" readonly/>
										</td>
									</tr>
									
									<tr>
										<td width="35%">Nama Ibu Kandung</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_mother" id="member_mother" autocomplete="off" value="<?php echo $coremember['member_mother'];?>" style="width: 100%" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Saldo Simp Pokok</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_principal_savings_last_balance_view" id="member_principal_savings_last_balance_view" autocomplete="off" value="<?php echo number_format($coremember['member_principal_savings_last_balance'], 2);?>" style="width: 100%" readonly/>
										</td>

									<tr>
										<td width="35%">Saldo Simp Khusus</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_special_savings_last_balance_view" id="member_special_savings_last_balance_view" autocomplete="off" style="width: 100%" value="<?php echo number_format($coremember['member_special_savings_last_balance'], 2);?>" readonly/>
										</td>
									</tr>
									<tr>
										<td width="35%">Saldo Simp Wajib</td>
										<td width="5%"></td>
										<td width="60%">
											<input type="text" class="easyui-textbox" name="member_mandatory_savings_last_balance_view" id="member_mandatory_savings_last_balance_view" autocomplete="off" style="width: 100%" value="<?php echo number_format($coremember['member_mandatory_savings_last_balance'], 2);?>" readonly/>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div class="form-body">
						<div class="row">
							<table class="table table-striped table-bordered table-full-width">
								<tr>
									<td width="48%">
										<table class="table table-striped table-bordered table-hover table-full-width">
											<thead>
												<tr>
													<th colspan="3"><center style="font-weight: bold;">Daftar Simpanan <?php echo $coremember['member_name']; ?></center></th>
												</tr>
												<tr>
													<th width="3%"><center>No</center></th>
													<th width="7%"><center>No. Rek. Simpanan</center></th>
													<th width="12%"><center>Jenis Simpanan</center></th>
												</tr>
											</thead>
											<tbody>
												<?php 
													$no = 1;
													foreach ($acctsavingsaccount as $key => $val) {
														echo "
															<tr>
																<td align='center'>$no</td>
																<td>".$val['savings_account_no']."</td>
																<td>".$val['savings_name']."</td>
															</tr>
														";
														$no++;
													}
												?>
											</tbody>
										</table>
									</td>
									<td></td>
									<td width="48%">
										<table class="table table-striped table-bordered table-hover table-full-width">
											<thead>
												<tr>
													<th colspan="5"><center style="font-weight: bold;">Daftar Pembiayaan <?php echo $coremember['member_name']; ?></center></th>
												</tr>
												<tr>
													<th width="8%"><center>No</center></th>
													<th width="27%"><center>No. Akad</center></th>
													<th width="35%"><center>Jenis Pembiayaan</center></th>
													<th width="10%"><center>Tenor</center></th>
													<th width="20%"><center>Sudah Angsur</center></th>
												</tr>
											</thead>
											<tbody>
												<?php 
													$no = 1;
													foreach ($acctcreditsaccount as $key => $val) {
														echo "
															<tr>
																<td align='center'>$no</td>
																<td>".$val['credits_account_serial']."</td>
																<td>".$val['credits_name']."</td>
																<td><center>".$val['credits_account_period']."</center></td>
																<td><center>".$val['credits_account_payment_to']."</center></td>
															</tr>
														";
														$no++;
													}
												?>
											</tbody>
										</table>
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

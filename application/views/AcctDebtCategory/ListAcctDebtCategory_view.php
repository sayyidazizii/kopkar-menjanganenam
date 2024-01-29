<style>
	th{
		font-size:14px  !important;
		font-weight: bold !important;
		text-align:center !important;
		margin : 0 auto;
		vertical-align:middle !important;
	}
	td{
		font-size:12px  !important;
		font-weight: normal !important;
	}
</style>
<div class="row-fluid">

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
			<a href="<?php echo base_url();?>debt-category">
				Daftar Kategori Potong Gaji
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>

<h3 class="page-title">
	Daftar Kategori Potong Gaji <small>Kelola Kategori Potong Gaji</small>
</h3>
<?php
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');
?>
	<div class="row">
		<div class="col-md-12">
			<div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Daftar
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>debt-category/add" class="btn btn-default btn-sm">
							<i class="fa fa-plus"></i>
							<span class="hidden-480">
								Tambah Kategori Baru
							</span>
						</a>
					</div>
				</div>
				<div class="portlet-body">
					<div class="form-body">
						<table class="table table-striped table-bordered table-hover table-full-width" id="sample_3">
						<thead>
							<tr>
								<th width="5%">No</th>
								<th width="10%">Kode</th>
								<th width="25%">Nama Kategori</th>
								<th width="25%">Debit</th>
								<th width="25%">Kredit</th>
								<th width="10%">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$no = 1;
								if(empty($acctdebtcategory)){
									echo "
										<tr>
											<td colspan='7' align='center'>Data Kosong</td>
										</tr>
									";
								} else {
									foreach ($acctdebtcategory as $key=>$val){									
										echo"
											<tr>			
												<td style='text-align:center'>$no.</td>
												<td>".$val['debt_category_code']."</td>
												<td>".$val['debt_category_name']."</td>
												<td>".$this->AcctDebtCategory_model->getAcctAccountCodeName($val['debet_account_id'])."</td>
												<td>".$this->AcctDebtCategory_model->getAcctAccountCodeName($val['credit_account_id'])."</td>
												<td>
													<a href='".$this->config->item('base_url').'debt-category/edit/'.$val['debt_category_id']."' class='btn default btn-xs purple'>
														<i class='fa fa-edit'></i> Edit
													</a>
													<a href='".$this->config->item('base_url').'debt-category/delete/'.$val['debt_category_id']."'class='btn default btn-xs red', onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'>
														<i class='fa fa-trash-o'></i> Hapus
													</a>
												</td>
											</tr>
										";
										$no++;
									} 
								}
							?>
							</tbody>
							</table>
						</div>
						</div>
					</div>
				</div>
			</div>
<?php echo form_close(); ?>
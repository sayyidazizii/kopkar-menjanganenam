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
			<a href="<?php echo base_url();?>AcctCredits">
				Daftar Pengumuman Anggota
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>

<h3 class="page-title">
	Daftar Pengumuman Anggota <small>Kelola Pengumuman Anggota</small>
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
						<a href="<?php echo base_url();?>wa-broadcast/add" class="btn btn-default btn-sm">
							<i class="fa fa-plus"></i>
							<span class="hidden-480">
								Tambah Pengumuman Baru
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
								<th width="20%">Judul</th>
								<th width="40%">Pesan</th>
								<th width="10%">Link</th>
								<th width="10%">Tanggal</th>
								<th width="15%">Pengirim</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$no = 1;
								if(empty($broadcast)){
									echo "
										<tr>
											<td colspan='7' align='center'>Data Kosong</td>
										</tr>
									";
								} else {
									foreach ($broadcast as $key => $val){									
										echo"
											<tr>			
												<td style='text-align:center'>$no.</td>
												<td>".$val['broadcast_title']."</td>
												<td>".$val['broadcast_message']."</td>
												<td>".$val['broadcast_link']."</td>
												<td>".$val['created_on']."</td>
												<td>".$val['username']."</td>
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
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
<?php echo form_close(); ?>
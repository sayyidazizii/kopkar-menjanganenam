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
			<a href="<?php echo base_url();?>debt">
				Potong Gaji
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>

<h3 class="page-title">
	Potong Gaji <small>Kelola Potong Gaji</small>
</h3>
<?php
	echo $this->session->userdata('message');
	$this->session->unset_userdata('message');

	$auth = $this->session->userdata('auth');

	$sesi=$this->session->userdata('filter-acctdebt');

	if(!is_array($sesi)){
		
		$sesi['start_date']		= date('Y-m-d');
		$sesi['end_date']		= date('Y-m-d');	
	}

	$start_date = $sesi['start_date'];
	$end_date 	= $sesi['end_date'];
?>	
<?php	echo form_open('debt/filter',array('id' => 'myform', 'class' => '')); ?>

<script type="text/javascript">
		base_url = '<?php echo base_url();?>';

	function reset_search(){
		document.location = base_url+"debt/reset-list";
	}

</script>
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Pencarian
				</div>
				<div class="tools">
					<a href="javascript:;" class='expand'></a>
				</div>
			</div>
			<div class="portlet-body display-hide">
				<div class="form-body form">
					 <div class = "row">
						<div class = "col-md-6">
							<div class="form-group form-md-line-input">
								<input class="form-control form-control-inline input-medium date-picker" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" value="<?php echo tgltoview($start_date);?>"/>
								<label class="control-label">Tanggal Awal
									<span class="required">
										*
									</span>
								</label>
							</div>
						</div>

						<div class = "col-md-6">
							<div class="form-group form-md-line-input">
								<input class="form-control form-control-inline input-medium date-picker" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" value="<?php echo tgltoview($end_date);?>"/>
								<label class="control-label">Tanggal Akhir
									<span class="required">
										*
									</span>
								</label>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="form-actions right">
							<button type="button" class="btn red" onClick="reset_search();"><i class="fa fa-times"></i> Batal</button>
							<button type="submit" class="btn green-jungle"><i class="fa fa-search"></i> Cari</button>
						</div>	
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue">
			<div class="portlet-title">
				<div class="caption">
					Daftar
				</div>
				<div class="actions">
					<a href="<?php echo base_url();?>debt/import" class="btn btn-default btn-sm">
						<i class="fa fa-file-excel-o"></i>
						<span class="hidden-480">
							Import Potong Gaji Baru
						</span>
					</a>
					<a href="<?php echo base_url();?>debt/add" class="btn btn-default btn-sm">
						<i class="fa fa-plus"></i>
						<span class="hidden-480">
							Tambah Potong Gaji Baru
						</span>
					</a>
				</div>
			</div>
			<div class="portlet-body">
				<div class="form-body">
					<table class="table table-striped table-bordered table-hover table-full-width" id="myDataTable">
					<thead>
						<tr>
							<th width="5%">No</th>
							<th width="5%">No. Potong Gaji</th>
							<th width="10%">Kategori</th>
							<th width="10%">No. Anggota</th>
							<th width="15%">Nama Anggota</th>
							<th width="10%">Tanggal</th>
							<th width="10%">Jumlah</th>
							<th width="15%">Keterangan</th>
							<th width="10%">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						   $no = 0;
						foreach($acctdebt as $val){ 
							$no++
							?>
						<tr>
							<td><?=$no?></td>
							<td><?= $val['debt_no']?></td>
							<td><?= $this->AcctDebt_model->getAcctDebtCategoryName($val['debt_category_id'])?></td>
							<td><?= $this->AcctDebt_model->getCoreMemberNo($val['member_id'])?></td>
							<td><?= $this->AcctDebt_model->getCoreMemberName($val['member_id'])?></td>
							<td><?= $val['debt_date']?></td>
							<td><?= $val['debt_amount']?></td>
							<td><?= $val['debt_remark']?></td>
							<td><a href="<?=base_url().'debt/delete/'.$val['debt_id']?>" class="btn btn-xs red" role="button"><i class="fa fa-trash"></i> Hapus</a></td>
						</tr>
						<?php } ?>
					</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var table;
$(document).ready(function() {
    table = $('#myDataTable').DataTable();
});
</script>
<?php echo form_close(); ?>
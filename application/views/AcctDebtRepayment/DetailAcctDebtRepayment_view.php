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
</style>
<script>
	
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
			<a href="<?php echo base_url();?>debt-repayment">
				Daftar Pelunasan Piutang Potong Gaji
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
		<li>
			<a href="<?php echo base_url();?>debt-repayment/detail/<?php echo $this->uri->segment(3);?>">
				Detail Pelunasan Piutang Potong Gaji
			</a>
			<i class="fa fa-angle-right"></i>
		</li>
	</ul>
</div>
<?php
	echo form_open('debt-repayment/process-printing'); 
?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet"> 
			 <div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						Form Detail
					</div>
					<div class="actions">
						<a href="<?php echo base_url();?>debt-repayment" class="btn btn-default btn-sm">
							<i class="fa fa-angle-left"></i>
							<span class="hidden-480">
								Kembali
							</span>
						</a>
					</div>
				</div>
			
				<div class="portlet-body">
					<div class="row">
						<div class="col-md-12">
							<table style="width: 100%;" border="0" padding="0">
								<tr>
									<td width="35%">No. Pelunasan Piutang Potong Gaji</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="debt_repayment_no" readonly id="debt_repayment_no" value="<?php echo $debtrepaymentdetail['debt_repayment_no']; ?>" style="width: 100%"/>
										<input class="easyui-textbox" type="hidden" name="debt_repayment_id" readonly id="debt_repayment_id" value="<?php echo $debtrepaymentdetail['debt_repayment_id']; ?>" style="width: 100%"/>

									</td>
								</tr>
								<tr>
									<td width="35%">Tanggal Pelunasan Piutang Potong Gaji</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="debt_repayment_date" readonly id="debt_repayment_date" value="<?php echo date('d-m-Y', strtotime($debtrepaymentdetail['debt_repayment_date'])); ?>" style="width: 100%"/>

									</td>
								</tr>
								<tr>
									<td width="35%">Total</td>
									<td width="5%"> : </td>
									<td width="60%">
										<input class="easyui-textbox" type="text" name="debt_repayment_amount" readonly id="debt_repayment_amount" value="<?php echo nominal($debtrepaymentdetail['debt_repayment_amount']); ?>" style="width: 100%"/>

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

	<div class="row">
		<div class="col-md-12">	
			<div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						List Data Pelunasan
					</div>
				</div>
				<div class="portlet-body ">
					<!-- BEGIN FORM-->
					<div class="form-body">
						<div class="table-responsive">
							<table class="table table-bordered table-advance table-hover" width="100%">
								<tbody>
									<tr>    
										<th width='5%' style='text-align:center'>No.</th>	
										<th width='15%' style='text-align:center;'>No. Anggota</th>		
										<th width='15%' style='text-align:center;'>Nama Anggota</th>	
										<th width='10%' style='text-align:center;'>Simp Pokok</th>	
										<th width='10%' style='text-align:center;'>Tabungan</th>	
										<th width='10%' style='text-align:center;'>Pinjaman</th>	
										<th width='10%' style='text-align:center;'>Pinjaman Lain</th>	
										<th width='10%' style='text-align:center;'>Pinjaman Toko</th>	
										<th width='10%' style='text-align:center;'>Seragam</th>	
										<th width='15%' style='text-align:center;'>Total</th>	
									</tr>
									<?php
										$no = 1;
										if($debtrepaymentitem){
											foreach($debtrepaymentitem as $key => $val){
												echo"
													<tr>
														<td style='text-align:center'>".$no."</td>
														<td>".$val['member_no']."</td>
														<td style='text-align:left;'>".$val['member_name']."</td>
														<td style='text-align:right;'>".nominal($val['debt_repayment_item_principal_amount'])."</td>
														<td style='text-align:right;'>".nominal($val['debt_repayment_item_savings_amount'])."</td>
														<td style='text-align:right;'>".nominal($val['debt_repayment_item_credits_amount'])."</td>
														<td style='text-align:right;'>".nominal($val['debt_repayment_item_credits_store_amount'])."</td>
														<td style='text-align:right;'>".nominal($val['debt_repayment_item_minimarket_amount'])."</td>
														<td style='text-align:right;'>".nominal($val['debt_repayment_item_uniform_amount'])."</td>
														<td style='text-align:right;'>".nominal($val['debt_repayment_item_amount'])."</td>
													</tr>
												";
												$no++;
											}
										}else{
											echo"
													<tr>
														<td colspan='8' align='center'>Data Kosong</td>
													</tr>
												";
										}
										
									?>			
									
								</tbody>
							</table>
						</div>
					</div>

					<BR>
					<BR>
					<div class="row">
						<div class="col-md-12 " style="text-align  : right !important;">
							<!-- <input type="submit" name="Preview" id="Preview" value="Preview" class="btn blue" title="Preview"> -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php echo form_close(); ?>
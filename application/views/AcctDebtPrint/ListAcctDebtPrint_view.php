<style>
    th {
        font-size: 14px !important;
        font-weight: bold !important;
        text-align: center !important;
        margin: 0 auto;
        vertical-align: middle !important;
    }

    td {
        font-size: 12px !important;
        font-weight: normal !important;
    }

    .nav-tabs {
        display: flex;
        justify-content: center;
    }
</style>
<script type="text/javascript">
    base_url = '<?php echo base_url(); ?>';

    function showTab(tabId) {
        // Hide all tab content
        var tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(function (content) {
            content.classList.remove('active');
        });

        // Show selected tab content
        var selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }
    }

    function confirmDelete() {
        return confirm('Apakah Anda yakin ingin menghapus potongan ini?');
    }
</script>
<div class="row-fluid">

    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li>
                <i class="fa fa-home"></i>
                <a href="<?php echo base_url(); ?>">
                    Beranda
                </a>
                <i class="fa fa-angle-right"></i>
            </li>
            <li>
                <a href="<?php echo base_url(); ?>debt-print">
                    Daftar Cetak Potong Gaji
                </a>
                <i class="fa fa-angle-right"></i>
            </li>
        </ul>
    </div>

    <h3 class="page-title">
        Daftar Cetak Potong Gaji <small>Kelola Cetak Potong Gaji</small>
    </h3>
    <?php
    echo $this->session->userdata('message');
    $this->session->unset_userdata('message');

    $auth = $this->session->userdata('auth');

    $sesi = $this->session->userdata('filter-acctdebt');

    if (!is_array($sesi)) {

        $sesi['start_date'] = date('Y-m-d');
        $sesi['end_date'] = date('Y-m-d');
    }

    $start_date = $sesi['start_date'];
    $end_date = $sesi['end_date'];
    ?>
    <?php echo form_open('debt-print/viewreport', array('id' => 'myform', 'class' => '')); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="portlet box blue">
                <div class="portlet-title">
                    <div class="caption">
                        Cetak Potong Gaji
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="form-body form">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-md-line-input">
                                    <input class="form-control form-control-inline input-medium date-picker"
                                        data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date"
                                        value="<?php echo tgltoview($start_date); ?>" />
                                    <label class="control-label">Tanggal Awal
                                        <span class="required">
                                            *
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group form-md-line-input">
                                    <input class="form-control form-control-inline input-medium date-picker"
                                        data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date"
                                        value="<?php echo tgltoview($end_date); ?>" />
                                    <label class="control-label">Tanggal Akhir
                                        <span class="required">
                                            *
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group form-md-line-input">
                                    <?php
                                    echo form_dropdown('debt_category_id', $acctdebtcategory, set_value('debt_category_id', $data['debt_category_id']), 'id="debt_category_id" class="form-control select2me"');
                                    ?>
                                    <label class="control-label">Kategori</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group form-md-line-input">
                                    <?php
                                    echo form_dropdown('part_id', $corepart, set_value('part_id', $data['part_id']), 'id="part_id" class="form-control select2me"');
                                    ?>
                                    <label class="control-label">Bagian</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group form-md-line-input">
                                    <?php
                                    echo form_dropdown('division_id', $coredivision, set_value('division_id', $data['division_id']), 'id="division_id" class="form-control select2me"');
                                    ?>
                                    <label class="control-label">Divisi</label>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: center !important">
                            <div class="row">
                                <div class="form-actions">
                                    <button type="submit" class="btn green" id="view" name="view"
                                        value="pdf_category"><i class="fa fa-file-pdf-o"></i> PDF Kategori</button>
                                    <button type="submit" class="btn green" id="view" name="view" value="pdf_member"><i
                                            class="fa fa-file-pdf-o"></i> PDF Simpanan Agt</button>
                                    <button type="submit" class="btn green" id="view" name="view" value="pdf_savings"><i
                                            class="fa fa-file-pdf-o"></i> PDF Tabungan</button>
                                    <button type="submit" class="btn green" id="view" name="view" value="pdf_credits"><i
                                            class="fa fa-file-pdf-o"></i> PDF Pinjaman</button>
                                    <button type="submit" class="btn green" id="view" name="view" value="pdf_store"><i
                                            class="fa fa-file-pdf-o"></i> PDF Toko</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-actions">
                                    <button type="submit" class="btn green-jungle" id="view" name="view"
                                        value="excel_category"><i class="fa fa-file-excel-o"></i> Excel
                                        Kategori</button>
                                    <button type="submit" class="btn green-jungle" id="view" name="view"
                                        value="excel_member"><i class="fa fa-file-excel-o"></i> Excel Simpanan
                                        Agt</button>
                                    <button type="submit" class="btn green-jungle" id="view" name="view"
                                        value="excel_savings"><i class="fa fa-file-excel-o"></i> Excel Tabungan</button>
                                    <button type="submit" class="btn green-jungle" id="view" name="view"
                                        value="excel_credits"><i class="fa fa-file-excel-o"></i> Excel Pinjaman</button>
                                    <button type="submit" class="btn green-jungle" id="view" name="view"
                                        value="excel_store"><i class="fa fa-file-excel-o"></i> Excel Toko</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-actions">
                                    <button type="submit" class="btn yellow" id="view" name="view" value="pdf_recap"><i
                                            class="fa fa-file-pdf-o"></i> PDF Rekap</button>
                                    <button type="submit" class="btn yellow" id="view" name="view"
                                        value="excel_recap"><i class="fa fa-file-excel-o"></i> Excel Rekap</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-actions">
                                    <button type="submit" class="btn purple" id="view" name="view" value="pdf_simple"><i
                                            class="fa fa-file-pdf-o"></i> PDF Simple</button>
                                    <button type="submit" class="btn purple" id="view" name="view"
                                        value="excel_simple"><i class="fa fa-file-excel-o"></i> Excel Simple</button>

                                    <button type="submit" class="btn red" id="view" name="view"
                                        value="pdf_simple_temp"><i class="fa fa-file-pdf-o"></i> PDF Simple
                                        Temporary</button>
                                    <button type="submit" class="btn red" id="view" name="view"
                                        value="excel_simple_temp"><i class="fa fa-file-excel-o"></i> Excel Simple
                                        Temporary</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#tab_1_1" data-toggle="tab">Simpanan Pokok Potong gaji</a>
                        </li>
                        <li>
                            <a href="#tab_1_2" data-toggle="tab">Simpanan Wajib Potong gaji</a>
                        </li>
                        <li>
                            <a href="#tab_1_3" data-toggle="tab">Tabungan Potong gaji</a>
                        </li>
                        <li>
                            <a href="#tab_1_4" data-toggle="tab">Angsuran potong gaji</a>
                        </li>
                        <li>
                            <a href="#tab_1_5" data-toggle="tab">Potong gaji Baru</a>
                        </li>
                    </ul>
                </div>
                <div class="portlet-body">
                    <div class="tab-content">
                        <!-- Simpanan Pokok Potong gaji TAB -->
                        <div class="tab-pane active" id="tab_1_1">

                            <!-- <form role="form" action="#"> -->
                            <table class="table table-striped table-bordered table-hover table-full-width"
                                id="sample_1">
                                <thead>
                                    <tr>
                                        <th style="text-align:center" width="25%">No Anggota</th>
                                        <th style="text-align:center" width="25%">Nama Anggota</th>
                                        <th style="text-align:center" width="20%">Bagian</th>
                                        <th style="text-align:center" width="20%">Tgl Transaksi</th>
                                        <th style="text-align:center" width="25%">Potongan Simpanan Pokok</th>
                                        <th style="text-align:center" width="25%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $principal_savings_total_amount = 0;
                                    if (empty($principal_savings)) {
                                        echo "<tr><td align='center' colspan='5'> Data Kosong !</td></tr>";
                                    } else {
                                        foreach ($principal_savings as $key => $val) {
                                            echo "
													<tr>
														<td>" . $val['member_no'] . "</td>
														<td>" . $val['member_name'] . "</td>
														<td>" . $val['division_name'] . "</td>
														<td>" . $val['transaction_date'] . "</td>
														<td style='text-align:right'>" . number_format($val['principal_savings_amount'], 2) . "</td>
							                            <td><a href='".base_url().'debt-print/delete/salary-principal/'.$val['savings_member_detail_id']."' class='btn red' onclick='return confirmDelete()' role='button'><i class='fa fa-trash'></i> Hapus</a></td>
													</tr>
												";
                                            $no++;
                                            $principal_savings_total_amount += $val['principal_savings_amount'];
                                        }
                                    } ?>
                                </tbody>
                            </table>
                            <div class="margiv-top-10">
                                <button type="submit" class="btn green" id="view" name="view"
                                    value="submit_principal"><i class="fa fa-check"></i>Submit</button>
                                <a href="javascript:;" class="btn default"> Cancel </a>
                            </div>
                            <!-- </form> -->
                        </div>
                        <!-- END -->

                        <!-- Simpanan Wajib Potong gaji TAB -->
                        <div class="tab-pane" id="tab_1_2">
                        <table class="table table-striped table-bordered table-hover table-full-width"
                                id="sample_2">
                                <thead>
                                    <tr>
                                        <th style="text-align:center" width="25%">No Anggota</th>
                                        <th style="text-align:center" width="25%">Nama Anggota</th>
                                        <th style="text-align:center" width="20%">Bagian</th>
                                        <th style="text-align:center" width="20%">Tgl Transaksi</th>
                                        <th style="text-align:center" width="25%">Potongan Simpanan Wajib</th>
                                        <th style="text-align:center" width="25%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    $mandatory_savings_total_amount = 0;
                                    if (empty($mandatory_savings)) {
                                        echo "<tr><td align='center' colspan='5'> Data Kosong !</td></tr>";
                                    } else {
                                        foreach ($mandatory_savings as $key => $val) {
                                            echo "
													<tr>
														<td>" . $val['member_no'] . "</td>
														<td>" . $val['member_name'] . "</td>
														<td>" . $val['division_name'] . "</td>
														<td>" . $val['transaction_date'] . "</td>
														<td style='text-align:right'>" . number_format($val['mandatory_savings_amount'], 2) . "</td>
							                            <td><a href='".base_url().'debt-print/delete/salary-mandatory/'.$val['savings_member_detail_id']."' class='btn red' onclick='return confirmDelete()' role='button'><i class='fa fa-trash'></i> Hapus</a></td>
													</tr>
												";
                                            $i++;
                                            $mandatory_savings_total_amount += $val['mandatory_savings_amount'];
                                        }
                                    } ?>
                                </tbody>
                            </table>
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">No. Perkiraan<span class="required"> *</span></label>
                                    <div class="col-md-6">
                                        <!-- Dropdown untuk No. Perkiraan -->
											<?php echo form_dropdown('account_id', $acctaccount,set_value('account_id',$data['account_id']),'id="account_id" class="form-control select2me"');?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Sandi</label>
                                    <div class="col-md-6">
                                        <!-- Dropdown untuk Sandi -->
											<?php echo form_dropdown('mutation_id', $acctmutation, set_value('mutation_id', 14),'id="mutation_id" class="form-control select2me" readonly');?>
                                    </div>
                                </div>
                                <input type="hidden" name="mutation_function" id="mutation_function" value="+" readonly/>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Total Simpanan Wajib</label>
                                    <div class="col-md-6">
                                        <!-- Input untuk Total Simpanan Wajib -->
                                        <input type="text" class="form-control" name="member_mandatory_savings_view" id="member_mandatory_savings_view" value="<?php echo number_format($mandatory_savings_total_amount, 2) ?>" readonly/>
                                        <input type="hidden" class="form-control" name="member_mandatory_savings" id="member_mandatory_savings" value="<?php echo $mandatory_savings_total_amount ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Keterangan</label>
                                    <div class="col-md-6">
                                        <!-- Textarea untuk Keterangan -->
                                        <textarea rows="3" name="savings_member_detail_remark" id="savings_member_detail_remark" class="form-control"><?php echo 'SETORAN SIMP WAJIB POTONG GAJI '; ?></textarea>
                                    </div>
                                </div>
                            </div>
                           

								<input type="hidden" class="form-control" name="member_token_edit" id="member_token_edit" placeholder="id" value="<?php echo $token;?>"/>
                                <div class="margiv-top-10">
                                <button type="submit" class="btn green" id="view" name="view"
                                    value="submit_mandatory"><i class="fa fa-check"></i>Submit</button>
                                <a href="javascript:;" class="btn default"> Cancel </a>
                            </div>
                        </div>
                        <!-- END TAB -->

                        <!-- Tabungan Potong gaji TAB -->
                        <div class="tab-pane" id="tab_1_3">
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
											<th style="text-align:center" width="15%">Aksi</th>
										</tr>
									</thead>
									<tbody>
										<?php 
										$no=1;
										$savings_amount_total = 0;
										if(empty($savings_salary_mutation)){
											echo "<tr><td align='center' colspan='5'> Data Kosong !</td></tr>";
										} else {
											foreach ($savings_salary_mutation as $key=>$val){ 
												echo"
													<tr>
														<td style='text-align:center'>".$no."</td>
														<td>".$val['savings_account_no']."</td>
														<td>".$val['savings_name']."</td>
														<td>".$val['member_no']."</td>
														<td>".$val['member_name']."</td>
														<td>".$val['division_name']."</td>
														<td style='text-align:right'>".number_format($val['savings_cash_mutation_amount'], 2)."</td>
							                            <td><a href='".base_url().'debt-print/delete/savings-salary-mutation/'.$val['savings_cash_mutation_id']."' class='btn red' onclick='return confirmDelete()' role='button'><i class='fa fa-trash'></i> Hapus</a></td>
                                                    </tr>
												";
												$no++;
												$savings_amount_total += $val['savings_cash_mutation_amount'];
											}
										} ?>
									</tbody>
								</table>
                                <div class="margin-top-10">
                                <button type="submit" class="btn green" id="view" name="view"
                                    value="submit_salary_savings"><i class="fa fa-check"></i>Submit</button>
                                <a href="javascript:;" class="btn default"> Cancel </a>
                                </div>
                        </div>
                        <!-- END CHANGE PASSWORD TAB -->

                        <!-- Angsuran potong gaji TAB -->
                        <div class="tab-pane" id="tab_1_4">
                            <form action="#">
                            <table class="table table-striped table-bordered table-hover table-full-width" id="sample_3">
										<thead>
											<tr>
												<th style="text-align:center" width="5%">No</th>
												<th style="text-align:center" width="10%">No Perjanjian Kredit</th>
												<th style="text-align:center" width="10%">No Anggota</th>
												<th style="text-align:center" width="15%">Nama Anggota</th>
												<th style="text-align:center" width="15%">Jenis Pinjaman</th>
												<th style="text-align:center" width="15%">Angsuran Pokok</th>
												<th style="text-align:center" width="15%">Angsuran Bunga</th>
												<th style="text-align:center" width="15%">Subtotal Angsuran</th>
											</tr>
										</thead>
										<tbody>
											<?php 
											$no=1;
											$payment_total_amount = 0;

											if(empty($salary_payments)){
												echo "<tr><td align='center' colspan='8'> Data Kosong !</td></tr>";
											} else {
                                                foreach ($salary_payments as $key=>$val){ 
													echo"
														<tr>
															<td style='text-align:center'>".$no."</td>
															<td>".$val['credits_account_serial']."</td>
															<td>".$val['member_no']."</td>
															<td>".$val['member_name']."</td>
															<td>".$val['credits_name']."</td>
															<td style='text-align:right'>".number_format($val['credits_payment_principal'], 2)."</td>
															<td style='text-align:right'>".number_format($val['credits_payment_interest'], 2)."</td>
															<td style='text-align:right'>".number_format($val['credits_payment_principal']+$val['credits_payment_interest'], 2)."</td>
														</tr>
													";
													$no++;
													// $payment_total_amount += $angsuranpokok+$angsuranbunga;
												}
                                            } ?>
										</tbody>
									</table>
                                <div class="margin-top-10">
                                <button type="submit" class="btn green" id="view" name="view"
                                    value="submit_salary_payments"><i class="fa fa-check"></i>Submit</button>
                                <a href="javascript:;" class="btn default"> Cancel </a>
                                </div>
                            </form>
                        </div>
                        <!-- END PRIVACY SETTINGS TAB -->

                        <!-- Potong gaji Baru TAB -->
                        <div class="tab-pane" id="tab_1_5">
                            <form action="#">
                            <table class="table table-striped table-bordered table-hover table-full-width"
                                id="sample_5">
                                <thead>
                                    <tr>
                                        <th style="text-align:center" width="5%">No</th>
                                        <th style="text-align:center" width="25%">No Anggota</th>
                                        <th style="text-align:center" width="25%">Nama Anggota</th>
                                        <th style="text-align:center" width="20%">Bagian</th>
                                        <th style="text-align:center" width="20%">Tgl Transaksi</th>
                                        <th style="text-align:center" width="25%">Potongan Simpanan Wajib</th>
                                        <th style="text-align:center" width="25%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $mandatory_savings_total_amount = 0;
                                    if (empty($mandatory_savings)) {
                                        echo "<tr><td align='center' colspan='5'> Data Kosong !</td></tr>";
                                    } else {
                                        foreach ($mandatory_savings as $key => $val) {
                                            echo "
													<tr>
														<td style='text-align:center'>" . $no . "</td>
														<td>" . $val['member_no'] . "</td>
														<td>" . $val['member_name'] . "</td>
														<td>" . $val['division_name'] . "</td>
														<td>" . $val['transaction_date'] . "</td>
														<td style='text-align:right'>" . number_format($val['mandatory_savings_amount'], 2) . "</td>
							                            <td><a href='".base_url().'debt/delete/'.$val['savings_member_detail_id']."' class='btn red' onclick='return confirmDelete()' role='button'><i class='fa fa-trash'></i> Hapus</a></td>
													</tr>
												";
                                            $no++;
                                            $mandatory_savings_total_amount += $val['mandatory_savings_amount'];
                                        }
                                    } ?>
                                </tbody>
                            </table>
                                <!--end profile-settings-->
                                <div class="margin-top-10">
                                    <a href="javascript:;" class="btn red"> Save Changes </a>
                                    <a href="javascript:;" class="btn default"> Cancel </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php echo form_close(); ?>
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
                                id="sample_3">
                                <thead>
                                    <tr>
                                        <th style="text-align:center" width="5%">No</th>
                                        <th style="text-align:center" width="25%">No Anggota</th>
                                        <th style="text-align:center" width="25%">Nama Anggota</th>
                                        <th style="text-align:center" width="20%">Bagian</th>
                                        <th style="text-align:center" width="25%">Potongan Simpanan Pokok</th>
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
														<td style='text-align:center'>" . $no . "</td>
														<td>" . $val['member_no'] . "</td>
														<td>" . $val['member_name'] . "</td>
														<td>" . $val['division_name'] . "</td>
														<td style='text-align:right'>" . number_format($val['member_principal_savings'], 2) . "</td>
													</tr>
												";
                                            $no++;
                                            $principal_savings_total_amount += $val['member_principal_savings'];
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

                            <div class="margin-top-10">
                                <a href="javascript:;" class="btn green"> Submit </a>
                                <a href="javascript:;" class="btn default"> Cancel </a>
                            </div>
                            </form>
                        </div>
                        <!-- END TAB -->

                        <!-- Tabungan Potong gaji TAB -->
                        <div class="tab-pane" id="tab_1_3">
                            <form action="#">

                                <div class="margin-top-10">
                                    <a href="javascript:;" class="btn green"> Change Password </a>
                                    <a href="javascript:;" class="btn default"> Cancel </a>
                                </div>
                            </form>
                        </div>
                        <!-- END CHANGE PASSWORD TAB -->

                        <!-- Angsuran potong gaji TAB -->
                        <div class="tab-pane" id="tab_1_4">
                            <form action="#">

                                <!--end profile-settings-->
                                <div class="margin-top-10">
                                    <a href="javascript:;" class="btn red"> Save Changes </a>
                                    <a href="javascript:;" class="btn default"> Cancel </a>
                                </div>
                            </form>
                        </div>
                        <!-- END PRIVACY SETTINGS TAB -->

                        <!-- Potong gaji Baru TAB -->
                        <div class="tab-pane" id="tab_1_5">
                            <form action="#">

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
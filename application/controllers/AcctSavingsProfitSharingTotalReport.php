<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	ini_set('memory_limit', '256M');
	ini_set('max_execution_time', 600);
	Class AcctSavingsProfitSharingTotalReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctSavingsProfitSharingNew_model');
			$this->load->model('AcctDepositoProfitSharingReport_model');
			$this->load->model('AcctDailyAverageBalanceCalculate_model');
			$this->load->model('AcctSavingsProfitSharingReport_model');
			$this->load->model('AcctSavingsIndex_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data['main_view']['month']				= $this->configuration->Month();
			$data['main_view']['corebranch']	    = create_double($this->AcctDepositoProfitSharingReport_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']			= 'AcctSavingsProfitSharingReport/ListTotalAcctSavingsProfitSharingNew_view';
			$this->load->view('MainPage_view',$data);
		}

		public function viewport() {
            $sesi = array (
				"start_date" 	=> tgltodb($this->input->post('start_date',true)),
				"end_date"		=> tgltodb($this->input->post('end_date', true)),
				"branch_id"		=> $this->input->post('branch_id',true),
				"view"			=> $this->input->post('view',true),
			);

			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
        }

        
        public function processPrinting($sesi) {
            $auth = $this->session->userdata('auth');
            
            // Determine branch_id based on branch_status
            if ($auth['branch_status'] == 1) {
                $branch_id = !empty($sesi['branch_id']) ? $sesi['branch_id'] : '';
            } else {
                $branch_id = $auth['branch_id'];
            }
        
            // Retrieve data from model
            $savingsReport = $this->AcctSavingsProfitSharingNew_model->getSavingsProfitSharingReport($branch_id, $sesi['start_date'], $sesi['end_date']);
            $depositoReport = $this->AcctSavingsProfitSharingNew_model->getDepositoProfitSharingReport($branch_id, $sesi['start_date'], $sesi['end_date']);
            $preference = $this->AcctSavingsProfitSharingReport_model->getPreferenceCompany();
        
            // Check if there is any data to display
            if (empty($savingsReport) && empty($depositoReport)) {
                // No data available for both reports
                echo "Tidak ada data untuk laporan yang dipilih.";
                return;
            }
        
            // Merge savings and deposito reports by member_id
            $combinedReport = [];
            $subtotal_savings = 0;
            $subtotal_deposito = 0;
        
            foreach ($savingsReport as $savings) {
                if (!isset($combinedReport[$savings['member_id']])) {
                    $combinedReport[$savings['member_id']] = [
                        'member_no' => $savings['member_no'],
                        'member_name' => $savings['member_name'],
                        'member_address' => $savings['member_address'],
                        'savings' => [],
                        'deposito' => [],
                        'savings_total' => 0,
                        'deposito_total' => 0,
                        'savings_tax' => 0,
                        'deposito_tax' => 0
                    ];
                }
                $combinedReport[$savings['member_id']]['savings'][] = [
                    'savings_name' => $savings['savings_name'],
                    'savings_account_no' => $savings['savings_account_no'],
                    'savings_profit_sharing_temp_amount' => $savings['savings_profit_sharing_temp_amount'],
                ];
                $combinedReport[$savings['member_id']]['savings_total'] += $savings['savings_profit_sharing_temp_amount'];
                $subtotal_savings += $savings['savings_profit_sharing_temp_amount'];
            }
        
            foreach ($depositoReport as $deposito) {
                if (!isset($combinedReport[$deposito['member_id']])) {
                    $combinedReport[$deposito['member_id']] = [
                        'member_no' => $deposito['member_no'],
                        'member_name' => $deposito['member_name'],
                        'member_address' => $deposito['member_address'],
                        'savings' => [],
                        'deposito' => [],
                        'savings_total' => 0,
                        'deposito_total' => 0,
                        'savings_tax' => 0,
                        'deposito_tax' => 0
                    ];
                }
                $combinedReport[$deposito['member_id']]['deposito'][] = [
                    'deposito_name' => $deposito['deposito_name'],
                    'deposito_account_no' => $deposito['deposito_account_no'],
                    'deposito_profit_sharing_amount' => $deposito['deposito_profit_sharing_amount'],
                ];
                $combinedReport[$deposito['member_id']]['deposito_total'] += $deposito['deposito_profit_sharing_amount'];
                $subtotal_deposito += $deposito['deposito_profit_sharing_amount'];
            }
        
            // Calculate tax per member
            foreach ($combinedReport as &$data) {
                $total_member = $data['savings_total'] + $data['deposito_total'];
                if ($total_member > $preference['tax_minimum_amount']) {
                    $data['savings_tax'] = $data['savings_total'] * $preference['tax_percentage'] / 100;
                    $data['deposito_tax'] = $data['deposito_total'] * $preference['tax_percentage'] / 100;
                }
            }
        
            // Include TCPDF library
            require_once('tcpdf/config/tcpdf_config.php');
            require_once('tcpdf/tcpdf.php');
        
            // Create new TCPDF object
            $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
        
            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('KME');
            $pdf->SetTitle('Laporan Bunga Simpanan Dan Deposito Bulanan');
            $pdf->SetSubject('Laporan Bunga Simpanan Dan Deposito Bulanan');
            $pdf->SetKeywords('TCPDF, PDF, report, savings, deposito');
        
            // Set margins
            $pdf->SetMargins(7, 7, 7, true);
        
            // Remove header and footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
        
            // Set auto page breaks
            $pdf->SetAutoPageBreak(true, 7);
        
            // Set font
            $pdf->SetFont('helvetica', '', 9);
        
            // Add a page for the combined report
            $pdf->AddPage();
            $pdf->SetTitle('Laporan Simpanan dan Deposito');
        
            $base_url = base_url();
            $img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preference['logo_koperasi'] . "\" alt=\"\" width=\"100\" height=\"50\"/>";
            $html = '
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td rowspan="2">' . $img . '</td>
                    </tr>
                    <tr></tr>
                </table>
                <br/><br/><br/><br/>
                <table cellspacing="0" cellpadding="1" border="0">
                    <tr>
                        <td width="100%"><div style="text-align: left; font-size:14px; font-weight:bold">Laporan Simpanan dan Deposito Bulanan</div></td>
                    </tr>
                </table>
            ';
        
            $pdf->writeHTML($html, true, false, false, false, '');
        
            // Table header for combined report
            $html = '
                <br>
                <table border="1" cellpadding="3" cellspacing="0" width="100%">
                        <tr>
                            <th>No.</th>
                            <th>No. Anggota</th>
                            <th>Nama Anggota</th>
                            <th>Alamat Anggota</th>
                            <th>Jenis Simpanan</th>
                            <th>Nomor Simpanan</th>
                            <th>Bunga Simpanan</th>
                            <th>Nama Deposito</th>
                            <th>Nomor Akun Deposito</th>
                            <th>Bunga Deposito</th>
                        </tr>
            ';
        
            // Data rows for combined report
            $no = 1; // Initializing numbering
            foreach ($combinedReport as $data) {
                $savings = isset($data['savings']) ? $data['savings'] : [];
                $deposito = isset($data['deposito']) ? $data['deposito'] : [];
                
                $savingsCount = count($savings);
                $depositoCount = count($deposito);
        
                for ($i = 0; $i < max($savingsCount, $depositoCount); $i++) {
                    $savings_row = isset($savings[$i]) ? $savings[$i] : [];
                    $deposito_row = isset($deposito[$i]) ? $deposito[$i] : [];
                    
                    $html .= '<tr>';
                    if ($i == 0) {
                        $html .= '
                            <td>' . $no . '</td>
                            <td>' . $data['member_no'] . '</td>
                            <td>' . $data['member_name'] . '</td>
                            <td>' . $data['member_address'] . '</td>
                        ';
                    } else {
                        $html .= '<td></td><td></td><td></td>';
                    }
        
                    if ($i < $savingsCount) {
                        $html .= '
                            <td>' . $savings_row['savings_name'] . '</td>
                            <td>' . $savings_row['savings_account_no'] . '</td>
                            <td>' . number_format($savings_row['savings_profit_sharing_temp_amount'], 2) . '</td>
                        ';
                    } else {
                        $html .= '<td></td><td></td><td></td><td></td>';
                    }
        
                    if ($i < $depositoCount) {
                        $html .= '
                            <td>' . $deposito_row['deposito_name'] . '</td>
                            <td>' . $deposito_row['deposito_account_no'] . '</td>
                            <td>' . number_format($deposito_row['deposito_profit_sharing_amount'], 2) . '</td>
                        ';
                    } else {
                        $html .= '<td></td><td></td><td></td>';
                    }
        
                    $html .= '</tr>';
                }
        
                // Add subtotal and tax rows for each member
                $total_member = $data['savings_total'] + $data['deposito_total'];
                $tax = $data['savings_tax'] + $data['deposito_tax'];
                $total_with_tax = $total_member - $tax;
        
                $taxBackgroundColor = ($tax > 0) ? '#FF0000' : '#f5f5dc'; // Set background color to red if tax > 0
        
                $html .= '
                    <tr style="background-color: #f5f5dc;"> <!-- Cream color -->
                        <td colspan="6" style="text-align:right; font-weight:bold;">Total Bunga Simpanan:</td>
                        <td>' . number_format($data['savings_total'], 2) . '</td>
                        <td colspan="3"></td>
                    </tr>
                    <tr style="background-color: #f5f5dc;"> <!-- Cream color -->
                        <td colspan="6" style="text-align:right; font-weight:bold;">Total Bunga Deposito:</td>
                        <td colspan="3">' . number_format($data['deposito_total'], 2) . '</td>
                    </tr>
                    <tr style="background-color: #e0e0e0;">
                        <td colspan="6" style="text-align:right; font-weight:bold;">Subtotal:</td>
                        <td>' . number_format($data['savings_total'], 2) . '</td>
                        <td>' . number_format($data['deposito_total'], 2) . '</td>
                        <td></td>
                    </tr>
                    <tr style="background-color: #e0e0e0;">
                        <td colspan="9" style="text-align:right; font-weight:bold;">Subtotal Total:</td>
                        <td>' . number_format($total_member, 2) . '</td>
                    </tr>
                    <tr style="background-color: ' . $taxBackgroundColor . ';">
                        <td colspan="9" style="text-align:right; font-weight:bold;">Pajak:</td>
                        <td>' . number_format($tax, 2) . '</td>
                    </tr>
                    <tr style="background-color: #f5f5dc;">
                        <td colspan="9" style="text-align:right; font-weight:bold;">Total Setelah Pajak:</td>
                        <td>' . number_format($total_with_tax, 2) . '</td>
                    </tr>
                ';
        
                $no++; // Increment numbering
            }
        
            // Close table
            $html .= '
                </table>
            ';
        
            // Write the HTML content to PDF
            $pdf->writeHTML($html, true, false, true, false, '');
        
            // Close and output PDF document
            $pdf->Output('laporan.pdf', 'I');
        }
        

        // Your function here
        
        public function export($sesi) {
            $auth = $this->session->userdata('auth');
        
            // Determine branch_id based on branch_status
            if ($auth['branch_status'] == 1) {
                $branch_id = !empty($sesi['branch_id']) ? $sesi['branch_id'] : '';
            } else {
                $branch_id = $auth['branch_id'];
            }
        
            // Retrieve data from model
            $savingsReport = $this->AcctSavingsProfitSharingNew_model->getSavingsProfitSharingReport($branch_id, $sesi['start_date'], $sesi['end_date']);
            $depositoReport = $this->AcctSavingsProfitSharingNew_model->getDepositoProfitSharingReport($branch_id, $sesi['start_date'], $sesi['end_date']);
            $preference = $this->AcctSavingsProfitSharingReport_model->getPreferenceCompany();
        
            // Check if there is any data to display
            if (empty($savingsReport) && empty($depositoReport)) {
                echo "Tidak ada data untuk laporan yang dipilih.";
                return;
            }
        
            // Merge savings and deposito reports by member_id
            $combinedReport = [];
            $subtotal_savings = 0;
            $subtotal_deposito = 0;
        
            foreach ($savingsReport as $savings) {
                if (!isset($combinedReport[$savings['member_id']])) {
                    $combinedReport[$savings['member_id']] = [
                        'member_no' => $savings['member_no'],
                        'member_name' => $savings['member_name'],
                        'member_address' => $savings['member_address'],
                        'savings' => [],
                        'deposito' => [],
                        'savings_total' => 0,
                        'deposito_total' => 0,
                        'savings_tax' => 0,
                        'deposito_tax' => 0
                    ];
                }
                $combinedReport[$savings['member_id']]['savings'][] = [
                    'savings_name' => $savings['savings_name'],
                    'savings_account_no' => $savings['savings_account_no'],
                    'savings_profit_sharing_temp_amount' => $savings['savings_profit_sharing_temp_amount'],
                ];
                $combinedReport[$savings['member_id']]['savings_total'] += $savings['savings_profit_sharing_temp_amount'];
                $subtotal_savings += $savings['savings_profit_sharing_temp_amount'];
            }
        
            foreach ($depositoReport as $deposito) {
                if (!isset($combinedReport[$deposito['member_id']])) {
                    $combinedReport[$deposito['member_id']] = [
                        'member_no' => $deposito['member_no'],
                        'member_name' => $deposito['member_name'],
                        'member_address' => $deposito['member_address'],
                        'savings' => [],
                        'deposito' => [],
                        'savings_total' => 0,
                        'deposito_total' => 0,
                        'savings_tax' => 0,
                        'deposito_tax' => 0
                    ];
                }
                $combinedReport[$deposito['member_id']]['deposito'][] = [
                    'deposito_name' => $deposito['deposito_name'],
                    'deposito_account_no' => $deposito['deposito_account_no'],
                    'deposito_profit_sharing_amount' => $deposito['deposito_profit_sharing_amount'],
                ];
                $combinedReport[$deposito['member_id']]['deposito_total'] += $deposito['deposito_profit_sharing_amount'];
                $subtotal_deposito += $deposito['deposito_profit_sharing_amount'];
            }
        
            // Calculate tax per member
            foreach ($combinedReport as &$data) {
                $total_member = $data['savings_total'] + $data['deposito_total'];
                if ($total_member > $preference['tax_minimum_amount']) {
                    $data['savings_tax'] = $data['savings_total'] * $preference['tax_percentage'] / 100;
                    $data['deposito_tax'] = $data['deposito_total'] * $preference['tax_percentage'] / 100;
                }
            }
        
            // Load PHPExcel library
            $this->load->library('Excel');
            $this->excel->setActiveSheetIndex(0);
            $sheet = $this->excel->getActiveSheet();
            $sheet->setTitle('Laporan Simpanan dan Deposito');
        
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(40);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(20);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);
        
            // Set title
            $sheet->mergeCells('A1:J1');
            $sheet->setCellValue('A1', 'Laporan Simpanan dan Deposito Bulanan');
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
            // Set table headers
            $headers = ['No.', 'No. Anggota', 'Nama Anggota', 'Alamat Anggota', 'Nama Simpanan', 'Nomor Simpanan', 'Bunga Simpanan', 'Nama Deposito', 'Nomor Akun Deposito', 'Bunga Deposito'];
            $sheet->fromArray($headers, null, 'A3');
            $sheet->getStyle('A3:J3')->getFont()->setBold(true);
            $sheet->getStyle('A3:J3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet->getStyle('A3:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
            // Fill data
            $row = 4; // Start from the 4th row
            $no = 1;
            foreach ($combinedReport as $data) {
                $savings = isset($data['savings']) ? $data['savings'] : [];
                $deposito = isset($data['deposito']) ? $data['deposito'] : [];
        
                $savingsCount = count($savings);
                $depositoCount = count($deposito);
        
                for ($i = 0; $i < max($savingsCount, $depositoCount); $i++) {
                    $savings_row = isset($savings[$i]) ? $savings[$i] : [];
                    $deposito_row = isset($deposito[$i]) ? $deposito[$i] : [];
        
                    $sheet->setCellValue('A' . $row, $i == 0 ? $no : '');
                    $sheet->setCellValue('B' . $row, $i == 0 ? $data['member_no'] : '');
                    $sheet->setCellValue('C' . $row, $i == 0 ? $data['member_name'] : '');
                    $sheet->setCellValue('D' . $row, $i == 0 ? $data['member_address'] : '');
        
                    if ($i < $savingsCount) {
                        $sheet->setCellValue('E' . $row, $savings_row['savings_name']);
                        $sheet->setCellValue('F' . $row, $savings_row['savings_account_no']);
                        $sheet->setCellValue('G' . $row, $savings_row['savings_profit_sharing_temp_amount']);
                    } else {
                        $sheet->setCellValue('E' . $row, '');
                        $sheet->setCellValue('F' . $row, '');
                        $sheet->setCellValue('G' . $row, '');
                    }
        
                    if ($i < $depositoCount) {
                        $sheet->setCellValue('H' . $row, $deposito_row['deposito_name']);
                        $sheet->setCellValue('I' . $row, $deposito_row['deposito_account_no']);
                        $sheet->setCellValue('J' . $row, $deposito_row['deposito_profit_sharing_amount']);
                    } else {
                        $sheet->setCellValue('H' . $row, '');
                        $sheet->setCellValue('I' . $row, '');
                        $sheet->setCellValue('J' . $row, '');
                    }
        
                    $row++;
                }
        
                // Add subtotal and tax rows for each member
                $total_member = $data['savings_total'] + $data['deposito_total'];
                $tax = $data['savings_tax'] + $data['deposito_tax'];
        
                // Subtotal row
                $sheet->setCellValue('F' . $row, 'Subtotal');
                $sheet->setCellValue('G' . $row, $data['savings_total']);
                $sheet->setCellValue('I' . $row, 'Subtotal');
                $sheet->setCellValue('J' . $row, $data['deposito_total']);
        
                // Remove yellow background for subtotal row
                $sheet->getStyle('F' . $row . ':J' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_NONE);
        
                $row++;
        
                // Tax row (if applicable)
                if ($tax > 0) {
                    // $sheet->setCellValue('F' . $row, 'Tax');
                    // $sheet->setCellValue('G' . $row, $data['savings_tax']);
                    $sheet->setCellValue('I' . $row, 'Tax');
                    $sheet->setCellValue('J' . $row, $tax);
        
                    // Apply red background for tax row
                    // $sheet->getStyle('F' . $row . ':G' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    // $sheet->getStyle('F' . $row . ':G' . $row)->getFill()->getStartColor()->setRGB('FF0000');
                    $sheet->getStyle('A' . $row . ':J' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $sheet->getStyle('A' . $row . ':J' . $row)->getFill()->getStartColor()->setRGB('FF0000');
        
                    $row++;
                }
        
                $no++;
            }
        
            // Set column auto size
            foreach (range('A', 'J') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
        
            // Set borders for the entire data range
            $sheet->getStyle('A4:J' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            // Export to Excel
            $filename = 'Laporan_Simpanan_Dan_Deposito_Bulanan_' . date('Ymd') . '.xls';
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
        
            $objWriter = IOFactory::createWriter($this->excel, 'Excel5');
			ob_end_clean();

            $objWriter->save('php://output');
        }
        
        
        
    
	}	
?>
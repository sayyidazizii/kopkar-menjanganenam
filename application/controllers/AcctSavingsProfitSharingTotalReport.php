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
            $acctsavingsprofitsharing = $this->AcctSavingsProfitSharingNew_model->getCombinedProfitSharingReport($branch_id, $sesi['start_date'], $sesi['end_date']);
            $preference = $this->AcctSavingsProfitSharingReport_model->getPreferenceCompany();
            
            // Include TCPDF library
            require_once('tcpdf/config/tcpdf_config.php');
            require_once('tcpdf/tcpdf.php');
            
            // Create new TCPDF object
            $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Your Name');
            $pdf->SetTitle('DAFTAR BUNGA TABUNGAN BULANAN');
            $pdf->SetSubject('DAFTAR BUNGA TABUNGAN BULANAN');
            $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
            
            // Set margins
            $pdf->SetMargins(7, 7, 7, true);
            
            // Remove header and footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(true, 7);
            
            // Set font
            $pdf->SetFont('helvetica', '', 9);
            
            // Add a page
            $pdf->AddPage();
            
            // Logo and title
            $base_url = base_url();
            $img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preference['logo_koperasi'] . "\" alt=\"\" width=\"100\" height=\"50\"/>";
            $html = '
            <table cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td rowspan="2">' . $img . '</td>
                </tr>
                <tr></tr>
            </table>
            <br/>
            <br/>
            <br/>
            <br/>
            <table cellspacing="0" cellpadding="1" border="0">
                <tr>
                    <td width="100%"><div style="text-align: left; font-size:14px; font-weight:bold">DAFTAR BUNGA TABUNGAN BULANAN</div></td>
                </tr>
            </table>
            ';
            
            // Output logo and title
            $pdf->writeHTML($html, true, false, false, false, '');
            
            // Menghitung jumlah baris untuk setiap member_id
            $rowspan_data = [];
            foreach ($acctsavingsprofitsharing as $data) {
                if (!isset($rowspan_data[$data['member_id']])) {
                    $rowspan_data[$data['member_id']] = 0;
                }
                $rowspan_data[$data['member_id']]++;
                if (!empty($data['deposito_account_no'])) {
                    $rowspan_data[$data['member_id']]++;
                }
            }
        
            // Table header
            $html = '
            <br>
            <table border="1" cellpadding="3" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No. Anggota</th>
                        <th>Nama Anggota</th>
                        <th>Alamat Anggota</th>
                        <th>Jenis</th>
                        <th>Nomor Simpanan</th>
                        <th>Bunga</th>
                    </tr>
                </thead>
                <tbody>
            ';
            
            // Data rows
            $previous_member_id = null;
            foreach ($acctsavingsprofitsharing as $data) {
                $html .= '<tr>';
                if ($data['member_id'] != $previous_member_id) {
                    $html .= '
                        <td rowspan="' . $rowspan_data[$data['member_id']] . '">' . $data['member_no'] . '</td>
                        <td rowspan="' . $rowspan_data[$data['member_id']] . '">' . $data['member_name'] . '</td>
                        <td rowspan="' . $rowspan_data[$data['member_id']] . '">' . $data['member_address'] . '</td>
                    ';
                    $previous_member_id = $data['member_id'];
                }
                
                $html .= '
                    <td>' . $data['savings_name'] . '</td>
                    <td>' . $data['savings_account_no'] . '</td>
                    <td>' . number_format($data['savings_profit_sharing_temp_amount'], 2) . '</td>
                ';
                $html .= '</tr>';

                
                // if (!empty($data['deposito_account_no'])) {
                    $html .= '<tr>';
                    $html .= '
                        <td>' . $data['deposito_name'] . '</td>
                        <td>' . $data['deposito_account_no'] . '</td>
                        <td>' . number_format($data['deposito_profit_sharing_amount'], 2) . '</td>
                    ';
                    $html .= '</tr>';
                // }
            }
            
            // Close table
            $html .= '
                </tbody>
            </table>
            ';
            
            // Write the HTML content to PDF
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Close and output PDF document
            $pdf->Output('laporan.pdf', 'I');
        }
    
	}	
?>
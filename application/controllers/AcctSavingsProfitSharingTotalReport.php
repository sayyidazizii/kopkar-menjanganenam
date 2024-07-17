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
				"view"				=> $this->input->post('view',true),
			);

			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
        }

        public function processPrinting($sesi) {
            $auth = $this->session->userdata('auth'); 
        
            if ($auth['branch_status'] == 1) {
                $branch_id = $sesi['branch_id'] ?: '';
            } else {
                $branch_id = $auth['branch_id'];
            }
        
            $acctsavingsprofitsharing = $this->AcctSavingsProfitSharingNew_model->getAcctSavingsProfitSharingTemp($auth['branch_id']);
            $acctdepositoprofitsharing = $this->AcctDepositoProfitSharingReport_model->getAcctDepositoProfitSharingAll($sesi['start_date'], $sesi['end_date'], $branch_id);
            $preference = $this->AcctDepositoProfitSharingReport_model->getPreferenceCompany();
        
            // Group by member_id
            $groupedData = [];
            foreach ($acctsavingsprofitsharing as $saving) {
                $member_id = $saving['member_id'];
                if (!isset($groupedData[$member_id])) {
                    $groupedData[$member_id] = [
                        'savings_account_no' => $saving['savings_account_no'],
                        'member_no' => $saving['member_no'],
                        'member_name' => $saving['member_name'],
                        'savings' => [],
                    ];
                }
                $groupedData[$member_id]['savings'][] = [
                    'last_balance' => $saving['savings_account_last_balance'],
                    'profit_sharing_amount' => $saving['savings_profit_sharing_temp_amount'],
                ];
            }
        
            // Create Savings Report
            $this->generateSavingsReport($groupedData, $preference);
        
            // Create Deposits Report
            $this->generateDepositsReport($acctdepositoprofitsharing, $preference);
        }
        
        private function generateSavingsReport($groupedData, $preference) {
            require_once('tcpdf/config/tcpdf_config.php');
            require_once('tcpdf/tcpdf.php');
        
            $pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);
            $pdf->SetMargins(7, 7, 7, 7);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 9);
        
            $base_url = base_url();
            $img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preference['logo_koperasi'] . "\" alt=\"\" width=\"50%\" height=\"50%\"/>";
        
            $tbl = "
            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                <tr>
                    <td rowspan=\"2\" width=\"10%\">" . $img . "</td>
                </tr>
            </table>
            <br/><br/><br/><br/>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td><div style=\"text-align: left;font-size:12;\">DAFTAR BUNGA SIMPANAN BULAN INI</div></td>               
                </tr>                        
            </table>";
        
            $pdf->writeHTML($tbl, true, false, false, false, '');
        
            $tbl1 = "
            <br>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <th width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></th>
                    <th width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Rekening</div></th>
                    <th width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Anggota</div></th>
                    <th width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></th>
                    <th width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Saldo</div></th>
                    <th width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bunga</div></th>
                </tr>                
            </table>";
        
            $tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
            $no = 1;
        
            foreach ($groupedData as $member_id => $data) {
                foreach ($data['savings'] as $saving) {
                    $tbl2 .= "
                    <tr>
                        <td width=\"5%\"><div style=\"text-align: left;\">" . $no . "</div></td>
                        <td width=\"15%\"><div style=\"text-align: left;\">" . $data['savings_account_no'] . "</div></td>
                        <td width=\"15%\"><div style=\"text-align: left;\">" . $data['member_no'] . "</div></td>
                        <td width=\"20%\"><div style=\"text-align: left;\">" . $data['member_name'] . "</div></td>
                        <td width=\"15%\"><div style=\"text-align: right;\">" . number_format($saving['last_balance'], 2) . "</div></td>
                        <td width=\"15%\"><div style=\"text-align: right;\">" . number_format($saving['profit_sharing_amount'], 2) . "</div></td>
                    </tr>";
                    $no++;
                }
            }
        
            $tbl2 .= "</table>";
        
            $pdf->writeHTML($tbl1 . $tbl2, true, false, false, false, '');
        
            ob_clean();
            
            $pdf->Output('DAFTAR BUNGA SIMPANAN BULAN INI.pdf', 'I');
        }
        
        private function generateDepositsReport($acctdepositoprofitsharing, $preference) {
            require_once('tcpdf/config/tcpdf_config.php');
            require_once('tcpdf/tcpdf.php');
        
            $pdf = new tcpdf('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);
            $pdf->SetMargins(7, 7, 7, 7);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 9);
        
            $base_url = base_url();
            $img = "<img src=\"" . $base_url . "assets/layouts/layout/img/" . $preference['logo_koperasi'] . "\" alt=\"\" width=\"50%\" height=\"50%\"/>";
        
            $tbl = "
            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                <tr>
                    <td rowspan=\"2\" width=\"10%\">" . $img . "</td>
                </tr>
            </table>
            <br/><br/><br/><br/>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td><div style=\"text-align: left;font-size:12;\">DAFTAR BUNGA DEPOSITO BULAN INI</div></td>               
                </tr>                        
            </table>";
        
            $pdf->writeHTML($tbl, true, false, false, false, '');
        
            $tbl1 = "
            <br>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <th width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></th>
                    <th width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Anggota</div></th>
                    <th width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></th>
                    <th width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Saldo</div></th>
                    <th width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bunga</div></th>
                    <th width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Deposito</div></th>
                </tr>                
            </table>";
        
            $tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
            $no = 1;
        
            foreach ($acctdepositoprofitsharing as $deposit) {
                $tbl2 .= "
                <tr>
                    <td width=\"5%\"><div style=\"text-align: left;\">" . $no . "</div></td>
                    <td width=\"15%\"><div style=\"text-align: left;\">" . $deposit['member_no'] . "</div></td>
                    <td width=\"20%\"><div style=\"text-align: left;\">" . $deposit['member_name'] . "</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">" . number_format($deposit['deposito_account_last_balance'], 2) . "</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">" . number_format($deposit['deposito_profit_sharing_amount'], 2) . "</div></td>
                    <td width=\"15%\"><div style=\"text-align: left;\">" . $deposit['deposito_account_no'] . "</div></td>
                </tr>";
                $no++;
            }
        
            $tbl2 .= "</table>";
        
            $pdf->writeHTML($tbl1 . $tbl2, true, false, false, false, '');
        
            ob_clean();
            
            $pdf->Output('DAFTAR BUNGA DEPOSITO BULAN INI.pdf', 'I');
        }
        
        

		
	}	
?>
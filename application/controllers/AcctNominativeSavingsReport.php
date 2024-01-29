<?php ob_start(); ?>
<?php
	ini_set('memory_limit', '512M');
	defined('BASEPATH') OR exit('No direct script access allowed');

	Class AcctNominativeSavingsReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctNominativeSavingsReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$corebranch 									= create_double_branch($this->AcctNominativeSavingsReport_model->getCoreBranch(),'branch_id','branch_name');
			$corebranch[0] 									= 'Semua Cabang';
			ksort($corebranch);
			$data['main_view']['corebranch']				= $corebranch;
			$data['main_view']['acctsavings']				= create_double($this->AcctNominativeSavingsReport_model->getAcctSavings(),'savings_id','savings_name');
			$data['main_view']['kelompoklaporansimpanan']	= $this->configuration->KelompokLaporanSimpanan();	
			$data['main_view']['content']					= 'AcctNominativeSavingsReport/ListAcctNominativeSavingsReport_view';
			$this->load->view('MainPage_view',$data);
		}
 
		public function viewreport(){
			$sesi = array (
				"branch_id"					=> $this->input->post('branch_id', true),
				"start_date" 				=> tgltodb($this->input->post('start_date',true)),
				"kelompok_laporan_simpanan"	=> $this->input->post('kelompok_laporan_simpanan',true),
				"view"						=> $this->input->post('view',true),
			);

			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
		}

		public function processPrinting($sesi){
			$auth 	=	$this->session->userdata('auth'); 
			$preferencecompany = $this->AcctNominativeSavingsReport_model->getPreferenceCompany();
			// $sesi 	= array(
			// 	"branch_id"					=> $this->input->post('branch_id', true),
			// 	"start_date" 				=> tgltodb($this->input->post('start_date',true)),
			// 	"kelompok_laporan_simpanan"	=> $this->input->post('kelompok_laporan_simpanan',true),
			// );
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}
			$kelompoklaporansimpanan	= $this->configuration->KelompokLaporanSimpanan();	
			$acctsavings 				= $this->AcctNominativeSavingsReport_model->getAcctSavings();
			$period 					= date('mY', strtotime($sesi['start_date']));

			$namaKelompoklaporansimpanan = $kelompoklaporansimpanan[$sesi['kelompok_laporan_simpanan']];

			if($sesi['kelompok_laporan_simpanan'] == 0){
				$acctsavingsaccount 		= $this->AcctNominativeSavingsReport_model->getAcctNomintiveSavingsReport();

				foreach ($acctsavingsaccount as $key => $val) {
					$savings_interest_rate	= $val['savings_interest_rate']/12;
					$savingsinterestrate 	= round($savings_interest_rate,2);

					$acctsavingsprofitsharing 	 		= $this->AcctNominativeSavingsReport_model->getAcctSavingsProfitSharing($val['savings_account_id'], $period, $branch_id);

					if(empty($acctsavingsprofitsharing)){
						$savings_daily_average_balance 	= 0;
						$savings_profit_sharing_amount 	= 0;
						$savings_account_last_balance 	= $val['savings_account_last_balance'];
					} else {
						$savings_daily_average_balance 	= $acctsavingsprofitsharing['savings_daily_average_balance'];
						$savings_profit_sharing_amount 	= $acctsavingsprofitsharing['savings_profit_sharing_amount'];
						$savings_account_last_balance 	= $acctsavingsprofitsharing['savings_account_last_balance'];
					}

					$data_acctsavingsaccount[] = array (
						'savings_account_no'			=> $val['savings_account_no'],
						'member_name'					=> $val['member_name'],
						'member_address'				=> $val['member_address'],
						'savings_interest_rate'			=> $savingsinterestrate,
						'savings_daily_average_balance'	=> $savings_daily_average_balance,
						'savings_profit_sharing_amount'	=> $savings_profit_sharing_amount,
						'savings_account_last_balance'	=> $savings_account_last_balance,
					);
				}
			} else {
				foreach ($acctsavings as $key => $vS) {

					$acctsavingsaccount 		= $this->AcctNominativeSavingsReport_model->getAcctNomintiveSavingsReport_Savings($vS['savings_id']);

					foreach ($acctsavingsaccount as $key => $val) {
						$acctsavingsprofitsharing	= $this->AcctNominativeSavingsReport_model->getAcctSavingsProfitSharing($val['savings_account_id'], $period, $sesi['branch_id']);
						
						$savings_interest_rate		= $val['savings_interest_rate']/12;
						$savingsinterestrate 		= round($savings_interest_rate,2);

						if(empty($acctsavingsprofitsharing)){
							$savings_daily_average_balance 	= 0;
							$savings_profit_sharing_amount 	= 0;
							$savings_account_last_balance 	= $val['savings_account_last_balance'];
						} else {
							$savings_daily_average_balance 	= $acctsavingsprofitsharing['savings_daily_average_balance'];
							$savings_profit_sharing_amount 	= $acctsavingsprofitsharing['savings_profit_sharing_amount'];
							$savings_account_last_balance 	= $acctsavingsprofitsharing['savings_account_last_balance'];
						}

						$data_acctsavingsaccount[$vS['savings_id']][] = array (
							'savings_account_no'			=> $val['savings_account_no'],
							'member_name'					=> $val['member_name'],
							'member_address'				=> $val['member_address'],
							'savings_interest_rate'			=> $savingsinterestrate,
							'savings_daily_average_balance'	=> $savings_daily_average_balance,
							'savings_profit_sharing_amount'	=> $savings_profit_sharing_amount,
							'savings_account_last_balance'	=> $savings_account_last_balance,
						);
					}
				}
			}

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');

			$pdf = new tcpdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// ---------------------------------------------------------

			$pdf->SetFont('helvetica', 'B', 20);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 9);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl0 = "
			<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
			    <tr>
			    	<td rowspan=\"2\" width=\"10%\">" .$img."</td>
			    </tr>
			    <tr>
			    </tr>
			</table>
			<br/>
			<br/>
			<br/>
			<br/>";

			if($sesi['kelompok_laporan_simpanan'] == 0){
				$tbl = "
					<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
					    <tr>
					        <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF SIMPANAN</div></td>
					    </tr>
					    <tr>
					        <td><div style=\"text-align: center; font-size:10px\">Periode ".tgltoview($sesi['start_date'])."</div></td>
					    </tr>
					</table>";
			} else {
				$tbl = "
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
					<tr>
						<td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF SIMPANAN PER JENIS</div></td>
					</tr>
					<tr>
						<td><div style=\"text-align: center; font-size:10px\">Periode ".tgltoview($sesi['start_date'])."</div></td>
					</tr>
				</table>";
			}
		
			$pdf->writeHTML($tbl0.$tbl, true, false, false, false, '');
			
			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
			        <td width=\"11%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Rek</div></td>
			        <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
			        <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
			         <td width=\"7%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">Bunga</div></td>
			        <td width=\"20%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Saldo Akhir</div></td>
			    </tr>				
			</table>";

			$no=1;

			$tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

			$totalbasil = 0;
			$totalsaldo = 0;

			if($sesi['kelompok_laporan_simpanan'] == 0){
				foreach ($data_acctsavingsaccount as $key => $val) {
					
					$tbl3 .= "
						<tr>
					    	<td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
					        <td width=\"11%\"><div style=\"text-align: left;\">".$val['savings_account_no']."</div></td>
					        <td width=\"25%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
					        <td width=\"30%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
					        <td width=\"5%\"><div style=\"text-align: left;\">".$val['savings_interest_rate']."%</div></td>
					        <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['savings_account_last_balance'], 2)."</div></td>
					    </tr>
					";

					$totalbasil += $val['savings_profit_sharing_amount'];
					$totalsaldo += $val['savings_account_last_balance'];
					$no++;
				}
			} else { 
				foreach ($acctsavings as $key => $vS) {
					if(!empty($data_acctsavingsaccount[$vS['savings_id']])){
						$tbl3 .= "
							<br>
							<tr>
								<td colspan =\"7\" width=\"100%\" style=\"border-bottom: 1px solid black;font-weight:bold\"><div style=\"font-size:10\">".$vS['savings_name']."</div></td>
							</tr>
							<br>
						";
						
						$nov 			= 1;
						$subtotalbasil 	= 0;
						$subtotalsaldo 	= 0;

						foreach ($data_acctsavingsaccount[$vS['savings_id']] as $k => $v) {
							
							$tbl3 .= "
								<tr>
							    	<td width=\"5%\"><div style=\"text-align: left;\">".$nov."</div></td>
							        <td width=\"11%\"><div style=\"text-align: left;\">".$v['savings_account_no']."</div></td>
							        <td width=\"16%\"><div style=\"text-align: left;\">".$v['member_name']."</div></td>
							        <td width=\"20%\"><div style=\"text-align: left;\">".$v['member_address']."</div></td>
							        <td width=\"17%\"><div style=\"text-align: right;\">".number_format($v['savings_daily_average_balance'], 2)."</div></td>
							        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($v['savings_profit_sharing_amount'], 2)."</div></td>
							        <td width=\"17%\"><div style=\"text-align: right;\">".number_format($v['savings_account_last_balance'], 2)."</div></td>
							    </tr>
							";

							$subtotalbasil += $v['savings_profit_sharing_amount'];
							$subtotalsaldo += $v['savings_account_last_balance'];
							$nov++;
						}

						$tbl3 .= "
							<br>
							<tr>
								<td colspan =\"4\"><div style=\"font-size:10;font-style:italic;text-align:right\"></div></td>
								<td><div style=\"font-size:10;font-weight:bold;text-align:center\">Subtotal</div></td>
								<td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($subtotalbasil, 2)."</div></td>
								<td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($subtotalsaldo, 2)."</div></td>
							</tr>
							<br>
						";

						$totalglobal 	+= $subtotalbasil;
						$totalsaldo 	+= $subtotalsaldo;
					}
				}
			}

			$tbl4 .= "
				<br>
				<tr>
					<td colspan =\"4\"><div style=\"font-size:10;text-align:left;font-style:italic\"></div></td>
					<td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Total </div></td>
					<td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalsaldo, 2)."</div></td>
				</tr>
				</table>
				<br>
			";

			$pdf->writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			$filename = 'Laporan Nominatif Simpanan '.$namaKelompoklaporansimpanan.'.pdf';
			$pdf->Output($filename, 'I');
		}

		public function export($sesi){	
			$auth = $this->session->userdata('auth');
			// $sesi 	= array(
			// 	"branch_id"					=> $this->input->post('branch_id', true),
			// 	"start_date" 				=> tgltodb($this->input->post('start_date',true)),
			// 	"kelompok_laporan_simpanan"	=> $this->input->post('kelompok_laporan_simpanan',true),
			// );
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			$kelompoklaporansimpanan	= $this->configuration->KelompokLaporanSimpanan();	
			$acctsavings 				= $this->AcctNominativeSavingsReport_model->getAcctSavings();
			$period 					= date('mY', strtotime($sesi['start_date']));

			$namaKelompoklaporansimpanan = $kelompoklaporansimpanan[$sesi['kelompok_laporan_simpanan']];

			if($sesi['kelompok_laporan_simpanan'] == 0){
				$acctsavingsaccount	= $this->AcctNominativeSavingsReport_model->getAcctNomintiveSavingsReport();

				foreach ($acctsavingsaccount as $key => $val) {
					$acctsavingsprofitsharing 	 		= $this->AcctNominativeSavingsReport_model->getAcctSavingsProfitSharing($val['savings_account_id'], $period, $sesi['branch_id']);

					if(empty($acctsavingsprofitsharing)){
						$savings_daily_average_balance 	= 0;
						$savings_profit_sharing_amount 	= 0;
						$savings_account_last_balance 	= $val['savings_account_last_balance'];
					} else {
						$savings_daily_average_balance 	= $acctsavingsprofitsharing['savings_daily_average_balance'];
						$savings_profit_sharing_amount 	= $acctsavingsprofitsharing['savings_profit_sharing_amount'];
						$savings_account_last_balance 	= $acctsavingsprofitsharing['savings_account_last_balance'];
					}

					$data_acctsavingsaccount[] = array (
						'savings_account_no'			=> $val['savings_account_no'],
						'member_name'					=> $val['member_name'],
						'member_address'				=> $val['member_address'],
						'savings_daily_average_balance'	=> $savings_daily_average_balance,
						'savings_profit_sharing_amount'	=> $savings_profit_sharing_amount,
						'savings_account_last_balance'	=> $savings_account_last_balance,
					);
				}
			} else {
				foreach ($acctsavings as $key => $vS) {
					$acctsavingsaccount 		= $this->AcctNominativeSavingsReport_model->getAcctNomintiveSavingsReport_Savings($vS['savings_id']);

					foreach ($acctsavingsaccount as $key => $val) {
						$acctsavingsprofitsharing 	 		= $this->AcctNominativeSavingsReport_model->getAcctSavingsProfitSharing($val['savings_account_id'], $period, $branch_id);

						if(empty($acctsavingsprofitsharing)){
							$savings_daily_average_balance 	= 0;
							$savings_profit_sharing_amount 	= 0;
							$savings_account_last_balance 	= $val['savings_account_last_balance'];
						} else {
							$savings_daily_average_balance 	= $acctsavingsprofitsharing['savings_daily_average_balance'];
							$savings_profit_sharing_amount 	= $acctsavingsprofitsharing['savings_profit_sharing_amount'];
							$savings_account_last_balance 	= $acctsavingsprofitsharing['savings_account_last_balance'];
						}

						$data_acctsavingsaccount[$vS['savings_id']][] = array (
							'savings_account_no'			=> $val['savings_account_no'],
							'member_name'					=> $val['member_name'],
							'member_address'				=> $val['member_address'],
							'savings_daily_average_balance'	=> $savings_daily_average_balance,
							'savings_profit_sharing_amount'	=> $savings_profit_sharing_amount,
							'savings_account_last_balance'	=> $savings_account_last_balance,
						);
					}
				}
			}
			
			if(count($data_acctsavingsaccount) !=0){
				$this->load->library('Excel');
				
				$this->excel->getProperties()->setCreator("CST FISRT")
									 ->setLastModifiedBy("CST FISRT")
									 ->setTitle("Laporan Nominatif Simpanan")
									 ->setSubject("")
									 ->setDescription("Laporan Nominatif Simpanan")
									 ->setKeywords("Laporan, Nominatif, Simpanan")
									 ->setCategory("Laporan Nominatif Simpanan");
									 
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);		

				$this->excel->getActiveSheet()->mergeCells("B1:H1");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B3:H3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B3:H3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3:H3')->getFont()->setBold(true);
				if($sesi['kelompok_laporan_simpanan'] == 0){
					$this->excel->getActiveSheet()->setCellValue('B1',"DAFTAR NOMINATIF SIMPANAN");
				} else {
					$this->excel->getActiveSheet()->setCellValue('B1',"DAFTAR NOMINATIF SIMPANAN PER JENIS");
				}
					
				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"No. Rek");
				$this->excel->getActiveSheet()->setCellValue('D3',"Nama Anggota");
				$this->excel->getActiveSheet()->setCellValue('E3',"Alamat");
				$this->excel->getActiveSheet()->setCellValue('F3',"SRH");
				$this->excel->getActiveSheet()->setCellValue('G3',"Basil");
				$this->excel->getActiveSheet()->setCellValue('H3',"Saldo");
				
				$no			= 0;
				$totalbasil = 0;
				$totalsaldo = 0;

				if($sesi['kelompok_laporan_simpanan'] == 0){
					$j=4;
					foreach($data_acctsavingsaccount as $key=>$val){
						if(is_numeric($key)){
							$no++;
							$this->excel->setActiveSheetIndex(0);
							$this->excel->getActiveSheet()->getStyle('B'.$j.':H'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
							$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
							$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
							$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
							$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
							$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
							$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
							$this->excel->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

							$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
							$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j, $val['savings_account_no'],PHPExcel_Cell_DataType::TYPE_STRING);								
							$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
							$this->excel->getActiveSheet()->setCellValue('E'.$j, $val['member_address']);
							$this->excel->getActiveSheet()->setCellValue('F'.$j, $val['savings_daily_average_balance']);
							$this->excel->getActiveSheet()->setCellValue('G'.$j, number_format($val['savings_profit_sharing_amount'],2));
							$this->excel->getActiveSheet()->setCellValue('H'.$j, number_format($val['savings_account_last_balance'],2));
				
							$totalbasil += $val['savings_profit_sharing_amount'];
							$totalsaldo += $val['savings_account_last_balance'];
						}else{
							continue;
						}
						$j++;
					}
				} else {
					$i=4;
					
					foreach ($acctsavings as $k => $v) {
						$this->excel->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true)->setSize(14);
						$this->excel->getActiveSheet()->getStyle('B'.$i.':H'.$i)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$this->excel->getActiveSheet()->mergeCells('B'.$i.':H'.$i);
						$this->excel->getActiveSheet()->setCellValue('B'.$i, $v['savings_name']);

						$nov			= 0;
						$j				= $i+1;
						$subtotalbasil 	= 0;
						$subtotalsaldo 	= 0;

						foreach($data_acctsavingsaccount[$v['savings_id']] as $key=>$val){
							if(is_numeric($key)){
								$nov++;
								$this->excel->setActiveSheetIndex(0);
								$this->excel->getActiveSheet()->getStyle('B'.$j.':H'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
								$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
								$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
								$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
								$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
								$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
								$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
								$this->excel->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

								$this->excel->getActiveSheet()->setCellValue('B'.$j, $nov);
								$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j, $val['savings_account_no'],PHPExcel_Cell_DataType::TYPE_STRING);
								$this->excel->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
								$this->excel->getActiveSheet()->setCellValue('E'.$j, $val['member_address']);
								$this->excel->getActiveSheet()->setCellValue('F'.$j, $val['savings_daily_average_balance']);
								$this->excel->getActiveSheet()->setCellValue('G'.$j, number_format($val['savings_profit_sharing_amount'],2));
								$this->excel->getActiveSheet()->setCellValue('H'.$j, number_format($val['savings_account_last_balance'],2));
							}else{
								continue;
							}
							$j++;

							$subtotalbasil += $val['savings_profit_sharing_amount'];
							$subtotalsaldo += $val['savings_account_last_balance'];
						}

						$m = $j;

						$this->excel->getActiveSheet()->getStyle('B'.$m.':H'.$m)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
						$this->excel->getActiveSheet()->getStyle('B'.$m.':H'.$m)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$this->excel->getActiveSheet()->mergeCells('B'.$m.':G'.$m);

						$this->excel->getActiveSheet()->setCellValue('B'.$m, 'SubTotal');
						$this->excel->getActiveSheet()->setCellValue('G'.$m, number_format($subtotalbasil,2));
						$this->excel->getActiveSheet()->setCellValue('H'.$m, number_format($subtotalsaldo,2));

						$i = $m + 1;
					}

					$totalbasil += $subtotalbasil;
					$totalsaldo += $subtotalsaldo;
				}

				$n = $j;

				$this->excel->getActiveSheet()->getStyle('B'.$n.':H'.$n)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
				$this->excel->getActiveSheet()->getStyle('B'.$n.':H'.$n)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->mergeCells('B'.$n.':G'.$n);
				
				$this->excel->getActiveSheet()->setCellValue('B'.$n, 'Total');
				$this->excel->getActiveSheet()->setCellValue('G'.$n, number_format($totalbasil,2));
				$this->excel->getActiveSheet()->setCellValue('H'.$n, number_format($totalsaldo,2));
				
				$filename='Laporan Nominatif Simpanan.xls';
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="'.$filename.'"');
				header('Cache-Control: max-age=0');
							 
				$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
				ob_end_clean();
				$objWriter->save('php://output');
			}else{
				echo "Maaf data yang di eksport tidak ada !";
			}
		}
	}
?>
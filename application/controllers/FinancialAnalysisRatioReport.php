<?php ob_start(); ?>
<?php
	ini_set('memory_limit', '512M');
	defined('BASEPATH') OR exit('No direct script access allowed');


	Class FinancialAnalysisRatioReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('FinancialAnalysisRatioReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$corebranch 									= create_double_branch($this->FinancialAnalysisRatioReport_model->getCoreBranch(),'branch_id','branch_name');
			$corebranch[0] 									= 'Semua Cabang';
			ksort($corebranch);
			$data['main_view']['corebranch']				= $corebranch;
			$data['main_view']['month']						= $this->configuration->Month();	
			$data['main_view']['content']					= 'FinancialAnalysisRatioReport/ListFinancialAnalysisRatioReport_view';
			$this->load->view('MainPage_view',$data);
		}
 
		public function viewreport(){

			$start_date = '01-'.$this->input->post('month_id',true).'-'.$this->input->post('year_id',true);
			$start_date = date('t-m-Y', strtotime($start_date));

			$sesi = array (
				"branch_id"					=> $this->input->post('branch_id', true),
				"start_date" 				=> tgltodb($start_date),
				"view"						=> $this->input->post('view',true),
			);

			if($sesi['view'] == 'pdf'){
				$this->processPrinting($sesi);
			} else {
				$this->export($sesi);
			}
		}

		public function processPrinting($sesi){
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->FinancialAnalysisRatioReport_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			// $datamutation 		= $this->FinancialAnalysisRatioReport_model->getFinancialAnalysisRatioReport($sesi['start_date'], $branch_id);
			
			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			// create new PDF document
			$pdf = new tcpdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7); 
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			// set font
			$pdf->SetFont('helvetica', 'B', 20);

			// add a page
			$pdf->AddPage();

			$pdf->SetFont('helvetica', '', 9);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$monthname		= $this->configuration->Month();
			
			$tanggal 		= date('d', strtotime($sesi['start_date']));
			$bulan 			= date('m', strtotime($sesi['start_date']));
			$tahun 			= date('Y', strtotime($sesi['start_date']));
			$date			= $tanggal.' '.$monthname[$bulan].' '.$tahun;

			$bulan_before	= $bulan-3;
			$tahun_before	= $tahun;
			if($bulan_before < 1){
				$bulan_before += 12;
				$tahun_before -= 1;
			}
			$bulan_before 	= sprintf("%02d", $bulan_before);
			$date_before 	= '01-'.$bulan_before.'-'.$tahun_before;
			$tanggal_before = date('t', strtotime("-3 months", strtotime($date_before)));
			$date_before	= $tanggal_before.' '.$monthname[$bulan_before].' '.$tahun_before;

			$principal_savings_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 633);
			$mandatory_savings_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 634);
			$special_savings_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 635);
			$cadangan_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 637);
			$hibah_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 636);

			$partisipasi_reguler_now		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 647);
			$partisipasi_non_reguler_now	= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 648);
			$gaji_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 654);
			$jasa_pengurus_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 655);
			$bingkisan_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 657);
			$bunga_pinjaman_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 642);
			$operasional_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 658);
			$seragam_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 659);
			$komputerisasi_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 663);
			$perjalanan_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 674);
			$pihak_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 675);
			$penyusutan_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 676);

			$shu_tahun_berjalan_now			= ($partisipasi_reguler_now + $partisipasi_non_reguler_now) - ($gaji_now + $jasa_pengurus_now + $bingkisan_now + $bunga_pinjaman_now + $operasional_now + 	$seragam_now + $komputerisasi_now + $perjalanan_now + $pihak_now + $penyusutan_now);

			$total_now						= $principal_savings_now + $mandatory_savings_now + $special_savings_now + $cadangan_now + $hibah_now + $shu_tahun_berjalan_now;

			$principal_savings_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 633);
			$mandatory_savings_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 634);
			$special_savings_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 635);
			$cadangan_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 637);
			$hibah_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 636);
			$shu_tahun_berjalan_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 722);

			$partisipasi_reguler_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 647);
			$partisipasi_non_reguler_before	= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 648);
			$gaji_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 654);
			$jasa_pengurus_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 655);
			$bingkisan_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 657);
			$bunga_pinjaman_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 642);
			$operasional_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 658);
			$seragam_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 659);
			$komputerisasi_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 663);
			$perjalanan_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 674);
			$pihak_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 675);
			$penyusutan_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 676);

			$shu_tahun_berjalan_before		= ($partisipasi_reguler_before + $partisipasi_non_reguler_before) - ($gaji_before + $jasa_pengurus_before + $bingkisan_before + $bunga_pinjaman_before + $operasional_before + 	$seragam_before + $komputerisasi_before + $perjalanan_before + $pihak_before + $penyusutan_before);

			$total_before					= $principal_savings_before + $mandatory_savings_before + $special_savings_before + $cadangan_before + $hibah_before + $shu_tahun_berjalan_before;

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
			<br/>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
				<tr>
					<td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">LEPORAN PERUBAHAN EKUITAS Per ".$date."</div></td>
				</tr>
				<tr>
					<td width=\"100%\"><div style=\"text-align: left; font-size:9px; font-style: italic\">(Dinyatakan dalam Rupiah)</div></td>
				</tr>
			</table>
			<br>";

			$tbl1 = "
			<br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td width=\"50%\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Keterangan</div></td>
					<td width=\"50%\" colspan=\"2\" style=\"font-weight:bold; border: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: center;font-size:10;\">Per ".$date."</div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"font-weight:bold; border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">EKUITAS Per ".$date_before."</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: center;font-size:10;\"></div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format($total_before, 2)."</div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10; font-style:italic; text-decoration:underline;\">Ditambah/Dikurangi</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: center;font-size:10;\"></div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: center;font-size:10;\"></div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10;\"> Simpanan Pokok</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format(($principal_savings_now-$principal_savings_before), 2)."</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\"></div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10;\"> Simpanan Wajib</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format(($mandatory_savings_now-$mandatory_savings_before), 2)."</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\"></div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10;\"> Simpanan Khusus</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format(($special_savings_now-$special_savings_before), 2)."</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\"></div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10;\"> Cadangan</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format(($cadangan_now-$cadangan_before), 2)."</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\"></div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10;\"> Hibah</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format(($hibah_now-$hibah_before), 2)."</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\"></div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10;\"> Sisa Hasil Usaha (SHU) Dibagi</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format(($shu_dibagi_now-$shu_dibagi_before), 2)."</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\"></div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10;\"> Sisa Hasil Usaha Tahun Berjalan</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format(($shu_tahun_berjalan_now-$shu_tahun_berjalan_before), 2)."</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\"></div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">Jumlah Penambahan</div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: right;font-size:10;\"></div></td>
					<td width=\"25%\" style=\"border-left: 1px solid black; border-right: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format($total_now, 2)."</div></td>
			    </tr>
			    <tr>
					<td width=\"50%\" style=\"font-weight:bold; border-left: 1px solid black; border-bottom: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: left;font-size:10;\">EKUITAS Per ".$date."</div></td>
					<td width=\"25%\" style=\"border-right: 1px solid black; border-bottom: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: right;font-size:10;\"></div></td>
					<td width=\"25%\" style=\"font-weight:bold; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black; border-top: 1px solid black;\"><div style=\"text-align: right;font-size:10;\">".number_format(($total_now + $total_before), 2)."</div></td>
			    </tr>
			</table>
			";

			$pdf->writeHTML($tbl0.$tbl1, true, false, false, '');

			ob_clean();

			// -----------------------------------------------------------------------------
			
			//Close and output PDF document
			$filename = 'Laporan Analisa Rasio Keuangan Per '.$date.'.pdf';
			$pdf->Output($filename, 'I');

			//============================================================+
			// END OF FILE
			//============================================================+
		}

		public function export($sesi){	
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->FinancialAnalysisRatioReport_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}


			$monthname		= $this->configuration->Month();
			
			$tanggal 		= date('d', strtotime($sesi['start_date']));
			$bulan 			= date('m', strtotime($sesi['start_date']));
			$tahun 			= date('Y', strtotime($sesi['start_date']));
			$date			= $tanggal.' '.$monthname[$bulan].' '.$tahun;

			if($bulan <=3){
				$triwulan = "I";
			}else if($bulan >=4 && $bulan <=6){
				$triwulan = "II";
			}else if($bulan >=7 && $bulan <=9){
				$triwulan = "III";
			}else if($bulan >=10){
				$triwulan = "IV";
			}

			$bulan_before	= $bulan-3;
			$tahun_before	= $tahun;
			if($bulan_before < 1){
				$bulan_before += 12;
				$tahun_before -= 1;
			}
			$bulan_before 	= sprintf("%02d", $bulan_before);
			$date_before 	= '01-'.$bulan_before.'-'.$tahun_before;
			$tanggal_before = date('t', strtotime("-3 months", strtotime($date_before)));
			$date_before	= $tanggal_before.' '.$monthname[$bulan_before].' '.$tahun_before;

			if($bulan_before <=3){
				$triwulan_before = "I";
			}else if($bulan_before >=4 && $bulan_before <=6){
				$triwulan_before = "II";
			}else if($bulan_before >=7 && $bulan_before <=9){
				$triwulan_before = "III";
			}else if($bulan_before >=10){
				$triwulan_before = "IV";
			}

			//*DATA NOW-------------------------------------------------------------------------------------------------------------------------

			$kas_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 720);
			$bank1_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 568);
			$bank2_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 569);
			$bank3_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 570);
			$piutang_reguler_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 577);
			$piutang_nonreguler_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 579);
			$piutang_lain_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 589);
			$biaya_dibayar_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 590);
			$aset_lancar_now				= $kas_now + $bank1_now + $bank2_now + $bank3_now + $piutang_reguler_now  + $piutang_nonreguler_now + $piutang_lain_now + $biaya_dibayar_now;

			$harga_perolehan_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 724);
			$akumulasi_penyusutan_now		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 594);
			$aset_tetap_now					= $harga_perolehan_now + $akumulasi_penyusutan_now;

			$aset_lain_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 596);

			$jumlah_aset_now				= $aset_lancar_now + $aset_tetap_now + $aset_lain_now;

			$biaya_masih_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 614);
			$tabungan_sukarelala_now		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 599);
			$tabungan_pendidikan_now		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 600);
			$tabungan_natal_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 604);
			$tabungan_qurban_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 605);
			$tabungan_kredit_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 606);
			$tabungan_lebaran_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 601);
			$dana_pendidikan_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 610);
			$dana_sosial_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 611);
			$dana_pembangunan_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 612);
			$hutang_pajak_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 616);
			$hutang_lain_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 618);
			$kewajiban_lancar_now			= $biaya_masih_now + $tabungan_sukarelala_now + $tabungan_pendidikan_now + $tabungan_natal_now + $tabungan_qurban_now + $tabungan_kredit_now + $tabungan_lebaran_now + $dana_pendidikan_now + $dana_sosial_now + $dana_pembangunan_now + $hutang_pajak_now + $hutang_lain_now;

			$hutang_bank_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 617);

			$principal_savings_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 633);
			$mandatory_savings_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 634);
			$special_savings_now			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 635);
			$cadangan_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 637);
			$hibah_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 636);

			$partisipasi_reguler_now		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 647);
			$partisipasi_non_reguler_now	= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 648);
			$gaji_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 654);
			$jasa_pengurus_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 655);
			$bingkisan_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 657);
			$bunga_pinjaman_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 642);
			$operasional_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 658);
			$seragam_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 659);
			$komputerisasi_now				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 663);
			$perjalanan_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 674);
			$pihak_now						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 675);
			$penyusutan_now					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan+1), $tahun, 676);
			$shu_tahun_berjalan_now			= ($partisipasi_reguler_now + $partisipasi_non_reguler_now) - ($gaji_now + $jasa_pengurus_now + $bingkisan_now + $bunga_pinjaman_now + $operasional_now + 	$seragam_now + $komputerisasi_now + $perjalanan_now + $pihak_now + $penyusutan_now);
			$ekuitas_now					= $principal_savings_now + $mandatory_savings_now + $special_savings_now + $cadangan_now + $hibah_now + $shu_tahun_berjalan_now;

			$jumlah_kewajiban_ekuitas_now 	= $kewajiban_lancar_now + $hutang_bank_now + $ekuitas_now;


			//*DATA BEFORE----------------------------------------------------------------------------------------------------------------------

			$kas_before						= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 720);
			$bank1_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 568);
			$bank2_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 569);
			$bank3_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 570);
			$piutang_reguler_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 577);
			$piutang_nonreguler_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 579);
			$piutang_lain_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 589);
			$biaya_dibayar_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 590);
			$aset_lancar_before				= $kas_before + $bank1_before + $bank2_before + $bank3_before + $piutang_reguler_before  + $piutang_nonreguler_before + $piutang_lain_before + $biaya_dibayar_before;

			$harga_perolehan_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 724);
			$akumulasi_penyusutan_before	= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 594);
			$aset_tetap_before				= $harga_perolehan_before + $akumulasi_penyusutan_before;

			$aset_lain_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 596);

			$jumlah_aset_before				= $aset_lancar_before + $aset_tetap_before + $aset_lain_before;

			$biaya_masih_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 614);
			$tabungan_sukarelala_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 599);
			$tabungan_pendidikan_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 600);
			$tabungan_natal_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 604);
			$tabungan_qurban_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 605);
			$tabungan_kredit_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 606);
			$tabungan_lebaran_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 601);
			$dana_pendidikan_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 610);
			$dana_sosial_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 611);
			$dana_pembangunan_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 612);
			$hutang_pajak_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 616);
			$hutang_lain_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 618);
			$kewajiban_lancar_before		= $biaya_masih_before + $tabungan_sukarelala_before + $tabungan_pendidikan_before + $tabungan_natal_before + $tabungan_qurban_before + $tabungan_kredit_before + $tabungan_lebaran_before + $dana_pendidikan_before + $dana_sosial_before + $dana_pembangunan_before + $hutang_pajak_before + $hutang_lain_before;

			$hutang_bank_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 617);

			$principal_savings_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 633);
			$mandatory_savings_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 634);
			$special_savings_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 635);
			$cadangan_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 637);
			$hibah_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 636);
			$shu_tahun_berjalan_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 722);

			$partisipasi_reguler_before		= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 647);
			$partisipasi_non_reguler_before	= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 648);
			$gaji_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 654);
			$jasa_pengurus_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 655);
			$bingkisan_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 657);
			$bunga_pinjaman_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 642);
			$operasional_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 658);
			$seragam_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 659);
			$komputerisasi_before			= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 663);
			$perjalanan_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 674);
			$pihak_before					= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 675);
			$penyusutan_before				= $this->FinancialAnalysisRatioReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 676);
			$shu_tahun_berjalan_before		= ($partisipasi_reguler_before + $partisipasi_non_reguler_before) - ($gaji_before + $jasa_pengurus_before + $bingkisan_before + $bunga_pinjaman_before + $operasional_before + 	$seragam_before + $komputerisasi_before + $perjalanan_before + $pihak_before + $penyusutan_before);
			$ekuitas_before					= $principal_savings_before + $mandatory_savings_before + $special_savings_before + $cadangan_before + $hibah_before + $shu_tahun_berjalan_before;

			$jumlah_kewajiban_ekuitas_before 	= $kewajiban_lancar_before + $hutang_bank_before + $ekuitas_before;



			$this->load->library('Excel');
			
			$this->excel->getProperties()->setCreator("CST FISRT")
									->setLastModifiedBy("CST FISRT")
									->setTitle("Laporan Data Analisa Rasio Keuangan")
									->setSubject("")
									->setDescription("Laporan Data Analisa Rasio Keuangan")
									->setKeywords("Laporan Data Analisa Rasio Keuangan")
									->setCategory("Laporan Data Analisa Rasio Keuangan");
									
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(5);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(5);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(5);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(5);

			//!CURRENT RATIO--------------------------------------------------------------------------------------------------------------------
					
			$this->excel->getActiveSheet()->mergeCells("B2:I2");
			$this->excel->getActiveSheet()->mergeCells("B3:I3");
			$this->excel->getActiveSheet()->mergeCells("B4:I4");
			$this->excel->getActiveSheet()->mergeCells("C11:I12");
			$this->excel->getActiveSheet()->mergeCells("C14:C15");
			$this->excel->getActiveSheet()->mergeCells("D14:D15");
			$this->excel->getActiveSheet()->mergeCells("F14:F15");
			$this->excel->getActiveSheet()->mergeCells("G14:G15");
			$this->excel->getActiveSheet()->mergeCells("C17:C18");
			$this->excel->getActiveSheet()->mergeCells("D17:D18");
			$this->excel->getActiveSheet()->mergeCells("F17:F18");
			$this->excel->getActiveSheet()->mergeCells("G17:G18");
			$this->excel->getActiveSheet()->mergeCells("H17:H18");
			$this->excel->getActiveSheet()->mergeCells("I17:I18");
			$this->excel->getActiveSheet()->mergeCells("C20:C21");
			$this->excel->getActiveSheet()->mergeCells("D20:D21");
			$this->excel->getActiveSheet()->mergeCells("F20:F21");
			$this->excel->getActiveSheet()->mergeCells("G20:G21");
			$this->excel->getActiveSheet()->mergeCells("H20:H21");
			$this->excel->getActiveSheet()->mergeCells("I20:I21");
			$this->excel->getActiveSheet()->mergeCells("C23:I25");
			$this->excel->getActiveSheet()->getStyle('B5')->getFont()->setSize(9);
			$this->excel->getActiveSheet()->getStyle('B2:B4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('D14:I21')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('C14:I21')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B2:B3')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('B7')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('C9')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('E14')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E17')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E20')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);$this->excel->getActiveSheet()->getStyle('C11:I12')->getAlignment()->setWrapText(true); 
			$this->excel->getActiveSheet()->getStyle('C23:I25')->getAlignment()->setWrapText(true); 
			
			$this->excel->getActiveSheet()->setCellValue('B2',"KOPERASI KARYAWAN MENJANGAN ENAM");
			$this->excel->getActiveSheet()->setCellValue('B3',"ANALISA RASIO KEUANGAN");
			$this->excel->getActiveSheet()->setCellValue('B4',"Periode yang Berakhir ".$date." dan ".$date_before);
			$this->excel->getActiveSheet()->setCellValue('B7',"I. ANALISA LIKUIDITAS");
			$this->excel->getActiveSheet()->setCellValue('C9',"1. Current Ratio");
			$this->excel->getActiveSheet()->setCellValue('C11',"Adalah  Rasio Aset Lancar dengan Kewajiban Lancar (Hutang Lancar) dikalikan 100%, dengan perhitungan sebagai berikut :");
			$this->excel->getActiveSheet()->setCellValue('C14',"Current Ratio");
			$this->excel->getActiveSheet()->setCellValue('D14',"=");
			$this->excel->getActiveSheet()->setCellValue('F14',"x");
			$this->excel->getActiveSheet()->setCellValue('G14',"100%");
			$this->excel->getActiveSheet()->setCellValue('E14',"Aset Lancar");
			$this->excel->getActiveSheet()->setCellValue('E15',"Kewajiban Lancar");
			$this->excel->getActiveSheet()->setCellValue('C17',"Triwulan ".$triwulan_before." tahun ".$tahun_before);
			$this->excel->getActiveSheet()->setCellValue('D17',"=");
			$this->excel->getActiveSheet()->setCellValue('F17',"x");
			$this->excel->getActiveSheet()->setCellValue('G17',"100%");
			$this->excel->getActiveSheet()->setCellValue('H17',"=");
			$this->excel->getActiveSheet()->setCellValue('C20',"Triwulan ".$triwulan." tahun ".$tahun);
			$this->excel->getActiveSheet()->setCellValue('D20',"=");
			$this->excel->getActiveSheet()->setCellValue('F20',"x");
			$this->excel->getActiveSheet()->setCellValue('G20',"100%");
			$this->excel->getActiveSheet()->setCellValue('H20',"=");

			$value_now 		= $aset_lancar_now/$kewajiban_lancar_now;
			if(is_nan($value_now)){
				$value_now = 0;
			}
			$value_before 	= $aset_lancar_before/$kewajiban_lancar_before;
			if(is_nan($value_before)){
				$value_before = 0;
			}
			$value = $value_now - $value_before;
			if($value > 0){
				$keterangan = "kenaikan";
			}else{
				$keterangan = "penurunan";
			}

			$this->excel->getActiveSheet()->setCellValue('E17',$aset_lancar_before);
			$this->excel->getActiveSheet()->setCellValue('E18',$kewajiban_lancar_before);
			$this->excel->getActiveSheet()->setCellValue('I17',$value_before."%");
			$this->excel->getActiveSheet()->setCellValue('E20',$aset_lancar_now);
			$this->excel->getActiveSheet()->setCellValue('E21',$kewajiban_lancar_now);
			$this->excel->getActiveSheet()->setCellValue('I20',$value_now."%");
			$this->excel->getActiveSheet()->setCellValue('K21',$value."%");
			$this->excel->getActiveSheet()->setCellValue('C23',"Hal ini berarti setiap Kewajiban Lancar (Hutang Lancar)  pada Triwulan ".$triwulan." tahun ".$tahun." sebesar Rp. 100,00 dijamin dengan Aset Lancar sebesar Rp. ".number_format($value_now,2,",",".").",  dibandingkan dengan Triwulan ".$triwulan_before." tahun ".$tahun_before." mengalami ".$keterangan."  sebesar ".number_format(abs($value),2,",",".").".");

			//!QUICK RATIO----------------------------------------------------------------------------------------------------------------------

			$this->excel->getActiveSheet()->mergeCells("C23:I25");
			$this->excel->getActiveSheet()->mergeCells("C29:I30");
			$this->excel->getActiveSheet()->mergeCells("C32:C33");
			$this->excel->getActiveSheet()->mergeCells("D32:D33");
			$this->excel->getActiveSheet()->mergeCells("F32:F33");
			$this->excel->getActiveSheet()->mergeCells("G32:G33");
			$this->excel->getActiveSheet()->mergeCells("C35:C36");
			$this->excel->getActiveSheet()->mergeCells("D35:D36");
			$this->excel->getActiveSheet()->mergeCells("F35:F36");
			$this->excel->getActiveSheet()->mergeCells("G35:G36");
			$this->excel->getActiveSheet()->mergeCells("H35:H36");
			$this->excel->getActiveSheet()->mergeCells("I35:I36");
			$this->excel->getActiveSheet()->mergeCells("C38:C39");
			$this->excel->getActiveSheet()->mergeCells("D38:D39");
			$this->excel->getActiveSheet()->mergeCells("F38:F39");
			$this->excel->getActiveSheet()->mergeCells("G38:G39");
			$this->excel->getActiveSheet()->mergeCells("H38:H39");
			$this->excel->getActiveSheet()->mergeCells("I38:I39");
			$this->excel->getActiveSheet()->mergeCells("C41:I43");
			$this->excel->getActiveSheet()->getStyle('C27')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('E32')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E35')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E38')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('D32:I39')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('C32:I39')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);$this->excel->getActiveSheet()->getStyle('C29:I30')->getAlignment()->setWrapText(true); 
			$this->excel->getActiveSheet()->getStyle('C41:I43')->getAlignment()->setWrapText(true); 

			$this->excel->getActiveSheet()->setCellValue('C27',"2. Quick Ratio");
			$this->excel->getActiveSheet()->setCellValue('C29',"Adalah Rasio Kas dan Setara Kas  ditambah  Piutang Lancar dengan Kewajiban Lancar (Hutang Lancar) dikalikan 100%, dengan perhitungan sebagai berikut :");
			$this->excel->getActiveSheet()->setCellValue('C32',"Quick Ratio");
			$this->excel->getActiveSheet()->setCellValue('D32',"=");
			$this->excel->getActiveSheet()->setCellValue('F32',"x");
			$this->excel->getActiveSheet()->setCellValue('G32',"100%");
			$this->excel->getActiveSheet()->setCellValue('E32',"Kas dan Bank + Piutang Lancar");
			$this->excel->getActiveSheet()->setCellValue('E33',"Kewajiban Lancar");
			$this->excel->getActiveSheet()->setCellValue('C35',"Triwulan ".$triwulan_before." tahun ".$tahun_before);
			$this->excel->getActiveSheet()->setCellValue('D35',"=");
			$this->excel->getActiveSheet()->setCellValue('F35',"x");
			$this->excel->getActiveSheet()->setCellValue('G35',"100%");
			$this->excel->getActiveSheet()->setCellValue('H35',"=");
			$this->excel->getActiveSheet()->setCellValue('C38',"Triwulan ".$triwulan." tahun ".$tahun);
			$this->excel->getActiveSheet()->setCellValue('D38',"=");
			$this->excel->getActiveSheet()->setCellValue('F38',"x");
			$this->excel->getActiveSheet()->setCellValue('G38',"100%");
			$this->excel->getActiveSheet()->setCellValue('H38',"=");

			$value_now 		= ($kas_now + $bank1_now + $bank2_now + $bank3_now + $piutang_reguler_now  + $piutang_nonreguler_now + $piutang_lain_now)/$kewajiban_lancar_now;
			if(is_nan($value_now)){
				$value_now = 0;
			}
			$value_before 	= ($kas_before + $bank1_before + $bank2_before + $bank3_before + $piutang_reguler_before  + $piutang_nonreguler_before + $piutang_lain_before)/$kewajiban_lancar_before;
			if(is_nan($value_before)){
				$value_before = 0;
			}
			$value = $value_now - $value_before;
			if($value > 0){
				$keterangan = "kenaikan";
			}else{
				$keterangan = "penurunan";
			}

			$this->excel->getActiveSheet()->setCellValue('E35',($kas_before + $bank1_before + $bank2_before + $bank3_before + $piutang_reguler_before  + $piutang_nonreguler_before + $piutang_lain_before));
			$this->excel->getActiveSheet()->setCellValue('E36',$kewajiban_lancar_before);
			$this->excel->getActiveSheet()->setCellValue('I35',$value_before."%");
			$this->excel->getActiveSheet()->setCellValue('E38',($kas_now + $bank1_now + $bank2_now + $bank3_now + $piutang_reguler_now  + $piutang_nonreguler_now + $piutang_lain_now));
			$this->excel->getActiveSheet()->setCellValue('E39',$kewajiban_lancar_now);
			$this->excel->getActiveSheet()->setCellValue('I38',$value_now."%");
			$this->excel->getActiveSheet()->setCellValue('K39',$value."%");
			$this->excel->getActiveSheet()->setCellValue('C41',"Hal ini berarti setiap Kewajiban Lancar (Hutang Lancar) pada Triwulan ".$triwulan." tahun ".$tahun." sebesar Rp. 100,00 dijamin dengan Kas dan Setara Kas ditambah Piutang Lancar, yaitu sebesar  Rp. ".number_format($value_now,2,",",".").",  dibandingkan dengan Triwulan ".$triwulan_before." tahun ".$tahun_before." quick ratio mengalami ".$keterangan." sebesar ".number_format(abs($value),2,",",".").".");


			//!CASH RATIO-----------------------------------------------------------------------------------------------------------------------

			$this->excel->getActiveSheet()->mergeCells("C47:I48");
			$this->excel->getActiveSheet()->mergeCells("C50:C51");
			$this->excel->getActiveSheet()->mergeCells("D50:D51");
			$this->excel->getActiveSheet()->mergeCells("F50:F51");
			$this->excel->getActiveSheet()->mergeCells("G50:G51");
			$this->excel->getActiveSheet()->mergeCells("C53:C54");
			$this->excel->getActiveSheet()->mergeCells("D53:D54");
			$this->excel->getActiveSheet()->mergeCells("F53:F54");
			$this->excel->getActiveSheet()->mergeCells("G53:G54");
			$this->excel->getActiveSheet()->mergeCells("H53:H54");
			$this->excel->getActiveSheet()->mergeCells("I53:I54");
			$this->excel->getActiveSheet()->mergeCells("C56:C57");
			$this->excel->getActiveSheet()->mergeCells("D56:D57");
			$this->excel->getActiveSheet()->mergeCells("F56:F57");
			$this->excel->getActiveSheet()->mergeCells("G56:G57");
			$this->excel->getActiveSheet()->mergeCells("H56:H57");
			$this->excel->getActiveSheet()->mergeCells("I56:I57");
			$this->excel->getActiveSheet()->mergeCells("C59:I61");
			$this->excel->getActiveSheet()->getStyle('C45')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('E50')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E53')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E56')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('D50:I57')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('C50:I57')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);$this->excel->getActiveSheet()->getStyle('C47:I48')->getAlignment()->setWrapText(true); 
			$this->excel->getActiveSheet()->getStyle('C59:I61')->getAlignment()->setWrapText(true); 

			$this->excel->getActiveSheet()->setCellValue('C45',"3. Cash Ratio");
			$this->excel->getActiveSheet()->setCellValue('C47',"Adalah  Rasio Kas dan Setara Kas  dengan Kewajiban Lancar (Hutang Lancar) dikalikan 100%, dengan perhitungan sebagai berikut :");
			$this->excel->getActiveSheet()->setCellValue('C50',"Cash Ratio");
			$this->excel->getActiveSheet()->setCellValue('D50',"=");
			$this->excel->getActiveSheet()->setCellValue('F50',"x");
			$this->excel->getActiveSheet()->setCellValue('G50',"100%");
			$this->excel->getActiveSheet()->setCellValue('E50',"Kas dan Setara Kas");
			$this->excel->getActiveSheet()->setCellValue('E51',"Kewajiban Lancar");
			$this->excel->getActiveSheet()->setCellValue('C53',"Triwulan ".$triwulan_before." tahun ".$tahun_before);
			$this->excel->getActiveSheet()->setCellValue('D53',"=");
			$this->excel->getActiveSheet()->setCellValue('F53',"x");
			$this->excel->getActiveSheet()->setCellValue('G53',"100%");
			$this->excel->getActiveSheet()->setCellValue('H53',"=");
			$this->excel->getActiveSheet()->setCellValue('C56',"Triwulan ".$triwulan." tahun ".$tahun);
			$this->excel->getActiveSheet()->setCellValue('D56',"=");
			$this->excel->getActiveSheet()->setCellValue('F56',"x");
			$this->excel->getActiveSheet()->setCellValue('G56',"100%");
			$this->excel->getActiveSheet()->setCellValue('H56',"=");

			$value_now 		= ($kas_now + $bank1_now + $bank2_now + $bank3_now)/$kewajiban_lancar_now;
			if(is_nan($value_now)){
				$value_now = 0;
			}
			$value_before 	= ($kas_before + $bank1_before + $bank2_before + $bank3_before)/$kewajiban_lancar_before;
			if(is_nan($value_before)){
				$value_before = 0;
			}
			$value = $value_now - $value_before;
			if($value > 0){
				$keterangan = "kenaikan";
			}else{
				$keterangan = "penurunan";
			}

			$this->excel->getActiveSheet()->setCellValue('E53',($kas_before + $bank1_before + $bank2_before + $bank3_before));
			$this->excel->getActiveSheet()->setCellValue('E54',$kewajiban_lancar_before);
			$this->excel->getActiveSheet()->setCellValue('I53',$value_before."%");
			$this->excel->getActiveSheet()->setCellValue('E56',($kas_now + $bank1_now + $bank2_now + $bank3_now));
			$this->excel->getActiveSheet()->setCellValue('E57',$kewajiban_lancar_now);
			$this->excel->getActiveSheet()->setCellValue('I56',$value_now."%");
			$this->excel->getActiveSheet()->setCellValue('K57',$value."%");
			$this->excel->getActiveSheet()->setCellValue('C59',"Hal ini berarti setiap Kewajiban Lancar (Hutang Lancar) pada Triwulan ".$triwulan." tahun ".$tahun." sebesar Rp. 100,00 dijamin dengan Kas dan Setara Kas  sebesar Rp. ".number_format($value_now,2,",",".").",  dibandingkan dengan Triwulan ".$triwulan_before." tahun ".$tahun_before." mengalami ".$keterangan." sebesar ".number_format(abs($value),2,",",".").".");

			//!RASIO MODAL DENGAN ASET----------------------------------------------------------------------------------------------------------
			$this->excel->getActiveSheet()->mergeCells("C68:I69");
			$this->excel->getActiveSheet()->mergeCells("C71:C72");
			$this->excel->getActiveSheet()->mergeCells("D71:D72");
			$this->excel->getActiveSheet()->mergeCells("F71:F72");
			$this->excel->getActiveSheet()->mergeCells("G71:G72");
			$this->excel->getActiveSheet()->mergeCells("C74:C75");
			$this->excel->getActiveSheet()->mergeCells("D74:D75");
			$this->excel->getActiveSheet()->mergeCells("F74:F75");
			$this->excel->getActiveSheet()->mergeCells("G74:G75");
			$this->excel->getActiveSheet()->mergeCells("H74:H75");
			$this->excel->getActiveSheet()->mergeCells("I74:I75");
			$this->excel->getActiveSheet()->mergeCells("C77:C78");
			$this->excel->getActiveSheet()->mergeCells("D77:D78");
			$this->excel->getActiveSheet()->mergeCells("F77:F78");
			$this->excel->getActiveSheet()->mergeCells("G77:G78");
			$this->excel->getActiveSheet()->mergeCells("H77:H78");
			$this->excel->getActiveSheet()->mergeCells("I77:I78");
			$this->excel->getActiveSheet()->mergeCells("C80:I82");
			
			$this->excel->getActiveSheet()->getStyle('B64')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('C66')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('E71')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E74')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E77')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('D71:I78')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('C71:I78')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);$this->excel->getActiveSheet()->getStyle('C68:I69')->getAlignment()->setWrapText(true); 
			$this->excel->getActiveSheet()->getStyle('C80:I82')->getAlignment()->setWrapText(true); 
			
			$this->excel->getActiveSheet()->setCellValue('B64',"II. ANALISA SOLVABILITAS");
			$this->excel->getActiveSheet()->setCellValue('C66',"1. Rasio Modal dengan Aset");
			$this->excel->getActiveSheet()->setCellValue('C68',"Adalah Rasio Modal Sendiri dengan Total Aset dikalikan 100%, dengan perhitungan sebagai berikut :");
			$this->excel->getActiveSheet()->setCellValue('C71',"Rasio  Modal  Sendiri dengan Aset");
			$this->excel->getActiveSheet()->setCellValue('D71',"=");
			$this->excel->getActiveSheet()->setCellValue('F71',"x");
			$this->excel->getActiveSheet()->setCellValue('G71',"100%");
			$this->excel->getActiveSheet()->setCellValue('E71',"Modal Sendiri");
			$this->excel->getActiveSheet()->setCellValue('E72',"Total Aset");
			$this->excel->getActiveSheet()->setCellValue('C74',"Triwulan ".$triwulan_before." tahun ".$tahun_before);
			$this->excel->getActiveSheet()->setCellValue('D74',"=");
			$this->excel->getActiveSheet()->setCellValue('F74',"x");
			$this->excel->getActiveSheet()->setCellValue('G74',"100%");
			$this->excel->getActiveSheet()->setCellValue('H74',"=");
			$this->excel->getActiveSheet()->setCellValue('C77',"Triwulan ".$triwulan." tahun ".$tahun);
			$this->excel->getActiveSheet()->setCellValue('D77',"=");
			$this->excel->getActiveSheet()->setCellValue('F77',"x");
			$this->excel->getActiveSheet()->setCellValue('G77',"100%");
			$this->excel->getActiveSheet()->setCellValue('H77',"=");

			$value_now 		= $ekuitas_now/$jumlah_aset_now;
			if(is_nan($value_now)){
				$value_now = 0;
			}
			$value_before 	= $ekuitas_before/$jumlah_aset_before;
			if(is_nan($value_before)){
				$value_before = 0;
			}
			$value = $value_now - $value_before;
			if($value > 0){
				$keterangan = "kenaikan";
			}else{
				$keterangan = "penurunan";
			}

			$this->excel->getActiveSheet()->setCellValue('E74',$ekuitas_before);
			$this->excel->getActiveSheet()->setCellValue('E75',$jumlah_aset_before);
			$this->excel->getActiveSheet()->setCellValue('I74',$value_before."%");
			$this->excel->getActiveSheet()->setCellValue('E77',$ekuitas_now);
			$this->excel->getActiveSheet()->setCellValue('E78',$jumlah_aset_now);
			$this->excel->getActiveSheet()->setCellValue('I77',$value_now."%");
			$this->excel->getActiveSheet()->setCellValue('K78',$value."%");
			$this->excel->getActiveSheet()->setCellValue('C80',"Hal ini berarti setiap Total Aset pada Triwulan ".$triwulan." tahun ".$tahun." sebesar Rp. 100,00 dijamin dengan Modal Sendiri sebesar Rp. ".number_format($value_now,2,",",".").",  dibanding dengan Triwulan ".$triwulan_before." tahun ".$tahun_before." mengalami  ".$keterangan." sebesar ".number_format(abs($value),2,",",".").".");

			//!RETURN ON EQUITY-----------------------------------------------------------------------------------------------------------------
			$this->excel->getActiveSheet()->mergeCells("C89:I90");
			$this->excel->getActiveSheet()->mergeCells("C92:C93");
			$this->excel->getActiveSheet()->mergeCells("D92:D93");
			$this->excel->getActiveSheet()->mergeCells("F92:F93");
			$this->excel->getActiveSheet()->mergeCells("G92:G93");
			$this->excel->getActiveSheet()->mergeCells("C95:C96");
			$this->excel->getActiveSheet()->mergeCells("D95:D96");
			$this->excel->getActiveSheet()->mergeCells("F95:F96");
			$this->excel->getActiveSheet()->mergeCells("G95:G96");
			$this->excel->getActiveSheet()->mergeCells("H95:H96");
			$this->excel->getActiveSheet()->mergeCells("I95:I96");
			$this->excel->getActiveSheet()->mergeCells("C98:C99");
			$this->excel->getActiveSheet()->mergeCells("D98:D99");
			$this->excel->getActiveSheet()->mergeCells("F98:F99");
			$this->excel->getActiveSheet()->mergeCells("G98:G99");
			$this->excel->getActiveSheet()->mergeCells("H98:H99");
			$this->excel->getActiveSheet()->mergeCells("I98:I99");
			$this->excel->getActiveSheet()->mergeCells("C101:I103");

			$this->excel->getActiveSheet()->getStyle('B85')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('C87')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('E92')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E95')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E98')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('D92:I99')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('C92:I99')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);$this->excel->getActiveSheet()->getStyle('C89:I90')->getAlignment()->setWrapText(true); 
			$this->excel->getActiveSheet()->getStyle('C101:I103')->getAlignment()->setWrapText(true); 

			$this->excel->getActiveSheet()->setCellValue('B85',"III. ANALISA RENTABILITAS");
			$this->excel->getActiveSheet()->setCellValue('C87',"1. Return On Equity Capital");
			$this->excel->getActiveSheet()->setCellValue('C89',"Adalah Rasio Laba (Rugi) Usaha Sebelum Pajak yang diperoleh dibandingkan dengan Kekayaan Bersih yang digunakan dalam melaksanakan usaha dikalikan 100%, dengan perhitungan sebagai berikut :");
			$this->excel->getActiveSheet()->setCellValue('C92',"Return On Equity Capital");
			$this->excel->getActiveSheet()->setCellValue('D92',"=");
			$this->excel->getActiveSheet()->setCellValue('F92',"x");
			$this->excel->getActiveSheet()->setCellValue('G92',"100%");
			$this->excel->getActiveSheet()->setCellValue('E92',"Laba (Rugi) Sebelum Pajak");
			$this->excel->getActiveSheet()->setCellValue('E93',"Ekuitas");
			$this->excel->getActiveSheet()->setCellValue('C95',"Triwulan ".$triwulan_before." tahun ".$tahun_before);
			$this->excel->getActiveSheet()->setCellValue('D95',"=");
			$this->excel->getActiveSheet()->setCellValue('F95',"x");
			$this->excel->getActiveSheet()->setCellValue('G95',"100%");
			$this->excel->getActiveSheet()->setCellValue('H95',"=");
			$this->excel->getActiveSheet()->setCellValue('C98',"Triwulan ".$triwulan." tahun ".$tahun);
			$this->excel->getActiveSheet()->setCellValue('D98',"=");
			$this->excel->getActiveSheet()->setCellValue('F98',"x");
			$this->excel->getActiveSheet()->setCellValue('G98',"100%");
			$this->excel->getActiveSheet()->setCellValue('H98',"=");

			$value_now 		= $shu_tahun_berjalan_now/$ekuitas_now;
			if(is_nan($value_now)){
				$value_now = 0;
			}
			$value_before 	= $shu_tahun_berjalan_before/$ekuitas_before;
			if(is_nan($value_before)){
				$value_before = 0;
			}
			$value = $value_now - $value_before;
			if($value > 0){
				$keterangan = "kenaikan";
			}else{
				$keterangan = "penurunan";
			}

			$this->excel->getActiveSheet()->setCellValue('E95',$shu_tahun_berjalan_before);
			$this->excel->getActiveSheet()->setCellValue('E96',$ekuitas_before);
			$this->excel->getActiveSheet()->setCellValue('I95',$value_before."%");
			$this->excel->getActiveSheet()->setCellValue('E98',$shu_tahun_berjalan_now);
			$this->excel->getActiveSheet()->setCellValue('E99',$ekuitas_now);
			$this->excel->getActiveSheet()->setCellValue('I98',$value_now."%");
			$this->excel->getActiveSheet()->setCellValue('K99',$value."%");
			$this->excel->getActiveSheet()->setCellValue('C101',"Hal ini berarti setiap Kekayaan Bersih yang digunakan Koperasi pada Triwulan ".$triwulan." tahun ".$tahun."  sebesar Rp. 100,00 akan memperoleh keuntungan sebesar  Rp. ".number_format($value_now,2,",",".").", dibandingkan dengan Triwulan ".$triwulan_before." tahun ".$tahun_before." mengalami ".$keterangan." sebesar ".number_format(abs($value),2,",",".").".");

			//!RETURN ON INVESTMENT-------------------------------------------------------------------------------------------------------------
			$this->excel->getActiveSheet()->mergeCells("C107:I108");
			$this->excel->getActiveSheet()->mergeCells("C110:C111");
			$this->excel->getActiveSheet()->mergeCells("D110:D111");
			$this->excel->getActiveSheet()->mergeCells("F110:F111");
			$this->excel->getActiveSheet()->mergeCells("G110:G111");
			$this->excel->getActiveSheet()->mergeCells("C113:C114");
			$this->excel->getActiveSheet()->mergeCells("D113:D114");
			$this->excel->getActiveSheet()->mergeCells("F113:F114");
			$this->excel->getActiveSheet()->mergeCells("G113:G114");
			$this->excel->getActiveSheet()->mergeCells("H113:H114");
			$this->excel->getActiveSheet()->mergeCells("I113:I114");
			$this->excel->getActiveSheet()->mergeCells("C116:C117");
			$this->excel->getActiveSheet()->mergeCells("D116:D117");
			$this->excel->getActiveSheet()->mergeCells("F116:F117");
			$this->excel->getActiveSheet()->mergeCells("G116:G117");
			$this->excel->getActiveSheet()->mergeCells("H116:H117");
			$this->excel->getActiveSheet()->mergeCells("I116:I117");
			$this->excel->getActiveSheet()->mergeCells("C119:I121");

			$this->excel->getActiveSheet()->getStyle('C105')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('E110')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E113')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E116')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('D110:I117')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('C110:I117')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);$this->excel->getActiveSheet()->getStyle('C107:I108')->getAlignment()->setWrapText(true); 
			$this->excel->getActiveSheet()->getStyle('C119:I121')->getAlignment()->setWrapText(true); 

			$this->excel->getActiveSheet()->setCellValue('C105',"2. Return On Investment");
			$this->excel->getActiveSheet()->setCellValue('C107',"Adalah Rasio Laba (Rugi) Usaha Sebelum Pajak yang diperoleh dibandingkan dengan Total Aset yang digunakan dalam melaksanakan kegiatan usaha dikalikan 100%, dengan perhitungan sebagai berikut :");
			$this->excel->getActiveSheet()->setCellValue('C110',"Return On Investment");
			$this->excel->getActiveSheet()->setCellValue('D110',"=");
			$this->excel->getActiveSheet()->setCellValue('F110',"x");
			$this->excel->getActiveSheet()->setCellValue('G110',"100%");
			$this->excel->getActiveSheet()->setCellValue('E110',"Laba (Rugi) Sebelum Pajak");
			$this->excel->getActiveSheet()->setCellValue('E111',"Total Aset");
			$this->excel->getActiveSheet()->setCellValue('C113',"Triwulan ".$triwulan_before." tahun ".$tahun_before);
			$this->excel->getActiveSheet()->setCellValue('D113',"=");
			$this->excel->getActiveSheet()->setCellValue('F113',"x");
			$this->excel->getActiveSheet()->setCellValue('G113',"100%");
			$this->excel->getActiveSheet()->setCellValue('H113',"=");
			$this->excel->getActiveSheet()->setCellValue('C116',"Triwulan ".$triwulan." tahun ".$tahun);
			$this->excel->getActiveSheet()->setCellValue('D116',"=");
			$this->excel->getActiveSheet()->setCellValue('F116',"x");
			$this->excel->getActiveSheet()->setCellValue('G116',"100%");
			$this->excel->getActiveSheet()->setCellValue('H116',"=");

			$value_now 		= $shu_tahun_berjalan_now/$jumlah_aset_now;
			if(is_nan($value_now)){
				$value_now = 0;
			}
			$value_before 	= $shu_tahun_berjalan_before/$jumlah_aset_before;
			if(is_nan($value_before)){
				$value_before = 0;
			}
			$value = $value_now - $value_before;
			if($value > 0){
				$keterangan = "kenaikan";
			}else{
				$keterangan = "penurunan";
			}

			$this->excel->getActiveSheet()->setCellValue('E113',$shu_tahun_berjalan_before);
			$this->excel->getActiveSheet()->setCellValue('E114',$jumlah_aset_before);
			$this->excel->getActiveSheet()->setCellValue('I113',$value_before."%");
			$this->excel->getActiveSheet()->setCellValue('E116',$shu_tahun_berjalan_now);
			$this->excel->getActiveSheet()->setCellValue('E117',$jumlah_aset_now);
			$this->excel->getActiveSheet()->setCellValue('I116',$value_now."%");
			$this->excel->getActiveSheet()->setCellValue('K117',$value."%");
			$this->excel->getActiveSheet()->setCellValue('C119',"Hal ini berarti setiap Rp. 100,00 Aset netto yang digunakan Koperasi dalam kegiatan usahanya pada Triwulan ".$triwulan." tahun ".$tahun.", akan memperoleh keuntungan  sebesar  Rp. ".number_format($value_now,2,",",".").", dibandingkan dengan Triwulan ".$triwulan_before." tahun ".$tahun_before." mengalami ".$keterangan." sebesar ".number_format(abs($value),2,",",".").".");


			$filename='Laporan Analisa Rasio Keuangan.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
							
			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
			ob_end_clean();
			$objWriter->save('php://output');
		}
	}
?>
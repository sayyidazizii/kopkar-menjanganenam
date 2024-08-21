<?php ob_start(); ?>
<?php
	ini_set('memory_limit', '512M');
	defined('BASEPATH') OR exit('No direct script access allowed');


	Class EquityChangeReport extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('EquityChangeReport_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$corebranch 									= create_double_branch($this->EquityChangeReport_model->getCoreBranch(),'branch_id','branch_name');
			$corebranch[0] 									= 'Semua Cabang';
			ksort($corebranch);
			$data['main_view']['corebranch']				= $corebranch;
			$data['main_view']['month']						= $this->configuration->Month();	
			$data['main_view']['content']					= 'EquityChangeReport/ListEquityChangeReport_view';
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
			$preferencecompany 	= $this->EquityChangeReport_model->getPreferenceCompany();
			
			if($auth['branch_status'] == 1){
				if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
					$branch_id = '';
				} else {
					$branch_id = $sesi['branch_id'];
				}
			} else {
				$branch_id = $auth['branch_id'];
			}

			// $datamutation 		= $this->EquityChangeReport_model->getEquityChangeReport($sesi['start_date'], $branch_id);
			
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

			$principal_savings_now			= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 633);
			$mandatory_savings_now			= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 634);
			$special_savings_now			= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 635);
			$cadangan_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 637);
			$hibah_now						= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 636);

			$partisipasi_reguler_now		= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 647);
			$partisipasi_non_reguler_now	= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 648);
			$gaji_now						= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 654);
			$jasa_pengurus_now				= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 655);
			$bingkisan_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 657);
			$bunga_pinjaman_now				= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 642);
			$operasional_now				= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 658);
			$seragam_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 659);
			$komputerisasi_now				= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 663);
			$perjalanan_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 674);
			$pihak_now						= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 675);
			$penyusutan_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 676);

			$shu_tahun_berjalan_now			= ($partisipasi_reguler_now + $partisipasi_non_reguler_now) - ($gaji_now + $jasa_pengurus_now + $bingkisan_now + $bunga_pinjaman_now + $operasional_now + 	$seragam_now + $komputerisasi_now + $perjalanan_now + $pihak_now + $penyusutan_now);

			$total_now						= $principal_savings_now + $mandatory_savings_now + $special_savings_now + $cadangan_now + $hibah_now + $shu_tahun_berjalan_now;

			$principal_savings_before		= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 633);
			$mandatory_savings_before		= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 634);
			$special_savings_before			= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 635);
			$cadangan_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 637);
			$hibah_before					= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 636);
			$shu_tahun_berjalan_before		= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 722);

			$partisipasi_reguler_before		= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 647);
			$partisipasi_non_reguler_before	= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 648);
			$gaji_before					= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 654);
			$jasa_pengurus_before			= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 655);
			$bingkisan_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 657);
			$bunga_pinjaman_before			= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 642);
			$operasional_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 658);
			$seragam_before					= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 659);
			$komputerisasi_before			= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 663);
			$perjalanan_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 674);
			$pihak_before					= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 675);
			$penyusutan_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 676);

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
			$filename = 'Laporan Perubahan Ekuitas Per '.$date.'.pdf';
			$pdf->Output($filename, 'I');

			//============================================================+
			// END OF FILE
			//============================================================+
		}

		public function export($sesi){	
			$auth 				= $this->session->userdata('auth'); 
			$preferencecompany 	= $this->EquityChangeReport_model->getPreferenceCompany();
			
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

			$principal_savings_now			= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 633);
			$mandatory_savings_now			= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 634);
			$special_savings_now			= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 635);
			$cadangan_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 637);
			$hibah_now						= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 636);

			$partisipasi_reguler_now		= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 647);
			$partisipasi_non_reguler_now	= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 648);
			$gaji_now						= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 654);
			$jasa_pengurus_now				= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 655);
			$bingkisan_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 657);
			$bunga_pinjaman_now				= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 642);
			$operasional_now				= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 658);
			$seragam_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 659);
			$komputerisasi_now				= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 663);
			$perjalanan_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 674);
			$pihak_now						= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 675);
			$penyusutan_now					= $this->EquityChangeReport_model->getOpeningBalance(($bulan+1), $tahun, 676);
			$shu_dibagi_now					= 0;

			$shu_tahun_berjalan_now			= ($partisipasi_reguler_now + $partisipasi_non_reguler_now) - ($gaji_now + $jasa_pengurus_now + $bingkisan_now + $bunga_pinjaman_now + $operasional_now + 	$seragam_now + $komputerisasi_now + $perjalanan_now + $pihak_now + $penyusutan_now);

			$total_now						= $principal_savings_now + $mandatory_savings_now + $special_savings_now + $cadangan_now + $hibah_now + $shu_tahun_berjalan_now;

			$principal_savings_before		= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 633);
			$mandatory_savings_before		= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 634);
			$special_savings_before			= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 635);
			$cadangan_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 637);
			$hibah_before					= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 636);
			$shu_tahun_berjalan_before		= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 722);

			$partisipasi_reguler_before		= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 647);
			$partisipasi_non_reguler_before	= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 648);
			$gaji_before					= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 654);
			$jasa_pengurus_before			= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 655);
			$bingkisan_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 657);
			$bunga_pinjaman_before			= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 642);
			$operasional_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 658);
			$seragam_before					= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 659);
			$komputerisasi_before			= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 663);
			$perjalanan_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 674);
			$pihak_before					= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 675);
			$penyusutan_before				= $this->EquityChangeReport_model->getOpeningBalance(($bulan_before+1), $tahun_before, 676);
			$shu_dibagi_before				= 0;

			$shu_tahun_berjalan_before		= ($partisipasi_reguler_before + $partisipasi_non_reguler_before) - ($gaji_before + $jasa_pengurus_before + $bingkisan_before + $bunga_pinjaman_before + $operasional_before + 	$seragam_before + $komputerisasi_before + $perjalanan_before + $pihak_before + $penyusutan_before);

			$total_before					= $principal_savings_before + $mandatory_savings_before + $special_savings_before + $cadangan_before + $hibah_before + $shu_tahun_berjalan_before;

			$this->load->library('Excel');
			
			$this->excel->getProperties()->setCreator("CST FISRT")
									->setLastModifiedBy("CST FISRT")
									->setTitle("Laporan Data Perubahan Ekuitas")
									->setSubject("")
									->setDescription("Laporan Data Perubahan Ekuitas")
									->setKeywords("Laporan Data Perubahan Ekuitas")
									->setCategory("Laporan Data Perubahan Ekuitas");
									
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
					
			$this->excel->getActiveSheet()->mergeCells("B2:D2");
			$this->excel->getActiveSheet()->mergeCells("B3:D3");
			$this->excel->getActiveSheet()->mergeCells("B4:D4");
			$this->excel->getActiveSheet()->mergeCells("B5:D5");
			$this->excel->getActiveSheet()->mergeCells("C8:D8");
			$this->excel->getActiveSheet()->mergeCells("B21:C22");
			$this->excel->getActiveSheet()->mergeCells("D21:D22");
			$this->excel->getActiveSheet()->getStyle('B5')->getFont()->setSize(9);
			$this->excel->getActiveSheet()->getStyle('B2:D8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B21:D22')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('B2:D4')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('B8:D8')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('B9')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('B21:D22')->getFont()->setBold(true);
			$this->excel->getActiveSheet()->getStyle('B8:D8')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B21:D22')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('B9:B20')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('C9:C20')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('D9:D20')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('E9:E20')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->excel->getActiveSheet()->getStyle('C18')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$this->excel->getActiveSheet()->setCellValue('B2',"KOPERASI KARYAWAN MENJANGAN ENAM");
			$this->excel->getActiveSheet()->setCellValue('B3',"LAPORAN PERUBAHAN EKUITAS");
			$this->excel->getActiveSheet()->setCellValue('B4',"Per ".$date);
			$this->excel->getActiveSheet()->setCellValue('B5',"(Dinyatakan dalam Rupiah)");
			$this->excel->getActiveSheet()->setCellValue('B8',"Keterangan");
			$this->excel->getActiveSheet()->setCellValue('B9',"Ekuitas Per ".$date_before);
			$this->excel->getActiveSheet()->setCellValue('C8',"Per ".$date);
			$this->excel->getActiveSheet()->setCellValue('B11',"Ditambah/Dikurangi");
			$this->excel->getActiveSheet()->setCellValue('B12',"Simpanan Pokok");
			$this->excel->getActiveSheet()->setCellValue('B13',"Simpanan Wajib");
			$this->excel->getActiveSheet()->setCellValue('B14',"Simpanan Khusus");
			$this->excel->getActiveSheet()->setCellValue('B15',"Cadangan");
			$this->excel->getActiveSheet()->setCellValue('B16',"Hibah");
			$this->excel->getActiveSheet()->setCellValue('B17',"Sisa Hasil Usaha (SHU) Dibagi");
			$this->excel->getActiveSheet()->setCellValue('B18',"Sisa Hasil Usaha Tahun Berjalan");
			$this->excel->getActiveSheet()->setCellValue('B20',"Jumlah Penambahan");
			$this->excel->getActiveSheet()->setCellValue('B21',"Ekuitas Per ".$date);

			$this->excel->getActiveSheet()->setCellValue('D9', $total_before);
			$this->excel->getActiveSheet()->setCellValue('C12', ($principal_savings_now - $principal_savings_before));
			$this->excel->getActiveSheet()->setCellValue('C13', ($mandatory_savings_now - $mandatory_savings_before));
			$this->excel->getActiveSheet()->setCellValue('C14', ($special_savings_now - $special_savings_before));
			$this->excel->getActiveSheet()->setCellValue('C15', ($cadangan_now - $cadangan_before));
			$this->excel->getActiveSheet()->setCellValue('C16', ($hibah_now - $hibah_before));
			$this->excel->getActiveSheet()->setCellValue('C17', ($shu_dibagi_now - $shu_dibagi_before));
			$this->excel->getActiveSheet()->setCellValue('C18', ($shu_tahun_berjalan_now - $shu_tahun_berjalan_before));
			$this->excel->getActiveSheet()->setCellValue('D20', ($total_now-$total_before));
			$this->excel->getActiveSheet()->setCellValue('D21', $total_now);

			$filename='Laporan Perubahan Ekuitas.xls';
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
							
			$objWriter = IOFactory::createWriter($this->excel, 'Excel5');  
			ob_end_clean();
			$objWriter->save('php://output');
		}
	}
?>
<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctDebtMemberPrint extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctDebtMemberPrint_model');
			$this->load->model('AcctDebtPrint_model');
			$this->load->model('CoreMember_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data['main_view']['coredivision']					= create_double($this->AcctDebtMemberPrint_model->getCoreDivision(), 'division_id', 'division_name');
			$data['main_view']['content']					= 'AcctDebtMemberPrint/ListAcctDebtMemberPrint_view';
			$this->load->view('MainPage_view',$data);
		}
 
		public function viewreport(){
			$sesi = array (
				"division_id"	=> $this->input->post('division_id',true),
			);

			$this->processPrintDebt($sesi);
		}

		public function processPrintDebt(){
			$sesi = array (
				"division_id"	=> $this->input->post('division_id',true),
			);

			$coremember			= $this->AcctDebtMemberPrint_model->getCoreMemberByDivision($sesi['division_id']);
			$preferencecompany 	= $this->CoreMember_model->getPreferenceCompany();

			ob_start();
			set_time_limit(0);
			ini_set("memory_limit", "2560M");

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			
			// Ukuran matte paper adalah 8.5 x 11 inci
			$pdf = new TCPDF('P', PDF_UNIT, array(8.5 * 25.4, 11 * 25.4), true, 'UTF-8', false);
		
			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);
			$pdf->setFontSubsetting(false);
		
			$pdf->SetMargins(3, 3, 3, 3	);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
				require_once(dirname(__FILE__).'/lang/eng.php');
				$pdf->setLanguageArray($l);
			}

			$pdf->SetFont('helvetica', 'B', 15);
			$pdf->AddPage();
			$pdf->SetFont('helvetica', '', 7);

			$tbl = '';

			foreach($coremember as $key => $val){
				$simpanan_pokok		= 0;
				$simpanan_wajib		= 0;
				$simpanan_sukarela	= 0;
				$angsuran_uang		= 0;
				$potongan_toko		= 0;
				$angsuran_barang	= 0;
				$angsuran_elektro	= 0;
				$angsuran_beras		= 0;
				$iuran_tenis		= 0;
				$sicantik			= 0;
				$angsuran_sepeda	= 0;
				$angsuran_sim		= 0;
				$potongan_listrik	= 0;
				$potongan_obat		= 0;
				$potongan_perumahan	= 0;
				$total 				= 0;

				$month 				= date('m');
				$year 				= date('Y');		
				$start_date 		= date('Y-m-d', strtotime($year.'-'.$month.'-01'));
				$end_date 			= date('Y-m-d', strtotime(date('t').'-'.$month.'-'.$year));
				$sesi 				= array();
				$sesi['start_date'] = $start_date;
				$sesi['end_date'] 	= $end_date;

				$debtcategory 		 = $this->AcctDebtPrint_model->getMemberDebtCategory($sesi, $val['member_id']);
				$debtsavings 		 = $this->AcctDebtPrint_model->getMemberDebtSavings($sesi, $val['member_id']);
				$debtsavingssicantik = $this->AcctDebtPrint_model->getMemberDebtSavingsSicantik($sesi, $val['member_id']);
				$debtcredits 		 = $this->AcctDebtPrint_model->getMemberDebtCredits($sesi, $val['member_id']);
				$debtstore	 		 = $this->AcctDebtPrint_model->getMemberDebtStore($sesi, $val['member_id']);
				$debtmembersavings	 = $this->AcctDebtPrint_model->getMemberDebtMemberSavings($sesi, $val['member_id']);

				if($debtcategory){
					foreach($debtcategory as $keyy => $vall){
						if($vall['debt_category_code'] == "KB"){
							$angsuran_beras 	+= $vall['debt_amount'];
							$total 				+= $vall['debt_amount'];
						}
						if($vall['debt_category_code'] == "OSB"){
							$potongan_obat 		+= $vall['debt_amount'];
							$total 				+= $vall['debt_amount'];
						}
						if($vall['debt_category_code'] == "LPT"){
							$potongan_listrik 	+= $vall['debt_amount'];
							$total 				+= $vall['debt_amount'];
						}
					}
				}

				if($debtsavings){
					foreach($debtsavings as $keyy => $vall){
						$simpanan_sukarela 	+= $vall['savings_cash_mutation_amount'];
						$total 		+= $vall['savings_cash_mutation_amount'];
					}
				}

				if($debtsavings){
					foreach($debtsavings as $keyy => $vall){
						$simpanan_sukarela 	+= $vall['savings_cash_mutation_amount'];
						$total 		+= $vall['savings_cash_mutation_amount'];
					}
				}

				if($debtsavingssicantik){
					foreach($debtsavingssicantik as $keyy => $vall){
						$sicantik 	+= $vall['savings_cash_mutation_amount'];
						$total 		+= $vall['savings_cash_mutation_amount'];
					}
				}

				if($debtcredits){
					foreach($debtcredits as $keyy => $vall){
						if($vall['credits_id'] == 1){
							$angsuran_uang 	+= $vall['credits_payment_amount'];
							$total 			+= $vall['credits_payment_amount'];
						}
					}
				}

				if($debtstore){
					foreach($debtstore as $keyy => $vall){
						$potongan_toko 	+= $vall['total_amount'];
						$total 			+= $vall['total_amount'];
					}
				}
				
				if($debtmembersavings){
					foreach($debtmembersavings as $keyy => $vall){
						$simpanan_pokok 	+= $vall['principal_savings_amount'];
						$simpanan_wajib 	+= $vall['mandatory_savings_amount'];
						$total 				+= $vall['principal_savings_amount'];
						$total 				+= $vall['mandatory_savings_amount'];
					}
				}
				
				$base_url 	= base_url();
				$month 		= $this->configuration->MonthUpperCase();
				$bulan 		= $month[date('m')];
				$date  		= date('d').' '.$bulan.' '.date('Y');

				$tbl = "
				<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
					<tr>
						<td rowspan=\"2\" width=\"10%\">" .$img."</td>
					</tr>
					<tr>
					</tr>
				</table>
				<br/>
				
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
					<tr>
						<td width=\"100%\"><hr/></td>
					</tr>
					<tr>
						<td width=\"50%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: center;\">KOPERASI MENJANGAN ENAM</div></td>
						<td width=\"12%\"><div style=\"text-align: left;\"> Perincian SIM / STNK</div></td>
						<td width=\"2%\"><div style=\"text-align: left;\">:</div></td>
					</tr>
					<tr>
						<td width=\"50%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: center;\">PERINCIAN POTONGAN TANGGAL : ".$date."</div></td>
					</tr>
					<tr>
						<td height=\"1px\"></td>
					</tr>
					<tr>
						<td width=\"50%\"><hr/></td>
					</tr>
					<tr>
						<td width=\"3%\"></td>
						<td width=\"12%\"><div style=\"text-align: left;\">No. Anggota</div></td>
						<td width=\"35%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: left;\">: ".$val['member_no']."</div></td>
						<td width=\"12%\"><div style=\"text-align: left;\"> Perincian Uang</div></td>
						<td width=\"2%\"><div style=\"text-align: left;\">:</div></td>
					</tr>
					<tr>
						<td width=\"3%\"></td>
						<td width=\"12%\"><div style=\"text-align: left;\">Nama</div></td>
						<td width=\"35%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: left;\">: ".$val['member_name']."</div></td>
					</tr>
					<tr>
						<td width=\"3%\"></td>
						<td width=\"12%\"><div style=\"text-align: left;\">Seksi</div></td>
						<td width=\"35%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: left;\">: ".$this->CoreMember_model->getCorePartNameFromId($val['part_id'])."</div></td>
					</tr>	
					<tr>
						<td height=\"1px\"></td>
					</tr>
					<tr>
						<td width=\"50%\"><hr/></td>
					</tr>
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">01. Simpanan Pokok</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$simpanan_pokok."</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">02. Simpanan Wajib</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$simpanan_wajib."</div></td>
						<td width=\"12%\"><div style=\"text-align: left;\"> Perincian Elektro</div></td>
						<td width=\"2%\"><div style=\"text-align: left;\">:</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">03. Simpanan Sukarela</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$simpanan_sukarela."</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">04. Total Angs. Uang</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$angsuran_uang."</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">05. Potongan Toko</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$potongan_toko."</div></td>
					</tr>
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">06. Total Angs. Barang</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$angsuran_barang."</div></td>
						<td width=\"12%\"><div style=\"text-align: left;\"> Perincian Barang</div></td>
						<td width=\"2%\"><div style=\"text-align: left;\">:</div></td>
					</tr>
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">07. Total Angs. Elektro</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$angsuran_elektro."</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">08. Total Angs. Beras</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$angsuran_beras."</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">09. Iuran Tenis</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$iuran_tenis."</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">10. SICANTIK / SITRENDI</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$sicantik."</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">11. Angsuran Sepeda</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$angsuran_sepeda."</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">12. Total Angsuran SIM / STNK</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$angsuran_sim."</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">13. Potongan Listrik / PAM</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$potongan_listrik."</div></td>
						<td width=\"12%\"><div style=\"text-align: left;\"> Perincian Uang</div></td>
						<td width=\"2%\"><div style=\"text-align: left;\">:</div></td>
					</tr>	
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">14. Potongan Obat</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$potongan_obat."</div></td>
					</tr>
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">15. Potongan Perumahan</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">: Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$potongan_perumahan."</div></td>
					</tr>
					<tr>
						<td height=\"1px\"></td>
					</tr>
					<tr>
						<td width=\"50%\"><hr/></td>
					</tr>
					<tr>
						<td width=\"3%\"></td>
						<td width=\"27%\"><div style=\"text-align: left;\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TOTAL</div></td>
						<td width=\"5%\"><div style=\"text-align: left;\">  Rp.</div></td>
						<td width=\"15%\" style=\"border-right: 1px solid black;\"><div style=\"text-align: right;\">".$total."</div></td>
					</tr>
					<hr/>
					<tr>
						<td height=\"1px\"></td>
					</tr>
					<tr>
						<td width=\"50%\"></td>
						<td width=\"50%\"><div style=\"text-align: center;\">Semarang, ".date('d-m-Y')."</div></td>
					</tr>
					<tr>
						<td height=\"10px\"></td>
					</tr>
					<tr>
						<td width=\"50%\"></td>
						<td width=\"50%\"><div style=\"text-align: center;\">Yuli Risdianto</div></td>
					</tr>
				</table>";

				$pdf->writeHTML($tbl, true, false, false, false, '');
			}

			ob_clean();

			$filename = 'Slip Potong Gaji'.'.pdf';
			$pdf->Output($filename, 'I');
		}
	}
?>
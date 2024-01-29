<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctCreditsAgunan extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctCreditsAgunan_model');
			$this->load->model('CoreMember_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data['main_view']['corebranch']	= create_double($this->AcctCreditsAgunan_model->getCoreBranch(),'branch_id','branch_name');
			$data['main_view']['content']		= 'AcctCreditsAgunan/ListAcctCreditsAgunan_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				"branch_id" 	=> $this->input->post('branch_id',true),
			);

			$this->session->set_userdata('filter-acctcreditsagunan',$data);
			redirect('credits-agunan');
		}

		public function reset_search(){
			$this->session->unset_userdata('filter-acctcreditsagunan');
			redirect('credits-agunan');
		}

		public function getAcctCreditsAgunanList(){
			$auth = $this->session->userdata('auth');

			if($auth['branch_status'] == 1){
				$sesi	= 	$this->session->userdata('filter-acctcreditsagunan');
				if(!is_array($sesi)){
					$sesi['branch_id']		= '';
				}
			} else {
				$sesi['branch_id']	= $auth['branch_id'];
			}

			$agunanstatus = $this->configuration->AgunanStatus();

			$list = $this->AcctCreditsAgunan_model->get_datatables($sesi['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $agunan) {
				if($agunan->credits_agunan_type == 1){
					$credits_agunan_type	= 'Penerimaan Anggota Dari Perusahaan';
					$credits_agunan_ket		= $agunan->credits_agunan_penerimaan_description;
				}else if($agunan->credits_agunan_type == 2){
					$credits_agunan_type 	= 'Deposito';
					$credits_agunan_ket		= "No Deposito : ".$agunan->credits_agunan_deposito_account_no;
				}else{
					$credits_agunan_type 	= 'Lain - Lain';
					$credits_agunan_ket		= $agunan->credits_agunan_other_description;
				}
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $agunan->credits_account_serial;
	            $row[] = $this->AcctCreditsAgunan_model->getMemberName($agunan->member_id);
	            $row[] = $agunanstatus[$agunan->credits_agunan_status];
	            $row[] = $credits_agunan_type;
	            $row[] = $credits_agunan_ket;
	            if($agunan->credits_agunan_status == 0){
            		$row[] = '
					<a href="'.base_url().'credits-agunan/update-status/'.$agunan->credits_agunan_id.'" onClick="javascript:return confirm(\'Yakin status agunan akan diupdate ?\')" class="btn default btn-xs purple" role="button"><i class="fa fa-edit"></i> Update</a>
					<a href="'.base_url().'credits-agunan/print-receipt/'.$agunan->credits_agunan_id.'" class="btn default btn-xs yellow-lemon" role="button"><i class="fa fa-edit"></i> Tanda Terima</a>';
            	} else {
            		$row[] = '';
            	}
	            $data[] = $row;
	        }
	        // print_r($list);exit;
	 
	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->AcctCreditsAgunan_model->count_all($sesi['branch_id']),
	                        "recordsFiltered" => $this->AcctCreditsAgunan_model->count_filtered($sesi['branch_id']),
	                        "data" => $data,
	                );
	        //output to json format
	        echo json_encode($output);
		}

		public function updateAgunanStatus(){
			if($this->AcctCreditsAgunan_model->updateAgunanStatus($this->uri->segment(3))){
				$auth = $this->session->userdata('auth');
				$msg = "<div class='alert alert-success alert-dismissable'>                 
							Update Status Agunan Sukses
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('credits-agunan');
			}else{
				$msg = "<div class='alert alert-danger alert-dismissable'>                
							Update Status Agunan Tidak Berhasil
						</div> ";
				$this->session->set_userdata('message',$msg);
				redirect('credits-agunan');
			}
		}

		public function export(){	
			$auth = $this->session->userdata('auth');
			$agunanstatus = $this->configuration->AgunanStatus();

			if($auth['branch_status'] == 1){
				$sesi	= 	$this->session->userdata('filter-acctcreditsagunan');
				if(!is_array($sesi)){
					$sesi['branch_id']		= '';
				}
			} else {
				$sesi['branch_id']	= $auth['branch_id'];
			}

			$acctcreditsagunan	= $this->AcctCreditsAgunan_model->getExportAcctCreditsAgunan($sesi['branch_id']);

			
			if($acctcreditsagunan->num_rows()!=0){
				$this->load->library('Excel');
				
				$this->excel->getProperties()->setCreator("SIS")
									 ->setLastModifiedBy("SIS")
									 ->setTitle("Master Data Agunan")
									 ->setSubject("")
									 ->setDescription("Master Data Agunan")
									 ->setKeywords("Master, Data, Agunan")
									 ->setCategory("Master Data Agunan");
									 
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(10);

				
				$this->excel->getActiveSheet()->mergeCells("B1:G1");
				$this->excel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
				$this->excel->getActiveSheet()->getStyle('B3:G3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$this->excel->getActiveSheet()->getStyle('B3:G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('B3:G3')->getFont()->setBold(true);	
				$this->excel->getActiveSheet()->setCellValue('B1',"Master Data Agunan");	
				
				$this->excel->getActiveSheet()->setCellValue('B3',"No");
				$this->excel->getActiveSheet()->setCellValue('C3',"No. Akad");
				$this->excel->getActiveSheet()->setCellValue('D3',"Nama Anggota");
				$this->excel->getActiveSheet()->setCellValue('E3',"Tipe Agunan");
				$this->excel->getActiveSheet()->setCellValue('F3',"Keterangan");
				$this->excel->getActiveSheet()->setCellValue('G3',"Status");
				
				$j=4;
				$no=0;
				
				foreach($acctcreditsagunan->result_array() as $key=>$val){
					if(is_numeric($key)){
						$no++;
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->getStyle('B'.$j.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$this->excel->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
						$this->excel->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

						if($val['credits_agunan_type'] == 1){
							$type = "Penerimaan Anggota Dari Perusahaan";
							$desc = $val['credits_agunan_penerimaan_description'];
						}else if($val['credits_agunan_type'] == 2){
							$type = "Deposito";
							$desc = "No Deposito : ".$val['credits_agunan_deposito_account_no'];
						}else{
							$type = "Lain - Lain";
							$desc = $val['credits_agunan_other_description'];
						}

						$this->excel->getActiveSheet()->setCellValue('B'.$j, $no);
						$this->excel->getActiveSheet()->setCellValueExplicit('C'.$j, $val['credits_account_serial']);
						$this->excel->getActiveSheet()->setCellValue('D'.$j, $this->AcctCreditsAgunan_model->getMemberName($val['member_id']));
						$this->excel->getActiveSheet()->setCellValue('E'.$j, $type);
						$this->excel->getActiveSheet()->setCellValue('F'.$j, $desc);
						$this->excel->getActiveSheet()->setCellValue('G'.$j, $agunanstatus[$val['credits_agunan_status']]);	
					}else{
						continue;
					}
					$j++;
				}
				$filename='Master Data Agunan.xls';
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

		public function printAgunanReceipt(){
			$auth 					= $this->session->userdata('auth');
			$credits_agunan_id 		= $this->uri->segment(3);
			$agunandetail		 	= $this->AcctCreditsAgunan_model->getAcctCreditAgunanDetail($credits_agunan_id);

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);

			$pdf->SetMargins(7, 7, 7, 7); 
			
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			    require_once(dirname(__FILE__).'/lang/eng.php');
			    $pdf->setLanguageArray($l);
			}

			$pdf->SetFont('helvetica', 'B', 20);

			$pdf->AddPage();


			$pdf->SetFont('helvetica', '', 12);

			$base_url = base_url();

			$tbl1 = "
			<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
				<tr>
					<td style=\"text-align:center;\" width=\"100%\">
						<div style=\"font-size:14px; font-weight:bold\">KOPERASI KARYAWAN MENJANGAN ENAM</div>
					</td>			
				</tr>
				<tr>
					<td style=\"text-align:center;\" width=\"100%\">
						<div style=\"font-size:14px; font-weight:bold\">TANDA TERIMA JAMINAN</div>
					</td>			
				</tr>
			</table>
			<br>
			<hr>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
					<br>
			    </tr>
			    <tr>
					<td style=\"text-align:left;\" width=\"15%\">
						<div style=\"font-size:12px;\">Tanggal</div>
					</td>
					<td style=\"text-align:left;\" width=\"2%\">
						<div style=\"font-size:12px;\">:</div>
					</td>
					<td style=\"text-align:left;\" width=\"80%\">
						<div style=\"font-size:12px;\">".date('d-m-Y', strtotime($agunandetail['credits_account_date']))."</div>
					</td>	
			    </tr>	
			    <tr>
					<td style=\"text-align:left;\" width=\"15%\">
						<div style=\"font-size:12px;\">Nama</div>
					</td>
					<td style=\"text-align:left;\" width=\"2%\">
						<div style=\"font-size:12px;\">:</div>
					</td>
					<td style=\"text-align:left;\" width=\"80%\">
						<div style=\"font-size:12px;\">".$agunandetail['member_name']."</div>
					</td>	
			    </tr>	
			    <tr>
					<td style=\"text-align:left;\" width=\"15%\">
						<div style=\"font-size:12px;\">Bagian</div>
					</td>
					<td style=\"text-align:left;\" width=\"2%\">
						<div style=\"font-size:12px;\">:</div>
					</td>
					<td style=\"text-align:left;\" width=\"80%\">
						<div style=\"font-size:12px;\">".$agunandetail['division_name']."</div>
					</td>	
			    </tr>	
			    <tr>
					<td style=\"text-align:left;\" width=\"15%\">
						<div style=\"font-size:12px;\">NIK / HP</div>
					</td>
					<td style=\"text-align:left;\" width=\"2%\">
						<div style=\"font-size:12px;\">:</div>
					</td>
					<td style=\"text-align:left;\" width=\"80%\">
						<div style=\"font-size:12px;\">".$agunandetail['member_identity_no']." / ".$agunandetail['member_phone']."</div>
					</td>	
			    </tr>	
			    <tr>
					<td style=\"text-align:left;\" width=\"20%\">
						<div style=\"font-size:12px;\">Rp ".nominal($agunandetail['credits_account_amount'])."</div>
					</td>
					<td style=\"text-align:left;\" width=\"60%\">
						<div style=\"font-size:12px;\"></div>
					</td>
					<td style=\"text-align:right;\" width=\"20%\">
						<div style=\"font-size:12px;\">".$agunandetail['credits_account_period']." Bln</div>
					</td>	
			    </tr>				
			</table>
			<br>

			<table id=\"items\" width=\"91%\" cellspacing=\"0\" cellpadding=\"5\" border=\"1\">
				<tr>";
				if($this->AcctCreditsAgunan_model->getAgunanByType(1, $agunandetail['credits_account_id'])){
					$tbl1 .= "
					<td style=\"text-align:center; font-weight:bold;\" width=\"10%\">
						<div style=\"font-size:12px;\">V</div>
					</td>";
				}else{
					$tbl1 .= "
					<td style=\"text-align:center; font-weight:bold;\" width=\"10%\">
						<div style=\"font-size:12px;\">X</div>
					</td>";
				}
					$tbl1 .= "
					<td style=\"text-align:left;\" width=\"45%\">
						<div style=\"font-size:12px;\">Penerimaan Anggota Dari Perusahaan</div>
					</td>
					<td style=\"text-align:left;\" width=\"55%\">
						<div style=\"font-size:12px;\">".$this->AcctCreditsAgunan_model->getAgunanPenerimaanDescription(1, $agunandetail['credits_account_id'])."</div>
					</td>
				</tr>
				<tr>";
				if($this->AcctCreditsAgunan_model->getAgunanByType(2, $agunandetail['credits_account_id'])){
					$tbl1 .= "
					<td style=\"text-align:center; font-weight:bold;\" width=\"10%\">
						<div style=\"font-size:12px;\">V</div>
					</td>";
				}else{
					$tbl1 .= "
					<td style=\"text-align:center; font-weight:bold;\" width=\"10%\">
						<div style=\"font-size:12px;\">X</div>
					</td>";
				}
					$tbl1 .= "
					<td style=\"text-align:left;\" width=\"45%\">
						<div style=\"font-size:12px;\">Deposito</div>
					</td>
					<td style=\"text-align:left;\" width=\"55%\">
						<div style=\"font-size:12px;\">".$this->AcctCreditsAgunan_model->getAgunanDepositoDescription(2, $agunandetail['credits_account_id'])."</div>
					</td>	
				</tr>
				<tr>";
				if($this->AcctCreditsAgunan_model->getAgunanByType(4, $agunandetail['credits_account_id'])){
					$tbl1 .= "
					<td style=\"text-align:center; font-weight:bold;\" width=\"10%\">
						<div style=\"font-size:12px;\">V</div>
					</td>";
				}else{
					$tbl1 .= "
					<td style=\"text-align:center; font-weight:bold;\" width=\"10%\">
						<div style=\"font-size:12px;\">X</div>
					</td>";
				}
					$tbl1 .= "
					<td style=\"text-align:left;\" width=\"45%\">
						<div style=\"font-size:12px;\">Lain - Lain</div>
					</td>
					<td style=\"text-align:left;\" width=\"55%\">
						<div style=\"font-size:12px;\">".$this->AcctCreditsAgunan_model->getAgunanOtherDescription(4, $agunandetail['credits_account_id'])."</div>
					</td>			
				</tr>
			</table>
			";
			
		$tbl1 .="
		<table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
			<tr>	
			   <td style=\"text-align:center;\" width=\"50%\" height=\"100px\">
				   <div style=\"font-size:12px;\">
					   Peminjam</div>
			   </td>
			   <td style=\"text-align:center;\" width=\"50%\" height=\"100px\">
				   <div style=\"font-size:12px;\">
					   Pengurus</div>
			   </td>			
			</tr>
			<tr>	
			   <td style=\"text-align:center;\" width=\"50%\" height=\"100px\">
				   <div style=\"font-size:12px;\">".$agunandetail['member_name']."</div>
			   </td>
			   <td style=\"text-align:center;\" width=\"50%\" height=\"100px\">
				   <div style=\"font-size:12px;\">".$agunandetail['office_name']."</div>
			   </td>			
			</tr>
		</table>";

			$pdf->writeHTML($tbl1, true, false, false, false, '');

			ob_clean();

			
			$filename = 'Kwitansi.pdf';
			$pdf->Output($filename, 'I');

		}
		
	}
?>
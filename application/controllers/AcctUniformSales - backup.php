<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class AcctUniformSales extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('AcctUniformSales_model');
			$this->load->model('AcctSavingsAccount_model');
			$this->load->model('CoreMember_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$auth 	= $this->session->userdata('auth');
			$unique = $this->session->userdata('unique');
			$this->session->unset_userdata('acctuniformsalestoken-'.$unique['unique']);

			$data['main_view']['content']	= 'AcctUniformSales/ListAcctUniformSales_view';
			$this->load->view('MainPage_view',$data);
		}

		public function filter(){
			$data = array (
				"start_date" 	=> tgltodb($this->input->post('start_date',true)),
				"end_date" 		=> tgltodb($this->input->post('end_date',true)),
			);

			$this->session->set_userdata('filter-acctuniformsales',$data);
			redirect('uniform-sales');
		}

		public function reset_search(){
			$this->session->unset_userdata('filter-acctuniformsales');
			redirect('uniform-sales');
		}

		public function getAcctUniformSales(){
			$auth 	= $this->session->userdata('auth');
			$sesi	= $this->session->userdata('filter-acctuniformsales');

			if(!is_array($sesi)){
				$sesi['start_date']				= date('Y-m-d');
				$sesi['end_date']				= date('Y-m-d');
			}

			$list = $this->AcctUniformSales_model->get_datatables($sesi['start_date'], $sesi['end_date'], $auth['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $uniformsales) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $uniformsales->uniform_sales_no;
	            $row[] = $uniformsales->member_no;
	            $row[] = $uniformsales->member_name;
	            $row[] = tgltoview($uniformsales->uniform_sales_date);
	            $row[] = $uniformsales->uniform_sales_size;
	            $row[] = number_format($uniformsales->uniform_sales_price, 2);
	            $row[] = $uniformsales->uniform_sales_remark;
				$row[] = '<a href="'.base_url().'uniform-sales/print-note/'.$uniformsales->uniform_sales_id.'" class="btn btn-info btn-xs" role="button"><span class="glyphicon glyphicon-print"></span> Kwitansi</a>';
	            $data[] = $row;
	        }
	 
	        $output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->AcctUniformSales_model->count_all($sesi['start_date'], $sesi['end_date'], $auth['branch_id']),
				"recordsFiltered" => $this->AcctUniformSales_model->count_filtered($sesi['start_date'], $sesi['end_date'], $auth['branch_id']),
				"data" => $data,
	                );
	        echo json_encode($output);
		}

		public function function_elements_add(){
			$unique 	= $this->session->userdata('unique');
			$name 		= $this->input->post('name',true);
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctuniformsales-'.$unique['unique']);
			$sessions[$name] = $value;
			$this->session->set_userdata('addacctuniformsales-'.$unique['unique'],$sessions);
		}

		public function reset_data(){
			$unique 	= $this->session->userdata('unique');
			$sessions	= $this->session->unset_userdata('addacctuniformsales-'.$unique['unique']);
			redirect('uniform-sales/add');
		}
		
		public function addAcctUniformSales(){
			$uniform_sales_id = $this->uri->segment(3);

			$unique = $this->session->userdata('unique');
			$token 	= $this->session->userdata('acctuniformsalestoken-'.$unique['unique']);

			if(empty($token)){
				$token = md5(date('Y-m-d H:i:s'));
				$this->session->set_userdata('acctuniformsalestoken-'.$unique['unique'], $token);
			}

			$data['main_view']['coremember']			= $this->AcctSavingsAccount_model->getCoreMember_Detail($this->uri->segment(3));
			$data['main_view']['paymenttype']			= $this->configuration->UniformPaymentType();
			$data['main_view']['content']				= 'AcctUniformSales/FormAddAcctUniformSales_view';
			$this->load->view('MainPage_view',$data);
		}


		public function getListCoreMember(){
			$auth = $this->session->userdata('auth');

			$list = $this->CoreMember_model->get_datatables_status($auth['branch_id']);
	        $data = array();
	        $no = $_POST['start'];
	        foreach ($list as $customers) {
	            $no++;
	            $row = array();
	            $row[] = $no;
	            $row[] = $customers->member_no;
	            $row[] = $customers->member_name;
	            $row[] = $customers->member_address;
	            $row[] = '<a href="'.base_url().'uniform-sales/add/'.$customers->member_id.'" class="btn btn-info" role="button"><span class="glyphicon glyphicon-ok"></span> Select</a>';
	            $data[] = $row;
	        }
	 
	        $output = array(
	                        "draw" => $_POST['draw'],
	                        "recordsTotal" => $this->CoreMember_model->count_all($auth['branch_id']),
	                        "recordsFiltered" => $this->CoreMember_model->count_filtered($auth['branch_id']),
	                        "data" => $data,
	                );
	        echo json_encode($output);
		}	

		public function getMutationFunction(){
			$mutation_id 	= $this->input->post('mutation_id');
			
			$mutation_function 			= $this->AcctUniformSales_model->getMutationFunction($mutation_id);
			echo json_encode($mutation_function);		
		}
		
		public function processAddAcctUniformSales(){
			$auth = $this->session->userdata('auth');

			$data = array(
				'member_id'									=> $this->input->post('member_id', true),
				'branch_id'									=> $auth['branch_id'],
				'uniform_sales_date'						=> date('Y-m-d', strtotime($this->input->post('uniform_sales_date', true))),
				'uniform_sales_size'						=> $this->input->post('uniform_sales_size', true),
				'uniform_sales_price'						=> $this->input->post('uniform_sales_price', true),
				'uniform_sales_payment_type'				=> $this->input->post('uniform_sales_payment_type', true),
				'uniform_sales_remark'						=> $this->input->post('uniform_sales_remark', true),
				'uniform_sales_token'						=> $this->input->post('uniform_sales_token', true),
				'created_id'								=> $auth['user_id'],
				'created_on'								=> date('Y-m-d H:i:s'),
			);
			
			$this->form_validation->set_rules('member_id', 'No. Anggota', 'required');
			$this->form_validation->set_rules('uniform_sales_date', 'Tanggal', 'required');
			$this->form_validation->set_rules('uniform_sales_size', 'Ukuran', 'required');
			$this->form_validation->set_rules('uniform_sales_price', 'Harga', 'required');
			$this->form_validation->set_rules('uniform_sales_payment_type', 'Jenis Pembayaran', 'required');

			$uniform_sales_token 			= $this->AcctUniformSales_model->getUniformSalesToken($data['uniform_sales_token']);

			$transaction_module_code 		= "PS";
			$transaction_module_id 			= $this->AcctUniformSales_model->getTransactionModuleID($transaction_module_code);
			$journal_voucher_period 		= date("Ym", strtotime($data['uniform_sales_date']));
			
			if($this->form_validation->run()==true){
				if($uniform_sales_token->num_rows()==0){
					if($this->AcctUniformSales_model->insertAcctUniformSales($data)){
						$uniformsaleslast = $this->AcctUniformSales_model->getAcctUniformSales_Last($auth['user_id']);

						if($uniformsaleslast['uniform_sales_payment_type'] == 1){
							// TODO : JOURNAL VOUCHER
						}else if($uniformsaleslast['uniform_sales_payment_type'] == 2){
							// TODO : JOURNAL VOUCHER
	
							$memberaccountdebt = $this->AcctUniformSales_model->getCoreMemberAccountReceivableAmount($data['member_id']);
	
							$member_account_receivable_amount = $memberaccountdebt['member_account_receivable_amount'] + $data['uniform_sales_price'];
	
							$member_account_uniform_debt 	= $memberaccountdebt['member_account_uniform_debt'] + $data['uniform_sales_price'];
	
							$data_member = array(
								"member_id" 						=> $data['member_id'],
								"member_account_receivable_amount" 	=> $member_account_receivable_amount,
								"member_account_uniform_debt" 		=> $member_account_uniform_debt,
							);
	
							$this->AcctUniformSales_model->updateCoreMember($data_member);
						}

						$auth = $this->session->userdata('auth');
						$msg = "<div class='alert alert-success alert-dismissable'>  
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Penjualan Seragam Simpanan Sukses
								</div> ";
						$sesi = $this->session->userdata('unique');
						$this->session->unset_userdata('addacctuniformsales-'.$sesi['unique']);
						$this->session->unset_userdata('acctuniformsalestoken-'.$sesi['unique']);
						$this->session->set_userdata('message',$msg);
						redirect('uniform-sales/print-note/'.$uniformsaleslast['uniform_sales_id']);
					}else{
						$this->session->set_userdata('addacctuniformsales',$data);
						$msg = "<div class='alert alert-danger alert-dismissable'>
								<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
									Tambah Penjualan Seragam Simpanan Tidak Berhasil
								</div> ";
						$this->session->set_userdata('message',$msg);
						redirect('uniform-sales');
					}
				} else {
					$uniformsaleslast = $this->AcctUniformSales_model->getAcctUniformSales_Last($auth['user_id']);
					
					if($uniformsaleslast['uniform_sales_payment_type'] == 1){
						// TODO : JOURNAL VOUCHER

					}else if($uniformsaleslast['uniform_sales_payment_type'] == 2){
						// TODO : JOURNAL VOUCHER

						$memberaccountdebt = $this->AcctUniformSales_model->getCoreMemberAccountReceivableAmount($data['member_id']);

						$member_account_receivable_amount = $memberaccountdebt['member_account_receivable_amount'] + $data['uniform_sales_price'];

						$member_account_uniform_debt 	= $memberaccountdebt['member_account_uniform_debt'] + $data['uniform_sales_price'];

						$data_member = array(
							"member_id" 						=> $data['member_id'],
							"member_account_receivable_amount" 	=> $member_account_receivable_amount,
							"member_account_uniform_debt" 		=> $member_account_uniform_debt,
						);

						$this->AcctUniformSales_model->updateCoreMember($data_member);
					}

					$auth = $this->session->userdata('auth');
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Tambah Penjualan Seragam Simpanan Sukses
							</div> ";
					$sesi = $this->session->userdata('unique');
					$this->session->unset_userdata('addacctuniformsales-'.$sesi['unique']);
					$this->session->unset_userdata('acctuniformsalestoken-'.$sesi['unique']);
					$this->session->set_userdata('message',$msg);
					redirect('uniform-sales/print-note/'.$uniformsaleslast['uniform_sales_id']);
				}
				
			}else{
				$this->session->set_userdata('addacctuniformsales',$data);
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('uniform-sales');
			}
		}

		public function printNoteAcctUniformSales(){
			$auth 						= $this->session->userdata('auth');
			$uniform_sales_id 			= $this->uri->segment(3);
			$acctuniformsales			= $this->AcctUniformSales_model->getAcctUniformSales_Detail($uniform_sales_id);
			$preferencecompany 			= $this->AcctUniformSales_model->getPreferenceCompany();

			require_once('tcpdf/config/tcpdf_config.php');
			require_once('tcpdf/tcpdf.php');
			$pdf = new tcpdf('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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

			$pdf->SetFont('helvetica', '', 12);

			// -----------------------------------------------------------------------------
			$base_url = base_url();
			$img = "<img src=\"".$base_url."assets/layouts/layout/img/".$preferencecompany['logo_koperasi']."\" alt=\"\" width=\"800%\" height=\"800%\"/>";

			$tbl = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
			    <tr>
					<td rowspan=\"2\" width=\"20%\">".$img."</td>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">BUKTI PENJUALAN SERAGAM</div></td>
			    </tr>
			    <tr>
			        <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
			    </tr>
			</table>";

			$pdf->writeHTML($tbl, true, false, false, false, '');
			

			$tbl1 = "
			<br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
				<tr>
					<td width=\"20%\"><div style=\"text-align: left;\">No. Anggota</div></td>
					<td width=\"80%\"><div style=\"text-align: left;\">: ".$acctuniformsales['member_no']."</div></td>
				</tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctuniformsales['member_name']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Tanggal</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctuniformsales['uniform_sales_date']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Ukuran</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctuniformsales['uniform_sales_size']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: ".numtotxt($acctuniformsales['uniform_sales_price'])."</div></td>
			    </tr>
			     <tr>
			        <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
			        <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctuniformsales['uniform_sales_price'], 2)."</div></td>
			    </tr>			
			</table>";

			$tbl2 = "
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			    	<td width=\"30%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">Surakarta</div></td>
			    </tr>
			    <tr>
			        <td width=\"30%\"><div style=\"text-align: center;\">".$paraf."</div></td>
			        <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
			        <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
			    </tr>				
			</table>";

			$pdf->writeHTML($tbl1.$tbl2, true, false, false, false, '');


			ob_clean();

			$js ='';
			// -----------------------------------------------------------------------------
			
			$filename = 'Kwitansi_Penjualan_Seragam_'.$acctuniformsales['member_name'].'.pdf';
			$js .= 'print(true);';
			$pdf->IncludeJS($js);
			$pdf->Output($filename, 'I');
		}
		
		public function voidAcctUniformSales(){
			$data['main_view']['acctuniformsales']	= $this->AcctUniformSales_model->getAcctUniformSales_Detail($this->uri->segment(3));
			$data['main_view']['memberidentity']			= $this->configuration->MemberIdentity();
			$data['main_view']['content']					= 'AcctUniformSales/FormVoidAcctUniformSales_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processVoidAcctUniformSales(){
			$auth	= $this->session->userdata('auth');

			$newdata = array (
				"uniform_sales_id"	=> $this->input->post('uniform_sales_id',true),
				"voided_on"					=> date('Y-m-d H:i:s'),
				'data_state'				=> 2,
				"voided_remark" 			=> $this->input->post('voided_remark',true),
				"voided_id"					=> $auth['user_id']
			);
			
			$this->form_validation->set_rules('voided_remark', 'Keterangan', 'required');

			if($this->form_validation->run()==true){
				if($this->AcctUniformSales_model->voidAcctUniformSales($newdata)){
					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Pembatalan Mutasi Simpanan Sukses
							</div>";
					$this->session->set_userdata('message',$msg);
					redirect('uniform-sales');
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'> 
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
								Pembatalan Mutasi Simpanan Tidak Berhasil
							</div> ";
					$this->session->set_userdata('message',$msg);
					redirect('uniform-sales');
				}
					
			}else{
				$msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				$this->session->set_userdata('message',$msg);
				redirect('uniform-sales');
			}
		}
  
		public function function_state_add(){
			$unique 	= $this->session->userdata('unique');
			$value 		= $this->input->post('value',true);
			$sessions	= $this->session->userdata('addacctuniformsales-'.$unique['unique']);
			$sessions['active_tab'] = $value;
			$this->session->set_userdata('addacctuniformsales-'.$unique['unique'],$sessions);
		}
	}
?>
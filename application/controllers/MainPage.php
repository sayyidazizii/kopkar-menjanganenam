<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class MainPage extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('Dashboard_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
		}
		
		public function index(){
			$auth 		= $this->session->userdata('auth');
			if(!empty($auth)){
				$date 		= date('d-m-Y');
				$max_date 	= date('t');
				$month 		= date('m');
				$year 		= date('Y');
				// print_r($date); exit;

				for ($i=1; $i <= $max_date ; $i++) { 
					if($i < 10){
						$i= '0'.$i;
					}
					$date = $year.'-'.$month.'-'.$i;
					$total_pencairan 	= $this->Dashboard_model->getAcctCreditsAccount($date);
					$total_credits	 	= $this->Dashboard_model->getAcctCreditsPayment_outstanding($date);
					$total_payment		= $this->Dashboard_model->getCreditsPayment($date);
					$total_akun_os		= $this->Dashboard_model->getHitungAkunOutstanding($date);
					$total_akun 		= $this->Dashboard_model->getHitungAccount($date);


					$total_credits_payment_amount = $this->Dashboard_model->getCreditsPaymentAmount($date);
					$total_credits_account_amount = $this->Dashboard_model->getCreditsAccountAmount($date);
					//print_r($total_payment); exit;
					$total_outstanding = $total_credits_account_amount - $total_credits_payment_amount ;

					if(empty($total_pencairan)){
						$total_pencairan 		= '0';
						
					}
					if(empty($total_outstanding)){
						$total_outstanding = '0';
					}
					if(empty($total_akun)){
						$total_akun = '0';
					}
					if(empty($total_akun_os)){
						$total_akun_os = '0';
					}

					$data_pencairan[$i]['year'] 			= $i;
					$data_pencairan[$i]['income']			= $total_pencairan;
					$data_pencairan[$i]['expenses']	 		= $total_outstanding;
					$data_pencairan[$i]['jumlah_akun']		= $total_akun;
					$data_pencairan[$i]['jumlah_akun_os']	= $total_akun_os;
				}
				// print_r($total_pencairan); exit;
				$json_pencairan_month_to_date 	= json_encode($data_pencairan);

				

				
			
				function getWeek($week, $year) {
				$dto = new DateTime();
				$result['start']	 = $dto->setISODate($year, $week, 0)->format('Y-m-d');
				$result['end']	 = $dto->setISODate($year, $week, 6)->format('Y-m-d');

				return $result;
				}

				$bulanini = date('m');
				$akhirbulan = date('t');
				
				
				$now 	= date('Y-m-d');
				$signupdate=date('Y-m-01');
				$signupweek=date("W",strtotime($signupdate));
				$year=date("Y",strtotime($signupdate));
				$currentweek = date("W");

				$currentweek = $signupweek + 4;
					
				$no=0;
				$index=0;
				for($i=$signupweek;$i<=$currentweek;$i++) 
				{
					$no++;
					$result=getWeek($i,$year);
				
					$date = array (
						'start'		=> $result['start'],
						'end'		=> $result['end'],
					);

					$creditsaccount = $this->Dashboard_model->getCreditsAccount();

					$total1 = 0;
					$total2	= 0;
					$total3	= 0;
					$total4	= 0;
					$total5	= 0;
					$collectibility 	= $this->Dashboard_model->getPreferenceCollectibility();

					foreach ($creditsaccount as $key => $val) {
						$date1 = date_create($date['end']);
						$date2 = date_create($val['credits_account_payment_date']);

						$interval    = $date1->diff($date2);
						$tunggakan2  = $interval->days;
					}
						$tunggakan = $tunggakan2;

					foreach ($creditsaccount as $key => $val) {
						
						$date1 = date_create($date['end']);
						$date2 = date_create($val['credits_account_payment_date']);

						$interval    = $date1->diff($date2);
						$tunggakan   = $interval->days;
		
						$interval    = $date1->diff($date2);
						$tunggakan   = $interval->days;
						if($date2 >= $date1){
							$tunggakan2 = 0;
						}else{
							$tunggakan2 = $tunggakan;
						}
						foreach ($collectibility as $k => $v) {
							if($tunggakan2 >= $v['collectibility_bottom'] && $tunggakan2 <= $v['collectibility_top']){
								$collectibility_id = $v['collectibility_id'];
							} 
						}
						
				
						if($collectibility_id == 1){
							$total1 = $total1 + $val['credits_account_last_balance'];
						} else if($collectibility_id == 2){
							$total2 = $total2 + $val['credits_account_last_balance'];
						} else if($collectibility_id == 3){
							$total3 = $total3 + $val['credits_account_last_balance'];
						} else if($collectibility_id == 4){
							$total4 = $total4 + $val['credits_account_last_balance'];
						} else if($collectibility_id == 5){
							$total5 = $total5 + $val['credits_account_last_balance'];
						}
					
					}
	
					$data_kolektibilitas[$index]['minggu']		= 'Minggu ke '.$no;
					
					foreach ($collectibility as $k => $v) {
						if($v['collectibility_id'] == 1){
							$data_kolektibilitas[$index]['total1']		= $total1;
						}else if($v['collectibility_id'] == 2){
							$data_kolektibilitas[$index]['total2']		= $total2;
						}else if($v['collectibility_id'] == 3){
							$data_kolektibilitas[$index]['total3']		= $total3;
						}else if($v['collectibility_id'] == 4){
							$data_kolektibilitas[$index]['total4']		= $total4;
						}else if($v['collectibility_id'] == 5){
							$data_kolektibilitas[$index]['total5']		= $total5;
						}
					}
					$index++;
				}
				$kolektibilitas 	= json_encode($data_kolektibilitas);
				$menus = $this->Dashboard_model->getUserMenus($auth['user_id']);
				
				$data['main_view']['dayname']							= $this->configuration->DayName();
				$data['main_view']['json_pencairan_month_to_date']		= $data_pencairan;
				$data['main_view']['kolektibilitas']					= $kolektibilitas;
				$data['main_view']['menus']								= $menus;
				$data['main_view']['monthname']							= $this->configuration->Month();
				$deposito_profit_sharing_due_date		= date('d-m-Y');


			}
			
			

			$data['main_view']['acctdepositoprofitsharing'] 	= $this->MainPage_model->getAcctDepositoProfitSharing();
			$data['main_view']['content']	= 'Home';
			$this->load->view('MainPage_view',$data);
		}
	}
?>
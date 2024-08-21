<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class Whatsapp extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('Whatsapp_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
			require 'vendor/autoload.php';
		}
		
		public function index(){
			$client     = new GuzzleHttp\Client();
			$url        = 'https://app.ruangwa.id/api/qrcode';
			try {
				$response = $client->request( 'POST', $url, [ 
					'headers' => [
						'Accept'        => 'application/json',
						'Content-Type'  => 'application/x-www-form-urlencoded',
					],
					'form_params' => [ 
						'token'	  => "bPnrMuLisbVPrCXiD7nmyMv9kkK4HYw2g6cuQyS6XzfYy262dV",
					] 
				]);

				$status_code 	= $response->getStatusCode();
				$response 		= $response->getBody()->getContents();
				$img        	= $response;
			} catch (GuzzleHttp\Exception\BadResponseException $e) {
				$response 				= $e->getResponse();
				$responseBodyAsString 	= $response->getBody()->getContents();
				$img					= null;
			}

			$data['main_view']['content']	= 'Whatsapp/Scan_view';
			$data['main_view']['img']		= $img;
			$this->load->view('MainPage_view', $data);
		}
		
		public function reload(){
			$client     = new GuzzleHttp\Client();
			$url        = 'https://app.ruangwa.id/api/reload';
			try {
				$response = $client->request( 'POST', $url, [ 
					'headers' => [
						'Accept'        => 'application/json',
						'Content-Type'  => 'application/x-www-form-urlencoded',
					],
					'form_params' => [ 
						'token'	  => "bPnrMuLisbVPrCXiD7nmyMv9kkK4HYw2g6cuQyS6XzfYy262dV",
					] 
				]);
				
				$status_code = $response->getStatusCode();
				$response_data = $response->getBody()->getContents();
				
				$msg = "<div class='alert alert-success alert-dismissable'>
				<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
					Reload Service Berhasil
				</div> ";
			} catch (GuzzleHttp\Exception\BadResponseException $e) {
				$response = $e->getResponse();
				$responseBodyAsString = $response->getBody()->getContents();
				print_r($responseBodyAsString);

				$msg = "<div class='alert alert-danger alert-dismissable'>
				<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
					Reload Service Tidak Berhasil
				</div> ";
			}

			$this->session->set_userdata('message',$msg);
			redirect('/wa-scan');
		}

		public function broadcast(){
			$data['main_view']['broadcast']	= $this->Whatsapp_model->getBroadcast();
			$data['main_view']['content']	= 'Whatsapp/ListBroadcast_view';
			$this->load->view('MainPage_view',$data);
		}

		public function addBroadcast(){
			$data['main_view']['content']	= 'Whatsapp/FormAddBroadcast_view';
			$this->load->view('MainPage_view',$data);
		}

		public function processAddBroadcast(){
			$auth = $this->session->userdata('auth');

			$data = array(
				'broadcast_title'		=> $this->input->post('broadcast_title', true),
				'broadcast_message'		=> $this->input->post('broadcast_message', true),
				'broadcast_link'		=> $this->input->post('broadcast_link', true),
				'created_id'			=> $auth['user_id'],
				'created_on'			=> date('Y-m-d H:i:s'),
			);
			
			$this->form_validation->set_rules('broadcast_title', 'Judul', 'required');
			$this->form_validation->set_rules('broadcast_message', 'Pesan', 'required');

			if($this->form_validation->run()==true){
				if($this->Whatsapp_model->insertBroadcast($data)){
					$coremember = $this->Whatsapp_model->getCoreMember();

					foreach($coremember as $key => $val){
						if(!$data['broadcast_link']){
							$msg 	= "[KopKar Menjangan Enam]"."\r\n\r\n".$data['broadcast_title']."\r\n\r\n".$data['broadcast_message'];
						}else{
							$msg 	= "[KopKar Menjangan Enam]"."\r\n\r\n".$data['broadcast_title']."\r\n\r\n".$data['broadcast_message']."\r\n\r\n"."Link : ".$data['broadcast_link'];
						}

						$client     = new GuzzleHttp\Client();
						$url        = 'https://app.ruangwa.id/api/send_message';
						try {
							$response = $client->request( 'POST', $url, [ 
								'headers' => [
									'Accept'        => 'application/json',
									'Content-Type'  => 'application/x-www-form-urlencoded',
								],
								'form_params' => [ 
									'token'     	=> "bPnrMuLisbVPrCXiD7nmyMv9kkK4HYw2g6cuQyS6XzfYy262dV",
									'number'    	=> $val['member_phone'],
									'message'   	=> $msg,
								] 
							]);
							
							$status_code 	= $response->getStatusCode();
							$response_data 	= $response->getBody()->getContents();
						} catch (GuzzleHttp\Exception\BadResponseException $e) {
							$response = $e->getResponse();
							$responseBodyAsString = $response->getBody()->getContents();
							print_r($responseBodyAsString);
						}
						sleep(1);
					}

					$msg = "<div class='alert alert-success alert-dismissable'>  
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Pengumuman Anggota Berhasil
							</div> ";
					// $this->session->set_userdata('message',$msg);
					// redirect('wa-broadcast');
					echo $msg;
				}else{
					$msg = "<div class='alert alert-danger alert-dismissable'>
							<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
								Pengumuman Anggota Tidak Berhasil
							</div> ";
					// $this->session->set_userdata('message',$msg);
					// redirect('wa-broadcast');
					echo $msg;
				}
			}else{
				// $msg = validation_errors("<div class='alert alert-danger alert-dismissable'>", '</div>');
				// $this->session->set_userdata('message',$msg);
				// redirect('wa-broadcast/add');
				$msg = "<div class='alert alert-danger alert-dismissable'>
				<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>					
					Pengumuman Anggota Tidak Berhasil
				</div> ";
				echo $msg;
			}
		}
	}
?>
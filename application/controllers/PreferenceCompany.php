<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class PreferenceCompany extends CI_Controller{
		public function __construct(){
			parent::__construct();
			$this->load->model('Connection_model');
			$this->load->model('MainPage_model');
			$this->load->model('PreferenceCompany_model');
			$this->load->helper('sistem');
			$this->load->helper('url');
			$this->load->database('default');
			$this->load->library('configuration');
			$this->load->library('fungsi');
			$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		}
		
		public function index(){
			$data	= $this->PreferenceCompany_model->getDataPreferenceCompany();
			echo json_encode($data);
		}
		
	}
?>
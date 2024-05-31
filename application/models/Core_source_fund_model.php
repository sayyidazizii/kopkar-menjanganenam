<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class Core_source_fund_model extends CI_Model {
		var $table = "acct_source_fund";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();

			// $auth = $this->session->userdata('auth');
			// $this->CI->load->model('Connection_model');

			// $database = $this->Connection_model->define_database($auth['user_id'], $auth['database']);
			// $this->database = $this->load->database($database, true);
		} 
		
		public function getData(){
			$this->db->select('*');
			$this->db->from('acct_source_fund');
			$this->db->where('data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		//** Get source name */
		public function getAcctSourceFundName($source_fund_id){
			$this->db->select('acct_source_fund.source_fund_name');
			$this->db->from('acct_source_fund');
			$this->db->where('acct_source_fund.source_fund_id', $source_fund_id);
			$this->db->where('acct_source_fund.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['source_fund_name'];
		}
		
	}
?>
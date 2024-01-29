<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctSavingsAccountOfficerReport_model extends CI_Model {
		var $table = "core_member";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();

			// $auth = $this->session->userdata('auth');
			// $this->CI->load->model('Connection_model');

			// $database = $this->Connection_model->define_database($auth['user_id'], $auth['database']);
			// $this->database = $this->load->database($database, true);
		} 
		
		public function getAcctSavingsAccount($office_id, $start_date, $end_date, $savings_id, $branch_id){
			$this->db->select('acct_savings_account.savings_account_id, acct_savings_account.savings_account_no, acct_savings_account.member_id, core_member.member_name, core_member.member_address, acct_savings_account.savings_account_last_balance, acct_savings_account.office_id, acct_savings_account.branch_id');
			$this->db->from('acct_savings_account');
			$this->db->join('core_member', 'acct_savings_account.member_id = core_member.member_id');
			$this->db->join('acct_savings', 'acct_savings_account.savings_id = acct_savings.savings_id');
			if(!empty($office_id)){
				$this->db->where('acct_savings_account.office_id', $office_id);
			}
			$this->db->where('acct_savings_account.savings_account_date >=', $start_date);
			$this->db->where('acct_savings_account.savings_account_date <=', $end_date);			
			$this->db->where('acct_savings_account.data_state ', 0);
			$this->db->where('acct_savings.savings_status ', 0);
			$this->db->where('acct_savings_account.savings_id', $savings_id);
			if(!empty($branch_id)){
				$this->db->where('acct_savings_account.branch_id', $branch_id);
			}
			$this->db->order_by('acct_savings_account.savings_account_no', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreBranch(){
			$this->db->select('core_branch.branch_id, core_branch.branch_name');
			$this->db->from('core_branch');
			$this->db->where('core_branch.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreOffice(){
			$this->db->select('office_id, office_name');
			$this->db->from('core_office');
			$this->db->where('data_state', 0);
			return $this->db->get()->result_array();
		}

		public function getAcctSavings(){
			$this->db->select('acct_savings.savings_id, acct_savings.savings_name');
			$this->db->from('acct_savings');
			$this->db->where('acct_savings.data_state', 0);
			$this->db->where('acct_savings.savings_status', 0);
			return $this->db->get()->result_array();
		}

		public function getOfficeName($office_id){
			$this->db->select('office_name');
			$this->db->from('core_office');
			$this->db->where('office_id', $office_id);
			$result = $this->db->get()->row_array();
			return $result['office_name'];
		}

		public function getOfficeCode($office_id){
			$this->db->select('office_code');
			$this->db->from('core_office');
			$this->db->where('office_id', $office_id);
			$result = $this->db->get()->row_array();
			return $result['office_code'];
		}

		public function getUsername($user_id){
			$this->db->select('username');
			$this->db->from('system_user');
			$this->db->where('user_id', $user_id);
			$result = $this->db->get()->row_array();
			return $result['username'];
		}

		public function getSavingsProfitSharing($savings_account_id, $start_date, $end_date, $branch_id){
			$this->db->select('SUM(savings_profit_sharing_amount) as savings_profit_sharing_amount');
			$this->db->from('acct_savings_profit_sharing');
			$this->db->where('savings_account_id', $savings_account_id);
			$this->db->where('branch_id', $branch_id);
			$this->db->where('savings_profit_sharing_date >=', $start_date);
			$this->db->where('savings_profit_sharing_date <=', $end_date);
			$result = $this->db->get()->row_array();
			return $result['savings_profit_sharing_amount'];
		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			$this->db->limit(1);
			return $this->db->get()->row_array();
		}
	}
?>
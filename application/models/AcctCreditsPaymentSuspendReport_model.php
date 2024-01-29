<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctCreditsPaymentSuspendReport_model extends CI_Model {
		var $table = "acct_savings_account";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();

			// $auth = $this->session->userdata('auth');
			// $this->CI->load->model('Connection_model');

			// $database = $this->Connection_model->define_database($auth['user_id'], $auth['database']);
			// $this->database = $this->load->database($database, true);
		} 

		public function getCoreBranch(){
			$this->db->select('core_branch.branch_id, core_branch.branch_name');
			$this->db->from('core_branch');
			$this->db->where('core_branch.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreOffice(){
			$this->db->select('core_office.office_id, core_office.office_name');
			$this->db->from('core_office');
			$this->db->where('core_office.data_state', 0);
			return $this->db->get()->result_array();

		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			return $this->db->get()->row_array();
		}

		public function getOfficeName($office_id){
			$this->db->select('office_name');
			$this->db->from('core_office');
			$this->db->where('office_id', $office_id);
			$result = $this->db->get()->row_array();
			return $result['office_name'];
		}
		
		public function AcctCreditsPaymentSuspend($start_date, $end_date, $credits_id, $branch_id){
			$this->db->select('acct_credits_payment_suspend.branch_id, acct_credits_payment_suspend.credits_account_id, acct_credits_payment_suspend.member_id, core_member.member_name,  acct_credits_payment_suspend.credits_payment_suspend_date, acct_credits_payment_suspend.credits_payment_period, acct_credits_payment_suspend.credits_id, acct_credits_payment_suspend.credits_grace_period, acct_credits_payment_suspend.credits_payment_date_old, acct_credits_payment_suspend.credits_payment_date_new, acct_credits.credits_name, acct_credits_account.credits_account_serial, acct_credits_account.credits_payment_period');
			$this->db->from('acct_credits_payment_suspend');
			$this->db->join('core_member', 'acct_credits_payment_suspend.member_id = core_member.member_id');
			$this->db->join('acct_credits','acct_credits_payment_suspend.credits_id = acct_credits.credits_id');
			$this->db->join('acct_credits_account','acct_credits_payment_suspend.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->where('acct_credits_payment_suspend.data_state ', 0);
			$this->db->where('acct_credits_payment_suspend.credits_payment_date_new >=', $start_date);
			$this->db->where('acct_credits_payment_suspend.credits_payment_date_new <=', $end_date);
			if(!empty($credits_id)){
				$this->db->where('acct_credits_payment_suspend.credits_id', $credits_id);
			}
			if(!empty($branch_id)){
				$this->db->where('acct_credits_payment_suspend.branch_id', $branch_id);
			}
			$this->db->order_by('acct_credits_account.credits_account_serial','ASC');
			$result = $this->db->get()->result_array();

			//print_r($this->db->last_query());exit;
			return $result;
		}

		public function getAcctCreditsPayment($credits_account_id){
			$this->db->select('acct_credits_payment.credits_payment_date, acct_credits_payment.credits_principal_last_balance');
			$this->db->from('acct_credits_payment');
			$this->db->where('acct_credits_payment.data_state ', 0);
			$this->db->where('acct_credits_payment.credits_account_id', $credits_account_id);
			$this->db->order_by('acct_credits_payment.credits_payment_date', 'DESC');	
			$this->db->limit(1);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getAcctCredits(){
			$this->db->select('acct_credits.credits_id, acct_credits.credits_name');
			$this->db->from('acct_credits');
			$this->db->where('acct_credits.data_state', 0);
			return $this->db->get()->result_array();
		}

		public function getAcctSourceFund(){
			$this->db->select('acct_source_fund.source_fund_id, acct_source_fund.source_fund_name');
			$this->db->from('acct_source_fund');
			$this->db->where('acct_source_fund.data_state', 0);
			return $this->db->get()->result_array();
		}

		public function getUsername($user_id){
			$this->db->select('username');
			$this->db->from('system_user');
			$this->db->where('user_id', $user_id);
			$result = $this->db->get()->row_array();
			return $result['username'];
		}
	}
?>
<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctCreditsPaymentDailyReport_model extends CI_Model {
		var $table = "acct_savings_account";

		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
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

		public function getOfficeName($office_id){
			$this->db->select('office_name');
			$this->db->from('core_office');
			$this->db->where('office_id', $office_id);
			$result = $this->db->get()->row_array();
			return $result['office_name'];
		}

		public function getCreditsName($credits_id){
			$this->db->select('credits_name');
			$this->db->from('acct_credits');
			$this->db->where('credits_id', $credits_id);
			$result = $this->db->get()->row_array();
			return $result['credits_name'];
		}

		public function getCreditsAccount($start_date, $end_date, $office_id, $branch_id){
			$this->db->select('acct_credits_account.credits_account_id, acct_credits_account.credits_account_serial, acct_credits_account.member_id, core_member.member_name, core_member.member_address, acct_credits_account.credits_account_amount, acct_credits_account.credits_account_principal_amount, acct_credits_account.credits_account_interest_amount, acct_credits_account.credits_account_last_balance, acct_credits_account.credits_account_last_payment_date,acct_credits_account.credits_account_payment_amount, acct_credits_account.credits_account_accumulated_fines, acct_credits_account.office_id, acct_credits_account.credits_account_period, acct_credits_account.credits_account_payment_to');
			$this->db->from('acct_credits_account');
			$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
			$this->db->where('acct_credits_account.data_state ', 0);
			$this->db->where('acct_credits_account.credits_approve_status', 1);
			$this->db->where('acct_credits_account.credits_account_status ', 0);
			$this->db->where('acct_credits_account.credits_account_last_balance > ', 0);
			$this->db->where('acct_credits_account.credits_account_date >=', $start_date);
			$this->db->where('acct_credits_account.credits_account_date <=', $end_date);
			if(!empty($office_id)){
				$this->db->where('acct_credits_account.office_id', $office_id);
			}
			if(!empty($branch_id)){
				$this->db->where('acct_credits_account.branch_id', $branch_id);
			}
			$this->db->order_by('acct_credits_account.credits_account_serial', 'ASC');	
			$result = $this->db->get()->result_array();

			return $result;
		}

		public function getMemberPayment($start_date, $end_date, $credits_id, $branch_id){
			$this->db->select('core_member.member_name, core_member.member_no, core_part.part_name, acct_credits.credits_name, SUM(acct_credits_payment.credits_principal_last_balance) as total_last_principal, SUM(acct_credits_payment.credits_payment_principal) as total_principal, SUM(acct_credits_payment.credits_payment_interest) as total_interest');
			$this->db->from('acct_credits_payment');
			$this->db->join('core_member', 'acct_credits_payment.member_id = core_member.member_id');
			$this->db->join('core_member_working', 'core_member.member_id = core_member_working.member_id');
			$this->db->join('core_part', 'core_member_working.part_id = core_part.part_id', 'left');
			$this->db->join('acct_credits', 'acct_credits_payment.credits_id = acct_credits.credits_id');
			$this->db->where('acct_credits_payment.data_state ', 0);
			$this->db->where('acct_credits_payment.credits_payment_date >=', $start_date);
			$this->db->where('acct_credits_payment.credits_payment_date <=', $end_date);
			if(!empty($credits_id)){
				$this->db->where('acct_credits_payment.credits_id', $credits_id);
			}
			if(!empty($branch_id)){
				$this->db->where('acct_credits_payment.branch_id', $branch_id);
			}
			$this->db->group_by('core_member.member_id');
			$this->db->group_by('acct_credits_payment.credits_id');
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCreditsPayment($start_date, $end_date, $branch_id){
			$this->db->select('acct_credits_payment.*, core_member.member_name, acct_credits_account.credits_account_serial');
			$this->db->from('acct_credits_payment');
			$this->db->join('core_member', 'acct_credits_payment.member_id = core_member.member_id');
			$this->db->join('acct_credits_account', 'acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->where('acct_credits_payment.data_state ', 0);
			$this->db->where('acct_credits_payment.credits_payment_date >=', $start_date);
			$this->db->where('acct_credits_payment.credits_payment_date <=', $end_date);
			if(!empty($branch_id)){
				$this->db->where('acct_credits_payment.branch_id', $branch_id);
			}
			// $this->db->group_by('acct_credits_payment.member_id');
			$result = $this->db->get()->result_array();
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

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			return $this->db->get()->row_array();
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
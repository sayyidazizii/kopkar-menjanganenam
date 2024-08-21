<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class CreditsAccountMutationReport_model extends CI_Model {
		var $table = "acct_savings_cash_mutation";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		public function getCreditsAccountMutationReport($start_date, $end_date, $credits_id){
			$this->db->select('acct_credits_payment.*, acct_credits_account.credits_account_serial, core_member.member_no, core_member.member_name, acct_credits.credits_name, core_division.division_name');
			$this->db->from('acct_credits_payment');
			$this->db->join('acct_credits_account', 'acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
			$this->db->join('core_member_working', 'acct_credits_account.member_id = core_member_working.member_id');
			$this->db->join('core_division', 'core_member_working.division_id = core_division.division_id');
			$this->db->where('acct_credits_payment.credits_payment_date >=', $start_date);
			$this->db->where('acct_credits_payment.credits_payment_date <=', $end_date);
			$this->db->where('acct_credits_payment.credits_id', $credits_id);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			return $this->db->get()->row_array();
		}

		public function getTaxReport1($savings_cash_mutation_date){
			$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_id, acct_savings_cash_mutation.savings_account_id, acct_savings_cash_mutation.member_id, acct_savings_cash_mutation.savings_id, acct_savings_cash_mutation.branch_id, acct_savings_cash_mutation.mutation_id, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_remark, acct_savings_cash_mutation.savings_cash_mutation_status, acct_savings_cash_mutation.operated_name, acct_mutation.mutation_name');
			$this->db->from('acct_savings_cash_mutation');
			$this->db->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id = acct_mutation.mutation_id');
			$this->db->where('acct_savings_cash_mutation.data_state ', 0);
			$this->db->where('acct_savings_cash_mutation.savings_cash_mutation_status ', 1);
			$this->db->where('acct_savings_cash_mutation.mutation_id =', 1);
			$this->db->where('acct_savings_cash_mutation.savings_cash_mutation_date ', $savings_cash_mutation_date);
			return $this->db->get()->result_array();

		}

		public function getTaxReport2($savings_cash_mutation_date){
			$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_id, acct_savings_cash_mutation.savings_account_id, acct_savings_cash_mutation.member_id, acct_savings_cash_mutation.savings_id, acct_savings_cash_mutation.branch_id, acct_savings_cash_mutation.mutation_id, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_remark, acct_savings_cash_mutation.savings_cash_mutation_status, acct_savings_cash_mutation.operated_name, acct_mutation.mutation_name');
			$this->db->from('acct_savings_cash_mutation');
			$this->db->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id = acct_mutation.mutation_id');
			$this->db->where('acct_savings_cash_mutation.data_state ', 0);
			$this->db->where('acct_savings_cash_mutation.savings_cash_mutation_status ', 1);
			$this->db->where('acct_savings_cash_mutation.mutation_id =', 2);
			$this->db->where('acct_savings_cash_mutation.savings_cash_mutation_date ', $savings_cash_mutation_date);
			return $this->db->get()->result_array();

		}

		public function getTaxReport3($credits_payment_date){
			$this->db->select('acct_credits_payment.credits_payment_id, acct_credits_payment.credits_account_id, acct_credits_payment.credits_id, acct_credits_payment.member_id, acct_credits_payment.branch_id, acct_credits_payment.bank_account_id, acct_credits_payment.savings_account_id, acct_credits_payment.credits_payment_branch, acct_credits_payment.credits_payment_date, acct_credits_payment.credits_payment_principal, acct_credits_payment.credits_payment_status, acct_credits_payment.operated_name');
			$this->db->from('acct_credits_payment');
			$this->db->where('acct_credits_payment.data_state ', 0);
			$this->db->where('acct_credits_payment.credits_payment_status ', 1);
			$this->db->where('acct_credits_payment.credits_payment_date ', $credits_payment_date);	
			return $this->db->get()->result_array();

		}

		public function getAcctNomintiveSavingsReport_Pickup($mutation_id){
			$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_id, acct_savings_cash_mutation.savings_account_id, acct_savings_cash_mutation.member_id, acct_savings_cash_mutation.savings_id, acct_savings_cash_mutation.branch_id, acct_savings_cash_mutation.mutation_id, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_remark, acct_savings_cash_mutation.savings_cash_mutation_status, acct_savings_cash_mutation.operated_name, acct_savings_cash_mutation.operated_name, acct_mutation.mutation_name');
			$this->db->from('acct_savings_cash_mutation');
			$this->db->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id = acct_mutation.mutation_id');
			$this->db->where('acct_savings_cash_mutation.data_state ', 0);
			$this->db->where('acct_savings_cash_mutation.mutation_id ', $mutation_id );
			$this->db->order_by('acct_savings_cash_mutation.savings_cash_mutation_date', 'ASC');		
			return $this->db->get()->result_array();
		}

		public function getPickupMutation(){
			$this->db->select('acct_mutation.mutation_id, acct_mutation.mutation_name');
			$this->db->from('acct_mutation');
			$this->db->where('acct_mutation.data_state ', 0);
			return $this->db->get()->result_array();
		}

		public function getCoreBranch(){
			$this->db->select('core_branch.branch_id, core_branch.branch_name');
			$this->db->from('core_branch');
			$this->db->where('core_branch.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctCredits(){
			$this->db->select('acct_credits.credits_id, acct_credits.credits_name');
			$this->db->from('acct_credits');
			$this->db->where('acct_credits.data_state', 0);
			return $this->db->get()->result_array();
		}

		public function getUsername($user_id){
			$this->db->select('username');
			$this->db->from('system_user');
			$this->db->where('user_id', $user_id);
			$result = $this->db->get()->row_array();
			return $result['username'];
		}

		public function getSavingsName($savings_id){
			$this->db->select('savings_name');
			$this->db->from('acct_savings');
			$this->db->where('savings_id', $savings_id);
			$result = $this->db->get()->row_array();
			return $result['savings_name'];
		}

		public function getAcctNomintiveSavingsReport_Savings($savings_id){
			$this->db->select('acct_savings_cash_mutation.savings_account_id, acct_savings_cash_mutation.savings_account_no, acct_savings_cash_mutation.member_id, core_member.member_name, core_member.member_address, acct_savings_cash_mutation.savings_account_date, acct_savings_cash_mutation.savings_account_last_balance, acct_savings_cash_mutation.savings_id, acct_savings.savings_name, acct_savings.savings_status');
			$this->db->from('acct_savings_cash_mutation');
			$this->db->join('acct_savings', 'acct_savings_cash_mutation.savings_id = acct_savings.savings_id');
			$this->db->join('core_member', 'acct_savings_cash_mutation.member_id = core_member.member_id');
			$this->db->where('acct_savings_cash_mutation.data_state ', 0);
			$this->db->where('acct_savings_cash_mutation.savings_id ', $savings_id);
			$this->db->order_by('acct_savings_cash_mutation.savings_account_id', 'ASC');
			$this->db->order_by('acct_savings_cash_mutation.savings_account_no', 'ASC');
			$this->db->order_by('acct_savings_cash_mutation.member_id', 'ASC');
			$this->db->order_by('core_member.member_name', 'ASC');
			$this->db->order_by('core_member.member_address', 'ASC');
			$this->db->order_by('acct_savings_cash_mutation.savings_account_date', 'ASC');
			$this->db->order_by('acct_savings_cash_mutation.savings_account_last_balance', 'ASC');
			$this->db->order_by('acct_savings_cash_mutation.savings_id', 'ASC');
			$this->db->order_by('acct_savings.savings_name', 'ASC');
			$this->db->order_by('acct_savings.savings_status', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctSavingsProfitSharing($savings_account_id, $period, $branch_id){
			$this->db->select('acct_savings_profit_sharing.savings_account_id, acct_savings_cash_mutation.savings_account_no, acct_savings_profit_sharing.member_id, core_member.member_name, core_member.member_address, acct_savings_profit_sharing.savings_profit_sharing_amount, acct_savings_profit_sharing.savings_daily_average_balance, acct_savings_profit_sharing.savings_account_last_balance');
			$this->db->from('acct_savings_profit_sharing');
			$this->db->join('acct_savings_cash_mutation', 'acct_savings_profit_sharing.savings_account_id = acct_savings_cash_mutation.savings_account_id');
			$this->db->join('core_member', 'acct_savings_profit_sharing.member_id = core_member.member_id');
			$this->db->where('acct_savings_profit_sharing.savings_profit_sharing_period', $period);
			if(empty($savings_account_id)){
			$this->db->where('acct_savings_profit_sharing.savings_account_id', $savings_account_id);
			}
			if($branch_id != ''){
				$this->db->where('acct_savings_profit_sharing.branch_id', $branch_id);
			}
			return $this->db->get()->row_array();
		}

	}
?>
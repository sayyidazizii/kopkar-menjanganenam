<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class PersonaliaReport_model extends CI_Model {
		var $table = "acct_savings_cash_mutation";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		public function getPersonaliaReport( $account_id){
			$this->db->select('acct_journal_voucher_item.*, acct_journal_voucher.journal_voucher_date');
			$this->db->from('acct_journal_voucher_item');
			$this->db->join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id = acct_journal_voucher_item.journal_voucher_id');
			$this->db->where('acct_journal_voucher_item.data_state ', 0);
			$this->db->where('acct_journal_voucher_item.account_id', $account_id);	
			$this->db->where('acct_journal_voucher_item.journal_voucher_debit_amount >', 0);	
			
			
			return $this->db->get()->result_array();
		}
		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			return $this->db->get()->row_array();
		}

		public function getPersonaliaReport1($savings_cash_mutation_date){
			$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_id, acct_savings_cash_mutation.savings_account_id, acct_savings_cash_mutation.member_id, acct_savings_cash_mutation.savings_id, acct_savings_cash_mutation.branch_id, acct_savings_cash_mutation.mutation_id, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_remark, acct_savings_cash_mutation.savings_cash_mutation_status, acct_savings_cash_mutation.operated_name, acct_mutation.mutation_name');
			$this->db->from('acct_savings_cash_mutation');
			$this->db->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id = acct_mutation.mutation_id');
			$this->db->where('acct_savings_cash_mutation.data_state ', 0);
			$this->db->where('acct_savings_cash_mutation.savings_cash_mutation_status ', 1);
			$this->db->where('acct_savings_cash_mutation.mutation_id =', 1);
			$this->db->where('acct_savings_cash_mutation.savings_cash_mutation_date ', $savings_cash_mutation_date);
			// $this->db->where('acct_savings_cash_mutation.savings_cash_mutation_status ', $savings_cash_mutation_status);	
			// $result = $this->db->get()->result_array();

			// print_r($this->db->last_query());exit;	
			return $this->db->get()->result_array();

		}

		public function getPersonaliaReport2($savings_cash_mutation_date){
			$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_id, acct_savings_cash_mutation.savings_account_id, acct_savings_cash_mutation.member_id, acct_savings_cash_mutation.savings_id, acct_savings_cash_mutation.branch_id, acct_savings_cash_mutation.mutation_id, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_remark, acct_savings_cash_mutation.savings_cash_mutation_status, acct_savings_cash_mutation.operated_name, acct_mutation.mutation_name');
			$this->db->from('acct_savings_cash_mutation');
			$this->db->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id = acct_mutation.mutation_id');
			$this->db->where('acct_savings_cash_mutation.data_state ', 0);
			$this->db->where('acct_savings_cash_mutation.savings_cash_mutation_status ', 1);
			$this->db->where('acct_savings_cash_mutation.mutation_id =', 2);
			$this->db->where('acct_savings_cash_mutation.savings_cash_mutation_date ', $savings_cash_mutation_date);
			$this->db->where('acct_savings_cash_mutation.savings_cash_mutation_status ', $savings_cash_mutation_status);	
			// $result = $this->db->get()->result_array();

			// print_r($this->db->last_query());exit;	
			return $this->db->get()->result_array();

		}

		public function getPersonaliaReport3($credits_payment_date){
			$this->db->select('acct_credits_payment.credits_payment_id, acct_credits_payment.credits_account_id, acct_credits_payment.credits_id, acct_credits_payment.member_id, acct_credits_payment.branch_id, acct_credits_payment.bank_account_id, acct_credits_payment.savings_account_id, acct_credits_payment.credits_payment_branch, acct_credits_payment.credits_payment_date, acct_credits_payment.credits_payment_principal, acct_credits_payment.credits_payment_status, acct_credits_payment.operated_name');
			$this->db->from('acct_credits_payment');
			$this->db->where('acct_credits_payment.data_state ', 0);
			$this->db->where('acct_credits_payment.credits_payment_status ', 1);
			$this->db->where('acct_credits_payment.credits_payment_date ', $credits_payment_date);	
			// $result = $this->db->get()->result_array();

			// print_r($this->db->last_query());exit;	
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
		
		public function getAcctCreditsAccount(){
			$this->db->select('core_member.member_name, acct_credits_account.credits_account_amount, acct_credits_account.credits_account_last_balance,  acct_credits_account.credits_account_payment_amount, acct_savings_account.savings_account_no, acct_credits_payment.credits_payment_principal, acct_credits_payment.credits_payment_interest');
			$this->db->from('acct_credits_account');
			$this->db->join('acct_savings_account', 'acct_savings_account.savings_account_no = acct_savings_account.savings_account_no');
			$this->db->join('acct_credits_payment', 'acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
			$this->db->where('acct_credits_account.credits_approve_status', 1);
			$this->db->where('acct_credits_account.data_state', 0);
			$this->db->where('acct_credits_account.credits_account_last_balance >=', 1);
			// $this->db->where('MONTH(acct_credits_payment.credits_payment_date)', $month_name);
			$this->db->order_by('acct_credits_account.credits_account_no', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
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
		public function getCredit1($month_name){
			$this->db->select('acct_savings_account.savings_account_no ,core_member.member_name, acct_credits_payment.credits_interest_last_balance, acct_credits_payment.credits_payment_principal, acct_credits_payment.credits_payment_interest');
			$this->db->from('acct_credits_account');
			$this->db->join('acct_savings_account', 'acct_savings_account.savings_account_no = acct_savings_account.savings_account_no');
			$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
			$this->db->join('acct_credits_payment', 'acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->where('MONTH(acct_credits_account.credits_account_date)',  $month_name);
			$result = $this->db->get()->result_array();
			return $result;
			
		}
	public function getAcctSavings(){
			$this->db->select('acct_savings.savings_id, acct_savings.savings_name');
			$this->db->from('acct_savings');
			$this->db->where('acct_savings.data_state', 0);
			$this->db->where('acct_savings.savings_status', 0);
			return $this->db->get()->result_array();
		}
		public function getAcctSavingsAccount(){
			$this->db->select('acct_savings_account.savings_account_id, acct_savings_account.savings_account_no, acct_savings_account.member_id, core_member.member_name, core_member.member_address, acct_savings_account.savings_account_date, acct_savings_account.savings_account_last_balance, acct_savings_account.savings_id, acct_savings.savings_name, acct_savings.savings_status');
			$this->db->from('acct_savings_account');
			$this->db->join('acct_savings', 'acct_savings_account.savings_id = acct_savings.savings_id');
			$this->db->join('core_member', 'acct_savings_account.member_id = core_member.member_id');
			$this->db->where('acct_savings_account.data_state ', 0);
			$this->db->order_by('acct_savings_account.savings_account_id', 'ASC');
			$this->db->order_by('acct_savings_account.savings_account_no', 'ASC');
			$this->db->order_by('acct_savings_account.member_id', 'ASC');
			$this->db->order_by('core_member.member_name', 'ASC');
			$this->db->order_by('core_member.member_address', 'ASC');
			$this->db->order_by('acct_savings_account.savings_account_date', 'ASC');
			$this->db->order_by('acct_savings_account.savings_account_last_balance', 'ASC');
			$this->db->order_by('acct_savings_account.savings_id', 'ASC');
			$this->db->order_by('acct_savings.savings_name', 'ASC');
			$this->db->order_by('acct_savings.savings_status', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
			// $this->db->select('acct_savings_account.savings_account_id, acct_savings_account.savings_account_last_balance');
			// $this->db->from('acct_savings_account');
			// $this->db->where('acct_savings_account.data_state', 0);
			// return $this->db->get()->result_array();
		}
		public function getCoreMember(){
			$this->db->select('core_member.member_no, core_member.member_name, core_member.member_principal_savings, core_member.member_special_savings, core_member.member_mandatory_savings');
			$this->db->from('core_member');
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
			$this->db->select('acct_savings_account.savings_account_id, acct_savings_account.savings_account_no, acct_savings_account.member_id, core_member.member_name, core_member.member_address, acct_savings_account.savings_account_date, acct_savings_account.savings_account_last_balance, acct_savings_account.savings_id, acct_savings.savings_name, acct_savings.savings_status');
			$this->db->from('acct_savings_account');
			$this->db->join('acct_savings', 'acct_savings_account.savings_id = acct_savings.savings_id');
			$this->db->join('core_member', 'acct_savings_account.member_id = core_member.member_id');
			$this->db->where('acct_savings_account.data_state ', 0);
			$this->db->where('acct_savings_account.savings_id ', $savings_id);
			$this->db->order_by('acct_savings_account.savings_account_id', 'ASC');
			$this->db->order_by('acct_savings_account.savings_account_no', 'ASC');
			$this->db->order_by('acct_savings_account.member_id', 'ASC');
			$this->db->order_by('core_member.member_name', 'ASC');
			$this->db->order_by('core_member.member_address', 'ASC');
			$this->db->order_by('acct_savings_account.savings_account_date', 'ASC');
			$this->db->order_by('acct_savings_account.savings_account_last_balance', 'ASC');
			$this->db->order_by('acct_savings_account.savings_id', 'ASC');
			$this->db->order_by('acct_savings.savings_name', 'ASC');
			$this->db->order_by('acct_savings.savings_status', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
		}
		public function getAcctSavingsProfitSharing($savings_account_id, $period, $branch_id){
			$this->db->select('acct_savings_profit_sharing.savings_account_id, acct_savings_account.savings_account_no, acct_savings_profit_sharing.member_id, core_member.member_name, core_member.member_address, acct_savings_profit_sharing.savings_profit_sharing_amount, acct_savings_profit_sharing.savings_daily_average_balance, acct_savings_profit_sharing.savings_account_last_balance');
			$this->db->from('acct_savings_profit_sharing');
			$this->db->join('acct_savings_account', 'acct_savings_profit_sharing.savings_account_id = acct_savings_account.savings_account_id');
			$this->db->join('core_member', 'acct_savings_profit_sharing.member_id = core_member.member_id');
			$this->db->where('acct_savings_profit_sharing.savings_profit_sharing_period', $period);
			if(empty($savings_account_id)){
			$this->db->where('acct_savings_profit_sharing.savings_account_id', $savings_account_id);
			}
			if($branch_id != ''){
				$this->db->where('acct_savings_profit_sharing.branch_id', $branch_id);
			}
			$result = $this->db->get()->row_array();
			return $result;
			}
		}
?>
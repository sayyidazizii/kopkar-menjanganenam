<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class HelpBookGeneral_model extends CI_Model {
		var $table = "acct_savings_cash_mutation";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		public function getHelpBookGeneralPlus($sesi){
			$this->db->select('acct_journal_voucher.journal_voucher_id, acct_journal_voucher.journal_voucher_date, acct_journal_voucher.journal_voucher_title, acct_journal_voucher.journal_voucher_no, acct_journal_voucher_item.journal_voucher_amount');
			$this->db->from('acct_journal_voucher_item');
			$this->db->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id = acct_journal_voucher_item.journal_voucher_id');
			$this->db->where('acct_journal_voucher.journal_voucher_date >=', tgltodb($sesi['start_date']));
			$this->db->where('acct_journal_voucher.journal_voucher_date <=', tgltodb($sesi['end_date']));
			$this->db->where('acct_journal_voucher_item.account_id', $sesi['account_id']);
			$this->db->where('acct_journal_voucher_item.account_id_status !=', 'acct_journal_voucher_item.account_id_default_status');
			$this->db->where('acct_journal_voucher.data_state', 0);
			$this->db->order_by('acct_journal_voucher.journal_voucher_id', "ASC");
			return $this->db->get()->result_array();
		}
		
		public function getHelpBookGeneralPlusVal($sesi, $repayment_id){
			$this->db->select('acct_journal_voucher.journal_voucher_id, acct_journal_voucher.journal_voucher_date, acct_journal_voucher.journal_voucher_title, acct_journal_voucher.journal_voucher_no, acct_journal_voucher_item.journal_voucher_amount');
			$this->db->from('acct_journal_voucher_item');
			$this->db->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id = acct_journal_voucher_item.journal_voucher_id');
			$this->db->where('acct_journal_voucher.journal_voucher_date >=', tgltodb($sesi['start_date']));
			$this->db->where('acct_journal_voucher.journal_voucher_date <=', tgltodb($sesi['end_date']));
			$this->db->where('acct_journal_voucher_item.account_id', $sesi['account_id']);
			$this->db->where('acct_journal_voucher.repayment_id', $repayment_id);
			$this->db->where('acct_journal_voucher_item.account_id_status !=', 'acct_journal_voucher_item.account_id_default_status');
			$this->db->where('acct_journal_voucher.data_state', 0);
			$this->db->order_by('acct_journal_voucher.journal_voucher_id', "ASC");
			return $this->db->get()->result_array();
		}
		
		public function getHelpBookGeneralMinus($sesi){
			$this->db->select('acct_journal_voucher.journal_voucher_id, acct_journal_voucher.journal_voucher_date, acct_journal_voucher.journal_voucher_title, acct_journal_voucher.journal_voucher_no, acct_journal_voucher_item.journal_voucher_amount');
			$this->db->from('acct_journal_voucher_item');
			$this->db->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id = acct_journal_voucher_item.journal_voucher_id');
			$this->db->where('acct_journal_voucher.journal_voucher_date >=', tgltodb($sesi['start_date']));
			$this->db->where('acct_journal_voucher.journal_voucher_date <=', tgltodb($sesi['end_date']));
			$this->db->where('acct_journal_voucher_item.account_id', $sesi['account_id']);
			$this->db->where('acct_journal_voucher_item.account_id_status', 'acct_journal_voucher_item.account_id_default_status');
			$this->db->where('acct_journal_voucher.data_state', 0);
			$this->db->order_by('acct_journal_voucher.journal_voucher_id', "ASC");
			return $this->db->get()->result_array();
		}
		
		public function getHelpBookGeneralPelunasan($sesi, $journal_voucher_id){
			$this->db->select('acct_journal_voucher.journal_voucher_id, acct_journal_voucher.journal_voucher_date, acct_journal_voucher.journal_voucher_title, acct_journal_voucher.journal_voucher_no, acct_journal_voucher_item.journal_voucher_amount');
			$this->db->from('acct_journal_voucher_item');
			$this->db->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id = acct_journal_voucher_item.journal_voucher_id');
			$this->db->where('acct_journal_voucher.journal_voucher_date >=', tgltodb($sesi['start_date']));
			$this->db->where('acct_journal_voucher.journal_voucher_date <=', tgltodb($sesi['end_date']));
			$this->db->where('acct_journal_voucher.repayment_id', $journal_voucher_id);
			$this->db->where('acct_journal_voucher_item.account_id_status', 1);
			$this->db->where('acct_journal_voucher.data_state', 0);
			$this->db->group_start();
			$this->db->or_where('acct_journal_voucher_item.account_id', 172);
			$this->db->or_where('acct_journal_voucher_item.account_id', 523);
			$this->db->group_end();
			$this->db->order_by('acct_journal_voucher.journal_voucher_id', "ASC");
			return $this->db->get()->row_array();
		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			return $this->db->get()->row_array();
		}

		public function getCoreBranch(){
			$this->db->select('core_branch.branch_id, core_branch.branch_name');
			$this->db->from('core_branch');
			$this->db->where('core_branch.data_state', 0);
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

		public function getAcctAccount(){
			$hasil = $this->db->query("
							SELECT acct_account.account_id, 
							CONCAT(acct_account.account_code,' - ', acct_account.account_name) as account_code 
							from acct_account
							where acct_account.data_state = 0
							and RIGHT(acct_account.account_code, 2) != 00");
			return $hasil->result_array();
		}

		public function getAccountDefaultStatus($account_id){
			$this->db->select('account_default_status');
			$this->db->from('acct_account');
			$this->db->where('account_id', $account_id);
			$result = $this->db->get()->row_array();
			return $result['account_default_status'];
		}

		public function getAccountName($account_id){
			$this->db->select('account_name');
			$this->db->from('acct_account');
			$this->db->where('account_id', $account_id);
			$result = $this->db->get()->row_array();
			return $result['account_name'];
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
	}
?>
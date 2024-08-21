<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctLedgerReport_model extends CI_Model {
		var $table = "acct_savings_account";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();

			// $auth = $this->session->userdata('auth');
			// $this->CI->load->model('Connection_model');

			// $database = $this->Connection_model->define_database($auth['user_id'], $auth['database']);
			// $this->database = $this->load->database($database, true);
		} 
		
		public function getAcctAccountBalanceDetail($account_id, $start_date, $end_date, $branch_id){
			$this->db->select('acct_account_balance_detail.account_balance_detail_id, acct_account_balance_detail.transaction_type, acct_account_balance_detail.transaction_code, acct_account_balance_detail.transaction_date, acct_account_balance_detail.transaction_id, acct_account_balance_detail.account_id, acct_account.account_code, acct_account.account_name, acct_account_balance_detail.opening_balance, acct_account_balance_detail.account_in, acct_account_balance_detail.account_out, acct_account_balance_detail.last_balance');
			$this->db->from('acct_account_balance_detail');
			$this->db->join('acct_account', 'acct_account_balance_detail.account_id = acct_account.account_id');
			$this->db->where('acct_account_balance_detail.account_id', $account_id);
			$this->db->where('acct_account_balance_detail.branch_id', $branch_id);
			$this->db->where('acct_account_balance_detail.transaction_date >=', $start_date);
			$this->db->where('acct_account_balance_detail.transaction_date <=', $end_date);
			$this->db->order_by('acct_account_balance_detail.transaction_date', 'ASC');	
			$this->db->order_by('acct_account_balance_detail.account_balance_detail_id', 'ASC');	
			$result = $this->db->get()->result_array();
			// print_r($result);exit;
			return $result;
		}

		public function getCoreBranch(){
			$this->db->select('core_branch.branch_id, core_branch.branch_name');
			$this->db->from('core_branch');
			$this->db->where('core_branch.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getOpeningDate($account_id, $start_date, $end_date, $branch_id){
			$this->db->select_min('transaction_date');
			$this->db->from('acct_account_balance_detail');
			$this->db->where('account_id', $account_id);
			$this->db->where('branch_id', $branch_id);
			$this->db->where('transaction_date >=', $start_date);
			$this->db->where('transaction_date <=', $end_date);
			$result = $this->db->get()->row_array();
			return $result['transaction_date'];
		}

		public function getLastDate($account_id, $start_date, $end_date, $branch_id){
			$this->db->select_max('transaction_date');
			$this->db->from('acct_account_balance_detail');
			$this->db->where('account_id', $account_id);
			$this->db->where('branch_id', $branch_id);
			$this->db->where('transaction_date <', $start_date);
			$result = $this->db->get()->row_array();
			return $result['transaction_date'];
		}

		public function getOpeningBalance($opening_date, $account_id, $branch_id){
			$this->db->select('acct_account_balance_detail.opening_balance');
			$this->db->from('acct_account_balance_detail');
			$this->db->where('acct_account_balance_detail.transaction_date', $opening_date);
			$this->db->where('acct_account_balance_detail.account_id', $account_id);
			$this->db->where('acct_account_balance_detail.branch_id', $branch_id);
			$this->db->order_by('acct_account_balance_detail.account_balance_detail_id', 'ASC');
			$this->db->limit(1);
			$result = $this->db->get()->row_array();
			return $result['opening_balance'];
		}

		public function getLastBalance($last_date, $account_id, $branch_id){
			$this->db->select('acct_account_balance_detail.last_balance');
			$this->db->from('acct_account_balance_detail');
			$this->db->where('acct_account_balance_detail.transaction_date', $last_date);
			$this->db->where('acct_account_balance_detail.account_id', $account_id);
			$this->db->where('acct_account_balance_detail.branch_id', $branch_id);
			$this->db->limit(1);
			$this->db->order_by('acct_account_balance_detail.account_balance_detail_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result['last_balance'];
		}

		public function getAcctAccountBalanceDetailTeller($account_id, $start_date, $end_date, $branch_id){
			$this->db->select('acct_account_balance_detail.account_balance_detail_id, acct_account_balance_detail.transaction_type, acct_account_balance_detail.transaction_code, acct_account_balance_detail.transaction_date, acct_account_balance_detail.transaction_id, acct_account_balance_detail.account_id, acct_account.account_code, acct_account.account_name, acct_account_balance_detail.opening_balance, acct_account_balance_detail.account_in, acct_account_balance_detail.account_out, acct_account_balance_detail.last_balance');
			$this->db->from('acct_account_balance_detail');
			$this->db->join('acct_account', 'acct_account_balance_detail.account_id = acct_account.account_id');
			$this->db->where('acct_account_balance_detail.branch_id', $branch_id);
			$this->db->where('acct_account_balance_detail.transaction_date >=', $start_date);
			$this->db->where('acct_account_balance_detail.transaction_date <=', $end_date);
			$this->db->group_start();
			$this->db->where('acct_account_balance_detail.account_id', $account_id);
			$this->db->or_where('acct_account_balance_detail.account_id', 6);
			$this->db->or_where('acct_account_balance_detail.account_id', 524);
			$this->db->group_end();
			$this->db->order_by('acct_account_balance_detail.transaction_date', 'ASC');	
			$this->db->order_by('acct_account_balance_detail.account_balance_detail_id', 'ASC');	
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getOpeningDateTeller($account_id, $start_date, $branch_id){
			$this->db->select_min('transaction_date');
			$this->db->from('acct_account_balance_detail');
			$this->db->where('branch_id', $branch_id);
			$this->db->where('transaction_date', $start_date);
			$this->db->group_start();
			$this->db->where('account_id', $account_id);
			$this->db->or_where('account_id', 6);
			$this->db->or_where('account_id', 524);
			$this->db->group_end();
			$result = $this->db->get()->row_array();
			return $result['transaction_date'];
		}

		public function getLastDateTeller($account_id, $start_date, $branch_id){
			$this->db->select_max('transaction_date');
			$this->db->from('acct_account_balance_detail');
			$this->db->where('branch_id', $branch_id);
			$this->db->where('transaction_date <', $start_date);
			$this->db->group_start();
			$this->db->where('account_id', $account_id);
			$this->db->or_where('account_id', 6);
			$this->db->or_where('account_id', 524);
			$this->db->group_end();
			$result = $this->db->get()->row_array();
			return $result['transaction_date'];
		}

		public function getOpeningBalanceTeller($opening_date, $account_id, $branch_id){
			$this->db->select('acct_account_balance_detail.opening_balance');
			$this->db->from('acct_account_balance_detail');
			$this->db->where('acct_account_balance_detail.transaction_date', $opening_date);
			$this->db->where('acct_account_balance_detail.branch_id', $branch_id);
			$this->db->group_start();
			$this->db->where('acct_account_balance_detail.account_id', $account_id);
			$this->db->or_where('acct_account_balance_detail.account_id', 6);
			$this->db->or_where('acct_account_balance_detail.account_id', 524);
			$this->db->group_end();
			$this->db->limit(1);
			$this->db->order_by('acct_account_balance_detail.account_balance_detail_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result['opening_balance'];
		}

		public function getLastBalanceTeller($last_date, $account_id, $branch_id){
			$this->db->select('acct_account_balance_detail.last_balance');
			$this->db->from('acct_account_balance_detail');
			$this->db->where('acct_account_balance_detail.transaction_date', $last_date);
			$this->db->where('acct_account_balance_detail.branch_id', $branch_id);
			$this->db->group_start();
			$this->db->where('acct_account_balance_detail.account_id', $account_id);
			$this->db->or_where('acct_account_balance_detail.account_id', 6);
			$this->db->or_where('acct_account_balance_detail.account_id', 524);
			$this->db->group_end();
			$this->db->limit(1);
			$this->db->order_by('acct_account_balance_detail.account_balance_detail_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result['last_balance'];
		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			$this->db->limit(1);
			return $this->db->get()->row_array();
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

		public function getAccountCode($account_id){
			$this->db->select('account_code');
			$this->db->from('acct_account');
			$this->db->where('account_id', $account_id);
			$result = $this->db->get()->row_array();
			return $result['account_code'];
		}

		public function getAccountName($account_id){
			$this->db->select('account_name');
			$this->db->from('acct_account');
			$this->db->where('account_id', $account_id);
			$result = $this->db->get()->row_array();
			return $result['account_name'];
		}

		public function getAccountIDDefaultStatus($account_id){
			$this->db->select('acct_account.account_default_status');
			$this->db->from('acct_account');
			$this->db->where('acct_account.data_state', 0);
			$this->db->group_start();
			$this->db->where('acct_account.account_id', $account_id);
			$this->db->or_where('acct_account.account_id', 6);
			$this->db->or_where('acct_account.account_id', 524);
			$this->db->group_end();
			$result = $this->db->get()->row_array();
			return $result['account_default_status'];
		}

		public function getJournalVoucherDescription($journal_voucher_id, $account_id){
			$this->db->select('journal_voucher_description');
			$this->db->from('acct_journal_voucher_item');
			$this->db->where('journal_voucher_id', $journal_voucher_id);
			$this->db->group_start();
			$this->db->where('account_id', $account_id);
			$this->db->or_where('account_id', 6);
			$this->db->or_where('account_id', 524);
			$this->db->group_end();
			$result = $this->db->get()->row_array();
			return $result['journal_voucher_description'];
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
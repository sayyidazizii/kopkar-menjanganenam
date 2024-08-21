<?php
	defined('BASEPATH') or exit('No direct script access allowed');   
	class AcctDebtRepayment_model extends CI_Model {
		var $table = "acct_debt_repayment";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
			$this->dbminimarket = $this->load->database('minimarket', true);
		} 
		
		public function getAcctDebtRepayment(){
			$this->db->select('acct_debt_repayment.debt_repayment_date, acct_debt_repayment.debt_repayment_amount');
			$this->db->from('acct_debt_repayment');
			$this->db->where('acct_debt_repayment.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getAcctDebtRepayment_Detail($debt_repayment_id){
			$this->db->select('*');
			$this->db->from('acct_debt_repayment');
			$this->db->where('acct_debt_repayment.debt_repayment_id', $debt_repayment_id);
			$this->db->where('acct_debt_repayment.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result;
		}
		
		public function getMemberDebtAmount($member_id){
			$this->db->select('member_account_principal_debt, member_account_savings_debt, member_account_credits_debt, member_account_credits_store_debt, member_account_minimarket_debt, member_account_uniform_debt, member_account_receivable_amount');
			$this->db->from('core_member');
			$this->db->where('core_member.member_id', $member_id);
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result;
		}
		
		public function getMemberAccountReceivableAmount($member_id){
			$this->db->select('member_account_receivable_amount');
			$this->db->from('core_member');
			$this->db->where('core_member.member_id', $member_id);
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['member_account_receivable_amount'];
		}
		
		public function getAcctDebtRepaymentItem($debt_repayment_id){
			$this->db->select('core_member.member_no, core_member.member_name, acct_debt_repayment_item.debt_repayment_item_principal_amount, acct_debt_repayment_item.debt_repayment_item_savings_amount, acct_debt_repayment_item.debt_repayment_item_credits_amount, acct_debt_repayment_item.debt_repayment_item_credits_store_amount, acct_debt_repayment_item.debt_repayment_item_minimarket_amount, acct_debt_repayment_item.debt_repayment_item_uniform_amount, acct_debt_repayment_item.debt_repayment_item_amount');
			$this->db->from('acct_debt_repayment_item');
			$this->db->join('core_member', 'core_member.member_id = acct_debt_repayment_item.member_id');
			$this->db->where('acct_debt_repayment_item.debt_repayment_id', $debt_repayment_id);
			$this->db->where('acct_debt_repayment_item.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getAcctDebtRepaymentItemTemp(){
			$this->db->select('*');
			$this->db->from('acct_debt_repayment_item_temp');
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getCoreMember(){
			$this->db->select('core_member.member_no, core_member.member_name, core_member.member_account_receivable_amount, core_member.member_account_principal_debt, core_member.member_account_savings_debt, core_member.member_account_credits_debt, core_member.member_account_credits_store_debt, core_member.member_account_minimarket_debt, core_member.member_account_uniform_debt, core_member.member_account_receivable_status');
			$this->db->from('core_member');
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getCoreMemberID($member_no){
			$this->db->select('core_member.member_id');
			$this->db->from('core_member');
			$this->db->where('core_member.member_no', $member_no);
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['member_id'];
		}
		
		public function getCoreMemberNo($member_id){
			$this->db->select('core_member.member_no');
			$this->db->from('core_member');
			$this->db->where('core_member.member_id', $member_id);
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['member_no'];
		}
		
		public function getCoreMemberName($member_id){
			$this->db->select('core_member.member_name');
			$this->db->from('core_member');
			$this->db->where('core_member.member_id', $member_id);
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['member_name'];
		}

		public function getTransactionModuleID($transaction_module_code){
			$this->db->select('preference_transaction_module.transaction_module_id');
			$this->db->from('preference_transaction_module');
			$this->db->where('preference_transaction_module.transaction_module_code', $transaction_module_code);
			$result = $this->db->get()->row_array();
			return $result['transaction_module_id'];
		}

		public function getTransactionModuleIDMinimarket($transaction_module_code){
			$this->dbminimarket->select('preference_transaction_module.transaction_module_id');
			$this->dbminimarket->from('preference_transaction_module');
			$this->dbminimarket->where('preference_transaction_module.transaction_module_code', $transaction_module_code);
			$result = $this->dbminimarket->get()->row_array();
			return $result['transaction_module_id'];
		}
		
		public function getAcctDebtRepaymentLast($created_id){
			$this->db->select('acct_debt_repayment.debt_repayment_id');
			$this->db->from('acct_debt_repayment');
			$this->db->where('acct_debt_repayment.created_id', $created_id);
			$this->db->where('acct_debt_repayment.data_state', 0);
			$this->db->order_by('acct_debt_repayment.debt_repayment_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result['debt_repayment_id'];
		}
		
		public function getAcctDebtRepaymentNoLast($created_id){
			$this->db->select('acct_debt_repayment.debt_repayment_no');
			$this->db->from('acct_debt_repayment');
			$this->db->where('acct_debt_repayment.created_id', $created_id);
			$this->db->where('acct_debt_repayment.data_state', 0);
			$this->db->order_by('acct_debt_repayment.debt_repayment_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result['debt_repayment_no'];
		}
		
		public function getCoreMemberAccountReceivableAmount($member_id){
			$this->db->select('core_member.member_account_savings_debt, core_member.member_account_principal_debt, core_member.member_account_credits_debt, core_member.member_account_credits_store_debt, core_member.member_account_minimarket_debt, core_member.member_account_uniform_debt, core_member.member_account_receivable_amount');
			$this->db->from('core_member');
			$this->db->where('core_member.member_id', $member_id);
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			$this->db->limit(1);
			return $this->db->get()->row_array();
		}

		public function getUsername($user_id){
			$this->db->select('username');
			$this->db->from('system_user');
			$this->db->where('user_id', $user_id);
			$result = $this->db->get()->row_array();
			return $result['username'];
		}
		
		public function updateCoreMemberAccountReceivableAmount($data){
			$this->db->where("member_id",$data['member_id']);
			$query = $this->db->update('core_member', $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function insertAcctDebtRepayment($data){
			$query = $this->db->insert('acct_debt_repayment',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function insertAcctDebtRepaymentItem($data){
			$query = $this->db->insert('acct_debt_repayment_item',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function insertAcctDataRepaymentItemTemp($data){
			$query = $this->db->insert('acct_debt_repayment_item_temp',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}

		public function truncateAcctDataRepaymentItemTemp(){
			$query = $this->db->truncate('acct_debt_repayment_item_temp');
			if($query){
				return true;
			}else{
				return false;
			}
		}
 
		function get_datatables_master($start_date, $end_date)
		{
			$this->_get_datatables_query_master($start_date, $end_date);
			if($_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
			$query = $this->db->get();
			return $query->result();
		}
 
		public function count_all_master($start_date, $end_date)
		{
			$this->db->from($this->table);
			$this->db->where('debt_repayment_date >=', $start_date);
			$this->db->where('debt_repayment_date <=', $end_date);
			return $this->db->count_all_results();
		}
 
		function count_filtered_master($start_date, $end_date)
		{
			$this->_get_datatables_query_master($start_date, $end_date);
			$query = $this->db->get();
			return $query->num_rows();
		}

		public function insertAcctJournalVoucher($data){
			if ($this->db->insert('acct_journal_voucher', $data)){
				return true;
			}else{
				return false;
			}
		}

		public function insertAcctJournalVoucherMinimarket($data){
			if ($this->dbminimarket->insert('acct_journal_voucher', $data)){
				return true;
			}else{
				return false;
			}
		}
		
		public function insertAcctJournalVoucherItem($data){
			if($this->db->insert('acct_journal_voucher_item', $data)){
				return true;
			}else{
				return false;
			}
		}
		
		public function insertAcctJournalVoucherItemMinimarket($data){
			if($this->dbminimarket->insert('acct_journal_voucher_item', $data)){
				return true;
			}else{
				return false;
			}
		}

		public function getAccountIDDefaultStatus($account_id){
			$this->db->select('acct_account.account_default_status');
			$this->db->from('acct_account');
			$this->db->where('acct_account.account_id', $account_id);
			$this->db->where('acct_account.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['account_default_status'];
		}

		public function getAccountIDDefaultStatusMinimarket($account_id){
			$this->dbminimarket->select('acct_account.account_default_status');
			$this->dbminimarket->from('acct_account');
			$this->dbminimarket->where('acct_account.account_id', $account_id);
			$this->dbminimarket->where('acct_account.data_state', 0);
			$result = $this->dbminimarket->get()->row_array();
			return $result['account_default_status'];
		}

		public function getJournalVoucherID($created_id){
			$this->db->select('acct_journal_voucher.journal_voucher_id');
			$this->db->from('acct_journal_voucher');
			$this->db->where('acct_journal_voucher.created_id', $created_id);
			$this->db->order_by('acct_journal_voucher.journal_voucher_id', 'DESC');
			$this->db->limit(1);
			$result = $this->db->get()->row_array();
			return $result['journal_voucher_id'];
		}

		public function getJournalVoucherIDMinimarket($created_id){
			$this->dbminimarket->select('acct_journal_voucher.journal_voucher_id');
			$this->dbminimarket->from('acct_journal_voucher');
			$this->dbminimarket->where('acct_journal_voucher.created_id', $created_id);
			$this->dbminimarket->order_by('acct_journal_voucher.journal_voucher_id', 'DESC');
			$this->dbminimarket->limit(1);
			$result = $this->dbminimarket->get()->row_array();
			return $result['journal_voucher_id'];
		}
		
		private function _get_datatables_query_master($start_date, $end_date)
		{
			$this->db->select('acct_debt_repayment.debt_repayment_id, acct_debt_repayment.debt_repayment_date, acct_debt_repayment.debt_repayment_amount, acct_debt_repayment.debt_repayment_no');
			$this->db->from('acct_debt_repayment');
			$this->db->where('acct_debt_repayment.debt_repayment_date >=', date("Y-m-d", strtotime($start_date)));
			$this->db->where('acct_debt_repayment.debt_repayment_date <=', date("Y-m-d", strtotime($end_date)));
			 $this->db->where('acct_debt_repayment.data_state', 0);
			 $this->db->order_by('acct_debt_repayment.debt_repayment_date', 'ASC');
			$i = 0;
		 
			foreach ($this->column_search as $item)
			{
				if($_POST['search']['value'])
				{
					 
					if($i===0)
					{
						$this->db->group_start(); 
						$this->db->like($item, $_POST['search']['value']);
					}
					else
					{
						$this->db->or_like($item, $_POST['search']['value']);
					}
	 
					if(count($this->column_search) - 1 == $i) 
						$this->db->group_end(); 
				}
				$i++;
			}
			 
			if(isset($_POST['order'])) 
			{
				$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
			} 
			else if(isset($this->order))
			{
				$order = $this->order;
				$this->db->order_by(key($order), $order[key($order)]);
			}
		}
	}
?>
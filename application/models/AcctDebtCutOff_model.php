<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctDebtCutOff_model extends CI_Model {
		var $table = "acct_debt_cut_off";
		
		public function __construct(){
			parent::__construct();
			$this->CI 			= get_instance();
			$this->dbminimarket = $this->load->database('minimarket', true);
		} 
		
		public function getAcctDebtCutOff(){
			$this->db->select('*');
			$this->db->from('acct_debt_cut_off');
			$this->db->where('data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function insertAcctDebtCutOff($data){
			return $query = $this->db->insert('acct_debt_cut_off',$data);
		}

		public function insertAcctDebtCutOffItem($data){
			return $query = $this->db->insert('acct_debt_cut_off_item',$data);
		}

		public function insertAcctCreditsPayment($data){
			return $query = $this->db->insert('acct_credits_payment',$data);
		}

		public function insertSalesInvoiceStore($data){
			return $query = $this->dbminimarket->insert('sales_invoice',$data);
		}

		public function insertAcctJournalVoucherStore($data){
			return $query = $this->dbminimarket->insert('acct_journal_voucher',$data);
		}

		public function insertAcctJournalVoucherItemStore($data){
			return $query = $this->dbminimarket->insert('acct_journal_voucher_item',$data);
		}
		
		public function updateAcctCreditsAccount($data){
			$this->db->where("credits_account_id", $data['credits_account_id']);
			$query = $this->db->update("acct_credits_account", $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}

		public function getAcctCreditsAccount(){
			$this->db->select('*');
			$this->db->from('acct_credits_account');
			$this->db->where('payment_preference_id', 3);
			$this->db->where('credits_approve_status', 1);
			$this->db->where('credits_account_status', 0);
			$this->db->where('data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctDebtCutOffMonth(){
			$this->db->select('debt_cut_off_month');
			$this->db->from('acct_debt_cut_off');
			$this->db->where('data_state', 0);
			$this->db->order_by('debt_cut_off_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result['debt_cut_off_month'];
		}

		public function getAcctDebtCutOffYear(){
			$this->db->select('debt_cut_off_year');
			$this->db->from('acct_debt_cut_off');
			$this->db->where('data_state', 0);
			$this->db->order_by('debt_cut_off_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result['debt_cut_off_year'];
		}

		public function getAcctDebtCutOffID($created_id){
			$this->db->select('debt_cut_off_id');
			$this->db->from('acct_debt_cut_off');
			$this->db->where('created_id', $created_id);
			$this->db->where('data_state', 0);
			$this->db->order_by('debt_cut_off_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result['debt_cut_off_id'];
		}

		public function getTransactionModuleIDStore($transaction_module_code){
			$this->dbminimarket->select('transaction_module_id');
			$this->dbminimarket->from('preference_transaction_module');
			$this->dbminimarket->where('transaction_module_code', $transaction_module_code);
			$this->dbminimarket->where('data_state', 0);
			$result = $this->dbminimarket->get()->row_array();
			return $result['transaction_module_id'];
		}

		public function getTransactionModuleNameStore($transaction_module_code){
			$this->dbminimarket->select('transaction_module_name');
			$this->dbminimarket->from('preference_transaction_module');
			$this->dbminimarket->where('transaction_module_code', $transaction_module_code);
			$this->dbminimarket->where('data_state', 0);
			$result = $this->dbminimarket->get()->row_array();
			return $result['transaction_module_name'];
		}

		public function getSalesInvoiceNoStore($created_id){
			$this->dbminimarket->select('sales_invoice_no');
			$this->dbminimarket->from('sales_invoice');
			$this->dbminimarket->where('created_id', $created_id);
			$this->dbminimarket->where('data_state', 0);
			$this->db->order_by('sales_invoice_id', 'DESC');
			$result = $this->dbminimarket->get()->row_array();
			return $result['sales_invoice_no'];
		}

		public function getAcctJournalVoucherStore($created_id){
			$this->dbminimarket->select('journal_voucher_id');
			$this->dbminimarket->from('acct_journal_voucher');
			$this->dbminimarket->where('created_id', $created_id);
			$this->dbminimarket->where('data_state', 0);
			$this->db->order_by('journal_voucher_id', 'DESC');
			$result = $this->dbminimarket->get()->row_array();
			return $result['journal_voucher_id'];
		}

		public function getAccountIdStore($account_setting_name){
			$this->dbminimarket->select('account_id');
			$this->dbminimarket->from('acct_account_setting');
			$this->dbminimarket->where('account_setting_name', $account_setting_name);
			$result = $this->dbminimarket->get()->row_array();
			return $result['account_id'];
		}

		public function getAccountSettingStatusStore($account_setting_name){
			$this->dbminimarket->select('account_setting_status');
			$this->dbminimarket->from('acct_account_setting');
			$this->dbminimarket->where('account_setting_name', $account_setting_name);
			$result = $this->dbminimarket->get()->row_array();
			return $result['account_setting_status'];
		}

		public function getAccountDefaultStatusStore($account_id){
			$this->dbminimarket->select('account_default_status');
			$this->dbminimarket->from('acct_account');
			$this->dbminimarket->where('account_id', $account_id);
			$result = $this->dbminimarket->get()->row_array();
			return $result['account_default_status'];
		}
	}
?>
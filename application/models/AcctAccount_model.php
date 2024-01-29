<?php
	defined('BASEPATH') or exit('No direct script access allowed');   
	class AcctAccount_model extends CI_Model {
		var $table = "acct_account";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		}
		
		public function getDataAcctAccount(){
			$this->db->select('acct_account.account_id, acct_account.account_type_id, acct_account.account_code, acct_account.account_name, acct_account.account_group, acct_account.account_status, acct_account.account_default_status');
			$this->db->from('acct_account');
			$this->db->where('acct_account.data_state', 0);
			$this->db->order_by('acct_account.account_code', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function insertAcctAccount($data){
			$query = $this->db->insert('acct_account',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function getAcctAccount_Detail($account_id){
			$this->db->select('acct_account.account_id, acct_account.account_type_id, acct_account.account_code, acct_account.account_name, acct_account.account_group, acct_account.account_status');
			$this->db->from('acct_account');
			$this->db->where('acct_account.data_state', 0);
			$this->db->where('acct_account.account_id', $account_id);
			return $this->db->get()->row_array();
		}
		
		public function updateAcctAccount($data){
			$this->db->where("account_id",$data['account_id']);
			$query = $this->db->update($this->table, $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function deleteAcctAccount($account_id){
			$this->db->where("account_id",$account_id);
			$query = $this->db->update($this->table, array('data_state'=>1));
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function getAccountCode(){
			$this->db->select('account_code, profit_loss_report_id');
			$this->db->from('acct_profit_loss_report');
			$this->db->where('account_code !=', '');
			$this->db->where('account_id', null);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getAccountIDFromCode($account_codes){
			$this->db->select('acct_account.account_id');
			$this->db->from('acct_account');
			$this->db->where('acct_account.account_code', $account_codes);
			$result = $this->db->get()->row_array();
			return $result['account_id'];
		}

		public function getTest(){
			$this->db->select('acct_account.account_id, acct_account.account_code');
			$this->db->from('acct_profit_loss_report');
			$this->db->join('acct_account', 'acct_account.account_code = acct_profit_loss_report.account_code');
			$this->db->where('acct_profit_loss_report.account_code !=', '');
			$this->db->where('acct_profit_loss_report.account_id', null);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function updateProfitLossReport($data){
			$this->db->where("profit_loss_report_id", $data['profit_loss_report_id']);
			$query = $this->db->update('acct_profit_loss_report', $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
	}
?>
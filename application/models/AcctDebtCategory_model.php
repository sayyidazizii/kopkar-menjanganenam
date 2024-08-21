<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctDebtCategory_model extends CI_Model {
		var $table = "acct_debt_category";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		public function getAcctDebtCategory(){
			$this->db->select('*');
			$this->db->from('acct_debt_category');
			$this->db->where('data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctAccount(){
			$hasil = $this->db->query("
							SELECT acct_account.account_id, 
							CONCAT(acct_account.account_code,' - ', acct_account.account_name) as account_code 
							FROM acct_account
							WHERE acct_account.data_state='0'
							and RIGHT(acct_account.account_code, 2) != 00");
			return $hasil->result_array();
		}

		public function getAcctAccountCodeName($account_id){
			$this->db->select('*');
			$this->db->from('acct_account');
			$this->db->where('account_id', $account_id);
			$this->db->where('data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['account_code'].' - '.$result['account_name'];
		}

		public function insertAcctDebtCategory($data){
			return $query = $this->db->insert('acct_debt_category',$data);
		}
	
		public function getAcctDebtCategory_Detail($debt_category_id){
			$this->db->select('*');
			$this->db->from('acct_debt_category');
			$this->db->where('acct_debt_category.data_state', 0);
			$this->db->where('acct_debt_category.debt_category_id', $debt_category_id);
			return $this->db->get()->row_array();
		}
		
		public function updateAcctDebtCategory($data){
			$this->db->where("debt_category_id",$data['debt_category_id']);
			$query = $this->db->update($this->table, $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function deleteAcctDebtCategory($debt_category_id){
			$this->db->where("debt_category_id",$debt_category_id);
			$query = $this->db->update($this->table, array('data_state'=>1));
			if($query){
				return true;
			}else{
				return false;
			}
		}
	}
?>
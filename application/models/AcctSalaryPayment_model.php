<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctSalaryPayment_model extends CI_Model {
		var $table = "acct_credits_payment";
		var $column_order = array(null, 'acct_credits_account.credits_account_serial','core_member.member_name','acct_credits_payment.credits_payment_date',);
		var $column_search = array('acct_credits_account.credits_account_serial','core_member.member_name','acct_credits_payment.credits_payment_date');
		var $order = array('acct_credits_payment.credits_payment_id' => 'asc');
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 

		public function getAcctBankAccount(){
			$this->db->select('acct_bank_account.bank_account_id, CONCAT(acct_account.account_code," - ", acct_bank_account.bank_account_name) AS bank_account_code');
			$this->db->from('acct_bank_account');
			$this->db->join('acct_account', 'acct_bank_account.account_id = acct_account.account_id');
			$this->db->where('acct_bank_account.data_state', 0);
			return $this->db->get()->result_array();
		}

		public function getAcctCreditsAccount(){
			$this->db->select('acct_credits_account.*, acct_credits.credits_name, core_member.member_no, core_member.member_name, core_division.division_name');
			$this->db->from('acct_credits_account');
			$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
			$this->db->join('core_member_working', 'core_member.member_id = core_member_working.member_id');
			$this->db->join('core_division', 'core_member_working.division_id = core_division.division_id');
			$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->where('acct_credits_account.data_state', 0);
			$this->db->where('acct_credits_account.credits_account_status', 0);
			$this->db->where('acct_credits_account.credits_approve_status', 1);
			$this->db->where('acct_credits_account.payment_preference_id', 3);
			$this->db->where('DATE_FORMAT(acct_credits_account.credits_account_payment_date,"%Y%m")', date('Ym'));
			return $this->db->get()->result_array();
		}

		public function getCoreMemberAccountReceivableAmount($member_id){
			$this->db->select('core_member.member_account_receivable_amount, core_member.member_account_credits_debt, core_member.member_account_credits_store_debt');
			$this->db->from('core_member');
			$this->db->where('core_member.member_id', $member_id);
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function updateCoreMember($data){
			$this->db->where("member_id",$data['member_id']);
			$query = $this->db->update('core_member', $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}

		function get_datatables($start_date, $end_date, $credits_id, $branch_id)
	    {
	        $this->_get_datatables_query($start_date, $end_date, $credits_id, $branch_id);
	        if($_POST['length'] != -1)
	        $this->db->limit($_POST['length'], $_POST['start']);
	        $query = $this->db->get();
	        return $query->result();
	    }
		 
	    function count_filtered($start_date, $end_date, $credits_id, $branch_id)
	    {
	        $this->_get_datatables_query($start_date, $end_date, $credits_id, $branch_id);
	        $query = $this->db->get();
	        return $query->num_rows();
	    }
 
	    public function count_all($start_date, $end_date, $credits_id, $branch_id)
	    {
	        $this->db->from('acct_credits_payment');
			$this->db->join('core_member','acct_credits_payment.member_id = core_member.member_id');
			$this->db->join('acct_credits_account','acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->where('acct_credits_payment.credits_payment_date >=', $start_date);
			$this->db->where('acct_credits_payment.credits_payment_date <=', $end_date);
			if(!empty($credits_id)){
				$this->db->where('acct_credits_payment.credits_id', $credits_id);
			}
			if(!empty($branch_id)){
				$this->db->where('acct_credits_payment.branch_id', $branch_id);
			}
	        return $this->db->count_all_results();
	    }

		private function _get_datatables_query($start_date, $end_date, $credits_id, $branch_id)
	    {
	         
	        $this->db->from('acct_credits_payment');
			$this->db->join('core_member','acct_credits_payment.member_id = core_member.member_id');
			$this->db->join('acct_credits','acct_credits_payment.credits_id = acct_credits.credits_id');
			$this->db->join('acct_credits_account','acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->where('acct_credits_payment.credits_payment_date >=', $start_date);
			$this->db->where('acct_credits_payment.credits_payment_date <=', $end_date);
			$this->db->where('acct_credits_payment.salary_payment_status !=', 0);
			if(!empty($credits_id)){
				$this->db->where('acct_credits_payment.credits_id', $credits_id);
			}
			if(!empty($branch_id)){
				$this->db->where('acct_credits_payment.branch_id', $branch_id);
			}
	 
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
		
		public function insert($data){
			return $query = $this->db->insert('acct_credits_payment',$data);
		}

		public function insertTemp($data){
			return $query = $this->db->insert('acct_credits_payment_temp',$data);
		}

		public function getAcctCreditsPaymentsTemp(){

			$this->db->select('acct_credits_payment_temp.*, acct_credits_account.*, acct_credits.credits_name, core_member.member_no, core_member.member_name, core_division.division_name');
			$this->db->from('acct_credits_payment_temp');
			$this->db->join('acct_credits_account', 'acct_credits_account.credits_account_id = acct_credits_payment_temp.credits_account_id');
			$this->db->join('core_member', 'acct_credits_payment_temp.member_id = core_member.member_id');
			$this->db->join('core_member_working', 'acct_credits_payment_temp.member_id = core_member_working.member_id');
			$this->db->join('core_division', 'core_member_working.division_id = core_division.division_id');
			$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->where('acct_credits_payment_temp.data_state', 0);
			// $this->db->where('acct_credits_account.credits_account_status', 0);
			// $this->db->where('acct_credits_account.credits_approve_status', 1);
			// $this->db->where('acct_credits_payment_temp.payment_preference_id', 3);
			$this->db->where('DATE_FORMAT(acct_credits_payment_temp.credits_payment_date,"%Y%m")', date('Ym'));
			return $this->db->get()->result_array();
		}

		public function getAcctCreditsPaymentsTempFirst($credits_payment_id){

			$this->db->select('acct_credits_payment_temp.*, acct_credits_account.*');
			$this->db->from('acct_credits_payment_temp');
			$this->db->join('acct_credits_account', 'acct_credits_account.credits_account_id = acct_credits_payment_temp.credits_account_id');
			$this->db->where('acct_credits_payment_temp.data_state', 0);
			// $this->db->where('acct_credits_payment_temp.payment_preference_id', 3);
			$this->db->where('acct_credits_payment_temp.credits_payment_id', $credits_payment_id);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function deleteSalaryPaymentTemp($credits_payment_id,$data)
		{
			$this->db->where("credits_payment_id",$credits_payment_id);
			$query = $this->db->update('acct_credits_payment_temp', $data);
			if($query){
				return true;
			}else{
				// return false;
				// Log the last query and the error message
				$last_query = $this->db->last_query();
				$error = $this->db->error();
				log_message('error', 'Failed to insert into acct_journal_voucher: ' . $last_query);
				log_message('error', 'DB Error: ' . $error['message']);
				
				// Return detailed error information for debugging
				return array('query' => $last_query, 'error' => $error);
			}
		}

		public function insertAcctSavingsMemberDetail($data){
			$query = $this->db->insert('acct_savings_member_detail',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function getPreferenceIncome(){
			$this->db->select('preference_income.income_id, preference_income.income_percentage, preference_income.income_status, preference_income.account_id');
			$this->db->from('preference_income');
			$this->db->where('preference_income.data_state', 0);
			$this->db->order_by('preference_income.income_id', 'ASC');
			return $this->db->get()->result_array();
		}

		public function getCreditsPaymentToken($credits_payment_token){
			$this->db->select('credits_payment_token');
			$this->db->from('acct_credits_payment');
			$this->db->where('credits_payment_token', $credits_payment_token);
			return $this->db->get();
		}

		public function AcctSalaryPaymentLast($created_id){
			$this->db->select('acct_credits_payment.credits_payment_id, acct_credits_payment.member_id, core_member.member_name, acct_credits_payment.credits_account_id, acct_credits_account.credits_account_serial, acct_credits_account.credits_id, acct_credits.credits_name');
			$this->db->from('acct_credits_payment');
			$this->db->join('core_member','acct_credits_payment.member_id = core_member.member_id');
			$this->db->join('acct_credits_account','acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->join('acct_credits','acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->where('acct_credits_payment.created_id', $created_id);
			$this->db->order_by('acct_credits_payment.credits_payment_id','DESC');
			$this->db->limit(1);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getSavingsAccountNO($savings_account_id){
			$this->db->select('savings_account_no');
			$this->db->from('acct_savings_account');
			$this->db->where('savings_account_id', $savings_account_id);
			$result = $this->db->get()->row_array();
			return $result['savings_account_no'];
		}

		public function getSavingsAccountID($savings_id){
			$this->db->select('account_id');
			$this->db->from('acct_savings');
			$this->db->where('savings_id', $savings_id);
			$result = $this->db->get()->row_array();
			return $result['account_id'];
		}

		public function getAccountBankID($bank_account_id){
			$this->db->select('account_id');
			$this->db->from('acct_bank_account');
			$this->db->where('bank_account_id', $bank_account_id);
			$result = $this->db->get()->row_array();
			return $result['account_id'];
		}
		
		public function getDataByIDCredit($id){
			$this->db->select('*');
			$this->db->from($this->table);
			$this->db->where('credits_account_id',$id);
			$this->db->where('data_state',0);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getAcctCreditspayment_Detail($credits_payment_id){
			$this->db->select('acct_credits_payment.credits_payment_id, acct_credits_payment.member_id, core_member.member_name, core_member.member_address, acct_credits_payment.credits_account_id, acct_credits_account.credits_account_serial, acct_credits_account.credits_id, acct_credits.credits_name, acct_credits_payment.credits_payment_to, acct_credits_payment.credits_payment_amount, acct_credits_payment.savings_account_id, core_division.division_name');
			$this->db->from('acct_credits_payment');
			$this->db->join('core_member','acct_credits_payment.member_id = core_member.member_id');
			$this->db->join('core_member_working','core_member.member_id = core_member_working.member_id');
			$this->db->join('acct_credits_account','acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->join('acct_credits','acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->join('core_division','core_member_working.division_id = core_division.division_id');
			$this->db->where('acct_credits_payment.credits_payment_id', $credits_payment_id);
			$result = $this->db->get()->row_array();
			return $result;
		}
		
		public function getAcctCreditsPaymentToken($token){
			$this->db->select('acct_credits_payment.credits_payment_id, acct_credits_payment.member_id, core_member.member_name, core_member.member_address, acct_credits_payment.credits_account_id, acct_credits_account.credits_account_serial, acct_credits_account.credits_id, acct_credits.credits_name, acct_credits_payment.credits_payment_to, acct_credits_payment.credits_payment_amount, acct_credits_payment.savings_account_id, core_division.division_name');
			$this->db->from('acct_credits_payment');
			$this->db->join('core_member','acct_credits_payment.member_id = core_member.member_id');
			$this->db->join('core_member_working','core_member.member_id = core_member_working.member_id');
			$this->db->join('acct_credits_account','acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->join('acct_credits','acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->join('core_division','core_member_working.division_id = core_division.division_id');
			$this->db->like('acct_credits_payment.credits_payment_token', $token);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getStoreName($store_id){
			$this->db->select('core_store.store_name');
			$this->db->from('core_store');
			$this->db->where('core_store.store_id', $store_id);
			$result = $this->db->get()->row_array();
			return $result['store_name'];
		}
		
		public function getAcctCreditsPaymentsPokokLast($credits_account_id){
			$this->db->select('acct_credits_payment.credits_payment_date, acct_credits_payment.credits_payment_principal');
			$this->db->from('acct_credits_payment');
			$this->db->where('acct_credits_payment.credits_payment_principal >', 0);
			$this->db->where('acct_credits_payment.credits_account_id', $credits_account_id);
			$this->db->order_by('acct_credits_payment.credits_payment_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result;
		}
		
		public function getAcctCreditsPaymentsLast($credits_account_id){
			$this->db->select('acct_credits_payment.credits_payment_date, acct_credits_payment.credits_payment_principal');
			$this->db->from('acct_credits_payment');
			$this->db->where('acct_credits_payment.credits_account_id', $credits_account_id);
			$this->db->order_by('acct_credits_payment.credits_payment_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result;
		}
	}
?>
<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctUniformSales_model extends CI_Model {
		var $table = "acct_uniform_sales";
		var $column_order = array(null, 'acct_uniform_sales.uniform_sales_no','core_member.member_name','core_member.member_address',); //field yang ada di table user
		var $column_search = array('acct_uniform_sales.uniform_sales_no','core_member.member_name','core_member.member_address'); //field yang diizin untuk pencarian 
		var $order = array('acct_uniform_sales.uniform_sales_id' => 'asc');
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		public function getAcctUniformSales($start_date, $end_date, $uniform_sales_id){
			$this->db->select('acct_uniform_sales.*, core_member.member_no, core_member.member_name');
			$this->db->from('acct_uniform_sales');
			$this->db->join('core_member', 'acct_uniform_sales.member_id = core_member.member_id');
			$this->db->where('acct_uniform_sales.uniform_sales_id >=', $start_date);
			$this->db->where('acct_uniform_sales.uniform_sales_id <=', $end_date);
			$this->db->where('acct_uniform_sales.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreMember_Detail($member_id){
			$this->db->select('core_member.member_id, core_member.branch_id, core_branch.branch_name, core_member.member_no, core_member.member_name, core_member.member_gender, core_member.member_place_of_birth, core_member.member_date_of_birth, core_member.member_address, core_member.province_id, core_province.province_name, core_member.city_id, core_city.city_name, core_member.kecamatan_id, core_kecamatan.kecamatan_name, core_member.member_phone, core_member.member_job, core_member.member_identity, core_member.member_identity_no, core_member.member_postal_code, core_member.member_mother, core_member.member_heir, core_member.member_family_relationship, core_member.member_status, core_member.member_register_date, core_member.member_principal_savings, core_member.member_special_savings, core_member.member_mandatory_savings, core_member.member_active_status');
			$this->db->from('core_member');
			$this->db->join('core_province', 'core_member.province_id = core_province.province_id');
			$this->db->join('core_city', 'core_member.city_id = core_city.city_id');
			$this->db->join('core_kecamatan', 'core_member.kecamatan_id = core_kecamatan.kecamatan_id');
			$this->db->join('core_branch', 'core_member.branch_id = core_branch.branch_id');
			$this->db->where('core_member.data_state', 0);
			$this->db->where('core_member.member_id', $member_id);
			return $this->db->get()->row_array();
		}

		function get_datatables($start_date, $end_date, $branch_id)
	    {
	        $this->_get_datatables_query($start_date, $end_date, $branch_id);
	        if($_POST['length'] != -1)
	        $this->db->limit($_POST['length'], $_POST['start']);
	        $query = $this->db->get();
	        return $query->result();
	    }
	 
	    function count_filtered($start_date, $end_date, $branch_id)
	    {
	        $this->_get_datatables_query($start_date, $end_date, $branch_id);
	        $query = $this->db->get();
	        return $query->num_rows();
	    }
	 
	    public function count_all($start_date, $end_date, $branch_id)
	    {
	        $this->db->from($this->table);
	        $this->db->where('acct_uniform_sales.uniform_sales_date >=', $start_date);
			$this->db->where('acct_uniform_sales.uniform_sales_date <=', $end_date);
			if(!empty($branch_id)){
				$this->db->where('acct_uniform_sales.branch_id', $branch_id);
			}
			$this->db->where('acct_uniform_sales.data_state', 0);
	        return $this->db->count_all_results();
	    }

	    private function _get_datatables_query($start_date, $end_date, $branch_id)
	    {
	    	$this->db->select('acct_uniform_sales.*, core_member.*');
	       	$this->db->from('acct_uniform_sales');
			$this->db->join('core_member', 'acct_uniform_sales.member_id = core_member.member_id');
			$this->db->where('acct_uniform_sales.uniform_sales_date >=', $start_date);
			$this->db->where('acct_uniform_sales.uniform_sales_date <=', $end_date);
			if(!empty($branch_id)){
				$this->db->where('acct_uniform_sales.branch_id', $branch_id);
			}
			$this->db->where('acct_uniform_sales.data_state', 0);
	        $i = 0;
	     
	        foreach ($this->column_search as $item) // looping awal
	        {
	            if($_POST['search']['value']) // jika datatable mengirimkan pencarian dengan metode POST
	            {
	                 
	                if($i===0) // looping awal
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

		public function getUsername($user_id){
			$this->db->select('username');
			$this->db->from('system_user');
			$this->db->where('user_id', $user_id);
			$result = $this->db->get()->row_array();
			return $result['username'];
		}
		
		public function insertAcctUniformSales($data){
			return $query = $this->db->insert('acct_uniform_sales',$data);
		}

		public function getUniformSalesToken($uniform_sales_token){
			$this->db->select('uniform_sales_token');
			$this->db->from('acct_uniform_sales');
			$this->db->where('uniform_sales_token', $uniform_sales_token);
			return $this->db->get();
		}

		public function getCoreMemberAccountReceivableAmount($member_id){
			$this->db->select('member_id, member_account_receivable_amount, member_account_uniform_debt');
			$this->db->from('core_member');
			$this->db->where('member_id', $member_id);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function updateCoreMember($data){
			$this->db->where("member_id", $data['member_id']);
			$query = $this->db->update('core_member', $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			$this->db->limit(1);
			return $this->db->get()->row_array();
		}

		public function getTransactionModuleID($transaction_module_code){
			$this->db->select('preference_transaction_module.transaction_module_id');
			$this->db->from('preference_transaction_module');
			$this->db->where('preference_transaction_module.transaction_module_code', $transaction_module_code);
			$result = $this->db->get()->row_array();
			return $result['transaction_module_id'];
		}

		public function getAcctUniformSales_Last($created_id){
			$this->db->select('acct_uniform_sales.uniform_sales_id, acct_uniform_sales.member_id, core_member.member_name');
			$this->db->from('acct_uniform_sales');
			$this->db->join('core_member','acct_uniform_sales.member_id = core_member.member_id');
			$this->db->where('acct_uniform_sales.created_id', $created_id);
			$this->db->limit(1);
			$this->db->order_by('acct_uniform_sales.uniform_sales_id','DESC');
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getMutationJournalDesc($mutation_id){
			$this->db->select('mutation_journal_desc');
			$this->db->from('acct_mutation');
			$this->db->where('mutation_id', $mutation_id);
			$result = $this->db->get()->row_array();
			return $result['mutation_journal_desc'];
		}

		public function insertAcctJournalVoucher($data){
			if ($this->db->insert('acct_journal_voucher', $data)){
				return true;
			}else{
				return false;
			}
		}

		public function getJournalVoucherToken($journal_voucher_token){
			$this->db->select('journal_voucher_token');
			$this->db->from('acct_journal_voucher');
			$this->db->where('journal_voucher_token', $journal_voucher_token);
			return $this->db->get();
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

		public function getAccountIDDefaultStatus($account_id){
			$this->db->select('acct_account.account_default_status');
			$this->db->from('acct_account');
			$this->db->where('acct_account.account_id', $account_id);
			$this->db->where('acct_account.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['account_default_status'];
		}
		
		public function insertAcctJournalVoucherItem($data){
			if($this->db->insert('acct_journal_voucher_item', $data)){
				return true;
			}else{
				return false;
			}
		}

		public function getJournalVoucherItemToken($journal_voucher_item_token){
			$this->db->select('journal_voucher_item_token');
			$this->db->from('acct_journal_voucher_item');
			$this->db->where('journal_voucher_item_token', $journal_voucher_item_token);
			return $this->db->get();
		}
		
		public function getAcctUniformSales_Detail($uniform_sales_id){
			$this->db->select('acct_uniform_sales.*, core_member.member_name, core_member.member_no');
			$this->db->from('acct_uniform_sales');
			$this->db->join('core_member', 'acct_uniform_sales.member_id = core_member.member_id');
			$this->db->join('core_branch', 'acct_uniform_sales.branch_id = core_branch.branch_id');
			$this->db->where('acct_uniform_sales.data_state', 0);
			$this->db->where('acct_uniform_sales.uniform_sales_id', $uniform_sales_id);
			return $this->db->get()->row_array();
		}

		public function updateAcctUniformSales($data){
			$this->db->where("uniform_sales_id",$data['uniform_sales_id']);
			$query = $this->db->update($this->table, $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
	}
?>
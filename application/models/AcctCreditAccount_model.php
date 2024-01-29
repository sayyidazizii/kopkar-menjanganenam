<?php
	defined('BASEPATH') or exit('No direct script access allowed');

	class AcctCreditAccount_model extends CI_Model {

		var $table 			= "acct_credits_account";
		var $column_order 	= array(null, 'acct_credits_account.credits_account_serial','core_member.member_name','core_member.member_address',); //field yang ada di table user
		var $column_search 	= array('core_member.member_name','core_member.member_no','acct_credits_account.credits_account_serial','core_member.member_address'); //field yang diizin untuk pencarian 
		var $order 			= array('acct_credits_account.credits_account_id' => 'asc');
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
			$this->dbminimarket = $this->load->database('minimarket', true);
		} 

		public function getAcctCreditsAccount($start_date, $end_date, $branch_id, $member_id, $credits_id){
			$this->db->select('acct_credits_account.credits_account_id, acct_credits_account.credits_account_serial, acct_credits_account.credits_account_date, acct_credits_account.member_id, core_member.member_name, core_member.member_no, core_member.member_address, core_member.province_id, core_province.province_name, core_member.city_id, core_city.city_name, core_member.kecamatan_id, core_kecamatan.kecamatan_name, acct_credits_account.credits_id, acct_credits.credits_name');
			$this->db->from('acct_credits_account');
			$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
			$this->db->join('core_province', 'core_member.province_id = core_province.province_id');
			$this->db->join('core_city', 'core_member.city_id = core_city.city_id');
			$this->db->join('core_kecamatan', 'core_member.kecamatan_id = core_kecamatan.kecamatan_id');
			$this->db->where('acct_credits_account.data_state', 0);
			$this->db->where('acct_credits_account.credits_account_date >=', $start_date);
			$this->db->where('acct_credits_account.credits_account_date <=', $end_date); 
			$this->db->where('acct_credits_account.branch_id', $branch_id); 
			
			if($member_id != ''){
				$this->db->where('acct_credits_account.member_id', $member_id);
			}

			if($credits_id != ''){
				$this->db->where('acct_credits_account.credits_id', $credits_id);
			}

			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreMember($branch_id){
			$this->db->select('core_member.member_id, core_member.member_name');
			$this->db->from('core_member');
			$this->db->where('core_member.branch_id', $branch_id);
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreStore(){
			$this->db->select('core_store.store_id, core_store.store_name');
			$this->db->from('core_store');
			$this->db->where('core_store.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctCredits(){
			$this->db->select('acct_credits.credits_id, acct_credits.credits_name');
			$this->db->from('acct_credits');
			$this->db->where('acct_credits.data_state', 0);
			$this->db->order_by('acct_credits.credits_number','ASC');
			$result = $this->db->get()->result_array();
			return $result;	
		}

		public function getAcctCreditsName($credits_id){
			$this->db->select('acct_credits.credits_id, acct_credits.credits_name');
			$this->db->from('acct_credits');
			$this->db->where('acct_credits.credits_id', $credits_id);			
			$result = $this->db->get()->row_array();
			return $result['credits_name'];	
		}

		public function getCoreStoreName($store_id){
			$this->db->select('core_store.store_id, core_store.store_name');
			$this->db->from('core_store');
			$this->db->where('core_store.store_id', $store_id);			
			$result = $this->db->get()->row_array();
			return $result['store_name'];	
		}

		public function getCoreBranch(){
			$this->db->select('core_branch.branch_id, core_branch.branch_name');
			$this->db->from('core_branch');
			$this->db->where('core_branch.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getBankAccount(){
			$this->db->select('bank_account_id, bank_account_name');
			$this->db->from('acct_bank_account');
			$this->db->where('data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAccountBank($bank_account_id){
			$this->db->select('account_id');
			$this->db->from('acct_bank_account');
			$this->db->where('bank_account_id', $bank_account_id);
			$this->db->where('data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['account_id'];
		}

		public function getBankAccountName($bank_account_id){
			$this->db->select('bank_account_name');
			$this->db->from('acct_bank_account');
			$this->db->where('bank_account_id', $bank_account_id);
			$this->db->where('data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['bank_account_name'];
		}

		public function getBranchManager($branch_id){
			$this->db->select('core_branch.branch_manager');
			$this->db->from('core_branch');
			$this->db->where('core_branch.branch_id', $branch_id);
			$result = $this->db->get()->row_array();
			return $result['branch_manager'];
		}

		public function getAcctCreditsAccount_Detail($credits_account_id){
			$this->db->select('acct_credits_account.*, core_member.member_name, core_member.member_no, core_member.member_address, core_member.province_id, core_province.province_name,core_member.member_mother, core_member.city_id, core_city.city_name, core_member.kecamatan_id, core_kecamatan.kecamatan_name, core_member.member_active_status, acct_credits.credits_id,core_member.member_identity, core_member.member_identity_no, acct_credits.credits_name, core_branch.branch_name, core_member.member_phone, core_member_working.member_company_name, core_member_working.member_company_job_title, core_member.member_mandatory_savings_last_balance, core_member.member_special_savings_last_balance, core_member.member_principal_savings_last_balance, core_division.division_name');
			$this->db->from('acct_credits_account');
			$this->db->join('core_branch', 'acct_credits_account.branch_id = core_branch.branch_id');
			$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
			$this->db->join('core_member_working', 'acct_credits_account.member_id = core_member_working.member_id');
			$this->db->join('core_division', 'core_member_working.division_id = core_division.division_id');
			$this->db->join('core_province', 'core_member.province_id = core_province.province_id');
			$this->db->join('core_city', 'core_member.city_id = core_city.city_id');
			$this->db->join('core_kecamatan', 'core_member.kecamatan_id = core_kecamatan.kecamatan_id');
			$this->db->where('acct_credits_account.data_state', 0);
			$this->db->where('acct_credits_account.credits_account_id', $credits_account_id);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getAcctCreditsAgunan_Detail($credits_account_id){
			$this->db->select('acct_credits_agunan.*');
			$this->db->from('acct_credits_agunan');
			$this->db->where('acct_credits_agunan.credits_account_id', $credits_account_id);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getExistedCreditsAccount($member_id, $credits_account_id){
			$this->db->select('acct_credits_account.*, acct_credits.credits_name');
			$this->db->from('acct_credits_account');
			$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->where('acct_credits_account.member_id', $member_id);
			$this->db->where('acct_credits_account.credits_account_id !=', $credits_account_id);
			$this->db->where('acct_credits_account.credits_account_status', 0);
			$this->db->where('acct_credits_account.credits_approve_status', 1);
			$this->db->where('acct_credits_account.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctCreditsPayment_Detail($credits_account_id){
			$this->db->select('acct_credits_payment.credits_payment_date, acct_credits_payment.credits_payment_amount, acct_credits_payment.credits_payment_principal, acct_credits_payment.credits_payment_interest, acct_credits_payment.credits_principal_last_balance, acct_credits_payment.credits_interest_last_balance, acct_credits_payment.credits_payment_fine, acct_credits_payment.credits_payment_fine_last_balance');
			$this->db->from('acct_credits_payment');
			$this->db->join('acct_credits_account','acct_credits_payment.credits_account_id = acct_credits_account.credits_account_id');
			$this->db->where('acct_credits_payment.data_state', 0);
			$this->db->where('acct_credits_payment.credits_account_id', $credits_account_id);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getCreditsAccount_Detail($credits_account_id){
			$this->db->select('*');
			$this->db->from('acct_credits_account');
			$this->db->where('credits_account_id', $credits_account_id);
			$result = $this->db->get()->row_array();
			return $result;
		}
		
		public function getCreditsAccount_Detail2($credits_account_id){
			$this->db->select('acct_credits_account.*, acct_savings_account.savings_id, acct_savings_account.savings_account_last_balance');
			$this->db->from('acct_credits_account');
			$this->db->join('acct_savings_account','acct_credits_account.savings_account_id = acct_savings_account.savings_account_id');
			$this->db->where('acct_credits_account.credits_account_id', $credits_account_id);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getCreditsPaymentSuspend_Detail($credits_account_id){
			$this->db->select('*');
			$this->db->from('acct_credits_payment_suspend');
			$this->db->where('credits_account_id', $credits_account_id);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getBranchCode($branch_id){
			$this->db->select('RIGHT(core_branch.branch_code,2) as branch_code');
			$this->db->from('core_branch');
			$this->db->where('branch_id', $branch_id);
			$result = $this->db->get()->row_array();
			return $result['branch_code'];
		}

		public function getBranchCity($branch_id){
			$this->db->select('branch_city');
			$this->db->from('core_branch');
			$this->db->where('branch_id', $branch_id);
			$result = $this->db->get()->row_array();
			return $result['branch_city'];
		}

		public function getCreditsCode($credits_id){
			$this->db->select('credits_code');
			$this->db->from('acct_credits');
			$this->db->where('credits_id', $credits_id);
			$result = $this->db->get()->row_array();
			return $result['credits_code'];
		}

		public function getSourceFundCode($source_fund_id){
			$this->db->select('source_fund_code');
			$this->db->from('acct_source_fund');
			$this->db->where('source_fund_id', $source_fund_id);
			$result = $this->db->get()->row_array();
			return $result['source_fund_code'];
		}
		
		public function getLastAccountCreditsNo($branch_id, $credits_id){
			$this->db->select('RIGHT(acct_credits_account.credits_account_serial,5) as last_credits_account_serial');
			$this->db->from('acct_credits_account');
			$this->db->where('acct_credits_account.branch_id', $branch_id);
			$this->db->where('acct_credits_account.credits_id', $credits_id);
			$this->db->limit(1);
			$this->db->order_by('acct_credits_account.credits_account_serial','DESC');
			$result = $this->db->get();
			return $result;
		}
	
		public function insertAcctCreditAccount($data){
			return $query = $this->db->insert('acct_credits_account',$data);
		}

		public function getCreditsAccountToken($credits_account_token){
			$this->db->select('credits_account_token');
			$this->db->from('acct_credits_account');
			$this->db->where('credits_account_token', $credits_account_token);
			return $this->db->get();
		}
		
		public function updatedata($data,$id){
			$this->db->where("credits_account_id",$id);
			$query = $this->db->update($this->table, $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function getData(){
			$this->db->select('*');
			$this->db->from($this->table);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getDetailByID($id){
			$this->db->select('acct_credits_account.*, core_member.member_name, core_member.member_no, core_member.member_gender, core_member.member_address, core_member.member_phone, core_member.member_date_of_birth, core_member.member_identity_no, core_member.city_id, core_city.city_name, core_member.kecamatan_id, core_kecamatan.kecamatan_name, core_member.member_identity, core_member.member_account_receivable_amount, core_member.member_account_receivable_status, core_member.member_mandatory_savings, acct_credits.credits_name, acct_credits.credits_fine, acct_credits_account.credits_account_temp_installment');
			$this->db->from($this->table);
			$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
			$this->db->join('core_city', 'core_member.city_id = core_city.city_id');
			$this->db->join('core_kecamatan', 'core_member.kecamatan_id = core_kecamatan.kecamatan_id');
			$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->where('acct_credits_account.credits_account_id',$id);
			$result = $this->db->get()->row_array();
			return $result;
		}
		
		public function getCoreOffice(){
			$this->db->select('core_office.office_id, core_office.office_name');
			$this->db->from('core_office');
			$this->db->where('data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			$this->db->limit(1);
			return $this->db->get()->row_array();
		}

		public function getPreferenceInventory(){
			$this->db->select('*');
			$this->db->from('preference_inventory');
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

		public function getAcctCreditsAccount_Last($created_on){
			$this->db->select('acct_credits_account.credits_account_id, acct_credits_account.credits_account_serial, acct_credits_account.member_id, core_member.member_name, acct_credits_account.credits_id, acct_credits.credits_name');
			$this->db->from('acct_credits_account');
			$this->db->join('core_member','acct_credits_account.member_id = core_member.member_id');
			$this->db->join('acct_credits','acct_credits_account.credits_id = acct_credits.credits_id');
			$this->db->where('acct_credits_account.created_on', $created_on);
			$this->db->limit(1);
			$this->db->order_by('acct_credits_account.created_on','DESC');
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getMemberAcctCreditsAccount($member_id){
			$this->db->select('acct_credits_account.credits_account_id');
			$this->db->from('acct_credits_account');
			$this->db->where('acct_credits_account.member_id', $member_id);
			$this->db->where('acct_credits_account.credits_account_status', 0);
			$this->db->where('acct_credits_account.credits_approve_status', 1);
			$result = $this->db->get()->result_array();
			return $result;
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

		public function getJournalVoucherToken($journal_voucher_token){
			$this->db->select('journal_voucher_token');
			$this->db->from('acct_journal_voucher');
			$this->db->where('journal_voucher_token', $journal_voucher_token);
			return $this->db->get();
		}

		public function insertAcctCreditsAgunan($data){
			if ($this->db->insert('acct_credits_agunan', $data)){
				return true;
			}else{
				return false;
			}
		}

		public function updateAcctCreditAccount($data){
			$this->db->where('credits_account_id', $data['credits_account_id']);
			if ($this->db->update('acct_credits_account', $data)){
				return true;
			}else{
				return false;
			}
		}
		
		public function updateApprove($data){
			$this->db->where('credits_account_id', $data['credits_account_id']);
			if ($this->db->update('acct_credits_account', $data)){
				return true;
			}else{
				return false;
			}
		}

		public function insertAcctCreditsAccountReschedule($data){
			if ($this->db->insert('acct_credits_account_reschedule', $data)){
				return true;
			}else{
				return false;
			}
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

		public function getReceivableAccountID($credits_id){
			$this->db->select('acct_credits.receivable_account_id');
			$this->db->from('acct_credits');
			$this->db->where('acct_credits.credits_id', $credits_id);
			$result = $this->db->get()->row_array();
			return $result['receivable_account_id'];
		}

		public function getIncomeAccountID($credits_id){
			$this->db->select('acct_credits.income_account_id');
			$this->db->from('acct_credits');
			$this->db->where('acct_credits.credits_id', $credits_id);
			$result = $this->db->get()->row_array();
			return $result['income_account_id'];
		}
		public function getAccountIdFromSavings($savings_id){
			$this->db->select('acct_savings.account_id');
			$this->db->from('acct_savings');
			$this->db->where('acct_savings.savings_id', $savings_id);
			$this->db->where('acct_savings.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['account_id'];
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

		public function getJournalVoucherItemToken($journal_voucher_item_token){
			$this->db->select('journal_voucher_item_token');
			$this->db->from('acct_journal_voucher_item');
			$this->db->where('journal_voucher_item_token', $journal_voucher_item_token);
			return $this->db->get();
		}

		function get_datatables($branch_id)
	    {
	        $this->_get_datatables_query($branch_id);
	        if($_POST['length'] != -1)
	        $this->db->limit($_POST['length'], $_POST['start']);
	        $query = $this->db->get();
	        return $query->result();
	    }
 
	    function count_filtered($branch_id)
	    {
	        $this->_get_datatables_query($branch_id);
	        $query = $this->db->get();
	        return $query->num_rows();
	    }
 
	    public function count_all($branch_id)
	    {
	        $this->db->from('acct_credits_account');
	        $this->db->join('core_member','acct_credits_account.member_id=core_member.member_id');
	        $this->db->where('acct_credits_account.credits_account_status', 0);
	        $this->db->where('acct_credits_account.credits_approve_status', 1);
	        if(!empty($branch_id)){
				$this->db->where('acct_credits_account.branch_id', $branch_id);
			}
			
	        return $this->db->count_all_results();
	    }

		private function _get_datatables_query($branch_id)
	    {
	        $this->db->from('acct_credits_account');
	        $this->db->join('core_member','acct_credits_account.member_id=core_member.member_id');
	        $this->db->where('acct_credits_account.credits_account_status', 0);
	        $this->db->where('acct_credits_account.credits_approve_status', 1);
	        if(!empty($branch_id)){
				$this->db->where('acct_credits_account.branch_id', $branch_id);
			}
	 
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

	    function get_datatables_master($start_date, $end_date, $credits_id, $branch_id)
	    {
	        $this->_get_datatables_query_master($start_date, $end_date, $credits_id, $branch_id);
	        if($_POST['length'] != -1)
	        $this->db->limit($_POST['length'], $_POST['start']);
	        $query = $this->db->get();
	        return $query->result();
	    }
 
	    function count_filtered_master($start_date, $end_date, $credits_id, $branch_id)
	    {
	        $this->_get_datatables_query_master($start_date, $end_date, $credits_id, $branch_id);
	        $query = $this->db->get();
	        return $query->num_rows();
	    }
 
	    public function count_all_master($start_date, $end_date, $credits_id, $branch_id)
	    {
	        $this->db->from('acct_credits_account');
			$this->db->join('core_member','acct_credits_account.member_id=core_member.member_id');
			$this->db->where('acct_credits_account.credits_account_date >=', $start_date);
			$this->db->where('acct_credits_account.credits_account_date <=', $end_date);
			if(!empty($credits_id)){
				$this->db->where('acct_credits_account.credits_id', $credits_id);
			}
			if(!empty($branch_id)){
				$this->db->where('acct_credits_account.branch_id', $branch_id);
			}
			
	        return $this->db->count_all_results();
	    }

		private function _get_datatables_query_master($start_date, $end_date, $credits_id, $branch_id)
	    {
	        $this->db->from('acct_credits_account');
	        $this->db->join('core_member','acct_credits_account.member_id = core_member.member_id');
	        $this->db->join('acct_credits','acct_credits_account.credits_id = acct_credits.credits_id');
	        $this->db->join('acct_source_fund','acct_credits_account.source_fund_id = acct_source_fund.source_fund_id');
	        $this->db->where('acct_credits_account.credits_account_date >=', $start_date);
			$this->db->where('acct_credits_account.credits_account_date <=', $end_date);
			$this->db->where('acct_credits_account.data_state', 0);
			if(!empty($credits_id)){
				$this->db->where('acct_credits_account.credits_id', $credits_id);
			}
			if(!empty($branch_id)){
				$this->db->where('acct_credits_account.branch_id', $branch_id);
			}
	 
	        $i = 0;
	     
	        foreach ($this->column_search as $item) // looping awal
	        {
	            if($_POST['search']['value']){
	                if($i===0){
	                    $this->db->group_start(); 
	                    $this->db->like($item, $_POST['search']['value']);
	                }else{
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

		public function truncateAcctCreditsImport(){
			$query = $this->db->truncate('acct_credits_import');
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function getAcctCreditImport(){
			$this->db->select('acct_credits_import.member_id, core_member.member_name, core_member.member_no');
			$this->db->from('acct_credits_import');
	        $this->db->join('core_member','acct_credits_import.member_id = core_member.member_id');
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
		
		public function insertAcctCreditsImport($data){
			$query = $this->db->insert('acct_credits_import',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
	}

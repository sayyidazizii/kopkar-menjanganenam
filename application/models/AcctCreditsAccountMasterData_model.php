<?php
defined('BASEPATH') or exit('No direct script access allowed');
class AcctCreditsAccountMasterData_model extends CI_Model
{
	var $table 			= "acct_credits_account";
	var $column_order 	= array(null, 'acct_credits_account.credits_account_serial', 'acct_savings_account.savings_account_no', 'core_member.member_name', 'core_member.member_address');
	var $column_search 	= array('core_member.member_name','acct_credits_account.credits_account_serial','core_member.member_no');
	var $order 			= array('acct_credits_account.savings_account_id' => 'asc');

	public function __construct()
	{
		parent::__construct();
		$this->CI = get_instance();
	}

	public function getCoreBranch()
	{
		$this->db->select('core_branch.branch_id, core_branch.branch_name');
		$this->db->from('core_branch');
		$this->db->where('core_branch.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getAcctCredits()
	{
		$this->db->select('acct_credits.credits_id, acct_credits.credits_name');
		$this->db->from('acct_credits');
		$this->db->where('acct_credits.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	private function _get_datatables_query($start_date, $end_date, $branch_id, $credits_id)
	{
		$this->db->select('core_member.member_no, core_member.member_name, acct_credits.credits_name, acct_credits_account.credits_account_id, acct_credits_account.credits_account_serial, acct_credits_account.credits_account_bank_account, acct_credits_account. credits_account_bank_name, acct_credits_account.credits_account_bank_owner, acct_credits_account.credits_account_period, acct_credits_account. credits_account_payment_to, acct_credits_account.credits_account_date, acct_credits_account.credits_account_due_date, acct_credits_account.credits_account_amount, acct_credits_account.credits_account_interest, acct_credits_account.credits_account_principal_amount, acct_credits_account.credits_account_interest_amount, acct_credits_account.credits_account_last_balance, acct_credits_account.credits_account_remark');
		$this->db->from('acct_credits_account');
		$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
		$this->db->join('core_member_working', 'acct_credits_account.member_id = core_member_working.member_id');
		$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
		$this->db->where('acct_credits_account.data_state', 0);
		$this->db->where('acct_credits_account.credits_account_date >=', $start_date);
		$this->db->where('acct_credits_account.credits_account_date <=', $end_date);
		if (!empty($branch_id)) {
			$this->db->where('acct_credits_account.branch_id', $branch_id);
		}
		if (!empty($credits_id)) {
			$this->db->where('acct_credits_account.credits_id', $credits_id);
		}

		$this->db->order_by('acct_credits_account.credits_account_serial', 'ASC');
		$i = 0;

		foreach ($this->column_search as $item) // looping awal
		{
			if ($_POST['search']['value']) // jika datatable mengirimkan pencarian dengan metode POST
			{
				if ($i === 0) // looping awal
				{
					$this->db->group_start();
					$this->db->like($item, $_POST['search']['value']);
				} else {
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if (count($this->column_search) - 1 == $i)
					$this->db->group_end();
			}
			$i++;
		}

		if (isset($_POST['order'])) {
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function getCreditsAccountAgunan($credits_account_id){
		$this->db->select('credits_agunan_id, credits_agunan_penerimaan_description, credits_agunan_deposito_account_no, credits_agunan_other_description');
		$this->db->from('acct_credits_agunan');
		$this->db->where('credits_account_id', $credits_account_id);
		$this->db->where('data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	function getFirstPaymentDate($credits_account_id){
		$this->db->select('credits_payment_date');
		$this->db->from('acct_credits_payment');
		$this->db->where('credits_account_id', $credits_account_id);
		$this->db->where('data_state', 0);
		$this->db->order_by('credits_payment_id', 'ASC');
		$result = $this->db->get()->row_array();
		return $result['credits_payment_date'];
	}

	function get_datatables($start_date, $end_date, $branch_id, $credits_id)
	{
		$this->_get_datatables_query($start_date, $end_date, $branch_id, $credits_id);
		if ($_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered($start_date, $end_date, $branch_id, $credits_id)
	{
		$this->_get_datatables_query($start_date, $end_date, $branch_id, $credits_id);
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all($start_date, $end_date, $branch_id, $credits_id)
	{
		$this->db->from($this->table);
		$this->db->where('acct_credits_account.credits_account_date >=', $start_date);
		$this->db->where('acct_credits_account.credits_account_date <=', $end_date);
		if (!empty($branch_id)) {
			$this->db->where('acct_credits_account.branch_id', $branch_id);
		}
		if (!empty($credits_id)) {
			$this->db->where('acct_credits_account.credits_id', $credits_id);
		}
		return $this->db->count_all_results();
	}

	public function getAcctSavingsAccountNo($savings_account_id)
	{
		$this->db->select('savings_account_no');
		$this->db->from('acct_savings_account');
		$this->db->where('savings_account_id', $savings_account_id);
		$result = $this->db->get()->row_array();
		return $result['savings_account_no'];
	}

	public function getStoreName($store_id)
	{
		$this->db->select('store_name');
		$this->db->from('core_store');
		$this->db->where('store_id', $store_id);
		$result = $this->db->get()->row_array();
		return $result['store_name'];
	}

	public function getExport($branch_id)
	{
		$this->db->select('acct_credits_account.credits_account_serial, acct_credits_account.savings_account_id, acct_credits_account.credits_account_special, core_member_working.member_working_type, core_member_working.member_company_name, acct_credits_account.member_id, core_member.member_name, core_member.member_address, core_member.member_gender, core_member.member_date_of_birth, core_member.member_job, core_member.member_identity, core_member.member_identity_no, acct_credits_account.credits_account_period, core_member.member_phone ,acct_credits_account.credits_account_date, acct_credits_account.credits_account_due_date, acct_credits_account.credits_account_principal_amount, acct_credits_account.credits_account_interest_amount, acct_credits_account.credits_account_amount, acct_credits_account.credits_account_interest, acct_credits_account.credits_account_last_balance, acct_credits_account.credits_id, acct_credits.credits_name, core_member.member_no, acct_credits_account.credits_account_payment_to, acct_credits_account.credits_account_amount_received');
		$this->db->from('acct_credits_account');
		$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
		$this->db->join('core_member_working', 'acct_credits_account.member_id = core_member_working.member_id');
		$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
		$this->db->join('core_store', 'core_store.store_id = acct_credits_account.store_id');
		$this->db->where('acct_credits_account.data_state', 0);
		if (!empty($branch_id)) {
			$this->db->where('acct_credits_account.branch_id', $branch_id);
		}
		$this->db->order_by('acct_credits_account.credits_account_serial', 'ASC');
		return $this->db->get();
	}
}

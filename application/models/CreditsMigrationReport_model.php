<?php
defined('BASEPATH') or exit('No direct script access allowed');
class CreditsMigrationReport_model extends CI_Model
{
	var $table = "acct_credits_account";

	public function __construct()
	{
		parent::__construct();
		$this->CI = get_instance();
	}

	public function getCreditsMigrationReport()
	{
		$this->db->select('acct_credits_account.*, core_member.member_no, core_member.member_name, acct_credits.credits_name');
		$this->db->from('acct_credits_account');
		$this->db->join('core_member', 'acct_credits_account.member_id = core_member.member_id');
		$this->db->join('acct_credits', 'acct_credits_account.credits_id = acct_credits.credits_id');
		$this->db->where('acct_credits_account.data_state ', 0);
		$this->db->where('acct_credits_account.credits_approve_status', 1);
		$this->db->where('acct_credits_account.credits_account_last_balance >', 0);
		$this->db->order_by('acct_credits_account.credits_account_date', 'ASC');
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getPreferenceCompany(){
		$this->db->select('*');
		$this->db->from('preference_company');
		$result = $this->db->get()->row_array();
		return $result;
	}

	public function getUsername($user_id){
		$this->db->select('username');
		$this->db->from('system_user');
		$this->db->where('user_id', $user_id);
		$result = $this->db->get()->row_array();
		return $result['username'];
	}
}

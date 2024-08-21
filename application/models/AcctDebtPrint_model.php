<?php
defined('BASEPATH') or exit('No direct script access allowed');
class AcctDebtPrint_model extends CI_Model
{
	var $table = "acct_debt_category";

	public function __construct()
	{
		parent::__construct();
		$this->CI = get_instance();
		$this->dbminimarket = $this->load->database('minimarket', true);
	}

	public function getAcctDebt($sesi)
	{
		$this->db->select('acct_debt.debt_date, acct_debt.debt_amount, acct_debt.debt_remark, acct_debt.debt_no, core_member.member_name, core_member.member_no, core_division.division_name, core_part.part_name, acct_debt_category.debt_category_name, acct_debt_category.debt_category_id');
		$this->db->from('acct_debt');
		$this->db->join('core_member', 'core_member.member_id = acct_debt.member_id', 'left');
		$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id', 'left');
		$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id', 'left');
		$this->db->join('core_part', 'core_part.part_id = core_member_working.part_id', 'left');
		$this->db->join('acct_debt_category', 'acct_debt_category.debt_category_id = acct_debt.debt_category_id', 'left');
		$this->db->where('debt_date >=', $sesi['start_date']);
		$this->db->where('debt_date <=', $sesi['end_date']);
		if ($sesi['debt_category_id'] && $sesi['debt_category_id'] != '') {
			$this->db->where('acct_debt.debt_category_id', $sesi['debt_category_id']);
		}
		if ($sesi['part_id'] && $sesi['part_id'] != '') {
			$this->db->where('core_member_working.part_id', $sesi['part_id']);
		}
		if ($sesi['division_id'] && $sesi['division_id'] != '') {
			$this->db->where('core_member_working.division_id', $sesi['division_id']);
		}
		$this->db->where('acct_debt.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getAcctDebtMember($sesi)
	{
		$this->db->select('acct_savings_member_detail.transaction_date, acct_savings_member_detail.principal_savings_amount, acct_savings_member_detail.mandatory_savings_amount, core_member.member_name, core_member.member_no, core_division.division_name, core_part.part_name, core_division.division_id, core_part.part_id');
		$this->db->from('acct_savings_member_detail');
		$this->db->join('core_member', 'core_member.member_id = acct_savings_member_detail.member_id');
		$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id');
		$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id');
		$this->db->join('core_part', 'core_part.part_id = core_member_working.part_id');
		$this->db->where('transaction_date >=', $sesi['start_date']);
		$this->db->where('transaction_date <=', $sesi['end_date']);
		if ($sesi['part_id'] && $sesi['part_id'] != '') {
			$this->db->where('core_member_working.part_id', $sesi['part_id']);
		}
		if ($sesi['division_id'] && $sesi['division_id'] != '') {
			$this->db->where('core_member_working.division_id', $sesi['division_id']);
		}
		$this->db->where('acct_savings_member_detail.salary_status', 1);
		$this->db->where('acct_savings_member_detail.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getAcctDebtSavings($sesi)
	{
		$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_account.savings_id, acct_savings_account.savings_account_no, core_member.member_name, core_member.member_no, core_division.division_name, core_part.part_name, acct_savings.savings_name');
		$this->db->from('acct_savings_cash_mutation');
		$this->db->join('acct_savings_account', 'acct_savings_account.savings_account_id = acct_savings_cash_mutation.savings_account_id');
		$this->db->join('acct_savings', 'acct_savings.savings_id = acct_savings_account.savings_id');
		$this->db->join('core_member', 'core_member.member_id = acct_savings_account.member_id');
		$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id');
		$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id');
		$this->db->join('core_part', 'core_part.part_id = core_member_working.part_id');
		$this->db->where('savings_cash_mutation_date >=', $sesi['start_date']);
		$this->db->where('savings_cash_mutation_date <=', $sesi['end_date']);
		if ($sesi['part_id'] && $sesi['part_id'] != '') {
			$this->db->where('core_member_working.part_id', $sesi['part_id']);
		}
		if ($sesi['division_id'] && $sesi['division_id'] != '') {
			$this->db->where('core_member_working.division_id', $sesi['division_id']);
		}
		$this->db->where('acct_savings_cash_mutation.salary_payment_status', 1);
		$this->db->where('acct_savings_cash_mutation.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getAcctDebtCredits($sesi)
	{
		$this->db->from('acct_credits_payment');
		$this->db->join('acct_credits_account', 'acct_credits_account.credits_account_id = acct_credits_payment.credits_account_id', 'left');
		$this->db->join('acct_credits', 'acct_credits.credits_id = acct_credits_account.credits_id', 'left');
		$this->db->join('core_member', 'core_member.member_id = acct_credits_account.member_id', 'left');
		$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id', 'left');
		$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id', 'left');
		$this->db->join('core_part', 'core_part.part_id = core_member_working.part_id', 'left');
		$this->db->where('credits_payment_date >=', $sesi['start_date']);
		$this->db->where('credits_payment_date <=', $sesi['end_date']);
		if ($sesi['part_id'] && $sesi['part_id'] != '') {
			$this->db->where('core_member_working.part_id', $sesi['part_id']);
		}
		if ($sesi['division_id'] && $sesi['division_id'] != '') {
			$this->db->where('core_member_working.division_id', $sesi['division_id']);
		}
		$this->db->where('acct_credits_payment.salary_payment_status', 1);
		$this->db->where('acct_credits_payment.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getAcctDebtStore($sesi)
	{
		$this->dbminimarket->select('customer_id, sales_invoice_no, sales_invoice_date, total_amount');
		$this->dbminimarket->from('sales_invoice');
		$this->dbminimarket->where('sales_invoice_date >=', $sesi['start_date']);
		$this->dbminimarket->where('sales_invoice_date <=', $sesi['end_date']);
		$this->dbminimarket->where('sales_payment_method', 2);
		$this->dbminimarket->where('data_state', 0);
		$result = $this->dbminimarket->get()->result_array();
		return $result;
	}

	public function getPreferenceCompany()
	{
		$this->db->select('*');
		$this->db->from('preference_company');
		$result = $this->db->get()->row_array();
		return $result;
	}

	public function getCorePart()
	{
		$this->db->select('*');
		$this->db->from('core_part');
		$this->db->where('data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getCoreDivision()
	{
		$this->db->select('division_id, CONCAT(division_code, " - ", division_name) as division_name');
		$this->db->from('core_division');
		$this->db->where('data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getAcctDebtCategory()
	{
		$this->db->select('*');
		$this->db->from('acct_debt_category');
		$this->db->where('data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getAcctSavings()
	{
		$this->db->select('*');
		$this->db->from('acct_savings');
		$this->db->where('data_state', 0);
		$this->db->where('savings_status', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getAcctCredits()
	{
		$this->db->select('*');
		$this->db->from('acct_credits');
		$this->db->where('data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getAcctDebtCategoryName($debt_category_id)
	{
		$this->db->select('debt_category_name');
		$this->db->from('acct_debt_category');
		$this->db->where('debt_category_id', $debt_category_id);
		$result = $this->db->get()->row_array();
		return $result['debt_category_name'];
	}

	public function getCoreMemberDetail($member_id)
	{
		$this->db->select('core_member.member_no, core_member.member_nik, core_member.member_name, core_division.division_name, core_part.part_name, core_member_working.member_company_specialities, core_member_working.part_id, core_member_working.division_id');
		$this->db->from('core_member');
		$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id');
		$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id');
		$this->db->join('core_part', 'core_part.part_id = core_member_working.part_id');
		$this->db->where('core_member.member_id', $member_id);
		$this->db->where('core_member.data_state', 0);
		$result = $this->db->get()->row_array();
		return $result;
	}

	public function getCoreMember($sesi)
	{
		$this->db->select('core_member.member_id, core_member.member_nik, core_member.member_name, core_member.member_no, core_division.division_name, core_part.part_name, core_member_working.member_company_specialities');
		$this->db->from('core_member');
		$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id');
		$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id');
		$this->db->join('core_part', 'core_part.part_id = core_member_working.part_id');
		if ($sesi['part_id'] && $sesi['part_id'] != '') {
			$this->db->where('core_member_working.part_id', $sesi['part_id']);
		}
		if ($sesi['division_id'] && $sesi['division_id'] != '') {
			$this->db->where('core_member_working.division_id', $sesi['division_id']);
		}
		$this->db->where('core_member.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getMemberDebtCategory($sesi, $member_id)
	{
		$this->db->select('acct_debt.debt_date, acct_debt.debt_amount, acct_debt.debt_remark, acct_debt.debt_no, acct_debt_category.debt_category_name, acct_debt_category.debt_category_code');
		$this->db->from('acct_debt');
		$this->db->join('acct_debt_category', 'acct_debt_category.debt_category_id = acct_debt.debt_category_id');
		$this->db->where('acct_debt.debt_date >=', $sesi['start_date']);
		$this->db->where('acct_debt.debt_date <=', $sesi['end_date']);
		if ($sesi['debt_category_id'] && $sesi['debt_category_id'] != '') {
			$this->db->where('acct_debt.debt_category_id', $sesi['debt_category_id']);
		}
		$this->db->where('acct_debt.member_id', $member_id);
		$this->db->where('acct_debt.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getMemberDebtCategoryTemp($sesi, $member_id)
	{
		$this->db->select('*');
		$this->db->from('acct_debt_temporary');
		$this->db->join('acct_debt_category', 'acct_debt_category.debt_category_id = acct_debt_temporary.debt_category_id');
		$this->db->where('acct_debt_temporary.debt_date >=', $sesi['start_date']);
		$this->db->where('acct_debt_temporary.debt_date <=', $sesi['end_date']);
		if ($sesi['debt_category_id'] && $sesi['debt_category_id'] != '') {
			$this->db->where('acct_debt_temporary.debt_category_id', $sesi['debt_category_id']);
		}
		$this->db->where('acct_debt_temporary.member_id', $member_id);
		$this->db->where('acct_debt_temporary.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	//sukarela 
	public function getMemberDebtSavings($sesi, $member_id)
	{
		$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_account.savings_account_no');
		$this->db->from('acct_savings_cash_mutation');
		$this->db->join('acct_savings_account', 'acct_savings_account.savings_account_id = acct_savings_cash_mutation.savings_account_id');
		$this->db->where('savings_cash_mutation_date >=', $sesi['start_date']);
		$this->db->where('savings_cash_mutation_date <=', $sesi['end_date']);
		$this->db->where('acct_savings_cash_mutation.member_id', $member_id);
		$this->db->where('acct_savings_cash_mutation.savings_id', 34);
		$this->db->where('acct_savings_cash_mutation.salary_payment_status', 1);
		$this->db->where('acct_savings_cash_mutation.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	//sukarela temp
	public function getMemberDebtSavingsTemp($sesi, $member_id)
	{
		$this->db->select('acct_savings_cash_mutation_temp.savings_cash_mutation_amount, acct_savings_cash_mutation_temp.savings_cash_mutation_date, acct_savings_account.savings_account_no');
		$this->db->from('acct_savings_cash_mutation_temp');
		$this->db->join('acct_savings_account', 'acct_savings_account.savings_account_id = acct_savings_cash_mutation_temp.savings_account_id');
		$this->db->where('savings_cash_mutation_date >=', $sesi['start_date']);
		$this->db->where('savings_cash_mutation_date <=', $sesi['end_date']);
		$this->db->where('acct_savings_cash_mutation_temp.member_id', $member_id);
		$this->db->where('acct_savings_cash_mutation_temp.savings_id', 34);
		$this->db->where('acct_savings_cash_mutation_temp.salary_payment_status', 1);
		$this->db->where('acct_savings_cash_mutation_temp.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getMemberDebtSavingsSicantik($sesi, $member_id)
	{
		$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_account.savings_account_no');
		$this->db->from('acct_savings_cash_mutation');
		$this->db->join('acct_savings_account', 'acct_savings_account.savings_account_id = acct_savings_cash_mutation.savings_account_id');
		$this->db->where('savings_cash_mutation_date >=', $sesi['start_date']);
		$this->db->where('savings_cash_mutation_date <=', $sesi['end_date']);
		$this->db->where('acct_savings_cash_mutation.member_id', $member_id);
		$this->db->where('acct_savings_cash_mutation.savings_id', 35);
		$this->db->where('acct_savings_cash_mutation.salary_payment_status', 1);
		$this->db->where('acct_savings_cash_mutation.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getMemberDebtCredits($sesi, $member_id)
	{
		$this->db->select('acct_credits_payment.credits_payment_amount, acct_credits_payment.credits_payment_date, acct_credits_account.credits_account_serial, acct_credits_account.credits_id, acct_credits.credits_name');
		$this->db->from('acct_credits_payment');
		$this->db->join('acct_credits_account', 'acct_credits_account.credits_account_id = acct_credits_payment.credits_account_id');
		$this->db->join('acct_credits', 'acct_credits.credits_id = acct_credits_account.credits_id');
		$this->db->where('credits_payment_date >=', $sesi['start_date']);
		$this->db->where('credits_payment_date <=', $sesi['end_date']);
		$this->db->where('acct_credits_payment.member_id', $member_id);
		$this->db->where('acct_credits_payment.salary_payment_status', 1);
		$this->db->where('acct_credits_payment.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getMemberDebtCreditsTemp($sesi, $member_id)
	{
		$this->db->select('acct_credits_payment_temp.credits_payment_amount, acct_credits_payment_temp.credits_payment_date, acct_credits_account.credits_account_serial, acct_credits_account.credits_id, acct_credits.credits_name');
		$this->db->from('acct_credits_payment_temp');
		$this->db->join('acct_credits_account', 'acct_credits_account.credits_account_id = acct_credits_payment_temp.credits_account_id');
		$this->db->join('acct_credits', 'acct_credits.credits_id = acct_credits_account.credits_id');
		$this->db->where('credits_payment_date >=', $sesi['start_date']);
		$this->db->where('credits_payment_date <=', $sesi['end_date']);
		$this->db->where('acct_credits_payment_temp.member_id', $member_id);
		$this->db->where('acct_credits_payment_temp.salary_payment_status', 1);
		$this->db->where('acct_credits_payment_temp.data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getMemberDebtStore($sesi, $member_id)
	{
		$this->dbminimarket->select('customer_id, sales_invoice_no, sales_invoice_date, total_amount');
		$this->dbminimarket->from('sales_invoice');
		$this->dbminimarket->where('sales_invoice_date >=', $sesi['start_date']);
		$this->dbminimarket->where('sales_invoice_date <=', $sesi['end_date']);
		$this->dbminimarket->where('customer_id', $member_id);
		$this->dbminimarket->where('sales_payment_method', 2);
		$this->dbminimarket->where('data_state', 0);
		$result = $this->dbminimarket->get()->result_array();
		return $result;
	}

	public function getMemberDebtMemberSavings($sesi, $member_id)
	{
		$this->db->select('principal_savings_amount, mandatory_savings_amount');
		$this->db->from('acct_savings_member_detail');
		$this->db->where('transaction_date >=', $sesi['start_date']);
		$this->db->where('transaction_date <=', $sesi['end_date']);
		$this->db->where('member_id', $member_id);
		$this->db->where('salary_status', 1);
		$this->db->where('data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getMemberDebtMemberSavingsTemp($sesi, $member_id)
	{
		$this->db->select('principal_savings_amount, mandatory_savings_amount');
		$this->db->from('acct_savings_member_detail_temp');
		$this->db->where('transaction_date >=', $sesi['start_date']);
		$this->db->where('transaction_date <=', $sesi['end_date']);
		$this->db->where('member_id', $member_id);
		$this->db->where('salary_status', 1);
		$this->db->where('data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getMemberDebtPrincipal($sesi, $member_id)
	{
		$this->db->select('transaction_date, principal_savings_amount');
		$this->db->from('acct_savings_member_detail');
		$this->db->where('transaction_date >=', $sesi['start_date']);
		$this->db->where('transaction_date <=', $sesi['end_date']);
		$this->db->where('member_id', $member_id);
		$this->db->where('principal_savings_amount !=', 0);
		$this->db->where('salary_status', 1);
		$this->db->where('data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}

	public function getMemberDebtMandatory($sesi, $member_id)
	{
		$this->db->select('transaction_date, mandatory_savings_amount');
		$this->db->from('acct_savings_member_detail');
		$this->db->where('transaction_date >=', $sesi['start_date']);
		$this->db->where('transaction_date <=', $sesi['end_date']);
		$this->db->where('member_id', $member_id);
		$this->db->where('mandatory_savings_amount !=', 0);
		$this->db->where('salary_status', 1);
		$this->db->where('data_state', 0);
		$result = $this->db->get()->result_array();
		return $result;
	}
}
?>
<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctDebtMemberPrint_model extends CI_Model {
		var $table = "acct_debt_category";

		public function __construct(){
			parent::__construct();
			$this->CI 			= get_instance();
			$this->dbminimarket = $this->load->database('minimarket', true);
		} 

		public function getAcctDebt($sesi){
			$this->db->select('acct_debt.debt_date, acct_debt.debt_amount, acct_debt.debt_remark, acct_debt.debt_no, core_member.member_name, core_member.member_no, core_division.division_name, acct_debt_category.debt_category_name');
			$this->db->from('acct_debt');
			$this->db->join('core_member', 'core_member.member_id = acct_debt.member_id');
			$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id');
			$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id');
			$this->db->join('acct_debt_category', 'acct_debt_category.debt_category_id = acct_debt.debt_category_id');
			$this->db->where('debt_date >=', $sesi['start_date']);
			$this->db->where('debt_date <=', $sesi['end_date']);
			if($sesi['debt_category_id'] && $sesi['debt_category_id'] != ''){
				$this->db->where('acct_debt.debt_category_id', $sesi['debt_category_id']);
			}
			$this->db->where('acct_debt.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctDebtSavings($sesi){
			$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_account.savings_account_no, core_member.member_name, core_member.member_no, core_division.division_name');
			$this->db->from('acct_savings_cash_mutation');
			$this->db->join('acct_savings_account', 'acct_savings_account.savings_account_id = acct_savings_cash_mutation.savings_account_id');
			$this->db->join('core_member', 'core_member.member_id = acct_savings_account.member_id');
			$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id');
			$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id');
			$this->db->where('savings_cash_mutation_date >=', $sesi['start_date']);
			$this->db->where('savings_cash_mutation_date <=', $sesi['end_date']);
			$this->db->where('acct_savings_cash_mutation.salary_payment_status', 1);
			$this->db->where('acct_savings_cash_mutation.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctDebtCredits($sesi){
			$this->db->select('acct_credits_payment.credits_payment_amount, acct_credits_payment. credits_payment_date, acct_credits_account.credits_account_serial, core_member.member_name, core_member.member_no, core_division.division_name');
			$this->db->from('acct_credits_payment');
			$this->db->join('acct_credits_account', 'acct_credits_account.credits_account_id = acct_credits_payment.credits_account_id');
			$this->db->join('core_member', 'core_member.member_id = acct_credits_account.member_id');
			$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id');
			$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id');
			$this->db->where('credits_payment_date >=', $sesi['start_date']);
			$this->db->where('credits_payment_date <=', $sesi['end_date']);
			$this->db->where('acct_credits_payment.salary_payment_status', 1);
			$this->db->where('acct_credits_payment.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getCoreMemberByDivision($division_id){
			$this->db->select('core_member.*, core_branch.branch_name, core_province.province_name, core_city.city_name, core_kecamatan.kecamatan_name, core_member_working.member_company_job_title, core_member_working.member_company_name, core_member_working.division_id, core_member_working.part_id');
			$this->db->from('core_member');
			$this->db->join('core_member_working','core_member.member_id = core_member_working.member_id');
			$this->db->join('core_province', 'core_member.province_id = core_province.province_id');
			$this->db->join('core_city', 'core_member.city_id = core_city.city_id');
			$this->db->join('core_kecamatan', 'core_member.kecamatan_id = core_kecamatan.kecamatan_id');
			$this->db->join('core_branch', 'core_member.branch_id = core_branch.branch_id');
			$this->db->where('core_member.data_state', 0);
			$this->db->where('core_member_working.division_id', $division_id);
			$this->db->order_by('core_member_working.part_id', 'ASC');
			$this->db->order_by('core_member.member_no', 'ASC');
			return $this->db->get()->result_array();
		}

		public function getAcctDebtStore($sesi){
			$this->dbminimarket->select('customer_id, sales_invoice_no, sales_invoice_date, total_amount');
			$this->dbminimarket->from('sales_invoice');
			$this->dbminimarket->where('sales_invoice_date >=', $sesi['start_date']);
			$this->dbminimarket->where('sales_invoice_date <=', $sesi['end_date']);
			$this->dbminimarket->where('sales_payment_method', 2);
			$this->dbminimarket->where('data_state', 0);
			$result = $this->dbminimarket->get()->result_array();
			return $result;
		}

		public function getCoreDivision(){
			$this->db->select('division_name, division_id');
			$this->db->from('core_division');
			$this->db->where('data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getAcctDebtCategory(){
			$this->db->select('*');
			$this->db->from('acct_debt_category');
			$this->db->where('data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctDebtCategoryName($debt_category_id){
			$this->db->select('debt_category_name');
			$this->db->from('acct_debt_category');
			$this->db->where('debt_category_id', $debt_category_id);
			$result = $this->db->get()->row_array();
			return $result['debt_category_name'];
		}

		public function getCoreMemberDetail($member_id){
			$this->db->select('core_member.member_no, core_member.member_name, core_division.division_name');
			$this->db->from('core_member');
			$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id');
			$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id');
			$this->db->where('core_member.member_id', $member_id);
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result;
		}

		public function getCoreMember(){
			$this->db->select('core_member.member_id, core_member.member_name, core_member.member_no, core_division.division_name');
			$this->db->from('core_member');
			$this->db->join('core_member_working', 'core_member_working.member_id = core_member.member_id');
			$this->db->join('core_division', 'core_division.division_id = core_member_working.division_id');
			$this->db->where('core_member.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getMemberDebtCategory($sesi, $member_id){
			$this->db->select('acct_debt.debt_date, acct_debt.debt_amount, acct_debt.debt_remark, acct_debt.debt_no, acct_debt_category.debt_category_name');
			$this->db->from('acct_debt');
			$this->db->join('acct_debt_category', 'acct_debt_category.debt_category_id = acct_debt.debt_category_id');
			$this->db->where('acct_debt.debt_date >=', $sesi['start_date']);
			$this->db->where('acct_debt.debt_date <=', $sesi['end_date']);
			$this->db->where('acct_debt.member_id', $member_id);
			$this->db->where('acct_debt.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getMemberDebtSavings($sesi, $member_id){
			$this->db->select('acct_savings_cash_mutation.savings_cash_mutation_amount, acct_savings_cash_mutation.savings_cash_mutation_date, acct_savings_account.savings_account_no');
			$this->db->from('acct_savings_cash_mutation');
			$this->db->join('acct_savings_account', 'acct_savings_account.savings_account_id = acct_savings_cash_mutation.savings_account_id');
			$this->db->where('savings_cash_mutation_date >=', $sesi['start_date']);
			$this->db->where('savings_cash_mutation_date <=', $sesi['end_date']);
			$this->db->where('acct_savings_cash_mutation.member_id', $member_id);
			$this->db->where('acct_savings_cash_mutation.salary_payment_status', 1);
			$this->db->where('acct_savings_cash_mutation.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getmemberDebtCredits($sesi, $member_id){
			$this->db->select('acct_credits_payment.credits_payment_amount, acct_credits_payment. credits_payment_date, acct_credits_account.credits_account_serial');
			$this->db->from('acct_credits_payment');
			$this->db->join('acct_credits_account', 'acct_credits_account.credits_account_id = acct_credits_payment.credits_account_id');
			$this->db->where('credits_payment_date >=', $sesi['start_date']);
			$this->db->where('credits_payment_date <=', $sesi['end_date']);
			$this->db->where('acct_credits_payment.member_id', $member_id);
			$this->db->where('acct_credits_payment.salary_payment_status', 1);
			$this->db->where('acct_credits_payment.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getMemberDebtStore($sesi, $member_id){
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
	}
?>
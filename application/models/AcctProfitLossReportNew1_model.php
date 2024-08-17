<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class AcctProfitLossReportNew1_model extends CI_Model {
		var $table = "acct_mutation";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();

			$this->CI->load->model('Connection_model');
			$this->CI->load->dbforge();

		} 

		public function getPreferenceCompany(){
			$this->db->select('preference_company.*');
			$this->db->from('preference_company');
			return $this->db->get()->row_array();
		}

		public function getCoreBranch(){
			$this->db->select('core_branch.branch_id, core_branch.branch_name');
			$this->db->from('core_branch');
			$this->db->where('core_branch.data_state', 0);
			return $this->db->get()->result_array();
		}

		public function getBranchName($branch_id){
			$this->db->select('branch_name');
			$this->db->from('core_branch');
			$this->db->where('branch_id', $branch_id);
			$result = $this->db->get()->row_array();
			return $result['branch_name'];
		}

		public function getBranchCity($branch_id){
			$this->db->select('branch_city');
			$this->db->from('core_branch');
			$this->db->where('branch_id', $branch_id);
			$result = $this->db->get()->row_array();
			return $result['branch_city'];
		}

		public function getBranchManager($branch_id){
			$this->db->select('branch_manager');
			$this->db->from('core_branch');
			$this->db->where('branch_id', $branch_id);
			$result = $this->db->get()->row_array();
			return $result['branch_manager'];
		}

		public function getAcctProfitLossReportNew1_Top($format_id){
			$this->db->select('acct_profit_loss_report.profit_loss_report_id, acct_profit_loss_report.report_no, acct_profit_loss_report.account_id, acct_profit_loss_report.account_code, acct_profit_loss_report.account_name, acct_profit_loss_report.report_formula, acct_profit_loss_report.report_operator, acct_profit_loss_report.report_type, acct_profit_loss_report.report_tab, acct_profit_loss_report.report_bold, acct_profit_loss_report.category_type');
			$this->db->from('acct_profit_loss_report');
			$this->db->where('acct_profit_loss_report.account_name <> " " ');
			$this->db->where('acct_profit_loss_report.account_name <> "" ');
			$this->db->where('acct_profit_loss_report.account_type_id', 2);
			$this->db->order_by('acct_profit_loss_report.report_no', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctProfitLossReportNew1_Bottom($format_id){
			$this->db->select('acct_profit_loss_report.profit_loss_report_id, acct_profit_loss_report.report_no, acct_profit_loss_report.account_id, acct_profit_loss_report.account_code, acct_profit_loss_report.account_name, acct_profit_loss_report.report_formula, acct_profit_loss_report.report_operator, acct_profit_loss_report.report_type, acct_profit_loss_report.report_tab, acct_profit_loss_report.report_bold, acct_profit_loss_report.category_type');
			$this->db->from('acct_profit_loss_report');
			$this->db->where('acct_profit_loss_report.account_name <> " " ');
			$this->db->where('acct_profit_loss_report.account_name <> "" ');
			$this->db->where('acct_profit_loss_report.account_type_id', 3);
			$this->db->order_by('acct_profit_loss_report.report_no', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAcctAccount_List($length, $account_code){
			$this->db->select('acct_account.account_id');
			$this->db->from('acct_account');
			$this->db->where('data_state', 0);
			$this->db->where('LEFT(account_code,'.$length.')', $account_code);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getAccountAmount($account_id, $month_start, $month_end, $year, $profit_loss_report_type, $branch_id){
			if ($profit_loss_report_type == 1){
				$this->db->select('SUM(acct_account_mutation.last_balance) AS last_balance');
				$this->db->from('acct_account_mutation');
				$this->db->where('acct_account_mutation.account_id', $account_id);
				$this->db->where('acct_account_mutation.branch_id', $branch_id);
				$this->db->where('acct_account_mutation.month_period >=', $month_start);
				$this->db->where('acct_account_mutation.month_period <=', $month_end);
				$this->db->where('acct_account_mutation.year_period', $year);
				$result = $this->db->get()->row_array();
				return $result['last_balance'];
			} else if ($profit_loss_report_type == 2){
				$this->db->select('SUM(acct_account_mutation.last_balance) AS last_balance');
				$this->db->from('acct_account_mutation');
				$this->db->where('acct_account_mutation.account_id', $account_id);
				$this->db->where('acct_account_mutation.branch_id', $branch_id);
				$this->db->where('acct_account_mutation.year_period', $year);
				$result = $this->db->get()->row_array();
				return $result['last_balance'];
			}
			
		}

		public function getLastBalance($account_id){
			$this->db->select('acct_account_balance_detail.last_balance');
			$this->db->from('acct_account_balance_detail');
			$this->db->where('acct_account_balance_detail.account_id', $account_id);
			$this->db->limit(1);
			$this->db->order_by('acct_account_balance_detail.account_balance_detail_id', 'DESC');
			$result = $this->db->get()->row_array();
			return $result['last_balance'];
		}

		public function getAccountListParent($length, $account_id){
			$this->db->select('account_id, account_code, account_name');
			$this->db->from('acct_account');
			$this->db->where('LEFT(account_code,'.$length.')', $account_id);
			$this->db->where('account_status', 1);
			$this->db->where('data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getSaldoAccountChild($account_id, $month, $year){
			$this->db->select('acct_account_balance_detail.opening_balance');
			$this->db->from('acct_account_balance_detail');
			$this->db->join('acct_account','acct_account_balance_detail.account_id = acct_account.account_id');
			$this->db->where('acct_account.parent_account_id', $account_id);
			$this->db->where('MONTH(acct_account_balance_detail.transaction_date)', $month);
			$this->db->where('YEAR(acct_account_balance_detail.transaction_date)', $year);
			$this->db->limit(1);
			$this->db->order_by('acct_account_balance_detail.transaction_date', 'ASC');
			$result = $this->db->get()->row_array();
			return $result['opening_balance'];
		}

		public function getSaldoAccountParent($account_id, $month, $year){
			$this->db->select('acct_account_balance_detail.opening_balance');
			$this->db->from('acct_account_balance_detail');
			$this->db->join('acct_account','acct_account_balance_detail.account_id = acct_account.account_id');
			$this->db->where('acct_account.account_id', $account_id);
			$this->db->where('MONTH(acct_account_balance_detail.transaction_date)', $month);
			$this->db->where('YEAR(acct_account_balance_detail.transaction_date)', $year);
			$this->db->limit(1);
			$this->db->order_by('acct_account_balance_detail.transaction_date', 'ASC');
			$result = $this->db->get()->row_array();
			return $result['opening_balance'];
		}

		public function getAccountChildAmount($account_id, $month, $year){
			$this->db->select('SUM(acct_account_balance_detail.account_in) AS account_in_amount, SUM(acct_account_balance_detail.account_out) AS account_out_amount');
			$this->db->from('acct_account_balance_detail');
			$this->db->join('acct_account','acct_account_balance_detail.account_id = acct_account.account_id');
			$this->db->where('acct_account.parent_account_id', $account_id);
			$this->db->where('MONTH(acct_account_balance_detail.transaction_date)', $month);
			$this->db->where('YEAR(acct_account_balance_detail.transaction_date)', $year);
			$result = $this->db->get()->row_array();
			return $result;
		}



//========update balance
		public function getAllAccountIds($month, $year) {
			$this->db->distinct();
			$this->db->select('account_id');
			$this->db->from('acct_account_mutation');
			$this->db->where('month_period', $month);
			$this->db->where('year_period', $year);
			$result = $this->db->get()->result_array();
			return array_column($result, 'account_id');
		}

		// Ambil last_balance bulan sebelumnya
		public function getLastBalanceFromPreviousMonth($account_id, $month, $year) {
			$this->db->select('last_balance');
			$this->db->from('acct_account_mutation');
			$this->db->where('account_id', $account_id);
			$this->db->where('month_period', $month);
			$this->db->where('year_period', $year);
			$this->db->limit(1);
			$result = $this->db->get()->row_array();
			
			return $result['last_balance'];
		}

		//get all data bulan 07
		public function getAcctAccountDetailAll($account_id, $month, $year) {
			$this->db->select('*');
			$this->db->from('acct_account_mutation');
			$this->db->where('acct_account_mutation.account_id', $account_id);
			$this->db->where('acct_account_mutation.month_period', $month);
			$this->db->where('acct_account_mutation.year_period', $year);
			$this->db->order_by('acct_account_mutation.account_id', 'ASC');
			$this->db->order_by('acct_account_mutation.account_mutation_id', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
		}


		// updateMutation bulan ke 07
		public function updateMutation($data){
			// $this->db->set('mutation_in_amount', $data['mutation_in_amount']);
			// $this->db->set('mutation_out_amount', $data['mutation_out_amount']);
			$this->db->set('last_balance', $data['last_balance']);
			$this->db->where('account_id', $data['account_id']);
			$this->db->where('month_period', $data['month_period']);
			$this->db->where('year_period', $data['year_period']);
			
			if($this->db->update('acct_account_mutation')){
				return true;
			} else {
				return false;
			}
		}

	}
?>

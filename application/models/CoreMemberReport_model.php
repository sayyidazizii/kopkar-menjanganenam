<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class CoreMemberReport_model extends CI_Model {
		var $table = "core_member";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		public function getCoreMember($member_character, $branch_id){
			$this->db->select('core_member.member_id, core_member.member_name, core_member.member_no, core_member.member_address, core_member.member_principal_savings_last_balance, core_member.member_special_savings_last_balance, core_member.member_mandatory_savings_last_balance');
			$this->db->from('core_member');
			if($member_character != 9){
				$this->db->where('core_member.member_character', $member_character);
			}
			if($branch_id !=''){
				$this->db->where('core_member.branch_id', $branch_id);
			}
			$this->db->where('core_member.data_state ', 0);
			$this->db->order_by('core_member.member_no', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getExportCoreMember($member_character, $branch_id){
			$this->db->select('core_member.member_id, core_member.member_name, core_member.member_no, core_member.member_address, core_member.member_principal_savings_last_balance, core_member.member_special_savings_last_balance, core_member.member_mandatory_savings_last_balance');
			$this->db->from('core_member');
			if($member_character != 9){
				$this->db->where('core_member.member_character', $member_character);
			} 			
			if ($branch_id != '') {
				$this->db->where('core_member.branch_id', $branch_id);
			}
			$this->db->where('core_member.data_state ', 0);
			$this->db->order_by('core_member.member_no', 'ASC');
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getMemberContribution($member_id){
			$this->db->select('credits_payment_id, credits_payment_interest');
			$this->db->from('acct_credits_payment');
			$this->db->where('member_id ', $member_id);
			$this->db->where('credits_id ', 1);
			$this->db->where('data_state ', 0);
			$this->db->where('YEAR(credits_payment_date) ', date('Y'));
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreBranch(){
			$this->db->select('core_branch.branch_id, core_branch.branch_name');
			$this->db->from('core_branch');
			$this->db->where('core_branch.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getMemberName($member_id){
			$this->db->select('member_name');
			$this->db->from('core_member');
			$this->db->where('member_id', $member_id);
			$result = $this->db->get()->row_array();
			return $result['member_name'];
		}

		public function getUsername($user_id){
			$this->db->select('username');
			$this->db->from('system_user');
			$this->db->where('user_id', $user_id);
			$result = $this->db->get()->row_array();
			return $result['username'];
		}

		public function getCityName($city_id){
			$this->db->select('city_name');
			$this->db->from('core_city');
			$this->db->where('city_id', $city_id);
			$result = $this->db->get()->row_array();
			return $result['city_name'];
		}

		public function getKecamatanName($kecamatan_id){
			$this->db->select('kecamatan_name');
			$this->db->from('core_kecamatan');
			$this->db->where('kecamatan_id', $kecamatan_id);
			$result = $this->db->get()->row_array();
			return $result['kecamatan_name'];
		}

		public function getBranchCity($branch_id){
			$this->db->select('branch_city');
			$this->db->from('core_branch');
			$this->db->where('branch_id', $branch_id);
			$result = $this->db->get()->row_array();
			return $result['branch_city'];
		}

		public function getPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			$this->db->limit(1);
			return $this->db->get()->row_array();
		}
	}
?>
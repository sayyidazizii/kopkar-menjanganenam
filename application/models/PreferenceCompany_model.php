<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class PreferenceCompany_model extends CI_Model {
		var $table = "preference_company";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		public function getDataPreferenceCompany(){
			$this->db->select('*');
			$this->db->from('preference_company');
			$result = $this->db->get()->result_array();
			return $result;
		}
	}
?>
<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class Whatsapp_model extends CI_Model {
		var $table = "wa_broadcast";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		public function getBroadcast(){
			$this->db->select('wa_broadcast.*, system_user.username');
			$this->db->join('system_user', 'system_user.user_id = wa_broadcast.created_id');
			$this->db->from('wa_broadcast');
			$result = $this->db->get()->result_array();
			return $result;
		}
		
		public function getCoreMember(){
			$this->db->select('member_phone');
			$this->db->from('core_member');
			$this->db->where('member_phone !=', '');
			$this->db->where('member_active_status', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function insertBroadcast($data){
			return $query = $this->db->insert('wa_broadcast',$data);
		}
	}
?>
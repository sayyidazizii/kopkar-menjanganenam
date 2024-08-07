<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	class CorePart_model extends CI_Model {
		var $table = "core_part";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		public function getCorePart(){
			$this->db->select('core_part.part_id, core_part.part_code, core_part.part_name, core_branch.branch_name');
			$this->db->from('core_part');
			$this->db->join('core_branch', 'core_part.branch_id = core_branch.branch_id');
			$this->db->where('core_part.data_state', 0);
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

		public function getCoreCity(){
			$this->db->select('core_city.city_id, core_city.city_name');
			$this->db->from('core_city');
			$this->db->where('core_city.province_id', 71);
			$this->db->where('core_city.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreKecamatan($city_id){
			$this->db->select('core_kecamatan.kecamatan_id, core_kecamatan.kecamatan_name');
			$this->db->from('core_kecamatan');
			$this->db->where('core_kecamatan.city_id', $city_id);
			$this->db->where('core_kecamatan.data_state', '0');
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreKelurahan($kecamatan_id){
			$this->db->select('core_kelurahan.kelurahan_id, core_kelurahan.kelurahan_name');
			$this->db->from('core_kelurahan');
			$this->db->where('core_kelurahan.kecamatan_id', $kecamatan_id);
			$this->db->where('core_kelurahan.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getCoreDusun($kelurahan_id){
			$this->db->select('core_dusun.dusun_id, core_dusun.dusun_name');
			$this->db->from('core_dusun');
			$this->db->where('core_dusun.kelurahan_id', $kelurahan_id);
			$this->db->where('core_dusun.data_state', 0);
			$result = $this->db->get()->result_array();
			return $result;
		}

		public function getKelurahanName($kelurahan_id){
			$this->db->select('core_kelurahan.kelurahan_name');
			$this->db->from('core_kelurahan');
			$this->db->where('core_kelurahan.kelurahan_id', $kelurahan_id);
			$this->db->where('core_kelurahan.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['kelurahan_name'];
		}

		public function getDusunName($dusun_id){
			$this->db->select('core_dusun.dusun_name');
			$this->db->from('core_dusun');
			$this->db->where('core_dusun.dusun_id', $dusun_id);
			$this->db->where('core_dusun.data_state', 0);
			$result = $this->db->get()->row_array();
			return $result['dusun_name'];
		}

		
		public function insertCorePart($data){
			$query = $this->db->insert('core_part',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}

		public function insertSystemUser($data){
			$query = $this->db->insert('system_user',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}

		public function getUserID($data){
			$this->db->select('system_user.user_id');
			$this->db->from('system_user');
			$this->db->where('system_user.username', $data['username']);
			$this->db->where('system_user.password', $data['password']);
			$this->db->where('system_user.branch_id', $data['branch_id']);
			$this->db->order_by('system_user.user_id', 'DESC');
			$this->db->limit(1);
			$result = $this->db->get()->row_array();
			return $result['user_id'];
		}

		public function insertSystemUserDusun($data){
			$query = $this->db->insert('system_user_dusun',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function getCorePart_Detail($part_id){
			$this->db->select('core_part.part_id, core_part.part_code, core_part.part_name, core_part.branch_id');
			$this->db->from('core_part');
			$this->db->where('core_part.part_id', $part_id);
			return $this->db->get()->row_array();
		}

		public function getSystemUserDusun($user_id){
			$this->db->select('system_user_dusun.user_dusun_id, system_user_dusun.kelurahan_id, system_user_dusun.dusun_id, system_user_dusun.user_id');
			$this->db->from('system_user_dusun');
			$this->db->where('system_user_dusun.user_id', $user_id);
			return $this->db->get()->result_array();
		}
		
		public function updateCorePart($data){
			$this->db->where("part_id",$data['part_id']);
			$query = $this->db->update($this->table, $data);
			if($query){
				return true;
			}else{
				return false;
			}
		}

		public function deleteSystemUserDusun($user_id){
			$this->db->where('user_id', $user_id);
			$query = $this->db->delete('system_user_dusun');
			if($query){
				return true;
			}else{
				return false;
			}
		}
		
		public function deleteCorePart($part_id){
			$this->db->where("part_id",$part_id);
			$query = $this->db->update($this->table, array('data_state'=>1));
			if($query){
				return true;
			}else{
				return false;
			}
		}
	}
?>
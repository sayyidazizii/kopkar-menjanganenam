<?php
	defined('BASEPATH') or exit('No direct script access allowed');   
	class AcctProfitLossMigration_model extends CI_Model {
		var $table = "migrasi_laba_rugi";
		
		public function __construct(){
			parent::__construct();
			$this->CI = get_instance();
		} 
		
		
		//** get data import array */
		public function getdataProfitLoss(){
			$this->db->select('*');
			$this->db->from('migrasi_laba_rugi');
			$result = $this->db->get()->result_array();
			return $result;
		}

		//** update import array */
		public function updateProfitLossAmount($month, $year) {
            $sql = "
                UPDATE 
                    `acct_account_mutation` a
                INNER JOIN 
                    `acct_account` b ON a.`account_id` = b.`account_id`
                INNER JOIN 
                    `migrasi_laba_rugi` c ON b.`account_code` = c.`account_code`
                SET 
                    a.`mutation_in_amount` = c.`account_amount`,
                    a.`last_balance` = c.`account_amount`
                WHERE 
                    a.`month_period` = ?
                    AND a.`year_period` = ?
            ";
            
            $result = $this->db->query($sql, array($month, $year));
			return $result;
        }
        
        


        public function truncateAcctProfitLossMigration(){
			$query = $this->db->truncate('migrasi_laba_rugi');
			if($query){
				return true;
			}else{
				return false;
			}
		}

        public function insertAcctProfitLossMigration($data){
			$query = $this->db->insert('migrasi_laba_rugi',$data);
			if($query){
				return true;
			}else{
				return false;
			}
		}

	}
?>
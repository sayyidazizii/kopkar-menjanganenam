<?php
	defined('BASEPATH') or exit('No direct script access allowed');

    class AcctSicantikMigration_model extends CI_Model
    {
        var $table = "migrasi_sicantik";
    
        public function __construct()
        {
            parent::__construct();
            $this->CI = get_instance();
        }
    
        public function getdataSicantik()
        {
            $this->db->select('*');
            $this->db->from('migrasi_sicantik');
            $result = $this->db->get()->result_array();
            return $result;
        }
    
        public function insertSicantikAmount()
        {
            // Query for account_code1
            $sql = " INSERT INTO acct_savings_account (branch_id, savings_id, office_id, savings_account_no, member_id, savings_account_last_balance, savings_account_date)
                SELECT '2', '34', '6', no_rek, member_id, saldo_akhr, NOW()
                FROM migrasi_sicantik
            ";

            // Execute the query
            $result = $this->db->query($sql);

            // Return the result
            return $result;
        }


    
        public function truncateAcctSicantikMigration()
        {
            $query = $this->db->truncate('migrasi_sicantik');
            return $query;
        }

        public function truncateAcctSicantikOld()
        {
            // Disable foreign key checks
            $this->db->query('SET foreign_key_checks = 0');
        
            // Truncate the tables
            $query1 = $this->db->truncate('migrasi_sicantik');
            $query2 = $this->db->truncate('acct_savings_account');
            $query3 = $this->db->truncate('acct_savings_account_detail');
            $query4 = $this->db->truncate('acct_savings_account_detail_temp');
        
            // Enable foreign key checks
            $this->db->query('SET foreign_key_checks = 1');
        
            // Check if all queries executed successfully
            return $query1 && $query2 && $query3 && $query4;
        }
        
    
        public function insertAcctSicantikMigration($data)
        {
            $query = $this->db->insert('migrasi_sicantik', $data);
            return $query;
        }

        public function updateMemberId()
        {
            $query = "UPDATE `migrasi_sicantik` mt
            JOIN `core_member` cm ON cm.`member_no` = mt.`no_agt`
            SET mt.`member_id` = cm.`member_id`";
            $result = $this->db->query($query);
            return $result;

        }
    }
    
?>
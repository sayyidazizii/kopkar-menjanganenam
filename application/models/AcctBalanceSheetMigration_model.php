<?php
	defined('BASEPATH') or exit('No direct script access allowed');

    class AcctBalanceSheetMigration_model extends CI_Model
    {
        var $table = "migrasi_neraca";
    
        public function __construct()
        {
            parent::__construct();
            $this->CI = get_instance();
        }
    
        public function getdataBalanceSheet()
        {
            $this->db->select('*');
            $this->db->from('migrasi_neraca');
            $result = $this->db->get()->result_array();
            return $result;
        }
    
        public function updateBalanceSheetAmount($month, $year)
        {
            // Query for account_code1
            $sql1 = "
                UPDATE 
                    `acct_account_opening_balance` a
                INNER JOIN 
                    `acct_account` b ON a.`account_id` = b.`account_id`
                INNER JOIN 
                    `migrasi_neraca` c ON b.`account_code` = c.`account_code1`
                SET 
                    a.`opening_balance` = c.`opening_balance1`
                WHERE 
                    a.`month_period` = ?
                    AND a.`year_period` = ?
            ";

            // Query for account_code2
            $sql2 = "
                UPDATE 
                    `acct_account_opening_balance` a
                INNER JOIN 
                    `acct_account` b ON a.`account_id` = b.`account_id`
                INNER JOIN 
                    `migrasi_neraca` c ON b.`account_code` = c.`account_code2`
                SET 
                    a.`opening_balance` = c.`opening_balance2`
                WHERE 
                    a.`month_period` = ?
                    AND a.`year_period` = ?
            ";

            // Execute both queries
            $result1 = $this->db->query($sql1, array($month, $year));
            $result2 = $this->db->query($sql2, array($month, $year));

            // Return combined result
            return $result1 && $result2;
        }

    
        public function truncateAcctBalanceSheetMigration()
        {
            $query = $this->db->truncate('migrasi_neraca');
            return $query;
        }
    
        public function insertAcctBalanceSheetMigration($data)
        {
            $query = $this->db->insert('migrasi_neraca', $data);
            return $query;
        }
    }
    
?>
<?php
	defined('BASEPATH') or exit('No direct script access allowed');

    class AcctDepositoAccountMigration_model extends CI_Model
    {
        var $table = "migrasi_deposito";
    
        public function __construct()
        {
            parent::__construct();
            $this->CI = get_instance();
        }
    
        public function getdataDeposito()
        {
            $this->db->select('*');
            $this->db->from('migrasi_deposito');
            $result = $this->db->get()->result_array();
            return $result;
        }
    
        public function insertDepositoAmount()
        {
             // Nonaktifkan trigger
            $this->db->query('SET @DISABLE_TRIGGERS = TRUE;');

            // Query for account_code1
            $sql = "INSERT INTO acct_deposito_account (branch_id, office_id, deposito_account_no, member_id, savings_account_id, deposito_account_period, deposito_account_date, deposito_account_due_date, deposito_account_amount, deposito_id,deposito_account_serial_no)
                    SELECT '2', '6', no_berjangka, member_id, savings_account_id, jangka_waktu, tanggal_buka, jatuh_tempo, saldo, deposito_id,no_berjangka
                    FROM migrasi_deposito";

            // Execute the query
            $result = $this->db->query($sql);

            // Aktifkan kembali trigger
            $this->db->query('SET @DISABLE_TRIGGERS = FALSE;');

            // Return the result
            return $result;
        }
    
        public function truncateAcctDepositoMigration()
        {
            $query = $this->db->truncate('migrasi_deposito');
            return $query;
        }

        public function truncateAcctDepositoOld()
        {
            // Disable foreign key checks
            $this->db->query('SET foreign_key_checks = 0');
        
            // Truncate the tables
            $query1 = $this->db->truncate('migrasi_deposito');
            // $query2 = $this->db->truncate('acct_savings_account');
            // $query3 = $this->db->truncate('acct_savings_account_detail');
            // $query4 = $this->db->truncate('acct_savings_account_detail_temp');
        
            // Enable foreign key checks
            $this->db->query('SET foreign_key_checks = 1');
        
            // Check if all queries executed successfully
            return $query1;
        }
    
        public function insertAcctDepositoMigration($data)
        {
            $query = $this->db->insert('migrasi_deposito', $data);
            return $query;
        }

        public function updateMemberId()
        {
            $query = "UPDATE `migrasi_deposito` mt
            JOIN `core_member` cm ON cm.`member_no` = mt.`no_anggota`
            SET mt.`member_id` = cm.`member_id`";
            $result = $this->db->query($query);
            return $result;

        }

        public function updateDepositoId()
        {
            $query = " UPDATE migrasi_deposito t1, acct_deposito t2
            SET t1.deposito_id = t2.deposito_id
            WHERE t1.jangka_waktu = t2.deposito_period AND t1.sk_bg = t2.deposito_interest_rate";
            $result = $this->db->query($query);
            return $result;

        }
        
        public function updateSavingsAccountId()
        {
            $query = "UPDATE migrasi_deposito t1, acct_savings_account t2
            SET t1.savings_account_id = t2.savings_account_id
            WHERE t1.no_tabungan = t2.savings_account_no";
            $result = $this->db->query($query);
            return $result;

        }

        public function updateSk_Bg()
        {
            $query = "UPDATE migrasi_deposito
            SET sk_bg = suku_bunga*100";
            $result = $this->db->query($query);
            return $result;

        }

        public function updateDepositoAccount()
        {
            $query = "UPDATE acct_deposito_account t1, migrasi_deposito t2
            SET t1.deposito_account_no = t2.no_berjangka,
            t1.deposito_account_nisbah = t2.sk_bg
            WHERE t1.deposito_account_serial_no = t2.no_berjangka";
            $result = $this->db->query($query);
            return $result;
        }

        public function validateDepositoAccount()
        {
            $query = "UPDATE acct_deposito_account
                    SET `validation` = '1',
                    `validation_id` = '37',
                    `validation_on` = NOW(),
                    `created_id` = '37',`created_on` = NOW()";
            $result = $this->db->query($query);
            return $result;
        }
    }
    
?>
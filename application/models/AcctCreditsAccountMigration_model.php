<?php
	defined('BASEPATH') or exit('No direct script access allowed');

    class AcctCreditsAccountMigration_model extends CI_Model
    {
        var $table = "migrasi_pinjaman";
    
        public function __construct()
        {
            parent::__construct();
            $this->CI = get_instance();
        }
    
        public function getdataCredits()
        {
            $this->db->select('*');
            $this->db->from('migrasi_pinjaman');
            $result = $this->db->get()->result_array();
            return $result;
        }
    
        public function insertCreditsAmount()
        {
             // Nonaktifkan trigger
            $this->db->query('SET @DISABLE_TRIGGERS = TRUE;');

            // Query for account_code1
            $sql = "INSERT INTO acct_deposito_account (branch_id, office_id, deposito_account_no, member_id, savings_account_id, deposito_account_period, deposito_account_date, deposito_account_due_date, deposito_account_amount, deposito_id,deposito_account_serial_no)
                    SELECT '2', '6', no_berjangka, member_id, savings_account_id, jangka_waktu, tanggal_buka, jatuh_tempo, saldo, deposito_id,no_berjangka
                    FROM migrasi_pinjaman";

            // Execute the query
            $result = $this->db->query($sql);

            // Aktifkan kembali trigger
            $this->db->query('SET @DISABLE_TRIGGERS = FALSE;');

            // Return the result
            return $result;
        }
    
        public function truncateAcctCreditsMigration()
        {
            $query = $this->db->truncate('migrasi_pinjaman');
            return $query;
        }

        public function truncateAcctCreditsOld()
        {
            // Disable foreign key checks
            $this->db->query('SET foreign_key_checks = 0');
        
            // Truncate the tables
            $query1 = $this->db->truncate('migrasi_pinjaman');
            // $query2 = $this->db->truncate('acct_savings_account');
            // $query3 = $this->db->truncate('acct_savings_account_detail');
            // $query4 = $this->db->truncate('acct_savings_account_detail_temp');
        
            // Enable foreign key checks
            $this->db->query('SET foreign_key_checks = 1');
        
            // Check if all queries executed successfully
            return $query1;
        }
    
        public function insertAcctCreditsMigration($data)
        {
            $query = $this->db->insert('migrasi_pinjaman', $data);
            return $query;
        }

        public function updateMemberId()
        {
            $query = "UPDATE `migrasi_pinjaman` mt
            JOIN `core_member` cm ON cm.`member_no` = mt.`no_agt`
            SET mt.`member_id` = cm.`member_id`";
            $result = $this->db->query($query);
            return $result;

        }

        public function updateCreditsId()
        {
            $query = " UPDATE migrasi_pinjaman t1, acct_credits t2
            SET t1.credits_id = t2.credits_id
            WHERE t1.jns_pinjm = t2.credits_name";
            $result = $this->db->query($query);
            return $result;

        }

        public function updatePaymentPeference()
        {
            $query1 = "UPDATE migrasi_pinjaman_elektro
                    SET preferensi_angsuran = 3
                    WHERE preferensi_angsuran = 'POTONG GAJI'";
            $query2 = "UPDATE migrasi_pinjaman_elektro
                     SET preferensi_angsuran = 1
                     WHERE preferensi_angsuran = 'MANUAL'";
            $result1 = $this->db->query($query1);
            $result2 = $this->db->query($query2);
            return $result1 && $result2;

        }
        
        public function updateSavingsAccountId()
        {
            $query = "UPDATE migrasi_pinjaman t1, acct_savings_account t2
            SET t1.savings_account_id = t2.savings_account_id
            WHERE t1.no_tabungan = t2.savings_account_no";
            $result = $this->db->query($query);
            return $result;

        }

        public function updateSk_Bg()
        {
            $query = "UPDATE migrasi_pinjaman
            SET sk_bg = suku_bunga*100";
            $result = $this->db->query($query);
            return $result;

        }

        public function updateDepositoAccount()
        {
            $query = "UPDATE acct_deposito_account t1, migrasi_pinjaman t2
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
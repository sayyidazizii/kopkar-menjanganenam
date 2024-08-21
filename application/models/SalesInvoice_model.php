<?php
	defined('BASEPATH') or exit('No direct script access allowed');
	Class SalesInvoice_model extends CI_Model{

        public function __construct(){
			parent::__construct();
			$this->CI = get_instance();

			// $auth = $this->session->userdata('auth');
			// $this->CI->load->model('Connection_model');

			// $database = $this->Connection_model->define_database($auth['user_id'], $auth['database']);
			// $this->database = $this->load->database($database, true);
            $this->dbminimarket = $this->load->database('minimarket',true);
		} 
        public function getSalesMember($customer_id) {
            $this->dbminimarket->select(['sales_invoice_date', 'sales_invoice_id', 'sales_invoice_no', 'total_amount']);
			$this->dbminimarket->from('sales_invoice');
			$this->dbminimarket->where('data_state', 0);
			$this->dbminimarket->where('credit_created', 0);
			$this->dbminimarket->where('customer_id', $customer_id);
            return $this->dbminimarket->get()->result();
        }
        public function markSalesAsCredit($sales_invoice_id) {
            $this->dbminimarket->where("credits_account_id",$sales_invoice_id);
			$query = $this->dbminimarket->update('sales_invoice', ["credit_created"=>1]);
			if($query){
				return true;
			}else{
				// return false;
				// Log the last query and the error message
				$last_query = $this->dbminimarket->last_query();
				$error = $this->dbminimarket->error();
				log_message('error', 'Failed to insert into acct_journal_voucher: ' . $last_query);
				log_message('error', 'DB Error: ' . $error['message']);
				
				// Return detailed error information for debugging
				return array('query' => $last_query, 'error' => $error);
			}
        }
    }
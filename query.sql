UPDATE acct_profit_loss_report 
SET profit_loss_report_id = profit_loss_report_id + 2
WHERE profit_loss_report_id >150
ORDER BY profit_loss_report_id DESC;

UPDATE acct_profit_loss_report 
SET report_no = report_no +1
WHERE report_no > 15
ORDER BY report_no DESC;

UPDATE acct_balance_sheet_report 
SET report_no = report_no +1
WHERE report_no > 25
ORDER BY report_no DESC;

UPDATE acct_profit_loss_report 
SET report_no = report_no - 1
WHERE report_no >= 32
ORDER BY report_no DESC;

INSERT INTO table1 (id, VALUE) VALUES (3, 300);

INSERT INTO acct_savings_transfer_mutation_to 
(savings_transfer_mutation_id, savings_account_id, savings_id, branch_id, member_id, mutation_id, 
savings_transfer_mutation_to_amount, savings_account_last_balance) 
SELECT 4962, savings_account_id, savings_id, branch_id, member_id, 578, savings_profit_sharing_temp_amount, savings_account_last_balance 
FROM acct_savings_profit_sharing_temp 
WHERE branch_id = 2
   
SELECT * FROM acct_savings_account_detail WHERE savings_account_id = 67843

SELECT account_savings_tax_id FROM preference_company

SELECT * FROM acct_account WHERE account_id = 606

SELECT * FROM acct_journal_voucher_item WHERE journal_voucher_id = 6676

DELETE FROM acct_account WHERE data_state = 1

UPDATE acct_savings_account_detail
SET savings_print_status = 0

SELECT * FROM acct_credits_account WHERE credits_account_serial = 60420220027

SELECT member_name,member_mandatory_savings_last_balance FROM core_member

INSERT INTO migrasi_member(member_no, member_name)
SELECT member_no, member_name
FROM data_anggota

UPDATE migrasi_member t1, core_member_class t2
SET t1.member_class_mandatory_savings = t2.member_class_mandatory_savings
WHERE t1.member_class_id = t2.member_class_id

UPDATE migrasi_member t1, data_anggota t2, migrasi_company_name t3
SET t1.company_id = t3.company_id
WHERE t1.member_no = t2.member_no AND t2.perusahaan = t3.company_code1

UPDATE migrasi_member
SET member = 0
WHERE company_id = "TIDAK AKTIF"`acct_account_mutation`

ALTER TABLE acct_deposito_account AUTO_INCREMENT=1

SELECT member_no,member_name FROM migrasi_member WHERE member_name = "Siswoyo"

INSERT INTO core_member_working (member_id)
SELECT member_id
FROM core_member;

INSERT INTO migrasi_credits (credits_account_serial)
SELECT credits_account_serial
FROM data_pinjaman;

UPDATE acct_deposito_account t1, data_berjangka t2
SET t1.deposito_account_period = t2.jk_waktu,t1.deposito_account_date = t2.tgl_buka,t1.deposito_account_due_date = t2.jt_tempo,t1.deposito_account_nisbah = t2.sk_bng
WHERE t1.deposito_account_no = t2.no_brjngka

UPDATE migrasi_credits t1, acct_credits t2
SET t1.credits_id = t2.credits_id
WHERE t1.credits_id = t2.credits_name

	SET new.credits_account_serial = CreditAccountSerial;
	
UPDATE core_member
SET kecamatan_id = 15233
WHERE kecamatan_id = 0

UPDATE core_member t1, core_city t2
SET t1.province_id = t2.province_id
WHERE t1.city_id = t2.city_id

3 = berjangka,14
4 = triwulanan,15

province = 72, city = 1087, kecamatan_id = 15233

UPDATE acct_deposito_account t1, acct_deposito t2
SET t1.deposito_id = t2.deposito_id
WHERE t1.deposito_account_period = t2.deposito_period
AND t1.deposito_account_nisbah = t2.deposito_interest_rate

UPDATE acct_deposito_account
SET validation_on = '2022-04-19 13:52:27'

INSERT INTO acct_account_balance (account_id, branch_id)
SELECT account_id,2
FROM acct_account;

UPDATE acct_account_mutation
SET month_period = '04'

UPDATE acct_account_balance t1, acct_account_opening_balance t2
SET t1.last_balance = t2.opening_balance
WHERE t1.account_id = t2.account_id

UPDATE acct_deposito_profit_sharing t1, acct_deposito t2
SET t1.deposito_account_nisbah = t2.deposito_interest_rate
WHERE t1.deposito_id = t2.deposito_id

UPDATE acct_deposito_account
SET deposito_process_last_date = deposito_account_date


INSERT INTO acct_deposito_accrual (deposito_account_id,created_on)
SELECT deposito_account_id,'2022-03-31'
FROM acct_deposito_account;


SELECT * FROM acct_deposito_account WHERE deposito_account_no = '5000000143'
291,
SELECT * FROM acct_credits_account WHERE credits_account_serial = '60320220011'


UPDATE acct_savings_account_detail t1, acct_savings_account t2
SET t1.last_balance = t2.savings_account_last_balance
WHERE t1.savings_account_id = t2.savings_account_id

SELECT * FROM acct_savings_member_detail WHERE id_menu = 78

INSERT INTO acct_savings_member_detail (branch_id, member_id, mutation_id, transaction_date, principal_savings_amount, mandatory_savings_amount,last_balance,operated_name)
SELECT branch_id, member_id, mutation_id, transaction_date, principal_savings_amount, mandatory_savings_amount,last_balance,operated_name
FROM acct_savings_member_detail
WHERE savings_member_detail_id = 1080

SELECT * FROM acct_account_opening_balance WHERE opening_balance = 11472200

SELECT * FROM system_menu WHERE id = 'savings-account-administration'

SELECT * FROM acct_savings_account WHERE monthly_administration = 1

CREATE TABLE `acct_savings_administration` (
  `savings_administration_id` BIGINT(22) NOT NULL AUTO_INCREMENT,
  `branch_id` INT(10) DEFAULT 0,
  `savings_account_id` BIGINT(22) DEFAULT 0,
  `savings_administration_amount` DECIMAL(10,2) DEFAULT 0.00,
  `data_state` INT(1) DEFAULT 0,
  `created_id` INT(10) DEFAULT 0,
  `created_on` DATETIME DEFAULT NULL,
  `last_update` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`savings_administration_id`)
)

SELECT * FROM acct_account_balance_detail WHERE account_id = 617

SELECT * FROM acct_journal_voucher_item WHERE account_id = 617

UPDATE acct_account_opening_balance
SET branch_id = 2

UPDATE acct_account_balance_detail
SET opening_balance = (opening_balance*-1)
WHERE account_id = 617

UPDATE acct_journal_voucher_item
SET account_id_default_status = 1
WHERE account_id = 617

SELECT * FROM core_member WHERE member_id = 2150 OR member_id = 1
SELECT * FROM core_member_working WHERE member_id = 2150 OR member_id = 1

SELECT member_name,member_status FROM core_member WHERE member_status = 0

SELECT COUNT(account_balance_detail_id) FROM acct_account_balance_detail WHERE transaction_date > "2022-04-30"

SELECT * FROM acct_account_balance_detail WHERE transaction_date > "2022-04-30"

UPDATE system_menu_mapping
SET id_menu = 531
WHERE id_menu = 53

SELECT SUM(total_amount) FROM sales_order

SELECT * FROM acct_credits_account WHERE credits_id = 13

SELECT * FROM acct_account_balance_detail WHERE account_id = 700 AND transaction_date > "2022-04-30"

SELECT * FROM acct_credits_account WHERE credits_account_payment_date > "1975-12-20"

SELECT COUNT(*) FROM acct_credits_account

UPDATE system_menu_mapping
SET id_menu = "A951"
WHERE id_menu = "A95"

SELECT credits_account_id,credits_account_date,credits_account_due_date,credits_account_period,
credits_account_payment_to,credits_account_payment_date,credits_account_last_payment_date 
FROM `acct_credits_account`

SELECT credits_account_payment_amount FROM acct_credits_account

--------------------------------------------------------------------------------------------------------------------------------------------

INSERT INTO acct_credits_account (branch_id, credits_id, member_id, office_id, payment_type_id, credits_payment_period, source_fund_id,
credits_account_date, created_id, created_on, credits_account_amount, credits_account_principal_amount, credits_account_interest_amount)
SELECT 2, 19, member_id, 6, 1, sisa_tenor, 5, "2022-06-25", 37, "2022-06-25", (sisa_pokok*sisa_tenor), angs_pokok, angs_bunga
FROM data_pinjaman;

UPDATE acct_savings_account_detail t1, acct_savings_account t2
SET t1.last_balance = t2.savings_account_last_balance
WHERE t1.savings_account_id = t2.savings_account_id

SELECT SUM(last_balance) FROM acct_savings_account_detail

UPDATE mustahik_form t1
SET t1.form_id = t1.form_no
WHERE t1.form_no = 6

ALTER TABLE mustahik_form AUTO_INCREMENT = 48

UPDATE acct_savings_account_detail t1, acct_savings_account t2
SET t1.last_balance = t2.savings_account_last_balance
WHERE t1.savings_account_id = t2.savings_account_id

SELECT SUM(savings_account_last_balance) FROM acct_savings_account

SELECT * FROM core_kecamatan WHERE kecamatan_id = 14722
SELECT * FROM core_kelurahan WHERE kecamatan_id = 14726

SELECT * FROM core_kelurahan WHERE kecamatan_id = 14722
OR kecamatan_id = 14723 OR kecamatan_id = 14724 OR kecamatan_id = 14725 OR kecamatan_id = 14726

//TEAMS
//data_karyawan
INSERT INTO hro_employee_data (applicant_id, marital_status_id, region_id, branch_id, company_id, division_id, department_id, section_id, unit_id, job_title_id, grade_id, class_id, location_id, bank_id, employee_shift_id, employee_code, employee_rfid_code, employee_name, employee_address, employee_city, employee_rt, employee_rw, employee_kelurahan, employee_kecamatan, employee_postal_code, employee_residential_address, employee_mobile_phone, employee_email_address, employee_id_type, employee_place_of_birth, employee_religion, employee_blood_type, employee_id_number, employee_date_of_birth, employee_residential_city, employee_residential_rt, employee_residential_rw, employee_residential_kecamatan, employee_residential_kelurahan, employee_residential_postal_code, employee_gender, employee_home_phone, employee_heir_name, employee_photo, employee_status, employee_remark, employee_employment_working_status, employee_hire_date, employee_employment_status, employee_employment_status_date,employee_employment_status_duedate, employee_employment_overtime_status, employee_last_day_off, employee_day_off_cycle, employee_day_off_status, employee_picture, employee_bank_acct_no, employee_bank_acct_name,employee_token)
SELECT applicant_id, marital_status_id, region_id, branch_id, company_id, division_id, department_id, section_id, unit_id, job_title_id, grade_id, class_id, location_id, bank_id, employee_shift_id, employee_code, employee_rfid_code, employee_name, employee_address, employee_city, employee_rt, employee_rw, employee_kelurahan, employee_kecamatan, employee_postal_code, employee_residential_address, employee_mobile_phone, employee_email_address, employee_id_type, employee_place_of_birth, employee_religion, employee_blood_type, employee_id_number, employee_date_of_birth, employee_residential_city, employee_residential_rt, employee_residential_rw, employee_residential_kecamatan, employee_residential_kelurahan, employee_residential_postal_code, employee_gender, employee_home_phone, employee_heir_name, employee_photo, employee_status, employee_remark, employee_employment_working_status, employee_hire_date, employee_employment_status, employee_employment_status_date,employee_employment_status_duedate, employee_employment_overtime_status, employee_last_day_off, employee_day_off_cycle, employee_day_off_status, employee_picture, employee_bank_acct_no, employee_bank_acct_name,employee_token
FROM hro_employee_data
WHERE employee_id = 11734

//data_user_login
INSERT INTO SYSTEM_USER (region_id, branch_id, location_id, division_id, department_id, section_id, unit_id, user_group_id, employee_id, employee_shift_id, username, PASSWORD, password_default_char, avatar, employee_employment_working_status, payroll_employee_level, user_status, data_state)
SELECT region_id, branch_id, location_id, division_id, department_id, section_id, unit_id, user_group_id, employee_id, employee_shift_id, username, PASSWORD, password_default_char, avatar, employee_employment_working_status, payroll_employee_level, user_status, data_state
FROM SYSTEM_USER
WHERE user_id = 37

//data_jadwal
INSERT INTO schedule_employee_schedule_item (employee_schedule_id,shift_assignment_id,employee_shift_id,shift_id,region_id,branch_id,location_id,division_id,department_id,section_id,unit_id,employee_id,employee_rfid_code, employee_schedule_item_status_default,employee_schedule_item_status,employee_schedule_item_date,employee_schedule_item_date_status, employee_schedule_item_in_start_date,employee_schedule_item_in_end_date,employee_schedule_item_out_start_date,employee_schedule_item_out_end_date,employee_schedule_item_log_status,employee_schedule_item_log_in_date,employee_schedule_item_log_out_date,employee_schedule_item_downloaded,employee_schedule_item_downloaded_on,employee_schedule_item_meal_coupon_status,employee_schedule_item_meal_coupon_date,employee_schedule_item_photo_in,employee_schedule_item_photo_out,employee_schedule_item_location_lat_in,employee_schedule_item_location_long_in,employee_schedule_item_location_lat_out,employee_schedule_item_location_long_out,employee_schedule_item_address_in,employee_schedule_item_address_out,employee_status)
SELECT employee_schedule_id,shift_assignment_id,employee_shift_id,shift_id,region_id,branch_id,location_id,division_id,department_id,section_id,unit_id,employee_id,employee_rfid_code, employee_schedule_item_status_default,employee_schedule_item_status,employee_schedule_item_date,employee_schedule_item_date_status, employee_schedule_item_in_start_date,employee_schedule_item_in_end_date,employee_schedule_item_out_start_date,employee_schedule_item_out_end_date,employee_schedule_item_log_status,employee_schedule_item_log_in_date,employee_schedule_item_log_out_date,employee_schedule_item_downloaded,employee_schedule_item_downloaded_on,employee_schedule_item_meal_coupon_status,employee_schedule_item_meal_coupon_date,employee_schedule_item_photo_in,employee_schedule_item_photo_out,employee_schedule_item_location_lat_in,employee_schedule_item_location_long_in,employee_schedule_item_location_lat_out,employee_schedule_item_location_long_out,employee_schedule_item_address_in,employee_schedule_item_address_out,employee_status
FROM schedule_employee_schedule_item
WHERE employee_schedule_item_id = 585955

INSERT INTO schedule_employee_schedule_item (employee_schedule_id,shift_assignment_id,employee_shift_id,shift_id,region_id,branch_id,location_id,division_id,department_id,section_id,unit_id,employee_id,employee_rfid_code, employee_schedule_item_status_default,employee_schedule_item_status,employee_schedule_item_date,employee_schedule_item_date_status, employee_schedule_item_in_start_date,employee_schedule_item_in_end_date,employee_schedule_item_out_start_date,employee_schedule_item_out_end_date,employee_schedule_item_log_status,employee_schedule_item_log_in_date,employee_schedule_item_log_out_date,employee_schedule_item_downloaded,employee_schedule_item_downloaded_on,employee_schedule_item_meal_coupon_status,employee_schedule_item_meal_coupon_date,employee_schedule_item_photo_in,employee_schedule_item_photo_out,employee_schedule_item_location_lat_in,employee_schedule_item_location_long_in,employee_schedule_item_location_lat_out,employee_schedule_item_location_long_out,employee_schedule_item_address_in,employee_schedule_item_address_out,employee_status)
SELECT employee_schedule_id,shift_assignment_id,employee_shift_id,shift_id,region_id,branch_id,location_id,division_id,department_id,section_id,unit_id,11750,'Ratih', employee_schedule_item_status_default,employee_schedule_item_status,employee_schedule_item_date,employee_schedule_item_date_status, employee_schedule_item_in_start_date,employee_schedule_item_in_end_date,employee_schedule_item_out_start_date,employee_schedule_item_out_end_date,employee_schedule_item_log_status,employee_schedule_item_log_in_date,employee_schedule_item_log_out_date,employee_schedule_item_downloaded,employee_schedule_item_downloaded_on,employee_schedule_item_meal_coupon_status,employee_schedule_item_meal_coupon_date,employee_schedule_item_photo_in,employee_schedule_item_photo_out,employee_schedule_item_location_lat_in,employee_schedule_item_location_long_in,employee_schedule_item_location_lat_out,employee_schedule_item_location_long_out,employee_schedule_item_address_in,employee_schedule_item_address_out,employee_status
FROM schedule_employee_schedule_item
WHERE employee_schedule_item_id = 585955

//schedule_employee_shift_item 

//hro_employee_attendance_log
INSERT INTO hro_employee_attendance_log (region_id, branch_id, location_id, division_id, department_id, section_id, unit_id, shift_id, employee_shift_id, employee_id, employee_rfid_code, employee_attendance_log_period) 
SELECT region_id, branch_id, location_id, division_id, department_id, section_id, unit_id, shift_id, employee_shift_id, employee_id, employee_rfid_code, employee_attendance_log_period 
FROM hro_employee_attendance_log
WHERE employee_attendance_log_id = 93

SET t1.employee_schedule_item_out_end_date = DATEADD(HOUR, 24, CAST(CAST(employee_schedule_item_out_end_date AS DATE) AS DATETIME)) 

UPDATE schedule_employee_schedule_item t1
SET t1.employee_schedule_item_out_end_date = employee_schedule_item_out_end_date + INTERVAL 419 MINUTE 
WHERE t1.employee_schedule_item_id >= 586625

SELECT * FROM schedule_employee_schedule_item
WHERE employee_schedule_item_id >= 586625

SELECT * FROM schedule_employee_schedule_item WHERE employee_schedule_item_id = '586954'

SELECT credits_account_serial, credits_account_last_balance, credits_account_last_payment_date, credits_account_payment_date, credits_account_payment_to, credits_account_interest_last_balance, credits_account_status, credits_account_accumulated_fines, credits_account_interest_1, credits_account_interest_2
FROM acct_credits_account 
WHERE credits_account_serial = '10920220009' OR credits_account_serial = '310/KOPKAR.2/010/22'

DELETE FROM `system_menu_mapping` WHERE id_menu = "D41" OR id_menu = "D42" OR id_menu = "D61" OR id_menu = "D71" OR id_menu = "D81" 

ALTER TABLE core_member_working AUTO_INCREMENT = 1

UPDATE acct_savings_member_detail t1, core_member t2
SET t1.last_balance = (t1.last_balance - t2.member_special_savings_last_balance), 
t1.opening_balance = (t1.opening_balance - t2.member_special_savings_last_balance)
WHERE t1.member_id = t2.member_id
ORDER BY t1.savings_member_detail_id DESC

SELECT * FROM system_menu_mapping WHERE id_menu = 214

INSERT INTO core_member (member_no, member_name, division_id) 
SELECT member_no, member_name, division_id
FROM core_member_migrasi

UPDATE core_member t1, core_member_migrasi t2
SET t1.member_account_minimarket_debt = t2.amountfence_1
WHERE t1.member_no = t2.member_no

UPDATE core_member
SET member_account_minimarket_debt = 0

INSERT INTO core_member_working (member_id, division_id) 
SELECT member_id, division_id
FROM core_member

SELECT SUM(member_account_minimarket_debt) FROM core_member WHERE member_account_minimarket_debt != 0

SELECT SUM(savings_account_last_balance) FROM acct_savings_account WHERE data_state = 0

SELECT savings_account_id, savings_account_last_balance, savings_account_blockir_amount, savings_account_blockir_status FROM acct_savings_account WHERE data_state = 0

SELECT * FROM mustahik_worksheet WHERE worksheet_code = "surveyor_respondent_photos"

SELECT * FROM acct_capital_statement_report WHERE capital_type = 1 AND report_type != 0

SELECT * FROM acct_capital_statement_report WHERE account_name = "Penerimaan Bagi Hasil Atas Penempatan Dana Zakat"

SELECT * FROM acct_credits_account WHERE credits_account_id > 548

SELECT * FROM acct_profit_loss_report WHERE account_name LIKE "%Pasca%"

SELECT * FROM acct_profit_loss_report WHERE format_id = 3 ORDER BY report_no ASC

UPDATE core_member
SET member_status = 1

SELECT * FROM acct_account_balance_detail WHERE account_id = 788

SELECT * FROM acct_journal_voucher_item WHERE account_id = 788

UPDATE acct_account_balance_detail
SET account_in = temp
WHERE account_id = 788

SELECT * FROM acct_account_balance_detail WHERE account_id = 791

SELECT * FROM acct_journal_voucher_item WHERE account_id = 791

UPDATE acct_account_balance_detail
SET account_in = temp
WHERE account_id = 791

SELECT * FROM acct_account_mutation WHERE year_period = 2022 AND account_id = 788

SELECT * FROM acct_account_opening_balance WHERE year_period = 2022 AND account_id = 788

SELECT member_id, member_name, member_phone, member_active_status FROM core_member WHERE member_phone != '' AND member_active_status = 0

UPDATE core_member
SET member_phone = ''

SELECT account_risk_reserve_id, account_insurance_cost_id FROM preference_company

SELECT * 
FROM invt_item t
WHERE NOT EXISTS
(
  SELECT *
  FROM invt_item_stock a
  WHERE a.item_id = t.item_id
  AND a.data_state = 0
  AND t.data_state = 0
)

SELECT * FROM invt_item_stock WHERE item_id = 671

SELECT * FROM system_menu_mapping WHERE id_menu = "7C"

SELECT account_interest_id FROM preference_company

SELECT * FROM acct_account_opening_balance WHERE month_period = 02 AND year_period = 2023

ALTER TABLE acct_account_mutation AUTO_INCREMENT = 1

SELECT * FROM invt_item_barcode WHERE item_id = 711741

SELECT * FROM core_member WHERE member_status = 0

SELECT account_id, account_code, account_name, account_type_id FROM acct_account

UPDATE acct_account
SET account_type_id = 3
WHERE account_id >= 357

UPDATE acct_profit_loss_report
SET profit_loss_report_id = 3
WHERE account_id >= 357

SELECT * FROM acct_profit_loss_report WHERE report_no = 113

SELECT * FROM acct_account WHERE account_code = "501.01.01.01"

UPDATE acct_profit_loss_report
SET created_id = 37
WHERE created_id = 0

UPDATE acct_profit_loss_report
SET account_type_id = 2
WHERE report_no >= 59

SELECT * FROM acct_profit_loss_report WHERE account_code = "603.01.04.01"

SELECT savings_profit_sharing_temp_date FROM acct_savings_profit_sharing_temp

UPDATE acct_profit_loss_report
SET category_type = 0

SELECT * FROM acct_profit_loss_report WHERE report_type = 6

SELECT * FROM system_menu_mapping WHERE id_menu = "24"

SELECT * FROM invt_item WHERE item_category_id = 12

SELECT account_income_tax_id FROM preference_company

SELECT * FROM acct_credits_account WHERE credits_account_id = 656 OR credits_account_id = 703

SELECT * FROM trans_service_disposition WHERE service_requisition_no = "000008/SR/IV/2022"

SELECT * FROM mustahik_worksheet_result WHERE worksheet_result_id = 42

SELECT * FROM mustahik_worksheet_item WHERE worksheet_item_name = "Layak Dibantu"

SELECT * FROM mustahik_worksheet WHERE service_id = 1

SELECT * FROM mustahik_worksheet_item WHERE worksheet_id = 152

SELECT * FROM acct_account_mutation WHERE year_period != 2022

SELECT * FROM acct_account_opening_balance WHERE year_period != 2022

SELECT * FROM acct_account_mutation WHERE year_period = 2022

SELECT * FROM acct_account_opening_balance WHERE year_period = 2023

SELECT * FROM acct_account_opening_balance WHERE account_id = 700 AND year_period = 2023

SELECT * FROM acct_account_mutation WHERE account_id = 593

SELECT * FROM system_menu_mapping WHERE id_menu = 213

UPDATE acct_account t1, acct_account2 t2
SET t2.parent_id = t1.account_id
WHERE t2.account_code = t1.parent_code

SELECT credits_account_principal FROM acct_credits_account

SELECT * FROM preference_transaction_module WHERE transaction_module_code = "PPG"

SELECT * FROM system_menu_mapping WHERE id_menu = "7C4"

SELECT * FROM acct_journal_voucher_item WHERE journal_voucher_item_id > 18948`system_menu_mapping`

SELECT account_insurance_cost_id FROM preference_company

UPDATE acct_account t1, acct_balance_sheet_report t2
SET t2 account_id = t1

SELECT * FROM system_menu_mapping WHERE id_menu = "7C4"

SELECT * FROM acct_credits_account WHERE credits_account_id = 412

SELECT * FROM trans_service_disposition WHERE review_status = 1

UPDATE invt_item_stock
SET last_balance = 0

SELECT COUNT(*) FROM invt_item

SELECT credits_account_id, credits_approve_status FROM acct_credits_account

SELECT * FROM acct_credits_account WHERE credits_account_serial = "P23-0635"

SELECT member_id, member_active_status, member_status FROM core_member

SELECT * FROM system_menu_mapping WHERE id_menu = 72 OR id_menu = 73

SELECT * FROM acct_journal_voucher WHERE transaction_module_id = 10

SELECT tax_minimum_amount FROM preference_company

UPDATE acct_account_mutation
SET month_period = "06"

UPDATE acct_account_opening_balance
SET month_period = "07"

INSERT INTO core_member (branch_id, member_no, member_nik, member_name, member_gender_name, member_date_of_birth, member_address, member_part_name, member_division_name, member_register_date, member_principal_savings_last_balance, member_mandatory_savings_last_balance) 
SELECT '2', no_anggota1, nik_anggota, nama_panjang, member_gender, member_date_of_birth, member_address, bagian, divisi, tgl_daftar, simp_pok, simp_wjb
FROM migrasi_member

INSERT INTO core_member_working(member_id, division_name, part_name)
SELECT member_id, member_division_name, member_part_name
FROM core_member

UPDATE core_member_working t1, core_part t2
SET t1.part_id = t2.part_id
WHERE t1.part_name = t2.part_name

UPDATE core_member_working
SET part_name = "Pembelian & Pengadaan"
WHERE part_name = "Pengadaan"

SELECT * FROM core_member_working WHERE part_id = 0
71 1048 14630 302748

UPDATE core_member
SET member_status = 1

UPDATE acct_savings_account t1, core_member t2
SET t1.savings_account_no = t2.member_no
WHERE t1.member_id = t2.member_id

UPDATE migrasi_tabungan t1, core_member t2
SET t1.member_id = t2.member_id
WHERE t1.nama = t2.member_name

INSERT INTO acct_savings_account(branch_id, savings_id, member_id, office_id, savings_account_no, savings_account_date, savings_account_last_balance)
SELECT '2', '34', member_id, '6', savings_account_no, tanggal_buka, saldo_akhr
FROM migrasi_tabungan

INSERT INTO acct_account_balance(branch_id, account_id, last_balance, created_id)
SELECT '2', account_id, opening_balance, '37'
FROM acct_account_opening_balance

UPDATE core_member
SET created_id = 37

SELECT * FROM core_part WHERE part_id =78

SELECT 'sales_cash_receivable_account' FROM preference_company

INSERT INTO core_member (branch_id, member_no, member_nik, member_name, member_register_date) 
SELECT '2', savings_account_no, '0', nama, "1980-01-01"
FROM migrasi_tabungan
WHERE migrasi_tabungan.member_id = 0

UPDATE core_member
SET province_id = 71, city_id = 1048, kecamatan_id = 14630, kelurahan_id = 302748, created_id = 37
WHERE member_id > 10055

INSERT INTO core_member_working (member_id, division_id, part_id) 
SELECT member_id, '79', '72'
FROM core_member
WHERE core_member.member_id > 10055

UPDATE acct_savings_account_detail t1, acct_savings_account t2
SET t1.last_balance = t2.savings_account_last_balance
WHERE t1.savings_account_id = t2.savings_account_id

INSERT acct_savings_member_detail(branch_id, member_id, mutation_id, transaction_date, principal_savings_amount, mandatory_savings_amount, last_balance)
SELECT '2', member_id, '1', '2023-07-17', member_principal_savings_last_balance, member_mandatory_savings_last_balance, (member_principal_savings_last_balance+member_mandatory_savings_last_balance)
FROM core_member

UPDATE migrasi_deposito t1, core_member t2
SET t1.member_id = t2.member_id
WHERE t1.no_anggota = t2.member_no

INSERT acct_deposito_account(deposito_id,  member_id, branch_id, office_id, savings_account_id, deposito_account_no, deposito_account_period, deposito_account_date, deposito_account_due_date, deposito_account_serial_no, deposito_account_amount, created_id)
SELECT deposito_id, member_id, '2', '6', savings_account_id, no_berjangka, jangka_waktu, tanggal_buka, jatuh_tempo, no_berjangka, saldo, '37'
FROM migrasi_deposito

UPDATE migrasi_deposito t1, acct_deposito t2
SET t1.deposito_id = t2.deposito_id
WHERE t1.jangka_waktu = t2.deposito_period AND t1.bunga = t2.deposito_interest_rate

UPDATE migrasi_deposito t1, acct_savings_account t2
SET t1.savings_account_id = t2.savings_account_id
WHERE t1.no_tabungan = t2.savings_account_no

SELECT deposito_account_id, deposito_account_no, deposito_process_last_date FROM acct_deposito_account

UPDATE acct_deposito_account
SET deposito_process_last_date = "2023-06-30"

UPDATE migrasi_pinjaman t1, core_member t2
SET t1.member_id = t2.member_id
WHERE t1.no_anggota = t2.member_no

SELECT credits_account_serial 
FROM acct_credits_account
GROUP BY credits_account_serial
HAVING COUNT(credits_account_serial) > 1;

SELECT * FROM core_member WHERE (member_name = "Indriyanto" OR member_name = "Pujiono" OR member_name = "Siti Maesaroh" OR member_name = "Slamet Haryadi" OR member_name = "Sugiyanto" OR member_name = "Sutarman" OR member_name = "Sutarno" OR member_name = "Wahyudi" OR member_name = "Wisnu Wardana")

SELECT * FROM acct_savings_account WHERE (savings_account_no = 00253 OR savings_account_no = 00505 OR savings_account_no = 00669 OR savings_account_no = 01708 OR savings_account_no = 01938 OR savings_account_no = 00046 OR savings_account_no = 00476 OR savings_account_no = 00740 OR savings_account_no = 00230 OR savings_account_no = 00834 OR savings_account_no = 00074 OR savings_account_no = 01570 OR savings_account_no = 00474 OR savings_account_no = 00379 OR savings_account_no = 00278 OR savings_account_no = 00520 OR savings_account_no = 01736 OR savings_account_no = 00948)

SELECT * FROM migrasi_deposito WHERE (no_anggota = 00253 OR no_anggota = 00505 OR no_anggota = 00669 OR no_anggota = 01708 OR no_anggota = 01938 OR no_anggota = 00046 OR no_anggota = 00476 OR no_anggota = 00740 OR no_anggota = 00230 OR no_anggota = 00834 OR no_anggota = 00074 OR no_anggota = 01570 OR no_anggota = 00474 OR no_anggota = 00379 OR no_anggota = 00278 OR no_anggota = 00520 OR no_anggota = 01736 OR no_anggota = 00948)

INSERT acct_credits_account(branch_id, credits_id, member_id, office_id, payment_preference_id, payment_type_id, credits_payment_period, source_fund_id, credits_account_date, credits_account_due_date, credits_account_period, credits_account_type, credits_account_payment_period, credits_account_amount, credits_account_interest, credits_account_amount_received, credits_account_principal_amount, credits_account_interest_amount, credits_account_payment_amount, credits_account_last_balance, credits_account_interest_last_balance, credits_account_payment_to, credits_account_payment_date, credits_account_last_payment_date, credits_account_status, credits_approve_status, created_id, created_on)
SELECT '2', '1', member_id, '6', '1', '1', '1', '5', tgl_pinjm, jt_tempo, jk_waktu, '0', '0', plafon, sk_bng, ((plafon-sld_pokok)+(jasa_perbulan*total_angsur)), pokok_perbulan, jasa_perbulan, total_perbulan, sld_pokok, (jasa_perbulan*total_angsur), total_angsur, tgl_angsur_brktny, tgl_trkhir_angsur, '0', '1', '37', tgl_pinjm
FROM migrasi_pinjaman

UPDATE core_member
SET member_mandatory_savings = 50000

SELECT COUNT(*) FROM core_member_working WHERE division_id = 80

SELECT * FROM trans_service_requisition WHERE (service_requisition_no = "000059/SR/V/2022" OR service_requisition_no = "000119/SR/VI/2022" OR service_requisition_no = "000125/SR/VI/2022" OR service_requisition_no = "000137/SR/VI/2022" OR service_requisition_no = "000155/SR/VII/2022" OR service_requisition_no = "000166/SR/VIII/2022" OR service_requisition_no = "000238/SR/IX/2022" OR service_requisition_no = "000225/SR/IX/2022" OR service_requisition_no = "000211/SR/VIII/2022" OR service_requisition_no = "000258/SR/IX/2022" OR service_requisition_no = "000260/SR/IX/2022" OR service_requisition_no = "000284/SR/IX/2022" OR service_requisition_no = "000409/SR/XII/2022" OR service_requisition_no = "000007/SR/I/2023" OR service_requisition_no = "000021/SR/II/2023" OR service_requisition_no = "000050/SR/III/2023" OR service_requisition_no = "000060/SR/III/2023" OR service_requisition_no = "000077/SR/III/2023" OR service_requisition_no = "000091/SR/IV/2023" OR service_requisition_no = "000134/SR/V/2023" OR service_requisition_no = "000145/SR/V/2023")

UPDATE schedule_employee_schedule_item
SET employee_schedule_item_log_out_date = NULL

SELECT member_id, member_debet_preference FROM core_member

UPDATE data_sicantik t1, core_member t2
SET t1.member_id = t2.member_id
WHERE t1.no_agt = t2.member_no

INSERT acct_savings_account()
SELECT member_id, no_rek, tanggal_buka, saldo_akhr, setoran_awal
FROM data_sicantik

INSERT INTO acct_savings_account(branch_id, savings_id, member_id, office_id, savings_account_no, savings_account_date, savings_account_last_balance, savings_account_first_deposit_amount)
SELECT '2', '35', member_id, '6', no_rek, tanggal_buka, saldo_akhr, setoran_awal
FROM data_sicantik

UPDATE acct_savings_account_detail t1, acct_savings_account t2
SET t1.last_balance = t2.savings_account_last_balance
WHERE t1.savings_account_id = t2.savings_account_id AND t1.savings_account_detail_id > 18176

SELECT * FROM acct_account_opening_balance WHERE month_period = 08

SELECT * FROM acct_account_mutation WHERE account_id = 517

SELECT * FROM acct_journal_voucher_item WHERE account_id = 426

SELECT COUNT(*) FROM acct_debt_cut_off_item

UPDATE trans_service_disposition
SET service_register_no = "-"
WHERE service_register_no = 0

SELECT * FROM acct_commission WHERE deposito_account_id = 301

SELECT * FROM acct_journal_voucher_item WHERE account_id = 683 AND last_update > "2023-09-10" AND last_update < "2023-09-12" AND journal_voucher_amount = 25000

//migrasi pinjaman mandiri sejahtera
INSERT INTO acct_credits_account(branch_id, credits_account_serial, member_id, credits_id, credits_account_period, credits_account_date,
credits_account_due_date, credits_account_amount, credits_account_principal_amount, credits_account_interest_amount, credits_account_payment_amount,
credits_account_interest, credits_account_last_balance, credits_account_payment_to, office_id, credits_account_last_payment_date, credits_account_payment_date, 
payment_preference_id, credits_account_provisi, credits_account_komisi, credits_account_insurance, credits_account_adm_cost, credits_account_materai, 
credits_account_risk_reserve, source_fund_id, created_on, credits_approve_status, credits_payment_period, payment_type_id)
SELECT '2', no_pinjaman, member_id, jenis_pinjaman, jk_waktu, tgl_pinjm, jt_tempo, plafon, angsuran_pokok_perbulan, angsuran_jasa_perbulan, 
angsuran_total_perbulan, suku_bunga, saldo_pokok, total_angsur, bo, tgl_trkhir_angsur, tgl_angsur_brktny, 
preferensi_angsuran, biaya_provisi, biaya_survei, biaya_asuransi, biaya_administrasi, biaya_materai, biaya_cadangan_resiko, '5', 
tgl_pinjm, '1', '1', '1'
FROM migrasi_pinjaman

SET new.credits_account_serial = CreditAccountSerial;

SELECT * FROM acct_profit_loss_report WHERE account_name = "Imbalan Pasca Kerja (IPK)"

UPDATE core_kelurahan`update_trans_service_disposition`
SET created_at = "2023-09-27 14:10:29"

UPDATE mustahik_worksheet
SET created_at = "2023-09-29 13:42:17"

INSERT INTO mustahik_worksheet(service_id, worksheet_no, worksheet_name, worksheet_type, worksheet_code, created_id, created_at)
SELECT "4", worksheet_no, worksheet_name, worksheet_type, worksheet_code, created_id, created_at
FROM mustahik_worksheet_copy

INSERT INTO mustahik_worksheet_item(worksheet_id, section_name, worksheet_item_name, worksheet_item_code, created_id, created_at)
SELECT worksheet_id, section_name, worksheet_item_name, worksheet_item_code, created_id, created_at
FROM mustahik_worksheet_item_copy

SELECT * FROM mustahik_worksheet WHERE worksheet_code = "worksheet__education"

SELECT * FROM acct_account_mutation WHERE month_period = 09 AND year_period = 2023

SELECT * FROM acct_account_opening_balance WHERE account_id = 700

INSERT INTO acct_account_balance(branch_id, account_id, created_id)
SELECT 2, account_id, 37
FROM acct_account

UPDATE acct_account_balance
SET last_balance = 0

//shu
INSERT INTO acct_account_mutation (branch_id, account_id, mutation_in_amount, last_balance, month_period, year_period, created_id)
SELECT '2', account_id, last_balance, last_balance, '11', '2023', '37'
FROM acct_account_balance

//neraca
INSERT INTO acct_account_opening_balance (branch_id, account_id, opening_balance, month_period, year_period, created_id)
SELECT '2', account_id, last_balance, '12', '2023', '37'
FROM acct_account_balance

//anggota
INSERT INTO core_member (member_no, member_name, member_gender, member_date_of_birth, member_address, member_phone, member_part_name, member_division_name, member_nik, member_register_date, member_principal_savings_last_balance, member_mandatory_savings_last_balance)
SELECT no_anggota, nama_panjang, member_gender, member_date_of_birth, member_address, no_telp, bagian, divisi, no_id, tgl_daftar, simp_pok, simp_wjb
FROM migrasi_anggota

//acct_savings_member_detail
INSERT acct_savings_member_detail(branch_id, member_id, mutation_id, transaction_date, principal_savings_amount, mandatory_savings_amount, last_balance)
SELECT '2', member_id, '1', '2023-11-30', member_principal_savings_last_balance, member_mandatory_savings_last_balance, (member_principal_savings_last_balance+member_mandatory_savings_last_balance)
FROM core_member

//tabungan
INSERT acct_savings_account (branch_id, savings_id, office_id, savings_account_no, member_id, savings_account_last_balance)
SELECT '2', '34', '6', no_rek, member_id, saldo_akhr
FROM migrasi_tabungan

//sicantik
INSERT acct_savings_account (branch_id, savings_id, office_id, savings_account_no, member_id, savings_account_date, savings_account_last_balance, savings_account_first_deposit_amount)
SELECT '2', '35', '6', no_rek, member_id, tanggal_buka, saldo_akhr, setoran_awal
FROM migrasi_sicantik

//acct_savings_account_detail
UPDATE acct_savings_account_detail t1, acct_savings_account t2
SET t1.last_balance = t2.savings_account_last_balance, t1.mutation_in = t2.savings_account_last_balance
WHERE t1.savings_account_id = t2.savings_account_id

//deposito
INSERT acct_deposito_account (branch_id, office_id, deposito_account_no, member_id, savings_account_id, deposito_account_period, deposito_account_date, deposito_account_due_date, deposito_account_amount, deposito_id)
SELECT '2', '6', no_berjangka, member_id, savings_account_id, jangka_waktu, tanggal_buka, jatuh_tempo, saldo, deposito_id
FROM migrasi_deposito

//anggota
INSERT INTO core_member (member_no, member_name)
SELECT no_agt, nama
FROM migrasi_tabungan
WHERE migrasi_tabungan.member_no IS NULL

//pinjaman
INSERT INTO acct_credits_account (branch_id, office_id, source_fund_id, member_id, credits_id, credits_account_period, credits_account_date, credits_account_due_date, 
credits_account_amount, credits_account_principal_amount, credits_account_interest_amount, credits_account_payment_amount, 
credits_account_interest, credits_account_last_balance, credits_account_payment_to, credits_account_last_payment_date, credits_account_payment_date, payment_preference_id, created_on, created_id, credits_account_serial)
SELECT '2', '6', '5', member_id, credits_id, jk_waktu, tgl_pinjm, jt_tempo, plafon, pokok_perbulan, jasa_perbulan, total_perbulan, sk_bng, sld_pokok, 
total_angsur, tgl_trkhir_angsur, tgl_angsur_brktny, payment_preference_id, tgl_pinjm, '37', no_pinjaman
FROM migrasi_pinjaman

//potong gaji sukarela
UPDATE acct_savings_account t1, migrasi_potong_gaji t2
SET t1.savings_account_deposit_amount = t2.nominal, t1.mutation_preference_id = '2'
WHERE t1.savings_account_id = t2.savings_account_id

SELECT COUNT(*) FROM acct_deposito_account

UPDATE acct_credits_account
SET payment_type_id = 1, credits_payment_period = 1, credits_approve_status = 1

UPDATE acct_credits_account
SET credits_approve_status = 1

UPDATE migrasi_pinjaman_elektro
SET preferensi_angsuran = 3
WHERE preferensi_angsuran = "POTONG GAJI"

UPDATE migrasi_pinjaman_elektro
SET jns_pinjm = 20

UPDATE migrasi_potong_gaji t1, acct_savings_account t2
SET t1.savings_account_id = t2.savings_account_id	
WHERE t1.no_agt = t2.savings_account_no

UPDATE acct_savings_account t1, migrasi_tabungan_potonggaji t2
SET mutation_preference_id = '2', t1.savings_account_deposit_amount = t2.simp_sukarela
WHERE t1.savings_account_no = t2.no_agt AND savings_id = '34'

SELECT COUNT(*)FROM acct_savings_account WHERE mutation_preference_id = '2'

SELECT COUNT(*)FROM migrasi_tabungan_potonggaji

UPDATE migrasi_deposito t1, core_member t2
SET t1.member_id = t2.member_id
WHERE t1.no_anggota = t2.member_no 

UPDATE acct_savings_account
SET mutation_preference_id = 1, savings_account_deposit_amount = 0

UPDATE acct_deposito_account
SET created_id = '37', created_on = deposito_account_date

UPDATE core_member t1, migrasi_sicantik t2
SET t2.member_id = t1.member_id
WHERE t1.member_no = t2.no_agt

INSERT INTO core_member_working (member_id, member_no)
SELECT member_id, member_no
FROM core_member

ALTER TABLE acct_credits_account AUTO_INCREMENT = 1

UPDATE acct_credits_account
SET payment_preference_id = 3

UPDATE acct_deposito_account
SET deposito_process_last_date = "2023-11-30"

UPDATE migrasi_deposito t1, core_member t2
SET t1.member_id = t2.member_id
WHERE t1.no_anggota = t2.member_no

UPDATE migrasi_deposito t1, acct_savings_account t2
SET t1.savings_account_id = t2.savings_account_id
WHERE t1.no_tabungan = t2.savings_account_no

SELECT member_name FROM core_member WHERE member_no = 90100

SELECT COUNT(savings_account_id) FROM acct_savings_account_detail

UPDATE migrasi_deposito t1, acct_deposito t2
SET t1.deposito_id = t2.deposito_id
WHERE t1.jangka_waktu = t2.deposito_period AND t1.sk_bg = t2.deposito_interest_rate

UPDATE migrasi_deposito
SET sk_bg = suku_bunga*100

SELECT * FROM acct_deposito_profit_sharing WHERE member_id = 483

UPDATE core_member
SET branch_id = 2, province_id = 71, city_id = 1048, kecamatan_id = 14630, kelurahan_id = 302748, member_date_of_birth = "1970-01-01", member_register_date = "1970-01-01", member_mandatory_savings = 50000
WHERE member_id >= 2048

INSERT INTO core_member_working (member_id, division_id, part_id)
SELECT member_id, "79", "72"
FROM core_member
WHERE core_member.member_id >= 2048

UPDATE migrasi_tabungan
SET setoran_awal = 0
WHERE setoran_awal IS NULL

SELECT * FROM migrasi_tabungan WHERE member_no IS NULL

INSERT INTO core_member_working (member_id, division_id, part_id)
SELECT member_id, member_division_name, member_part_name
FROM core_member

UPDATE core_member_working t1, core_part t2
SET t1.part_id = t2.part_id
WHERE t1.part_id = t2.part_name

UPDATE core_member_working
SET part_id = 79
WHERE part_id = "RESIGN PH"

UPDATE migrasi_tabungan
SET tanggal_buka = "1980-01-01 00:00:00.000000"
WHERE tanggal_buka IS NULL

	SET new.member_no = nMemberNo;
	
	SET new.savings_account_no = nSavingsAccountNo;
	
SELECT COUNT(*) FROM acct_credits_account

UPDATE acct_credits_account
SET created_on = credits_account_date

UPDATE acct_credits_account
SET credits_account_serial = CONCAT('P', RIGHT(TRIM(YEAR(credits_account_date)),2), '-', MONTH(credits_account_date), credits_account_id)

UPDATE acct_credits_account
SET credits_account_serial = CONCAT('P', RIGHT(TRIM(YEAR(credits_account_date)),2), '-', RIGHT(CONCAT('00', TRIM(CAST(MONTH(credits_account_date) AS CHAR(2)))), 2), member_id)

UPDATE core_member SET member_debet_preference = 3

SELECT credits_account_serial, COUNT(credits_account_serial)
FROM acct_credits_account
GROUP BY credits_account_serial
HAVING COUNT(*) > 1

SELECT * FROM `acct_savings_account` WHERE savings_account_no = "00092" AND savings_account_no = "00149" AND savings_account_no = "00150" AND savings_account_no = "00214" AND savings_account_no = "00299" AND savings_account_no = "00417" AND savings_account_no = "00418" AND savings_account_no = "00436" AND savings_account_no = "00466" AND savings_account_no = "00471" AND savings_account_no = "00489" AND savings_account_no = "00506" AND savings_account_no = "00543" AND savings_account_no = "00546" AND savings_account_no = "00565" AND savings_account_no = "00823" AND savings_account_no = "00825" AND savings_account_no = "00829" AND savings_account_no = "00888" AND savings_account_no = "00899" AND savings_account_no = "01147" AND savings_account_no = "01246" AND savings_account_no = "01307" AND savings_account_no = "01390" AND savings_account_no = "01396" AND savings_account_no = "01944"

SELECT * FROM acct_savings_account WHERE savings_account_no = 00092 OR savings_account_no = 00149

UPDATE core_member t1, migrasi_anggota_keluar t2
SET t1.member_debet_preference = 1
WHERE t1.member_no = t2.member_no

SELECT member_no, member_debet_preference
FROM core_member

INSERT INTO acct_savings_member_detail

SELECT * FROM system_menu_mapping WHERE id_menu = "B3"

SELECT savings_account_id, savings_id, savings_account_no FROM acct_savings_account WHERE savings_account_no = "00092" OR savings_account_no = "00149"
 OR savings_account_no = "00150" OR savings_account_no = "00214" OR savings_account_no = "00299" 
 OR savings_account_no = "00417" OR savings_account_no = "00418" OR savings_account_no = "00436" OR savings_account_no = "00466" 
 OR savings_account_no = "00471" OR savings_account_no = "00489" OR savings_account_no = "00506" OR savings_account_no = "00543" 
 OR savings_account_no = "00546" OR savings_account_no = "00565" OR savings_account_no = "00823" OR savings_account_no = "00825" 
 OR savings_account_no = "00829" OR savings_account_no = "00888" OR savings_account_no = "00899" OR savings_account_no = "01147" 
 OR savings_account_no = "01246" OR savings_account_no = "01307" OR savings_account_no = "01390" OR savings_account_no = "01396" 
 OR savings_account_no = "01944"
 ORDER BY savings_account_no ASC, savings_id ASC
 02149 -> 02150
 26
 
 UPDATE migrasi_deposito t1, core_member t2
 SET t1.member_id = t2.member_id
 WHERE t1.no_anggota = t2.member_no
 
 SELECT * FROM acct_account_opening_balance WHERE account_id = 700
 
 SELECT * FROM acct_account_mutation WHERE account_id = 700
 
 SELECT * FROM acct_account_balance_detail WHERE account_id = 700
 
 SELECT * FROM acct_credits_account WHERE created_on >= "2023-11-01"
 
 SELECT * FROM trans_service_disposition WHERE service_disposition_id = 731
 
 SELECT * FROM acct_credits_account WHERE credits_account_serial = "2500007418"
 
 SELECT * FROM acct_deposito_profit_sharing WHERE MONTH(deposito_profit_sharing_due_date) = '11' AND YEAR(deposito_profit_sharing_due_date) = 2023
 
 SELECT account_income_tax_id FROM preference_company
 
 SELECT credits_account_id, method_id, bank_account_name FROM acct_credits_account
 JOIN acct_bank_account ON acct_credits_account.bank_account_id = acct_bank_account.bank_account_id
 WHERE method_id = 2
 
 SELECT deposito_account_no, deposito_account_date FROM acct_deposito_account ORDER BY RIGHT(TRIM(deposito_account_no), 3)
 
 UPDATE acct_deposito_account
 SET deposito_account_no = CONCAT('SB-', deposito_account_no)
 
 UPDATE invt_item_stock
 SET last_balance = 0
 
 INSERT INTO acct_account_balance(company_id, account_id, last_balance, created_id)
 SELECT '2', account_id, '0', '61'
 FROM acct_account
 
 UPDATE acct_account_balance_detail SET company_id = 1
 
 SELECT account_mutation_adm_id FROM preference_company
 
 INSERT INTO acct_account_balance(company_id, account_id, last_balance, created_id)
 SELECT '2', account_id, '0', '61'
 FROM acct_account
 
 SELECT account_savings_tax_id FROM preference_company
 3
 SELECT SUM(savings_tax_temp_amount) FROM acct_savings_profit_sharing_total_temp
 
 UPDATE acct_account_balance SET last_balance = 0
 
 UPDATE migrasi_pinjaman
 SET payment_preference_id = 1
 WHERE preferensi_angsuran = "Manual"
 
 SELECT COUNT(savings_account_id) FROM acct_savings_account
 
 SELECT * FROM acct_journal_voucher_item 
 WHERE journal_voucher_id >= 56234 AND journal_voucher_id <= 56560 AND account_id = 683
 
 SELECT * FROM acct_account_mutation WHERE account_id = 620
 SELECT * FROM acct_account_opening_balance WHERE account_id = 620
 
 SELECT * FROM acct_account_opening_balance WHERE account_id = 700 AND month_period = 01 AND year_period = 2024
 SELECT * FROM acct_account_opening_balance WHERE account_id = 597 AND month_period = 01 AND year_period = 2024
 SELECT * FROM acct_account_opening_balance WHERE account_id = 701 AND month_period = 01 AND year_period = 2024
 
 SELECT * FROM acct_account_mutation WHERE account_id = 701 AND month_period = 12 AND year_period = 2023161
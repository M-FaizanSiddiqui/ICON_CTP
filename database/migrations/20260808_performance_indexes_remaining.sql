-- Remaining indexes after correcting customer_payment receive_in_acc column.

ALTER TABLE customer_payment ADD INDEX idx_customer_payment_receive_in (receive_in_acc);

ALTER TABLE supplier_payment ADD INDEX idx_supplier_payment_supplier_date_status (supplier_id, payment_date, pay_status);
ALTER TABLE supplier_payment ADD INDEX idx_supplier_payment_paid_from (paid_from_acc);

ALTER TABLE vouchers ADD INDEX idx_vouchers_type_date_cancel (v_type_id, trans_dated, cancel_flag);
ALTER TABLE vouchers ADD INDEX idx_vouchers_account_date_cancel (account_id, trans_dated, cancel_flag);
ALTER TABLE vouchers ADD INDEX idx_vouchers_ref (ref_column, ref_id);
ALTER TABLE vouchers ADD INDEX idx_vouchers_no_type_cancel (voucher_no, v_type_id, cancel_flag);

ALTER TABLE accounts ADD INDEX idx_accounts_account_no (account_no);
ALTER TABLE accounts ADD INDEX idx_accounts_parent_company_del (parent_id, company_id, del_status);
ALTER TABLE accounts ADD INDEX idx_accounts_type_company_del (acc_type, company_id, del_status);

ALTER TABLE customers ADD INDEX idx_customers_acc_status (acc_id, cust_status);
ALTER TABLE suppliers ADD INDEX idx_suppliers_acc_status (acc_id, supp_status);

ALTER TABLE plate_rate_calculations ADD INDEX idx_plate_rate_plate_del_qty (prc_plate_id, del_status, prc_qty);

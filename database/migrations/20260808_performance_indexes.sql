-- ICON CTP performance indexes
-- Safe to run once on production. If an index already exists, skip that line manually.

ALTER TABLE job_order ADD INDEX idx_job_order_customer_date_status (customer_id, order_rec_date, order_status, del_status);
ALTER TABLE job_order ADD INDEX idx_job_order_status_del (order_status, del_status);
ALTER TABLE job_order ADD INDEX idx_job_order_rec_date (order_rec_date);

ALTER TABLE job_order_details ADD INDEX idx_job_details_job_delete (job_id, delete_status);
ALTER TABLE job_order_details ADD INDEX idx_job_details_item_delete (item_id, delete_status);

ALTER TABLE inventory_audit ADD INDEX idx_inventory_audit_item_date (item_id, dated);
ALTER TABLE inventory_audit ADD INDEX idx_inventory_audit_ref (ref_column, ref_id);

ALTER TABLE inventoty_received_details ADD INDEX idx_inv_recv_supplier_date_status (supplier_id, received_date, status);
ALTER TABLE inventoty_received_details ADD INDEX idx_inv_recv_item_date_status (item_id, received_date, status);
ALTER TABLE inventoty_received_details ADD INDEX idx_inv_recv_ir (ir_id);

ALTER TABLE customer_payment ADD INDEX idx_customer_payment_customer_date_status (customer_id, payment_date, pay_status);
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

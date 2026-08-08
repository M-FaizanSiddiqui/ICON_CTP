-- Role-based permissions.
-- Keeps old module_permision table as legacy/direct overrides.

CREATE TABLE IF NOT EXISTS roles (
	role_id INT AUTO_INCREMENT PRIMARY KEY,
	role_name VARCHAR(80) NOT NULL UNIQUE,
	role_slug VARCHAR(80) NOT NULL UNIQUE,
	role_desc VARCHAR(255) NOT NULL DEFAULT '',
	status TINYINT NOT NULL DEFAULT 0,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS role_permissions (
	rp_id INT AUTO_INCREMENT PRIMARY KEY,
	role_id INT NOT NULL,
	mod_id INT NOT NULL,
	UNIQUE KEY uq_role_module (role_id, mod_id),
	INDEX idx_role_permissions_role (role_id),
	INDEX idx_role_permissions_mod (mod_id)
);

CREATE TABLE IF NOT EXISTS user_roles (
	ur_id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	role_id INT NOT NULL,
	UNIQUE KEY uq_user_role (user_id, role_id),
	INDEX idx_user_roles_user (user_id),
	INDEX idx_user_roles_role (role_id)
);

INSERT IGNORE INTO roles (role_name,role_slug,role_desc) VALUES
('Admin','admin','Full system access'),
('Accounts','accounts','Accounting, reports, receipts and ledgers'),
('Sales','sales','Customers, jobs and sales reporting'),
('Inventory','inventory','Stock, supplier and inventory operations'),
('HR','hr','Employees, attendance and salary slips');

INSERT INTO modules_1 (m_name,m_url,m_parent_id,fav_icon,ordering,heading,show_in_menu)
SELECT 'Activity Log','SystemUser/activity-log',51,'fa fa-history',99,0,1
WHERE NOT EXISTS (SELECT 1 FROM modules_1 WHERE m_url='SystemUser/activity-log');

INSERT INTO modules_1 (m_name,m_url,m_parent_id,fav_icon,ordering,heading,show_in_menu)
SELECT 'Report Center','Reports/report-center',42,'fa fa-chart-pie',1,0,1
WHERE NOT EXISTS (SELECT 1 FROM modules_1 WHERE m_url='Reports/report-center');

INSERT INTO modules_1 (m_name,m_url,m_parent_id,fav_icon,ordering,heading,show_in_menu)
SELECT 'User Roles','Modules/user-roles',51,'fa fa-users-cog',55,0,1
WHERE NOT EXISTS (SELECT 1 FROM modules_1 WHERE m_url='Modules/user-roles');

INSERT IGNORE INTO role_permissions (role_id,mod_id)
SELECT r.role_id,m.m_id FROM roles r CROSS JOIN modules_1 m WHERE r.role_slug='admin';

INSERT IGNORE INTO role_permissions (role_id,mod_id)
SELECT r.role_id,m.m_id FROM roles r JOIN modules_1 m ON m.m_id IN (34,35,36,37,38,39,40,42,43,44,45,46,48,66,67,68,69,70,71,73,78,79,80,802)
WHERE r.role_slug='accounts';

INSERT IGNORE INTO role_permissions (role_id,mod_id)
SELECT r.role_id,m.m_id FROM roles r JOIN modules_1 m ON m.m_id IN (2,6,7,8,11,12,13,14,15,29,30,31,41,42,43,44,46,48,802)
WHERE r.role_slug='sales';

INSERT IGNORE INTO role_permissions (role_id,mod_id)
SELECT r.role_id,m.m_id FROM roles r JOIN modules_1 m ON m.m_id IN (3,4,5,11,12,13,20,21,22,23,24,25,26,27,28,74,802)
WHERE r.role_slug='inventory';

INSERT IGNORE INTO role_permissions (role_id,mod_id)
SELECT r.role_id,m.m_id FROM roles r JOIN modules_1 m ON m.m_id IN (59,60,61,62,63,64,65,72,75,76,77)
WHERE r.role_slug='hr';

INSERT IGNORE INTO user_roles (user_id,role_id)
SELECT u.id,r.role_id FROM users u JOIN roles r ON r.role_slug='admin';

INSERT IGNORE INTO user_roles (user_id,role_id)
SELECT u.id,r.role_id FROM users u JOIN roles r ON r.role_slug='accounts'
WHERE LOWER(u.name) LIKE '%asif%' OR LOWER(u.username) LIKE '%asif%';

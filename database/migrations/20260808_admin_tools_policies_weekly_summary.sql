-- Admin tools, policies CRUD and weekly WhatsApp summary modules.
-- Safe to run multiple times.

INSERT INTO modules_1 (m_name, m_url, m_parent_id, fav_icon, ordering, heading, show_in_menu)
SELECT 'Data Health', 'SystemUser/data-health', 39, 'fa fa-heartbeat', 3, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM modules_1 WHERE m_url = 'SystemUser/data-health');

INSERT INTO modules_1 (m_name, m_url, m_parent_id, fav_icon, ordering, heading, show_in_menu)
SELECT 'Error Logs', 'SystemUser/error-logs', 39, 'fa fa-bug', 4, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM modules_1 WHERE m_url = 'SystemUser/error-logs');

INSERT INTO modules_1 (m_name, m_url, m_parent_id, fav_icon, ordering, heading, show_in_menu)
SELECT 'Policies', 'SystemUser/policies', 39, 'fa fa-clipboard', 5, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM modules_1 WHERE m_url = 'SystemUser/policies');

INSERT INTO modules_1 (m_name, m_url, m_parent_id, fav_icon, ordering, heading, show_in_menu)
SELECT 'Weekly Summary', 'Reports/weekly-summary', 42, 'fab fa-whatsapp', 9, 0, 1
WHERE NOT EXISTS (SELECT 1 FROM modules_1 WHERE m_url = 'Reports/weekly-summary');

-- Admin role can access all new screens.
INSERT INTO role_permissions (role_id, mod_id)
SELECT r.role_id, m.m_id
FROM roles r
JOIN modules_1 m ON m.m_url IN ('SystemUser/data-health','SystemUser/error-logs','SystemUser/policies','Reports/weekly-summary')
WHERE r.role_slug = 'admin'
AND NOT EXISTS (
	SELECT 1 FROM role_permissions rp WHERE rp.role_id = r.role_id AND rp.mod_id = m.m_id
);

-- Accounts role can access weekly summary.
INSERT INTO role_permissions (role_id, mod_id)
SELECT r.role_id, m.m_id
FROM roles r
JOIN modules_1 m ON m.m_url = 'Reports/weekly-summary'
WHERE r.role_slug = 'accounts'
AND NOT EXISTS (
	SELECT 1 FROM role_permissions rp WHERE rp.role_id = r.role_id AND rp.mod_id = m.m_id
);

-- Legacy direct permission for Faizan user.
INSERT INTO module_permision (mod_id, user_id)
SELECT m.m_id, 1
FROM modules_1 m
WHERE m.m_url IN ('SystemUser/data-health','SystemUser/error-logs','SystemUser/policies','Reports/weekly-summary')
AND NOT EXISTS (
	SELECT 1 FROM module_permision mp WHERE mp.mod_id = m.m_id AND mp.user_id = 1
);

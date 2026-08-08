-- Adds Activity Log and Report Center modules.
-- Replace user_id 1 if your admin user is different.

INSERT INTO modules_1 (m_name,m_url,m_parent_id,fav_icon,ordering,heading,show_in_menu)
SELECT 'Activity Log','SystemUser/activity-log',51,'fa fa-history',99,0,1
WHERE NOT EXISTS (SELECT 1 FROM modules_1 WHERE m_url='SystemUser/activity-log');

INSERT INTO modules_1 (m_name,m_url,m_parent_id,fav_icon,ordering,heading,show_in_menu)
SELECT 'Report Center','Reports/report-center',42,'fa fa-chart-pie',1,0,1
WHERE NOT EXISTS (SELECT 1 FROM modules_1 WHERE m_url='Reports/report-center');

INSERT INTO module_permision (mod_id,user_id)
SELECT m.m_id,1
FROM modules_1 m
WHERE m.m_url IN ('SystemUser/activity-log','Reports/report-center')
AND NOT EXISTS (
	SELECT 1 FROM module_permision p WHERE p.mod_id=m.m_id AND p.user_id=1
);

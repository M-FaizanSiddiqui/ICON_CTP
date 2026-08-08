-- Convert System User into a dropdown and place Activity Log inside it.

UPDATE modules_1
SET heading=1, m_url='', fav_icon='fa fa-users-cog'
WHERE m_id=39;

INSERT INTO modules_1 (m_name,m_url,m_parent_id,fav_icon,ordering,heading,show_in_menu)
SELECT 'Users','SystemUser/users',39,'fa fa-users',1,0,1
WHERE NOT EXISTS (
	SELECT 1 FROM modules_1 WHERE m_url='SystemUser/users' AND m_parent_id=39
);

UPDATE modules_1
SET m_parent_id=39, ordering=2, fav_icon='fa fa-history', show_in_menu=1
WHERE m_url='SystemUser/activity-log';

INSERT IGNORE INTO role_permissions (role_id,mod_id)
SELECT r.role_id,m.m_id
FROM roles r
JOIN modules_1 m ON (m.m_id=39 OR m.m_parent_id=39)
WHERE r.role_slug='admin';

INSERT IGNORE INTO module_permision (mod_id,user_id)
SELECT m.m_id,1
FROM modules_1 m
WHERE m.m_id=39 OR m.m_parent_id=39;

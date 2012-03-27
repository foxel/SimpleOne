update qfso_objects_navi set path = substring(path, 2) where path like '/%';

update qfso_objects set parent_id = null where parent_id IN (select id from qfso_objects_navi where path = '');

update qfso_objects_navi set path = 'index' where path = '';

update qfso_objects_navi set path_hash = md5(path);
DROP TRIGGER IF EXISTS qfso_objects_navi_hash_insert;

DROP TRIGGER IF EXISTS qfso_objects_navi_hash_update;

CREATE TRIGGER qfso_objects_navi_hash_insert BEFORE INSERT ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path);

CREATE TRIGGER qfso_objects_navi_hash_update BEFORE UPDATE ON qfso_objects_navi FOR EACH ROW SET NEW.path_hash = MD5(NEW.path);

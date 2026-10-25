-- Copyright (C) 2026		Nicolas SILOBRE				<contact@ns-info.fr>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_nssuiviprod_siteplugin(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128), 
	label varchar(255), 
	description text, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	fk_site integer NOT NULL, 
	fk_product integer, 
	version varchar(64), 
	entity integer DEFAULT 1 NOT NULL, 
	module_name varchar(128), 
	module_class varchar(128), 
	module_number integer, 
	module_description text, 
	publisher varchar(255), 
	publisher_url varchar(255), 
	family varchar(64), 
	module_enabled integer, 
	activation_date datetime, 
	module_path varchar(255), 
	version_file varchar(64), 
	has_changelog integer, 
	need_dolibarr_version varchar(32), 
	php_min varchar(32), 
	depends text, 
	required_by text, 
	conflict_with text, 
	setup_page varchar(255), 
	lang_files text, 
	url_last_version varchar(255), 
	nb_rights integer, 
	nb_menus integer, 
	nb_tabs integer, 
	nb_dictionaries integer, 
	nb_widgets integer, 
	nb_cron_jobs integer, 
	has_triggers integer, 
	has_models integer, 
	hooks text, 
	css text, 
	js text, 
	json_date datetime, 
	rental integer DEFAULT 0 NOT NULL, 
	dolistore_id integer, 
	dolistore_version varchar(64), 
	dolistore_date datetime, 
	dolistore_label varchar(255), 
	fk_siteurl integer NOT NULL, 
	dolistore_ref varchar(128), 
	fk_module integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

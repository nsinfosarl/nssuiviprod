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

CREATE TABLE llx_nssuiviprod_pluginmodule(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	entity integer DEFAULT 1 NOT NULL, 
	ref varchar(128) NOT NULL, 
	label varchar(255), 
	fk_product integer, 
	module_class varchar(128), 
	module_number integer, 
	module_description text, 
	publisher varchar(255), 
	publisher_url varchar(255), 
	family varchar(64), 
	url_last_version varchar(255), 
	dolistore_id integer, 
	dolistore_ref varchar(128), 
	dolistore_label varchar(255), 
	dolistore_version varchar(64), 
	dolistore_date datetime, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14)
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

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

-- Upgrade of the tables of a module already installed (run at each activation of the module: Dolibarr ignores
-- the errors "column already exists"). Add here an ALTER TABLE for each field added after the first installation.

-- 1.0.0: complement of the URLs (advanced text)
ALTER TABLE llx_nssuiviprod_siteurl ADD COLUMN complement text;

-- 1.0.0: fields of the JSON file of the module nslistmodexterne (import of the plugins)
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN module_name varchar(128);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN module_class varchar(128);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN module_number integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN module_description text;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN publisher varchar(255);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN publisher_url varchar(255);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN family varchar(64);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN module_enabled integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN activation_date datetime;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN module_path varchar(255);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN version_file varchar(64);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN has_changelog integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN need_dolibarr_version varchar(32);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN php_min varchar(32);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN depends text;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN required_by text;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN conflict_with text;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN setup_page varchar(255);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN lang_files text;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN url_last_version varchar(255);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN nb_rights integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN nb_menus integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN nb_tabs integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN nb_dictionaries integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN nb_widgets integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN nb_cron_jobs integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN has_triggers integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN has_models integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN hooks text;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN css text;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN js text;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN json_date datetime;

-- 1.0.0: environment of the URLs (1=production, 2=development, 3=sandbox)
ALTER TABLE llx_nssuiviprod_siteurl ADD COLUMN environment integer DEFAULT 1 NOT NULL;

-- 1.0.0: plugins rented or not
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN rental integer DEFAULT 0 NOT NULL;

-- 1.0.0: current version of the plugins on the Dolistore
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN dolistore_id integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN dolistore_version varchar(64);
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN dolistore_date datetime;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN dolistore_label varchar(255);

-- 1.0.0: plugins installed on an URL of the site (instance)
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN fk_siteurl integer;

-- 1.0.0: date of the last check on the URLs (instances), no more on the sites
ALTER TABLE llx_nssuiviprod_siteurl ADD COLUMN date_check date;

-- 1.0.0: order of the URLs of a site
ALTER TABLE llx_nssuiviprod_siteurl ADD COLUMN rang integer DEFAULT 0 NOT NULL;
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN dolistore_ref varchar(128);

-- URL label optional (the tabs of the plugins show the icon of the environment)
ALTER TABLE llx_nssuiviprod_siteurl MODIFY COLUMN label varchar(255) NULL;

-- 23.0.1: installed version of the URLs as a number (x.y.z), compared to the current version of the type of site of the
-- setup; the versions referential is removed: the number of the former version of each URL is kept
ALTER TABLE llx_nssuiviprod_siteurl ADD COLUMN version varchar(64);
UPDATE llx_nssuiviprod_siteurl SET version = (SELECT v.ref FROM llx_nssuiviprod_siteversion as v WHERE v.rowid = llx_nssuiviprod_siteurl.fk_siteversion) WHERE (version IS NULL OR version = '') AND fk_siteversion > 0;

-- 23.0.2: modules unique (table llx_nssuiviprod_pluginmodule): identity and Dolistore information of each module once,
-- the plugins of the URLs are linked to it (fk_module). The data are migrated by PluginModule::migrateFromPlugins(),
-- called by the activation of the module (init() of the descriptor). The former columns of the plugins are kept, no
-- more used.
ALTER TABLE llx_nssuiviprod_siteplugin ADD COLUMN fk_module integer;
ALTER TABLE llx_nssuiviprod_siteplugin ADD INDEX idx_nssuiviprod_siteplugin_fk_module (fk_module);

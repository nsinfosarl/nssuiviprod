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


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_nssuiviprod_siteurl ADD INDEX idx_nssuiviprod_siteurl_rowid (rowid);
ALTER TABLE llx_nssuiviprod_siteurl ADD INDEX idx_nssuiviprod_siteurl_fk_site (fk_site);
ALTER TABLE llx_nssuiviprod_siteurl ADD INDEX idx_nssuiviprod_siteurl_fk_typeurl (fk_typeurl);
ALTER TABLE llx_nssuiviprod_siteurl ADD INDEX idx_nssuiviprod_siteurl_entity (entity);
ALTER TABLE llx_nssuiviprod_siteurl ADD INDEX idx_nssuiviprod_siteurl_environment (environment);
-- END MODULEBUILDER INDEXES

-- Hors balises MODULEBUILDER (non regenere par ModuleBuilder)
ALTER TABLE llx_nssuiviprod_siteurl ADD INDEX idx_nssuiviprod_siteurl_ref (ref);
ALTER TABLE llx_nssuiviprod_siteurl ADD CONSTRAINT llx_nssuiviprod_siteurl_fk_site FOREIGN KEY (fk_site) REFERENCES llx_nssuiviprod_site(rowid);

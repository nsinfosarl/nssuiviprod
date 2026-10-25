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

-- Initial values of the dictionaries for the current entity (ignored if the code already exists)
INSERT IGNORE INTO llx_c_nssuiviprod_typesite (entity, code, label, position, active) VALUES (__ENTITY__, 'DOLIBARR', 'Dolibarr', 10, 1);
INSERT IGNORE INTO llx_c_nssuiviprod_typesite (entity, code, label, position, active) VALUES (__ENTITY__, 'WORDPRESS', 'WordPress', 20, 1);
INSERT IGNORE INTO llx_c_nssuiviprod_typesite (entity, code, label, position, active) VALUES (__ENTITY__, 'PRESTASHOP', 'PrestaShop', 30, 1);
INSERT IGNORE INTO llx_c_nssuiviprod_typesite (entity, code, label, position, active) VALUES (__ENTITY__, 'OTHER', 'Autre', 90, 1);

INSERT IGNORE INTO llx_c_nssuiviprod_typeurl (entity, code, label, position, active) VALUES (__ENTITY__, 'DIRECT', 'Accès direct', 10, 1);
INSERT IGNORE INTO llx_c_nssuiviprod_typeurl (entity, code, label, position, active) VALUES (__ENTITY__, 'ADMIN', 'Administration', 20, 1);
INSERT IGNORE INTO llx_c_nssuiviprod_typeurl (entity, code, label, position, active) VALUES (__ENTITY__, 'API', 'API', 30, 1);
INSERT IGNORE INTO llx_c_nssuiviprod_typeurl (entity, code, label, position, active) VALUES (__ENTITY__, 'OTHER', 'Autre', 90, 1);

-- Copyright (C) ---Put here your own copyright and developer email---
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
-- along with this program.  If not, see http://www.gnu.org/licenses/.

CREATE TABLE llx_consignes_consigne(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_soc integer, 
	tms timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	date_creation datetime, 
	import_key varchar(14), 
	fk_product integer NOT NULL, 
	qty integer NOT NULL, 
	fk_shipping integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
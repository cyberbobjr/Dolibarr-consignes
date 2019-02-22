-- Copyright (C) 2018 SuperAdmin <contact@h2eauassistance.com>
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
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

INSERT INTO `llx_extrafields` (`name`, `entity`, `elementtype`, `tms`, `label`, `type`, `size`, `fieldunique`, `fieldrequired`, `perms`, `list`, `pos`, `alwayseditable`, `param`, `ishidden`, `fieldcomputed`, `fielddefault`, `langs`, `fk_user_author`, `fk_user_modif`, `datec`, `enabled`)
VALUES
	('isconsigne', 1, 'product', '2018-10-27 17:01:53', 'Produit consigné ?', 'boolean', '', 0, 0, NULL, '1', 100, 1, 'a:1:{s:7:\"options\";a:1:{s:0:\"\";N;}}', 0, NULL, NULL, NULL, 1, 1, '2018-10-27 17:01:53', '1');

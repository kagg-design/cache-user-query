# WordPress Plugin Cache User Query

Cache User Query allows to cache user query in the WordPress Core to improve performance on site with big number of users.

# Description

This is a must-use plugin (mu-plugin) which shall reside in mu-plugins folder.

On test site with 250,000 users it reduced `wp-admin/edit.php` loading time by 75%: from 10s to 2.5s.

## Usage

Just install it as described below.

## Installation

In `wp-content/mu-plugins` folder:
```
git clone https://github.com/OnTheGoSystems/cache-user-query
cd cache-user-query
composer install --no-dev
cp cache-user-query.php ..
```

## Development

In `wp-content/mu-plugins` folder:
```
git clone https://github.com/OnTheGoSystems/cache-user-query
cd cache-user-query
composer install
cp cache-user-query.php ..
```

## License

The WordPress Plugin Disable Plugins is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named `LICENSE`.

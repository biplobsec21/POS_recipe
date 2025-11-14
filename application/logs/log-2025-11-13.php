<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2025-11-13 08:04:55 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 08:06:14 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 14:06:25 --> Could not find the language line "company_address"
ERROR - 2025-11-13 14:06:26 --> Severity: error --> Exception: ext-gd not loaded /var/www/html/vendor/chillerlan/php-qrcode/src/Output/QRImage.php 63
ERROR - 2025-11-13 08:16:21 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 08:16:22 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 08:21:29 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 08:21:29 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 08:21:33 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 08:21:33 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 08:21:48 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 08:21:48 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 08:22:23 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 08:22:23 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 08:23:01 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 08:23:01 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 14:23:22 --> Query error: MySQL server has gone away - Invalid query: 
			SELECT 
				COALESCE(SUM(s.grand_total), 0) as total_sales,
				COALESCE(SUM(sp.payment), 0) as total_payments
			FROM db_customers c
			LEFT JOIN db_sales s ON c.id = s.customer_id AND s.status = 1
			LEFT JOIN db_salespayments sp ON s.id = sp.sales_id AND sp.status = 1
			WHERE c.id = '1'
			GROUP BY c.id
		
ERROR - 2025-11-13 08:23:22 --> Query error: MySQL server has gone away - Invalid query: SELECT GET_LOCK('b120349dee9c21ad8a30f138a264f300', 300) AS ci_session_lock
ERROR - 2025-11-13 14:23:22 --> Severity: error --> Exception: Call to a member function num_rows() on bool /var/www/html/application/models/Customers_model.php 36
ERROR - 2025-11-13 08:23:22 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/system/libraries/Session/drivers/Session_database_driver.php 384
ERROR - 2025-11-13 14:23:22 --> Query error: MySQL server has gone away - Invalid query: UPDATE `ci_sessions` SET `timestamp` = 1763022202
WHERE `id` = '63feb279a6a1d8c791693141684495d7bb77e0f9'
ERROR - 2025-11-13 14:23:22 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 14:23:22 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 14:23:22 --> Query error: MySQL server has gone away - Invalid query: SELECT RELEASE_LOCK('b120349dee9c21ad8a30f138a264f300') AS ci_session_lock
ERROR - 2025-11-13 08:25:56 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 08:25:56 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 08:30:20 --> Query error: MySQL server has gone away - Invalid query: SELECT GET_LOCK('a8ebf9f00ba2bfbd1d8c6fbec79a523b', 300) AS ci_session_lock
ERROR - 2025-11-13 08:30:20 --> Query error: MySQL server has gone away - Invalid query: SELECT GET_LOCK('a8ebf9f00ba2bfbd1d8c6fbec79a523b', 300) AS ci_session_lock
ERROR - 2025-11-13 08:30:20 --> Query error: MySQL server has gone away - Invalid query: SELECT GET_LOCK('a8ebf9f00ba2bfbd1d8c6fbec79a523b', 300) AS ci_session_lock
ERROR - 2025-11-13 08:30:20 --> Query error: MySQL server has gone away - Invalid query: SELECT GET_LOCK('a8ebf9f00ba2bfbd1d8c6fbec79a523b', 300) AS ci_session_lock
ERROR - 2025-11-13 08:30:20 --> Query error: MySQL server has gone away - Invalid query: SELECT GET_LOCK('a8ebf9f00ba2bfbd1d8c6fbec79a523b', 300) AS ci_session_lock
ERROR - 2025-11-13 14:30:20 --> Query error: MySQL server has gone away - Invalid query: 
			SELECT 
				c.customer_code,
				c.customer_name,
				c.sales_due as stored_sales_due,
				COALESCE(SUM(s.grand_total), 0) as total_sales,
				COALESCE(SUM(sp.payment), 0) as total_payments,
				(COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(sp.payment), 0)) as calculated_sales_due
			FROM db_customers c
			LEFT JOIN db_sales s ON c.id = s.customer_id AND s.status = 1
			LEFT JOIN db_salespayments sp ON s.id = sp.sales_id AND sp.status = 1
			WHERE c.id = '1'
			GROUP BY c.id
		
ERROR - 2025-11-13 08:30:20 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/system/libraries/Session/drivers/Session_database_driver.php 384
ERROR - 2025-11-13 08:30:20 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/system/libraries/Session/drivers/Session_database_driver.php 384
ERROR - 2025-11-13 08:30:21 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/system/libraries/Session/drivers/Session_database_driver.php 384
ERROR - 2025-11-13 08:30:21 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/system/libraries/Session/drivers/Session_database_driver.php 384
ERROR - 2025-11-13 08:30:21 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/system/libraries/Session/drivers/Session_database_driver.php 384
ERROR - 2025-11-13 14:30:21 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/application/models/Customers_model.php 94
ERROR - 2025-11-13 14:30:21 --> Query error: MySQL server has gone away - Invalid query: UPDATE `ci_sessions` SET `timestamp` = 1763022621
WHERE `id` = 'cfad9343864fe7d4c4f130ee8a5b678c8540d8a2'
ERROR - 2025-11-13 14:30:21 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 14:30:21 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 14:30:21 --> Query error: MySQL server has gone away - Invalid query: SELECT RELEASE_LOCK('a8ebf9f00ba2bfbd1d8c6fbec79a523b') AS ci_session_lock
ERROR - 2025-11-13 08:30:30 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 08:30:31 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 08:41:41 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 08:41:41 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 08:44:11 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 08:44:11 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 08:47:33 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 08:49:53 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 08:56:54 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 08:56:54 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 08:59:22 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 08:59:22 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 08:59:24 --> 404 Page Not Found: Faviconico/index
ERROR - 2025-11-13 09:09:06 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 09:09:06 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 09:09:45 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 09:09:48 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 09:09:58 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 09:10:01 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 15:10:06 --> Query error: MySQL server has gone away - Invalid query: 
			SELECT 
				c.customer_code,
				c.customer_name,
				c.sales_due as stored_sales_due,
				COALESCE(SUM(s.grand_total), 0) as total_sales,
				COALESCE(SUM(sp.payment), 0) as total_payments,
				(COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(sp.payment), 0)) as calculated_sales_due
			FROM db_customers c
			LEFT JOIN db_sales s ON c.id = s.customer_id AND s.status = 1
			LEFT JOIN db_salespayments sp ON s.id = sp.sales_id AND sp.status = 1
			WHERE c.id = '1'
			GROUP BY c.id
		
ERROR - 2025-11-13 15:10:07 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/application/models/Customers_model.php 94
ERROR - 2025-11-13 15:10:07 --> Query error: MySQL server has gone away - Invalid query: UPDATE `ci_sessions` SET `timestamp` = 1763025007
WHERE `id` = 'aae005cad58fd273aa7a039dd369c1a8a118ae50'
ERROR - 2025-11-13 15:10:07 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-13 15:10:07 --> Severity: Warning --> session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) Unknown 0
ERROR - 2025-11-13 15:10:07 --> Query error: MySQL server has gone away - Invalid query: SELECT RELEASE_LOCK('9ff06d0cee7c72ed3b0874f48b4c8ad1') AS ci_session_lock
ERROR - 2025-11-13 09:11:01 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 09:11:01 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 17:39:37 --> Severity: error --> Exception: Too few arguments to function Sales::invoice(), 0 passed in /var/www/html/system/core/CodeIgniter.php on line 532 and exactly 1 expected /var/www/html/application/controllers/Sales.php 224
ERROR - 2025-11-13 12:03:49 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 12:03:49 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 18:17:42 --> Cache: Failed to initialize APC; extension not loaded/enabled?
ERROR - 2025-11-13 12:17:42 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 18:17:54 --> Cache: Failed to initialize APC; extension not loaded/enabled?
ERROR - 2025-11-13 12:17:55 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 18:18:30 --> Cache: Failed to initialize APC; extension not loaded/enabled?
ERROR - 2025-11-13 12:18:31 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:18:42 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:18:42 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 18:18:46 --> Cache: Failed to initialize APC; extension not loaded/enabled?
ERROR - 2025-11-13 18:18:50 --> Cache: Failed to initialize APC; extension not loaded/enabled?
ERROR - 2025-11-13 18:18:51 --> Cache: Failed to initialize APC; extension not loaded/enabled?
ERROR - 2025-11-13 12:26:20 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:27:08 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:27:08 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 12:27:37 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:28:24 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:28:33 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:28:33 --> 404 Page Not Found: Purchase/bootstrap.min.css.map
ERROR - 2025-11-13 12:28:47 --> 404 Page Not Found: Purchase/bootstrap.min.css.map
ERROR - 2025-11-13 12:28:47 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:29:03 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 12:29:06 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:29:14 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:29:39 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:29:46 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:30:02 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 12:30:05 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:30:18 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:30:50 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:31:27 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:31:50 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:31:54 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:31:57 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:32:25 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:32:26 --> 404 Page Not Found: Purchase/bootstrap.min.css.map
ERROR - 2025-11-13 12:32:41 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:32:55 --> 404 Page Not Found: Faviconico/index
ERROR - 2025-11-13 12:33:22 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:33:23 --> 404 Page Not Found: Theme/plugins
ERROR - 2025-11-13 12:33:27 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:33:27 --> 404 Page Not Found: Bootstrapmincssmap/index
ERROR - 2025-11-13 12:33:54 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:33:54 --> 404 Page Not Found: Bootstrapmincssmap/index
ERROR - 2025-11-13 12:34:13 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:34:13 --> 404 Page Not Found: Bootstrapmincssmap/index
ERROR - 2025-11-13 12:34:16 --> 404 Page Not Found: Bootstrapmincssmap/index
ERROR - 2025-11-13 12:34:16 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:34:20 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:34:20 --> 404 Page Not Found: Sales/bootstrap.min.css.map
ERROR - 2025-11-13 12:34:20 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:34:20 --> 404 Page Not Found: Purchase/bootstrap.min.css.map
ERROR - 2025-11-13 12:34:23 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:34:23 --> 404 Page Not Found: Customers/bootstrap.min.css.map
ERROR - 2025-11-13 12:34:23 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:34:23 --> 404 Page Not Found: Suppliers/bootstrap.min.css.map
ERROR - 2025-11-13 12:34:24 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 12:34:24 --> 404 Page Not Found: Reports/bootstrap.min.css.map
ERROR - 2025-11-13 12:34:25 --> 404 Page Not Found: Expense/bootstrap.min.css.map
ERROR - 2025-11-13 12:34:25 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 18:34:26 --> Could not find the language line "add_recipe"
ERROR - 2025-11-13 18:34:26 --> Could not find the language line "recipes_list"
ERROR - 2025-11-13 12:34:26 --> 404 Page Not Found: Expense/bootstrap.min.css.map
ERROR - 2025-11-13 12:34:26 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2025-11-13 18:34:56 --> Could not find the language line "add_recipe"
ERROR - 2025-11-13 18:34:56 --> Could not find the language line "recipes_list"
ERROR - 2025-11-13 18:35:01 --> Could not find the language line "add_recipe"
ERROR - 2025-11-13 18:35:01 --> Could not find the language line "recipes_list"
ERROR - 2025-11-13 18:35:03 --> Could not find the language line "add_recipe"
ERROR - 2025-11-13 18:35:03 --> Could not find the language line "recipes_list"
ERROR - 2025-11-13 18:35:08 --> Could not find the language line "add_recipe"
ERROR - 2025-11-13 18:35:08 --> Could not find the language line "recipes_list"
ERROR - 2025-11-13 18:35:08 --> Could not find the language line "previous_due"

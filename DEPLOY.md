# Sector7xNoida - new site: deploy guide (cPanel)

1. cPanel mein domain/account ban jaye (document root = /public_html/sector7xnoida.com ya naye account ka public_html).
2. File Manager > us folder mein `sector7xnoida-new-site.zip` upload karo > Extract.
   (`.htaccess` hidden file hai - File Manager > Settings > "Show Hidden Files" ON rakho.)
3. cPanel > MySQL Databases: naya database + user banao, user ko All Privileges do.
4. cPanel > phpMyAdmin > apna database chuno > Import:
   a) pehle `database/schema.sql`  b) phir `database/data.sql`
5. `inc/config.php` kholo (File Manager > Edit) aur db name / user / password bharo.
6. Browser mein site kholo. Sab theek ho to GoDaddy DNS ka A record VPS IP (68.178.174.53) pe point karo.
7. SSL chalu hone ke baad `.htaccess` mein HTTPS wali commented lines uncomment kar do.

Login: `inc/config.php` > auth. Numbers sirf login ke baad dikhane ho to `require_login_for_contact` => true.
Data update karna ho to phpMyAdmin mein tables `providers`, `provider_phones`, `society_providers` edit karo.

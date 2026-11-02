Practical 12 - Event Management CRUD with Poster Upload and File Validation
===========================================================================
SETUP (XAMPP)
1. Start Apache and MySQL in the XAMPP Control Panel.
2. Copy this folder to  C:\xampp\htdocs\event-system\
3. Open http://localhost/phpmyadmin -> SQL tab -> paste schema.sql -> Go.
4. Open http://localhost/event-system/  (you will be sent to the login page)
5. Login:  admin / admin123

FILES
db.php         database connection
functions.php  shared helpers (session, flash messages, validation, upload)
login.php      admin login (password_verify, session_regenerate_id)
logout.php     ends the session
index.php      Create + Read (search, category and status filters) + Delete + Open/Close
edit.php       Update (poster optional)
schema.sql     database, tables, sample data
uploads/       saved posters (.htaccess blocks script execution here)

NOTES
- Bootstrap loads from a CDN, so the lab PCs need internet. For offline use,
  download bootstrap.min.css / bootstrap.bundle.min.js and change the 2 links.
- If big uploads fail, check upload_max_filesize and post_max_size in php.ini.

DOCUMENTATION MYLOLOID
By Alifio Rasendriya Rasyid, Jason Sianandar

How to Deploy Locally:
- Create MySQL database
- Configure .env file to connect with your database
- php artisan key:generate
- php artisan migrate:rollback
- php artisan migrate --seed
- php artisan serve

Default Login Information
Admin:
	- email: admin@test.com
	- password: password
Client:
	- email: user@test.com
	- password: password
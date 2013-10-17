# Quick notes for installation

- Create a directory on your hard drive, we'll assume it's at $SYMFONY
- Go in it  `cd $SYMFONY`
- Fork the repo and clone it in $SYMFONY: `git clone https://github.com/yourname/netrunnerdb`
- Configure your local webserver with a DocumentRoot pointing to $SYMFONY (vhost probably)
- Copy `app/config/parameters.yml.dist` as `app/config/parameters.yml` and edit it
- Create the database in MySQL
- Create the tables: `php app/console doctrine:schema:update --force`
- Import the data from the latest `./netrunnerdb-*.sql.gz` into MySQL
- Install the assets: `php app/console assets:install --symlink`
- Point your browser to `/web/app_dev.php` ; the page should load without card images

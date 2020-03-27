# The Base Package


### The Base package installation

1) `laravel new sendportal_host`.

2) `git clone git@github.com:JonoB/base.git sendportal_base`.

3) Edit the `sendportal_host` composer.json file and add the following lines:
```json
"sendportal/base": "dev-master"
// ...
"repositories": [
    {
        "type": "path",
        "symlink": true,
        "url": "../sendportal_base"
    }
],
```
4) `cd sendportal_host && composer update`.

5) Edit the `sendportal_host` `.env` file and adjust it accordingly (DB connection, default mail driver).

6) Edit the `sendportal_host` `config/auth.php` file and change the `providers.users.model`

to `\Sendportal\Base\Models\User::class,`.

7) `php artisan migrate`.

8) `php artisan vendor:publish --provider=Sendportal\\Base\\SendportalBaseServiceProvider --tag=sendportal-assets`.

### The Pro package installation

1) Install the Base package first.

2) `git clone git@github.com:JonoB/sendportal-pro.git`.

3) Edit the `sendportal_host` composer.json file and add the following lines:
```json
"sendportal/pro": "dev-master"
// ...
"repositories": [
    {
        "type": "path",
        "symlink": true,
        "url": "../sendportal_pro"
    }
],
```
4) Run `composer update` for the host app.

5) `php artisan migrate`.

6) Refresh the page in your browser.


---

## Release new version

- If CSS or JS files have been changed we need to recompile them: `npm run prod`
- In the main repository we have to publish the newly compiled files: `php artisan vendor:publish --provider=Sendportal\\Base\\SendportalBaseServiceProvider --tag=sendportal-assets`  

## Tests
Tests should run out of the box with a couple of things:

- Run `composer install` in the `base` package, as well as in the host package (cannot run tests from the host package)
- Create a MySQL database named `sendportal_dev`
- Create a Postgres database named `sendportal_dev`

After this, running `phpunit` on the commandline should produce passing tests, without errors. Unless you broke something. But that's on you.

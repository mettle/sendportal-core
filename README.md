# base

- `laravel new sendportal_testing`
- `git clone git@github.com:JonoB/base.git sendportal_base`
- Edit the `sendportal_testing/composer.json` file and add the following JSON:
```
"sendportal/base": "dev-master"
...
    "repositories": [
        {
            "type": "path",
            "symlink": true,
            "url": "../sendportal_base"
        }
    ],
```
- `composer update` in the sendportal_testing folder
- Now you should be able to edit code in the `sendportal_base` project and it's changes should be automatically picked up by the `sendportal_testing` project.

* Please adjust this readme if you experienced any issues or things which I didn't list here. Thanks!

## Release new version

- If CSS or JS files have been changed we need to recompile them: `npm run prod`
- In the main repository we have to publish the newly compiled files: `php artisan vendor:publish --provider=Sendportal\\Base\\SendportalBaseServiceProvider --tag=sendportal-assets`  

## Tests
Tests should run out of the box with a couple of things:

- Run `composer install` in the `base` package, as well as in the host package (cannot run tests from the host package)
- Create a MySQL database named `sendportal_dev`
- Create a Postgres database named `sendportal_dev`

After this, running `phpunit` on the commandline should produce passing tests, without errors. Unless you broke something. But that's on you.

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
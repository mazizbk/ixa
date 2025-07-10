Azimut System
=============

Online platform and applications developed for Azimut. Includes an online application framework and base applications (site manager, content management system, media center, user and access right manager, ...)
Please do not redistribute without authorization from copyrighters.


Contributors
------------

Yoann Le Crom <yoann.lecrom@azimut.net>

:ghost: Gerda Le Duc <gerda.leduc@azimut.net>

Mikael Peigney <mikael.peigney@azimut.net>


How to install
--------------

You need to have an [Azimut Login](https://git.home.azimut.net/azimut/login) instance running before you can install this project.

After cloning the project, install composer dependencies:

    $ composer install

Composer will ask you to provide values for the `parameters.yml` file. Most default values do not need to be changed, but make sure to update the database name and Azimut Login credentials.
After installing the dependencies, you'll need to dump the assets (`php bin/console assets:dump`) and generate the database schema (`php bin/console doctrine:schema:create`).

If you want, you can then load the fixtures using `php bin/console doctrine:fixtures:load`


Init for new projet
--------------

Use the script cleanAndInitForNewProject.sh to clean assets and init for a new blank project


Deploy to production
--------------------

Deployment is handled by GitLab CI. If you wish to deploy, push your code to master. GitLab will automatically test your code and push it to the staging environment (http://system.preprod/).
Once the deployment to staging is done, you can manually start the deployment to production directly from GitLab. Go to `CI / CD`, `Pipelines` and press the "play" icon on the `deploy_production` job.

Writing tests
-------------

Only PHPUnit tests are supported. More could be added but GitLab CI configuration will need to be updated.
When writing a test, please make sure to add the `@group azsystem` annotation to the test class. This will make so your tests are only run on the Azimut System project and not its forks.


Production server notes
-----------------------

On Azimut's production servers, the Apache and CLI users are not the same. They share the same group but the CLI user has a default umask which excludes permissions on group.

To prevent commands like `cache:clear` to remove permissions to the Apache user, you **need** to set the following parameter:

    after_console_auto_chmod: true

This will execute `chmod g+rw` on cache, logs, sessions and uploads directories.


More help
---------

[Read documentation](https://git.home.azimut.net/azimut/system/wikis/home)


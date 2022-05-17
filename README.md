For MAC- CLI:
-------------
brew install symfony-cli/tap/symfony-cli
symfony new --webapp QuizzProject  

Composer CLI:
-------------
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

Run symfony:
------------
symfony server:start

.env:
-----
DATABASE_URL="mysql://root:root@127.0.0.1:8889/my_quizz?serverVersion=5.7&charset=utf8mb4"

twig-view:
----------
composer require twig

////////////////////////////////////////////////////////////////////////////////

create-projet CLI:
------------------
composer create-project symfony/skeleton QuizzProject
cd QuizzProject
composer require webapp

start CLI :
----------- 
cd QuizzProject
symfony serve

Email DSN:
-----------
MAILER_DSN=smtp://d04cb4cf607f7e:b0d9e001f03d9f@smtp.mailtrap.io:2525?encryption=tls&auth_mode=login
composer require symfony/mailer


securité:
---------
composer require symfony/security-bundle



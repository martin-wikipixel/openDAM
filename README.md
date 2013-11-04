# OPENDAM (v1.0.0) #

## What ##

OpenDAM is an open source digital asset management platform to centralize , organize , manage and share picture and video files.

For more information, please visit [Wikipixel](http://wikipixel.com/)

##  System requirements ##

The OpenDam application requires :

- A Web Server with an OS(Debian, Ubuntu, etc.),
- PHP 5.3 minimum,
- A Connection to a  MySql Database,
- The Application [NODE.JS](http://nodejs.org/),
- The Application [FFMPEG](http://www.ffmpeg.org/),
- The Application [IMAGEMAGICK](http://www.imagemagick.org/).

## Installation ##

When you have finished downloading the application , unzip it in the directory "web" of your server (```/var/www/```ou ```/home/www/``` in most cases). You should have a path such as ```/var/www/opendam/```.

## Configuration ## 

#### Directory Permissions ####

You must first of all, change the directory cache/ and log/ so that the application can embeded:

```
chmod 777 cache/ log/
```

You should also authorize permission to the file to upload media :

```
chmod 767 -R web/data/
```

#### Web server #### 
Replace /path/to/application/ the path to the application folder.

```
Listen 80
NameVirtualHost 127.0.0.1:80

<VirtualHost 127.0.0.1:80>
    DocumentRoot "/path/to/application/web"
    DirectoryIndex index.php

    <Directory "/path/to/application/web">
        Options -Indexes FollowSymLinks MultiViews
        AllowOverride All
        Allow from All
    </Directory>

    Alias /sf /path/to/application/lib/vendor/symfony/data/web/sf
    <Directory "/path/to/application/lib/vendor/symfony/data/web/sf">
        AllowOverride All
        Allow from all
    </Directory>
</VirtualHost>
```

Restart Apache, so the modifications can be reloaded.

From the server , open a browser and type in the address bar http://localhost/ . You arrive at the login screen of the application.

#### PHP #### 

Some guidelines PHP must be enabled / disabled. Must edit the files /etc/php5/cli/php.ini and /etc/php5/apache2/php.ini If these directives are prefixed by the character ; it must be removed.

```
mbstring.language = UTF-8  
mbstring.internal_encoding  = UTF-8
mbstring.http_input = auto
mbstring.http_output = UTF-8

mbstring.encoding_translation = On (Si la version de PHP < 5.4)
mbstring.detect_order = auto
mbstring.substitute_character = none

expose_php = Off
display_errors = Off
log_errors = On
short_open_tag = Off
magic_quotes_gpc = Off
register_globals = Off
session.auto_start = 0
```
Restart Apache for the changes to be loaded.

#### Node.js ####

You must then install the less for the node.js module

```
npm install -g less
```

#### FFMPEG #### 

To ensure the proper functioning video conversion tool , the following libraries must be installed and configured for FFMPEG :
- [LAME](http://lame.sourceforge.net/),
- x264,
- libvpx.

To check your version of ffmpeg has been configured , enter :

```
ffmpeg
```

Among the lines of the command output , you should see :

```
configuration: --enable-libmp3lame --enable-libx264 --enable-libvpx
```

This means that FFMPEG does support the essential OpenDam modules.

#### MySql #### 

To create the database , run the file located in ```config/data/database.sql```.

Then edit the ```config/databses.yml``` file and change the SQL as well as the login and password (```myHost```, ```myDatabase```, ```myUsername``` et ```myPassword``).

```
all:
  propel:
    class: sfPropelDatabase
    param:
      classname: PropelPDO
      dsn: 'mysql:host=myHost;dbname=myDatabase'
      username: myUsername
      password: myPassword
      encoding: utf8
      persistent: true
      pooling: true
```

Once you are done, you need to clear the cache of the application by the command:

```
php symfony cc

```

## Use ##
The application contains a default set of tests to show you a taste of OpenDam solution.
To log on, simply enter an email (```john.doe@opendam.org```) et un mot de passe (```opendam```).






## Français ##

## What ##

OpenDAM est une plateforme cloud Open-source de gestion et partage de fichiers images et vidéos.

Pour plus d'informations, visitez [Wikipixel](http://wikipixel.com/)

## Pré-requis ##

#### Système ####

L'application OpenDam nécessite :

- un serveur web sous Linux (Debian, Ubuntu, etc.),
- PHP 5.3 minimum,
- une connexion à une base de données MySql,
- l'application [NODE.JS](http://nodejs.org/),
- l'application [FFMPEG](http://www.ffmpeg.org/),
- l'application [IMAGEMAGICK](http://www.imagemagick.org/).

## Installation ##

Lorsque vous avez terminé de télécharger l'application, décompressez-la dans le répertoire "web" de votre serveur (```/var/www/```ou ```/home/www/``` dans la plupart des cas). Vous devriez alors avoir un chemin du type ```/var/www/opendam/```.

## Configuration ##

#### Droits sur les répertoires ####

Vous devez avant toute chose, modifier les droits d'accès aux répertoires ```cache/```et ```log/``` pour que l'application puisse écrire dedans :

```
chmod 777 cache/ log/
```

Il faut aussi autoriser l'écriture dans le dossier d'upload des médias :

```
chmod 767 -R web/data/
```

#### Serveur web ####
Remplacez ```/path/to/application/``` par le chemin absolu vers le dossier de l'application.

```
Listen 80
NameVirtualHost 127.0.0.1:80

<VirtualHost 127.0.0.1:80>
	DocumentRoot "/path/to/application/web"
    DirectoryIndex index.php

	<Directory "/path/to/application/web">
		Options -Indexes FollowSymLinks MultiViews
		AllowOverride All
		Allow from All
	</Directory>

	Alias /sf /path/to/application/lib/vendor/symfony/data/web/sf
	<Directory "/path/to/application/lib/vendor/symfony/data/web/sf">
		AllowOverride All
		Allow from all
	</Directory>
</VirtualHost>
```
Redémarrez Apache, pour que les modifications soient chargées.

Depuis le serveur, lancez un navigateur et tapez dans la barre d'adresse <http://localhost/>. Vous arrivez sur l'écran de connexion de l'application.

#### PHP #####

Certaines directives de PHP doivent être activées / désactivées. Il faut modifier les fichiers ```/etc/php5/cli/php.ini```et ```/etc/php5/apache2/php.ini```. Si ces directives sont préfixées par le caractère de mise en commentaire ```;```, il faut le supprimer.

```
mbstring.language = UTF-8  
mbstring.internal_encoding  = UTF-8
mbstring.http_input = auto
mbstring.http_output = UTF-8

mbstring.encoding_translation = On (Si la version de PHP < 5.4)
mbstring.detect_order = auto
mbstring.substitute_character = none

expose_php = Off
display_errors = Off
log_errors = On
short_open_tag = Off
magic_quotes_gpc = Off
register_globals = Off
session.auto_start = 0
```
Redémarrez Apache, pour que les modifications soient chargées.

#### Node.js ####

Il faut installer ensuite le module ```less```pour node.js :

```
npm install -g less
```

#### FFMPEG ####

Pour assurer le bon fonctionnement des conversions vidéos de l'outil, les librairies suivantes doivent être installées et configurées pour FFMPEG :

- [LAME](http://lame.sourceforge.net/),
- x264,
- libvpx.

Pour vérifier que votre version de ffmpeg a bien été configurée, saisissez :

```
ffmpeg
```

Parmi les lignes de résultats de la commande, vous devriez voir :

```
configuration: --enable-libmp3lame --enable-libx264 --enable-libvpx
```

Cela signifie que FFMPEG prend bien en charge les modules indispensables à OpenDam.

#### MySql ####

Pour créer la base de données, exécutez le fichier qui se trouve dans ```config/data/database.sql```.

Éditez ensuite le fichier ```config/databses.yml``` et modifiez le serveur SQL ainsi que le login et mot de passe (```myHost```, ```myDatabase```, ```myUsername``` et ```myPassword``).

```
all:
  propel:
    class: sfPropelDatabase
    param:
      classname: PropelPDO
      dsn: 'mysql:host=myHost;dbname=myDatabase'
      username: myUsername
      password: myPassword
      encoding: utf8
      persistent: true
      pooling: true
```

Une fois les modifications effectuées, il vous faut vider le cache de l'application par la commande :

```
php symfony cc
```

## Utilisation ##
L'application contient, par défaut, un jeu de test pour vous montrer un avant-goût de la solution OpenDam.
Pour vous connecter, il vous suffit de saisir un email (```john.doe@opendam.org```) et un mot de passe (```opendam```).
# symfony5base

#### 8 de Abril de 2021 por [Francisco Gimenez](/)

Realizado en ambiente windows con servidor XAMPP.


## Requisitos previos

- composer https://getcomposer.org/download/
- servidor web apache con php ^7.2
- servidor MySql o MariaDB 

## Descargar repositorio git

~~~
git clone https://github.com/benji66/symfony5base.git
~~~

## Configuracion de archivo .env

Sustituir los valores correspondientes:

~~~
APP_ENV=env
APP_SECRET=6d4e99ff0680e4c7547c0fd5d8800da3

DATABASE_URL=mysql://usuario:password@127.0.0.1:3306/sf5_base?serverVersion=mariadb-10.4.11

###> nelmio/cors-bundle ###
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$
###< nelmio/cors-bundle ###


###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=1234567
###< lexik/jwt-authentication-bundle ###
~~~

## Ejemplo de configuracion del servidor

#### VirtualHost

Sustituir los elementos que considere.

~~~
<VirtualHost *:80>
    ServerName symfony5base.lc
    ServerAlias symfony5base.lc

    DocumentRoot "C:\symfony\symfony5base\public"
    DirectoryIndex /index.php

    <Directory "C:\symfony\symfony5base" >
        AllowOverride All
	Require all granted
        
	FallbackResource /index.php
    </Directory>
   
    <Directory "C:\symfony\symfony5base\public\bundles">
        FallbackResource disabled
    </Directory>
    ErrorLog "C:\xampp\apache\logs\symfony_error.log"   
</VirtualHost>
~~~

#### Archivo Hosts

~~~
127.0.0.1          symfony5base.lc
~~~

## Inicializar aplicacion

Ejecutar en consola

##### Creacion de dependencias

~~~
composer install
~~~

##### Revision de configuracion correcta de php para el funcionamiento correcto del Framework

symfony5base.lc es el dns elegido en el archivo hosts.

~~~
http://symfony5base.lc/check.php
~~~

##### Copia de archivos accesibles desde la carpete public

~~~
php bin/console assets:install
~~~

##### Creacion de base de datos bajo los parametros del archivo .env

~~~
php bin/console doctrine:database:create
~~~

##### Migraciones de tablas del sistema

~~~
php bin/console doctrine:migrations:migrate --all-or-nothing
~~~

##### Datos precargados 

~~~
//src/DataFixtures

php bin/console doctrine:fixtures:load
~~~

##### Usuario de acceso 

El acceso a la prueba se encuentra libre, pero el login y password para los crud son los siguientes:

~~~
username: admin@admin.com
password: admin
~~~

## Puntos adicionales

- Se coloca como extra un control de acceso que contiene el CRUD de Paises y un CRUD de Usuarios bajo el esquema MVC
- Se configuran algunos componentes para los behavior y trazas
- Se configura JWT para el uso de generacion de tokens en caso de ser necesario. Se puede comprobar por postman por la ruta /api/login_check pasandole los parametros username y password en el body via post. 



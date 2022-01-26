# Dolibarr application server: Repositorio modificado de la version: https://github.com/upshift-docker/dolibarr

RECOMENDACIÓN: 
1-CLONA EL REPOSITORIO.
2-LEVANTA EL DOCKER-COMPOSE.
3-ASEGURATE DE QUE FUNCIONA.
4-LUEGO MODIFICA EL PROYECTO AL GUSTO. 

Docker image para [Dolibarr ERP](https://www.dolibarr.org).


# Ejecutando esta imagen con docker-compose

Este ejemplo usará un contenedor MariaDB (también puede usar MySQL o PostgreSQL si lo prefiere). Los volúmenes están configurados para mantener sus datos persistentes. Esta configuración no proporciona cifrado SSL y está diseñada para ejecutarse detrás de un proxy. Incorpora un servicio phpMyAdmin

Crear docker-compose.yml  de la siguiente manera:

```yml
version: '3'

services:

  mariadb:
    image: mariadb:10.6
    container_name: mariadb
    restart: unless-stopped
    command: --character_set_client=utf8 --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci
    volumes:
      - ./db_data:/var/lib/mysql    

    environment:
      - MYSQL_DATABASE=dolibarr
      - MYSQL_USER=dolibarr
      - MYSQL_PASSWORD=dolibarr
      - MYSQL_RANDOM_ROOT_PASSWORD=yes
      - TZ=America/Costa_Rica

  dolibarr:
    image: upshift/dolibarr:14.0
    container_name: dolibarr
    restart: unless-stopped
    depends_on:
        - mariadb
    ports:
        - "8000:80"
    environment:
      - DOLI_ADMIN_LOGIN=admin
      - DOLI_ADMIN_PASSWORD=p4ssw0rd
      - DOLI_DB_HOST=mariadb
      - DOLI_DB_NAME=dolibarr
      - DOLI_DB_USER=dolibarr
      - DOLI_DB_PASSWORD=dolibarr
      - TZ=America/Costa_Rica
      - LANG=es_ES
    volumes:
      - ./doli_html:/var/www/html
      - ./doli_docs:/var/www/documents
  
  phpmyadmin:
    image: phpmyadmin
    
    restart: always
    ports:
       - 3600:80
    environment:
       - PMA_ARBITRARY=1
       - TZ=America/Costa_Rica

    links:
        - mariadb
```

A continuación, ejecute todos los servicios docker-compose up -d. Ahora, vaya a http://localhost:8000/install para acceder al nuevo asistente de instalación de Dolibarr.

# Haga que su Dolibarr esté disponible desde Internet

Hasta aquí, su Dolibarr solo está disponible desde su host docker. Si desea que Dolibarr esté disponible desde Internet, es obligatorio agregar el cifrado SSL. Hay muchas posibilidades diferentes para introducir el cifrado dependiendo de su configuración.

Recomendamos usar un proxy inverso frente a nuestra instalación de Dolibarr. Solo se podrá acceder a su Dolibarr a través del proxy, que encripta todo el tráfico a los clientes. Puede montar sus certificados generados manualmente en el proxy o usar una solución completamente automatizada, que genera y renueva los certificados por usted.

# Primer uso

Cuando accede por primera vez a su Dolibarr, debe acceder al asistente de instalación en http://localhost:8000/install/. Aparecerá el asistente de configuración y le pedirá que elija una cuenta de administrador, una contraseña y la conexión a la base de datos. Para la base de datos, use el nombre de su contenedor de base de datos como host y dolibarrcomo tabla y nombre de usuario. También ingrese la contraseña de la base de datos que eligió en su docker-compose.ymlarchivo.

La mayoría de los campos del asistente se pueden inicializar con las variables de entorno.

Sin embargo, debe tener en cuenta que algunas variables de entorno se ignorarán durante la instalación del asistente ( DOLI_AUTHy DOLI_LDAP_*por ejemplo). El contenedor generó una inicial conf.phpen el primer inicio con las variables de entorno de Dolibarr que configuró a través de Docker. Para usar la configuración generada por el contenedor, puede omitir el primer paso de instalación e ir directamente a http://localhost:8000/install/step2.php .

# Actualizar a una versión más nueva

La actualización del contenedor de Dolibarr se realiza extrayendo la nueva imagen, desechando el contenedor antiguo y comenzando con el nuevo. Dado que todos los datos se almacenan en volúmenes, nada se pierde. La secuencia de comandos de inicio verificará la versión en su volumen y la versión de Docker instalada. Si encuentra una discrepancia, inicia automáticamente el proceso de actualización. No olvide agregar todos los volúmenes a su nuevo contenedor, para que funcione como se esperaba. Además, le recomendamos que no se salte las versiones principales durante la actualización. Por ejemplo, actualice de 5.0 a 6.0, luego de 6.0 a 7.0, no directamente de 5.0 a 7.0.

```console
$ docker pull upshift/dolibarr
$ docker stop <your_dolibarr_container>
$ docker rm <your_dolibarr_container>
$ docker run <OPTIONS> -d upshift/dolibarr
```

Tenga en cuenta que debe ejecutar el mismo comando con las opciones que utilizó para iniciar inicialmente su Dolibarr. Eso incluye volúmenes, mapeo de puertos.

Cuando usa docker-compose, su archivo de composición se encarga de su configuración, por lo que solo tiene que ejecutar:

```console
$ docker-compose pull
$ docker-compose up -d
```


## DOCKER Usage

This image does not contain the database for Dolibarr. You need to use either an existing database or a database container.

To start the container type:

```console
# docker run -d -p 8080:80 --link my-db:db upshift/dolibarr
```

Now you can access Dolibarr at http://localhost:8080/ from your host system. Default password for the 'admin' user is 'dolibarr'.

## Persistent data

The Dolibarr installation and all data beyond what lives in the database (file uploads, etc) are stored in the [unnamed docker volume](https://docs.docker.com/engine/tutorials/dockervolumes/#adding-a-data-volume) volume `/var/www/html` and `/var/www/documents`. The docker daemon will store that data within the docker directory `/var/lib/docker/volumes/...`. That means your data is saved even if the container crashes, is stopped or deleted.

To make your data persistent to upgrading and get access for backups is using named docker volume or mount a host folder. To achieve this you need one volume for your database container and two volumes for Dolibarr.

Dolibarr:
- `/var/www/html/` folder where all Dolibarr data lives
- `/var/www/documents/` folder where all Dolibarr documents lives

```console
# docker run -d \
    -v dolibarr_html:/var/www/html \
    -v dolibarr_docs:/var/www/documents \
    upshift/dolibarr
```

Database:
- `/var/lib/mysql` MySQL / MariaDB Data
- `/var/lib/postgresql/data` PostgreSQL Data

```console
# docker run -d \
    -v db:/var/lib/mysql \
    mariadb
```

If you want to get fine grained access to your individual files, you can mount additional volumes for config, your theme and custom modules. 
The `conf` is stored in subfolder inside `/var/www/html/`. The modules are split into core `apps` (which are shipped with Dolibarr and you don't need to take care of) and a `custom` folder. If you use a custom theme it would go into the `theme` subfolder.

Overview of the folders that can be mounted as volumes:

- `/var/www/html` Main folder, needed for updating
- `/var/www/html/custom` installed / modified modules
- `/var/www/html/conf` local configuration
- `/var/www/html/theme/<YOUR_CUSTOM_THEME>` theming/branding

## Auto configuration via environment variables

The Dolibarr image supports auto configuration via environment variables. You can preconfigure nearly everything that is asked on the install page on first run. To enable auto configuration, set your database connection via the following environment variables. ONLY use one database type!

See [conf.php.example](https://github.com/Dolibarr/dolibarr/blob/develop/htdocs/conf/conf.php.example) and [install.forced.sample.php](https://github.com/Dolibarr/dolibarr/blob/develop/htdocs/install/install.forced.sample.php) for more details on install configuration.

### DOLI_DB_TYPE

*Default value*: `mysqli`

*Possible values*: `mysqli`, `pgsql`

This parameter contains the name of the driver used to access your Dolibarr database.

Examples:
```
DOLI_DB_TYPE=mysqli
DOLI_DB_TYPE=pgsql
```

### DOLI_DB_HOST

*Default value*: `localhost`

This parameter contains host name or ip address of Dolibarr database server.

Examples:
```
DOLI_DB_HOST=localhost
DOLI_DB_HOST=127.0.2.1
DOLI_DB_HOST=192.168.0.10
DOLI_DB_HOST=mysql.myserver.com
```

### DOLI_DB_PORT

*Default value*: `3306`

This parameter contains the port of the Dolibarr database.

Examples:
```
DOLI_DB_PORT=3306
DOLI_DB_PORT=5432
```

### DOLI_DB_NAME

*Default value*: 

This parameter contains name of Dolibarr database.

Examples:
```
DOLI_DB_NAME=dolibarr
DOLI_DB_NAME=mydatabase
```

### DOLI_DB_USER

*Default value*: 

This parameter contains user name used to read and write into Dolibarr database.

Examples:
```
DOLI_DB_USER=admin
DOLI_DB_USER=dolibarruser
```

### DOLI_DB_PASSWORD

*Default value*: 

This parameter contains password used to read and write into Dolibarr database.

Examples:
```
DOLI_DB_PASSWORD=myadminpass
DOLI_DB_PASSWORD=myuserpassword
```

### DOLI_DB_PREFIX

*Default value*: `llx_`

This parameter contains prefix of Dolibarr database.

Examples:
```
DOLI_DB_PREFIX=llx_
```

### DOLI_DB_CHARACTER_SET

*Default value*: `utf8`

Database character set used to store data (forced during database creation. value of database is then used).
Depends on database driver used. See `DOLI_DB_TYPE`.

Examples:
```
DOLI_DB_CHARACTER_SET=utf8
```

### DOLI_DB_COLLATION

*Default value*: `utf8_unicode_ci`

Database collation used to sort data (forced during database creation. value of database is then used).
Depends on database driver used. See `DOLI_DB_TYPE`.

Examples:
```
DOLI_DB_COLLATION=utf8_unicode_ci
```

### DOLI_DB_ROOT_LOGIN

*Default value*: 

This parameter contains the database server root username used to create the Dolibarr database.

If this parameter is set, the container will automatically tell Dolibarr to create the database and database user on first install with the root account.

Examples:
```
DOLI_DB_ROOT_LOGIN=root
DOLI_DB_ROOT_LOGIN=dolibarruser
```

### DOLI_DB_ROOT_PASSWORD

*Default value*: 

This parameter contains the database server root password used to create the Dolibarr database.

Examples:
```
DOLI_DB_ROOT_PASSWORD=myrootpass
```

### DOLI_ADMIN_LOGIN

*Default value*: `admin`

This parameter contains the admin's login used in the first install.

Examples:
```
DOLI_ADMIN_LOGIN=admin
```

### DOLI_ADMIN_PASSWORD

*Default value*: `dolibarr`

This parameter contains the admin's password used in the first install.

Examples:
```
DOLI_ADMIN_PASSWORD=dolibarr
```

### DOLI_MODULES

*Default value*: 

This parameter contains the list (comma separated) of modules to enable in the first install.

Examples:
```
DOLI_MODULES=modSociete
DOLI_MODULES=modSociete,modPropale,modFournisseur,modContrat,modLdap
```

### DOLI_URL_ROOT

*Default value*: `http://localhost`

This parameter defines the root URL of your Dolibarr index.php page without ending "/".
It must link to the directory htdocs.
In most cases, this is autodetected but it's still required 
* to show full url bookmarks for some services (ie: agenda rss export url, ...)
* or when using Apache dir aliases (autodetect fails)
* or when using nginx (autodetect fails)

Examples:
```
DOLI_URL_ROOT=http://localhost
DOLI_URL_ROOT=http://mydolibarrvirtualhost
DOLI_URL_ROOT=http://myserver/dolibarr/htdocs
DOLI_URL_ROOT=http://myserver/dolibarralias
```

### DOLI_AUTH

*Default value*: `dolibarr`

*Possible values*: Any values found in files in htdocs/core/login directory after the `function_` string and before the `.php` string, **except forceuser**. You can also separate several values using a `,`. In this case, Dolibarr will check login/pass for each value in order defined into value. However, note that this can't work with all values.

This parameter contains the way authentication is done.
**Will not be used if you use first install wizard.** See *First use* for more details.

If value `ldap` is used, you must also set parameters `DOLI_LDAP_*` and `DOLI_MODULES` must contain `modLdap`.

Examples:
```
DOLI_AUTH=http
DOLI_AUTH=dolibarr
DOLI_AUTH=ldap
DOLI_AUTH=openid,dolibarr
```

### DOLI_LDAP_HOST

*Default value*: `127.0.2.1`

You can define several servers here separated with a comma.

Examples:
```
DOLI_LDAP_HOST=localhost
DOLI_LDAP_HOST=ldap.company.com
DOLI_LDAP_HOST=ldaps://ldap.company.com:636,ldap://ldap.company.com:389
```

### DOLI_LDAP_PORT

*Default value*: `389`

### DOLI_LDAP_VERSION

*Default value*: `3`

### DOLI_LDAP_SERVERTYPE

*Default value*: `openldap`
*Possible values*: `openldap`, `activedirectory` or `egroupware`

### DOLI_LDAP_DN

*Default value*: 

Examples:
```
DOLI_LDAP_DN=ou=People,dc=company,dc=com
```

### DOLI_LDAP_LOGIN_ATTRIBUTE

*Default value*: `uid`

Ex: uid or samaccountname for active directory

### DOLI_LDAP_FILTER

*Default value*: 

If defined, the two previous parameters are not used to find a user into LDAP.

Examples:
```
DOLI_LDAP_FILTER=(uid=%1%)
DOLI_LDAP_FILTER=(&(uid=%1%)(isMemberOf=cn=Sales,ou=Groups,dc=company,dc=com))
```

### DOLI_LDAP_ADMIN_LOGIN

*Default value*: 

Required only if anonymous bind disabled.

Examples:
```
DOLI_LDAP_ADMIN_LOGIN=cn=admin,dc=company,dc=com
```

### DOLI_LDAP_ADMIN_PASS

*Default value*: 

Required only if anonymous bind disabled. Ex: 

Examples:
```
DOLI_LDAP_ADMIN_PASS=secret
```

### DOLI_LDAP_DEBUG

*Default value*: `false`


### DOLI_PROD

*Default value*: `0`

*Possible values*: `0` or `1`

When this parameter is defined, all errors messages are not reported.
This feature exists for production usage to avoid to give any information to hackers.

Examples:
```
DOLI_PROD=0
DOLI_PROD=1
```

### DOLI_HTTPS

*Default value*: `0`

*Possible values*: `0`, `1`, `2` or `'https://my.domain.com'`

This parameter allows to force the HTTPS mode.
* 0 = No forced redirect
* 1 = Force redirect to https, until SCRIPT_URI start with https into response
* 2 = Force redirect to https, until SERVER["HTTPS"] is 'on' into response
* 'https://my.domain.com' = Force redirect to https using this domain name.

*Warning*: If you enable this parameter, your web server must be configured to
respond URL with https protocol. 
According to your web server setup, some values may work and other not. Try 
different values (1,2 or 'https://my.domain.com') if you experience problems.

Examples:
```
DOLI_HTTPS=0
DOLI_HTTPS=1
DOLI_HTTPS=2
DOLI_HTTPS=https://my.domain.com
```

### DOLI_NO_CSRF_CHECK

*Default value*: `0`

*Possible values*: `0`, `1`

This parameter can be used to disable CSRF protection.

This might be required if you access Dolibarr behind a proxy that make URL rewriting, to avoid false alarms.

Examples:
```
DOLI_NO_CSRF_CHECK=0
DOLI_NO_CSRF_CHECK=1
```

### PHP_INI_*

Replace or add configuration in php.ini file.

Examples:
```
ENV PHP_INI_upload_max_filesize=50M
ENV PHP_INI_memory_limit=256M
ENV PHP_INI_max_execution_time=60
```


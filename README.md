# Log Retrieval Systems

## What's this about?

In the California QSO Party, ham radio stations that participate make
radio contacts with other stations and log these contacts. Those who
want to enter the contest and be eligible for awards and recognition
must submit their log in the [Cabrillo file
format](http://www.arrl.org/cabrillo-format-tutorial) whose general
[specification is here](https://wwrof.org/cabrillo/). The QSO line
format for CQP is covered in the [qso_syntax.html](qso_syntax.html)
file in this repository.

The code in this git repository is for a webserver to receive log
uploads and for an email robot to download logs submitted via
email. The webserver provides some initial checking of submitted
Cabrillo (or other) files to try to help ensure the submitted logs are
for the correct contest and in the correct format. The email robot
does some of the same checking.

## Three important branches

There are three directories related to setting up the CQP log
retrieval server. One contains web content in the form of HTML files,
CSS files, and [Ruby](https://ruby-lang.org/) scripts. The second
contains Apache webserver configuration files, and the third has a
`crontab` configuration.

### main

On the original robot.cqp.org webserver (a virtual Debian Linux box),
the web content goes in `/var/www/cqp/cqp`. This material is stored in
the git `main` branch.

### apache-config

The second configuration directory is related to the [Apache
webserver](https:///httpd.apache.org). This is stored in the git
`apache-config` branch. On robot.cqp.org (Debian Linux), this goes in
`/etc/apache2/sites-available/`.

### crontab

The third configuration file involves crontab entries for the
webserver account. Here we assume that it's `www-data`.

## Installing the main branch

### Installing Ruby

This webserver uses the [Ruby](https://ruby-lang.org/) language. It
does *not* use Ruby on Rails. I like to use my own install of ruby.
Below is a bash shell script to compile ruby and the necessary gems
from source. Before you run it, you'll need the build dependencies for
Ruby installed.

```sh
$ sudo apt-get install gcc g++ git lzma-dev libssl-dev make xz-utils \ 
  libffi-dev sqlite3 libsqlite3-dev  libreadline-dev libgmp-dev \
  mariadb-server libmariadb-dev libyaml-dev libfcgi-dev
```

```sh
#!/usr/bin/bash

RUBY_VERSION=ruby-3.2.2
# assumes ruby-${RUBY_VERSION}.tar.gz already downloaded and untar'ed
mv -f /usr/local/${RUBY_VERSION} /usr/local/${RUBY_VERSION}_old
rm -rf  /usr/local/${RUBY_VERSION}_old
cd ${RUBY_VERSION}
mv -f build build.old
rm -rf build.old &
mkdir build
cd build
../configure --prefix=/usr/local/${RUBY_VERSION}
make -j2 install
GEMS="svg-graph levenshtein sqlite3 prawn nokogiri jaro_winkler gmail_xoauth fcgi caxlsx gd gmail gmail-imap hoe humanize amatch mysql2 oauth2 ruby-xz"
for gem in ${GEMS} ; do
    /usr/local/${RUBY_VERSION}/bin/gem install  ${gem}
done
```
Then make a symbolic link from `/usr/local/ruby` to `/usr/local/${RUBY_VERSION}`.

### Cloning the main branch

My Debian Linux system tends to want to put web content in
`/var/www/`. If you're starting from a fresh Debian Linux install, I
would suggest the following commands to install the basic web content:

```sh
$ su --login --shell /bin/bash www-data
$ cd /var/www
$ mkdir cqp
$ cd cqp
$ git clone https://github.com/California-QSO-Party/cqp-log-server.git cqp
```

You're going to need to create two MySQL/MariaDB account to access a database
named `CQPUploads` for CQP.

```sh
$ mysql -u root -p
MariaDB [(none)]> create database CQPUploads;
MariaDB [(none)]> create user 'mycqp'@'localhost' identified by 'verycomplexpassword';
MariaDB [(none)]> grant all on CQPUploads.* to 'mycqp'@'localhost';
MariaDB [(none)]> create user 'backupuser'@'localhost' identified by 'alsocomplexpassword';
MariaDB [(none)]> GRANT SELECT, LOCK TABLES ON `CQPUploads`.* TO `backupuser`@`localhost`;
MariaDB [(none)]> GRANT INSERT ON `CQPUploads`.`CQPError` TO `backupuser`@`localhost`;
MariaDB [(none)]> flush privileges;
```

The user names and passwords chosen here will need to go into
`/var/www/cqp/cqp/server/config.rb`. However, before you do that makes
sure you've configured Apache, so people browsing your site can't see
that file.

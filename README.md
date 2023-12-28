# Log Retrieval Systems

## What's this about

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

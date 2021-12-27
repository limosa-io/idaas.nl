
# INSTALL

This installation documentation is nothing more than a raw sketch.

* nginx
* php
* redis
* postgres / citus

## Postgres

Create a database with a user and update `.env`. 

~~~
CREATE DATABASE forge;
CREATE USER youruser WITH ENCRYPTED PASSWORD 'yourpass';
GRANT ALL PRIVILEGES ON DATABASE yourdbname TO youruser;
~~~

In case docker-compose is used: it stores pgdata in `~/.pgdata`.

## Redis

Set max memory limit. Choose volatile-lru 

* Redis max_memory met migrate instellen?? Zorgen voor juiste remove strategy.

## Nginx

Use "hash" directive for upstreams in nginx to set-up sticky loadbalancer. 
Hash on authorization-header, and x-authchain (or what it's called) header.

## Mailgun

Create a mailgun account.

## S3

Create a proper S3 account.

## OpenWhisk

Create an openwhisk IBM account

## DNS

Ensure you can resolve the domains. For development, route all *.tst traffic to the same ip.

On Ubuntu, do something like this: `https://stackoverflow.com/questions/49998099/dns-not-working-within-docker-containers-when-host-uses-dnsmasq-and-googles-dns/50001940`.

Or something like this:

https://askubuntu.com/questions/1032450/how-to-add-dnsmasq-and-keep-systemd-resolved-18-04

Or the simpler way => https://computingforgeeks.com/install-and-configure-dnsmasq-on-ubuntu-18-04-lts/

Populate file `/etc/NetworkManager/dnsmasq.d/tst` with the following.

~~~
address=/.tst/127.0.0.1
~~~

And  `/etc/NetworkManager/dnsmasq.d/docker-bridge.conf` with the following.

~~~
listen-address=172.17.0.1
~~~

~~~
sudo service network-manager restart
~~~

## Max file descriptiors

~~~
echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
~~~

## Ubuntu

~~~
sudo apt-get install php7.2-sqlite
sudo apt install php7.2-bcmath
~~~

## Finaly

~~~
cd frontend
npm install
npm run build
cd ..
php artisan migrate
php artisan tenant:master master arietimmerman@gmail.com
php artisan config:cache
php artisan route:cache
php artisan view:cache
~~~





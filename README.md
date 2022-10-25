# iCal-Bridge

iCal-Bridge is a spin-off of the popular [RSS-Bridge](https://github.com/RSS-Bridge/rss-bridge) project aimed at generating [iCalendar](https://icalendar.org/) files for websites that don't provide them.

# Installation

## Requirements

* PHP >= 7.4
* PHP mbstring extension
* [Composer](https://getcomposer.org/)

## Setup

```bash
cd /var/www
git clone https://github.com/simon816/ical-bridge.git
composer install

# Ensure cache directory is writable by the web server. e.g:
chown www-data:www-data /var/www/ical-bridge/cache

# Optionally copy over the default config file
cp config.default.ini.php config.ini.php

# Optionally copy over the default whitelist file
cp whitelist.default.txt whitelist.txt
```

### With Docker

```bash
# Build image from Dockerfile
docker build -t ical-bridge .

# Create container
docker create --name ical-bridge --publish 3000:80 ical-bridge

# Start the container
docker start ical-bridge
```

Browse http://localhost:3000/

# License

This project is licensed under GPL-3.0.

Third-party libraries are licensed under their own license:

* [RSS-Bridge](https://github.com/RSS-Bridge/rss-bridge) - [unlicense](https://unlicense.org/)
* [Zap Cal PHP Library](https://icalendar.org/php-library.html) - [GPL-3.0](https://www.gnu.org/licenses/gpl-3.0.html)
* [PHP Simple HTML DOM Parser](https://sourceforge.net/projects/simplehtmldom/) - [MIT](https://opensource.org/licenses/MIT)

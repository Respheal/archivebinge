# Archive Binge
AB was a webcomic aggregator and reader. As the original developer is no longer able to work on the project, the source code is made available here for use, reproduction, modification, display, and distribution. Please see the license for more details on what you may do with this source code. If you use this code, you must provide access to the source code, whether it be linking to this repo (if unmodified), or linking to your own public repo.

## Requirements
* PHP 7.3+
* Python 2.7
* MySQL (Preferably MariaDB 10.2+)

## Installation
```sh
git clone git@github.com:Respheal/archivebinge.git
cd ./archivebinge/
sudo apt-get install python-dev python-pip libxml2-dev libxslt1-dev zlib1g-dev libffi-dev libssl-dev
python -m virtualenv ./crawler/crawlerenv
source ./crawler/crawlerenv/bin/activate
pip install -U pip
pip install -r ./crawler/requirements.txt
```
Before use, you should update the following strings throughout the codebase.

Any instance of:
* DOMAIN.COM should be updated to the host domain
* HANDLE should be updated to a live social media handle
* '/full/path/to/archivebinge/crawler/crawlerenv/' should be updated to the virtual env created above
* DATABASE_HOST should be your database host (probably 'localhost')
* DATABASE_USER should be your database user
* DATABASE_PASSWORD should be your database password
* DATABASE_NAME should be your database name
* SECRET_KEY should be a unique key used for encryption

Files to rename:
```sh
mv ./crawler/minisup.sample.py ./crawler/minisup.py
mv ./crawler/supervisor.sample.py ./crawler/supervisor.py
mv ./includes/conf.inc.sample.php ./includes/conf.inc.php
mv ./includes/tos.inc.sample.php ./includes/tos.inc.php
mv ./includes/privacy.inc.sample.php ./includes/privacy.inc.php
```

To use the social media logins, you will need to configure their OAuth settings:

<b>Facebook:</b><br />
See: https://developers.facebook.com/docs/facebook-login/web/<br />
Config: ./includes/auth/facebook.settings.php

<b>Twitter:</b><br />
See: https://developer.twitter.com/en/docs/authentication/guides/log-in-with-twitter<br />
Config: ./includes/auth/twitter.settings.php

<b>Google:</b><br />
See: https://developers.google.com/identity/protocols/oauth2<br />
Config: ./includes/auth/google.settings.php

Lastly, although you may create a database yourself to your own specifications, I've included a dump of an empty database which you may import: `./mysql_dump/ab_database.sql`

## Usage Notes

1. Whichever user has an ID of 1 in the database is the admin user.
2. I make absolutely no promises about the functionality, readability, usability of any code. Use at your own risk.
3. Not all files included are necessary for functionality (see: some tutorial files that got left in)
4. Any updates to this repo will not be pushed to archivebinge.com. Any updates to this repo are meant for use in derivative sites and/or repos
5. Pull requests will be accepted at my leisure (likely never). It's recommended to fork instead.

## Crons
In order to collect comic updates, AB relies on two crons:
```
23,53 * * * * cd /path/to/public_html/crawler; ./minisup.py 2>> /path/to/public_html/crawler/minisuplog
*/15 * * * * cd /path/to/public_html/crawler; ./supervisor.py 2>> /path/to/public_html/crawler/supervisorlog
```
`minisup.py` collects updates for existing comics. `supervisor.py` collects updates for newly-added comics. You may set them to run at whatever intervals you like. Do check on them occasionally though--some comics may trigger an infinite-crawl bug, resulting in multiple processes, which may result in server resource overages.

## Crawlers
All scrapy crawlers are stored under ./crawler/archivebinger/spiders. You can run the spiders manually like so:

```sh
.crawler/crawlerenv/bin/scrapy crawl typefinder -a starturl='https://comic.com/first-page' -a secondurl='https://comic.com/second-page' -a cid='crawldata.json'
```

This spider will find a reference to the second page on the first page of a comic and note that for future crawling. The output file, crawldata.json, contains variables used in the following:

```sh
.crawler/crawlerenv/bin/scrapy crawl superbinge -a starturl="https://comic.com/any-page" -a position="inner" -a tag="rel" -a identifier="next"
```

This will launch a crawler through all of the pages of the referenced comic.

## License
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/

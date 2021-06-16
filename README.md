# Archive Binge
About Archive Binge: <br>
AB was a webcomic aggregator and reader. As the original developer is no longer able to work on the project, the source code is made available here for use, reproduction, modification, display, and distribution. Please see the license for more details on what you may do with this source code. The main point of using the MPL 2.0 license is that, if you use the code, you must provide access to the source code, whether it be linking to this repo (if unmodified), or linking to your own public repo.

# Installation
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

Files to rename:
```sh
mv ./crawler/minisup.sample.py ./crawler/minisup.py
mv ./crawler/supervisor.sample.py ./crawler/supervisor.py
mv ./includes/conf.inc.sample.php ./includes/conf.inc.php
```

# Crawlers
All scrapy crawlers are stored under ./crawler/archivebinger/spiders. You can run the spiders manually like so:

```sh
.crawler/crawlerenv/bin/scrapy crawl typefinder -a starturl='https://comic.com/first-page' -a secondurl='https://comic.com/second-page' -a cid='crawldata.json'
```

This spider will find a reference to the second page on the first page of a comic and note that for the crawler. The output file, crawldata.json, contains variables used in args in the following:

```sh
.crawler/crawlerenv/bin/scrapy crawl superbinge -a starturl="https://comic.com/any-page" -a position="inner" -a tag="rel" -a identifier="next"
```

This will launch a crawler through all of the pages of the referenced comic.

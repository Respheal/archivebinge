#!/full/path/to/archivebinge/crawler/crawlerenv/bin/python2.7
'''Grabs comics that need crawled from the DB and then divvys out the task to
the appropriate crawler. This is the full enchilada crawl which should only be
used for new comics. Use minisup.py to only append new updates'''
from subprocess import Popen, PIPE
from datetime import date, datetime
import datetime, time
import shlex, json, ast

import MySQLdb

nocrawl = []
oldcrawl = []


db = MySQLdb.connect(host="DATABASE_HOST",  # your host
                     user="DATABASE_USER",       # username
                     passwd="DATABASE_PASSWORD",     # password
                     db="DATABASE_NAME")   # name of the database

cur = db.cursor()
cur.execute("SELECT comic_id, comic_crawler FROM comics WHERE last_crawl IS NULL and (comic_crawler like '%taghunt%' or comic_crawler like '%increment%')")

for row in cur.fetchall() :
    derp = ast.literal_eval("{"+row[1]+"}")
    comic = [int(row[0]), derp]
    nocrawl.append(comic)


for comic in nocrawl:
    pages = []
    if "taghunt" in comic[1]['crawler']['type']:
        if "lastpage" in comic[1]['crawler']:
            starturl=comic[1]['crawler']['lastpage']
            nevercrawled = False
        else:
            starturl=comic[1]['crawler']['firstpage']
            nevercrawled = True
        cmd = 'crawlerenv/bin/scrapy crawl superbinge -a starturl="{0}" -a position="{1}" -a tag="{2}" -a identifier="{3}"'.format(starturl, comic[1]['crawler']['position'], comic[1]['crawler']['tag'], comic[1]['crawler']['identifier'])

        process = Popen(shlex.split(cmd), stdout=PIPE)
        #process = Popen(shlex.split(cmd), stdout=PIPE, stderr=PIPE)

        while True:
          line = process.stdout.readline()
          if line != '':
            pages.append(line.rstrip())
          else:
            break

        now = datetime.datetime.now().strftime("%Y-%m-%d %H:%M")
        if "lastpage" in comic[1]['crawler'] and pages[-1]==comic[1]['crawler']['lastpage']:
            #the comic has been crawled before and the last page found this time is the same as the last page found last time, so just update the crawl time
            update_comic = ("update comics set last_crawl=%s where comic_id=%s")
            update_data = (str(now), comic[0])
            cur.execute(update_comic, update_data)
            db.commit()
        else:
            #the last page found isn't the same as last time, so recrawl everything
            if not nevercrawled:
                pages = []
                starturl=comic[1]['crawler']['firstpage']

                cmd = 'crawlerenv/bin/scrapy crawl superbinge -a starturl="{0}" -a position="{1}" -a tag="{2}" -a identifier="{3}"'.format(starturl, comic[1]['crawler']['position'], comic[1]['crawler']['tag'], comic[1]['crawler']['identifier'])

                process = Popen(shlex.split(cmd), stdout=PIPE)

                while True:
                  line = process.stdout.readline()
                  if line != '':
                    pages.append(line.rstrip())
                  else:
                    break


        comic[1]['crawler']['lastpage'] = pages[-1]

        #update pages, last crawl date, last update date if it was blank, add last page to the crawl so we can just start from there next time
        newjson = '"crawler": {0}'.format(str(comic[1]['crawler']))

        #move this out of the if-taghunt once more things have been added
        update_comic = ("update comics set comic_pages=%s, last_crawl=%s, last_update=%s, comic_crawler=%s where comic_id=%s")
        update_data = (str(pages), str(now), str(now), newjson, comic[0])
        cur.execute(update_comic, update_data)

        # Make sure data is committed to the database
        db.commit()

    if "increment" in comic[1]['crawler']['type']:
        if "lastpage" in comic[1]['crawler']:
            starturl=comic[1]['crawler']['lastpage']
            nevercrawled = False
        else:
            starturl=comic[1]['crawler']['firstpage']
            nevercrawled = True
        cmd = 'crawlerenv/bin/scrapy crawl superincrement -a starturl="{0}"'.format(starturl)

        process = Popen(shlex.split(cmd), stdout=PIPE)

        while True:
          line = process.stdout.readline()
          if line != '':
            pages.append(line.rstrip())
          else:
            break

        now = datetime.datetime.now().strftime("%Y-%m-%d %H:%M")
        if "lastpage" in comic[1]['crawler'] and pages[-1]==comic[1]['crawler']['lastpage']:
            #the comic has been crawled before and the last page found this time is the same as the last page found last time, so just update the crawl time
            update_comic = ("update comics set last_crawl=%s where comic_id=%s")
            update_data = (str(now), comic[0])
            cur.execute(update_comic, update_data)
            db.commit()
        else:
            #the last page found isn't the same as last time, so recrawl everything
            if not nevercrawled:
                pages = []
                starturl=comic[1]['crawler']['firstpage']

                cmd = 'crawlerenv/bin/scrapy crawl superincrement -a starturl="{0}"'.format(starturl)

                process = Popen(shlex.split(cmd), stdout=PIPE)

                while True:
                  line = process.stdout.readline()
                  if line != '':
                    pages.append(line.rstrip())
                  else:
                    break


        comic[1]['crawler']['lastpage'] = pages[-1]

        #update pages, last crawl date, last update date if it was blank, add last page to the crawl so we can just start from there next time
        newjson = '"crawler": {0}'.format(str(comic[1]['crawler']))

        #move this out of the if-taghunt once more things have been added
        update_comic = ("update comics set comic_pages=%s, last_crawl=%s, last_update=%s, comic_crawler=%s where comic_id=%s")
        update_data = (str(pages), str(now), str(now), newjson, comic[0])
        cur.execute(update_comic, update_data)

        # Make sure data is committed to the database
        db.commit()

cur.close()
db.close()

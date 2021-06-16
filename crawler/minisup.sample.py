#!/full/path/to/crawlerenv/bin/python2.7
'''Grabs comics that need crawled from the DB and then divvys out the task to
the appropriate crawler. This is the mini version that only grabs that last
few pages. Use supervisor.py for a complete crawl.'''
from subprocess import Popen, PIPE
import arrow
import shlex, json, ast
import MySQLdb

nocrawl = []
oldcrawl = []

db = MySQLdb.connect(host="DATABASE_HOST",  # your host
                     user="DATABASE_USER",       # username
                     passwd="DATABASE_PASSWORD",     # password
                     db="DATABASE_NAME")   # name of the database

cur = db.cursor()
cur.execute("SELECT comic_id, comic_crawler, comic_pages FROM comics WHERE (comic_status='Ongoing' or comic_status='On Hiatus') and ((last_crawl < NOW() - INTERVAL 12 hour and last_crawl IS not NULL and comic_crawler like '%taghunt%') or (last_crawl < NOW() - INTERVAL 12 hour and last_crawl IS not NULL and comic_crawler like '%increment%') or (comic_crawler like '%smackjeeves%' and (last_crawl < NOW() - INTERVAL 12 hour or last_crawl is NULL)) or (comic_crawler like '%webtoons%' and (last_crawl < NOW() - INTERVAL 12 hour or last_crawl is NULL)))")

for row in cur.fetchall() :
    derp = ast.literal_eval("{"+row[1]+"}")
    comic = [int(row[0]), derp, ast.literal_eval(row[2])]
    nocrawl.append(comic)

for comic in nocrawl:
    print comic[0]
    pages=comic[2]
    newpages = []
    if "taghunt" in comic[1]['crawler']['type']:
        starturl=pages[-1]
        cmd = 'crawlerenv/bin/scrapy crawl superbinge -a starturl="{0}" -a position="{1}" -a tag="{2}" -a identifier="{3}"'.format(starturl, comic[1]['crawler']['position'], comic[1]['crawler']['tag'], comic[1]['crawler']['identifier'])

        try:
            process = Popen(shlex.split(cmd), stdout=PIPE)

            while True:
              line = process.stdout.readline()
              if line != '':
                newpages.append(line.rstrip())
              else:
                break

            print process.stderr
            now = arrow.now('America/New_York').format("YYYY-MM-DD HH:mm:ss")
            if newpages[-1]==pages[-1]:
                #the last page found this time is the same as the last page found last time, so just update the crawl time
                update_comic = ("update comics set last_crawl=%s where comic_id=%s")
                update_data = (str(now), comic[0])
                cur.execute(update_comic, update_data)
                db.commit()
            else:
                print "{0} has updated".format(comic[0])
                #the last page found isn't the same as last time, so add the new pages on to the existing ones
                pages.extend(page for page in newpages if page not in pages)

                #update pages, last crawl date, last update date if it was blank, add last page to the crawl so we can just start from there next time
                newjson = '"crawler": {0}'.format(str(comic[1]['crawler']))

                #move this out of the if-taghunt once more things have been added
                update_comic = ("update comics set comic_pages=%s, last_crawl=%s, last_update=%s, comic_crawler=%s where comic_id=%s")
                update_data = (str(pages), str(now), str(now), newjson, comic[0])
                cur.execute(update_comic, update_data)

                # Make sure data is committed to the database
                db.commit()
                #print pages
        except:
            pass

    if "increment" in comic[1]['crawler']['type']:
        starturl=pages[-1]
        cmd = 'crawlerenv/bin/scrapy crawl superincrement -a starturl="{0}"'.format(starturl)

        process = Popen(shlex.split(cmd), stdout=PIPE)

        while True:
          line = process.stdout.readline()
          if line != '':
            newpages.append(line.rstrip())
          else:
            break

        print process.stderr
        now = arrow.now('America/New_York').format("YYYY-MM-DD HH:mm:ss")
        if newpages:
            if newpages[-1]==pages[-1]:
                #the last page found this time is the same as the last page found last time, so just update the crawl time
                update_comic = ("update comics set last_crawl=%s where comic_id=%s")
                update_data = (str(now), comic[0])
                cur.execute(update_comic, update_data)
                db.commit()
            else:
                print "{0} has updated".format(comic[0])
                #the last page found isn't the same as last time, so add the new pages on to the existing ones
                pages.extend(page for page in newpages if page not in pages)

                #update pages, last crawl date, last update date if it was blank, add last page to the crawl so we can just start from there next time
                newjson = '"crawler": {0}'.format(str(comic[1]['crawler']))

                #move this out of the if-taghunt once more things have been added
                update_comic = ("update comics set comic_pages=%s, last_crawl=%s, last_update=%s, comic_crawler=%s where comic_id=%s")
                update_data = (str(pages), str(now), str(now), newjson, comic[0])
                cur.execute(update_comic, update_data)

                # Make sure data is committed to the database
                db.commit()
                #print pages
        else:
            #comic bugged out
            update_comic = ("update comics set last_crawl=%s where comic_id=%s")
            update_data = (str(now), comic[0])
            cur.execute(update_comic, update_data)
            db.commit()

    if "tapas" in comic[1]['crawler']['type']:
        if pages:
            starturl=pages[0]
            cmd = 'crawlerenv/bin/scrapy crawl supertapas -a starturl="{0}"'.format(starturl.replace("\\",""))
            process = Popen(shlex.split(cmd), stdout=PIPE)

            while True:
              line = process.stdout.readline()
              if line != '':
                newpages.append(line.rstrip())
              else:
                break

            print process.stderr
            now = arrow.now('America/New_York').format("YYYY-MM-DD HH:mm:ss")

            if len(newpages) == len(pages):
                update_comic = ("update comics set last_crawl=%s where comic_id=%s")
                update_data = (str(now), comic[0])
                cur.execute(update_comic, update_data)
                db.commit()
            else:
                print "{0} has updated".format(comic[0])
                #move this out of the if-taghunt once more things have been added
                update_comic = ("update comics set comic_status=%s, comic_pages=%s, last_crawl=%s, last_update=%s where comic_id=%s")
                update_data = ("ongoing", str(newpages), str(now), str(now), comic[0])
                cur.execute(update_comic, update_data)

                # Make sure data is committed to the database
                db.commit()
        else:
            #if a Tapas comic gets deleted, pages will be blank. Mark comic as cancelled and move on
            update_comic = ("update comics set comic_status=%s, last_crawl=%s where comic_id=%s")
            update_data = ("Deleted", str(now), comic[0])
            cur.execute(update_comic, update_data)
            db.commit()

    if "smackjeeves" in comic[1]['crawler']['type']:
        starturl=comic[1]['crawler']['firstpage']
        cmd = 'crawlerenv/bin/scrapy crawl superjeeves -a starturl="{0}"'.format(starturl.replace("\\",""))
        process = Popen(shlex.split(cmd), stdout=PIPE)

        while True:
          line = process.stdout.readline()
          if line != '':
            newpages.append(line.rstrip())
          else:
            break

        print process.stderr
        now = arrow.now('America/New_York').format("YYYY-MM-DD HH:mm:ss")

        if len(newpages) == len(pages):
            update_comic = ("update comics set last_crawl=%s where comic_id=%s")
            update_data = (str(now), comic[0])
            cur.execute(update_comic, update_data)
            db.commit()
        else:
            print "{0} has updated".format(comic[0])
            #move this out of the if-taghunt once more things have been added
            update_comic = ("update comics set comic_pages=%s, last_crawl=%s, last_update=%s where comic_id=%s")
            update_data = (str(newpages), str(now), str(now), comic[0])
            cur.execute(update_comic, update_data)

            # Make sure data is committed to the database
            db.commit()

    if "webtoons" in comic[1]['crawler']['type']:
        if pages:
            starturl=pages[0]
            cmd = 'crawlerenv/bin/scrapy crawl superwebtoons -a starturl="{0}"'.format(starturl.replace("\\",""))
            process = Popen(shlex.split(cmd), stdout=PIPE)

            while True:
              line = process.stdout.readline()
              if line != '':
                newpages.append(line.rstrip())
              else:
                break

            print process.stderr
            now = arrow.now('America/New_York').format("YYYY-MM-DD HH:mm:ss")

            if len(newpages) == len(pages):
                update_comic = ("update comics set last_crawl=%s where comic_id=%s")
                update_data = (str(now), comic[0])
                cur.execute(update_comic, update_data)
                db.commit()
            else:
                print "{0} has updated".format(comic[0])
                #move this out of the if-taghunt once more things have been added
                update_comic = ("update comics set comic_pages=%s, last_crawl=%s, last_update=%s where comic_id=%s")
                update_data = (str(newpages), str(now), str(now), comic[0])
                cur.execute(update_comic, update_data)

                # Make sure data is committed to the database
                db.commit()
        else:
            #if a WT comic gets deleted, pages will be blank. Mark comic as cancelled and move on
            update_comic = ("update comics set comic_status=%s, last_crawl=%s where comic_id=%s")
            update_data = ("Deleted", str(now), comic[0])
            cur.execute(update_comic, update_data)
            db.commit()

cur.close()
db.close()

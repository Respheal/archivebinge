from urlparse import urlparse
import scrapy, json, re

pages = []
iteration = 0
increment = 0
pagebase = ""

class Increment(scrapy.Spider):
    name = "increment"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, cid, count=0, *args, **kwargs):
        super(Increment, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]
        self.cid = cid
        self.count = int(count)

    def parse(self, response):
        cid = self.cid
        next_page = None
        global iteration
        global increment
        global pagebase
        count = self.count
        if not pagebase:
            pagebase = response.url
            url = response.url
        else:
            url = "{0}{1}".format(pagebase,increment)
        pageregex = "([0-9]{1}\d*$)"
        m = re.search(pageregex, url)
        sub = m.group(0) #the number at the end that indicates the page number
        index = url.rfind(sub) #index of the start of the number
        pagebase = url[:index] #everything but the page number
        increment = int(m.group(0))+1
        thispage = "{0}{1}".format(pagebase,increment-1)
        next_page = "{0}{1}".format(pagebase,increment)
        
        try:
            comic
        except NameError:
            comic = {'pages': []}

        pagetag = "//a[contains(@href, '{}')]".format(str(increment))

        pages.append(response.url)
        if response.xpath(pagetag).extract_first() and next_page and (iteration <= count or count <= 0):
            #if the next page is real, crawl that next
            iteration = iteration+1
            yield response.follow(next_page, callback=self.parse, dont_filter=True)

        #add pages to the comic[page] list if they aren't already in there
        comic['pages'].extend(page for page in pages if page not in comic['pages'])

        #write to db
        writeJson(comic, cid)

def writeJson(comic, cid):
    filepath = '{}.pagefound'.format(cid)
    with open(filepath, "w") as dbrow:
        json.dump(comic, dbrow, indent=4, ensure_ascii=False)

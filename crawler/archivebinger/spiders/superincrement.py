from urlparse import urlparse
import scrapy, json, re

increment = 0
pagebase = ""

class SuperIncrement(scrapy.Spider):
    name = "superincrement"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, *args, **kwargs):
        super(SuperIncrement, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]

    def parse(self, response):
        next_page = None
        global increment
        global pagebase

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

        pagetag = "//a[contains(@href, '{}')]".format(str(increment))

        print response.url
        if response.xpath(pagetag).extract_first() and next_page:
            #if the next page is real, crawl that next
            yield response.follow(next_page, callback=self.parse, dont_filter=True)

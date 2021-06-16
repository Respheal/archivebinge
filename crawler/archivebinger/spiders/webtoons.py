from urlparse import urlparse
import scrapy, json

class Webtoons(scrapy.Spider):
    name = "webtoons"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, cid, *args, **kwargs):
        super(Webtoons, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]
        self.cid = cid

    def parse(self, response):
        cid = self.cid
        pages = []
        comic = {'pages': []}

        links = response.xpath("//li[contains(@data-episode-no,'')]/a/@href").extract()
        pages = [str(x) for x in links if "&episode_no=" in x]        

        #add pages to the comic[page] list if they aren't already in there
        comic['pages'].extend(page for page in pages if page not in comic['pages'])

        #write to db
        writeJson(comic, cid)

def writeJson(comic, cid):
    filepath = '{}.pagefound'.format(cid)
    with open(filepath, "w") as dbrow:
        json.dump(comic, dbrow, indent=4, ensure_ascii=False)

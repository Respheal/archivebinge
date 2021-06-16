from urlparse import urlparse
import scrapy, json

class Archive(scrapy.Spider):
    name = "archive"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, cid, *args, **kwargs):
        super(Archive, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]
        self.cid = cid

    def parse(self, response):
        cid = self.cid
        next_page = None
        pages = []
        comic = {'pages': []}

        links = response.xpath("//a[contains(@href, 'chapter') or contains(@href, 'comic') or contains(@href, 'page')]/@href").extract()
        for link in links:
            if "http" not in link and link not in response.url and "http" not in link:
            # strip everything, but then make it a full link again
                currenturl = urlparse(response.url)
                baseurl = "{uri.scheme}://{uri.hostname}/".format(uri=currenturl)
                pageurl = "{}{}".format(baseurl,link)
                pages.append(pageurl)
                

        
        #add pages to the comic[page] list if they aren't already in there
        comic['pages'].extend(page for page in pages if page not in comic['pages'])

        
        
        #write to db
        writeJson(comic, cid)

def writeJson(comic, cid):
    filepath = '{}.pagefound'.format(cid)
    with open(filepath, "w") as dbrow:
        json.dump(comic, dbrow, indent=4, ensure_ascii=False)

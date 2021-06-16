from urlparse import urlparse
import scrapy, json

class Smackjeeves(scrapy.Spider):
    name = "smackjeeves"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, cid, *args, **kwargs):
        super(Smackjeeves, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]
        self.cid = cid

    def parse(self, response):
        #$_SESSION["newComic"]["firstPage"] = "https://www.smackjeeves.com/api/discover/articleList?titleNo=".$titleno['titleNo'];
        cid = self.cid
        pages = []
        comic = {'pages': []}

        jsonresponse = json.loads(response.body_as_unicode())
        links = jsonresponse['result']['list']

        pages = [str(x['articleUrl']) for x in links ]        

        #add pages to the comic[page] list if they aren't already in there
        comic['pages'].extend(page for page in pages if page not in comic['pages'])

        #write to db
        writeJson(comic, cid)

def writeJson(comic, cid):
    filepath = '{}.pagefound'.format(cid)
    with open(filepath, "w") as dbrow:
        json.dump(comic, dbrow, indent=4, ensure_ascii=False)

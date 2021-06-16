from urlparse import urlparse
import scrapy, json, js2xml

class OldTapas(scrapy.Spider):
    name = "oldtapas"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, cid, *args, **kwargs):
        super(Tapas, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]
        self.cid = cid

    def parse(self, response):
        cid = self.cid
        pages = []
        comic = {'pages': []}

        js = response.xpath('//script[contains(text(), "episodeList")]/text()').extract_first()
        jstree = js2xml.parse(js)
        for thing in jstree.xpath('//property[@name="id"]'):
            episode = "https://tapas.io/episode/{}".format(js2xml.jsonlike.make_dict(thing)[1])
            pages.append(episode)       
        
        #add pages to the comic[page] list if they aren't already in there
        comic['pages'].extend(page for page in pages if page not in comic['pages'])

        #write to db
        writeJson(comic, cid)

def writeJson(comic, cid):
    filepath = '{}.pagefound'.format(cid)
    with open(filepath, "w") as dbrow:
        json.dump(comic, dbrow, indent=4, ensure_ascii=False)

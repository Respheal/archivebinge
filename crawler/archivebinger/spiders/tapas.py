from urlparse import urlparse
import scrapy, json

pages = []
iteration = 0

class TapasSpider(scrapy.Spider):
    name = "tapas"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, cid, count=0, *args, **kwargs):
        super(BingeSpider, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]
        self.cid = cid
        self.count = int(count)

    def parse(self, response):
        next_page = None
        global iteration
        count = self.count
        cid = self.cid
        deadends = ["#", "/comic", "None", "/comics/"]
        
        comic = getJson(self.cid)
        if not 'pages' in comic:
            comic['pages'] = []

        if "tapas.io" in response.url:
            next_episode_number = response.xpath('//div[@data-next-id]/@data-next-id').extract_first()
            next_page = "https://tapas.io/episode/{0}".format(next_episode_number)

        if next_page:
            print next_page
            #strip next page url of extra params
            #wait this will be a problem for comics that need those params ew
            makeurl = urlparse(next_page)
            realpage = "{uri.scheme}://{uri.hostname}{uri.path}".format(uri=makeurl)
            if "?" in next_page:
                realpage = "{0}?{uri.query}".format(realpage, uri=makeurl)
            print realpage

            #is there REALLY a next page or just an infinite loop?
            if realpage == response.url or next_page in deadends or next_page == response.url:
                next_page = None
            pages.append(response.url)
            
            #if the next page is real, crawl that next
            if next_page and (iteration <= count or count <= 0):
                iteration = iteration + 1
                yield response.follow(next_page, callback=self.parse, dont_filter=True)
        else:
            print "no links found"

        #add pages to the comic[page] list if they aren't already in there
        comic['pages'].extend(page for page in pages if page not in comic['pages'])

        #write to db
        writeJson(comic, cid)

def getJson(cid):
    with open(cid, 'r') as comic:
        return json.load(comic)

def writeJson(comic, cid):
    pagedump = "{}.pagefound".format(cid)
    with open(pagedump, "w") as dbrow:
        json.dump(comic, dbrow, indent=4, ensure_ascii=False)

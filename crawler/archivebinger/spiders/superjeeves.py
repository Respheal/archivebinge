from urlparse import urlparse
import scrapy, json

class Superjeeves(scrapy.Spider):
    name = "superjeeves"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, *args, **kwargs):
        super(Superjeeves, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]

    def parse(self, response):
        #$_SESSION["newComic"]["firstPage"] = "https://www.smackjeeves.com/api/discover/articleList?titleNo=".$titleno['titleNo'];
        pages = []
        comic = {'pages': []}

        jsonresponse = json.loads(response.body_as_unicode())
        links = jsonresponse['result']['list']
        pages = [str(x['articleUrl']) for x in links ]

        for page in pages:
            print page

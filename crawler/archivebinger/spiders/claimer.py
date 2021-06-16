from urlparse import urlparse
import scrapy, json, js2xml

class Claim(scrapy.Spider):
    name = "claim"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, *args, **kwargs):
        super(Claim, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]

    def parse(self, response):
        claim = str(response.xpath('//meta[@name="archive-binge"]/@content').extract_first())
        print claim

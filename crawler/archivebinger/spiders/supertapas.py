from urlparse import urlparse
import scrapy, json, js2xml

class SuperTapas(scrapy.Spider):
    name = "supertapas"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, *args, **kwargs):
        super(SuperTapas, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]

    def parse(self, response):
        pages = []
        comic = {'pages': []}

        js = response.xpath('//script[contains(text(), "episodeList")]/text()').extract_first()
        jstree = js2xml.parse(js)
        for thing in jstree.xpath('//property[@name="id"]'):
            episode = "https://tapas.io/episode/{}".format(js2xml.jsonlike.make_dict(thing)[1])
            print episode     
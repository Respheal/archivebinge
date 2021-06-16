from urlparse import urlparse
import scrapy, json

class SuperWebtoons(scrapy.Spider):
    name = "superwebtoons"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, *args, **kwargs):
        super(SuperWebtoons, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]

    def parse(self, response):
        pages = []
        seen = set()
        comic = {'pages': []}

        links = response.xpath("//li[contains(@data-episode-no,'')]/a/@href").extract()
        pages = [str(x) for x in links if "&episode_no=" in x and not (x in seen or seen.add(x))]

        #add pages to the comic[page] list if they aren't already in there
        for page in pages:
            print page
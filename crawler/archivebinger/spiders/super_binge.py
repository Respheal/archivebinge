from urlparse import urlparse
import scrapy, json

pages = []
#crawlerenv/bin/scrapy crawl superbinge -a starturl="http://comic.com/page" -a position="inner" -a tag="id" -a identifier="next"

class SuperbingeSpider(scrapy.Spider):
    name = "superbinge"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, position, tag, identifier, *args, **kwargs):
        super(SuperbingeSpider, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]
        self.position = position
        self.tag = tag
        self.identifier = identifier

    def parse(self, response):
        next_page = None
        position = self.position
        tag = self.tag
        identifier = self.identifier
        deadends = ["#", "/comic", "None", "/comics/"]

        try:
            if position == "inner":
                finderstring = '//a[contains(@{0}, "{1}")]/@href'.format(
                        tag,
                        identifier
                        )
                next_page = str(
                        response.xpath(
                            finderstring
                        ).extract_first())
            elif position == "outer":
                finderstring = '//div[contains(@{0}, "{1}")]/a/@href'.format(
                        tag,
                        identifier
                        )
                next_page = str(
                        response.xpath(
                            finderstring
                        ).extract_first())
            else:
                finderstring = '//a[img[contains(@{0},"{1}")]]/@href'.format(
                        tag,
                        identifier
                        )
                next_page = str(
                        response.xpath(
                            finderstring
                        ).extract_first())


            if "None" in next_page:
                finderstring = '//link[contains(@{0}, "{1}")]/@href'.format(
                        tag,
                        identifier
                        )
                next_page = str(
                        response.xpath(
                            finderstring
                        ).extract_first())


            if "None" in next_page:
                finderstring = '//a[div[contains(@{0},"{1}")]]/@href'.format(
                        tag,
                        identifier
                        )
                next_page = str(
                        response.xpath(
                            finderstring
                        ).extract_first())

            if "tapas.io" in response.url:
                next_episode_number = response.xpath('//div[@data-next-id]/@data-next-id').extract_first()
                next_page = "https://tapas.io/episode/{0}".format(next_episode_number)

            if next_page:
                #strip next page url of extra params
                #wait this will be a problem for comics that need those params ew
                makeurl = urlparse(next_page)
                realpage = "{uri.scheme}://{uri.hostname}{uri.path}".format(uri=makeurl)
                if "?" in next_page:
                    realpage = "{0}?{uri.query}".format(realpage, uri=makeurl)
                makeurl = urlparse(response.url)
                cleanresponse = "{uri.scheme}://{uri.hostname}{uri.path}".format(uri=makeurl)
                if "?" in next_page:
                    cleanresponse = "{0}?{uri.query}".format(cleanresponse, uri=makeurl)

                #print realpage
                #is there REALLY a next page or just an infinite loop?
                if realpage == cleanresponse or next_page in deadends or next_page in response.url:
                    next_page = None
                #pages.append(response.url)
            print response.url

                #if the next page is real, crawl that next
            if next_page:
                yield response.follow(next_page, callback=self.parse, dont_filter=True)
        except:
            pass
        #add pages to the comic[page] list if they aren't already in there

        #pages.extend(page for page in pages if page not in pages)

        #write to db
        #print pages

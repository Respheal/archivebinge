from urlparse import urlparse
import scrapy, json

class TypeFinder(scrapy.Spider):
    name = "typefinder"
    handle_http_status = ['303', '302', '301']

    def __init__(self, starturl, secondurl, cid, *args, **kwargs):
        super(TypeFinder, self).__init__(*args, **kwargs)
        self.start_urls = [starturl]
        self.secondurl = secondurl
        self.cid = cid

    def parse(self, response):
        parsed_uri = urlparse(self.secondurl)
        subpath = '{uri.path}'.format(uri=parsed_uri)
        if "?" in self.secondurl:
            subpath = '{0}?{uri.query}'.format(subpath, uri=parsed_uri)
        #print subpath
        tagtypes = ['id', 'rel', 'class', 'title']
        positions = ['inner', 'outer', 'container']
        badtags = ['None', 'nofollow']
        loopcounter = 0
        identifier = "None"
        cid = self.cid
        
        try:
            while (identifier == "None"):
                if "tapas.io" in response.url:
                    identifier = "tapas"
                    break
                for tag in tagtypes:
                    position, identifier = pickTag(response, subpath, tag, positions[loopcounter])
                    if not identifier in badtags:
                        correcttag = tag
                        correctposition = positions[loopcounter]
                        break
                loopcounter = loopcounter + 1
			
            if "tapas.io" in response.url:
                comic = {
                        'crawler': {
                            'type': 'tapas',
                            'tag': 'tapas',
                            'position': 'tapas',
                            'identifier': 'tapas',
                            }
                        }

            else:
                comic = {
                        'crawler': {
                            'type': 'taghunt',
                            'tag': correcttag,
                            'position': correctposition,
                            'identifier': identifier,
                            }
                        }
            writeToJson(comic, cid)
        except:
            print "Page Crawl wasn't able to find a path to page three. Please try one of the other crawlers."

def pickTag(response, subpath, tagtype, position):
    if position == "inner":
        identifier = "None"
        #position = "inner"
        realstring = '//a[contains(@href,"{0}")]/@{1}'.format(subpath, tagtype)
        identifier = str(response.xpath(realstring).extract_first())
        if "None" in identifier:
            realstring = '//link[contains(@href,"{0}")]/@{1}'.format(subpath, tagtype)
            identifier = str(response.xpath(realstring).extract_first())
    elif position == "outer":
        #position = "outer"
        realstring = '//div[a[contains(@href,"{0}")]]/@{1}'.format(subpath, tagtype)
        identifier = str(response.xpath(realstring).extract_first())
    else:
        #position = "container"
        realstring = '//a[contains(@href,"{0}")]//img/@{1}'.format(subpath, tagtype)
        identifier = str(response.xpath(realstring).extract_first())
        if "None" in identifier:
            realstring = '//a[contains(@href,"{0}")]//div/@{1}'.format(subpath, tagtype)
            identifier = str(response.xpath(realstring).extract_first())
        pass
    return position, identifier

def writeToJson(comic, cid):
    filepath = '{}'.format(cid)
    with open(filepath, "w") as dbrow:
        json.dump(comic, dbrow, indent=4, ensure_ascii=False)

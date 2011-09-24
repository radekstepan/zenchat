#! /usr/bin/env python

import urllib
import urllib2
from urllib2 import Request, urlopen, URLError, HTTPError
import simplejson
import htmllib

class ShoutboxClient(object):
    # are we authenticated?
    authenticated = False
    # auth details
    username = ''

    opener = urllib2.build_opener(urllib2.HTTPCookieProcessor())

    # init global opener
    def __init__(self):
        urllib2.install_opener(ShoutboxClient.opener)

    # unescape html entities
    def htmlUnescape(self, input):
        parser = htmllib.HTMLParser(None)
        parser.save_bgn()
        parser.feed(input)
        return parser.save_end()

    # authenticate with the server
    def auth(self, username, password, serverAddress):
        self.username = username # save username so we can compare if has changed
        params = urllib.urlencode(dict(username=username, password=password))
        request = urllib2.Request(serverAddress + '/secure/')
        request.add_header('User-Agent', 'ZenChat/0.1') # custom identifier for our client app
	try:
		f = ShoutboxClient.opener.open(request, params)
	except HTTPError, e:
		print 'The server couldn\'t fulfill the request.'
		print 'Error code: ', e.code
		return 'fail'
	except URLError, e:
		print 'We failed to reach a server.'
		print 'Reason: ', e.reason
		return 'fail'
	else:
		response = simplejson.load(f)
		return response['status'] # the status of authentication
		f.close()

    # post a message
    def add(self, messageText, username, password, serverAddress):
        # authenticate first
        if ShoutboxClient.authenticated != True or username != self.username:
            self.auth(username, password, serverAddress)
            ShoutboxClient.authenticated = True

        messageText = messageText.encode('utf-8') # take care of diacritics
        params = urllib.urlencode(dict(text=messageText)) # urlencode
        f = ShoutboxClient.opener.open(serverAddress + '/shoutbox/add/', params)
        simplejson.load(f)
        f.close()

    # fetch initial messages
    def get(self, username, password, serverAddress):
        # authenticate first
        if ShoutboxClient.authenticated != True or username != self.username:
            response = self.auth(username, password, serverAddress)
            # have we succeeded in authentication?
            if response == 'fail': return False
            ShoutboxClient.authenticated = True

        f = ShoutboxClient.opener.open(serverAddress + '/shoutbox/get/-1')
        # reconstruct into Python object
        messages = simplejson.load(f)
        f.close()

        # traverse & unescape
        for i in range(0, len(messages)):
            messages[i]['text'] = self.htmlUnescape(messages[i]['text'])
            messages[i]['author'] = self.htmlUnescape(messages[i]['author'])
        return messages

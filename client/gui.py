#!/usr/bin/python

import wx
import wx.html as html

from threading import Thread
import time
import sys

import client # our ShoutboxClient

# Presenter to give results from Model (client or db) to the GUI
class Presenter():
    # create the client
    shoutbox = client.ShoutboxClient()

    def getAllMessages(self, username, password, serverAddress):
        # get the messages array
        result = Presenter.shoutbox.get(username, password, serverAddress)
        if (result != False):
            # convert array to formatted html
            messagesHtml = ''
            for i in range(0, len(result)):
                messagesHtml += '<b><font color="#004646">"'+result[i]['text']+'"</font></b><br>' \
                    '- <font color="#333">'+result[i]['author']+', '+result[i]['time']+'</font><br><br>'
            return messagesHtml
        else: return False

    def sendMessage(self, messageText, username, password, serverAddress):
        Presenter.shoutbox.add(messageText, username, password, serverAddress)

# authenticate with ZenChat server dialog
class AuthenticateDialog(wx.Dialog):
    username = ''
    password = ''
    server = ''

    def __init__(self, parent, id, title):
        wx.Dialog.__init__(self, parent, id, title, size=(250, 210))

        panel = wx.Panel(self, -1)
        vbox = wx.BoxSizer(wx.VERTICAL)

        wx.StaticText(panel, -1, 'Server:', (15, 20))
        self.server = wx.TextCtrl(panel, -1, 'http://127.0.0.1/active/ZenChat', (100, 15), size=(130, 30))
        wx.StaticText(panel, -1, 'Username:', (15, 60))
        self.username = wx.TextCtrl(panel, -1, 'python', (100, 55), size=(130, 30))
        wx.StaticText(panel, -1, 'Password:', (15, 100))
        self.password = wx.TextCtrl(panel, -1, 'client', (100, 95), size=(130, 30))

        hbox = wx.BoxSizer(wx.HORIZONTAL)
        loginButton = wx.Button(self, 4, 'Login', size=(70, 30))
        wx.EVT_BUTTON(self, 4, self.onLogin)
        closeButton = wx.Button(self, 5, 'Close', size=(70, 30))
        wx.EVT_BUTTON(self, 5, self.onClose)
        hbox.Add(loginButton, 1)
        hbox.Add(closeButton, 1, wx.LEFT, 5)

        vbox.Add(panel)
        vbox.Add(hbox, 1, wx.ALIGN_CENTER | wx.TOP | wx.BOTTOM, 10)

        self.SetSizer(vbox)

    def showErrorMessage(self):
        dialog = wx.MessageDialog(None, 'Please fill in all the fields',
        'Error', wx.OK | wx.ICON_ERROR)
        dialog.ShowModal()
        
    # events
    def onLogin(self, event):
        # XXX: check these are not blank!
        Gui.username = self.username.GetValue()
        Gui.password = self.password.GetValue()
        Gui.server = self.server.GetValue()
        if Gui.username != '' and Gui.password != '' and Gui.server != '':
            self.Close()
        else: self.showErrorMessage()

    def onClose(self, event):
        self.Close()

# app settings like refresh rate
class SettingsDialog(wx.Dialog):
    refresh = ''

    def __init__(self, parent, id, title):
        wx.Dialog.__init__(self, parent, id, title, size=(250, 120))

        panel = wx.Panel(self, -1)
        vbox = wx.BoxSizer(wx.VERTICAL)

        wx.StaticText(panel, -1, 'Refresh (s):', (15, 20))
        self.refresh = wx.TextCtrl(panel, -1, str(GUI.refresh), (100, 15), size=(130, 30))

        hbox = wx.BoxSizer(wx.HORIZONTAL)
        saveButton = wx.Button(self, 4, 'Save', size=(70, 30))
        wx.EVT_BUTTON(self, 4, self.onSave)
        closeButton = wx.Button(self, 5, 'Close', size=(70, 30))
        wx.EVT_BUTTON(self, 5, self.onClose)
        hbox.Add(saveButton, 1)
        hbox.Add(closeButton, 1, wx.LEFT, 5)

        vbox.Add(panel)
        vbox.Add(hbox, 1, wx.ALIGN_CENTER | wx.TOP | wx.BOTTOM, 10)

        self.SetSizer(vbox)

    # events
    def onSave(self, event):
        Gui.refresh = int(self.refresh.GetValue()) # convert to int from string input
        self.Close()

    def onClose(self, event):
        self.Close()

# works as a Front Controller and a View
class Gui(wx.Frame):
    # link to the Presenter
    presenter = Presenter()
    # holder for the panels we will be displaying
    panelsBox = wx.BoxSizer()
    # the window with messages
    htmlwin = ''

    # username, password & server address
    username = ''
    password = ''
    server = ''
    refresh = 5

    # initialize the GUI
    def __init__(self, parent, id, title):
        wx.Frame.__init__(self, parent, id, title, size=(400, 400))

        panel = wx.Panel(self, -1)

        vbox = wx.BoxSizer(wx.VERTICAL)
        hbox = wx.BoxSizer(wx.HORIZONTAL)

        # toolbar
        toolbar = self.CreateToolBar(wx.TB_HORIZONTAL)
        toolbar.AddLabelTool(1, 'Authenticate', wx.Bitmap('icons/group.png'))
        #toolbar.AddLabelTool(2, 'Send', wx.Bitmap('icons/chat.png'))
        toolbar.AddLabelTool(3, 'Settings', wx.Bitmap('icons/config.png'))
        self.Bind(wx.EVT_TOOL, self.onAuthenticate, id=1) # authenticate with the server dialog
        self.Bind(wx.EVT_TOOL, self.onSettings, id=3) # app settings dialog
        toolbar.Realize()

        # send message box
        sendMessageBox = wx.BoxSizer(wx.HORIZONTAL)
        self.messageInput = wx.TextCtrl(panel, -1) # input field
        sendMessageBox.Add(self.messageInput, 1)
        self.messageInput.Bind(wx.EVT_KEY_DOWN, self.onKeypress) # keypress event
        sendButton = wx.Button(panel, 0, 'Send', size=(70, 30)) # button
        sendMessageBox.Add(sendButton, 0, wx.LEFT | wx.BOTTOM , 5)
        wx.EVT_BUTTON(self, 0, self.onSend) # click event
        vbox.Add(sendMessageBox, 0, wx.EXPAND | wx.LEFT | wx.RIGHT | wx.TOP, 10)

        # messages window
        self.htmlwin = html.HtmlWindow(panel, -1, style=wx.SUNKEN_BORDER)
        self.htmlwin.SetBackgroundColour(wx.RED)
        self.htmlwin.SetStandardFonts()

        vbox.Add((-1, 10), 0)
        vbox.Add(self.htmlwin, 1, wx.EXPAND | wx.ALL, 9)
        
        panel.SetSizer(vbox)
        self.Centre()
        self.Show(True)

        # show authentication screen
        self.onAuthenticate(0)

        # get the initial batch of messages in HTML
        self.updateMessages
        wx.CallAfter(self.pollServer) # CallAfter to have a thread after GUI is shown

    # poll the server for updates
    def pollServer(self):
        # check that we have auth details available
        if self.username != '':
            self.updateMessages() # check for new messages
        wx.FutureCall(self.refresh*1000, self.pollServer) # poll again in 5 seconds

    def updateMessages(self):
        result = Gui.presenter.getAllMessages(self.username, self.password, self.server)
        if (result != False):
            self.htmlwin.SetPage(result)
        else: wx.MessageDialog(None, 'Please check your login and password.', 'Failed to authenticate',
        wx.OK | wx.ICON_EXCLAMATION).ShowModal()

    # events
    def onKeypress(self, event):
        keycode = event.GetKeyCode()
        if (keycode == wx.WXK_RETURN):
            self.onSend(event) # send message
        event.Skip()

    def onSend(self, event):
        messageText = self.messageInput.GetValue() # get the text of the message
        if (len(messageText) > 0):
            self.messageInput.SetValue('') # clear the input field
            # send the message
            Gui.presenter.sendMessage(messageText, self.username, self.password, self.server)

    def onAuthenticate(self, event):
        dialog = AuthenticateDialog(None, -1, 'Authenticate')
        dialog.ShowModal()
        dialog.Destroy()
    
    def onSettings(self, event):
        dialog = SettingsDialog(None, -1, 'Settings')
        dialog.ShowModal()
        dialog.Destroy()

    def onClose(self, event):
        self.Close()

app = wx.App()
GUI = Gui(None, -1, 'ZenChat 0.1')
app.MainLoop()

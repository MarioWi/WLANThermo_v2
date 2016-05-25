#!/usr/bin/python
# coding=utf-8

# Copyright (c) 2015 Mario Wicke
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Benötigt installiertes Paket telepot
# sudo pip install telepot
# sudo pip install telepot --upgrade  # UPGRADE
# Inspieriert von https://github.com/nickoala/telepot/blob/master/examples/diceyclock.py

import telepot
import sys
import time
import os
import signal

import random
import datetime


class Telegram(object):
	def __init__(self, Token, Bot, ChatID):
		self.Telegram_token = Token
		self.Telegram_Chat_ID = ChatID
		self.bot = Bot

		print self.Telegram_token
		print self.Telegram_Chat_ID

	def handle(self, msg):
		
		content_type, chat_type, chat_id = telepot.glance(msg)

		if self.Telegram_Chat_ID == 0:
			ID = chat_id
		else:
			ID = self.Telegram_Chat_ID
			
		try:
			userName = msg['from']['first_name']
		except:
			userName = ""
		try:
			userName = userName+" "+msg['from']['last_name']
		except:
			userName = userName
		
		if (content_type == 'text'):
			command = msg['text']

			print 'Got command: %s' % command

			if command == '/roll':
				self.bot.sendMessage(chat_id, random.randint(1,6))
			elif command == '/time':
				self.bot.sendMessage(chat_id, str(datetime.datetime.now()))
			if  '/whoiam' in command:
				if ID !=0 and ID != chat_id:
					self.bot.sendMessage(chat_id, 'Sorry You have no permission for this Bot!')
					return
				self.bot.sendMessage(ID, "Name: "+userName+ "; Chat-ID: " +str(chat_id))

				
			

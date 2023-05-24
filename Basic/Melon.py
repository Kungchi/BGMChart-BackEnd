import requests
import os
import subprocess
import regex as re  # Using regex package instead of re

from bs4 import BeautifulSoup
from pymongo import MongoClient
from dotenv import load_dotenv

# MongoDB Connection
load_dotenv()
mongo_connection_string = os.getenv('MONGO_CONNECTION_STRING')
mongoClient = MongoClient(mongo_connection_string)

# User-Agent
headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
}


# Clear the MongoDB collection
mongoCollection = mongoClient['Music']['Melon'] 
mongoCollection.delete_many({})

# URL to scrape
url = "https://www.melon.com/chart/index.htm"

# Request the page
response = requests.get(url, headers=headers)

# Parse the page with BeautifulSoup
soup = BeautifulSoup(response.content, 'html.parser')

documents = []
pattern = re.compile(r"[^\p{Hangul}\p{Latin}\p{Nd}\s'\u2019&]+", re.UNICODE)  # Corrected the Unicode character

rank_nodes = soup.select('td > div.wrap.t_center > span.rank')
title_nodes = soup.select('div.ellipsis.rank01 > span > a')
singer_nodes = soup.select('div.ellipsis.rank02 > span.checkEllipsis')

for index in range(len(rank_nodes)):
    title_text = title_nodes[index].text
    title_filtered = re.sub(pattern, '', title_text)
    title_filtered = title_filtered.upper().replace('PROD BY', 'PROD').strip()

    documents.append({
        'rank': int(rank_nodes[index].text),
        'title': title_filtered,
        'singer': singer_nodes[index].text,
    })

# Insert into MongoDB
mongoCollection.insert_many(documents)
subprocess.run(['sudo', 'python3', '/home/ubuntu/BGM_Back/Basic/Bugs.py'])

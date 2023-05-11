import requests
import time
import regex as re  # Using regex package instead of re
import os

from bs4 import BeautifulSoup
from pymongo import MongoClient
from dotenv import load_dotenv

# MongoDB Connection
load_dotenv()
mongo_connection_string = os.getenv('MONGO_CONNECTION_STRING')
mongoClient = MongoClient(mongo_connection_string)

while True:
    # Connect to MongoDB
    mongoCollection = mongoClient['Music']['Melon'] 
    mongoCollection.delete_many({})

    # User-Agent
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
    }

    # URL to scrape
    url = "https://music.bugs.co.kr/chart"

    # Request the page
    response = requests.get(url, headers=headers)

    # Parse the page with BeautifulSoup
    soup = BeautifulSoup(response.content, 'html.parser')

    documents = []
    pattern = re.compile(r"[^\p{Hangul}\p{Latin}\p{Nd}\s'\u2019&]+", re.UNICODE)

    rank_nodes = soup.select('div.ranking > strong')
    title_nodes = soup.select('p.title > a')
    singer_nodes = soup.select('td.left > p.artist > a:nth-child(1)')

    for index in range(len(rank_nodes)):
        title_text = title_nodes[index].text
        title_filtered = re.sub(pattern, '', title_text)
        title_filtered = title_filtered.upper().replace('PROD BY', 'PROD').replace('’', "'").strip()

        documents.append({
            'rank': int(rank_nodes[index].text),
            'title': title_filtered,
            'singer': singer_nodes[index].text,
        })

    # Insert into MongoDB
    mongoCollection.insert_many(documents)

    # Sleep for an hour
    time.sleep(3600)

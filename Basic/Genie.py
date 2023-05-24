import requests
import subprocess
import regex as re  # Using regex package instead of re
import os

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

# Connect to MongoDB
mongoCollection = mongoClient['Music']['Genie']
mongoCollection.delete_many({})

pattern = re.compile(r"[^\p{Hangul}\p{Latin}\p{Nd}\s'\u2019&]+", re.UNICODE)

for i in range(1, 3):
    # URL to scrape
    url = f"https://www.genie.co.kr/chart/top200?ditc=D&ymd=20230418&hh=23&rtm=Y&pg={i}"

    # Request the page
    response = requests.get(url, headers=headers)

    # Parse the page with BeautifulSoup
    soup = BeautifulSoup(response.content, 'html.parser')

    documents = []

    rank_nodes = soup.select('td.number')
    title_nodes = soup.select('a.title.ellipsis')
    singer_nodes = soup.select('td.info > a:nth-child(2)')

    for index in range(len(rank_nodes)):
        title_text = title_nodes[index].text
        title_filtered = re.sub(pattern, '', title_text)
        title_filtered = title_filtered.upper().replace('PROD BY', 'PROD').replace('’', "'").strip()

        documents.append({
            'rank': int(rank_nodes[index].text.split('\n')[0]),  # Take the first part of the text, which is the rank
            'title': title_filtered,
            'singer': singer_nodes[index].text,
        })

    # Insert into MongoDB
    mongoCollection.insert_many(documents)
    
subprocess.run(['sudo', 'python3', '/home/ubuntu/BGM_Back/Basic/Merge.py'])

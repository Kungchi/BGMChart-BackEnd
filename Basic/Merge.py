import re
import textdistance
import os
import subprocess

from pymongo import MongoClient
from operator import itemgetter
from dotenv import load_dotenv

# Normalization and comparison functions
def normalize_title(title):
    title = title.lower()
    title = re.sub('\s+', '', title)
    title = re.sub('[^a-z가-힣0-9]+', '', title)
    return title

def normalize_singer(singer):
    singer = re.sub('\([^)]*\)', '', singer)
    singer = singer.lower()
    singer = re.sub('\s+', '', singer)
    singer = re.sub('[^a-z가-힣0-9]+', '', singer)
    return singer

def jaro_winkler(str1, str2):
    return textdistance.jaro_winkler(str1, str2)

# MongoDB Connection
load_dotenv()
mongo_connection_string = os.getenv('MONGO_CONNECTION_STRING')
mongoClient = MongoClient(mongo_connection_string)

testCol = mongoClient['Music']['test']
testCol.delete_many({})

pipeline = [
    {'$project': {'rank': 1, 'title': 1, 'singer': 1, 'source': {'$literal': 'melon'}}},
    {'$unionWith': {'coll': 'Bugs', 'pipeline': [{'$project': {'rank': 1, 'title': 1, 'singer': 1, 'source': {'$literal': 'bugs'}}}]}},
    {'$unionWith': {'coll': 'Genie', 'pipeline': [{'$project': {'rank': 1, 'title': 1, 'singer': 1, 'source': {'$literal': 'genie'}}}]}},
    {'$sort': {'rank': 1}},
    {'$out': 'test'}
]

mongoClient['Music']['Melon'].aggregate(pipeline, allowDiskUse=True)

merged_data = list(testCol.find())

for item in merged_data:
    item['normalizedTitle'] = normalize_title(item['title'])
    item['normalizedSinger'] = normalize_singer(item['singer'])

threshold = 0.1
grouped_data = []

for value in merged_data:
    is_grouped = False

    for group in grouped_data:
        if (value['normalizedSinger'] == group['normalizedSinger'] and
            jaro_winkler(value['normalizedTitle'], group['normalizedTitle']) >= threshold and
            value['normalizedTitle'][2:5] == group['normalizedTitle'][2:5]):

            group['values'].append(value)
            is_grouped = True
            break

    if not is_grouped:
        grouped_data.append({
            'values': [value],
            'normalizedTitle': value['normalizedTitle'],
            'normalizedSinger': value['normalizedSinger']
        })

for group in grouped_data:
    total_rank = sum(value['rank'] for value in group['values'])
    total_count = len(group['values'])
    group['averageRank'] = total_rank / total_count

final_data = []

for group in grouped_data:
    final_data.append({
        'title': group['values'][0]['title'],
        'singer': group['values'][0]['singer'],          
        'rank': int(group['averageRank'])
    })

final_data.sort(key=itemgetter('rank'))
testCol.delete_many({})
testCol.insert_many(final_data[:100])
subprocess.Popen(['sudo', 'python3', '/home/ubuntu/BGM_Back/Basic/Img.py'])
    


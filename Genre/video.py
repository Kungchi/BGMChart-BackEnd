import requests
from bs4 import BeautifulSoup

def updateSong(collection):
    songs = collection.find()

    for song in songs:
        title = song.get('track_name')
        singer = song.get('artist_name')

        # Google 검색 URL
        search_url = f"https://www.google.com/search?q={title}%20{singer}&tbm=vid"

        # 검색결과 가져오기
        headers = {
            "User-Agent":"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36"
        }
        response = requests.get(search_url, headers=headers)
        soup = BeautifulSoup(response.text, 'html.parser')
        print(response.status_code)
        
        # 첫 번째 a태그 검색
        a_tag = soup.select_one('div.ct3b9e > div.DhN8Cf > a')
        video_link = a_tag.get('href') if a_tag else None

        # 첫 번째 링크가 YouTube가 아니라면 두 번째 링크 검색
        if video_link not in "https://www.youtube.com":
            a_tags = soup.select('div.ct3b9e > div.DhN8Cf > a')
            if len(a_tags) > 1: # 두 개 이상의 일치하는 태그가 있는지 확인
                a_tag = a_tags[1] # 두 번째 태그 선택
                video_link = a_tag.get('href') if a_tag else None
                
        # MongoDB에 업데이트
        if video_link:
            collection.update_one(
                {'_id': song['_id']},
                {'$set': {'video_link': video_link}}
            )

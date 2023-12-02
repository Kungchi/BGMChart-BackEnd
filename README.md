# BGMChart-BackEnd
BGMChart 백엔드(각 음원사이트 순위 종합 및 뮤직 플레이어)

# ⌛개발 기간
2023.04 ~ 2023.11

### 👯팀원
* 팀장 : 김상훈 - 각 음원사이트 순위 데이터 가져오기 및 병합, 채팅방, 각 장르별 음악정보 크롤링, 음악 스트리밍  
* 팀원 : 사공도현 - 각 음원 순위 변별력 생성, 장르별 음악 추출  

### 💾개발환경
* **Python**
* **AWS EC2 Ubuntu**
* **Visual Studio Code**
* **MongoDB Atlas**
* **Icecast**
* **Liquidsoap**

## 🖥주요기능
1. **[각 음원사이트 순위 데이터 가져오기](https://github.com/Kungchi/BGMChart-BackEnd/wiki/%EA%B0%81-%EC%9D%8C%EC%9B%90%EC%82%AC%EC%9D%B4%ED%8A%B8-%EC%88%9C%EC%9C%84-%EB%8D%B0%EC%9D%B4%ED%84%B0-%EA%B0%80%EC%A0%B8%EC%98%A4%EA%B8%B0)**
* 깃허브(https://github.com/gold24park/melon-chart.py) 활용
   
2. **[음원사이트 순위 병합](https://github.com/Kungchi/BGMChart-BackEnd/wiki/%EC%9D%8C%EC%9B%90%EC%82%AC%EC%9D%B4%ED%8A%B8-%EC%88%9C%EC%9C%84-%EB%B3%91%ED%95%A9)**
* MongoDB의 Aggregation Framework를 사용하여 병합
* Jaro-Winkler Distance 알고리즘 사용
  
3. **[각 음원 순위 변별력 생성](https://github.com/Kungchi/BGMChart-BackEnd/wiki/%EA%B0%81-%EC%9D%8C%EC%9B%90-%EC%88%9C%EC%9C%84-%EB%B3%80%EB%B3%84%EB%A0%A5-%EC%83%9D%EC%84%B1)**
* 각 음원순위의 편차를 이용한 변별력 생성
  
4. **[장르별 음악 정보 크롤링](https://github.com/Kungchi/BGMChart-BackEnd/wiki/%EC%9E%A5%EB%A5%B4%EB%B3%84-%EC%9D%8C%EC%95%85-%EC%A0%95%EB%B3%B4-%ED%81%AC%EB%A1%A4%EB%A7%81)**
* spotipy API를 사용해서 특정 장르 정보 가져오기

6. **[장르별 음악 추출](https://github.com/Kungchi/BGMChart-BackEnd/wiki/%EC%9E%A5%EB%A5%B4%EB%B3%84-%EC%9D%8C%EC%95%85-%EC%B6%94%EC%B6%9C)**
* AudioSegment 라이브러리와 pytube 라이브러리를 사용해서 유튜브 음원 추출

8. **[음악 스트리밍](https://github.com/Kungchi/BGMChart-BackEnd/wiki/%EC%9D%8C%EC%95%85-%EC%8A%A4%ED%8A%B8%EB%A6%AC%EB%B0%8D)**
* Liquidsoap와 Icecast를 사용해서 노래 스트리밍 

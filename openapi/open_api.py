#-*- coding:utf-8 -*-
import requests
import re

"""
선거 코드 조회 api

반환값 결과 (순서대로)
num == 인덱스 (쓸데없음)
sgid == 선거ID
sgName == 선거명
sgTypecode == 선거종류코드 1.대통형 2.국회의원 3.시도지사 4.구시군장 5.시도의원 6.구시군의회의원 7.국회의원비례대표 8.광역의원비례대표 9.기초의원비례대표 10.교육의원 11.교육감
sgVotedate == 선거일자
"""
url = 'http://apis.data.go.kr/9760000/CommonCodeService/getCommonSgCodeList'
data={'ServiceKey':'key','numOfRows':'1000'}
resp=requests.get(url,params=data)


"""
결과값이 xml로 반환되기떄문에
xml을 보기 편하게 정리하고 .txt 파일로 저장하기 위함
"""
#xml에서  필요없는 데이터 자름
text=resp.text.split("<numOfRows>")[0]
#xml 태그 없애기 정규식
text=text.replace("  ","")
text = re.sub('<.+?>', '', text, 0, re.I|re.S)

text=text.replace('INFO-00','')
text=text.replace('NORMAL SERVICE','')
text=text.replace("\n","",8)
text=text.replace("\n\n",'')
# text=text.replace("  ",'')

f=open('중앙선거관리위원회_코드정보_선거코드결과.txt','w')
print(text)
for i in range(len(text.split('\n'))):
    print(text.split('\n')[i])
    f.write('||'+text.split('\n')[i])
    if (i+1)%5==0:
        print('')
        f.write('\n')

f.close()

#도움말.txt만들기
surmmary=open('도움말.txt','w')
surmmary.write('순서대로\n')
surmmary.write('num == 인덱스 (쓸데없음)\n')
surmmary.write('sgid == 선거ID\n')
surmmary.write('sgName == 선거명\n')
surmmary.write('sgTypecode == 선거종류코드 1.대통형 2.국회의원 3.시도지사 4.구시군장 5.시도의원 6.구시군의회의원 7.국회의원비례대표 8.광역의원비례대표 9.기초의원비례대표 10.교육의원 11.교육감\n')
surmmary.write('sgVotedate == 선거일자\n')
surmmary.close()
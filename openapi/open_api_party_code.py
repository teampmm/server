#-*- coding:utf-8 -*-
import requests
import re
"""
open_api 로 실행한 결과 (선거 코드 결과 정보)를 가지고 
해당 선거때 출마한 정당들의 정보를 반환받는다 

미리 저장된 open_api의 결과 중앙선거관리위원회_코드정보_선거코드결과.txt에서
******선거 ID******   를 가지고 요청함
"""

"""
선거 코드 (선거 날짜 ) 로 정당을 검색함 
결과 값 순서대로
num == 필요없음
sgId == 선거id
jdName == 정당명
pOder == 순서  (기호 번호를 뜻하는듯 ? )
"""

f= open('중앙선거관리위원회_코드정보_선거코드결과.txt','r')

for v,i in enumerate(f.readlines()):

    try:
        url = 'http://apis.data.go.kr/9760000/CommonCodeService/getCommonPartyCodeList'
        data = {
            'ServiceKey': 'key',
            'numOfRows': '1000', 'sgId': ''+str(i.split('||')[2])}
        result_txt = open('중앙선거관리위원회_.' + str(i.split('||')[3]) + '_' + str(i.split('||')[2]) + '_코드_' + str(i.split('||')[4]) + '.txt', 'w')

        resp = requests.get(url, params=data)

        """
        결과값이 xml로 반환되기떄문에
        xml을 보기 편하게 정리하고 .txt 파일로 저장하기 위함
        """
        # 필요없는 데이터 자름
        text=resp.text.split("<numOfRows>")[0]
        text=text.replace("  ",'')
        # xml 태그 없애기 정규식
        text = re.sub('<.+?>', '', text, 0, re.I|re.S)
        text = text.replace('\n\n', '')
        text = text.replace('\n', '',2)
        text = text.replace('INFO-00', '')
        text = text.replace('NORMAL SERVICE', '')



        num = 1  # 줄바꿈을 위해 임의로 넣은 변수
        for i in range(len(text.split('\n'))):
            print(text.split('\n')[i])
            result_txt.write('||' + text.split('\n')[i])
            if num%4==0:
                result_txt.write('\n')
                print('')
            num+=1
        result_txt.close()
    except IndexError :
        break

#도움말.txt만들기
surmmary=open('도움말.txt','w')
surmmary.write('선거코드결과.txt에 있는 선거ID를 바탕으로 해당 선거때 어떤 정당이 출마했는지를 반환값으로 받음 \n')
surmmary.write('선거 코드 (선거 날짜 ) 로 정당을 검색함 \n')
surmmary.write('결과 값 순서대로\n')
surmmary.write('num == 필요없음\n')
surmmary.write('sgId == 선거id\n')
surmmary.write('jdName == 정당명\n')
surmmary.write('pOder == 순서  (기호 번호를 뜻하는듯 ? )\n')
surmmary.close()
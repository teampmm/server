import requests
import re
import xml.etree.ElementTree as ET
import json
"""

선거구 코드 조회

open_api 로 실행한 결과 (선거 코드 결과 정보)를 가지고 
해당 선거때 선거를 실시한 선거구 정보를 반환받는다 

미리 저장된 open_api의 결과 중앙선거관리위원회_코드정보_선거코드결과.txt에서
선거ID와 선거종류코드 을 가지고 요청함



반환값은
선거ID 와 선거종류코드를 보고 해당 선거날짜에 선거가 열린 지역을 나타냄

"""
"""
저장되는 값
num == 인덱스 (의미없음)
sgid ==  선거 id
sgTypecode == 선거 종류 
sggName == 선거구명	종로구
sgName == 시도명		서울특별시
wiwName==구시군명 	종로구
sggJungsu == 선출정수 	1
sOrder == 순서 		정당 투표 순서인듯 ?
"""

"""
주의사항 : 재·보궐선거의 경우 추가적으로 선거구지역이 추가될 수 있습니다. <<=====  이것때문에 따로 구분자를 쓰지않음
다른txt 파일들은 || 을 구분자로 사용

"""

read_file =open('중앙선거관리위원회_코드정보_선거코드결과.txt','r')
for v,i in enumerate(read_file.readlines()):
    try:
        url = 'http://apis.data.go.kr/9760000/CommonCodeService/getCommonSggCodeList'
        data = {
            'ServiceKey': 'key',
            'numOfRows': '1000', 'sgId': '' + str(i.split('||')[2]),'sgTypecode':str(i.split('||')[4])} # 선거 코드 번호 ,  선거 종류 코드가 들어감
        resp = requests.get(url, params=data)
        resp=resp.text
        if str(i.split('||')[2]) == '20180613':
            print(resp)

        """
        결과값이 xml로 반환되기떄문에
        xml을 보기 편하게 정리하고 .txt 파일로 저장하기 위함
        """

        root=ET.fromstring(resp)
        root=root.find('body').find('items')

        result_file = open(
            '중앙선거관리위원회_' + i.split('||')[3] + '_' + i.split('||')[2] + '_' + i.split('||')[4] + '_선거구.txt','w')
        #하위 tree 반복문 = iter
        for j in root.iter('item'):
            for k in j.iter():
                if k.tag != 'item':
                    # print(k.tag,k.text)
                    try:
                        result_file.write(k.tag+k.text)
                    except TypeError:
                        pass
            result_file.write('\n')
        result_file.close()


    except IndexError:
        break
#도움말.txt만들기
surmmary=open('도움말.txt','w')
surmmary.write('선거코드결과.txt에 있는 선거ID와 선거종류코드를  바탕으로 해당 선거때 어떠한 지역구에서 선거가 열렸는지를 반환함 \n')
surmmary.write('num == 인덱스 (의미없음) \n')
surmmary.write('sgid ==  선거 id\n')
surmmary.write('sgTypecode == 선거 종류\n')
surmmary.write('sggName == 선거구명	종로구\n')
surmmary.write('sgName == 시도명		서울특별시\n')
surmmary.write('wiwName==구시군명 	종로구\n')
surmmary.write('sggJungsu == 선출정수 	1\n')
surmmary.write('sOrder == 순서 		정당 투표 순서인듯 ?\n')
surmmary.close()


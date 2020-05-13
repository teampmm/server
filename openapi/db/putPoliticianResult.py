import openpyxl
import pymysql
from db import dbInfo

"""
정치인 정보를 db에 넣는 스크립트
국회의원현황/국회의원.xlsx 가 있어야함
"""
"""
한글이름
한자이름
영어이름
약력
생년월일
이미지
생성된때
"""
host,user,password,db,charset=dbInfo.디비정보()

conn=pymysql.connect(host=host,user=user,password=password,db=db,charset=charset)

curs=conn.cursor()


load_code=openpyxl.load_workbook('국회의원_현황/국회의원.xlsx',data_only=True)
load_code=load_code['Sheet']
start=0
for i in load_code.rows:
    if (start==1):
        print(i[0].value)
        sql = "INSERT INTO Politician (kr_name,ch_name, en_name, history, birthday, profile_image_url, create_at) VALUES (%s, %s, %s,%s,%s,%s, NOW());"
        curs.execute(sql,(str(i[2].value),str(i[4].value),str(i[3].value),str(i[25].value),int(i[11].value),str(i[7].value)))
    if (i[0].value=='부서코드'):
        start=1
conn.commit()
conn.close()
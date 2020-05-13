import sys
import openpyxl
from db import dbInfo
import pymysql
"""
엑셀에서 정당 이름을 가져와 PartyName 을 참조한후
PartyInfo 컬럼에 해당 선거때 참여한 정당을 추가함 
"""

load_code=openpyxl.load_workbook('garbage/정당정책조회결과.xlsx',data_only=True)
sheet=load_code['Sheet']
start=0
for i in sheet.rows:
    if start==1:
        print(i[2].value)
    if i[2].value=='정당명':
        start=1

host,user,password,db,charset=dbInfo.디비정보()

conn=pymysql.connect(host=host,user=user,password=password,db=db,charset=charset)

curs=conn.cursor()
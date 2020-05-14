import sys
import openpyxl
from db import dbInfo
import pymysql
"""
엑셀에서 정당 이름을 가져와 PartyName 을 참조한후
PartyInfo 컬럼에 해당 선거때 참여한 정당을 추가함
and
PartyPledge 테이블에도 정당 공약 삽입 
"""

host,user,password,db,charset=dbInfo.디비정보()

conn=pymysql.connect(host=host,user=user,password=password,db=db,charset=charset)

curs=conn.cursor()

load_code=openpyxl.load_workbook('garbage/정당정책조회결과.xlsx',data_only=True)
sheet=load_code['Sheet']
start=0
for i in sheet.rows:
    if start==1:
        sql="select * from PartyName where name=%s"
        curs.execute(sql,((i[2].value)))
        idx=curs.fetchone()
        sql = "insert into PartyInfo(party_idx,generation,create_at) values (%s,%s,NOW())"
        curs.execute(sql, (int(idx[0]), 20))
        idx=conn.insert_id()
        for k,j in enumerate(range(7,53,5)):
            if i[j].value == None:
                break
            sql="insert into PartyPledge(party_info_idx,title,content,create_at)values (%s,%s,%s,NOW())"
            curs.execute(sql,(int(idx),(i[j].value),(i[j+1].value)))

    if i[2].value=='정당명':
        start=1
conn.commit()
conn.close()

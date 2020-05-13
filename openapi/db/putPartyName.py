import pymysql
import openpyxl
from db import dbInfo

"""
정당의 이름들을 db에 저장 함

"""
host,user,password,db,charset=dbInfo.디비정보()

conn=pymysql.connect(host=host,user=user,password=password,db=db,charset=charset)

curs=conn.cursor()


load_code=openpyxl.load_workbook('선거참여정당/선거참여정당.xlsx',data_only=True)
names=load_code.sheetnames
result=[]
for i,v in enumerate(names):
    if i>=1:
        sheet=load_code[v]
        for j in sheet.rows:
            if(j[1].value!="정당명"):
                print(j[1].value)
                result.append(j[1].value)
print(len(result))
result=list(set(result))

for i in result:
    sql="insert into PartyName(name) values (%s)"
    curs.execute(sql,(i))
conn.commit()
conn.close()
import pymysql
import openpyxl
from db import dbInfo

"""
정치인 상세정보를 넣는 스크립트
putPolticianResult.py 가 먼저 실행 되어야함 

"""
host,user,password,db,charset=dbInfo.디비정보()

conn=pymysql.connect(host=host,user=user,password=password,db=db,charset=charset)

curs=conn.cursor()


load_code=openpyxl.load_workbook('국회의원_현황/국회의원.xlsx',data_only=True)
load_code=load_code['Sheet']
start=0

for i in load_code.rows:

    sql="select * from Politician where kr_name=%s and ch_name=%s"
    curs.execute(sql,((i[2].value),(i[4].value)))
    query_result=curs.fetchone()
    print(query_result)
    for j in str(i[16].value).replace("대","").split(","):
        if(start==1):
            if j=="None" or j=='20':
                sql="INSERT INTO PoliticianInfo (politician_idx, elect_generation, elect_area, committee_idx, party_idx, office_number, email_id, email_address, aide, secretary, create_at,vote_score,deptCd, num, homepage) " \
                    "VALUES " \
                    "(%s, %s, %s, %s, %s, %s, %s, %s, %s,  %s, NOW(), %s, %s, %s, %s);"
                politician_idx=int(query_result[0])
                elect_generation=20
                elect_area=(i[6].value)
                committee_idx=(i[14].value)
                party_idx=(i[12].value)
                party_idx_sql="select * from PartyName where name=%s"
                curs.execute(party_idx_sql,(party_idx))
                party_idx=curs.fetchone()
                party_idx=party_idx[0]
                office_number=(i[17].value)
                email_id=None
                email_address=None
                try:
                    email_id=str(i[19].value).split("@")[0]
                    email_address=str(i[19].value).split("@")[1]
                except IndexError:
                    pass
                aide=(i[20].value).replace(" ","")
                secretary=((i[21].value)+","+(i[22].value)).replace(" ","")

                deptCd=int(i[0].value)
                num=int(i[1].value)

                vote_score=(i[46].value)


                homepage=str(i[18].value)
                print(politician_idx,elect_generation,elect_area,committee_idx,party_idx,office_number,email_id,email_address)
                print(aide)
                print(secretary)
                print(deptCd,num)
                curs.execute(sql, (politician_idx,elect_generation,elect_area,committee_idx,party_idx,office_number,email_id,email_address,aide,secretary
                                   ,vote_score,deptCd,num,homepage))

            else :
                sql="insert into PoliticianInfo(politician_idx,elect_generation,create_at) values (%s,%s,NOW())"
                curs.execute(sql,(int(query_result[0]),int(j)))
        if (i[0].value == '부서코드'):
            start = 1
conn.commit()
conn.close()
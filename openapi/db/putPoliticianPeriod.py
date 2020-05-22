import pymysql
import openpyxl
import datetime

"""
PolticianGeneration 테이블에 정치인 idx와 선거 대수에 대한 정보가 들어감
"""
conn=pymysql.connect(host="",user="",password="",db="",charset='utf8')
curs=conn.cursor()


load_ex=openpyxl.load_workbook("data.xlsx",data_only=True)
load_data=load_ex['Sheet1']

for i,v in enumerate(load_data.rows):
    if i>0:
        kr_name=v[4].value
        en_name=v[9].value
        ch_name=v[8].value
        start_day=int(str(v[29].value).split("/")[0])
        end_day=int(str(v[29].value).split("/")[1])
        generation=20
        elect_do=v[6].value
        elect_gun=v[7].value
        if elect_gun=='':
            elect_gun=None
        elect_gu=v[5].value
        vote_score=v[20].value
        progress_status=str(v[29].value).split("/")[2][0:2]
        print(kr_name,en_name,ch_name,start_day,end_day,generation,elect_do,elect_gu,elect_gun,vote_score,progress_status)
        sql="select idx from Politician where kr_name=%s and ch_name=%s"
        curs.execute(sql,(kr_name,ch_name))
        politician_idx=curs.fetchone()[0]
        sql="insert into PoliticianGeneration(politician_idx,start_day,end_day,generation,elect_do,elect_gun,elect_gu,vote_score,progress_status,create_at) values (%s,%s,%s,%s,%s,%s,%s,%s,%s,NOW())"
        curs.execute(sql,(politician_idx,start_day,end_day,generation,elect_do,elect_gun,elect_gu,vote_score,progress_status))

        nums=v[14].value
        nums=str(nums).split(",")
        for n in nums:
            if n=='20':
                continue
            else:
                sql = "insert into PoliticianGeneration(politician_idx,start_day,end_day,generation,elect_do,elect_gun,elect_gu,vote_score,progress_status,create_at) values (%s,%s,%s,%s,%s,%s,%s,%s,%s,NOW())"
                curs.execute(sql, (
                politician_idx, None, None, int(n), None, None, None, None,
                progress_status))

conn.commit()
conn.close()
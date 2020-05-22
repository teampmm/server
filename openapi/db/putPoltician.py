import pymysql
import openpyxl
import datetime

"""
정치인 기본 정보 넣기
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
        committee=v[18].value
        if committee=="" or committee=='-':
            committee=None
        birthday=v[16].value
        history=v[12].value
        edu=v[13].value
        if edu=="" or edu=='-':
            edu=None
        sex=v[10].value
        office_number=v[21].value
        if office_number=="" or office_number=='-':
            office_number=None
        email=v[22].value
        if email=="" or email=='-':
            email=None
        soldier=v[15].value
        if soldier=="" or soldier=='-':
            soldier=None
        birth_area=v[17].value
        if birth_area=="" or birth_area=='-':
            birth_area=None
        t=v[24].value
        if t=="" or t=='-':
            t=None
        ins=v[26].value
        if ins=="" or ins=='-':
            ins=None
        b=v[28].value
        if b=="" or b=='-':
            b=None
        f=v[25].value
        if f=="" or f=='-':
            f=None
        y=v[27].value
        if y=="" or y=='-':
            y=None
        # print(kr_name,en_name,ch_name,committee,birthday,history,edu,sex,office_number,email,soldier,birth_area,t,i,b,f,y)
        print(edu,sex,office_number,email,soldier,birth_area,t,ins,b,f,y)

        sql="insert into Politician(kr_name,en_name,ch_name,committee,birthday,history,education,sex,office_number,email,soldier,birth_area,twitter,instagram,blog,facebook,youtube,create_at)" \
            "values (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,NOW())"
        curs.execute(sql,(kr_name,en_name,ch_name,committee,int(birthday),history,edu,sex,office_number,email,soldier,birth_area,t,ins,b,f,y))

conn.commit()
conn.close()

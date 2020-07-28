import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.wait import WebDriverWait

# 크롬드라이버를 메모리상에서만 작업이 이루어지게 하는 옵션
# True = 크롬창 안열림
# False = 크롬창 열림
options = Options()
options.headless = True

# 크롬드라이버 실행파일 불러오기
browser = webdriver.Chrome('C:/ChromDriver/chromedriver.exe', options=options)
# browser = webdriver.Chrome('C:/ChromDriver/chromedriver.exe')
#
# 해당 url 요청
url = "https://kind.krx.co.kr/corpgeneral/corpList.do?method=loadInitPage"
browser.get(url)

while True:
    try:
        company_name_table = browser.find_element_by_xpath('//*[@id="title-contents"]/th[1]/a').is_displayed()
        print(company_name_table)
        if company_name_table == True:
            print("show company_name_table")
            break
    except:
        pass

company_info = []

while True:
    try:
        company_list = browser.find_elements_by_xpath('//*[@id="companysum"]')
        for company_link in company_list:
            company_link.click()
            browser.switch_to.window(browser.window_handles[-1])

            회사명 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[1]/td[1]').text
            표준코드 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[2]/td[1]').text
            설립일 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[3]/td[1]').text
            대표이사 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[4]/td[1]').text
            자본금 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[5]/td[1]').text
            결산월 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[6]/td[1]').text
            업종 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[7]/td[1]').text
            주요제품 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[8]/td[1]').text
            주소 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[9]/td[1]').text
            홈페이지 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[10]/td/a').text
            영문회사명 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[1]/td[2]').text
            종목코드 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[2]/td[2]').text
            시장구분 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[3]/td[2]/strong').text
            종업원수 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[5]/td[2]').text
            전화번호 = browser.find_element_by_xpath('//*[@id="tab-contents"]/table[1]/tbody/tr[6]/td[2]').text

            print(회사명)

            company_info.append(
                [회사명,표준코드,설립일,대표이사,자본금,결산월,업종,주요제품,주소,
                 홈페이지,영문회사명,종목코드,시장구분,종업원수,전화번호])

            browser.close()
            browser.switch_to.window(browser.window_handles[0])

        page_data = browser.find_element_by_xpath('//*[@id="main-contents"]/section[2]/div[2]').text
        current_page = str(page_data).split('\n')[0].split(': ')[1].split('/')[0]
        total_page = str(page_data).split('\n')[0].split(': ')[1].split('/')[1]

        if current_page == total_page:
            break

        # 다음 페이지
        browser.find_element_by_xpath('//*[@id="main-contents"]/section[2]/div[1]/a[13]').click()
        time.sleep(1)

    except:
        pass


for i in company_info:
    print(i)

print(len(i))
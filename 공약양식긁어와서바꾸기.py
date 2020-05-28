file=open('text.txt', encoding='utf-8')

new_text = ""
remove_text = "• "

for i,s in enumerate(file):
    s = str(s).replace('##','·')
    new_text += str(s).replace(remove_text,'$$')
print(new_text.replace('\n',''))
print('미분류는 보지마셈 - 지역 개수: ',len(new_text.split('$!')))
file.close()


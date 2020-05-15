# Politics King - Server 
  - #### 팀노바에서 진행하고 있는 창업 프로젝트 - 서버
  - #### 개발자 : 신동휘, 김종영
  - #### 멘토 : 연성훈

## 프로젝트 설명
  - #### 창업 아이템 : 정치인에 대한 객관적인 정보를 제공해주는 플랫폼
  - #### 프로젝트 기간 : 2020-04-25 ~ 진행중

## API Document URL
* https://teampmm2020.postman.co/collections/11170770-32239f79-f7d1-4342-98ef-15a7fb25c0ee?workspace=5622e2eb-e8eb-451b-8a39-98c1853d0190

## 폴더 구조 및 내용
- ### html
  - #### application - config // Codeigniter의 기본 설정 파일이 들어있다.
    
    - #### config - database.php
      - pmm Database에 연결하는 기능이 있는 파일을 불러오는 php파일.
      
    - #### config - routes.php
      - router 설정 및 CORS요청 처리를 하는 php파일.

- #### application - controller // 클라이언트의 요청을 받고, 필요한 데이터를 Models으로 부터 받아오는 역할
    
    - #### User.php
      - 회원 관련 API Controller
    
    - #### Politician.php
      - 정치인 관련 API Controller
    
    - #### Bill.php
      - 법안 관련 API Controller

    - #### controller - DTO
      - Option.php - 예외처리 관련 Class
      - PolicticsJwt.php - JWT 토큰 관련 Class

- #### application - models // controller가 요청한 데이터를 pmm Database에서 찾아 반환해주는 역할

    - #### UserModel.php
      - 회원 관련 API Model
    
    - #### PoliticianModel.php
      - 정치인 관련 API Model
    
    - #### BillModel.php
      - 법안 관련 API Model

----------------------------------------------------------
- ### openapi
  - #### *.py 파일, (폴더  - *.py파일 ) // db 폴더 제외
    - 공공데이터를 파싱해서 엑셀파일로 저장하는 파일
  
  - #### db 폴더 - *.py 파일    
    - 엑셀파일을 불러와서 pmm Database에 데이터를 추가하는 파일

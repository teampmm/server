# Politics King - Server 
  - #### 팀노바에서 진행하고 있는 창업 프로젝트 - 서버
  - #### 개발자 : 김종영, 신동휘

## 프로젝트 설명
  - #### 창업 아이템 : 정치인에 대한 객관적인 정보를 제공해주는 플랫폼
  - #### 프로젝트 기간 : 2020-04-25 ~ 진행중
----------------------------------------------------------------------------------------------------------------------------------------

## 포스트맨 API 문서 URL
* https://teampmm2020.postman.co/collections/11170770-32239f79-f7d1-4342-98ef-15a7fb25c0ee?workspace=5622e2eb-e8eb-451b-8a39-98c1853d0190

## 폴더 구조 및 내용
  #### MVC pattern 사용중
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


- ### openapi
  - #### *.py 파일, (폴더  - *.py파일 ) // db 폴더 제외
    - 엑셀파일을 만든 후에 공공데이터 포털에서 받은 데이터를 가공 후 정리하여, 엑셀에 데이터를 추가하는 작업을 함.
  
  - #### db 폴더 - *.py 파일    
    - 엑셀파일을 불러와서 pmm Database에 데이터를 추가하는 작업을 함.

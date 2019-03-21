
<p align="center"> 
  <img src="https://raw.githubusercontent.com/xpressengine/plugin-board/master/icon.png">
 </p>

# XE3 Board Plugin
이 플러그인은 Xpressengine3(이하 XE3)의 플러그인입니다.

이 플러그인을 사용하여, 게시판 기능을 사용할 수 있습니다.
<p align="center"> 
  <img src="https://raw.githubusercontent.com/xpressengine/plugin-board/develop/board_preview.png">
 </p>



## What can I do?

본 플러그인을 사용하여 커뮤니티를 구축/운영 함에 있어서 기본적인 게시판 기능을 수행할 수 있으며,
Comment 플러그인과 같이 사용하여 댓글을 작성할 수 있습니다.

그 이외의도 아래의 기능을 지원합니다.

* 카테고리 생성 / 제거 / 게시글 분류
* 댓글 작성 및 삭제
* 익명으로 글 작성 

## Installation specification
* Minimum installation environment
   XE3, PHP 7.0 or later
* Recommended installation environment
   XE3, PHP 7.1 or later

## Caution
본 플러그인과의 의존성이 있는 Comment 플러그인을 삭제하는 경우 정상적인 플러그인 사용이 되지 않을 수 있습니다.



# Installation
### Console
```
$ php artisan plugin:install board
```

### Web install
- 관리자 > 플러그인 & 업데이트 > 플러그인 목록 내에 새 플러그인 설치 버튼 클릭
- `board` 검색 후 설치하기



# Usage
관리자 > 사이트 맵> 사이트 메뉴 편집에서 `아이템 추가` 기능으로 게시판을 추가해서 사용합니다.
게시판 추가는 아래 순서로 가능합니다.
1. `아이템 추가` 클릭
2. Board 선택 후 하단에 `다음` 클릭
3. itemURL, Item Title 등 세부사항 입력
4. 등록

# Option
**게시판 기본 설정**
> **Table Division**
> 
> 생성되는 게시판 데이터를 분리된 데이터베이스 테이블을 사용하도록 하는 설정입니다. 이 설정을 사용함으로 할 경우 데이터베이스에 새로운 테이블이 추가되어 데이터베이스 데이터 레벨에서 부하분산 될 수 있도록 기능을 제공합니다.
>
> **Revision**
>
> 게시물의 버전 관리를 제공합니다. 버전 관리를 사용할 경우 이전 버전으로 되돌리기 기능 등을 사용할 수 있습니다.

## License
이 플러그인은 LGPL라이선스 하에 있습니다. <https://opensource.org/licenses/LGPL-2.1>
![License](http://img.shields.io/badge/license-GNU%20LGPL-brightgreen.svg)

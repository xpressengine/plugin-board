# plugin-board
XpressEngine 3.0 게시판 플러그인 입니다.

[![License](http://img.shields.io/badge/license-GNU%20LGPL-brightgreen.svg)]

# 설치
### Console
```
$ php artisan plugin:install board
```

### Web install
- 관리자 > 플러그인 & 업데이트 > 플러그인 목록 내에 새 플러그인 설치 버튼 클릭
- `board` 입력 후 설치하기

### Ftp upload
- 다음의 페이지에서 다운로드
    * https://store.xpressengine.io/plugins/board
    * https://github.com/xpressengine/plugin-board/releases
- 프로젝트의 `plugin` 디렉토리 아래 `board` 디렉토리명으로 압축해제
- `board` 디렉토리 이동 후 `composer dump` 명령 실행

## 사용
관리자 > 사이트 맵> 사이트 메뉴 편집에서 `아이템 추가` 기능으로 게시판을 추가해서 사용합니다.
게시판 추가는 아래 순서로 가능합니다.
1. `아이템 추가` 클릭
2. Board 선택 후 하단에 `다음` 클릭
3. itemURL, Item Title 등 세부사항 입력
**게시판 기본 설정**
> **Table Division**
> 생성되는 게시판 데이터를 분리된 데이터베이스 테이블을 사용하도록 하는 설정입니다. 이 설정을 사용함으로 할 경우 데이터베이스에 새로운 테이블이 추가되어 데이터베이스 데이터 레벨에서 부하분산 될 수 있도록 기능을 제공합니다.
>
> **Revision**
> 게시물의 버전 관리를 제공합니다. 버전 관리를 사용할 경우 이전 버전으로 되돌리기 기능 등을 사용할 수 있습니다.
4. 하단에 `등록` 클릭

## 게시판 설정
#### 설정 페이지 이동
관리자 > 사이트 맵> 사이트 메뉴 편집에서 `아이템 이름` 클릭 -> 아이템 설정 페이지에서 `상세설정` 클릭

#### 상위 설정에 따름
XE3에서는 상위 설정 개념이 도입되었습니다.
모든 게시판은 상위 설정을 갖으며 플러그인&업데이트 > 플러그인 목록으로 이동하고 목록에서 게시판을 클릭합니다.
페이지 하단 `지원 컴포넌트` 목록에서 Board 의 설정 아이콘을 클릭합니다.(http://yourdomain/settings/module/board@board/global/config)


#### 기본설정
목록 수, 새글 기준 시간, 댓글 사용, 익명글, CAPTCHA, 태그 사용, 새 댓글 알림 등 다양한 설정이 제공됩니다.

* 댓글 설정
게시판에서 사용되는 댓글의 상세 설정은 댓글 사용유무를 선택하는 selectbox 우측의 `설정`을 클릭 후 이동 한 페이지에서 제공됩니다.

#### 권한
목록, 쓰기, 읽기, 관리 권한을 설정할 수 있습니다.

#### 스킨
스킨을 변경하고 스킨이 제공하는 설정을 관리할 수 있습니다.
스킨은 `Desktop`, `Mobile`을 별도로 설정할 수 있습니다.

#### 에디터
게시판에서 사용되는 에디터 설정을 제공합니다.
기본 제공되는 CkEditor 이며 CkEditor의 설정을 수정하기 위해서 `설정` 버튼을 클릭하면 됩니다.
`편집` 을 클릭하여 나오는 하단 selectbox로 다른 에디터를 선택하여 사용할 수 있습니다.

#### 확장 필드
입력 필드 추가 기능을 제공합니다. (XE1의 사용자 정의, ExtraVars)
주소, 숫자 등 입력 필드를 추가할 수 있습니다.

#### 토글 메뉴
게시물 읽기 페이지에의 우측 하단에 추가 기능을 제공합니다.
기본으로 배포되는 Claim 플러그인에서 제공하는 `신고` 기능이 활성화 되어 있습니다.

## 외부 플러그인을 통한 설정 추가
> 개발중입니다.

## 구조

* assets
 웹페이지에서 필요한 stylesheet, javascript, 이미지 같은 파일이 있습니다.

* components
XE3에서 사용되는 컴포넌트가 있습니다.
각 컴포넌트는 독립된 디렉토리에 구현체인 Class 파일과 컴포넌트에서 사용하는 View Blade 파일 등을 포함하고 있습니다.
components 디렉토리는 플러그인의 composer.json 에 정의되어 autoload 됩니다.

* langs
다국어 정의 파일인 lang.php 가 있습니다.

* markup
퍼블리싱된 원본 마크업 파일이 있습니다.

* node_modules
프런트엔드 개발에서 사용하는 Node Module 파일이 있습니다.

* src
게시판에서 사용하는 Class 가 있습니다.
src 디렉토리는 플러그인의 composer.json 에 정의되어 autoload 됩니다.

* vendor (?)
Ftp 방법으로 설치 할 때 `composer dump`를 하면 생성되는 디렉토리 입니다.
Console 이나 Web install 의 방법으로 설치한 경우는 존재하지 않습니다.

* views (x)
0.9.20 버전까지 사용되던 디렉토리 입니다.
0.9.21 버전에서 View Blade 파일이 각 컴포넌트 디렉토리를 이동되어 삭제되었습니다.


## Skin
BoardSkinGenerator 명령어 제공
```
$ php artisan make:board_skin plugin_dir_name skin_dir_name
```

### Skin 구조
컴포넌트는 Class 파일과 View Blade, assets 등 다양한 요소들이 하나의 목적을 위해 동작합니다. XE3에서는 관련된 파일을 한 디렉토리 안에 모와서 배포하는 구조를 추천하고 있습니다.

#### 기본 디렉토리
스킨은 {plugin_dir_name}/components/Skins/Board/{skin_dir_name}/ 에 생성됩니다.
{skin_dir_name} 디렉토리 이름은 CamelCase로 됩니다.

#### 스킨 컴포넌트 구조
{skin_dir_name}Skin.php
assets/
views/
{skin_dir_name} PHP 파일 이름은 CamelCase로 됩니다.


## 주요 업데이트

### 0.9.21
* 디렉토리 구성 변경
[#113 컴포넌트 구성 변경](https://github.com/xpressengine/plugin-board/issues/113) 을 참고하세요.
* autoload 갱신
0.9.21 버전으로 업데이트 후 `composer dump` 해야 합니다. components 디렉토리 추가로 인해 autoload 가 갱신되어야 합니다.
* interception proxy 삭제
storage/app/intercetion 의 php 파일을 삭제해야 합니다.

## License
Copyright 2015 NAVER Corp. <http://www.navercorp.com>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  usage

## Powered By
* Naver D2 : http://d2.naver.com/

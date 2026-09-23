# WordPress.org 디렉터리 자료

공식 Sendgo 본체의 `themes/sendgo/public/svg` 로고와 Pretendard 글꼴을 사용했다.
아이콘은 기존 심볼을 그대로 사용하며, 배너는 공식 보라색 계열과 한·영 문구로 구성했다.

- `icon-128x128.png`, `icon-256x256.png`: 디렉터리 아이콘.
- `banner-772x250*.png`, `banner-1544x500*.png`: 기본 영문 및 `ko_KR` 한국어 배너.
- `screenshot-1*.png`: 임시 WordPress의 실제 Sendgo 설정 화면. 인증 정보는 빈 값이다.

배포 시 PNG 파일만 WordPress.org SVN 최상위 `assets/`에 복사한다.
플러그인 설치 ZIP에는 이 디렉터리를 넣지 않는다.
관리자 화면 로고는 플러그인 내부 `assets/logo.svg`를 사용한다.

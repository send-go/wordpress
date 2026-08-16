=== Sendgo ===
Contributors: sendgo
Tags: kakao, alimtalk, sms, woocommerce, notification
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.2.2
Requires PHP: 8.2
License: MIT
License URI: https://opensource.org/licenses/MIT

카카오 알림톡/브랜드메시지 및 SMS/LMS/MMS 발송과 WooCommerce 주문 알림을 지원하는 Sendgo 연동 플러그인입니다.

== Description ==

Sendgo 플러그인은 한국 메시징 서비스 Sendgo(https://sendgo.io)를 WordPress 및 WooCommerce와 연동합니다.

주요 기능:

* 카카오 알림톡 / 브랜드메시지 발송 (친구톡은 2025-12-31 종료 — 요청은 브랜드메시지로 자동 대체 발송됩니다)
* SMS / LMS / MMS 발송
* WooCommerce 주문 완료 및 처리 중 상태 변경 시 구매자에게 알림톡 자동 발송
* 알림톡 발송 실패 시 SMS 대체 발송 지원
* WordPress 설정 API 기반의 간편한 관리자 설정 화면

이 플러그인은 서버 사이드에서만 동작하며, 인증 키는 서버에 안전하게 저장됩니다.

== Installation ==

1. 플러그인 파일을 `/wp-content/plugins/sendgo` 디렉터리에 업로드하거나 플러그인 화면에서 설치합니다.
2. Composer로 설치하는 경우: `composer require sendgo/wordpress` 후 플러그인 디렉터리에서 `composer install`을 실행하여 `vendor/autoload.php`를 생성합니다.
3. WordPress 관리자 화면에서 플러그인을 활성화합니다.
4. 설정 > Sendgo 메뉴에서 Access Key, Secret Key, 발신 키, API 버전을 입력합니다.
5. WooCommerce를 사용하는 경우, 주문 완료 알림톡 템플릿 코드와 SMS 대체 내용을 설정합니다.

== Frequently Asked Questions ==

= WooCommerce 없이도 사용할 수 있나요? =

네. WooCommerce가 없어도 코어 클라이언트를 통해 알림톡/SMS를 발송할 수 있습니다. WooCommerce 주문 자동 알림 기능만 WooCommerce 활성화 시 동작합니다.

= 인증 키는 어디에서 발급받나요? =

Sendgo 콘솔(https://sendgo.io)에서 발급받을 수 있습니다.

== Changelog ==

= 1.2.2 =
* wordpress.org 배포 준비. 코어 SDK(sendgo/php)를 vendor/ 에 번들해 Composer 없이
  설치·동작하도록 했습니다.
* 플러그인 헤더(1.2.1)와 SENDGO_VERSION 상수(1.1.0)의 버전 불일치 수정.
* 코어 SDK 를 찾지 못하면 조용히 아무것도 하지 않던 문제 수정 — 설치·설정은 정상으로
  보이는데 알림만 안 나가는 상태였습니다. 이제 관리자 알림으로 원인을 표시합니다.
* readme.txt 의 Tested up to 를 WordPress 7.0 으로 갱신하고 변경 이력을 채웠습니다.

= 1.2.1 =
* 레지스트리 목록에 노출되는 플러그인 설명에서 친구톡을 브랜드메시지로 교체.

= 1.2.0 =
* 친구톡 종료(2025-12-31) 반영. 2026-01-01 부터 친구톡 발송 요청은 카카오 측에서
  브랜드메시지(자유형)로 자동 대체 발송됩니다. 문서에 전환 안내와 메시지 타입
  1:1 대응표(FT→BT 등)를 추가했습니다.
* 번들된 코어 SDK를 sendgo/php 1.2.1 로 갱신.

= 1.1.0 =
* 짧은 URL API 지원 (코어 SDK 경유).
* 주문 상태별 템플릿 분리 — 처리 중 → 완료로 넘어가는 주문에 같은 알림이 두 번
  발송되어 이중 과금되던 문제를 수정했습니다.

= 1.0.0 =
* 최초 릴리스: 알림톡/친구톡/SMS 발송 및 WooCommerce 주문 알림 연동.

== Upgrade Notice ==

= 1.2.0 =
친구톡이 2025-12-31 종료되었습니다. 발송 요청은 카카오 측에서 브랜드메시지로 자동
대체되므로 기존 설정은 그대로 동작하지만, 신규 연동은 브랜드메시지를 권장합니다.

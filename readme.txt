=== Sendgo ===
Contributors: sendgo
Tags: kakao, alimtalk, sms, woocommerce, notification
Requires at least: 6.0
Tested up to: 6.6
Stable tag: 1.2.0
Requires PHP: 8.2
License: MIT
License URI: https://opensource.org/licenses/MIT

카카오 알림톡/브랜드메시지 및 SMS/LMS/MMS 발송과 WooCommerce 주문 알림을 지원하는 Sendgo 연동 플러그인입니다.

== Description ==

Sendgo 플러그인은 한국 메시징 서비스 Sendgo(https://sendgo.io)를 WordPress 및 WooCommerce와 연동합니다.

주요 기능:

* 카카오 알림톡 / 브랜드메시지 발송
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

= 1.0.0 =
* 최초 릴리스: 알림톡/친구톡/SMS 발송 및 WooCommerce 주문 알림 연동.

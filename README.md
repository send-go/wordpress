# Sendgo for WordPress / WooCommerce

카카오 알림톡/친구톡 및 SMS/LMS/MMS 발송과 WooCommerce 주문 알림을 지원하는 Sendgo 연동 WordPress 플러그인입니다.

Sendgo 코어 SDK([`sendgo/php`](https://sendgo.io))를 Composer로 번들하여 동작합니다.

## 설치

### 1. Composer로 설치 (권장)

```bash
composer require sendgo/wordpress
```

또는 플러그인 디렉터리에서 직접:

```bash
cd wp-content/plugins/sendgo
composer install
```

`composer install`은 코어 SDK를 포함한 `vendor/autoload.php`를 생성합니다. 이 파일이 있어야 플러그인이 코어 클라이언트를 로드할 수 있습니다.

### 2. 플러그인 활성화

WordPress 관리자 > 플러그인 화면에서 **Sendgo**를 활성화합니다.

## 설정

관리자 화면의 **설정 > Sendgo** 메뉴에서 다음 값을 입력합니다.

| 항목 | 설명 |
| --- | --- |
| Access Key | Sendgo 콘솔에서 발급받은 액세스 키 |
| Secret Key | Sendgo 콘솔에서 발급받은 시크릿 키 |
| 카카오 발신 키 | 알림톡/친구톡 발신 키 |
| SMS 발신 키 | SMS/LMS/MMS 발신 키 |
| API 버전 | `v1` 또는 `v2` 선택 |

Access Key와 Secret Key가 모두 입력되어야 발송 기능이 활성화됩니다.

## WooCommerce 연동

WooCommerce가 활성화되어 있으면 다음 주문 상태 변경 시 구매자 청구 연락처로 알림을 자동 발송합니다.

- `주문 완료` (`woocommerce_order_status_completed`)
- `처리 중` (`woocommerce_order_status_processing`)

**설정 > Sendgo > WooCommerce 주문 알림** 섹션에서 다음을 설정합니다.

- **주문 완료 알림톡 템플릿 코드**: 발송할 알림톡 템플릿 코드. 템플릿의 첫 번째 변수(`var1`)로 주문 번호가 전달됩니다.
- **알림톡 실패 시 SMS 대체 내용**: 알림톡 발송이 실패하거나 템플릿 코드가 없을 때 발송할 SMS 본문. `{order_number}` 플레이스홀더를 사용할 수 있습니다.

발송 실패는 결제/주문 흐름을 절대 중단시키지 않으며, WooCommerce 로그(source: `sendgo`)에 기록됩니다.

## 사용법 (프로그래밍 방식)

플러그인이 로드된 이후에는 코어 클라이언트를 직접 사용할 수 있습니다.

```php
$client = Sendgo_Plugin::instance()->client();

if ($client) {
    // 알림톡 발송
    $client->alimtalk->send([
        'templateCode' => 'ORDER_CONFIRM_001',
        'contacts'     => [['contact' => '01012345678', 'var1' => 'ORD-001']],
    ]);

    // SMS 발송
    $client->sms->sendSms([
        'content'  => '인증번호: 123456',
        'contacts' => [['contact' => '01012345678']],
    ]);
}
```

## FAQ

**Q. WooCommerce 없이도 사용할 수 있나요?**
네. 코어 클라이언트(`Sendgo_Plugin::instance()->client()`)를 통해 알림톡/SMS를 직접 발송할 수 있습니다. 주문 자동 알림만 WooCommerce에 의존합니다.

**Q. 클라이언트가 `null`을 반환합니다.**
Access Key 또는 Secret Key가 설정되지 않았거나, `composer install`을 실행하지 않아 `vendor/autoload.php`가 없는 경우입니다.

**Q. 인증 키는 어디에 저장되나요?**
`sendgo_options` 옵션에 서버 사이드로만 저장됩니다. 프런트엔드에 노출되지 않습니다.

## 라이선스

MIT © Sendgo — https://sendgo.io

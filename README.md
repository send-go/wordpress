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

**설정 > Sendgo > WooCommerce 주문 알림** 섹션에서 **상태별로** 다음을 설정합니다.

| 필드 | 설명 |
| --- | --- |
| `[주문 완료] 알림톡 템플릿 코드` | 완료 상태에서 보낼 템플릿 코드. 첫 번째 변수(`#{var1}`)로 주문 번호가 전달됩니다. |
| `[주문 완료] 알림톡 실패 시 SMS 대체 내용` | 알림톡이 실패하거나 템플릿 코드가 비어 있을 때 보낼 SMS 본문. `{order_number}` 플레이스홀더 사용 가능. |
| `[처리 중] 알림톡 템플릿 코드` | 처리 중 상태에서 보낼 템플릿 코드. |
| `[처리 중] 알림톡 실패 시 SMS 대체 내용` | 처리 중 상태의 SMS 대체 본문. |

**비워둔 상태는 아무것도 발송하지 않습니다.** 두 상태 모두 채우면 주문마다
알림이 두 번(처리 중 + 완료) 발송되므로, 실제로 알려야 하는 상태만 채우세요.

같은 주문·같은 상태로는 한 번만 발송됩니다. 발송에 성공하면 주문 메타
(`_sendgo_notified_completed` / `_sendgo_notified_processing`)에 기록해두기 때문에,
관리자가 주문을 다시 저장하거나 다른 플러그인이 상태를 재설정해도 중복
발송되지 않습니다. 발송이 실패하면 기록하지 않으므로 다음 상태 변경에서 재시도됩니다.

발송 실패는 결제/주문 흐름을 절대 중단시키지 않으며, WooCommerce 로그(source: `sendgo`)에 기록됩니다.

> **1.0.x에서 올라오는 경우** — 예전 버전은 두 상태가 `order_template_code`
> 하나를 공유했기 때문에 처리 중 → 완료로 넘어가는 주문에 같은 알림이 두 번
> 발송됐습니다. 이제 완료 상태만 기존 키를 그대로 쓰고, 처리 중은 새 필드를
> 채워야 발송됩니다. 기존 설정은 그대로 동작하며 중복 발송만 사라집니다.

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

### 브랜드메시지 (친구톡의 후속 채널)

브랜드메시지는 채널 친구가 아닌 수신자에게도 보낼 수 있고(`targeting` = `N`),
수신 동의한 전체 채널 친구에게 동보 발송할 수도 있습니다(`targeting` = `F`).
메시지 타입은 친구톡과 1:1로 대응합니다(`FT`→`BT`, `FI`→`BI`, `FW`→`BW`,
`FL`→`BL`, `FC`→`BC`, `FM`→`BM`, `FP`→`BP`, `FA`→`BA`).

> **v2 전용입니다.** **설정 > Sendgo > API 버전**을 `v2`로 바꿔야 동작합니다.
> 플러그인 기본값은 `v1`입니다.

```php
$client = Sendgo_Plugin::instance()->client();

// 단건 발송 — targeting 이 M/N/I 이면 contacts 가 필요합니다.
$client->brandMessage->send([
    'targeting'          => 'M',
    'messageType'        => 'FL',
    'friendTemplateUuid' => '9cd5460b-6458-4edc-9b11-c26d3013c340',
    'contacts'           => [['contact' => '01012345678', 'var1' => '29,000원']],
]);

// 동보 발송 — 수신 동의한 전체 채널 친구 (수신자 목록 없음)
$result = $client->brandMessage->broadcast([
    'messageType'        => 'FW',
    'friendTemplateUuid' => '9cd5460b-6458-4edc-9b11-c26d3013c340',
]);

// 동보 발송은 업스트림에서 비동기 처리되므로 진행 상황을 조회합니다.
$client->brandMessage->campaign($result['data']['campaignId']);
$client->brandMessage->campaigns(['count' => 10]);
```

### 짧은 URL (클릭 반응 분석)

메시지 본문에 넣는 링크를 줄이고, 실제로 눌렸는지 집계합니다. 문자는 한 통에
들어가는 바이트가 정해져 있어 긴 링크가 그대로 들어가면 LMS 로 넘어가 단가가 올라갑니다.

> **v2 전용입니다.**

```php
$client = Sendgo_Plugin::instance()->client();

// 생성 — 같은 원본 URL 이면 기존 코드를 재사용합니다(forceNew 로 강제 신규 생성).
$link = $client->shortUrl->create([
    'targetUrl' => 'https://shop.example.com/orders/1024',
    'title'     => '10월 주문 안내',
]);

$link['data']['shortUrl'];  // https://sendgo.io/s/aB3xY7z

// 클릭 반응 — 일별 추이 · 디바이스 · 유입경로 · 국가
$client->shortUrl->stats($link['data']['uuid'], ['from' => '2026-08-01', 'to' => '2026-08-11']);

$client->shortUrl->list(['count' => 20]);
$client->shortUrl->show($link['data']['uuid']);

// 중지 — 리다이렉트만 멈추고(410 Gone) 통계는 남습니다.
$client->shortUrl->deactivate($link['data']['uuid']);
```

## FAQ

**Q. WooCommerce 없이도 사용할 수 있나요?**
네. 코어 클라이언트(`Sendgo_Plugin::instance()->client()`)를 통해 알림톡/SMS를 직접 발송할 수 있습니다. 주문 자동 알림만 WooCommerce에 의존합니다.

**Q. 클라이언트가 `null`을 반환합니다.**
Access Key 또는 Secret Key가 설정되지 않았거나, `composer install`을 실행하지 않아 `vendor/autoload.php`가 없는 경우입니다.

**Q. 인증 키는 어디에 저장되나요?**
`sendgo_options` 옵션에 서버 사이드로만 저장됩니다. 프런트엔드에 노출되지 않습니다.

## 변경 사항

### 1.1.0 (2026-08-11)

- **버그 수정: WooCommerce 중복 알림 발송** — 주문 상태가 같은 값으로 다시 저장되면
  알림이 다시 나갔습니다. `_sendgo_notified_{status}` 주문 메타로 상태별 1회만
  발송하도록 막았습니다. 발송이 실패하면 메타를 남기지 않아 다음 상태 변경에서 재시도됩니다.
- 주문 상태별로 템플릿을 따로 지정할 수 있게 옵션을 분리했습니다.
- 브랜드메시지(친구톡 후속 채널) 사용법 추가
- 짧은 URL 사용법 추가

## 라이선스

MIT © Sendgo — https://sendgo.io

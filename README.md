# Sendgo for WordPress / WooCommerce

> **카카오 알림톡·브랜드메시지와 SMS/LMS/MMS 를 발송하고, WooCommerce 주문 상태에 따라 구매자에게 자동으로 알리는 공식 WordPress 플러그인**

Sendgo 코어 SDK([`sendgo/php`](https://github.com/send-go/php))를 번들해 WordPress 에 연결합니다.
관리자 설정 화면에서 키를 넣고, WooCommerce 훅으로 주문 상태가 바뀔 때 구매자에게 알립니다.

PHP 8.2 이상이 필요합니다.

## 설치

### 1. wordpress.org / zip 으로 설치 (권장)

배포 zip 에는 코어 SDK 가 `vendor/` 에 포함되어 있습니다. Composer 없이 그대로 동작합니다.
**관리자 > 플러그인 > 새로 추가**에서 zip 을 업로드하거나 `wp-content/plugins/sendgo` 에 풀어 넣으세요.

### 2. Composer 로 설치

```bash
composer require sendgo/wordpress
```

또는 플러그인 디렉터리에서 직접:

```bash
cd wp-content/plugins/sendgo
composer install
```

소스에서 직접 받은 경우에는 `vendor/autoload.php` 가 없으므로 `composer install` 을 실행해야 합니다.
이 파일이 없으면 플러그인은 발송을 하지 못하고 관리자 화면에 원인을 알리는 오류 알림을 띄웁니다.

### 3. 플러그인 활성화

WordPress 관리자 > 플러그인 화면에서 **Sendgo** 를 활성화합니다.

## 설정

관리자 화면의 **Settings > Sendgo** 메뉴에서 다음 값을 입력합니다.
관리자 화면 문자열은 영어를 원본으로 하며, 한국어는 translate.wordpress.org 의 번역을 통해 표시됩니다.

| 항목 | 설명 |
| --- | --- |
| `Access Key` | 샌드고 콘솔에서 발급받은 액세스 키 |
| `Secret Key` | 샌드고 콘솔에서 발급받은 시크릿 키 |
| `Kakao Sender Key` | 알림톡·브랜드메시지 발신프로필 키 |
| `SMS Sender Key` | SMS/LMS/MMS 발신번호 키 |
| `API Version` | `v1` 또는 `v2` 선택 |

Access Key 와 Secret Key 가 모두 입력되어야 발송 기능이 활성화됩니다. 키는 `sendgo_options`
옵션에 서버 사이드로만 저장되며 프런트엔드에 노출되지 않습니다.

API 버전 기본값은 **`v1`** 입니다. 브랜드메시지와 짧은 URL 은 `v2` 에서만 동작하므로 해당
채널을 쓰려면 바꿔야 합니다.

> API 베이스 URL 은 `url` 옵션에서 읽지만 설정 화면에 필드가 없습니다. 다른 호스트를
> 가리키려면 코드로 지정하세요.
> ```php
> $options = get_option('sendgo_options', []);
> $options['url'] = 'https://staging.sendgo.io';
> update_option('sendgo_options', $options);
> ```
> 1.2.4 이전에는 설정 화면을 한 번 저장하면 이 값이 사라졌습니다. 이제는 저장해도 유지됩니다.

## WooCommerce 연동

WooCommerce가 활성화되어 있으면 다음 주문 상태 변경 시 구매자 청구 연락처로 알림을 자동 발송합니다.

- `주문 완료` (`woocommerce_order_status_completed`)
- `처리 중` (`woocommerce_order_status_processing`)

**Settings > Sendgo > WooCommerce Order Notifications** 섹션에서 **상태별로** 다음을 설정합니다.

| 필드 | 설명 |
| --- | --- |
| `[Order completed] Alimtalk template code` | 완료 상태에서 보낼 템플릿 코드. 첫 번째 변수(`#{var1}`)로 주문 번호가 전달됩니다. |
| `[Order completed] SMS text used if Alimtalk fails` | 알림톡이 실패하거나 템플릿 코드가 비어 있을 때 보낼 SMS 본문. `{order_number}` 플레이스홀더 사용 가능. |
| `[Processing] Alimtalk template code` | 처리 중 상태에서 보낼 템플릿 코드. |
| `[Processing] SMS text used if Alimtalk fails` | 처리 중 상태의 SMS 대체 본문. |

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

### 전화번호 처리

청구 연락처에서 숫자만 남겨 발송합니다(`preg_replace('/[^0-9]/', ...)`). `010-1234-5678` 과
`+82 10 1234 5678` 은 구두점만 제거되며 **국가번호를 변환하지는 않으므로**, API 가 받는
형식의 국내 번호여야 합니다. 청구 연락처가 비어 있는 주문은 조용히 건너뜁니다.

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

**항상 `$client` 를 확인하세요.** 키가 비어 있거나 `vendor/autoload.php` 가 없으면 `null` 이고,
`null` 에 메서드를 호출하면 그 훅이 걸린 페이지가 백지가 됩니다.

채널은 클라이언트의 속성으로 접근합니다.

| 속성 | 채널 |
| --- | --- |
| `$client->alimtalk` | 카카오 알림톡 |
| `$client->friendtalk` | 카카오 친구톡 |
| `$client->brandMessage` | 카카오 브랜드메시지 (v2 전용) |
| `$client->sms` | SMS / LMS / MMS |

클라이언트는 요청 단위로 메모이즈되므로 `Sendgo_Plugin::instance()->client()` 를 여러 번 불러도 비용이 없습니다.

### 내 이벤트에 붙이기

```php
add_action('user_register', function (int $user_id): void {
    $client = Sendgo_Plugin::instance()->client();
    if (!$client) {
        return;
    }

    $user  = get_userdata($user_id);
    $phone = preg_replace('/[^0-9]/', '', (string) get_user_meta($user_id, 'billing_phone', true));

    if ('' === $phone) {
        return;
    }

    try {
        $client->alimtalk->send([
            'templateCode' => 'WELCOME_001',
            'contacts'     => [['contact' => $phone, 'var1' => $user->display_name]],
        ]);
    } catch (\Throwable $e) {
        // 발송 실패가 회원가입을 깨뜨리지 않게 한다.
        error_log('Sendgo welcome message failed: ' . $e->getMessage());
    }
}, 10, 1);
```

`try`/`catch` 로 감싸는 것이 핵심입니다. WordPress 훅 안에서 처리되지 않은
`SendgoException` 은 그 훅을 실행한 페이지의 치명적 오류로 드러납니다.

### 브랜드메시지 (친구톡의 후속 채널)

> **친구톡은 2025-12-31 종료되었습니다.** 2026-01-01 부터 친구톡 발송 요청은 카카오 측에서
> 브랜드메시지(자유형)로 자동 대체 발송됩니다. `friendtalk` 은 여전히 동작하며, 개별 수신자에게
> 보내는 자유 본문 `FT`/`FI`/`FW` 의 유일한 경로이기도 합니다. 템플릿 기반 리치 타입
> (`FL`/`FC`/`FM`/`FP`/`FA`), 친구가 아닌 대상(`N`/`I`), 동보(`F`)에는 브랜드메시지를 쓰세요.

브랜드메시지는 채널 친구가 아닌 수신자에게도 보낼 수 있고(`targeting` = `N`),
수신 동의한 전체 채널 친구에게 동보 발송할 수도 있습니다(`targeting` = `F`).
메시지 타입은 친구톡과 1:1로 대응합니다(`FT`→`BT`, `FI`→`BI`, `FW`→`BW`,
`FL`→`BL`, `FC`→`BC`, `FM`→`BM`, `FP`→`BP`, `FA`→`BA`).

> **v2 전용입니다.** **Settings > Sendgo > API Version** 을 `v2` 로 바꿔야 동작합니다.
> 플러그인 기본값은 `v1` 입니다.

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

동보 발송은 친구 목록 전체에 닿을 수 있으므로, 공개 훅이 아니라 의도적인 관리자 동작
(WP-CLI 명령이나 권한 검사를 붙인 admin-post 핸들러)에서만 호출하세요.

### 짧은 URL (클릭 반응 분석)

메시지 본문에 넣는 링크를 줄이고, 실제로 눌렸는지 집계합니다. 문자는 한 통에
들어가는 바이트가 정해져 있어 긴 링크가 그대로 들어가면 LMS 로 넘어가 단가가 올라갑니다.

> **v2 전용입니다.**

같은 원본 URL 을 다시 줄이면 **기존 링크를 그대로 반환합니다.** 캠페인별로 반응 수치를
따로 보려면 `forceNew` 를 넘겨 새 코드를 만드세요.

`deactivate` 는 링크를 지우지 않고 리다이렉트만 멈춥니다. 이미 발송한 메시지의 링크를
막아야 할 때 쓰며, 쌓인 통계는 남고 방문자는 `410 Gone` 을 받습니다.

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

## 오류 처리

```php
use Sendgo\Php\Exception\SendgoException;

try {
    $client->alimtalk->send([...]);
} catch (SendgoException $e) {
    // 발송 실패는 구매자에게 드러내지 않고 로그로 남긴다.
    if (function_exists('wc_get_logger')) {
        wc_get_logger()->error(
            sprintf('Sendgo %d [%s]: %s', $e->getStatusCode(), $e->getErrorCode(), $e->getMessage()),
            ['source' => 'sendgo']
        );
    }
}
```

메시지 문자열이 아니라 `getErrorCode()` 로 분기하세요. 메시지는 바뀔 수 있고 코드가 계약입니다.
`TOKEN_EXPIRED` 와 `TOKEN_MISMATCH` 는 코어 SDK 안에서 처리됩니다 — 토큰을 재발급하고 요청을 한 번 재시도합니다.

## FAQ

**Q. WooCommerce 없이도 사용할 수 있나요?**
네. 코어 클라이언트(`Sendgo_Plugin::instance()->client()`)를 통해 알림톡/SMS를 직접 발송할 수 있습니다. 주문 자동 알림만 WooCommerce에 의존합니다.

**Q. 클라이언트가 `null`을 반환합니다.**
Access Key 또는 Secret Key 가 설정되지 않았거나, 소스에서 직접 받아 `composer install` 을
실행하지 않아 `vendor/autoload.php` 가 없는 경우입니다. 후자는 관리자 화면에 오류 알림으로 표시됩니다.
배포 zip 에는 `vendor/` 가 포함되어 있으므로 이 경우가 생기지 않습니다.

**Q. 인증 키는 어디에 저장되나요?**
`sendgo_options` 옵션에 서버 사이드로만 저장됩니다. 프런트엔드에 노출되지 않으며, 시크릿 키는
관리자 화면에서 password 필드로 렌더링됩니다.

**Q. 발송이 안 되는데 오류도 없습니다.**
콘솔의 **연동하기 > 연동 정보**에서 호출을 허용할 IP 를 등록했는지 확인하세요. 등록되지 않은
주소에서 온 요청은 거부됩니다. WordPress 사이트가 나가는 IP 를 등록해야 하며, 공유 호스팅이나
CDN 뒤에 있으면 브라우저에 보이는 IP 와 다를 수 있습니다.

**Q. 삭제하면 정리가 되나요?**
`uninstall.php` 가 `sendgo_options` 옵션을 지웁니다. `_sendgo_notified_*` 주문 메타는 남깁니다 —
무해하고, 지우려면 전체 주문에 대량 쓰기를 해야 합니다.

## 변경 사항

### 1.2.4 (2026-08-20)

- readme 와 플러그인 헤더, 번역 가능한 모든 문자열을 영어로 교체했습니다. msgid 가 한국어라
  translate.wordpress.org 에서 번역이 불가능했습니다.
- 연동하는 외부 서비스와 전송되는 데이터를 readme 에 명시했습니다(약관·개인정보처리방침 링크 포함).
- **버그 수정: `url` 옵션 유실** — 설정 화면에 필드가 없는데 정제 목록에 들어 있어서,
  설정을 한 번 저장하면 값이 항상 사라졌습니다. API 베이스 URL 을 재정의해 둔 사이트는
  관리자가 저장 버튼을 누르는 순간 기본값으로 되돌아갔습니다.

### 1.2.3 (2026-08-16)

- 플러그인 헤더의 Plugin URI 와 Author URI 가 같아 wordpress.org 업로드가 거부되던 문제 수정.

### 1.2.2 (2026-08-16)

- wordpress.org 배포 준비 — 코어 SDK(`sendgo/php`)를 `vendor/` 에 번들해 Composer 없이 동작합니다.
- 플러그인 헤더(1.2.1)와 `SENDGO_VERSION` 상수(1.1.0)의 버전 불일치 수정.
- **코어 SDK 를 찾지 못하면 조용히 아무것도 하지 않던 문제 수정** — 설치·활성화·설정까지 정상으로
  보이는데 알림만 안 나가는 상태였습니다. 이제 관리자 알림으로 원인을 표시합니다.

### 1.2.1 (2026-08-14)

- 레지스트리 목록에 노출되는 패키지 설명에서 친구톡을 브랜드메시지로 교체했습니다.
  npm/PyPI/Packagist/Maven/NuGet/RubyGems 검색 결과에 그대로 찍히는 문자열이라
  종료된 채널을 계속 홍보하고 있었습니다.
- 검색 키워드에 `brand-message` 를 추가했습니다 (`friendtalk` 은 유입 검색어라 유지).

### 1.2.0 (2026-08-14)

- **친구톡 Deprecated 표기** — 친구톡은 카카오 정책에 따라 2025-12-31 종료되었고,
  2026-01-01 부터 발송 요청이 브랜드메시지(자유형)로 자동 대체 발송됩니다.
  관련 API 에 각 언어의 표준 deprecation 표기를 달았습니다.
- 자유 본문 타입(`FT`/`FI`/`FW`)의 개별 발송 경로는 아직 친구톡 API 뿐이라는 점을
  문서에 명시했습니다 — 브랜드메시지 API 는 그 조합에 `NOT_A_BRAND_MESSAGE` 를 반환합니다.
- 브랜드메시지 전환 안내와 메시지 타입 1:1 대응표를 README 에 추가했습니다.

### 1.1.0 (2026-08-11)

- **버그 수정: WooCommerce 중복 알림 발송** — 주문 상태가 같은 값으로 다시 저장되면
  알림이 다시 나갔습니다. `_sendgo_notified_{status}` 주문 메타로 상태별 1회만
  발송하도록 막았습니다. 발송이 실패하면 메타를 남기지 않아 다음 상태 변경에서 재시도됩니다.
- 주문 상태별로 템플릿을 따로 지정할 수 있게 옵션을 분리했습니다.
- 브랜드메시지(친구톡 후속 채널) 사용법 추가
- 짧은 URL 사용법 추가

## 연동하는 외부 서비스

이 플러그인은 카카오 알림톡·브랜드메시지와 SMS/LMS/MMS 를 발송하기 위해 샌드고 API
(https://sendgo.io)에 접속합니다. 발송에는 샌드고 계정이 필요하며, 발송은 유료입니다.

- 발송 직전 액세스 키와 시크릿 키로 인증 요청을 보내 단기 토큰을 받습니다.
- WooCommerce 주문이 `처리 중`·`주문 완료` 로 바뀌고 해당 상태에 템플릿 코드나 SMS 대체
  내용이 설정돼 있으면, 구매자 청구 연락처와 주문 번호를 발신 키·템플릿 코드와 함께 보냅니다.
- 코드에서 직접 클라이언트를 호출하면 넘긴 수신 번호와 본문이 그대로 전송됩니다.

설치·활성화만 한 상태나 템플릿 코드·SMS 대체 내용이 모두 비어 있는 상태에서는 아무 데이터도 전송되지 않습니다.

- 이용약관: https://sendgo.io/terms-of-service
- 개인정보처리방침: https://sendgo.io/privacy-policy

## 라이선스

MIT © amuz — https://sendgo.io

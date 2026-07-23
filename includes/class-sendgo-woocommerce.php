<?php
/**
 * WooCommerce 주문 알림 연동.
 *
 * 주문 상태가 완료/처리 중으로 변경되면 구매자 연락처로 알림톡을 발송한다.
 *
 * @package Sendgo
 */

defined('ABSPATH') || exit;

/**
 * WooCommerce 훅과 Sendgo 발송을 연결하는 클래스.
 */
class Sendgo_WooCommerce
{
    /**
     * WooCommerce 주문 상태 변경 훅을 등록한다.
     */
    public function __construct()
    {
        // WooCommerce가 없으면 아무것도 하지 않는다.
        if (!class_exists('WooCommerce')) {
            return;
        }

        add_action('woocommerce_order_status_completed', [$this, 'on_order_completed'], 10, 1);
        add_action('woocommerce_order_status_processing', [$this, 'on_order_processing'], 10, 1);
    }

    /**
     * 주문 완료 시 알림 발송.
     *
     * @param int $order_id 주문 ID.
     */
    public function on_order_completed(int $order_id): void
    {
        $this->notify_order($order_id, 'completed');
    }

    /**
     * 주문 처리 중 시 알림 발송.
     *
     * @param int $order_id 주문 ID.
     */
    public function on_order_processing(int $order_id): void
    {
        $this->notify_order($order_id, 'processing');
    }

    /**
     * 주문 알림을 발송한다.
     *
     * 알림톡 우선, 실패/미설정 시 SMS 대체 내용으로 발송한다.
     * 발송 실패가 결제 흐름을 절대 중단시키지 않도록 전체를 try/catch로 감싼다.
     *
     * @param int    $order_id 주문 ID.
     * @param string $context  상태 컨텍스트 (completed|processing).
     */
    private function notify_order(int $order_id, string $context): void
    {
        try {
            if (!function_exists('wc_get_order')) {
                return;
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }

            // 구매자 연락처 확보 및 정규화.
            $phone = $this->normalize_phone((string) $order->get_billing_phone());
            if ('' === $phone) {
                return;
            }

            $client = Sendgo_Plugin::instance()->client();
            if (null === $client) {
                return;
            }

            $options       = get_option('sendgo_options', []);
            $options       = is_array($options) ? $options : [];
            $template_code = isset($options['order_template_code']) ? trim((string) $options['order_template_code']) : '';

            // 알림톡 변수로 활용할 주문 정보.
            $order_number = (string) $order->get_order_number();

            if ('' !== $template_code) {
                // 알림톡 발송 (실패 시 SMS 자동 대체).
                $sms_content = $this->build_sms_content($options, $order_number, $context);

                $client->alimtalk->send([
                    'templateCode' => $template_code,
                    'contacts'     => [[
                        'contact' => $phone,
                        'var1'    => $order_number,
                    ]],
                    'replaceSms'   => '' !== $sms_content ? 'Y' : 'N',
                    'smsContent'   => '' !== $sms_content ? $sms_content : null,
                ]);

                return;
            }

            // 템플릿 코드가 없으면 SMS로 대체 발송.
            $sms_content = $this->build_sms_content($options, $order_number, $context);
            if ('' === $sms_content) {
                return;
            }

            $client->sms->sendSms([
                'content'  => $sms_content,
                'contacts' => [['contact' => $phone]],
            ]);
        } catch (\Throwable $e) {
            // 발송 실패는 로그만 남기고 결제/주문 흐름에 영향을 주지 않는다.
            if (function_exists('wc_get_logger')) {
                wc_get_logger()->error(
                    'Sendgo 주문 알림 발송 실패: ' . $e->getMessage(),
                    ['source' => 'sendgo']
                );
            }
        }
    }

    /**
     * SMS 대체 내용을 구성한다.
     *
     * @param array<string, mixed> $options      플러그인 옵션.
     * @param string               $order_number 주문 번호.
     * @param string               $context      상태 컨텍스트.
     * @return string 발송할 SMS 본문 (없으면 빈 문자열).
     */
    private function build_sms_content(array $options, string $order_number, string $context): string
    {
        $fallback = isset($options['order_sms_fallback']) ? trim((string) $options['order_sms_fallback']) : '';

        if ('' !== $fallback) {
            // {order_number} 플레이스홀더 치환 지원.
            return str_replace('{order_number}', $order_number, $fallback);
        }

        return '';
    }

    /**
     * 전화번호에서 숫자만 남긴다.
     *
     * @param string $phone 원본 전화번호.
     * @return string 정규화된 번호.
     */
    private function normalize_phone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone) ?? '';
    }
}

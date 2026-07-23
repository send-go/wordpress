<?php
/**
 * Sendgo 플러그인 메인 클래스.
 *
 * @package Sendgo
 */

defined('ABSPATH') || exit;

/**
 * 플러그인 부팅 및 코어 클라이언트 관리를 담당하는 싱글톤.
 */
class Sendgo_Plugin
{
    /**
     * 싱글톤 인스턴스.
     *
     * @var Sendgo_Plugin|null
     */
    private static ?Sendgo_Plugin $instance = null;

    /**
     * 관리자 설정 핸들러.
     *
     * @var Sendgo_Settings|null
     */
    private ?Sendgo_Settings $settings = null;

    /**
     * 메모이즈된 Sendgo 코어 클라이언트.
     *
     * @var \Sendgo\Php\Sendgo|null
     */
    private ?\Sendgo\Php\Sendgo $client = null;

    /**
     * 클라이언트 초기화 시도 여부 (키 누락 시 재시도 방지).
     *
     * @var bool
     */
    private bool $client_built = false;

    /**
     * 싱글톤 인스턴스를 반환한다.
     *
     * @return Sendgo_Plugin
     */
    public static function instance(): Sendgo_Plugin
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 관리자 및 WooCommerce 연동을 초기화한다.
     */
    private function __construct()
    {
        // 관리자 설정 페이지 연결.
        $this->settings = new Sendgo_Settings();

        // WooCommerce가 활성화된 경우에만 주문 연동 초기화.
        if (class_exists('WooCommerce')) {
            new Sendgo_WooCommerce();
        }
    }

    /**
     * 저장된 옵션을 기반으로 Sendgo 코어 클라이언트를 생성/메모이즈한다.
     *
     * access_key 또는 secret_key가 없으면 null을 반환한다.
     *
     * @return \Sendgo\Php\Sendgo|null
     */
    public function client(): ?\Sendgo\Php\Sendgo
    {
        if ($this->client_built) {
            return $this->client;
        }

        $this->client_built = true;

        $options = get_option('sendgo_options', []);
        if (!is_array($options)) {
            $options = [];
        }

        $access_key = isset($options['access_key']) ? trim((string) $options['access_key']) : '';
        $secret_key = isset($options['secret_key']) ? trim((string) $options['secret_key']) : '';

        // 필수 키가 없으면 클라이언트를 만들 수 없음.
        if ('' === $access_key || '' === $secret_key) {
            return null;
        }

        // 코어 SDK가 오토로드되지 않은 경우 방어.
        if (!class_exists('\Sendgo\Php\Sendgo')) {
            return null;
        }

        $api_version = isset($options['api_version']) ? (string) $options['api_version'] : 'v1';
        $url         = isset($options['url']) && '' !== trim((string) $options['url'])
            ? trim((string) $options['url'])
            : 'https://sendgo.io';

        $this->client = new \Sendgo\Php\Sendgo([
            'access_key'       => $access_key,
            'secret_key'       => $secret_key,
            'kakao_sender_key' => isset($options['kakao_sender_key']) ? (string) $options['kakao_sender_key'] : null,
            'sms_sender_key'   => isset($options['sms_sender_key']) ? (string) $options['sms_sender_key'] : null,
            'api_version'      => $api_version,
            'url'              => $url,
        ]);

        return $this->client;
    }

    /**
     * 관리자 설정 핸들러를 반환한다.
     *
     * @return Sendgo_Settings|null
     */
    public function settings(): ?Sendgo_Settings
    {
        return $this->settings;
    }
}

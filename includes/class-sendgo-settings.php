<?php
/**
 * Sendgo 관리자 설정 페이지.
 *
 * WordPress Settings API를 사용하여 설정 > Sendgo 메뉴를 제공한다.
 *
 * @package Sendgo
 */

defined('ABSPATH') || exit;

/**
 * 관리자 설정 화면과 옵션 등록을 담당하는 클래스.
 */
class Sendgo_Settings
{
    /**
     * 옵션 키 이름.
     *
     * @var string
     */
    private const OPTION_NAME = 'sendgo_options';

    /**
     * 설정 페이지 슬러그.
     *
     * @var string
     */
    private const PAGE_SLUG = 'sendgo-settings';

    /**
     * 관리자 훅을 등록한다.
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * 설정(options-general.php) 하위에 메뉴를 추가한다.
     */
    public function add_menu(): void
    {
        add_options_page(
            __('Sendgo Settings', 'sendgo'),
            __('Sendgo', 'sendgo'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_page']
        );
    }

    /**
     * 설정, 섹션, 필드를 등록한다.
     */
    public function register_settings(): void
    {
        register_setting(
            'sendgo_settings_group',
            self::OPTION_NAME,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
                'default'           => [],
            ]
        );

        add_settings_section(
            'sendgo_api_section',
            __('API Credentials', 'sendgo'),
            [$this, 'render_api_section'],
            self::PAGE_SLUG
        );

        // API 인증 필드 정의.
        $text_fields = [
            'access_key'       => __('Access Key', 'sendgo'),
            'secret_key'       => __('Secret Key', 'sendgo'),
            'kakao_sender_key' => __('Kakao Sender Key', 'sendgo'),
            'sms_sender_key'   => __('SMS Sender Key', 'sendgo'),
        ];

        foreach ($text_fields as $key => $label) {
            add_settings_field(
                $key,
                $label,
                [$this, 'render_text_field'],
                self::PAGE_SLUG,
                'sendgo_api_section',
                [
                    'key'      => $key,
                    'is_secret' => in_array($key, ['secret_key'], true),
                ]
            );
        }

        add_settings_field(
            'api_version',
            __('API Version', 'sendgo'),
            [$this, 'render_api_version_field'],
            self::PAGE_SLUG,
            'sendgo_api_section'
        );

        // WooCommerce 주문 알림 섹션.
        add_settings_section(
            'sendgo_woo_section',
            __('WooCommerce Order Notifications', 'sendgo'),
            [$this, 'render_woo_section'],
            self::PAGE_SLUG
        );

        // 주문 상태별 필드. 상태마다 값을 따로 두어야 처리 중 → 완료로
        // 넘어가는 주문에 같은 알림이 두 번 발송되지 않는다.
        $woo_fields = [
            'order_template_code'      => __('[Order completed] Alimtalk template code', 'sendgo'),
            'order_sms_fallback'       => __('[Order completed] SMS text used if Alimtalk fails', 'sendgo'),
            'processing_template_code' => __('[Processing] Alimtalk template code', 'sendgo'),
            'processing_sms_fallback'  => __('[Processing] SMS text used if Alimtalk fails', 'sendgo'),
        ];

        foreach ($woo_fields as $key => $label) {
            $is_textarea = str_ends_with($key, '_sms_fallback');

            add_settings_field(
                $key,
                $label,
                [$this, $is_textarea ? 'render_textarea_field' : 'render_text_field'],
                self::PAGE_SLUG,
                'sendgo_woo_section',
                ['key' => $key, 'is_secret' => false]
            );
        }
    }

    /**
     * 입력값을 정제한다.
     *
     * @param mixed $input 폼에서 전달된 원본 값.
     * @return array<string, string> 정제된 옵션 배열.
     */
    public function sanitize($input): array
    {
        $output = [];

        if (!is_array($input)) {
            return $output;
        }

        $text_keys = [
            'access_key',
            'secret_key',
            'kakao_sender_key',
            'sms_sender_key',
            'order_template_code',
            'processing_template_code',
        ];
        foreach ($text_keys as $key) {
            if (isset($input[$key])) {
                $output[$key] = sanitize_text_field((string) $input[$key]);
            }
        }

        // API 베이스 URL 재정의. 설정 화면에 필드가 없고 wp-cli / update_option 으로만
        // 설정한다. 폼 제출에는 포함되지 않으므로, 저장할 때마다 값이 사라지지 않게
        // 기존 값을 그대로 옮겨 준다.
        $existing = get_option(self::OPTION_NAME, []);
        $existing = is_array($existing) ? $existing : [];

        if (isset($input['url'])) {
            $output['url'] = esc_url_raw((string) $input['url']);
        } elseif (isset($existing['url'])) {
            $output['url'] = esc_url_raw((string) $existing['url']);
        }

        // API 버전은 허용된 값만 저장.
        $version = isset($input['api_version']) ? (string) $input['api_version'] : 'v1';
        $output['api_version'] = in_array($version, ['v1', 'v2'], true) ? $version : 'v1';

        // SMS 대체 내용은 여러 줄 허용.
        foreach (['order_sms_fallback', 'processing_sms_fallback'] as $key) {
            if (isset($input[$key])) {
                $output[$key] = sanitize_textarea_field((string) $input[$key]);
            }
        }

        return $output;
    }

    /**
     * 설정 페이지 본문을 렌더링한다.
     */
    public function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Sendgo Settings', 'sendgo'); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('sendgo_settings_group');
                do_settings_sections(self::PAGE_SLUG);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * API 섹션 설명.
     */
    public function render_api_section(): void
    {
        echo '<p>' . esc_html__('Enter the credentials issued to you in the Sendgo console at https://sendgo.io.', 'sendgo') . '</p>';
    }

    /**
     * WooCommerce 섹션 설명.
     */
    public function render_woo_section(): void
    {
        echo '<p>' . esc_html__('When a WooCommerce order changes status, an Alimtalk message is sent to the buyer. Each status is configured separately, and a status left blank sends nothing.', 'sendgo') . '</p>';
        echo '<p>' . esc_html__('The order number is passed as the first template variable (#{var1}). In the SMS text you can use the {order_number} placeholder.', 'sendgo') . '</p>';
    }

    /**
     * 일반 텍스트 입력 필드를 렌더링한다.
     *
     * @param array{key:string, is_secret?:bool} $args 필드 인자.
     */
    public function render_text_field(array $args): void
    {
        $key    = $args['key'];
        $secret = !empty($args['is_secret']);
        $options = get_option(self::OPTION_NAME, []);
        $value   = is_array($options) && isset($options[$key]) ? (string) $options[$key] : '';
        $type    = $secret ? 'password' : 'text';

        printf(
            '<input type="%1$s" id="%2$s" name="%3$s[%4$s]" value="%5$s" class="regular-text" autocomplete="off" />',
            esc_attr($type),
            esc_attr($key),
            esc_attr(self::OPTION_NAME),
            esc_attr($key),
            esc_attr($value)
        );
    }

    /**
     * 여러 줄 텍스트 입력 필드를 렌더링한다.
     *
     * @param array{key:string} $args 필드 인자.
     */
    public function render_textarea_field(array $args): void
    {
        $key     = $args['key'];
        $options = get_option(self::OPTION_NAME, []);
        $value   = is_array($options) && isset($options[$key]) ? (string) $options[$key] : '';

        printf(
            '<textarea id="%1$s" name="%2$s[%3$s]" rows="3" class="large-text">%4$s</textarea>',
            esc_attr($key),
            esc_attr(self::OPTION_NAME),
            esc_attr($key),
            esc_textarea($value)
        );
    }

    /**
     * API 버전 선택 필드를 렌더링한다.
     */
    public function render_api_version_field(): void
    {
        $options = get_option(self::OPTION_NAME, []);
        $value   = is_array($options) && isset($options['api_version']) ? (string) $options['api_version'] : 'v1';
        $choices = ['v1' => 'v1', 'v2' => 'v2'];

        echo '<select id="api_version" name="' . esc_attr(self::OPTION_NAME) . '[api_version]">';
        foreach ($choices as $val => $label) {
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr($val),
                selected($value, $val, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }
}

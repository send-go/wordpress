<?php
/**
 * Plugin Name:       Sendgo
 * Plugin URI:        https://github.com/send-go/wordpress
 * Description:       Send Kakao Alimtalk, Kakao Brand Message and SMS/LMS/MMS through Sendgo. Notifies WooCommerce buyers automatically when an order changes status.
 * Version:           1.2.4
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            amuz
 * Author URI:        https://sendgo.io
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       sendgo
 *
 * @package Sendgo
 */

// WordPress 외부에서의 직접 접근 차단.
defined('ABSPATH') || exit;

// 플러그인 상수 정의.
define('SENDGO_VERSION', '1.2.4');
define('SENDGO_PLUGIN_FILE', __FILE__);
define('SENDGO_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Composer 오토로더 로드 (sendgo/php 코어 및 플러그인 클래스 포함).
// 배포 zip 에는 vendor/ 가 포함되어 있다. 소스에서 직접 받은 경우에만 없을 수 있다.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

// 코어 SDK 가 없으면 플러그인은 발송을 할 수 없다. 예전에는 이 경우 조용히
// 아무것도 하지 않아서, 설치·활성화·설정까지 정상으로 보이는데 알림만 안 나가는
// 상태가 됐다. 관리자에게 원인을 알려 준다.
if (!class_exists('\Sendgo\Php\Sendgo')) {
    add_action('admin_notices', static function (): void {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        echo '<div class="notice notice-error"><p>';
        echo esc_html__(
            'Sendgo: the core SDK (sendgo/php) could not be found, so no message can be sent. Run "composer install" in the plugin directory, or reinstall using the distributed zip, which includes the vendor directory.',
            'sendgo'
        );
        echo '</p></div>';
    });

    return;
}

// 플러그인 자체 클래스는 명시적으로 include (classmap 오토로드가 없어도 동작하도록).
require_once __DIR__ . '/includes/class-sendgo-settings.php';
require_once __DIR__ . '/includes/class-sendgo-woocommerce.php';
require_once __DIR__ . '/includes/class-sendgo-plugin.php';

/**
 * 플러그인 메인 인스턴스를 반환한다.
 *
 * @return Sendgo_Plugin
 */
function sendgo(): Sendgo_Plugin
{
    return Sendgo_Plugin::instance();
}

// 모든 플러그인 로드 완료 후 메인 클래스 부팅.
add_action('plugins_loaded', 'sendgo');

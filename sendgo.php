<?php
/**
 * Plugin Name:       Sendgo
 * Plugin URI:        https://sendgo.io
 * Description:       카카오 알림톡/브랜드메시지 및 SMS/LMS/MMS를 발송하는 Sendgo 연동 플러그인. WooCommerce 주문 상태 변경 시 알림톡을 자동 발송합니다.
 * Version:           1.2.2
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            Sendgo
 * Author URI:        https://sendgo.io
 * License:           MIT
 * Text Domain:       sendgo
 *
 * @package Sendgo
 */

// WordPress 외부에서의 직접 접근 차단.
defined('ABSPATH') || exit;

// 플러그인 상수 정의.
define('SENDGO_VERSION', '1.2.2');
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
            'Sendgo: 코어 SDK(sendgo/php)를 찾을 수 없어 메시지를 발송할 수 없습니다. 플러그인 디렉터리에서 "composer install" 을 실행하거나, vendor 디렉터리가 포함된 배포 zip 으로 다시 설치하세요.',
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

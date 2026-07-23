<?php
/**
 * 플러그인 삭제 시 정리 작업.
 *
 * @package Sendgo
 */

// WordPress 삭제 컨텍스트 외의 직접 접근 차단.
defined('WP_UNINSTALL_PLUGIN') || exit;

// 저장된 플러그인 옵션 제거.
delete_option('sendgo_options');

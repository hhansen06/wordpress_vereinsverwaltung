<?php
/**
 * Plugin Name: Vereinsverwaltung
 * Description: Verwaltung von Sparten, Ansprechpartnern und Terminen im Einstellungsbereich.
 * Version: 1.0.1
 * Author: Henrik Hansen
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-vereinsverwaltung.php';
require_once plugin_dir_path(__FILE__) . 'includes/widgets.php';

function vv_vereinsverwaltung_activate(): void
{
    $plugin = new Vereinsverwaltung_Plugin();
    $plugin->register_profile_rewrite();
    flush_rewrite_rules();
}

function vv_vereinsverwaltung_deactivate(): void
{
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'vv_vereinsverwaltung_activate');
register_deactivation_hook(__FILE__, 'vv_vereinsverwaltung_deactivate');

new Vereinsverwaltung_Plugin();

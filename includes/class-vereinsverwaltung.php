<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Vereinsverwaltung_Plugin
{
    const OPT_SPARTEN = 'vv_sparten';
    const OPT_FUNKTIONEN = 'vv_funktionen';
    const OPT_ANSPRECHPARTNER = 'vv_ansprechpartner';
    const OPT_TERMINE = 'vv_termine';
    const OPT_KLASSEN = 'vv_klassen';
    const OPT_ERGEBNISSE = 'vv_ergebnisse';
    const META_PAGE_SPARTE = 'vv_page_sparte_id';
    const META_USER_PHONE = 'vv_user_phone';
    const META_USER_ADDRESS = 'vv_user_address';
    const META_USER_SPARTE = 'vv_user_sparte_id';
    const META_USER_KLASSE = 'vv_user_klasse_id';
    const META_USER_PUBLIC_PROFILE = 'vv_user_public_profile';
    const META_USER_BANNER = 'vv_user_banner_url';
    const META_USER_BANNER_ID = 'vv_user_banner_id';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_settings_pages']);
        add_action('admin_post_vv_add_spart', [$this, 'handle_add_spart']);
        add_action('admin_post_vv_delete_spart', [$this, 'handle_delete_spart']);

        add_action('admin_post_vv_add_funktion', [$this, 'handle_add_funktion']);
        add_action('admin_post_vv_delete_funktion', [$this, 'handle_delete_funktion']);

        add_action('admin_post_vv_add_klasse', [$this, 'handle_add_klasse']);
        add_action('admin_post_vv_update_klasse', [$this, 'handle_update_klasse']);
        add_action('admin_post_vv_delete_klasse', [$this, 'handle_delete_klasse']);

        add_action('admin_post_vv_add_ansprechpartner', [$this, 'handle_add_ansprechpartner']);
        add_action('admin_post_vv_update_ansprechpartner', [$this, 'handle_update_ansprechpartner']);
        add_action('admin_post_vv_delete_ansprechpartner', [$this, 'handle_delete_ansprechpartner']);

        add_action('admin_post_vv_add_termin', [$this, 'handle_add_termin']);
        add_action('admin_post_vv_update_termin', [$this, 'handle_update_termin']);
        add_action('admin_post_vv_delete_termin', [$this, 'handle_delete_termin']);

        add_action('admin_post_vv_add_ergebnis', [$this, 'handle_add_ergebnis']);
        add_action('admin_post_vv_update_ergebnis', [$this, 'handle_update_ergebnis']);
        add_action('admin_post_vv_delete_ergebnis', [$this, 'handle_delete_ergebnis']);

        add_action('widgets_init', [$this, 'register_widgets']);
        add_action('widgets_init', [$this, 'register_sparten_sidebars']);
        add_filter('sidebars_widgets', [$this, 'filter_sidebars_widgets']);

        add_action('add_meta_boxes', [$this, 'register_page_meta_box']);
        add_action('save_post_page', [$this, 'save_page_sparte']);

        add_shortcode('vv_termine_tabelle', [$this, 'shortcode_termine_tabelle']);
        add_shortcode('vv_ansprechpartner', [$this, 'shortcode_ansprechpartner']);
        add_shortcode('vv_buehne', [$this, 'shortcode_buehne']);

        add_action('wp_head', [$this, 'output_frontend_styles']);

        add_action('show_user_profile', [$this, 'render_user_contact_fields']);
        add_action('edit_user_profile', [$this, 'render_user_contact_fields']);
        add_action('personal_options_update', [$this, 'save_user_contact_fields']);
        add_action('edit_user_profile_update', [$this, 'save_user_contact_fields']);

        add_action('admin_print_footer_scripts-profile.php', [$this, 'add_display_name_option_script']);
        add_action('admin_print_footer_scripts-user-edit.php', [$this, 'add_display_name_option_script']);

        add_action('init', [$this, 'register_profile_rewrite']);
        add_filter('query_vars', [$this, 'register_profile_query_var']);
        add_filter('template_include', [$this, 'load_profile_template']);
        add_action('admin_init', [$this, 'maybe_flush_profile_rewrite']);

        add_action('wp_dashboard_setup', [$this, 'hide_wp_events_dashboard_widget'], 999);
        add_action('wp_dashboard_setup', [$this, 'register_termine_dashboard_widget']);
    }

    public function register_settings_pages(): void
    {
        add_menu_page(
            'Ergebnisse',
            'Ergebnisse',
            'read',
            'vereinsverwaltung-ergebnisse',
            [$this, 'render_ergebnisse_page'],
            'dashicons-awards',
            27
        );

        add_menu_page(
            'Termine',
            'Termine',
            'manage_options',
            'vereinsverwaltung-termine',
            [$this, 'render_termine_page'],
            'dashicons-calendar-alt',
            26
        );

        add_options_page(
            'Sparten',
            'Sparten',
            'manage_options',
            'vereinsverwaltung',
            [$this, 'render_sparten_page']
        );

        add_options_page(
            'Ansprechpartner',
            'Ansprechpartner',
            'manage_options',
            'vereinsverwaltung-ansprechpartner',
            [$this, 'render_ansprechpartner_page']
        );

        add_options_page(
            'Klassen',
            'Klassen',
            'manage_options',
            'vereinsverwaltung-klassen',
            [$this, 'render_klassen_page']
        );

        add_submenu_page(
            'vereinsverwaltung',
            'Sparten',
            'Sparten',
            'manage_options',
            'vereinsverwaltung',
            [$this, 'render_sparten_page']
        );

        add_submenu_page(
            'vereinsverwaltung',
            'Ansprechpartner',
            'Ansprechpartner',
            'manage_options',
            'vereinsverwaltung-ansprechpartner',
            [$this, 'render_ansprechpartner_page']
        );

        add_submenu_page(
            'vereinsverwaltung',
            'Klassen',
            'Klassen',
            'manage_options',
            'vereinsverwaltung-klassen',
            [$this, 'render_klassen_page']
        );

        add_submenu_page(
            'vereinsverwaltung',
            'Termine',
            'Termine',
            'manage_options',
            'vereinsverwaltung-termine',
            [$this, 'render_termine_page']
        );
    }

    private function get_sparten(): array
    {
        $sparten = get_option(self::OPT_SPARTEN, []);
        return is_array($sparten) ? $sparten : [];
    }

    private function get_funktionen(): array
    {
        $funktionen = get_option(self::OPT_FUNKTIONEN, []);
        return is_array($funktionen) ? $funktionen : [];
    }

    private function get_ansprechpartner(): array
    {
        $ansprechpartner = get_option(self::OPT_ANSPRECHPARTNER, []);
        return is_array($ansprechpartner) ? $ansprechpartner : [];
    }

    private function get_termine(): array
    {
        $termine = get_option(self::OPT_TERMINE, []);
        return is_array($termine) ? $termine : [];
    }

    private function get_klassen(): array
    {
        $klassen = get_option(self::OPT_KLASSEN, []);
        return is_array($klassen) ? $klassen : [];
    }

    private function get_ergebnisse(): array
    {
        $ergebnisse = get_option(self::OPT_ERGEBNISSE, []);
        return is_array($ergebnisse) ? $ergebnisse : [];
    }

    private function sort_by_name(array $items): array
    {
        usort($items, function ($a, $b) {
            return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
        });
        return $items;
    }

    public static function is_termin_recent(array $termin, int $max_days_old = 5): bool
    {
        $datum_raw = $termin['datum'] ?? '';
        if (!$datum_raw) {
            return false;
        }

        $timezone = wp_timezone();
        $datum = DateTimeImmutable::createFromFormat('Y-m-d', $datum_raw, $timezone);
        if (!$datum) {
            return false;
        }

        $today = new DateTimeImmutable('now', $timezone);
        $diff = $today->diff($datum);

        if ($diff->invert === 1 && $diff->days !== false && $diff->days > $max_days_old) {
            return false;
        }

        return true;
    }

    public function register_widgets(): void
    {
        register_widget('VV_Termine_Widget');
    }

    public function register_sparten_sidebars(): void
    {
        $sparten = $this->sort_by_name($this->get_sparten());
        foreach ($sparten as $spart) {
            if (empty($spart['id']) || empty($spart['name'])) {
                continue;
            }

            register_sidebar([
                'name' => 'Sparte: ' . $spart['name'],
                'id' => 'vv-spart-' . $spart['id'],
                'description' => 'Widget-Bereich für Sparte ' . $spart['name'],
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget' => '</section>',
                'before_title' => '<h4 class="widget-title">',
                'after_title' => '</h4>',
            ]);
        }
    }

    public function filter_sidebars_widgets(array $sidebars_widgets): array
    {
        if (is_admin() || !is_page()) {
            return $sidebars_widgets;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return $sidebars_widgets;
        }

        $spart_id = (string) get_post_meta($post_id, self::META_PAGE_SPARTE, true);
        if ($spart_id === '') {
            return $sidebars_widgets;
        }

        $sidebar_id = 'vv-spart-' . $spart_id;
        if (empty($sidebars_widgets[$sidebar_id])) {
            return $sidebars_widgets;
        }

        $base_sidebar = apply_filters('vv_sidebar_base_id', $this->get_base_sidebar_id(), $post_id, $spart_id);
        if (empty($base_sidebar) || !isset($sidebars_widgets[$base_sidebar])) {
            return $sidebars_widgets;
        }

        $sidebars_widgets[$base_sidebar] = $sidebars_widgets[$sidebar_id];
        return $sidebars_widgets;
    }

    private function get_base_sidebar_id(): string
    {
        $theme = wp_get_theme();
        $template = $theme->get_template();
        $stylesheet = $theme->get_stylesheet();

        if ($template === 'bam' || $stylesheet === 'bam' || $stylesheet === 'msg-sulingen') {
            return 'sidebar-1';
        }

        return 'sidebar-1';
    }

    public function register_page_meta_box(): void
    {
        add_meta_box(
            'vv_page_sparte',
            'Sparte',
            [$this, 'render_page_sparte_meta_box'],
            'page',
            'side',
            'default'
        );
    }

    public function render_page_sparte_meta_box($post): void
    {
        $sparten = $this->sort_by_name($this->get_sparten());
        $current = (string) get_post_meta($post->ID, self::META_PAGE_SPARTE, true);
        wp_nonce_field('vv_page_sparte_save', 'vv_page_sparte_nonce');
        ?>
        <p>
            <label for="vv_page_sparte_select">Sparte auswählen</label>
            <select name="vv_page_sparte" id="vv_page_sparte_select" class="widefat">
                <option value="">Keine</option>
                <?php foreach ($sparten as $spart): ?>
                    <option value="<?php echo esc_attr($spart['id']); ?>" <?php selected($current, $spart['id']); ?>>
                        <?php echo esc_html($spart['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public function save_page_sparte(int $post_id): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_page', $post_id)) {
            return;
        }

        if (empty($_POST['vv_page_sparte_nonce']) || !wp_verify_nonce($_POST['vv_page_sparte_nonce'], 'vv_page_sparte_save')) {
            return;
        }

        $spart_id = isset($_POST['vv_page_sparte']) ? sanitize_text_field(wp_unslash($_POST['vv_page_sparte'])) : '';

        if ($spart_id === '') {
            delete_post_meta($post_id, self::META_PAGE_SPARTE);
            return;
        }

        $sparten = $this->get_sparten();
        $valid = false;
        foreach ($sparten as $spart) {
            if (($spart['id'] ?? '') === $spart_id) {
                $valid = true;
                break;
            }
        }

        if ($valid) {
            update_post_meta($post_id, self::META_PAGE_SPARTE, $spart_id);
        }
    }

    public function render_sparten_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $sparten = $this->sort_by_name($this->get_sparten());
        ?>
        <div class="wrap">
            <h1>Sparten</h1>
            <p>Sparten können nur unter Einstellungen verwaltet werden.</p>

            <h2>Neue Sparte</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="vv_add_spart" />
                <?php wp_nonce_field('vv_add_spart'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="vv_spart_name">Name</label></th>
                        <td><input name="name" id="vv_spart_name" type="text" class="regular-text" required /></td>
                    </tr>
                </table>
                <?php submit_button('Sparte hinzufügen'); ?>
            </form>

            <h2>Vorhandene Sparten</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sparten)): ?>
                        <tr>
                            <td colspan="2">Noch keine Sparten angelegt.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sparten as $spart): ?>
                            <tr>
                                <td><?php echo esc_html($spart['name']); ?></td>
                                <td>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="vv_delete_spart" />
                                        <input type="hidden" name="id" value="<?php echo esc_attr($spart['id']); ?>" />
                                        <?php wp_nonce_field('vv_delete_spart_' . $spart['id']); ?>
                                        <?php submit_button('Löschen', 'delete', 'submit', false); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_ansprechpartner_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $sparten = $this->sort_by_name($this->get_sparten());
        $funktionen = $this->sort_by_name($this->get_funktionen());
        $ansprechpartner = $this->get_ansprechpartner();
        $users = get_users(['fields' => ['ID', 'display_name', 'user_email']]);
        $edit_id = isset($_GET['edit_ap']) ? sanitize_text_field(wp_unslash($_GET['edit_ap'])) : '';
        $edit_ap = null;
        if ($edit_id) {
            foreach ($ansprechpartner as $entry) {
                if (($entry['id'] ?? '') === $edit_id) {
                    $edit_ap = $entry;
                    break;
                }
            }
        }
        ?>
        <div class="wrap">
            <h1>Ansprechpartner</h1>

            <p><strong>Shortcode:</strong> <code>[vv_ansprechpartner sparte="SPARTENNAME"]</code></p>

            <h2>Funktionen</h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="vv_add_funktion" />
                <?php wp_nonce_field('vv_add_funktion'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="vv_funktion_name">Funktion</label></th>
                        <td><input name="name" id="vv_funktion_name" type="text" class="regular-text" required /></td>
                    </tr>
                </table>
                <?php submit_button('Funktion hinzufügen'); ?>
            </form>

            <table class="widefat striped" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th>Funktion</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($funktionen)): ?>
                        <tr>
                            <td colspan="2">Noch keine Funktionen angelegt.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($funktionen as $funktion): ?>
                            <tr>
                                <td><?php echo esc_html($funktion['name']); ?></td>
                                <td>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="vv_delete_funktion" />
                                        <input type="hidden" name="id" value="<?php echo esc_attr($funktion['id']); ?>" />
                                        <?php wp_nonce_field('vv_delete_funktion_' . $funktion['id']); ?>
                                        <?php submit_button('Löschen', 'delete', 'submit', false); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <hr />

            <h2><?php echo $edit_ap ? 'Ansprechpartner bearbeiten' : 'Neuen Ansprechpartner anlegen'; ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action"
                    value="<?php echo $edit_ap ? 'vv_update_ansprechpartner' : 'vv_add_ansprechpartner'; ?>" />
                <?php
                if ($edit_ap) {
                    echo '<input type="hidden" name="id" value="' . esc_attr($edit_ap['id']) . '" />';
                    wp_nonce_field('vv_update_ansprechpartner_' . $edit_ap['id']);
                } else {
                    wp_nonce_field('vv_add_ansprechpartner');
                }
                ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="vv_ap_user">Benutzer</label></th>
                        <td>
                            <select name="user_id" id="vv_ap_user" required>
                                <option value="">Bitte wählen</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($edit_ap['user_id'] ?? '', $user->ID); ?>>
                                        <?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_ap_funktion">Funktion</label></th>
                        <td>
                            <select name="funktion_id" id="vv_ap_funktion" required>
                                <option value="">Bitte wählen</option>
                                <?php foreach ($funktionen as $funktion): ?>
                                    <option value="<?php echo esc_attr($funktion['id']); ?>" <?php selected($edit_ap['funktion_id'] ?? '', $funktion['id']); ?>>
                                        <?php echo esc_html($funktion['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_ap_spart">Sparte</label></th>
                        <td>
                            <select name="spart_id" id="vv_ap_spart" required>
                                <option value="">Bitte wählen</option>
                                <?php foreach ($sparten as $spart): ?>
                                    <option value="<?php echo esc_attr($spart['id']); ?>" <?php selected($edit_ap['spart_id'] ?? '', $spart['id']); ?>><?php echo esc_html($spart['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button($edit_ap ? 'Ansprechpartner speichern' : 'Ansprechpartner hinzufügen'); ?>
            </form>

            <h2>Vorhandene Ansprechpartner</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Benutzer</th>
                        <th>Funktion</th>
                        <th>Sparte</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ansprechpartner)): ?>
                        <tr>
                            <td colspan="4">Noch keine Ansprechpartner angelegt.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ansprechpartner as $entry): ?>
                            <tr>
                                <td><?php echo esc_html($entry['user_label']); ?></td>
                                <td><?php echo esc_html($entry['funktion_label']); ?></td>
                                <td><?php echo esc_html($entry['spart_label']); ?></td>
                                <td>
                                    <a class="button button-secondary"
                                        href="<?php echo esc_url(admin_url('admin.php?page=vereinsverwaltung-ansprechpartner&edit_ap=' . $entry['id'])); ?>">Bearbeiten</a>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="vv_delete_ansprechpartner" />
                                        <input type="hidden" name="id" value="<?php echo esc_attr($entry['id']); ?>" />
                                        <?php wp_nonce_field('vv_delete_ansprechpartner_' . $entry['id']); ?>
                                        <?php submit_button('Löschen', 'delete', 'submit', false); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_termine_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $termine = $this->get_termine();
        usort($termine, function ($a, $b) {
            return strcmp($b['datum'] ?? '', $a['datum'] ?? '');
        });
        $sparten = $this->sort_by_name($this->get_sparten());
        $edit_id = isset($_GET['edit_termin']) ? sanitize_text_field(wp_unslash($_GET['edit_termin'])) : '';
        $edit_termin = null;
        if ($edit_id) {
            foreach ($termine as $termin) {
                if (($termin['id'] ?? '') === $edit_id) {
                    $edit_termin = $termin;
                    break;
                }
            }
        }
        ?>
        <div class="wrap">
            <h1>Termine</h1>

            <p><strong>Shortcode:</strong> <code>[vv_termine_tabelle]</code> oder mit Sparte:
                <code>[vv_termine_tabelle sparte="SPARTEN_ID"]</code>
            </p>

            <h2><?php echo $edit_termin ? 'Termin bearbeiten' : 'Neuen Termin anlegen'; ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="<?php echo $edit_termin ? 'vv_update_termin' : 'vv_add_termin'; ?>" />
                <?php
                if ($edit_termin) {
                    echo '<input type="hidden" name="id" value="' . esc_attr($edit_termin['id']) . '" />';
                    wp_nonce_field('vv_update_termin_' . $edit_termin['id']);
                } else {
                    wp_nonce_field('vv_add_termin');
                }
                ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="vv_termin_name">Name</label></th>
                        <td><input name="name" id="vv_termin_name" type="text" class="regular-text" required
                                value="<?php echo esc_attr($edit_termin['name'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_termin_spart">Sparte</label></th>
                        <td>
                            <select name="spart_id" id="vv_termin_spart" required>
                                <option value="">Bitte wählen</option>
                                <?php foreach ($sparten as $spart): ?>
                                    <option value="<?php echo esc_attr($spart['id']); ?>" <?php selected($edit_termin['spart_id'] ?? '', $spart['id']); ?>><?php echo esc_html($spart['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_termin_ort">Ort</label></th>
                        <td><input name="ort" id="vv_termin_ort" type="text" class="regular-text" required
                                value="<?php echo esc_attr($edit_termin['ort'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_termin_link">Link</label></th>
                        <td><input name="link" id="vv_termin_link" type="url" class="regular-text" placeholder="https://..."
                                value="<?php echo esc_attr($edit_termin['link'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_termin_text">Text</label></th>
                        <td><textarea name="text" id="vv_termin_text" class="large-text"
                                rows="4"><?php echo esc_textarea($edit_termin['text'] ?? ''); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_termin_datum">Datum</label></th>
                        <td><input name="datum" id="vv_termin_datum" type="date" required
                                value="<?php echo esc_attr($edit_termin['datum'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_termin_extern">Extern</label></th>
                        <td>
                            <input type="checkbox" name="extern" id="vv_termin_extern" value="1" <?php checked($edit_termin['extern'] ?? false, true); ?> />
                            <label for="vv_termin_extern">Termin ist ein externer Termin</label>
                        </td>
                    </tr>
                </table>
                <?php submit_button($edit_termin ? 'Termin speichern' : 'Termin hinzufügen'); ?>
            </form>

            <h2>Vorhandene Termine</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Sparte</th>
                        <th>Ort</th>
                        <th>Link</th>
                        <th>Text</th>
                        <th>Datum</th>
                        <th>Extern</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($termine)): ?>
                        <tr>
                            <td colspan="8">Noch keine Termine angelegt.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($termine as $termin): ?>
                            <tr>
                                <td><?php echo esc_html($termin['name']); ?></td>
                                <td><?php echo esc_html($termin['spart_label'] ?? ''); ?></td>
                                <td><?php echo esc_html($termin['ort']); ?></td>
                                <td>
                                    <?php if (!empty($termin['link'])): ?>
                                        <a href="<?php echo esc_url($termin['link']); ?>" target="_blank" rel="noopener">Link</a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($termin['text']); ?></td>
                                <td><?php echo esc_html($termin['datum']); ?></td>
                                <td><?php echo !empty($termin['extern']) ? 'Ja' : 'Nein'; ?></td>
                                <td>
                                    <a class="button button-secondary"
                                        href="<?php echo esc_url(admin_url('admin.php?page=vereinsverwaltung-termine&edit_termin=' . $termin['id'])); ?>">Bearbeiten</a>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="vv_delete_termin" />
                                        <input type="hidden" name="id" value="<?php echo esc_attr($termin['id']); ?>" />
                                        <?php wp_nonce_field('vv_delete_termin_' . $termin['id']); ?>
                                        <?php submit_button('Löschen', 'delete', 'submit', false); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_klassen_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $sparten = $this->sort_by_name($this->get_sparten());
        $klassen = $this->get_klassen();
        $edit_id = isset($_GET['edit_klasse']) ? sanitize_text_field(wp_unslash($_GET['edit_klasse'])) : '';
        $edit_klasse = null;
        if ($edit_id) {
            foreach ($klassen as $klasse) {
                if (($klasse['id'] ?? '') === $edit_id) {
                    $edit_klasse = $klasse;
                    break;
                }
            }
        }
        ?>
        <div class="wrap">
            <h1>Klassen</h1>

            <h2><?php echo $edit_klasse ? 'Klasse bearbeiten' : 'Neue Klasse anlegen'; ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="<?php echo $edit_klasse ? 'vv_update_klasse' : 'vv_add_klasse'; ?>" />
                <?php
                if ($edit_klasse) {
                    echo '<input type="hidden" name="id" value="' . esc_attr($edit_klasse['id']) . '" />';
                    wp_nonce_field('vv_update_klasse_' . $edit_klasse['id']);
                } else {
                    wp_nonce_field('vv_add_klasse');
                }
                ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="vv_klasse_name">Klassenname</label></th>
                        <td><input name="name" id="vv_klasse_name" type="text" class="regular-text" required
                                value="<?php echo esc_attr($edit_klasse['name'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_klasse_spart">Sparte</label></th>
                        <td>
                            <select name="spart_id" id="vv_klasse_spart" required>
                                <option value="">Bitte wählen</option>
                                <?php foreach ($sparten as $spart): ?>
                                    <option value="<?php echo esc_attr($spart['id']); ?>" <?php selected($edit_klasse['spart_id'] ?? '', $spart['id']); ?>><?php echo esc_html($spart['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button($edit_klasse ? 'Klasse speichern' : 'Klasse hinzufügen'); ?>
            </form>

            <h2>Vorhandene Klassen</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Klasse</th>
                        <th>Sparte</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($klassen)): ?>
                        <tr>
                            <td colspan="3">Noch keine Klassen angelegt.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($klassen as $klasse): ?>
                            <tr>
                                <td><?php echo esc_html($klasse['name']); ?></td>
                                <td><?php echo esc_html($klasse['spart_label']); ?></td>
                                <td>
                                    <a class="button button-secondary"
                                        href="<?php echo esc_url(admin_url('admin.php?page=vereinsverwaltung-klassen&edit_klasse=' . $klasse['id'])); ?>">Bearbeiten</a>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="vv_delete_klasse" />
                                        <input type="hidden" name="id" value="<?php echo esc_attr($klasse['id']); ?>" />
                                        <?php wp_nonce_field('vv_delete_klasse_' . $klasse['id']); ?>
                                        <?php submit_button('Löschen', 'delete', 'submit', false); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_ergebnisse_page(): void
    {
        if (!current_user_can('read')) {
            return;
        }

        $current_user = wp_get_current_user();
        $is_abonent = in_array('abonennten', (array) $current_user->roles, true);
        $can_assign_user = current_user_can('manage_options') || in_array('author', (array) $current_user->roles, true);
        $klassen = $this->get_klassen();
        $ergebnisse = $this->get_ergebnisse();
        $edit_id = isset($_GET['edit_ergebnis']) ? sanitize_text_field(wp_unslash($_GET['edit_ergebnis'])) : '';
        $edit_ergebnis = null;
        $users = [];

        if ($can_assign_user) {
            $users = get_users(['fields' => ['ID', 'display_name']]);
            usort($users, function ($a, $b) {
                return strcasecmp($a->display_name ?? '', $b->display_name ?? '');
            });
        }

        if ($is_abonent) {
            $ergebnisse = array_values(array_filter($ergebnisse, function ($entry) use ($current_user) {
                return (int) ($entry['user_id'] ?? 0) === (int) $current_user->ID;
            }));
        }

        if ($edit_id) {
            foreach ($ergebnisse as $entry) {
                if (($entry['id'] ?? '') === $edit_id) {
                    $edit_ergebnis = $entry;
                    break;
                }
            }
        }

        usort($ergebnisse, function ($a, $b) {
            return strcmp($b['datum'] ?? '', $a['datum'] ?? '');
        });
        ?>
        <div class="wrap">
            <h1>Ergebnisse</h1>

            <h2><?php echo $edit_ergebnis ? 'Ergebnis bearbeiten' : 'Neues Ergebnis anlegen'; ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action"
                    value="<?php echo $edit_ergebnis ? 'vv_update_ergebnis' : 'vv_add_ergebnis'; ?>" />
                <?php
                if ($edit_ergebnis) {
                    echo '<input type="hidden" name="id" value="' . esc_attr($edit_ergebnis['id']) . '" />';
                    wp_nonce_field('vv_update_ergebnis_' . $edit_ergebnis['id']);
                } else {
                    wp_nonce_field('vv_add_ergebnis');
                }
                ?>
                <table class="form-table" role="presentation">
                    <?php if ($can_assign_user): ?>
                        <tr>
                            <th scope="row"><label for="vv_ergebnis_user">Benutzer</label></th>
                            <td>
                                <select name="user_id" id="vv_ergebnis_user" class="regular-text" required>
                                    <option value="">Bitte wählen</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($edit_ergebnis['user_id'] ?? $current_user->ID, $user->ID); ?>>
                                            <?php echo esc_html($user->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row"><label for="vv_ergebnis_veranstaltung">Name der Veranstaltung</label></th>
                        <td><input name="veranstaltung" id="vv_ergebnis_veranstaltung" type="text" class="regular-text" required
                                value="<?php echo esc_attr($edit_ergebnis['veranstaltung'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_ergebnis_ort">Ort</label></th>
                        <td><input name="ort" id="vv_ergebnis_ort" type="text" class="regular-text" required
                                value="<?php echo esc_attr($edit_ergebnis['ort'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_ergebnis_datum">Datum</label></th>
                        <td><input name="datum" id="vv_ergebnis_datum" type="date" required
                                value="<?php echo esc_attr($edit_ergebnis['datum'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_ergebnis_platz">Platzierung</label></th>
                        <td><input name="platzierung" id="vv_ergebnis_platz" type="number" class="small-text" min="1" step="1"
                                required value="<?php echo esc_attr($edit_ergebnis['platzierung'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="vv_ergebnis_teilnehmer">Teilnehmer in Klasse</label></th>
                        <td><input name="teilnehmer" id="vv_ergebnis_teilnehmer" type="number" class="small-text" min="1"
                                step="1" required value="<?php echo esc_attr($edit_ergebnis['teilnehmer'] ?? ''); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button($edit_ergebnis ? 'Ergebnis speichern' : 'Ergebnis hinzufügen'); ?>
            </form>

            <h2>Vorhandene Ergebnisse</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Veranstaltung</th>
                        <th>Ort</th>
                        <th>Datum</th>
                        <th>Platzierung</th>
                        <th>Teilnehmer</th>
                        <th>Klasse</th>
                        <?php if (!$is_abonent): ?>
                            <th>Benutzer</th>
                        <?php endif; ?>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ergebnisse)): ?>
                        <tr>
                            <td colspan="<?php echo $is_abonent ? 6 : 7; ?>">Noch keine Ergebnisse angelegt.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ergebnisse as $entry): ?>
                            <tr>
                                <td><?php echo esc_html($entry['veranstaltung']); ?></td>
                                <td><?php echo esc_html($entry['ort']); ?></td>
                                <td><?php echo esc_html($entry['datum']); ?></td>
                                <td><?php echo esc_html($entry['platzierung']); ?></td>
                                <td><?php echo esc_html($entry['teilnehmer']); ?></td>
                                <td><?php echo esc_html($entry['klasse_label']); ?></td>
                                <?php if (!$is_abonent): ?>
                                    <td><?php echo esc_html($entry['user_label']); ?></td>
                                <?php endif; ?>
                                <td>
                                    <a class="button button-secondary"
                                        href="<?php echo esc_url(admin_url('admin.php?page=vereinsverwaltung-ergebnisse&edit_ergebnis=' . $entry['id'])); ?>">Bearbeiten</a>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="vv_delete_ergebnis" />
                                        <input type="hidden" name="id" value="<?php echo esc_attr($entry['id']); ?>" />
                                        <?php wp_nonce_field('vv_delete_ergebnis_' . $entry['id']); ?>
                                        <?php submit_button('Löschen', 'delete', 'submit', false); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function handle_add_spart(): void
    {
        $this->assert_admin();
        check_admin_referer('vv_add_spart');

        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        if (!$name) {
            $this->redirect_back();
        }

        $sparten = $this->get_sparten();
        $sparten[] = [
            'id' => uniqid('spart_', true),
            'name' => $name,
        ];

        update_option(self::OPT_SPARTEN, $sparten, false);
        $this->redirect_back();
    }

    public function handle_delete_spart(): void
    {
        $this->assert_admin();
        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_delete_spart_' . $id);

        $sparten = array_values(array_filter($this->get_sparten(), function ($spart) use ($id) {
            return ($spart['id'] ?? '') !== $id;
        }));

        update_option(self::OPT_SPARTEN, $sparten, false);
        $this->redirect_back();
    }

    public function handle_add_funktion(): void
    {
        $this->assert_admin();
        check_admin_referer('vv_add_funktion');

        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        if (!$name) {
            $this->redirect_back();
        }

        $funktionen = $this->get_funktionen();
        $funktionen[] = [
            'id' => uniqid('funktion_', true),
            'name' => $name,
        ];

        update_option(self::OPT_FUNKTIONEN, $funktionen, false);
        $this->redirect_back();
    }

    public function handle_delete_funktion(): void
    {
        $this->assert_admin();
        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_delete_funktion_' . $id);

        $funktionen = array_values(array_filter($this->get_funktionen(), function ($funktion) use ($id) {
            return ($funktion['id'] ?? '') !== $id;
        }));

        update_option(self::OPT_FUNKTIONEN, $funktionen, false);
        $this->redirect_back();
    }

    public function handle_add_ansprechpartner(): void
    {
        $this->assert_admin();
        check_admin_referer('vv_add_ansprechpartner');

        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $funktion_id = isset($_POST['funktion_id']) ? sanitize_text_field(wp_unslash($_POST['funktion_id'])) : '';
        $spart_id = isset($_POST['spart_id']) ? sanitize_text_field(wp_unslash($_POST['spart_id'])) : '';

        if (!$user_id || !$funktion_id || !$spart_id) {
            $this->redirect_back();
        }

        $user = get_user_by('id', $user_id);
        $funktionen = $this->get_funktionen();
        $sparten = $this->get_sparten();

        $funktion_label = '';
        foreach ($funktionen as $funktion) {
            if (($funktion['id'] ?? '') === $funktion_id) {
                $funktion_label = $funktion['name'];
                break;
            }
        }

        $spart_label = '';
        foreach ($sparten as $spart) {
            if (($spart['id'] ?? '') === $spart_id) {
                $spart_label = $spart['name'];
                break;
            }
        }

        if (!$user || !$funktion_label || !$spart_label) {
            $this->redirect_back();
        }

        $ansprechpartner = $this->get_ansprechpartner();
        $ansprechpartner[] = [
            'id' => uniqid('ap_', true),
            'user_id' => $user_id,
            'user_label' => $user->display_name,
            'funktion_id' => $funktion_id,
            'funktion_label' => $funktion_label,
            'spart_id' => $spart_id,
            'spart_label' => $spart_label,
        ];

        update_option(self::OPT_ANSPRECHPARTNER, $ansprechpartner, false);
        $this->redirect_back();
    }

    public function handle_update_ansprechpartner(): void
    {
        $this->assert_admin();
        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_update_ansprechpartner_' . $id);

        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $funktion_id = isset($_POST['funktion_id']) ? sanitize_text_field(wp_unslash($_POST['funktion_id'])) : '';
        $spart_id = isset($_POST['spart_id']) ? sanitize_text_field(wp_unslash($_POST['spart_id'])) : '';

        if (!$id || !$user_id || !$funktion_id || !$spart_id) {
            $this->redirect_back();
        }

        $user = get_user_by('id', $user_id);
        $funktionen = $this->get_funktionen();
        $sparten = $this->get_sparten();

        $funktion_label = '';
        foreach ($funktionen as $funktion) {
            if (($funktion['id'] ?? '') === $funktion_id) {
                $funktion_label = $funktion['name'];
                break;
            }
        }

        $spart_label = '';
        foreach ($sparten as $spart) {
            if (($spart['id'] ?? '') === $spart_id) {
                $spart_label = $spart['name'];
                break;
            }
        }

        if (!$user || !$funktion_label || !$spart_label) {
            $this->redirect_back();
        }

        $ansprechpartner = $this->get_ansprechpartner();
        foreach ($ansprechpartner as &$entry) {
            if (($entry['id'] ?? '') === $id) {
                $entry['user_id'] = $user_id;
                $entry['user_label'] = $user->display_name;
                $entry['funktion_id'] = $funktion_id;
                $entry['funktion_label'] = $funktion_label;
                $entry['spart_id'] = $spart_id;
                $entry['spart_label'] = $spart_label;
                break;
            }
        }
        unset($entry);

        update_option(self::OPT_ANSPRECHPARTNER, $ansprechpartner, false);
        $this->redirect_back();
    }

    public function handle_delete_ansprechpartner(): void
    {
        $this->assert_admin();
        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_delete_ansprechpartner_' . $id);

        $ansprechpartner = array_values(array_filter($this->get_ansprechpartner(), function ($entry) use ($id) {
            return ($entry['id'] ?? '') !== $id;
        }));

        update_option(self::OPT_ANSPRECHPARTNER, $ansprechpartner, false);
        $this->redirect_back();
    }

    public function handle_add_klasse(): void
    {
        $this->assert_admin();
        check_admin_referer('vv_add_klasse');

        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $spart_id = isset($_POST['spart_id']) ? sanitize_text_field(wp_unslash($_POST['spart_id'])) : '';

        if (!$name || !$spart_id) {
            $this->redirect_back();
        }

        $sparten = $this->get_sparten();
        $spart_label = '';
        foreach ($sparten as $spart) {
            if (($spart['id'] ?? '') === $spart_id) {
                $spart_label = $spart['name'];
                break;
            }
        }

        if (!$spart_label) {
            $this->redirect_back();
        }

        $klassen = $this->get_klassen();
        $klassen[] = [
            'id' => uniqid('klasse_', true),
            'name' => $name,
            'spart_id' => $spart_id,
            'spart_label' => $spart_label,
        ];

        update_option(self::OPT_KLASSEN, $klassen, false);
        $this->redirect_back();
    }

    public function handle_update_klasse(): void
    {
        $this->assert_admin();
        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_update_klasse_' . $id);

        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $spart_id = isset($_POST['spart_id']) ? sanitize_text_field(wp_unslash($_POST['spart_id'])) : '';

        if (!$id || !$name || !$spart_id) {
            $this->redirect_back();
        }

        $sparten = $this->get_sparten();
        $spart_label = '';
        foreach ($sparten as $spart) {
            if (($spart['id'] ?? '') === $spart_id) {
                $spart_label = $spart['name'];
                break;
            }
        }

        if (!$spart_label) {
            $this->redirect_back();
        }

        $klassen = $this->get_klassen();
        foreach ($klassen as &$klasse) {
            if (($klasse['id'] ?? '') === $id) {
                $klasse['name'] = $name;
                $klasse['spart_id'] = $spart_id;
                $klasse['spart_label'] = $spart_label;
                break;
            }
        }
        unset($klasse);

        update_option(self::OPT_KLASSEN, $klassen, false);
        $this->redirect_back();
    }

    public function handle_delete_klasse(): void
    {
        $this->assert_admin();
        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_delete_klasse_' . $id);

        $klassen = array_values(array_filter($this->get_klassen(), function ($klasse) use ($id) {
            return ($klasse['id'] ?? '') !== $id;
        }));

        update_option(self::OPT_KLASSEN, $klassen, false);
        $this->redirect_back();
    }

    public function handle_add_termin(): void
    {
        $this->assert_admin();
        check_admin_referer('vv_add_termin');

        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $spart_id = isset($_POST['spart_id']) ? sanitize_text_field(wp_unslash($_POST['spart_id'])) : '';
        $ort = isset($_POST['ort']) ? sanitize_text_field(wp_unslash($_POST['ort'])) : '';
        $link = isset($_POST['link']) ? esc_url_raw(wp_unslash($_POST['link'])) : '';
        $text = isset($_POST['text']) ? sanitize_textarea_field(wp_unslash($_POST['text'])) : '';
        $datum = isset($_POST['datum']) ? sanitize_text_field(wp_unslash($_POST['datum'])) : '';
        $extern = isset($_POST['extern']) && $_POST['extern'] === '1';

        if (!$name || !$spart_id || !$ort || !$datum) {
            $this->redirect_back();
        }

        $sparten = $this->get_sparten();
        $spart_label = '';
        foreach ($sparten as $spart) {
            if (($spart['id'] ?? '') === $spart_id) {
                $spart_label = $spart['name'];
                break;
            }
        }

        if (!$spart_label) {
            $this->redirect_back();
        }

        $termine = $this->get_termine();
        $termine[] = [
            'id' => uniqid('termin_', true),
            'name' => $name,
            'spart_id' => $spart_id,
            'spart_label' => $spart_label,
            'ort' => $ort,
            'link' => $link,
            'text' => $text,
            'datum' => $datum,
            'extern' => $extern,
        ];

        update_option(self::OPT_TERMINE, $termine, false);
        $this->redirect_back();
    }

    public function handle_update_termin(): void
    {
        $this->assert_admin();
        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_update_termin_' . $id);

        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $spart_id = isset($_POST['spart_id']) ? sanitize_text_field(wp_unslash($_POST['spart_id'])) : '';
        $ort = isset($_POST['ort']) ? sanitize_text_field(wp_unslash($_POST['ort'])) : '';
        $link = isset($_POST['link']) ? esc_url_raw(wp_unslash($_POST['link'])) : '';
        $text = isset($_POST['text']) ? sanitize_textarea_field(wp_unslash($_POST['text'])) : '';
        $datum = isset($_POST['datum']) ? sanitize_text_field(wp_unslash($_POST['datum'])) : '';
        $extern = isset($_POST['extern']) && $_POST['extern'] === '1';

        if (!$id || !$name || !$spart_id || !$ort || !$datum) {
            $this->redirect_back();
        }

        $sparten = $this->get_sparten();
        $spart_label = '';
        foreach ($sparten as $spart) {
            if (($spart['id'] ?? '') === $spart_id) {
                $spart_label = $spart['name'];
                break;
            }
        }

        if (!$spart_label) {
            $this->redirect_back();
        }

        $termine = $this->get_termine();
        foreach ($termine as &$termin) {
            if (($termin['id'] ?? '') === $id) {
                $termin['name'] = $name;
                $termin['spart_id'] = $spart_id;
                $termin['spart_label'] = $spart_label;
                $termin['ort'] = $ort;
                $termin['link'] = $link;
                $termin['text'] = $text;
                $termin['datum'] = $datum;
                $termin['extern'] = $extern;
                break;
            }
        }
        unset($termin);

        update_option(self::OPT_TERMINE, $termine, false);
        $this->redirect_back();
    }

    public function handle_delete_termin(): void
    {
        $this->assert_admin();
        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_delete_termin_' . $id);

        $termine = array_values(array_filter($this->get_termine(), function ($termin) use ($id) {
            return ($termin['id'] ?? '') !== $id;
        }));

        update_option(self::OPT_TERMINE, $termine, false);
        $this->redirect_back();
    }

    public function handle_add_ergebnis(): void
    {
        if (!current_user_can('read')) {
            $this->assert_admin();
        }
        check_admin_referer('vv_add_ergebnis');

        $current_user = wp_get_current_user();
        $is_abonent = in_array('abonennten', (array) $current_user->roles, true);
        $can_assign_user = current_user_can('manage_options') || in_array('author', (array) $current_user->roles, true);

        $veranstaltung = isset($_POST['veranstaltung']) ? sanitize_text_field(wp_unslash($_POST['veranstaltung'])) : '';
        $ort = isset($_POST['ort']) ? sanitize_text_field(wp_unslash($_POST['ort'])) : '';
        $datum = isset($_POST['datum']) ? sanitize_text_field(wp_unslash($_POST['datum'])) : '';
        $platzierung = isset($_POST['platzierung']) ? (int) $_POST['platzierung'] : 0;
        $teilnehmer = isset($_POST['teilnehmer']) ? (int) $_POST['teilnehmer'] : 0;

        $target_user_id = $current_user->ID;
        if ($can_assign_user && !empty($_POST['user_id'])) {
            $target_user_id = (int) $_POST['user_id'];
        }

        $target_user = get_user_by('id', $target_user_id);
        $klasse_id = $target_user ? (string) get_user_meta($target_user_id, self::META_USER_KLASSE, true) : '';

        if (!$target_user || !$veranstaltung || !$ort || !$datum || $platzierung < 1 || $teilnehmer < 1 || !$klasse_id) {
            $this->redirect_back();
        }

        $klassen = $this->get_klassen();
        $klasse_label = '';
        foreach ($klassen as $klasse) {
            if (($klasse['id'] ?? '') === $klasse_id) {
                $klasse_label = $klasse['name'];
                break;
            }
        }

        if (!$klasse_label) {
            $this->redirect_back();
        }

        $ergebnisse = $this->get_ergebnisse();
        $ergebnisse[] = [
            'id' => uniqid('erg_', true),
            'user_id' => (int) $target_user_id,
            'user_label' => $target_user->display_name,
            'veranstaltung' => $veranstaltung,
            'ort' => $ort,
            'datum' => $datum,
            'platzierung' => $platzierung,
            'teilnehmer' => $teilnehmer,
            'klasse_id' => $klasse_id,
            'klasse_label' => $klasse_label,
        ];

        update_option(self::OPT_ERGEBNISSE, $ergebnisse, false);
        $this->redirect_back();
    }

    public function handle_update_ergebnis(): void
    {
        if (!current_user_can('read')) {
            $this->assert_admin();
        }

        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_update_ergebnis_' . $id);

        $current_user = wp_get_current_user();
        $is_abonent = in_array('abonennten', (array) $current_user->roles, true);
        $can_assign_user = current_user_can('manage_options') || in_array('author', (array) $current_user->roles, true);

        $veranstaltung = isset($_POST['veranstaltung']) ? sanitize_text_field(wp_unslash($_POST['veranstaltung'])) : '';
        $ort = isset($_POST['ort']) ? sanitize_text_field(wp_unslash($_POST['ort'])) : '';
        $datum = isset($_POST['datum']) ? sanitize_text_field(wp_unslash($_POST['datum'])) : '';
        $platzierung = isset($_POST['platzierung']) ? (int) $_POST['platzierung'] : 0;
        $teilnehmer = isset($_POST['teilnehmer']) ? (int) $_POST['teilnehmer'] : 0;

        $target_user_id = $current_user->ID;
        if ($can_assign_user && !empty($_POST['user_id'])) {
            $target_user_id = (int) $_POST['user_id'];
        }

        $target_user = get_user_by('id', $target_user_id);
        $klasse_id = $target_user ? (string) get_user_meta($target_user_id, self::META_USER_KLASSE, true) : '';

        if (!$target_user || !$id || !$veranstaltung || !$ort || !$datum || $platzierung < 1 || $teilnehmer < 1 || !$klasse_id) {
            $this->redirect_back();
        }

        $klassen = $this->get_klassen();
        $klasse_label = '';
        foreach ($klassen as $klasse) {
            if (($klasse['id'] ?? '') === $klasse_id) {
                $klasse_label = $klasse['name'];
                break;
            }
        }

        if (!$klasse_label) {
            $this->redirect_back();
        }

        $ergebnisse = $this->get_ergebnisse();
        foreach ($ergebnisse as &$entry) {
            if (($entry['id'] ?? '') === $id) {
                if ($is_abonent && (int) ($entry['user_id'] ?? 0) !== (int) $current_user->ID) {
                    $this->redirect_back();
                }

                $entry['veranstaltung'] = $veranstaltung;
                $entry['ort'] = $ort;
                $entry['datum'] = $datum;
                $entry['platzierung'] = $platzierung;
                $entry['teilnehmer'] = $teilnehmer;
                if ($can_assign_user) {
                    $entry['user_id'] = (int) $target_user_id;
                    $entry['user_label'] = $target_user->display_name;
                }
                $entry['klasse_id'] = $klasse_id;
                $entry['klasse_label'] = $klasse_label;
                break;
            }
        }
        unset($entry);

        update_option(self::OPT_ERGEBNISSE, $ergebnisse, false);
        $this->redirect_back();
    }

    public function handle_delete_ergebnis(): void
    {
        if (!current_user_can('read')) {
            $this->assert_admin();
        }

        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        check_admin_referer('vv_delete_ergebnis_' . $id);

        $current_user = wp_get_current_user();
        $is_abonent = in_array('abonennten', (array) $current_user->roles, true);

        $ergebnisse = $this->get_ergebnisse();
        $ergebnisse = array_values(array_filter($ergebnisse, function ($entry) use ($id, $is_abonent, $current_user) {
            if (($entry['id'] ?? '') !== $id) {
                return true;
            }

            if ($is_abonent && (int) ($entry['user_id'] ?? 0) !== (int) $current_user->ID) {
                return true;
            }

            return false;
        }));

        update_option(self::OPT_ERGEBNISSE, $ergebnisse, false);
        $this->redirect_back();
    }

    private function assert_admin(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Keine Berechtigung.');
        }
    }

    private function redirect_back(): void
    {
        $redirect = wp_get_referer();
        if (!$redirect) {
            $redirect = admin_url('options-general.php?page=vereinsverwaltung');
        }
        wp_safe_redirect($redirect);
        exit;
    }

    public function shortcode_termine_tabelle($atts): string
    {
        $atts = shortcode_atts([
            'sparte' => '',
            'extern' => '',
        ], $atts, 'vv_termine_tabelle');

        $termine = $this->get_termine();
        $spart_id = $this->resolve_sparte_identifier($atts['sparte']);
        if ($spart_id) {
            $termine = array_values(array_filter($termine, function ($termin) use ($spart_id) {
                return ($termin['spart_id'] ?? '') === $spart_id;
            }));
        }

        if ($atts['extern'] !== '') {
            $filter_extern = filter_var($atts['extern'], FILTER_VALIDATE_BOOLEAN);
            $termine = array_values(array_filter($termine, function ($termin) use ($filter_extern) {
                $is_extern = !empty($termin['extern']);
                return $is_extern === $filter_extern;
            }));
        }

        $termine = array_values(array_filter($termine, function ($termin) {
            return self::is_termin_recent($termin, 5);
        }));

        if (empty($termine)) {
            return '<p>Keine Termine vorhanden.</p>';
        }

        usort($termine, function ($a, $b) {
            return strcmp($a['datum'] ?? '', $b['datum'] ?? '');
        });

        $rows = '';
        foreach ($termine as $termin) {
            $datum_raw = $termin['datum'] ?? '';
            $datum = '';
            if ($datum_raw) {
                $date_obj = DateTime::createFromFormat('Y-m-d', $datum_raw);
                $datum = $date_obj ? $date_obj->format('d.m.Y') : $datum_raw;
            }

            $name = esc_html($termin['name'] ?? '');
            $ort = esc_html($termin['ort'] ?? '');
            $link = !empty($termin['link']) ? esc_url($termin['link']) : '';

            $title_cell = $name;
            if ($link) {
                $title_cell = '<a href="' . $link . '">' . $name . '</a>';
            }

            $rows .= '<tr>'
                . '<td>' . esc_html($datum) . '</td>'
                . '<td>' . $title_cell . '</td>'
                . '<td>' . $ort . '</td>'
                . '</tr>';
        }

        return '<table class="vv-termine-table">'
            . '<thead><tr><th>Datum</th><th>Titel</th><th>Ort</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>';
    }

    public function shortcode_ansprechpartner($atts): string
    {
        $atts = shortcode_atts([
            'sparte' => '',
        ], $atts, 'vv_ansprechpartner');

        $spart_id = $this->resolve_sparte_identifier($atts['sparte']);
        if (!$spart_id) {
            return '<p>Keine Sparte ausgewählt.</p>';
        }

        $ansprechpartner = $this->get_ansprechpartner();
        $ansprechpartner = array_values(array_filter($ansprechpartner, function ($entry) use ($spart_id) {
            return ($entry['spart_id'] ?? '') === $spart_id;
        }));

        if (empty($ansprechpartner)) {
            return '<p>Keine Ansprechpartner vorhanden.</p>';
        }

        $cards = '';
        foreach ($ansprechpartner as $entry) {
            $user_id = (int) ($entry['user_id'] ?? 0);
            $user = $user_id ? get_user_by('id', $user_id) : null;
            $name = $user ? $user->display_name : ($entry['user_label'] ?? '');
            $email = $user ? $user->user_email : '';
            $phone = $user_id ? (string) get_user_meta($user_id, self::META_USER_PHONE, true) : '';
            $address = $user_id ? (string) get_user_meta($user_id, self::META_USER_ADDRESS, true) : '';

            $avatar = $user_id ? get_avatar($user_id, 96) : '';

            $cards .= '<div class="vv-ansprechpartner-card">'
                . '<div class="vv-ap-avatar">' . $avatar . '</div>'
                . '<div class="vv-ap-body">'
                . '<div class="vv-ap-col vv-ap-left">'
                . '<div class="vv-ap-role">' . esc_html($entry['funktion_label'] ?? '') . '</div>'
                . '<div class="vv-ap-name">' . esc_html($name) . '</div>'
                . ($email ? '<div class="vv-ap-email"><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></div>' : '')
                . '</div>'
                . '<div class="vv-ap-col vv-ap-right">'
                . ($phone ? '<div class="vv-ap-phone">' . esc_html($phone) . '</div>' : '')
                . ($address ? '<div class="vv-ap-address">' . nl2br(esc_html($address)) . '</div>' : '')
                . '</div>'
                . '</div>'
                . '</div>';
        }

        return '<div class="vv-ansprechpartner-grid">' . $cards . '</div>';
    }

    public function shortcode_buehne($atts): string
    {
        $atts = shortcode_atts([
            'sparte' => '',
        ], $atts, 'vv_buehne');

        $spart_id = $this->resolve_sparte_identifier($atts['sparte']);
        if (!$spart_id) {
            return '<p>Keine Sparte ausgewählt.</p>';
        }

        $users = get_users([
            'fields' => ['ID', 'display_name', 'user_nicename'],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => self::META_USER_SPARTE,
                    'value' => $spart_id,
                    'compare' => '='
                ],
                [
                    'key' => self::META_USER_PUBLIC_PROFILE,
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ]);

        if (empty($users)) {
            return '<p>Keine Benutzer vorhanden.</p>';
        }

        $items = '';
        foreach ($users as $user) {
            $avatar = get_avatar($user->ID, 96);
            $name = esc_html($user->display_name);
            $url = esc_url(home_url('/user/' . $user->ID));

            $items .= '<a class="vv-buehne-card" href="' . $url . '">'
                . '<div class="vv-buehne-avatar">' . $avatar . '</div>'
                . '<div class="vv-buehne-name">' . $name . '</div>'
                . '</a>';
        }

        return '<div class="vv-buehne-grid">' . $items . '</div>';
    }

    private function resolve_sparte_identifier(string $value): string
    {
        $value = trim(sanitize_text_field($value));
        if ($value === '') {
            return '';
        }

        $sparten = $this->get_sparten();
        foreach ($sparten as $spart) {
            if (strcasecmp($spart['id'] ?? '', $value) === 0) {
                return (string) $spart['id'];
            }
            if (strcasecmp($spart['name'] ?? '', $value) === 0) {
                return (string) $spart['id'];
            }
        }

        return '';
    }

    public function output_frontend_styles(): void
    {
        echo '<style>
            .vv-ansprechpartner-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .vv-ansprechpartner-card { display: grid; grid-template-columns: 64px 1fr; gap: 12px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; align-items: start; }
            .vv-ap-avatar img { border-radius: 50%; width: 64px; height: 64px; object-fit: cover; }
            .vv-ap-body { display: grid; grid-template-columns: 1fr 1fr; column-gap: 16px; row-gap: 6px; align-items: start; }
            .vv-ap-col { display: grid; row-gap: 4px; }
            .vv-ap-name { font-weight: 600; margin: 0; }
            .vv-ap-role { color: #6b7280; margin: 0; font-size: 0.95em; }
            .vv-ap-email, .vv-ap-phone, .vv-ap-address { color: #374151; margin: 0; font-size: 0.95em; }
            @media (max-width: 900px) { .vv-ansprechpartner-grid { grid-template-columns: 1fr; } }
            @media (max-width: 720px) {
                .vv-ansprechpartner-card { grid-template-columns: 1fr; text-align: center; }
                .vv-ap-avatar { justify-self: center; }
                .vv-ap-body { grid-template-columns: 1fr; text-align: center; }
                .vv-ap-col { justify-items: center; }
            }
            @media (max-width: 640px) { .vv-ansprechpartner-grid { grid-template-columns: 1fr; } }
            .vv-buehne-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
            .vv-buehne-card { display: grid; justify-items: center; gap: 8px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; text-decoration: none; }
            .vv-buehne-avatar img { border-radius: 50%; width: 96px; height: 96px; object-fit: cover; }
            .vv-buehne-name { font-weight: 600; color: #111827; text-align: center; }
            @media (max-width: 1024px) { .vv-buehne-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
            @media (max-width: 768px) { .vv-buehne-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            @media (max-width: 480px) { .vv-buehne-grid { grid-template-columns: 1fr; } }
            .vv-user-banner { width: 100%; height: 260px; background: #f3f4f6; border-radius: 12px; overflow: hidden; margin: 16px 0; position: relative; }
            .vv-user-banner img { width: 100%; height: 100%; object-fit: cover; display: block; }
            .vv-user-banner::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.0) 0%, rgba(0,0,0,0.55) 100%); }
            .vv-user-banner-overlay { position: absolute; inset: auto 0 0 0; padding: 16px; z-index: 2; color: #fff; }
            .vv-user-title { display: flex; align-items: center; gap: 12px; }
            .vv-user-avatar img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.8); }
            .vv-user-title-text { display: flex; flex-direction: column; align-items: center; }
            .vv-user-header { margin: 0 0 6px; color: #fff; text-shadow: 0 2px 6px rgba(0,0,0,0.4); text-align: center; }
            .vv-user-meta { color: #e5e7eb; text-shadow: 0 1px 4px rgba(0,0,0,0.4); text-align: center; }
            .vv-user-results { width: 100%; border-collapse: collapse; margin-top: 16px; }
            .vv-user-results th, .vv-user-results td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; }
            .vv-user-results thead th { background: #f8fafc; }
            .vv-user-results-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .vv-user-results { min-width: 640px; }
        </style>';
    }

    public function register_profile_rewrite(): void
    {
        add_rewrite_rule('^user/([^/]+)/?$', 'index.php?vv_user_profile=$matches[1]', 'top');
    }

    public function maybe_flush_profile_rewrite(): void
    {
        if (get_option('vv_profile_rewrite_flushed') === '1') {
            return;
        }

        $this->register_profile_rewrite();
        flush_rewrite_rules();
        update_option('vv_profile_rewrite_flushed', '1', false);
    }

    public function register_profile_query_var(array $vars): array
    {
        $vars[] = 'vv_user_profile';
        return $vars;
    }

    public function load_profile_template(string $template): string
    {
        $slug = get_query_var('vv_user_profile');
        if (!$slug) {
            return $template;
        }

        $path = plugin_dir_path(__FILE__) . '../templates/user-profile.php';
        if (file_exists($path)) {
            return $path;
        }

        return $template;
    }

    public function render_user_contact_fields($user): void
    {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }

        wp_enqueue_media();

        $phone = (string) get_user_meta($user->ID, self::META_USER_PHONE, true);
        $address = (string) get_user_meta($user->ID, self::META_USER_ADDRESS, true);
        $current_sparte = (string) get_user_meta($user->ID, self::META_USER_SPARTE, true);
        $current_klasse = (string) get_user_meta($user->ID, self::META_USER_KLASSE, true);
        $public_profile = (string) get_user_meta($user->ID, self::META_USER_PUBLIC_PROFILE, true);
        $banner_url = (string) get_user_meta($user->ID, self::META_USER_BANNER, true);
        $banner_id = (string) get_user_meta($user->ID, self::META_USER_BANNER_ID, true);
        $sparten = $this->sort_by_name($this->get_sparten());
        $klassen = $this->sort_by_name($this->get_klassen());
        ?>
        <h2>Über Dich</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="vv_user_sparte">Sparte</label></th>
                <td>
                    <select name="vv_user_sparte" id="vv_user_sparte" class="regular-text">
                        <option value="">Keine</option>
                        <?php foreach ($sparten as $spart): ?>
                            <option value="<?php echo esc_attr($spart['id']); ?>" <?php selected($current_sparte, $spart['id']); ?>>
                                <?php echo esc_html($spart['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="vv_user_public_profile">Sportlerprofil öffentlich anzeigen</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="vv_user_public_profile" id="vv_user_public_profile" value="1" <?php checked($public_profile, '1'); ?> />
                        Ja
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="vv_user_klasse">Klasse</label></th>
                <td>
                    <select name="vv_user_klasse" id="vv_user_klasse" class="regular-text">
                        <option value="">Keine</option>
                        <?php foreach ($klassen as $klasse): ?>
                            <option value="<?php echo esc_attr($klasse['id']); ?>"
                                data-spart="<?php echo esc_attr($klasse['spart_id'] ?? ''); ?>" <?php selected($current_klasse, $klasse['id']); ?>>
                                <?php echo esc_html($klasse['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="vv_user_banner">Bannerbild</label></th>
                <td>
                    <div id="vv-user-banner-preview" style="margin-bottom:10px;">
                        <?php if ($banner_url): ?>
                            <img src="<?php echo esc_url($banner_url); ?>" alt=""
                                style="width:100%;max-width:480px;height:auto;border-radius:8px;" />
                        <?php else: ?>
                            <span
                                style="display:inline-block;width:100%;max-width:480px;height:120px;background:#f3f4f6;border-radius:8px;"></span>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="vv_user_banner" id="vv_user_banner"
                        value="<?php echo esc_url($banner_url); ?>" />
                    <input type="hidden" name="vv_user_banner_id" id="vv_user_banner_id"
                        value="<?php echo esc_attr($banner_id); ?>" />
                    <button type="button" class="button" id="vv-user-banner-upload">Banner auswählen</button>
                    <button type="button" class="button" id="vv-user-banner-remove" style="margin-left:6px;">Entfernen</button>
                </td>
            </tr>
            <tr>
                <th><label for="vv_user_phone">Telefon</label></th>
                <td><input type="text" name="vv_user_phone" id="vv_user_phone" class="regular-text"
                        value="<?php echo esc_attr($phone); ?>" /></td>
            </tr>
            <tr>
                <th><label for="vv_user_address">Anschrift</label></th>
                <td><textarea name="vv_user_address" id="vv_user_address" class="large-text"
                        rows="3"><?php echo esc_textarea($address); ?></textarea></td>
            </tr>
        </table>
        <script>
            (function () {
                var sparteSelect = document.getElementById('vv_user_sparte');
                var klasseSelect = document.getElementById('vv_user_klasse');
                if (!sparteSelect || !klasseSelect) {
                    return;
                }

                function filterKlassen() {
                    var sparte = sparteSelect.value;
                    var hasSelection = false;
                    Array.prototype.forEach.call(klasseSelect.options, function (option) {
                        if (!option.value) {
                            option.hidden = false;
                            return;
                        }
                        var optionSparte = option.getAttribute('data-spart') || '';
                        var visible = sparte === '' || optionSparte === sparte;
                        option.hidden = !visible;
                        if (visible && option.selected) {
                            hasSelection = true;
                        }
                    });

                    if (!hasSelection) {
                        klasseSelect.value = '';
                    }
                }

                sparteSelect.addEventListener('change', filterKlassen);
                filterKlassen();
            })();
        </script>
        <script>
            (function ($) {
                var bannerFrame;
                var $bannerPreview = $('#vv-user-banner-preview');
                var $bannerInput = $('#vv_user_banner');
                var $bannerIdInput = $('#vv_user_banner_id');

                $('#vv-user-banner-upload').on('click', function (e) {
                    e.preventDefault();

                    if (bannerFrame) {
                        bannerFrame.open();
                        return;
                    }

                    bannerFrame = wp.media({
                        title: 'Banner auswählen',
                        button: { text: 'Banner verwenden' },
                        multiple: false,
                        library: { type: 'image' }
                    });

                    bannerFrame.on('select', function () {
                        var attachment = bannerFrame.state().get('selection').first().toJSON();
                        $bannerInput.val(attachment.url || '');
                        $bannerIdInput.val(attachment.id || '');
                        $bannerPreview.html('<img src="' + attachment.url + '" alt="" style="width:100%;max-width:480px;height:auto;border-radius:8px;" />');
                    });

                    bannerFrame.open();
                });

                $('#vv-user-banner-remove').on('click', function (e) {
                    e.preventDefault();
                    $bannerInput.val('');
                    $bannerIdInput.val('');
                    $bannerPreview.html('<span style="display:inline-block;width:100%;max-width:480px;height:120px;background:#f3f4f6;border-radius:8px;"></span>');
                });
            })(jQuery);
        </script>
        <?php
    }

    public function save_user_contact_fields(int $user_id): void
    {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        $phone = isset($_POST['vv_user_phone']) ? sanitize_text_field(wp_unslash($_POST['vv_user_phone'])) : '';
        $address = isset($_POST['vv_user_address']) ? sanitize_textarea_field(wp_unslash($_POST['vv_user_address'])) : '';
        $sparte_id = isset($_POST['vv_user_sparte']) ? sanitize_text_field(wp_unslash($_POST['vv_user_sparte'])) : '';
        $klasse_id = isset($_POST['vv_user_klasse']) ? sanitize_text_field(wp_unslash($_POST['vv_user_klasse'])) : '';
        $public_profile = isset($_POST['vv_user_public_profile']) ? '1' : '0';
        $banner_url = isset($_POST['vv_user_banner']) ? esc_url_raw(wp_unslash($_POST['vv_user_banner'])) : '';
        $banner_id = isset($_POST['vv_user_banner_id']) ? absint($_POST['vv_user_banner_id']) : 0;

        if ($phone !== '') {
            update_user_meta($user_id, self::META_USER_PHONE, $phone);
        } else {
            delete_user_meta($user_id, self::META_USER_PHONE);
        }

        if ($address !== '') {
            update_user_meta($user_id, self::META_USER_ADDRESS, $address);
        } else {
            delete_user_meta($user_id, self::META_USER_ADDRESS);
        }

        if ($public_profile === '1') {
            update_user_meta($user_id, self::META_USER_PUBLIC_PROFILE, '1');
        } else {
            delete_user_meta($user_id, self::META_USER_PUBLIC_PROFILE);
        }

        if ($banner_url !== '') {
            update_user_meta($user_id, self::META_USER_BANNER, $banner_url);
        } else {
            delete_user_meta($user_id, self::META_USER_BANNER);
        }

        if ($banner_id > 0) {
            update_user_meta($user_id, self::META_USER_BANNER_ID, $banner_id);
        } else {
            delete_user_meta($user_id, self::META_USER_BANNER_ID);
        }

        if ($sparte_id === '') {
            delete_user_meta($user_id, self::META_USER_SPARTE);
            return;
        }

        $sparten = $this->get_sparten();
        $valid = false;
        foreach ($sparten as $spart) {
            if (($spart['id'] ?? '') === $sparte_id) {
                $valid = true;
                break;
            }
        }

        if ($valid) {
            update_user_meta($user_id, self::META_USER_SPARTE, $sparte_id);
        }

        if ($klasse_id === '') {
            delete_user_meta($user_id, self::META_USER_KLASSE);
            return;
        }

        $klassen = $this->get_klassen();
        $klasse_valid = false;
        foreach ($klassen as $klasse) {
            if (($klasse['id'] ?? '') === $klasse_id) {
                $klasse_valid = true;
                break;
            }
        }

        if ($klasse_valid) {
            update_user_meta($user_id, self::META_USER_KLASSE, $klasse_id);
        }
    }

    public function hide_wp_events_dashboard_widget(): void
    {
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
    }

    public function register_termine_dashboard_widget(): void
    {
        wp_add_dashboard_widget(
            'vv_termine_dashboard_widget',
            'Termine',
            [$this, 'render_termine_dashboard_widget']
        );
    }

    public function render_termine_dashboard_widget(): void
    {
        $termine = $this->get_termine();
        usort($termine, function ($a, $b) {
            return strcmp($b['datum'] ?? '', $a['datum'] ?? '');
        });

        if (empty($termine)) {
            echo '<p>Keine Termine vorhanden.</p>';
            return;
        }

        echo '<ul>';
        foreach ($termine as $termin) {
            $datum_raw = $termin['datum'] ?? '';
            $datum = '';
            if ($datum_raw) {
                $date_obj = DateTime::createFromFormat('Y-m-d', $datum_raw);
                $datum = $date_obj ? $date_obj->format('d.m.Y') : $datum_raw;
            }

            $name = esc_html($termin['name'] ?? '');
            $ort = esc_html($termin['ort'] ?? '');
            $label = trim(($datum ? $datum . ' - ' : '') . $name);

            echo '<li>' . esc_html($label) . ($ort ? ' (' . esc_html($ort) . ')' : '') . '</li>';
        }
        echo '</ul>';
    }

    public function add_display_name_option_script(): void
    {
        $user_id = 0;
        if (isset($_GET['user_id'])) {
            $user_id = (int) $_GET['user_id'];
        } else {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return;
        }

        $first_name = (string) get_user_meta($user_id, 'first_name', true);
        $last_name = (string) get_user_meta($user_id, 'last_name', true);

        if (!$first_name || !$last_name) {
            return;
        }

        $last_initial = mb_substr($last_name, 0, 1);
        $display_variant = $first_name . ' ' . $last_initial . '.';

        ?>
        <script>
        (function($) {
            $(document).ready(function() {
                var $displayNameSelect = $('#display_name');
                if ($displayNameSelect.length) {
                    var variant = <?php echo json_encode($display_variant); ?>;
                    var exists = false;
                    
                    $displayNameSelect.find('option').each(function() {
                        if ($(this).val() === variant) {
                            exists = true;
                            return false;
                        }
                    });
                    
                    if (!exists) {
                        $displayNameSelect.append($('<option></option>').attr('value', variant).text(variant));
                    }
                }
                
                // Bei Änderungen von Vorname/Nachname die Option aktualisieren
                $('#first_name, #last_name').on('change keyup', function() {
                    var firstName = $('#first_name').val().trim();
                    var lastName = $('#last_name').val().trim();
                    
                    if (firstName && lastName) {
                        var lastInitial = lastName.charAt(0);
                        var newVariant = firstName + ' ' + lastInitial + '.';
                        
                        var $displayNameSelect = $('#display_name');
                        var $existingOption = $displayNameSelect.find('option[value^="' + firstName + ' "]').filter(function() {
                            return /^.+ [A-Z]\.$/.test($(this).val());
                        });
                        
                        if ($existingOption.length) {
                            $existingOption.val(newVariant).text(newVariant);
                        } else {
                            $displayNameSelect.append($('<option></option>').attr('value', newVariant).text(newVariant));
                        }
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}

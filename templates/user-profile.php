<?php
if (!defined('ABSPATH')) {
    exit;
}

$slug = (string) get_query_var('vv_user_profile');
$user = $slug ? get_user_by('slug', $slug) : null;

if (!$user) {
    status_header(404);
    nocache_headers();
    get_header();
    echo '<div class="wrap"><p>Profil nicht gefunden.</p></div>';
    get_footer();
    exit;
}

$public_profile = (string) get_user_meta($user->ID, Vereinsverwaltung_Plugin::META_USER_PUBLIC_PROFILE, true);
if ($public_profile !== '1') {
    status_header(404);
    nocache_headers();
    get_header();
    echo '<div class="wrap"><p>Profil nicht öffentlich.</p></div>';
    get_footer();
    exit;
}

$first_name = (string) get_user_meta($user->ID, 'first_name', true);
$last_name = (string) get_user_meta($user->ID, 'last_name', true);
$display_name = trim($first_name . ' ' . $last_name);
if ($display_name === '') {
    $display_name = $user->display_name;
}

$banner_url = (string) get_user_meta($user->ID, Vereinsverwaltung_Plugin::META_USER_BANNER, true);
$banner_id = (int) get_user_meta($user->ID, Vereinsverwaltung_Plugin::META_USER_BANNER_ID, true);

if ($banner_url === '') {
    $all_urls = (array) get_user_meta($user->ID, Vereinsverwaltung_Plugin::META_USER_BANNER, false);
    foreach (array_reverse($all_urls) as $value) {
        $value = (string) $value;
        if ($value !== '') {
            $banner_url = $value;
            break;
        }
    }
}

if ($banner_url === '') {
    $all_urls = (array) get_user_meta($user->ID, 'vv_user_banner', false);
    foreach (array_reverse($all_urls) as $value) {
        $value = (string) $value;
        if ($value !== '') {
            $banner_url = $value;
            break;
        }
    }
}
if ($banner_id > 0) {
    $resolved = wp_get_attachment_image_url($banner_id, 'full');
    if ($resolved) {
        $banner_url = $resolved;
    }
}

if ($banner_id === 0 && $banner_url !== '') {
    $maybe_id = attachment_url_to_postid($banner_url);
    if ($maybe_id) {
        $resolved = wp_get_attachment_image_url($maybe_id, 'full');
        if ($resolved) {
            $banner_url = $resolved;
        }
    }
}

if ($banner_url !== '') {
    $parsed = wp_parse_url($banner_url);
    if (!isset($parsed['scheme'])) {
        $banner_url = home_url($banner_url);
    }
}
$current_sparte = (string) get_user_meta($user->ID, Vereinsverwaltung_Plugin::META_USER_SPARTE, true);
$current_klasse = (string) get_user_meta($user->ID, Vereinsverwaltung_Plugin::META_USER_KLASSE, true);

$sparten = get_option(Vereinsverwaltung_Plugin::OPT_SPARTEN, []);
$sparten = is_array($sparten) ? $sparten : [];
$klassen = get_option(Vereinsverwaltung_Plugin::OPT_KLASSEN, []);
$klassen = is_array($klassen) ? $klassen : [];

$sparte_label = '';
foreach ($sparten as $spart) {
    if (($spart['id'] ?? '') === $current_sparte) {
        $sparte_label = (string) ($spart['name'] ?? '');
        break;
    }
}

$klasse_label = '';
foreach ($klassen as $klasse) {
    if (($klasse['id'] ?? '') === $current_klasse) {
        $klasse_label = (string) ($klasse['name'] ?? '');
        break;
    }
}

$ergebnisse = get_option(Vereinsverwaltung_Plugin::OPT_ERGEBNISSE, []);
$ergebnisse = is_array($ergebnisse) ? $ergebnisse : [];
$ergebnisse = array_values(array_filter($ergebnisse, function ($entry) use ($user) {
    return (int) ($entry['user_id'] ?? 0) === (int) $user->ID;
}));

usort($ergebnisse, function ($a, $b) {
    return strcmp($b['datum'] ?? '', $a['datum'] ?? '');
});

get_header();
?>
<div class="wrap">
    <div class="vv-user-banner">
        <?php if ($banner_url): ?>
            <img src="<?php echo esc_url($banner_url); ?>" alt="" />
        <?php else: ?>
            <span aria-hidden="true"></span>
        <?php endif; ?>
        <div class="vv-user-banner-overlay">
            <div class="vv-user-title">
                <div class="vv-user-avatar"><?php echo get_avatar($user->ID, 64); ?></div>
                <div class="vv-user-title-text">
                    <h1 class="vv-user-header"><?php echo esc_html($display_name); ?></h1>
                    <div class="vv-user-meta">
                        <?php
                        if ($sparte_label !== '' && $klasse_label !== '') {
                            echo esc_html($sparte_label . ' (' . $klasse_label . ')');
                        } elseif ($sparte_label !== '') {
                            echo esc_html($sparte_label);
                        } else {
                            echo esc_html($klasse_label);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2>Ergebnisse</h2>
    <?php if (empty($ergebnisse)): ?>
        <p>Keine Ergebnisse vorhanden.</p>
    <?php else: ?>
        <div class="vv-user-results-wrapper">
            <table class="vv-user-results">
                <thead>
                    <tr>
                        <th>Platzierung</th>
                        <th>Veranstaltung</th>
                        <th>Ort</th>
                        <th>Datum</th>
                        <th>Klasse</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ergebnisse as $entry): ?>
                        <?php
                        $raw_date = $entry['datum'] ?? '';
                        $formatted_date = $raw_date;
                        if ($raw_date) {
                            $date_obj = DateTime::createFromFormat('Y-m-d', $raw_date);
                            if ($date_obj) {
                                $formatted_date = $date_obj->format('d.m.Y');
                            }
                        }
                        ?>
                        <tr>
                            <td><?php echo esc_html($entry['platzierung'] ?? ''); ?></td>
                            <td><?php echo esc_html($entry['veranstaltung'] ?? ''); ?></td>
                            <td><?php echo esc_html($entry['ort'] ?? ''); ?></td>
                            <td><?php echo esc_html($formatted_date); ?></td>
                            <td><?php echo esc_html($entry['klasse_label'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php
get_footer();

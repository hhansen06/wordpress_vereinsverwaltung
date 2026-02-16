<?php

if (!defined('ABSPATH')) {
    exit;
}

class VV_Termine_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'vv_termine_widget',
            'Vereinsverwaltung: Termine',
            ['description' => 'Zeigt Termine einer Sparte als Liste an.']
        );
    }

    public function widget($args, $instance): void
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'Termine';
        $spart_id = !empty($instance['spart_id']) ? $instance['spart_id'] : '';

        $termine = get_option(Vereinsverwaltung_Plugin::OPT_TERMINE, []);
        $termine = is_array($termine) ? $termine : [];

        if ($spart_id) {
            $termine = array_values(array_filter($termine, function ($termin) use ($spart_id) {
                return ($termin['spart_id'] ?? '') === $spart_id;
            }));
        }

        $termine = array_values(array_filter($termine, function ($termin) {
            return Vereinsverwaltung_Plugin::is_termin_recent($termin, 5);
        }));

        usort($termine, function ($a, $b) {
            return strcmp($a['datum'] ?? '', $b['datum'] ?? '');
        });

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }

        if (empty($termine)) {
            echo '<p>Keine Termine vorhanden.</p>';
            echo $args['after_widget'];
            return;
        }

        echo '<ul class="vv-termine-list">';
        foreach ($termine as $termin) {
            $name = esc_html($termin['name'] ?? '');
            $datum_raw = $termin['datum'] ?? '';
            $datum = '';
            if ($datum_raw) {
                $date_obj = DateTime::createFromFormat('Y-m-d', $datum_raw);
                $datum = $date_obj ? $date_obj->format('d.m.Y') : $datum_raw;
            }
            $link = !empty($termin['link']) ? esc_url($termin['link']) : '';

            $label = trim(($datum ? $datum . ' - ' : '') . $name);
            $label = esc_html($label);

            echo '<li>';
            if ($link) {
                echo '<a href="' . $link . '">' . $label . '</a>';
            } else {
                echo $label;
            }
            echo '</li>';
        }
        echo '</ul>';

        echo $args['after_widget'];
    }

    public function form($instance): void
    {
        $title = $instance['title'] ?? 'Termine';
        $spart_id = $instance['spart_id'] ?? '';
        $sparten = get_option(Vereinsverwaltung_Plugin::OPT_SPARTEN, []);
        $sparten = is_array($sparten) ? $sparten : [];

        usort($sparten, function ($a, $b) {
            return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
        });
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Titel</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('spart_id')); ?>">Sparte</label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('spart_id')); ?>"
                name="<?php echo esc_attr($this->get_field_name('spart_id')); ?>">
                <option value="">Alle Sparten</option>
                <?php foreach ($sparten as $spart): ?>
                    <option value="<?php echo esc_attr($spart['id']); ?>" <?php selected($spart_id, $spart['id']); ?>>
                        <?php echo esc_html($spart['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance): array
    {
        return [
            'title' => sanitize_text_field($new_instance['title'] ?? ''),
            'spart_id' => sanitize_text_field($new_instance['spart_id'] ?? ''),
        ];
    }
}

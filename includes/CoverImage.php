<?php
declare(strict_types=1);

namespace ImoGrifo;

if ( ! defined('ABSPATH') ) { exit; }

/**
 * Capa do empreendimento (DR-14 / F2-07).
 *
 * Campo próprio (_imo_cover_id), com upload via meta box nativo do WP —
 * funciona tanto no editor clássico quanto no editor de blocos (Gutenberg,
 * padrão pra este CPT já que show_in_rest é true no PostType.php).
 * Sobrescreve post_thumbnail_id: qualquer lugar que leia a Imagem Destacada
 * (inclusive a Dynamic Tag nativa do Elementor usada no card) passa a
 * resolver pra essa imagem automaticamente, sem reconfigurar o editor.
 */
final class CoverImage
{
    private const META_KEY  = '_imo_cover_id';
    private const NONCE_KEY = 'imo_cover_nonce';

    public function boot(): void {
        add_action('init', [$this, 'register_meta']);

        add_filter('post_thumbnail_id', [$this, 'override_thumbnail_id'], 10, 2);

        add_action('add_meta_boxes_empreendimento', [$this, 'register_metabox']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('save_post_empreendimento', [$this, 'save_cover']);
    }

    public function register_metabox(): void {
        add_meta_box(
            'imo_cover_box',
            __('Capa do Empreendimento', 'imo-grifo'),
            [$this, 'render_cover_box'],
            'empreendimento',
            'side',
            'high'
        );
    }

    public function register_meta(): void {
        register_post_meta('empreendimento', self::META_KEY, [
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => false,
            'sanitize_callback' => 'absint',
            'auth_callback'     => static fn(): bool => current_user_can('edit_posts'),
        ]);
    }

    public function override_thumbnail_id(mixed $thumbnail_id, \WP_Post $post): mixed {
        if ( $post->post_type !== 'empreendimento' ) { return $thumbnail_id; }

        $cover_id = (int) get_post_meta($post->ID, self::META_KEY, true);

        return $cover_id > 0 ? $cover_id : $thumbnail_id;
    }

    public function render_cover_box(\WP_Post $post): void {
        wp_nonce_field('imo_cover_save', self::NONCE_KEY);

        $cover_id  = (int) get_post_meta($post->ID, self::META_KEY, true);
        $image_url = $cover_id ? wp_get_attachment_image_url($cover_id, 'medium') : '';
        ?>
        <p class="description">
            <?php esc_html_e('Substitui a Imagem Destacada para este empreendimento. Usada automaticamente em qualquer lugar que exiba a imagem destacada, inclusive no card do Elementor.', 'imo-grifo'); ?>
        </p>

        <div class="imo-cover-preview" style="margin:12px 0;<?php echo $image_url ? '' : 'display:none;'; ?>">
            <img src="<?php echo esc_url((string) $image_url); ?>" style="max-width:100%;height:auto;display:block;" alt="">
        </div>

        <input type="hidden" name="imo_cover_id" class="imo-cover-id-input" value="<?php echo esc_attr((string) $cover_id); ?>">

        <p>
            <button type="button" class="button imo-cover-select"><?php echo $cover_id ? esc_html__('Trocar imagem', 'imo-grifo') : esc_html__('Selecionar imagem', 'imo-grifo'); ?></button>
            <button type="button" class="button imo-cover-remove" <?php echo $cover_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e('Remover imagem', 'imo-grifo'); ?></button>
        </p>
        <?php
    }

    public function enqueue_assets(string $hook): void {
        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) { return; }

        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'empreendimento' ) { return; }

        wp_enqueue_media();

        $js = IMOGRIFO_PATH . 'assets/js/cover-admin.js';
        wp_enqueue_script(
            'imo-cover-admin',
            IMOGRIFO_URL . 'assets/js/cover-admin.js',
            ['jquery'],
            is_readable($js) ? (string) filemtime($js) : IMOGRIFO_VER,
            true
        );

        wp_localize_script('imo-cover-admin', 'imoCoverI18n', [
            'modalTitle'   => __('Selecionar capa do empreendimento', 'imo-grifo'),
            'modalButton'  => __('Usar esta imagem', 'imo-grifo'),
            'buttonSelect' => __('Selecionar imagem', 'imo-grifo'),
            'buttonChange' => __('Trocar imagem', 'imo-grifo'),
        ]);
    }

    public function save_cover(int $post_id): void {
        if ( ! isset($_POST[self::NONCE_KEY]) ) { return; }
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST[self::NONCE_KEY]) ), 'imo_cover_save') ) { return; }
        if ( ! current_user_can('edit_post', $post_id) ) { return; }
        if ( wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) ) { return; }
        if ( ! isset($_POST['imo_cover_id']) ) { return; }

        $cover_id = absint($_POST['imo_cover_id']);

        if ( $cover_id === 0 ) {
            delete_post_meta($post_id, self::META_KEY);
        } else {
            update_post_meta($post_id, self::META_KEY, $cover_id);
        }
    }
}

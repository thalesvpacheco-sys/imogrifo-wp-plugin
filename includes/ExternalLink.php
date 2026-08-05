<?php
declare(strict_types=1);

namespace ImoGrifo;

if ( ! defined('ABSPATH') ) { exit; }

/**
 * Link externo por empreendimento (DR-13 / F2-06).
 *
 * Quando _imo_external_link está preenchido, get_permalink() passa a retornar
 * essa URL para o post — o botão "Veja mais" do card (Post URL nativo do
 * Elementor) abre a LP externa sem precisar mexer no widget nem no template.
 */
final class ExternalLink
{
    private const META_KEY  = '_imo_external_link';
    private const NONCE_KEY = 'imo_external_link_nonce';

    public function boot(): void {
        add_action('init', [$this, 'register_meta']);

        add_filter('post_type_link', [$this, 'override_permalink'], 10, 2);

        add_filter('manage_empreendimento_posts_columns', [$this, 'add_column']);
        add_action('manage_empreendimento_posts_custom_column', [$this, 'render_column'], 10, 2);

        add_action('quick_edit_custom_box', [$this, 'render_quick_edit_field'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_quick_edit_script']);
        add_action('save_post_empreendimento', [$this, 'save_quick_edit']);
    }

    public function register_meta(): void {
        register_post_meta('empreendimento', self::META_KEY, [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => false,
            'sanitize_callback' => 'esc_url_raw',
            'auth_callback'     => static fn(): bool => current_user_can('edit_posts'),
        ]);
    }

    public function override_permalink(string $post_link, \WP_Post $post): string {
        if ( $post->post_type !== 'empreendimento' ) { return $post_link; }

        $external = get_post_meta($post->ID, self::META_KEY, true);
        if ( is_string($external) && $external !== '' ) {
            return esc_url($external);
        }

        return $post_link;
    }

    public function add_column(array $columns): array {
        $columns['imo_external_link'] = __('Link (LP externa)', 'imo-grifo');
        return $columns;
    }

    public function render_column(string $column, int $post_id): void {
        if ( $column !== 'imo_external_link' ) { return; }

        $value = (string) get_post_meta($post_id, self::META_KEY, true);
        echo '<span class="imo-external-link-cell" data-value="' . esc_attr($value) . '">';
        echo $value !== '' ? esc_html__('Sim', 'imo-grifo') : esc_html__('—', 'imo-grifo');
        echo '</span>';
    }

    public function render_quick_edit_field(string $column_name, string $post_type): void {
        if ( $post_type !== 'empreendimento' || $column_name !== 'imo_external_link' ) { return; }

        wp_nonce_field('imo_external_link_save', self::NONCE_KEY);
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php esc_html_e('Link (LP externa)', 'imo-grifo'); ?></span>
                    <span class="input-text-wrap">
                        <input type="url" name="imo_external_link" class="imo-external-link-input"
                               placeholder="https://... (opcional)" value="">
                    </span>
                </label>
            </div>
        </fieldset>
        <?php
    }

    public function enqueue_quick_edit_script(string $hook): void {
        $post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : '';
        if ( $hook !== 'edit.php' || $post_type !== 'empreendimento' ) { return; }

        wp_add_inline_script('inline-edit-post', <<<JS
            jQuery(function(\$){
                var wpInlineEdit = inlineEditPost.edit;
                inlineEditPost.edit = function(id){
                    wpInlineEdit.apply(this, arguments);
                    var postId = (typeof id === 'object') ? parseInt(this.getId(id), 10) : id;
                    if (!postId) { return; }
                    var row = \$('#post-' + postId);
                    var value = row.find('.imo-external-link-cell').data('value') || '';
                    \$('input.imo-external-link-input').val(value);
                };
            });
JS
        );
    }

    public function save_quick_edit(int $post_id): void {
        if ( ! isset($_POST[self::NONCE_KEY]) ) { return; }
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST[self::NONCE_KEY]) ), 'imo_external_link_save') ) { return; }
        if ( ! current_user_can('edit_post', $post_id) ) { return; }
        if ( ! isset($_POST['imo_external_link']) ) { return; }

        $url = sanitize_text_field( wp_unslash($_POST['imo_external_link']) );
        $url = esc_url_raw($url);

        if ( $url === '' ) {
            delete_post_meta($post_id, self::META_KEY);
        } else {
            update_post_meta($post_id, self::META_KEY, $url);
        }
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('admin_menu', 'mlzrcp_add_reset_cpt_options_page');
function mlzrcp_add_reset_cpt_options_page() {
    add_submenu_page(
        'tools.php',
        'Reset CPT Options',
        'Reset CPT',
        'manage_options',
        'mlzrcp_reset_cpt',
        'mlzrcp_display_reset_cpt_options_page'
    );
}

function mlzrcp_display_reset_cpt_options_page() {
    ?>
    <div class="wrap" id="reset-custom-post">
        <h2>Reset CPT Options</h2>
        <form method="post" action="options.php">
            <input type="hidden" id="nonce" name="nonce" value="<?php echo esc_attr(wp_create_nonce( 'mlzrcp_reset_cpt_nonce' )); ?>">
            <div class="inside">
                <div class="panel-form">
                    
                    <div class="rcpt-load">
                        <div class="rcpt-loader"></div>
                    </div>
                    <div>
                        <label><strong><?php esc_attr_e('Select Custom post type', 'reset-custom-post'); ?></strong></label>
                        <?php   
                        $custom_post_type = get_option('custom_post_type');
                        $post_types = get_post_types(array('public' => true), 'objects');
                        ?>
                        <select name="custom_post_type" id="custom_post_type" autocomplete="off">
                            <option value=""><strong><?php esc_attr_e('Select', 'reset-custom-post'); ?></strong></option>
                            <?php foreach ($post_types as $post_type) { ?>
                                <option value="<?php echo esc_attr($post_type->name); ?>">
                                    <?php echo esc_attr($post_type->labels->singular_name); ?>
                                </option>
                            <?php } ?>
                        </select> 
                    </div>
                    <div>
                        <?php
                        $delete_images = get_option('delete_images'); ?>   
                        <strong><?php esc_attr_e('Delete attachments images ?', 'reset-custom-post'); ?></strong>
                        <label class="toggle-switch">
                            <input type="checkbox" name="delete_images" id="delete_images" value="1" <?php checked($delete_images, 1); ?> />
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                    <div id="taxonomy-list"></div>
                    <div>
                        <p class="txt-total"><strong><?php esc_attr_e('Total:', 'reset-custom-post'); ?></strong> <span id="total-posts"></span></p>
                        <button type="submit" name="submit" id="mlzrcp_reset_cpt_button" class="btn-reset" data-total="" disabled>
                            <span class="btn-txt"><?php esc_attr_e('Delete', 'reset-custom-post'); ?></span>
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"></path></svg></span>
                        </button>
                    </div>
                </div>
                <div class="terminal-loader">
                    <div class="terminal-header">
                        <div class="terminal-title"><?php esc_attr_e('Logs', 'reset-custom-post'); ?></div>
                        <div class="terminal-controls">
                            <div class="control close"></div>
                            <div class="control minimize"></div>
                            <div class="control maximize"></div>
                        </div>
                    </div>
                    <div id="progress-bar-container">
                        <div id="progress-bar"></div>
                    </div>
                    <div id="log" class="text"></div>
                </div>
            </div>
        </form>

    </div>    
    
    <?php
}
<?php
class Merit_Aktiva_Settings {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_theme_options_page'));
        add_action('admin_init', array($this, 'setup_theme_options'));
    }

    public function add_theme_options_page() {
        add_menu_page(
            'Merit Aktiva Options',
            'Merit Aktiva Options',
            'manage_options',
            'merit-aktiva-options',
            array($this, 'render_theme_options_page'));
    }

    public function render_theme_options_page() {
        ?>
        <div class="wrap">
            <h2>Merit aktiva plugin Options</h2>
            <form method="post" action="options.php">
                <?php settings_fields('theme_options_group'); ?>
                <?php do_settings_sections('theme_options'); ?>
                <input type="submit" class="button-primary" value="Save Changes">
            </form>
        </div>
        <?php
    }

    public function setup_theme_options() {
        add_settings_section(
            'theme_options_section',
            'General Settings',
            array($this, 'section_callback'),
            'theme_options');

        // Text Input Field
        add_settings_field(
            'apikey_text_field',
            'Insert API Key in here',
            array($this, 'text_field_callback'),
            'theme_options',
            'theme_options_section');
        register_setting(
            'theme_options_group',
            'apikey_text_field',
            array($this, 'sanitize_text_field'));

        // Tax Select Field
        add_settings_field(
            'tax_select_field',
            'Select tax type',
            array($this, 'select_field_callback'),
            'theme_options',
            'theme_options_section');
        register_setting(
            'theme_options_group',
            'tax_select_field',
            array($this, 'sanitize_text_field'));

        // Payment Status Select Field
        add_settings_field(
            'payment_status_select_field',
            'Payment status when invices are sended to Merit aktiva server type',
            array($this, 'payment_status_field_callback'),
            'theme_options',
            'theme_options_section');
        register_setting(
            'theme_options_group',
            'payment_status_select_field',
            array($this, 'sanitize_text_field'));


    }

    public function section_callback() {
        echo '<p>Here are Merit aktiva syn plugin options.</p>';
    }

    public function text_field_callback() {
        $value = get_option('apikey_text_field');
        echo '<input type="text" name="apikey_text_field" value="' . esc_attr($value) . '" />';
    }

    public function select_field_callback() {
        $value = get_option('tax_select_field');
        echo '<select name="tax_select_field">';
        echo '<option value="307000b4-f1f2-4bc7-a110-24cb18d77212" ' . selected($value, '307000b4-f1f2-4bc7-a110-24cb18d77212', false) . '>22% käibemaks</option>';
        echo '<option value="c72ccfab-94fe-479a-9832-e78c0cfb0f34" ' . selected($value, 'c72ccfab-94fe-479a-9832-e78c0cfb0f34', false) . '>Maksuvaba käive</option>';
        echo '<option value="973a4395-665f-47a6-a5b6-5384dd24f8d0" ' . selected($value, '973a4395-665f-47a6-a5b6-5384dd24f8d0', false) . '>0% käibemaks</option>';
        echo '</select>';
    }

    public function payment_status_field_callback() {
        $value = get_option('payment_status_select_field');
        echo '<select name="payment_status_select_field">';
        echo '<option value="all" ' . selected($value, 'all', true) . '>All</option>';
        echo '<option value="processing" ' . selected($value, 'processing', false) . '>Processing</option>';
        echo '<option value="on-hold" ' . selected($value, 'on-hold', false) . '>On hold</option>';
        echo '<option value="completed" ' . selected($value, 'completed', false) . '>Completed</option>';
        echo '<option value="cancelled" ' . selected($value, 'cancelled', false) . '>Cancelled</option>';
        echo '<option value="refunded" ' . selected($value, 'refunded', false) . '>Refunded</option>';
        echo '<option value="failed" ' . selected($value, 'failed', false) . '>Failed</option>';
        echo '<option value="draft" ' . selected($value, 'draft', false) . '>Draft</option>';
        echo '</select>';
    }



    public function sanitize_text_field($input) {
        return sanitize_text_field($input);
    }
}

// Instantiate the class
new Merit_Aktiva_Settings();

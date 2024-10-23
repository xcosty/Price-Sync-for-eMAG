<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Plugin Name: Price Sync for eMAG
 * Description: Sync WooCommerce product prices with eMAG Marketplace and includes advanced settings for added functionality.
 * Version: 1.5.1
 * Author: xCosty
 * Author URI: https://www.solgarden.ro
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/*Funcționalități
Acest plugin oferă sincronizarea automată a prețurilor și stocurilor produselor din WooCommerce cu eMAG Marketplace, folosind API-ul eMAG. Include și setări avansate care extind funcționalitatea pluginului. Iată ce poate face pluginul:

Sincronizare prețuri și stocuri: Produsele din WooCommerce sunt sincronizate automat cu API-ul eMAG. Pluginul actualizează prețurile și stocurile produselor listate pe eMAG.

Adăugare adaos la prețuri: Setarea "Price Multiplier" permite aplicarea unui adaos procentual la prețurile produselor înainte de a fi trimise către eMAG.

Sincronizare selectivă pe branduri: Utilizatorii pot alege să sincronizeze doar anumite branduri, specificate în secțiunea de setări "Brands to Sync".

Sincronizare stocuri pe branduri: Setarea "Brands with Real Stock" permite selectarea brandurilor care vor trimite stocul real către eMAG. Pentru celelalte branduri, se poate configura o valoare de stoc personalizată.

Maparea personalizată a ID-urilor: Pluginul permite maparea ID-urilor produselor între WooCommerce și eMAG, astfel încât sincronizarea să țină cont de identificatori personalizați, specificați în formatul eMAG_ID=WordPress_ID.

Configurarea unui stoc minim: Setarea "Stock Value for Zero Stock" permite definirea unei valori de stoc minime care va fi transmisă către eMAG în cazul în care stocul produsului este 0 în WooCommerce.

Sincronizare automată periodică: Pluginul folosește un cron job care rulează sincronizarea la fiecare oră. Acest comportament poate fi personalizat în funcție de nevoi.

Opțiuni de logare în debug: Utilizatorii pot activa opțiunea de logare în fișierul debug.log pentru a verifica posibilele erori sau mesaje de eroare trimise de API-ul eMAG.

Interfață de administrare avansată: Setările API eMAG și cele avansate sunt gestionate printr-o interfață dedicată în panoul de administrare WordPress, permițând utilizatorilor să configureze cu ușurință parametrii necesari.

Selectare multiplă pentru branduri: Caseta de selecție a brandurilor este afișată într-o fereastră statică, cu dimensiuni personalizate (300px înălțime și 100% lățime), și permite selecția multiplă de branduri folosind Ctrl sau Shift.

Cum funcționează:
Sincronizarea prețurilor și stocurilor: Când un produs este creat sau actualizat în WooCommerce, pluginul apelează funcțiile API eMAG pentru a sincroniza prețurile și stocurile cu platforma eMAG. Procesul poate fi declanșat automat și printr-un cron job.

Setări avansate: În secțiunea "Advanced API Settings", utilizatorii pot configura multiplicatorii de preț, condițiile de stoc, brandurile sincronizate și setările de logare.

Interfață și administrare: Pluginul adaugă un meniu în secțiunea de setări din WordPress, unde utilizatorii pot introduce detalii de autentificare API (username, password, URL de bază) și să personalizeze comportamentul sincronizării.
*/

// Adăugăm un meniu de setări în panoul de administrare WordPress
add_action('admin_menu', 'weps_add_admin_menu');
add_action('admin_init', 'weps_settings_init');

function weps_add_admin_menu() { 
    add_menu_page('WooCommerce Price Sync for eMAG
', 'Price Sync for eMAG
', 'manage_options', 'woocommerce_emag_price_sync', 'weps_options_page');
    add_menu_page('WooCommerce Price Sync for eMAG
 Advanced', 'Price Sync for eMAG
 Advanced', 'manage_options', 'woocommerce_emag_price_sync_advanced', 'weps_advanced_options_page');
}

function weps_settings_init() { 

// Înregistrarea setărilor cu funcții de sanitizare
register_setting('pluginPage', 'weps_settings', 'sanitize_weps_settings');
register_setting('advancedPluginPage', 'weps_advanced_settings', 'sanitize_weps_advanced_settings');

// Funcția de sanitizare pentru setările generale
function sanitize_weps_settings($input) {
    $sanitized_input = array();

    // Sanitizare pentru numele de utilizator (text simplu)
    if (isset($input['weps_username'])) {
        $sanitized_input['weps_username'] = sanitize_text_field($input['weps_username']);
    }

    // Sanitizare pentru parola (se poate păstra ca text simplu sau criptat)
    if (isset($input['weps_password'])) {
        $sanitized_input['weps_password'] = sanitize_text_field($input['weps_password']);
    }

    // Sanitizare pentru URL-ul API
    if (isset($input['weps_api_url'])) {
        $sanitized_input['weps_api_url'] = esc_url($input['weps_api_url']);
    }

    return $sanitized_input;
}

// Funcția de sanitizare pentru setările avansate
function sanitize_weps_advanced_settings($input) {
    $sanitized_input = array();

    // Sanitizare pentru multiplicator de preț (numeric)
    if (isset($input['price_multiplier'])) {
        $sanitized_input['price_multiplier'] = floatval($input['price_multiplier']);
    }

    // Sanitizare pentru alte setări care pot fi text sau valori numerice
    if (isset($input['some_other_setting'])) {
        $sanitized_input['some_other_setting'] = sanitize_text_field($input['some_other_setting']);
    }

    return $sanitized_input;
}

    add_settings_section(
        'weps_pluginPage_section', 
        __('API Settings', 'price-sync-for-emag'), 
        'weps_settings_section_callback', 
        'pluginPage'
    );

    add_settings_section(
        'weps_advanced_pluginPage_section', 
        __('Advanced API Settings', 'price-sync-for-emag'), 
        'weps_advanced_settings_section_callback', 
        'advancedPluginPage'
    );

    add_settings_field( 
        'weps_username', 
        __('eMAG API Username', 'price-sync-for-emag'), 
        'weps_username_render', 
        'pluginPage', 
        'weps_pluginPage_section' 
    );

    add_settings_field( 
        'weps_password', 
        __('eMAG API Password', 'price-sync-for-emag'), 
        'weps_password_render', 
        'pluginPage', 
        'weps_pluginPage_section' 
    );

    add_settings_field( 
        'weps_base_url', 
        __('eMAG API Base URL', 'price-sync-for-emag'), 
        'weps_base_url_render', 
        'pluginPage', 
        'weps_pluginPage_section' 
    );

    add_settings_field( 
        'weps_selected_brands', 
        __('Brands to Sync', 'price-sync-for-emag'), 
        'weps_selected_brands_render', 
        'advancedPluginPage', 
        'weps_advanced_pluginPage_section' 
    );

    add_settings_field( 
        'weps_real_stock_brands', 
        __('Brands with Real Stock', 'price-sync-for-emag'), 
        'weps_real_stock_brands_render', 
        'advancedPluginPage', 
        'weps_advanced_pluginPage_section' 
    );

    add_settings_field( 
        'weps_stock_zero_value', 
        __('Stock Value for Zero Stock', 'price-sync-for-emag'), 
        'weps_stock_zero_value_render', 
        'advancedPluginPage', 
        'weps_advanced_pluginPage_section' 
    );

    add_settings_field( 
        'weps_price_multiplier', 
        __('Price Multiplier', 'price-sync-for-emag'), 
        'weps_price_multiplier_render', 
        'advancedPluginPage', 
        'weps_advanced_pluginPage_section' 
    );

    add_settings_field( 
        'weps_sale_price_multiplier', 
        __('Sale Price Multiplier', 'price-sync-for-emag'), 
        'weps_sale_price_multiplier_render', 
        'advancedPluginPage', 
        'weps_advanced_pluginPage_section' 
    );

    add_settings_field(
        'weps_debug_logging',
        __('Enable Debug Logging', 'price-sync-for-emag'),
        'weps_debug_logging_render',
        'advancedPluginPage',
        'weps_advanced_pluginPage_section'
    );

    add_settings_field(
        'weps_id_conditions',
        __('ID Conditions', 'price-sync-for-emag'),
        'weps_id_conditions_render',
        'advancedPluginPage',
        'weps_advanced_pluginPage_section'
    );
}

function weps_username_render() { 
    $options = get_option('weps_settings');
    ?>
		<input type='text' name='weps_settings[weps_username]' value='<?php echo esc_attr($options['weps_username']); ?>'>
	<?php
}

function weps_password_render() { 
    $options = get_option('weps_settings');
    ?>
    <input type='password' name='weps_settings[weps_password]' value='<?php echo esc_attr($options['weps_password']); ?>'>
    <?php
}

function weps_base_url_render() { 
    $options = get_option('weps_settings');
    ?>
	<input type='text' name='weps_settings[weps_base_url]' value='<?php echo esc_attr($options['weps_base_url']); ?>'>
    <?php
}

function weps_selected_brands_render() { 
    $options = get_option('weps_advanced_settings');
    $selected_brands = isset($options['weps_selected_brands']) ? $options['weps_selected_brands'] : array();

    $brands = get_terms(array(
        'taxonomy' => apply_filters('weps_brands_taxonomy', 'pwb-brand'),
        'hide_empty' => false,
    ));
    ?>
    <select name='weps_advanced_settings[weps_selected_brands][]' multiple="multiple">
        <?php foreach ($brands as $brand) : ?>
            <option value="<?php echo esc_attr($brand->slug); ?>" <?php echo in_array($brand->slug, $selected_brands) ? 'selected' : ''; ?>><?php echo esc_html($brand->name); ?></option>
        <?php endforeach; ?>
    </select>
    <p class="description"><?php esc_html_e('Selectați brandurile care vor fi sincronizate cu eMAG.', 'price-sync-for-emag'); ?></p>
    <?php
}

function weps_real_stock_brands_render() { 
    $options = get_option('weps_advanced_settings');
    $real_stock_brands = isset($options['weps_real_stock_brands']) ? $options['weps_real_stock_brands'] : array();

    $brands = get_terms(array(
        'taxonomy' => apply_filters('weps_brands_taxonomy', 'pwb-brand'),
        'hide_empty' => false,
    ));
    ?>
    <select name='weps_advanced_settings[weps_real_stock_brands][]' multiple="multiple">
        <?php foreach ($brands as $brand) : ?>
            <option value="<?php echo esc_attr($brand->slug); ?>" <?php echo in_array($brand->slug, $real_stock_brands) ? 'selected' : ''; ?>><?php echo esc_html($brand->name); ?></option>
        <?php endforeach; ?>
    </select>
    <p class="description"><?php esc_html_e('Selectați brandurile care vor trimite stocul real.', 'price-sync-for-emag'); ?></p>
    <?php
}

function weps_stock_zero_value_render() { 
    $options = get_option('weps_advanced_settings');
    ?>
	<input type='number' name='weps_advanced_settings[weps_stock_zero_value]' value='<?php echo esc_attr($options['weps_stock_zero_value'] ?? 0); ?>'>
    <?php
}

function weps_price_multiplier_render() { 
    $options = get_option('weps_advanced_settings');
    ?>
	<input type='text' name='weps_advanced_settings[weps_price_multiplier]' value='<?php echo esc_attr($options['weps_price_multiplier'] ?? 1); ?>'>
    <?php
}

function weps_sale_price_multiplier_render() { 
    $options = get_option('weps_advanced_settings');
    ?>
	<input type='text' name='weps_advanced_settings[weps_sale_price_multiplier]' value='<?php echo esc_attr($options['weps_sale_price_multiplier'] ?? 1); ?>'>
    <?php
}

function weps_debug_logging_render() {
    $options = get_option('weps_advanced_settings');
    ?>
    <input type='checkbox' name='weps_advanced_settings[weps_debug_logging]' <?php checked(isset($options['weps_debug_logging'])); ?> value='1'>
    <p class="description"><?php esc_html_e('Bifați pentru a activa logarea în debug.log.', 'price-sync-for-emag'); ?></p>
    <?php
}

function weps_id_conditions_render() {
    $options = get_option('weps_advanced_settings');
    ?>
    <textarea name='weps_advanced_settings[weps_id_conditions]'><?php echo esc_textarea($options['weps_id_conditions'] ?? ''); ?></textarea>
    <p class="description"><?php esc_html_e('Introduceți condițiile ID-urilor în formatul eMAG_ID=WordPress_ID separate prin virgulă.', 'price-sync-for-emag'); ?></p>
    <?php
}

function weps_settings_section_callback() { 
    echo esc_html__('Introduceți detaliile API-ului eMAG.', 'price-sync-for-emag');
}

function weps_advanced_settings_section_callback() { 
    echo esc_html__('Introduceți detaliile setărilor avansate pentru eMAG.', 'price-sync-for-emag');
}

function weps_options_page() { 
    ?>
    <form action='options.php' method='post'>
        <h2>SBS Price Sync for eMAG
</h2>
        <?php
        settings_fields('pluginPage');
        do_settings_sections('pluginPage');
        submit_button();
        ?>
    </form>
    <?php
}

function weps_advanced_options_page() { 
    ?>
    <form action='options.php' method='post'>
        <h2>SBS Price Sync for eMAG
 Advanced</h2>
        <?php
        settings_fields('advancedPluginPage');
        do_settings_sections('advancedPluginPage');
        submit_button();
        ?>
    </form>
    <?php
}

// Funcția care verifică dacă produsul există pe eMAG și preia `part_number_key`
function weps_get_emag_product_pnk($product_id) {
    $options = get_option('weps_settings');
    $username = $options['weps_username'];
    $password = $options['weps_password'];
    $base_url = $options['weps_base_url'];

    $api_url = $base_url . '/product_offer/read';
    $auth = base64_encode("$username:$password");

    $data = array(
        'id' => !empty($product_id) ? strval($product_id) : null
    );

    $response = wp_remote_post($api_url, array(
        'method' => 'POST',
        'body' => wp_json_encode($data),
        'headers' => array(
            'Authorization' => 'Basic ' . $auth,
            'Content-Type' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        weps_log('eMAG API error: ' . $response->get_error_message());
        return null;
    } else {
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        if (isset($result['isError']) && $result['isError']) {
            weps_log('eMAG API error messages: ' . implode(', ', $result['messages']));
            return null;
        } else {
            return $result['results'][0]['part_number_key'] ?? null;
        }
    }
}

// Funcția care trimite prețurile produselor către eMAG
function weps_sync_prices_to_emag($post_id = null) {
    // Check if the synchronization is disabled
    if (defined('DISABLE_EMAG_SYNC') && DISABLE_EMAG_SYNC) {
        return;
    }

    static $already_called = [];

    if (isset($already_called[$post_id])) {
        return;
    }

    $already_called[$post_id] = true;

    weps_log('weps_sync_prices_to_emag called');
    $options = get_option('weps_settings');
    $username = $options['weps_username'];
    $password = $options['weps_password'];
    $base_url = $options['weps_base_url'];

    $advanced_options = get_option('weps_advanced_settings', []);
    $selected_brands = $advanced_options['weps_selected_brands'] ?? [];
    $real_stock_brands = $advanced_options['weps_real_stock_brands'] ?? [];
    $stock_zero_value = intval($advanced_options['weps_stock_zero_value'] ?? 0);
    $price_multiplier = floatval($advanced_options['weps_price_multiplier'] ?? 1);
    $sale_price_multiplier = floatval($advanced_options['weps_sale_price_multiplier'] ?? 1);
    $id_conditions = $advanced_options['weps_id_conditions'] ?? '';

    weps_log('Advanced options: ' . wp_json_encode($advanced_options));
    weps_log('Selected brands: ' . wp_json_encode($selected_brands));
    weps_log('Real stock brands: ' . wp_json_encode($real_stock_brands));
    weps_log('Stock zero value: ' . $stock_zero_value);
    weps_log('Price multiplier: ' . $price_multiplier);
    weps_log('Sale price multiplier: ' . $sale_price_multiplier);

    // Procesăm condițiile ID-urilor
    $id_mapping = [];
    if (!empty($id_conditions)) {
        $id_pairs = explode(',', $id_conditions);
        foreach ($id_pairs as $pair) {
            list($emag_id, $wp_id) = explode('=', $pair);
            $id_mapping[trim($emag_id)] = trim($wp_id);
        }
    }

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1
    );

    if ($post_id) {
        $args['p'] = $post_id;
    }

    $products = get_posts($args);

    foreach ($products as $product) {
        $product_id = $product->ID;

        // Verificăm dacă există o mapare personalizată pentru acest ID de produs
        if (array_key_exists($product_id, $id_mapping)) {
            $product_id = $id_mapping[$product_id];
        }

        $regular_price = get_post_meta($product_id, '_regular_price', true); // Prețul recomandat de vânzare
        $sale_price = get_post_meta($product_id, '_price', true); // Prețul de vânzare
        $product_name = get_the_title($product_id);
        $product_stock = get_post_meta($product_id, '_stock', true);
        $brand_terms = get_the_terms($product_id, 'pwb-brand');
        $brand = !empty($brand_terms) ? $brand_terms[0]->slug : '';
        $emag_id = get_post_meta($product_id, 'emag_id', true); // Preluăm ID-ul eMAG din metadate
    $part_number_key = get_post_meta($product_id, 'part_number_key', true); // Preluăm valoarea PNK folosind get_post_meta

        if (!in_array($brand, $selected_brands)) {
            continue; // Săriți la următorul produs dacă brandul nu este selectat
        }

        
if (empty($part_number_key)) {
    // If part_number_key is missing, use the product ID
    $part_number_key = $product_id;

            $part_number_key = weps_get_emag_product_pnk($product_id);
            if ($part_number_key) {
                update_post_meta($product_id, 'part_number_key', $part_number_key); // Actualizăm valoarea PNK folosind update_post_meta
            }
        }

        if (empty($product_name)) {
            weps_log("eMAG API error: Missing product name for product id: $product_id");
        }
        if (empty($product_stock) && $product_stock !== '0') {
            weps_log("eMAG API error: Missing stock information for product id: $product_id");
        }
        
if (empty($part_number_key)) {
    // If part_number_key is missing, use the product ID
    $part_number_key = $product_id;

            weps_log("eMAG API error: Missing part_number_key for product id: $product_id");
        }

        if (empty($product_name) || (empty($product_stock) && $product_stock !== '0') || empty($part_number_key)) {
            continue;
        }

        // Calculăm prețurile cu adaosurile setate
        $regular_price = round(floatval($regular_price) * $price_multiplier, 2);
        $sale_price = round(floatval($sale_price) * $sale_price_multiplier, 2);

        // Aplicăm regula de stoc
        if (in_array($brand, $real_stock_brands)) {
            $stock = intval($product_stock);
        } else {
            $stock = ($product_stock > 0) ? intval($product_stock) : $stock_zero_value;
        }

        $api_url = $base_url . '/product_offer/save';
        $auth = base64_encode("$username:$password");

        $data = array(
            array(
                'id' => !empty($emag_id) ? strval($emag_id) : (!empty($product_id) ? strval($product_id) : null), // Folosim ID-ul eMAG aici
                'part_number_key' => strval($part_number_key),
                'sale_price' => strval($sale_price),
                'recommended_price' => strval($regular_price),
                'currency_type' => 'RON',
                'stock' => array(
                    array(
                        'warehouse_id' => 1, 
                        'value' => intval($stock)
                    )
                ),
                'vat_id' => 1 
            )
        );

        weps_log('eMAG API request payload: ' . wp_json_encode($data)); 

        $response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'body' => wp_json_encode($data),
            'headers' => array(
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json'
            )
        ));

        if (is_wp_error($response)) {
            weps_log('eMAG API error: ' . $response->get_error_message());
        } else {
            $body = wp_remote_retrieve_body($response);
            weps_log('eMAG API response: ' . $body);
            $result = json_decode($body, true);
            if (isset($result['isError']) && $result['isError']) {
                weps_log('eMAG API error messages: ' . implode(', ', $result['messages']));
            }
        }
    }
}

// Hook pentru a sincroniza prețurile la salvarea/actualizarea produselor
add_action('woocommerce_update_product', 'weps_sync_prices_to_emag', 99);

// Hook pentru a sincroniza prețurile la crearea produselor
add_action('woocommerce_new_product', 'weps_sync_prices_to_emag', 99);

// Adăugăm funcția de sincronizare la cron job
add_action('weps_sync_emag_cron_job', 'weps_sync_prices_to_emag');

// Funcție pentru a programa cron job-ul
function weps_activate_emag_cron_job() {
    if (!wp_next_scheduled('weps_sync_emag_cron_job')) {
        wp_schedule_event(time(), 'twicedaily', 'weps_sync_emag_cron_job'); // Rulează sincronizarea de 2 ori pe zi
    }
}
add_action('wp', 'weps_activate_emag_cron_job');

// Funcție pentru a dezactiva cron job-ul la dezactivarea pluginului
function weps_deactivate_emag_cron_job() {
    $timestamp = wp_next_scheduled('weps_sync_emag_cron_job');
    wp_unschedule_event($timestamp, 'weps_sync_emag_cron_job');
}
register_deactivation_hook(__FILE__, 'weps_deactivate_emag_cron_job');

// Funcție pentru a loga mesaje în debug.log doar dacă logarea este activată
function weps_log($message) {
    $advanced_options = get_option('weps_advanced_settings', []);
    if (isset($advanced_options['weps_debug_logging']) && $advanced_options['weps_debug_logging']) {
        // Excludem mesajele pe care nu dorim să le logăm
        $excluded_messages = [
            'weps_sync_prices_to_emag called',
            'Advanced options: ',
            'Selected brands: ',
            'Real stock brands: ',
            'Stock zero value: ',
            'Price multiplier: ',
            'Sale price multiplier: '
        ];
        
        foreach ($excluded_messages as $excluded_message) {
            if (strpos($message, $excluded_message) !== false) {
                return;
            }
        }
        
        error_log($message);
    }
}

// Adăugăm un nou tab în secțiunea "Date produs"
add_filter( 'woocommerce_product_data_tabs', 'weps_add_emag_sync_product_data_tab' );
function weps_add_emag_sync_product_data_tab( $tabs ) {
    $tabs['emag_sync'] = array(
        'label'    => __( 'EMAG Sync', 'price-sync-for-emag' ),
        'target'   => 'emag_sync_product_data', // Acesta va fi ID-ul conținutului tab-ului
        'class'    => array(),
        'priority' => 60, // Poziția tab-ului în listă
    );
    return $tabs;
}

// Conținutul tab-ului "EMAG Sync"
add_action( 'woocommerce_product_data_panels', 'weps_add_emag_sync_product_data_fields' );
function weps_add_emag_sync_product_data_fields() {
    global $post;
    ?>
    <div id='emag_sync_product_data' class='panel woocommerce_options_panel'>
        <div class='options_group'>
            <?php
                // Afișăm câmpul part_number_key
                woocommerce_wp_text_input( array(
                    'id'          => 'part_number_key',
                    'label'       => __( 'Part Number Key', 'price-sync-for-emag' ),
                    'description' => __( 'Introduceți codul part_number_key pentru acest produs.', 'price-sync-for-emag' ),
                    'desc_tip'    => 'true',
                    'value'       => get_post_meta( $post->ID, 'part_number_key', true ),
                ) );

                // Afișăm câmpul emag_id pentru ID-ul eMAG
                woocommerce_wp_text_input( array(
                    'id'          => 'emag_id',
                    'label'       => __( 'eMAG Product ID', 'price-sync-for-emag' ),
                    'description' => __( 'Introduceți ID-ul produsului eMAG pentru acest produs.', 'price-sync-for-emag' ),
                    'desc_tip'    => 'true',
                    'value'       => get_post_meta( $post->ID, 'emag_id', true ),
                ) );
            ?>
        </div>
    </div>
    <?php
}

// Salvează valoarea câmpului part_number_key
add_action( 'woocommerce_process_product_meta', 'weps_save_emag_sync_custom_fields' );
function weps_save_emag_sync_custom_fields( $post_id ) {
    $part_number_key = isset( $_POST['part_number_key'] ) ? sanitize_text_field( $_POST['part_number_key'] ) : '';
    update_post_meta( $post_id, 'part_number_key', $part_number_key );

    $emag_id = isset( $_POST['emag_id'] ) ? sanitize_text_field( $_POST['emag_id'] ) : '';
    update_post_meta( $post_id, 'emag_id', $emag_id );
}


// Adăugăm scripturile necesare pentru select2 în pagina de setări avansate
add_action('admin_enqueue_scripts', 'weps_advanced_enqueue_admin_scripts');
function weps_advanced_enqueue_admin_scripts($hook) {
    if ($hook != 'settings_page_woocommerce_emag_price_sync_advanced') {
        return;
    }
    wp_enqueue_script('select2', plugins_url('/assets/js/select2.min.js', __FILE__), array('jquery'), '4.0.13', true);
wp_enqueue_style('select2-css', plugins_url('/assets/css/select2.min.css', __FILE__), array(), '4.0.13');

	
    wp_add_inline_style('select2-css', '
        select[multiple] {
            width: 100%;    /* Lățimea 100% */
            height: 300px;  /* Înălțimea 300px */
        }
		.wp-core-ui select[multiple] {
            width: 100%;    /* Lățimea 100% */
            height: 300px;  /* Înălțimea 300px */
        }
    ');
}
?>

<?php

/**
 * Plugin Name: My Plugin Delivery Shipping method
 * Description: Custom shipping method plugin for WooCommerce
 * inspiration:
 * url: https://stackoverflow.com/questions/45177226/how-to-add-a-custom-working-shipping-method-in-woocommerce-3
 * http://code.tutsplus.com/tutorials/create-a-custom-shipping-method-for-woocommerce--cms-26098
 * Author: Paweł Kalisz
 */
if (!defined('WPINC')) {
    die;
}

/*
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    //tworzenie callbacka z klasa do tworzenia metod wysylkowych , callback dodoany do akcji woocommerce_shipping_init
    function create_my_custom_shipping_method()
    {
        if (!class_exists('Custom_Shipping_Method')) {
            //WC_Shipping_Method -  klasa wbudowana w wc
            class Custom_Shipping_Method extends WC_Shipping_Method
            {
                /**
                 * Constructor.
                 *
                 * @param int $instance_id
                 */
                public function __construct($instance_id = 0)
                {
                    $this->id = 'custom_shipping_id';
                    $this->instance_id = absint($instance_id);
                    $this->method_title = __("my method title", 'textdomain');

                    $this->supports = array(//support dla wysyylek WC
                        'shipping-zones',//mzoliwosc selekcji metody
                        'instance-settings',
                        'instance-settings-modal',//pojawienei sie formaularza przy okinku modalnym
                    );

                    $this->init();//init formularza i ustawień
                    //ustawinia domyslne dla opcji formularza
                    $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('TutsPlus Shipping', 'tutsplus');

                }

                /**
                 * Initialize custom shiping method.
                 */
                public function init()
                {

                    // Load the settings.
                    $this->init_form_fields();
                    $this->init_settings();

                    // Define user set variables
                    //$this->title = $this->get_option('title');

                    // Save settings in admin if you have any defined
                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                }

                /**
                 * calculate_shipping function.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                //kalkulator  objawia sie przy samym koszyku
                public function calculate_shipping($package = array())
                {
                    $rate = array(
                        'label' => $this->title,//tytyl metody na labelce przy koszyku
                        'cost' => '10.99',//pokazuje koszt wysyslki
                        'calc_tax' => 'per_item'//njprwd sposob oblicznia na kazdy prdod ososbno
                    );

                    // Register the rate, reejestracja kosztów wysyłki
                    $this->add_rate($rate);
                }

                /**
                 * Init form fields.
                 */
                public function init_form_fields()
                {
                    $this->instance_form_fields = array(

                        'enabled' => array(
                            'title' => __('Enable', 'textdomain'),
                            'type' => 'checkbox',
                            'description' => __('Enable this shipping.', 'textdomain'),
                            'default' => 'yes'
                        ),

                        'title' => array(
                            'title' => __('Title', 'textdomain'),
                            'type' => 'text',
                            'description' => __('Title to be display on site', 'textdomain'),
                            'default' => __('TutsPlus Shipping', 'textdomain')
                        ),

                        'weight' => array(
                            'title' => __('Weight (kg)', 'textdomain'),
                            'type' => 'number',
                            'description' => __('Maximum allowed weight', 'textdomain'),
                            'default' => 100
                        ),

                    );
                }
            }
        }
    }

    add_action('woocommerce_shipping_init', 'create_my_custom_shipping_method');
     //ostatteczne dodanie nowej metody wysyłkowej
    function add_my_custom_shipping_method($methods)
    {
        $methods['custom_shipping_id'] = 'Custom_Shipping_Method';

        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'add_my_custom_shipping_method');
}
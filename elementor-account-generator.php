<?php
/**
 * Plugin Name: Elementor Account Generator
 * Description: A plugin that creates an account through a webhook when a button is clicked in an Elementor widget.
 * Version: 1.0
 * Author: Rohit Kumar
 * Author URI: https://iamrohit.net/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Register the Elementor widget
add_action( 'elementor/widgets/widgets_registered', function() {
    class Account_Generator_Widget extends \Elementor\Widget_Base {

        public function get_name() {
            return 'account_generator';
        }

        public function get_title() {
            return __( 'Account Generator', 'elementor' );
        }

        public function get_script_depends() {
            return [ 'elementor-account-generator' ];
        }

        private function user_has_email() {
            $user_id = get_current_user_id();
            return get_user_meta( $user_id, 'Insightkade_Email', true );
        }

        public function get_content() {
            $user_id = get_current_user_id();
            $email = $this->user_has_email();
            $has_accessed = get_user_meta( $user_id, 'Insightkade_Accessed', true );

            if ( $email ) {
                $html = '<div class="login-box">';
                $html .= '<h2>Your InsightKade Credentials:</h2>';
                $html .= '<label for="emailField">Email:</label>';
                $html .= '<input type="text" id="emailField" value="' . esc_attr( $email ) . '" readonly>';
                if ( ! $has_accessed ) {
                    $html .= '<label id="passwordLabel" for="passwordField">Password:</label>';
                    $html .= '<input type="password" id="passwordField" value="serverkade" readonly>';
                }
                $html .= '<div class="click-to-copy">Click to Copy</div>';
                $html .= '<button id="accessNowBtn">Access Now</button>';
                $html .= '<div id="loginLinkMsg" style="display: none; color: red; font-weight: bold;">Reset link sent to your email</div>';
                $html .= '</div>';
                return $html;
            }
            return '<button id="generateAccountBtn">Generate Your Account</button><div id="waitMsg" style="display: none; color: red; font-weight: bold;">Please wait while we create your account...</div><div id="errorMsg" style="color: red; font-weight: bold; display:none;"></div>';
        }

        public function render() {
            echo '<div id="accountGeneratorWidget">' . $this->get_content() . '</div>';
        }
    }
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Account_Generator_Widget() );
} );

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'elementor-account-generator-style', plugin_dir_url( __FILE__ ) . 'account-generator.css' );
    wp_enqueue_script( 'elementor-account-generator', plugin_dir_url( __FILE__ ) . 'account-generator.js', array('jquery'), '1.0.0', true );
    wp_localize_script( 'elementor-account-generator', 'account_generator_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
} );

// Handle the AJAX request
add_action( 'wp_ajax_generate_account', 'generate_account_callback' );
add_action( 'wp_ajax_nopriv_generate_account', 'generate_account_callback' );

function generate_account_callback() {
    $user_id = get_current_user_id();
    
    $response = wp_remote_post( '<api>', array(
        'method'      => 'POST',
        'body'        => json_encode( array( 'user_id' => $user_id ) ),
        'headers'     => array( 'Content-Type' => 'application/json' ),
        'timeout'     => 15,
    ) );

    // Log the raw response for debugging
    if ( is_wp_error( $response ) ) {
        error_log( 'Webhook request failed: ' . $response->get_error_message() );
        wp_send_json_error( 'Failed to connect to the webhook.' );
    }

    $body = wp_remote_retrieve_body( $response );

    // Log the webhook response body
    error_log( 'Webhook response body: ' . $body );

    // Check if the response contains the success message
    if ( strpos( $body, 'User created successfully' ) !== false ) {
        $user = wp_get_current_user();
        update_user_meta( $user_id, 'Insightkade_Email', $user->user_email );
        update_user_meta( $user_id, 'Insightkade_Accessed', false ); // Set accessed to false initially
        wp_send_json_success( array( 'email' => $user->user_email ) );
    } else {
        wp_send_json_error( 'Failed to create user.' );
    }
}

// Handle marking as accessed
add_action( 'wp_ajax_mark_accessed', function() {
    $user_id = get_current_user_id();
    update_user_meta( $user_id, 'Insightkade_Accessed', true );
    wp_send_json_success();
} );

// Register shortcode
add_shortcode('account_generator', 'account_generator_shortcode');

function account_generator_shortcode() {
    ob_start();

    $user_id = get_current_user_id();
    $email = get_user_meta( $user_id, 'Insightkade_Email', true );
    $has_accessed = get_user_meta( $user_id, 'Insightkade_Accessed', true );
    
    if ( $email ) {
        echo '<div class="login-box">';
        echo '<h2>Your InsightKade Credentials:</h2>';
        echo '<label for="emailField">Email:</label>';
        echo '<input type="text" id="emailField" value="' . esc_attr( $email ) . '" readonly>';
        if ( ! $has_accessed ) {
            echo '<label id="passwordLabel" for="passwordField">Password:</label>';
            echo '<input type="password" id="passwordField" value="serverkade" readonly>';
        }
        echo '<div class="click-to-copy">Click to Copy</div>';
        echo '<button id="accessNowBtn">Access Now</button>';
        echo '</div>';
    } else {
        echo '<button id="generateAccountBtn">Generate Your Account</button>';
        echo '<div id="waitMsg" style="display: none; color: red; font-weight: bold;">Please wait while we create your account...</div>';
        echo '<div id="errorMsg" style="color: red; font-weight: bold; display:none;"></div>';
    }

    return ob_get_clean();
}
?>
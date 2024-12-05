<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('EasyAppSetting')) {
    class EasyAppSetting {
		
		private $ew_testing_page;

        public function __construct() {
            add_action('init', array($this, 'register_settings'));
            add_action('admin_menu', array($this, 'register_menu_page'));
    		add_action('admin_menu', array($this, 'rename_default_submenu'), 999);

        }

        public function register_settings() { 
            //here is simple example of register settings
            // register_setting('easy-whatsapp-plugin-status', 'plugin_status');

            // General settings
            $settings = [
                'cloud_messaging_api_server_key',
				'android_channel_id',
				'order_cancel_status_notification',
                'order_received_status_notification',
                'order_shipped_status_notification',
                'order_delivered_status_notification',
				'test_fcm_token', // FCM - Firebase Cloud Messaging 
            ];

            foreach ($settings as $setting) {
                register_setting('easy-app-settings-group', $setting);
            }
        }

        public function register_menu_page() {
            // Menu name -> EasyApp, url -> easy-app-main, callback function ->  dashboard_page, icon -> 'dashicons-smartphone', priority -> 7    
	add_menu_page('EasyApp Dashboard', 'EasyApp', 'manage_options', 'easy-app-main', array($this, 'easyapp_settings'), 'dashicons-smartphone', 7 );
	add_submenu_page('easy-app-main','Notification Settings','Notification Settings','manage_options','ea-notification-settings', array($this, 'notification_settings') );
			// Hook to change the default submenu
        }
		
		public function rename_default_submenu() {
			global $submenu;

			// Check if the EasyApp Dashboard menu exists
			if (isset($submenu['easy-app-main'])) { // Replace with the slug of your menu
				foreach ($submenu['easy-app-main'] as &$item) {
					// Rename the desired submenu item
					if ($item[2] === 'easy-app-main') { // Replace with the current submenu title
						$item[0] = 'EasyApp Settings'; // Replace with your new title
						break;
					}
				}
			}
		}


		
        //Page design start from here
        public function notification_settings() {
            ?>
            <div class="wrap">
                <h1>Easy App Notification Settings</h1>

                <!-- form for setting -->
                <form method="post" action="options.php">
                    <?php settings_fields('easy-app-settings-group'); ?>
                    <?php do_settings_sections('easy-app-settings-group'); ?>
                    <table class="form-table">	
						
						<!-- User access token -->
						<tr valign="top">
							<th scope="row">Cloud messaging API server key</th>
							<td><input type="text" name="cloud_messaging_api_server_key" placeholder="AAAxxxxx" value="<?php echo esc_attr(get_option('cloud_messaging_api_server_key')); ?>" />
								<a href="https://www.google.com" target="_blank">Click here get server key</a>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Android channel id</th>
							<td><input type="text" name="android_channel_id" placeholder="puxxxxxxx" value="<?php echo esc_attr(get_option('android_channel_id')); ?>" />
							</td>
						</tr>
						
						<?php
						// order status and templat
                        $statuses = [
                            'order_received' => 'Select order received status',
                            'order_shipped' => 'Select order shipped status',
                            'order_delivered' => 'Select order delivered status',
							'order_cancel' => 'Select order cancel status',
                        ];

                        foreach ($statuses as $status => $label) {
                            echo '<tr valign="top">
                                    <th scope="row">' . esc_html($label) . '</th>
                                    <td>
                                        <select name="' . esc_attr($status) . '_status_notification">
                                            <option value="">-- Select Status --</option>';
                                            if (function_exists('wc_get_order_statuses')) {
                                                $order_statuses = wc_get_order_statuses();
                                                $selected_status = get_option($status . '_status_notification');
                                                foreach ($order_statuses as $order_status => $status_label) {
                                                    $selected = selected($order_status, $selected_status, false);
                                                    echo '<option value="' . esc_attr($order_status) . '" ' . $selected . '>' . esc_html($status_label) . '</option>';
                                                }
                                            } else {
                                                echo '<option value="" disabled>WooCommerce not active</option>';
                                            }
                            echo ' </select>
                                    </td>
                                  </tr>';
                        }
						?>
						<tr valign="top">
							<th scope="row">Test FCM Token</th>
							<td><input type="text" name="test_fcm_token" placeholder="dbn7khYETfiHExxxx" value="<?php echo esc_attr(get_option('test_fcm_token')); ?>" />
							</td>
						</tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>

			<?php 
			if (isset($_POST['ea_test_notification']))  {
				$fcm_token = esc_attr(get_option('test_fcm_token'));
				if($fcm_token){
					$response = (new EeasyAppNotification())->sendTestNotification();
					if($response['success']){
						echo '<div class="notice notice-success is-dismissible"><p>'.$response['message'].'</p></div>';
					} else {
						echo '<div class="notice notice-error is-dismissible"><p>'. $response['message'] .'</p></div>';
					}
				}else {
					echo '<div class="notice notice-error is-dismissible"><p>Test FCM Token is empty</p></div>';
				}
			}
			?>
	<form method="post" action="">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Test Notification</th>
				<td><input type="submit" name="ea_test_notification" class="button-primary" value="Test Notification"></td>
			</tr>
		</table>
	</form>
            <?php
        }
		
		
		public function easyapp_settings() {
		?>
			<div class="wrap">
				<h1>EasyApp Settings</h1>
			</div>
		<?php
		}


		
    }
}


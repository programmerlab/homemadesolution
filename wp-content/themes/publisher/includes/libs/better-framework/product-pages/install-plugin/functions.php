<?php
/***
 *  BetterFramework is BetterStudio framework for themes and plugins.
 *
 *  ______      _   _             ______                                           _
 *  | ___ \    | | | |            |  ___|                                         | |
 *  | |_/ / ___| |_| |_ ___ _ __  | |_ _ __ __ _ _ __ ___   _____      _____  _ __| | __
 *  | ___ \/ _ \ __| __/ _ \ '__| |  _| '__/ _` | '_ ` _ \ / _ \ \ /\ / / _ \| '__| |/ /
 *  | |_/ /  __/ |_| ||  __/ |    | | | | | (_| | | | | | |  __/\ V  V / (_) | |  |   <
 *  \____/ \___|\__|\__\___|_|    \_| |_|  \__,_|_| |_| |_|\___| \_/\_/ \___/|_|  |_|\_\
 *
 *  Copyright © 2017 Better Studio
 *
 *
 *  Our portfolio is here: http://themeforest.net/user/Better-Studio/portfolio
 *
 *  \--> BetterStudio, 2017 <--/
 */


/**
 * enqueue static files.
 */

function bf_install_plugins_enqueue_scripts() {

	if ( bf_is_product_page( 'install-plugin' ) ) {

		bf_enqueue_script( 'bf-modal' );
		bf_enqueue_style( 'bf-modal' );

		$ver = BF_Product_Pages::Run()->get_version();

		wp_enqueue_style(
			'bs-product-plugin-styles',
			BF_Product_Pages::get_url( 'install-plugin/assets/css/bs-plugin-install.css' ),
			array(),
			$ver
		);

		wp_enqueue_script(
			'bs-product-plugin-scripts',
			BF_Product_Pages::get_url( 'install-plugin/assets/js/bs-plugin-install.js' ),
			array(),
			$ver
		);

		wp_localize_script( 'bs-product-plugin-scripts', 'bs_plugin_install_loc', array(
			'on_error'  => array(
				'button_ok'       => __( 'Ok', 'publisher' ),
				'default_message' => __( 'Cannot install plugin.', 'publisher' ),
				'body'            => __( 'please try again several minutes later or contact better studio team support.', 'publisher' ),
				'header'          => __( 'plugin installation failed', 'publisher' ),
				'rollback_error'  => __( 'unable to remove temporary plugin files', 'publisher' ),
				'title'           => __( 'an error occurred while installing plugin', 'publisher' ),
				'display_error'   => __( '<div class="bs-pages-error-section">
					<a href="#" class="btn bs-pages-error-copy" data-copied="' . esc_attr__( 'Copied !', 'publisher' ) . '">
						<i class="fa fa-files-o" aria-hidden="true"></i> Copy</a>  <textarea> Error:  %ERROR_CODE% %ERROR_MSG% </textarea>
				</div>', 'publisher' ),
			),
			'menu_slug' => BF_PRODUCT_PAGES_MAIN_MENU
		) );
	}

}

add_action( 'admin_enqueue_scripts', 'bf_install_plugins_enqueue_scripts' );

/**
 * get plugins config array
 *
 * @return array  {
 *
 *  Plugin_ID => array {
 *
 * @type string  $name        plugin name
 * @type string  $slug        plugin slug( plugin directory )
 * @type boolean $required    is plugin required?
 * @type string  $version     plugin version number
 * @type string  $description plugin description
 * @type string  $thumbnail   plugin image  URI
 * @type string  $local_path  path to zip file if plugin not exists in wordpress.org plugin repository
 *
 * }
 *
 * ...
 * }
 */
function bf_get_plugins_config() {

	$result = array();

	foreach ( apply_filters( 'better-framework/product-pages/install-plugin/config', array() ) as $id => $plugin ) {
		if ( ! isset( $plugin['type'] ) ) {
			$plugin['type'] = empty( $plugin['local_path'] ) ? 'global' : 'local';
		}
		$result[ $id ] = $plugin;
	}

	return $result;
}

/**
 * Display notice if required plugins was not installed
 */
function bf_required_plugin_notice() {
	if ( $plugins = bf_get_plugins_config() ) {

		$add_notice            = FALSE;
		$last_required_plugins = get_option( 'bs-require-plugin-install' );
		$required_plugins      = array();

		if ( ! class_exists( 'BS_Product_Plugin_Factor' ) ) {
			require_once BF_Product_Pages::get_path( 'install-plugin/class-bf-product-plugin-installer.php' );
		}

		foreach ( $plugins as $plugin_ID => $plugin ) {

			if ( ! empty( $plugin['required'] ) && $plugin['required'] ) {

				if ( ! BF_Product_Plugin_Installer::is_plugin_installed( $plugin_ID )
				     ||
				     ! BF_Product_Plugin_Installer::is_plugin_active( $plugin_ID )
				) {
					$required_plugins[] = $plugin['name'];
				}
			}
		}

		if ( $last_required_plugins === FALSE ) {
			$add_notice = TRUE;
		} else {
			sort( $required_plugins );
			if ( $required_plugins != $last_required_plugins ) {
				$add_notice = TRUE;
			}
		}

		if ( ! $add_notice ) {
			return;
		}


		if ( empty( $required_plugins ) ) {
			delete_option( 'bs-require-plugin-install' );
			Better_Framework::admin_notices()->remove_notice( 'bs-product-required-plugins' );

		} else {

			update_option( 'bs-require-plugin-install', $required_plugins );

			$link = admin_url( 'admin.php?page=' . BF_Product_Pages::$menu_slug . '-install-plugin' );

			if ( count( $required_plugins ) > 1 ) {

				if ( count( $required_plugins ) === 2 ) {
					$msg = wp_kses( sprintf(
						__( 'The <strong>%s</strong> and <strong>%s</strong> plugins are required for %s to work properly. Install and activate them from <a href="%s">Plugin Installer</a>.', 'publisher' ),
						isset( $required_plugins['0'] ) ? $required_plugins['0'] : '',
						isset( $required_plugins['1'] ) ? $required_plugins['1'] : '',
						BF_Product_Pages::get_product_info( 'product_name', '' ),
						$link
					), bf_trans_allowed_html() );
				} else {
					$msg = wp_kses( sprintf( __( 'Some required plugins was not installed currently. Install and activate them from <a href="%s">Plugin Installer</a>.', 'publisher' ), $link ), bf_trans_allowed_html() );
				}

			} else {
				$msg = wp_kses( sprintf(
					__( 'The <strong>%s</strong> plugin is required for %s to work properly. Install and activate it from <a href="%s">Plugin Installer</a>.', 'publisher' ),
					isset( $required_plugins['0'] ) ? $required_plugins['0'] : '',
					BF_Product_Pages::get_product_info( 'product_name', '' ),
					$link
				), bf_trans_allowed_html() );
			}

			Better_Framework::admin_notices()->add_notice( array(
				'msg'       => $msg,
				'id'        => 'bs-product-required-plugins',
				'type'      => 'fixed',
				'state'     => 'danger',
				'thumbnail' => BF_Product_Pages::get_product_info( 'notice-icon', '' ),
				'product'   => 'theme:publisher',
			) );
		}

	}
}

add_action( 'in_admin_header', 'bf_required_plugin_notice', 17 );


function bf_update_plugin_schedule() {

	if ( ! class_exists( 'BF_Product_Plugin_Installer' ) ) {
		require_once BF_Product_Pages::get_path( 'install-plugin/class-bf-product-plugin-installer.php' );
	}

	if ( ! class_exists( 'BF_Product_Plugin_Manager' ) ) {
		require_once BF_Product_Pages::get_path( 'install-plugin/class-bf-product-plugin-manager.php' );
	}

	$obj    = new BF_Product_Plugin_Manager();
	$status = $obj->update_plugins( TRUE );
	if ( ! empty( $status->remote_plugins ) && is_array( $status->remote_plugins ) ) {
		$plugins_update = get_site_transient( 'update_plugins' );
		if ( empty( $plugins_update->response ) ) {
			$plugins_update->response = array();
		}

		$r = &$plugins_update->response;
		foreach ( $status->remote_plugins as $p_file => $plugin_data ) {
			$r[ $p_file ]          = (object) $plugin_data;
			$r[ $p_file ]->plugin  = $p_file;
			$r[ $p_file ]->package = 'FETCH_FROM_BETTER_STUDIO/' . $plugin_data['slug'];
		}

		set_site_transient( 'update_plugins', $plugins_update );
	}
}

add_action( 'wp_update_plugins', 'bf_update_plugin_schedule' );

/**
 *
 *
 * @param mixed $value
 *
 * @return mixed
 */
function bf_update_plugins_list( $value ) {

	if ( bf_is_doing_ajax( 'update-plugin' ) ) { // when updating plugin in plugins.php page
		if ( ! empty( $value->response ) && is_array( $value->response ) ) {

			if ( ! class_exists( 'BF_Product_Plugin_Installer' ) ) {
				require_once BF_Product_Pages::get_path( 'install-plugin/class-bf-product-plugin-installer.php' );
			}
			$installer = new BF_Product_Plugin_Installer();
			add_filter( 'http_request_args', 'bf_remove_reject_unsafe_urls', 99 );

			foreach ( $value->response as $p_file => $plugin_data ) {
				if ( isset( $plugin_data->package ) && preg_match( '/^FETCH_FROM_BETTER_STUDIO\/.+/i', $plugin_data->package ) ) {

					$action  = BF_Product_Plugin_Installer::is_plugin_installed( $plugin_data->slug ) ? 'update' : 'install';
					$dl_link = $installer->get_bundled_plugin_download_link( $plugin_data->slug, $action );

					if ( $dl_link && ! is_wp_error( $dl_link ) ) {
						$value->response[ $p_file ]->package = $dl_link;
					}
				}
			}
		}
	}

	return $value;
}

add_action( 'site_transient_update_plugins', 'bf_update_plugins_list' );

/**
 *
 * @param object $value
 *
 * @return object
 */
function bf_sync_plugin_update_information( $value ) {
	global $pagenow;

	// Make sure plugin update data is valid
	// we expect wordpress check plugin updates in plugin and updates page
	if ( ! in_array( $pagenow, array(
			'plugins.php',
			'update-core.php',
			'update.php'
		) ) && ! bf_is_doing_ajax( 'update-plugin' )
	) {
		return $value;
	}

	$current_plugin_stat = get_option( 'bs-product-plugins-status' );
	$update_plugin_stat  = FALSE;

	//
	// Remove Updated Plugin From our List
	//
	if ( ! empty( $current_plugin_stat->remote_plugins ) ) {

		$plugins       = empty( $value->response ) ? array() : $value->response;
		$maybe_updated = array_diff_key( $current_plugin_stat->remote_plugins, $plugins );

		$plugin_dir = trailingslashit( WP_PLUGIN_DIR );
		foreach ( $maybe_updated as $plugin_path => $plugin_new_ver ) {

			$plugin_installed_data = get_plugin_data( $plugin_dir . $plugin_path );

			// check if plugin was really updated
			if ( version_compare( $plugin_installed_data['Version'], $plugin_new_ver['new_version'], '=' ) ) {
				unset( $current_plugin_stat->remote_plugins[ $plugin_path ] );
				$update_plugin_stat = TRUE;
			}
		}
	}

	if ( $update_plugin_stat ) {
		update_option( 'bs-product-plugins-status', $current_plugin_stat );
	}

	return $value;
}

add_filter( 'set_site_transient_update_plugins', 'bf_sync_plugin_update_information' );


if ( ! function_exists( 'bf_remove_reject_unsafe_urls' ) ) {
	function bf_remove_reject_unsafe_urls( $args ) {
		$args['reject_unsafe_urls'] = FALSE;

		return $args;
	}
}

function bf_update_plugin_bulk( $bool, $package ) {
	if ( preg_match( '/^FETCH_FROM_BETTER_STUDIO\/(.+)/i', $package, $match ) ) {
		$plugin_slug = &$match[1];

		if ( ! class_exists( 'BF_Product_Plugin_Installer' ) ) {
			require_once BF_Product_Pages::get_path( 'install-plugin/class-bf-product-plugin-installer.php' );
		}

		$installer = new BF_Product_Plugin_Installer();
		$action    = BF_Product_Plugin_Installer::is_plugin_installed( $plugin_slug ) ? 'update' : 'install';
		$url       = $installer->get_bundled_plugin_download_link( $plugin_slug, $action );
		if ( $url && ! is_wp_error( $url ) ) {

			try {
				$result = $installer->download_package( $url, $plugin_slug );

				return $result;
			} catch( Exception $e ) {
				return FALSE;
			}
		}
	}

	return $bool;
}

add_filter( 'upgrader_pre_download', 'bf_update_plugin_bulk', 999, 2 );


/**
 * callback: remove visual composer register admin notice
 * action  : vc_after_mapping
 */
function bf_remove_vc_register_notice() {
	global $_bs_vc_access_changes;

	if ( function_exists( 'vc_user_access' ) ) {
		$instance = vc_user_access();
		if ( is_callable( array( $instance, 'setValidAccess' ) ) ) {
			$instance->setValidAccess( FALSE );
		}
		if ( is_callable( array( $instance, 'getValidAccess' ) ) ) {
			$_bs_vc_access_changes = vc_user_access()->getValidAccess();
		}
	}
}

add_action( 'vc_after_mapping', 'bf_remove_vc_register_notice' );

/**
 * callback: Hide register revolution slider admin notice
 *
 * action  : better-framework/product-pages/install-plugin/install-finished,
 *           better-framework/product-pages/install-plugin/active-finished
 *
 * @param string|array $data
 *
 * @return bool
 */
function bf_remove_revslider_register_notice( $data ) {
	if ( is_string( $data ) ) {
		$slug = $data;
	} else if ( isset( $data['slug'] ) ) {
		$slug = $data['slug'];
	} else {
		return FALSE;
	}

	if ( $slug === 'revslider' ) {
		remove_filter( 'pre_update_option_revslider-valid-notice', '__return_false', PHP_INT_MAX );
		update_option( 'revslider-valid-notice', 'false' );
	}

	return TRUE;
}

/**
 * Hide revolution slider registration notice
 */
function bf_hide_revslider_register_notice() {
	add_option( 'revslider-valid-notice', 'false' );
}

add_action( 'better-framework/product-pages/install-plugin/install-finished', 'bf_remove_revslider_register_notice' );
add_action( 'better-framework/product-pages/install-plugin/active-finished', 'bf_remove_revslider_register_notice' );
add_action( 'delete_option_revslider-valid-notice', 'bf_hide_revslider_register_notice', PHP_INT_MAX );
add_filter( 'pre_update_option_revslider-valid-notice', '__return_false', PHP_INT_MAX );

/**
 * callback: undo changes on visual composer user access
 * action  : vc_after_init
 */
function bf_undo_vc_access_changes() {
	global $_bs_vc_access_changes;

	if ( ! is_null( $_bs_vc_access_changes ) ) {
		vc_user_access()->setValidAccess( $_bs_vc_access_changes );
		unset( $_bs_vc_access_changes );
	}
}

add_action( 'vc_after_init', 'bf_undo_vc_access_changes' );

function bf_force_check_plugins_update() {
	BF_Product_Pages::Run()->plugins_menu_instance()->update_plugins( TRUE );
}

add_action( 'load-update.php', 'bf_force_check_plugins_update' );
add_action( 'load-update-core.php', 'bf_force_check_plugins_update' );


function bf_append_plugins_update_badge( $menu ) {
	if ( empty( $menu['parent'] ) || $menu['id'] === BF_Product_Pages::$menu_slug . '-install-plugin' ) {
		if ( $update_status = get_option( 'bs-product-plugins-status' ) ) {
			$activated_plugins_updates = 0;

			if ( ! empty( $update_status->remote_plugins ) && is_array( $update_status->remote_plugins ) ) {

				/**
				 * Just display number of updates for activated plugins
				 * @see BF_Product_Plugin_Manager::render_content
				 */
				if ( ! class_exists( 'BS_Product_Plugin_Factor' ) ) {
					require_once BF_Product_Pages::get_path( 'install-plugin/class-bf-product-plugin-installer.php' );
				}
				foreach ( $update_status->remote_plugins as $plugin ) {
					if ( isset( $plugin['slug'] ) &&
					     BF_Product_Plugin_Installer::is_plugin_installed( $plugin['slug'] ) &&
					     BF_Product_Plugin_Installer::is_plugin_active( $plugin['slug'] )
					) {
						$activated_plugins_updates ++;
					}
				}
				if ( $activated_plugins_updates ) {
					$menu_title_index = $menu['parent'] ? 'menu_title' : 'parent_title';

					$menu[ $menu_title_index ] .= ' &nbsp;<span class="bs-admin-menu-badge"><span class="plugin-count">'
					                              . number_format_i18n( $activated_plugins_updates ) .
					                              '</span></span>';
				}
			}

			if ( ! isset( $update_status->number ) || $update_status->number !== $activated_plugins_updates ) {
				$update_status->number = $activated_plugins_updates;
				update_option( 'bs-product-plugins-status', $update_status );
			}
		}
	}

	return $menu;
}

add_filter( 'better-framework/product-pages/register-menu/params', 'bf_append_plugins_update_badge' );
<?php
//* Fetch the required instance within this file.
$instance = $this->get_view_instance( 'the_seo_framework_sitemaps_metabox', $instance );

switch ( $instance ) :
	case 'the_seo_framework_sitemaps_metabox_main' :

		if ( ! $this->pretty_permalinks ) {

			$permalink_settings_url = esc_url( admin_url( 'options-permalink.php' ) );
			$here = '<a href="' . $permalink_settings_url  . '" target="_blank" title="' . __( 'Permalink Settings', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

			?><h4><?php _e( "You're using the plain permalink structure.", 'autodescription' ); ?></h4><?php
			$this->description( __( "This means we can't output the sitemap through the WordPress rewrite rules.", 'autodescription' ) );
			?><hr><?php
			$this->description_noesc( sprintf( _x( "Change your Permalink Settings %s (Recommended: 'postname').", '%s = here', 'autodescription' ), $here ) );

		} else {

			/**
			 * Parse tabs content
			 *
			 * @param array $default_tabs { 'id' = The identifier =>
			 *			array(
			 *				'name' 		=> The name
			 *				'callback' 	=> The callback function, use array for method calling (accepts $this, but isn't used here for optimization purposes)
			 *				'dashicon'	=> Desired dashicon
			 *			)
			 * }
			 *
			 * @since 2.2.9
			 */
			$default_tabs = array(
				'general' => array(
					'name' 		=> __( 'General', 'autodescription' ),
					'callback'	=> array( $this, 'sitemaps_metabox_general_tab' ),
					'dashicon'	=> 'admin-generic',
				),
				'robots' => array(
					'name'		=> 'Robots.txt',
					'callback'	=> array( $this, 'sitemaps_metabox_robots_tab' ),
					'dashicon'	=> 'share-alt2',
				),
				'timestamps' => array(
					'name'		=> __( 'Timestamps', 'autodescription' ),
					'callback'	=> array( $this, 'sitemaps_metabox_timestamps_tab' ),
					'dashicon'	=> 'backup',
				),
				'notify' => array(
					'name'		=> _x( 'Ping', 'Ping or notify Search Engine', 'autodescription' ),
					'callback'	=> array( $this, 'sitemaps_metabox_notify_tab' ),
					'dashicon'	=> 'megaphone',
				),
			);

			/**
			 * Applies filters the_seo_framework_sitemaps_settings_tabs : array see $default_tabs
			 *
			 * Used to extend Knowledge Graph tabs
			 */
			$defaults = (array) apply_filters( 'the_seo_framework_sitemaps_settings_tabs', $default_tabs, $args );

			$tabs = wp_parse_args( $args, $defaults );
			$use_tabs = true;

			$sitemap_plugin = $this->detect_sitemap_plugin();
			$sitemap_detected = $this->has_sitemap_xml();
			$robots_detected = $this->has_robots_txt();

			/**
			 * Remove the timestamps and notify submenus
			 * @since 2.5.2
			 */
			if ( $sitemap_plugin || $sitemap_detected ) {
				unset( $tabs['timestamps'] );
				unset( $tabs['notify'] );
			}

			/**
			 * Remove the robots submenu
			 * @since 2.5.2
			 */
			if ( $robots_detected ) {
				unset( $tabs['robots'] );
			}

			if ( $robots_detected && ( $sitemap_plugin || $sitemap_detected ) )
				$use_tabs = false;

			$this->nav_tab_wrapper( 'sitemaps', $tabs, '2.2.8', $use_tabs );

		}

	break;
	case 'the_seo_framework_sitemaps_metabox_general' :

		$site_url = $this->the_home_url_from_cache( true );

		$sitemap_url = $site_url . 'sitemap.xml';
		$has_sitemap_plugin = $this->detect_sitemap_plugin();
		$sitemap_detected = $this->has_sitemap_xml();

		?><h4><?php _e( 'Sitemap Integration Settings', 'autodescription' ); ?></h4><?php

		if ( $has_sitemap_plugin ) {
			$this->description( __( "Another active sitemap plugin has been detected. This means that the sitemap functionality has been replaced.", 'autodescription' ) );
		} else if ( $sitemap_detected ) {
			$this->description( __( "A sitemap has been detected in the root folder of your website. This means that the sitemap functionality has no effect.", 'autodescription' ) );
		} else {
			$this->description( __( "The Sitemap is an XML file that lists pages and posts for your website along with optional metadata about each post or page. This helps Search Engines crawl your website more easily.", 'autodescription' ) );
			$this->description( __( "The optional metadata include the post and page modified time and a page priority indication, which is automated.", 'autodescription' ) );

			?>
			<hr>

			<h4><?php _e( 'Sitemap Output', 'autodescription' ); ?></h4>
			<?php

			//* Echo checkbox.
			$this->wrap_fields(
				$this->make_checkbox(
					'sitemaps_output',
					__( 'Output Sitemap?', 'autodescription' ),
					''
				), true
			);
		}

		if ( ! ( $has_sitemap_plugin || $sitemap_detected ) && $this->get_option( 'sitemaps_output' ) ) {
			$here = '<a href="' . $sitemap_url  . '" target="_blank" title="' . __( 'View sitemap', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';
			$this->description_noesc( sprintf( _x( 'The sitemap can be found %s.', '%s = here', 'autodescription' ), $here ) );
		}

	break;
	case 'the_seo_framework_sitemaps_metabox_robots' :

		$site_url = $this->the_home_url_from_cache( true );
		$robots_url = trailingslashit( $site_url ) . 'robots.txt';
		$here =  '<a href="' . $robots_url  . '" target="_blank" title="' . __( 'View robots.txt', 'autodescription' ) . '">' . _x( 'here', 'The sitemap can be found %s.', 'autodescription' ) . '</a>';

		?><h4><?php _e( 'Robots.txt Settings', 'autodescription' ); ?></h4><?php

		if ( $this->can_do_sitemap_robots() ) :
			$this->description( __( 'The robots.txt file is the first thing Search Engines look for. If you add the sitemap location in the robots.txt file, then Search Engines will look for and index the sitemap.', 'autodescription' ) );
			$this->description( __( 'If you do not add the sitemap location to the robots.txt file, you will need to notify Search Engines manually through the Webmaster Console provided by the Search Engines.', 'autodescription' ) );

			?>
			<hr>

			<h4><?php _e( 'Add sitemap location in robots.txt', 'autodescription' ); ?></h4>
			<?php

			//* Echo checkbox.
			$this->wrap_fields(
				$this->make_checkbox(
					'sitemaps_robots',
					__( 'Add sitemap location in robots?', 'autodescription' ),
					''
				), true
			);
		else :
			$this->description( __( 'Another robots.txt sitemap Location addition has been detected.', 'autodescription' ) );
		endif;

		$this->description_noesc( sprintf( _x( 'The robots.txt file can be found %s.', '%s = here', 'autodescription' ), $here ) );

	break;
	case 'the_seo_framework_sitemaps_metabox_timestamps' :

		//* Sets timezone according to WordPress settings.
		$this->set_timezone();

		$timestamp_0 = date( 'Y-m-d' );

		/**
		 * @link https://www.w3.org/TR/NOTE-datetime
		 * We use the second expression of the time zone offset handling.
		 */
		$timestamp_1 = date( 'Y-m-d\TH:iP' );

		//* Reset timezone to previous value.
		$this->reset_timezone();

		?><h4><?php _e( 'Timestamps Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( 'The modified time suggests to Search Engines where to look for content changes. It has no impact on the SEO value unless you drastically change pages or posts. It then depends on how well your content is constructed.', 'autodescription'  ) );
		$this->description( __( "By default, the sitemap only outputs the modified date if you've enabled them within the Social Metabox. This setting overrides those settings for the Sitemap.", 'autodescription' ) );

		?>
		<hr>

		<h4><?php _e( 'Output Modified Date', 'autodescription' ); ?></h4>
		<?php

		//* Echo checkbox.
		$this->wrap_fields(
			$this->make_checkbox(
				'sitemaps_modified',
				sprintf( __( 'Add %s to the sitemap?', 'autodescription' ), $this->code_wrap( '<lastmod>' ) ),
				''
			), true
		);

		?>
		<hr>

		<fieldset>
			<legend><h4><?php _e( 'Timestamp Format Settings', 'autodescription' ); ?></h4></legend>
			<?php $this->description( __( 'Determines how specific the modification timestamp is.', 'autodescription' ) ); ?>

			<p id="sitemaps-timestamp-format" class="theseoframework-fields">
				<span class="toblock">
					<input type="radio" name="<?php $this->field_name( 'sitemap_timestamps' ); ?>" id="<?php $this->field_id( 'sitemap_timestamps_0' ); ?>" value="0" <?php checked( $this->get_field_value( 'sitemap_timestamps' ), '0' ); ?> />
					<label for="<?php $this->field_id( 'sitemap_timestamps_0' ); ?>">
						<span title="<?php _e( 'Complete date', 'autodescription' ); ?>"><?php echo $this->code_wrap( $timestamp_0 ) ?> [?]</span>
					</label>
				</span>
				<span class="toblock">
					<input type="radio" name="<?php $this->field_name( 'sitemap_timestamps' ); ?>" id="<?php $this->field_id( 'sitemap_timestamps_1' ); ?>" value="1" <?php checked( $this->get_field_value( 'sitemap_timestamps' ), '1' ); ?> />
					<label for="<?php $this->field_id( 'sitemap_timestamps_1' ); ?>">
						<span title="<?php _e( 'Complete date plus hours, minutes and timezone', 'autodescription' ); ?>"><?php echo $this->code_wrap( $timestamp_1 ); ?> [?]</span>
					</label>
				</span>
			</p>
		</fieldset>
		<?php

	break;
	case 'the_seo_framework_sitemaps_metabox_notify' :

		?><h4><?php _e( 'Ping Settings', 'autodescription' ); ?></h4><?php
		$this->description( __( "Notifying Search Engines of a sitemap change is helpful to get your content indexed as soon as possible.", 'autodescription' ) );
		$this->description( __( "By default this will happen at most once an hour.", 'autodescription' ) );

		?>
		<hr>

		<h4><?php _e( 'Notify Search Engines', 'autodescription' ); ?></h4>
		<?php

		$engines = array(
			'ping_google'	=> 'Google',
			'ping_bing' 	=> 'Bing',
			'ping_yandex'	=> 'Yandex'
		);

		$ping_checkbox = '';

		foreach ( $engines as $option => $engine ) {
			$ping_label = sprintf( __( 'Notify %s about sitemap changes?', 'autodescription' ), $engine );
			$ping_checkbox .= $this->make_checkbox( $option, $ping_label, '' );
		}

		//* Echo checkbox.
		$this->wrap_fields( $ping_checkbox, true );

	break;
	default :
	break;
endswitch;
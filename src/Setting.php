<?php
namespace WordPress_Modern_Images;

/**
 * Class representing the settings.
 *
 * @since 1.0.0
 */
class Setting {

	/**
	 * The setting slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_NAME = 'wordpress-modern-images-setting';
	const SLUG = 'wordpress-modern-images';
	/**
	 * Registers the setting with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		add_action(
			'admin_init',
			function() {
				register_setting(
					'media',
					self::OPTION_NAME
				);
				add_settings_section(
					'modernimageformats',
					__( 'Modern image output format', 'wordpress-modern-images' ),
					$this->get_sanitize_callback(),
					'media'
				);
				$option       = $this->get();
				$sub_settings = $this->get_sub_settings();

				foreach ( $sub_settings as $sub_setting ) {
					if ( ! isset( $sub_setting['id'] ) ) {
						continue;
					}
					add_settings_field(
						$sub_setting['id'],
						$sub_setting['title'],
						function( $args ) use ( $sub_setting, $option ) {
							if ( isset( $option[ $sub_setting['id'] ] ) ) {
								$value = $option[ $sub_setting['id'] ];
							} elseif ( ! empty( $sub_setting['multiple'] ) ) {
								$value = array();
							} else {
								$value = '';
							}

							// If there are choices, render a select.
							if ( isset( $sub_setting['choices'] ) ) {
								if ( ! empty( $sub_setting['multiple'] ) ) {
									?>
									<fieldset
										<?php
										$this->id_attr( $sub_setting );
										$this->class_attr( $sub_setting );
										?>
									>
										<legend class="screen-reader-text"><?php echo esc_html( $sub_setting['title'] ); ?></legend>
										<?php
										$first = true;
										foreach ( $sub_setting['choices'] as $option_value => $option_label ) {
											// Support both associative (value => label) and indexed arrays (item is both value and label).
											if ( is_int( $option_value ) ) {
												$option_value = $option_label;
											}
											if ( ! $first ) {
												echo '<br>';
											}
											$first = false;
											?>
											<label>
												<input
													type="checkbox"
													value="<?php echo esc_attr( $option_value ); ?>"
													<?php
													$this->name_attr( $sub_setting );
													$this->checked_attr( $value, $option_value );
													?>
												/>
												<span><?php echo esc_html( $option_label ); ?></span>
											</label>
											<?php
										}
										?>
									</fieldset>
									<?php
								} else {
									?>
									<select
										type="text"
										<?php
										$this->id_attr( $sub_setting );
										$this->name_attr( $sub_setting );
										$this->class_attr( $sub_setting );
										$this->aria_describedby_attr( $sub_setting );
										?>
									>
										<?php
										foreach ( $sub_setting['choices'] as $option_value => $option_label ) {
											// Support both associative (value => label) and indexed arrays (item is both value and label).
											if ( is_int( $option_value ) ) {
												$option_value = $option_label;
											}
											?>
											<option
												value="<?php echo esc_attr( $option_value ); ?>"
												<?php
												$this->selected_attr( $value, $option_value );
												?>
											>
												<?php echo esc_html( $option_label ); ?>
											</option>
											<?php
										}
										?>
									</select>
									<?php
								}
							} else { // Otherwise a simple text input.
								?>
								<input
									type="text"
									value="<?php echo esc_attr( $value ); ?>"
									<?php
									$this->id_attr( $sub_setting );
									$this->name_attr( $sub_setting );
									$this->class_attr( $sub_setting );
									$this->placeholder_attr( $sub_setting );
									$this->aria_describedby_attr( $sub_setting );
									?>
								/>
								<?php
							}

							if ( ! empty( $sub_setting['description'] ) ) {
								?>
								<p
									<?php
									$this->description_id_attr( $sub_setting );
									?>
									class="description"
								>
									<?php echo esc_html( $sub_setting['description'] ); ?>
								</p>
								<?php
							}
						},
						'media',
						$sub_setting['section'],
						! empty( $sub_setting['multiple'] ) ? array() : array( 'label_for' => Setting::OPTION_NAME . '-' . $sub_setting['id'] )
					);
				}
			}
		);
	}

	/**
	 * Gets the features list from the option.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of $policy_name => $policy_origins pairs.
	 */
	public function get() {
		return array_filter( (array) get_option( self::OPTION_NAME ) );
	}

	/**
	 * Gets sub settings that the setting should contain.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Added settings for placing existing tags.
	 * @since 1.3.0 Added settings for providing mock response data for AdSense and Analytics API calls.
	 * @since 1.4.0 Added settings for controlling feature flags.
	 *
	 * @return array List of associative setting definition arrays.
	 */
	public function get_sub_settings() {
		$mime_types = get_allowed_mime_types();

		$allowed_image_mime_types = array_filter( $mime_types, function( $type ) {
			return 0 === strpos( $type, 'image' );
		} );

		$potential_types = array(
			"image/jpeg",
			"image/gif",
			"image/png",
			"image/bmp",
			"image/tiff",
			"image/webp",
			"image/avif",
			"image/jpegxl",
		);

		$image_options = array_intersect( $potential_types, $allowed_image_mime_types );

		$options = array_map( function( $type ) {
				$format = str_replace( 'image/', '', $type );
				$choices =array(
					'' => __( 'Use original format (default)', 'wordpress-modern-images' ),
					'image/webp' => __( 'WebP', 'wordpress-modern-images' ),
					'image/avif' => __( 'AVIF', 'wordpress-modern-images' ),
					'image/jpegxl' => __( 'JPEG XL', 'wordpress-modern-images' ),

				);
				return array(
					'id'          => sprintf( 'modern_image_output_format_for_%s', $format ),
					'title'       => sprintf( __( 'For %s images', 'wordpress-modern-images' ), lcfirst( $format ) ),
					'description' => sprintf( __( 'Uploaded %s images will be output in this format.', 'wordpress-modern-images' ), lcfirst( $format ) ),
					'section'     => 'modernimageformats',
					'choices'     => $choices,
				);
			}, $image_options );

		return $options;
	}

	/**
	 * Gets the sanitize callback for the setting.
	 *
	 * @since 1.0.0
	 *
	 * @return callable Sanitize callback.
	 */
	public function get_sanitize_callback() {
		return function( $value ) {

			$sub_settings = $this->get_sub_settings();

			if ( ! is_array( $value ) ) {
				$value = array();
			}

			foreach ( $sub_settings as $sub_setting ) {
				if ( ! isset( $sub_setting['id'] ) ){
					continue;
				}
				if ( ! empty( $sub_setting['multiple'] ) ) {
					if ( ! isset( $value[ $sub_setting['id'] ] ) || ! is_array( $value[ $sub_setting['id'] ] ) ) {
						$value[ $sub_setting['id'] ] = array();
						continue;
					}
					foreach ( $value[ $sub_setting['id'] ] as $index => $option ) {
						$value[ $sub_setting['id'] ][ $index ] = sanitize_text_field( $option );
					}
					continue;
				}

				if ( ! isset( $value[ $sub_setting['id'] ] ) ) {
					$value[ $sub_setting['id'] ] = '';
					continue;
				}

				$value[ $sub_setting['id'] ] = sanitize_text_field( $value[ $sub_setting['id'] ] );
			}

			return $value;
		};
	}

	/**
	 * Gets the default value for the setting.
	 *
	 * @since 1.0.0
	 *
	 * @return array Default value.
	 */
	protected function get_default() {
		$sub_settings = $this->get_sub_settings();

		$value = array();
		foreach ( $sub_settings as $sub_setting ) {
			if ( ! empty( $sub_setting['multiple'] ) ) {
				$value[ $sub_setting['id'] ] = array();
				continue;
			}
			$value[ $sub_setting['id'] ] = '';
		}

		return $value;
	}


	/**
	 * Prints the 'id' attribute for a specific sub-setting.
	 *
	 * @since 1.4.0
	 *
	 * @param array $sub_setting Associative array of sub setting definition data.
	 */
	protected function id_attr( $sub_setting ) {
		echo ' id="' . esc_attr( Setting::OPTION_NAME . '-' . $sub_setting['id'] ) . '"';
	}

	/**
	 * Prints the 'name' attribute for a specific sub-setting.
	 *
	 * @since 1.4.0
	 *
	 * @param array $sub_setting Associative array of sub setting definition data.
	 */
	protected function name_attr( $sub_setting ) {
		echo ' name="' . esc_attr( Setting::OPTION_NAME . '[' . $sub_setting['id'] . ']' . ( ! empty( $sub_setting['multiple'] ) ? '[]' : '' ) ) . '"';
	}

	/**
	 * Prints the 'class' attribute for a specific sub-setting, if relevant.
	 *
	 * @since 1.4.0
	 *
	 * @param array $sub_setting Associative array of sub setting definition data.
	 */
	protected function class_attr( $sub_setting ) {
		if ( empty( $sub_setting['class'] ) ) {
			return;
		}

		echo ' class="' . esc_attr( $sub_setting['class'] ) . '"';
	}

	/**
	 * Prints the 'placeholder' attribute for a specific sub-setting, if relevant.
	 *
	 * @since 1.4.0
	 *
	 * @param array $sub_setting Associative array of sub setting definition data.
	 */
	protected function placeholder_attr( $sub_setting ) {
		if ( empty( $sub_setting['placeholder'] ) ) {
			return;
		}

		echo ' placeholder="' . esc_attr( $sub_setting['placeholder'] ) . '"';
	}

	/**
	 * Prints the 'aria-describedby' attribute for a specific sub-setting, if relevant.
	 *
	 * @since 1.4.0
	 *
	 * @param array $sub_setting Associative array of sub setting definition data.
	 */
	protected function aria_describedby_attr( $sub_setting ) {
		if ( empty( $sub_setting['description'] ) ) {
			return;
		}

		echo ' aria-describedby="' . esc_attr( Setting::OPTION_NAME . '-' . $sub_setting['id'] . '-description' ) . '"';
	}

	/**
	 * Prints the 'id' attribute for a specific sub-setting's description, if relevant.
	 *
	 * @since 1.4.0
	 *
	 * @param array $sub_setting Associative array of sub setting definition data.
	 */
	protected function description_id_attr( $sub_setting ) {
		if ( empty( $sub_setting['description'] ) ) {
			return;
		}

		echo ' id="' . esc_attr( Setting::OPTION_NAME . '-' . $sub_setting['id'] . '-description' ) . '"';
	}

	/**
	 * Prints the 'selected' attribute, if relevant.
	 *
	 * Expands on the similar WordPress function in that it supports array checks, relevant for multiple-choice UI.
	 *
	 * @param mixed $value   The value stored in the database, either a single value or an array of values.
	 * @param mixed $current The current value that the option element is rendered for, if not just true.
	 */
	protected function selected_attr( $value, $current = true ) {
		$enabled = is_array( $value ) && in_array( $current, $value, true ) || ! is_array( $value ) && (string) $value === (string) $current;
		selected( $enabled );
	}
}

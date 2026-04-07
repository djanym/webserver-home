<?php

/**
 * Class Validator.
 */
class Validator {

    /**
     * Check value against validation rules.
     * -----
     * -----
     * Available rules:
     * - optional: check if value is optional. If optional and empty then skip validation.
     * - not_empty: check if value is not empty if set in the form data.
     * - not_empty_array: check if value is an array and is not empty. Used for set of subfields.
     * - always_required: check if value is not empty even if it's not set in the form data. See rule_always_required() for more details.
     * - current_user_can: check if current user has the capability. Example: [ 'current_user_can' => 'edit_posts' ].
     * - min: check if value length is greater than the rule value. Example: [ 'min' => 10 ]. Works for both strings and arrays.
     * - max: check if value length is less than the rule value. Example: [ 'max' => 10 ]. Works for both strings and arrays.
     * - is_phone: check if value is a valid phone number format. The rule should be 'is_phone', not 'is_phone' => true.
     * - is_phone_format: check if value is a valid phone number format. Example 'is_phone_format' => 'XXX-XXX-XXXX' or 'is_phone_format' => [ 'XXX-XXX-XXXX', '(XXX) XXX-XXXX' ].
     * - is_email: check if value is a valid email address format. The rule should be 'is_email', not 'is_email' => true.
     * - is_price: check if value is a valid price format. The rule should be 'is_price', not 'is_price' => true.
     *              `is_price' rule can have additional arguments:
     *                      - 'allow_negative': by default price should be positive. If you want to allow negative prices, then set 'allow_negative'.
     *                      - 'max': maximum allowed price. Example: 'max' => 100000,
     *                      - 'min': minimum allowed price.
     * - is_unix_timestamp: check if value is a valid Unix timestamp. The rule should be 'is_unix_timestamp', not 'is_unix_timestamp' => true.
     * - is_url: check if value is a valid URL format. The rule should be 'is_url', not 'is_url' => true.
     * - options: check if value is one of the options. Example: [ 'options' => [ 'option1', 'option2' ] ].
     * - allowed_chars: should be an array of allowed characters. Example: [ 'letters', 'spaces' ]. See rule_allowed_chars() for more details.
     * - bool: check if value is '1' or '0'.
     * Rules specific for $_FILES field:
     * - file_max: check if input file size is not greater than the rule value. Example: [ 'file_max' => 1000000 ].
     * - file_min: check if input file size is not less than the rule value. Example: [ 'file_min' => 1000 ].
     * - file_extension: check if input file extension is in the allowed file extensions. Example: [ 'file_extension' => [ 'jpg', 'png' ] ].
     * - file_image_dimension: check if input file image dimensions are within the allowed dimensions. Example: [ 'file_image_dimension' => [ 'max_width' => 100, 'max_height' => 100 ] ].
     * - file_error_check: check if the uploaded file has any errors. The rule should be 'file_error_check', not 'file_error_check' => true.
     * -----
     * -----
     * Also, a set of subfields can be created by using the 'subfields_set' rule. Example:
     * 'subfields_set' => [
     *      'title'     => [
     *          'not_empty',
     *          'always_required',
     *          'max' => 50,
     *      ],
     * ],
     * -----
     * -----
     * Rule value can be an array with additional arguments:
     * - rule_value: value of the rule. Example: 'min' => 10 could be used as: 'min' => [ ..., 'rule_value' => 10 ]
     * - error_message: custom error message.
     * -----
     * -----
     * Additional field options:
     * `on_error_field_key` - if set, then the error message will be assigned to this field key. Helpful if you want to show the error message in the response container. Example: 'field_key' => [
     * ..., 'on_error_field_key' => 'general' ]
     *
     * @param string|array $value Value to validate.
     * @param array        $rules Validation rules.
     *
     * @return true|string True if value is valid, error message otherwise.
     */
    public static function validate( string|array $value, array $rules = [] ): true|string {
        foreach ( $rules as $rule_key => $rule_value ) {
            $rule_args = [];

            // There is a different way of declaring a rule: as a key, as a key=>value pair, or even with the additional args. Therefore, we need to determine the format here and assign right values for rule key, value, and args.
            // Check if rule value is the rule key.
            if ( is_int( $rule_key ) ) {
                $rule_key   = $rule_value;
                $rule_value = [];
            } elseif ( is_array( $rule_value ) ) {
                $rule_args = $rule_value;
                if ( ! empty( $rule_args['rule_value'] ) ) {
                    $rule_value = $rule_args['rule_value'];
                }
            }

            // Check if value is optional. If optional and empty then skip validation.
            if ( $rule_key === 'optional' && empty( $value ) ) {
                return true;
            }

            $class_method = 'rule_' . $rule_key;

            // Check if method exists for the rule.
            if ( method_exists( __CLASS__, $class_method ) ) {
                $result = self::$class_method( $value, $rule_value, $rule_args );
                if ( $result !== true ) {
                    // Default `custom error message` logic for all fields.
                    if ( ! empty( $rule_args['error_message'] ) ) {
                        return $rule_args['error_message'];
                    }

                    return $result;
                }
            }
        }

        return true;
    }

    /**
     * Check if value is not empty.
     * Difference from `always_required` is that this rule is only required if the field is set in the form data array.
     *
     * @param mixed $value Value to check.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_not_empty( $value ): true|string {
        if ( empty( $value ) ) {
            return 'Required field.';
        }

        return true;
    }

    /**
     * Check if value is not empty. Difference from `not_empty` is that this rule is always required even if not set in the form data.
     * By `not set` means that the field is not in the form data array. In that case it will throw an error.
     * Meanwhile, `not_empty` will not throw an error if the field is not in the form data array.
     * Because it applies only if the field is set in the form data array.
     *
     * @param mixed $value     Value to check.
     * @param array $rule_args Rule options.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_always_required( mixed $value, array $rule_args = [] ): true|string {
        if ( empty( $value ) ) {
//            if ( ! empty( $rule_args['error_message'] ) ) {
//                return $rule_args['error_message'];
//            }

            return 'Required field.';
        }

        return true;
    }

    /**
     * Check if value is an array and is not empty.
     *
     * @param mixed $value Value to check.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_not_empty_array( $value ): true|string {
        if ( ! is_array( $value ) || empty( $value ) ) {
            return 'Required field.';
        }

        return true;
    }

    /**
     * Check if value length is not less than the rule value.
     * Works for both strings and arrays.
     * In case of arrays, it checks the number of items.
     *
     * @param string|array $value      Value to check.
     * @param int          $min_length Minimum length.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_min( string|array $value, int $min_length ): true|string {
        if ( is_array( $value ) && count( $value ) > $min_length ) {
            return 'Minimum ' . $min_length . ' items required.';
        }

        if ( is_string( $value ) && strlen( $value ) < $min_length ) {
            return 'Minimum length is ' . $min_length . ' characters.';
        }

        return true;
    }

    /**
     * Check if value length is not greater than the rule value.
     * Works for both strings and arrays.
     * In case of arrays, it checks the number of items.
     *
     * @param string|array $value      Value to check.
     * @param int          $max_length Maximum length.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_max( string|array $value, int $max_length ): true|string {
        if ( is_array( $value ) && count( $value ) > $max_length ) {
            return 'Maximum ' . $max_length . ' items allowed.';
        }

        if ( is_string( $value ) && strlen( $value ) > $max_length ) {
            return 'Maximum length is ' . $max_length . ' characters.';
        }

        return true;
    }

    /**
     * Check if current user has the capability.
     *
     * @param mixed  $value      Value of the field. Not used in this rule.
     * @param string $capability Capability to check.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_current_user_can( $value, string $capability ): true|string {
        if ( ! current_user_can( $capability ) ) {
            return 'You do not have permission to perform this action.';
        }

        return true;
    }

    /**
     * Check if value is a valid phone number format.
     *
     * @param string       $value  Value to check.
     * @param string|array $format Format to check against. Can be a string or an array of formats.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_is_phone_format( string $value, $format ): true|string {
        if ( ! is_array( $format ) ) {
            $format = [ $format ];
        }

        // Set flag to check if the value matches any of the formats.
        $is_valid = false;

        foreach ( $format as $f ) {
            // Check if value matches the format.
            if ( preg_match( '/^' . str_replace( 'X', '\d', preg_quote( $f, '/' ) ) . '[\d]*/', $value ) ) {
                $is_valid = true;
                break;
            }
        }

        if ( ! $is_valid ) {
            return 'Invalid phone number format. Should be: ' . implode( ' or ', $format );
        }

        return true;
    }

    /**
     * Check if value is a valid email address format.
     *
     * @param string $value Value to check.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_is_email( string $value ): true|string {
        if ( ! is_email( $value ) ) {
            return 'Invalid email format.';
        }

        if ( is_email_address_unsafe( $value ) ) {
            return 'You cannot use that email address. Please use another email provider.';
        }

        // Check if email domain is allowed.
        $limited_email_domains = get_site_option( 'limited_email_domains' );
        // Check against limited email domains. This option is under Network Settings.
        if ( is_array( $limited_email_domains ) && ! empty( $limited_email_domains ) ) {
            $limited_email_domains = array_map( 'strtolower', $limited_email_domains );
            $emaildomain           = strtolower( substr( $value, 1 + strpos( $value, '@' ) ) );
            if ( ! in_array( $emaildomain, $limited_email_domains, true ) ) {
                return 'Sorry, that email address is not allowed!';
            }
        }

        return true;
    }

    /**
     * Check if value is a valid price format.
     *
     * @param string $value     Value to check.
     * @param array  $rule_args Rule options.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_is_price( string $value, array $rule_args = [] ): true|string {
        $negative_regex = '';
        if ( in_array( 'allow_negative', $rule_args ) ) {
            // Optional negative value.
            $negative_regex = '(-)?';
        }

        if ( ! preg_match( '/^' . $negative_regex . '\d+(\.\d{1,2})?$/', $value ) ) {
            if ( ! empty( $rule_options['error_message'] ) ) {
                return $rule_options['error_message'];
            }

            return 'Invalid format.';
        }

        // Check if price is greater than 0.
        if ( (float) $value === 0 ) {
            return 'Value should not be 0.';
        }

        if ( ! empty( $rule_args['max'] ) && (float) $value > $rule_args['max'] ) {
            return 'Value should not be greater than ' . $rule_args['max'];
        }

        if ( ! empty( $rule_args['min'] ) && (float) $value < $rule_args['min'] ) {
            return 'Value should not be less than ' . $rule_args['min'];
        }

        return true;
    }

    /**
     * Check if value is a valid URL format.
     *
     * @param string $value Value to check.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_is_url( string $value ): true|string {
        if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
            return 'Invalid URL format.';
        }

        return true;
    }

    private static function rule_options( string $value, array $options ): true|string {
        if ( ! in_array( $value, $options, true ) ) {
            return 'Invalid value.';
        }

        return true;
    }

    /**
     * Check if value is a valid UNIX timestamp.
     *
     * @param string $value Value to check.
     *
     * @return true|string
     */
    private static function rule_is_unix_timestamp( string $value ): bool|string {
        // Check if value is numeric.
        if ( ! is_numeric( $value ) ) {
            return 'Invalid format: must be numeric.';
        }

        // Convert to integer and check for overflow/underflow.
        $timestamp = (int) $value;
        if ( (string) $timestamp !== $value ) {
            return 'Invalid format: precision loss detected.';
        }

        // Check if timestamp is within reasonable range.
        // PHP's max int is typically 2147483647 (Jan 19, 2038) on 32-bit systems and for 64-bit systems we can use a more future-proof range.
        $min_timestamp = 0; // Jan 1, 1970.
        $max_timestamp = min( PHP_INT_MAX, 9999999999 ); // ~Year 2286

        if ( $timestamp < $min_timestamp || $timestamp > $max_timestamp ) {
            return 'Invalid format: wrong range.';
        }

        return true;
    }

    /**
     * Check if value has only allowed characters.
     *
     * @param string $value         Value to check.
     * @param array  $allowed_chars Allowed characters rules.
     *                              Available rules:
     *                              - letters: all letters in all languages.
     *                              - spaces: spaces and tabs.
     *                              - digits: 0-9.
     *                              - dash: -.
     *                              - underscore: _.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_allowed_chars( string $value, array $allowed_chars ): true|string {
        $regex = '';

        // Run through each character rule and add it to the regex.
        foreach ( $allowed_chars as $char_rule ) {
            $error_message = false;
            if ( $char_rule === 'name' ) {
                $error_message = 'Only letters, spaces, hyphens, apostrophes and periods are allowed.';
                // Adds all letters in all languages, spaces, hyphens, apostrophes, periods.
                $regex .= '\p{L}\s\-\'\.';            }
            if ( $char_rule === 'name_digits' ) {
                $error_message = 'Only letters, numbers, spaces, hyphens, apostrophes and periods are allowed.';
                // Adds all letters in all languages, digits, spaces, hyphens, apostrophes, periods.
                $regex .= '\p{L}\d\s\-\'\.';
            }
            if ( $char_rule === 'letters' ) {
                // Add all letters in all languages.
                $regex .= '\p{L}';
            }
            if ( $char_rule === 'spaces' ) {
                $regex .= '\s';
            }
            if ( $char_rule === 'digits' ) {
                $regex .= '\d';
            }
            if ( $char_rule === 'dot' ) {
                $regex .= '\.';
            }
            if ( $char_rule === 'comma' ) {
                $regex .= '\,';
            }
            if ( $char_rule === 'dash' ) {
                $regex .= '\-';
            }
            if ( $char_rule === 'underscore' ) {
                $regex .= '\_';
            }
        }

        // Prepare the regex.
        $regex = '/^[' . $regex . ']+$/iu';

        if ( ! preg_match( $regex, $value ) ) {
            if ( ! empty( $error_message ) ) {
                return $error_message;
            }

            return 'Only ' . implode( ', ', $allowed_chars ) . ' are allowed.';
        }

        return true;
    }

    /**
     * Check if value is a 1 (true) or 0 (false).
     *
     * @param string $value Value to check.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_bool( string $value ): true|string {
        if ( $value !== '1' && $value !== '0' ) {
            return 'Invalid value.';
        }

        return true;
    }

    /**
     * Check if input file size is not greater than the rule value.
     * Specific for $_FILES field.
     *
     * @param array $input_field_value Value from the $_FILES to check.
     * @param int   $max_size          Maximum file size.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_file_max( array $input_field_value, int $max_size ): true|string {
        if ( $input_field_value['size'] > $max_size ) {
            return 'Maximum file size is ' . size_format( $max_size );
        }

        return true;
    }

    /**
     * Check if input file size is not less than the rule value.
     * Specific for $_FILES field.
     *
     * @param array $input_field_value Value from the $_FILES to check.
     * @param int   $min_size          Minimum file size.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_file_min( array $input_field_value, int $min_size ): true|string {
        if ( $input_field_value['size'] < $min_size ) {
            return 'Minimum file size is ' . size_format( $min_size );
        }

        return true;
    }

    private static function rule_file_extension( array $input_field_value, array $allowed_file_extensions ): true|string {
        $file_extension = wp_check_filetype( $input_field_value['name'] );
        if ( ! in_array( $file_extension['ext'], $allowed_file_extensions, true ) ) {
            return 'Invalid file extension. Allowed extensions: ' . implode( ', ', $allowed_file_extensions );
        }

        return true;
    }

    private static function rule_file_image_dimension( array $input_field_value, array $allowed_file_types ): true|string {
//                // Check image dimensions.
//                if ( $rule_key === 'image_dimensions' ) {
//                    $image_size = getimagesize( $file_data['tmp_name'] );
//                    if ( $image_size[0] > $rule_value['max_width'] || $image_size[1] > $rule_value['max_height'] ) {
//                        $this->error->add(
//                            $field_key,
//                            sprintf(
//                                'Invalid image dimensions. Allowed dimensions: %dpx*%dpx',
//                                $rule_value['max_width'],
//                                $rule_value['max_height']
//                            )
//                        );
//                        break;
//                    }
//                }

        return true;
    }

    /**
     * Checks if the uploaded file has any errors.
     * Specific for $_FILES field.
     *
     * @param array $input_field_value Value from the $_FILES to check.
     *
     * @return true|string True if valid, error message otherwise.
     */
    private static function rule_file_error_check( array $input_field_value ): true|string {
        if ( $input_field_value['error'] === UPLOAD_ERR_OK ) {
            return true;
        }

        // Check if file was uploaded and there is any error except empty.
        if ( $input_field_value['error'] === UPLOAD_ERR_NO_FILE ) {
            return 'Required field.';
        }

        return $input_field_value['error'];
    }
}

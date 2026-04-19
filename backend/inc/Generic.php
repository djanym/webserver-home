<?php

namespace WebserverHome;

use RuntimeException;

/**
 * Backend request-flow base contract.
 * Responsibilities:
 * - Owns per-request error container (`AppError`) and additional response payload.
 * - Provides JSON emitters: `sendJsonResponse()` for success path, `sendErrorResponse()` for failure path.
 * - Provides validation wrappers: `filterValidateAll()` (field filtering + recursive subset validation)
 *   and `validateField()` (delegates rule execution to `Validator::validate()`).
 * Agent usage:
 * - Extend/reuse this class for handlers/services that validate input and return API responses.
 * - Do not duplicate response/error plumbing in module classes.
 */
class Generic {
    /**
     * The additional custom data to be sent in the ajax response.
     *
     * @var array
     */
    public array $additionalResponseData = [];

    public AppError $error;

    /**
     * Generic constructor
     */
    public function __construct() {
        $this->error = new AppError();
    }

    /**
     * Send ajax response in JSON format.
     *
     * @param array|string|null $data
     * @param int|null          $status_code
     *
     * @return void
     */
    public function sendJsonResponse( array|string|null $data = null, int $status_code = null, bool $skip_error_checking = false ) : void {
        // If object has errors, then switch to error message response. Except when skip_error_checking is true.
        if ( ! $skip_error_checking && $this->error->hasErrors() ) {
            $this->sendErrorResponse( null, $status_code );

            return;
        }

        // Default value.
        $response = [ 'success' => true ];

        if ( isset( $data ) ) {
            if ( is_string( $data ) ) {
                $response['message'] = $data;
            } elseif ( is_array( $data ) ) {
                $response['data'] = $data;

                if ( ! empty( $data['message'] ) ) {
                    $response['message'] = $data['message'];
                }
            }
        }

        // Errors can happen even if action was successful.
        //So we need to check if there are errors and add them to the response.
        if ( $this->error->hasErrors() ) {
            // Create an array of error messages in format: error code => error message.
            foreach ( $this->error->getErrorCodes() as $code ) {
                $response['errors'][ $code ] = $this->error->getErrorMessage( $code );
            }
        }

        // Add additional data to the response.
        if ( $this->additionalResponseData ) {
            $response = array_merge( $response, $this->additionalResponseData );
        }

        // Send response.
        send_json( $response, $status_code );
    }

    /**
     * Send ajax response in JSON format.
     *
     * @param string|null $error_message Optional. The message to be sent in the response.
     *
     * @return void
     */
    public function sendErrorResponse( ?string $error_message = '', int $status_code = null ) : void {
        // Default value.
        $response = [ 'success' => false ];

        // Prepare errors data response.
        if ( $this->error->hasErrors() ) {
            // Create an array of error messages in format: error code => error message.
            foreach ( $this->error->getErrorCodes() as $code ) {
                $response['errors'][ $code ] = $this->error->getErrorMessage( $code );
            }
        }

        if ( ! empty( $error_message ) ) {
            $response['message'] = $error_message;
        }

        // Add additional data to the response.
        if ( $this->additionalResponseData ) {
            $response = array_merge( $response, $this->additionalResponseData );
        }

        // Send response.
        send_json( $response, $status_code );
    }

    /**
     * Validate all fields in the form data against the validation rules.
     * The validation rules described in Validator class.
     *
     * @param array $form_data        The form data to validate. The array should be in the format: [ 'field_key' => 'field_value' ].
     * @param array $validation_rules The validation rules for each field. The array should be in the format: [ 'field_key' => [ 'rule1', 'rule2', ... ] ].
     * @param array $options          Optional. Additional options for the validation process.
     *
     * @return array The validated fields data. If a field is not presented in the validation rules, it will be removed from the result.
     */
    public function filterValidateAll( array $form_data, array $validation_rules, array $options = [] ) : array {
        $fields_data = [];
        $options     = parse_args(
            $options,
            [
                'error_field_prefix' => '',
                'field_in_subset'    => false,
            ]
        );

        // Filter fields and retain only editable/updatable data fields. And only NOT NULL fields.
        // A field should be added to the class $fields array.
        foreach ( $validation_rules as $field_key => $field_rules ) {
            // Check if the field exists in the form data and not null.
            // In case it's null and the field is always required in this set of the fields, then add it to the fields data with the empty value.
            // We need this because subfields usually are set of fields that are all required.
            if ( isset( $form_data[ $field_key ] ) ) {
                $fields_data[ $field_key ] = $form_data[ $field_key ];
            } elseif ( isset( $field_rules['always_required'] ) || in_array( 'always_required', $field_rules, true ) ) {
                $fields_data[ $field_key ] = '';
            }
        }

        // Run the validation rules.
        foreach ( $fields_data as $field_key => $field_value ) {
            if ( isset( $validation_rules[ $field_key ] ) ) {
                // Prepare error field key.
                // If field is part of subset fields, then add the subset key to the error field key.
                $error_field_key = $options['error_field_prefix']
                                   . ( $options['field_in_subset'] ? '[' . $field_key . ']' : $field_key );

                if ( ! empty( $validation_rules[ $field_key ]['on_error_field_key'] ) ) {
                    $error_field_key = $validation_rules[ $field_key ]['on_error_field_key'];
                }

                // Run the validation for the field.
                $validation_result = $this->validateField(
                    $field_value,
                    $validation_rules[ $field_key ],
                    $error_field_key,
                    [
                        'form_data' => $form_data,
                        'field_key' => $field_key,
                        // Why not $error_field_key? Because $error_field_key can be overridden by the validation rules, and we want to keep the original field key for the context of the validation rules. Should be confirmed!
                    ]
                );

                // Check if the field has subfields set, then validate each of them.
                if ( $validation_result && isset( $validation_rules[ $field_key ]['subfields_set'] ) ) {
                    // If it has subfields values, then validate them. Throwing errors for empty array is made before by using `not_empty_array` rule.
                    // Here we just need to check if the field is an array and not empty.
                    if ( is_array( $field_value ) && ! empty( $field_value ) ) {
                        // Run the validation for each subset of the field.
                        foreach ( $field_value as $subset_nn => $subset_fields ) {
                            $fields_data[ $field_key ][ $subset_nn ] = $this->filterValidateAll(
                                $subset_fields,
                                $validation_rules[ $field_key ]['subfields_set'],
                                [
                                    'error_field_prefix' => $options['error_field_prefix'] . $field_key . '[' . $subset_nn . ']',
                                    'field_in_subset'    => true,
                                ]
                            );
                        }
                    }
                }
            }
        }

        return $fields_data;
    }

    /**
     * Validate the field value against the rules. In case of error, add it to the error object.
     *
     * @param mixed  $value           The value to validate.
     * @param array  $rules           The validation rules.
     * @param string $error_field_key The error field key to add the error message to.
     * @param array  $context         Optional. Extra context for rule evaluation.
     *
     * @return bool True if the field is valid, false otherwise. Also, adds the error message to the error object.
     * @uses Validator::validate()
     */
    public function validateField( mixed $value, array $rules, string $error_field_key, array $context = [] ) : bool {
        $validation_result = Validator::validate( $value, $rules, $context );
        if ( $validation_result !== true ) {
            $this->error->add( $error_field_key, $validation_result );

            return false;
        }

        return true;
    }

    /**
     * Filters and validates the files data to be uploaded. Then uploads the files.
     *
     * @param array $file_data        The files data from the form.
     * @param array $options          The additional options for the files upload.
     * @param array $validation_rules The validation rules for the files.
     *
     * @return string|AppError The uploaded file path or AppError object if an error occurred.
     */
    public function uploadFile( array $file_data, array $options = [], array $validation_rules = [] ) : AppError|string {
        $overrides = [ 'test_form' => false ];
        if ( isset( $validation_rules['allowed_mimes'] ) ) {
            $overrides['mimes'] = $validation_rules['allowed_mimes'];
        }

        $options = wp_parse_args(
            $options,
            [
                'subfolder' => null,
            ]
        );

        // If set subfolder, then add it to the upload path.
        if ( ! empty( $options['subfolder'] ) ) {
            $upload_subfolder = $options['subfolder'];

            add_filter(
                'upload_dir',
                static function( $upload ) use ( $upload_subfolder ) {
                    $upload['subdir'] = '/' . $upload_subfolder;
                    $upload['path']   = $upload['basedir'] . $upload['subdir'];
                    $upload['url']    = $upload['baseurl'] . $upload['subdir'];

                    return $upload;
                }
            );
        }

        // Upload the file and move it to the site's uploads directory.
        $uploaded_file = wp_handle_upload(
            $file_data,
            $overrides
        );

        // Check for errors.
        if ( ! empty( $uploaded_file['error'] ) ) {
            return new AppError( 'upload_error', $uploaded_file['error'] );
        }

        return $uploaded_file['file'];
    }

    /**
     * Deletes the file from the site's uploads directory.
     *
     * @param string $file_path The file path to delete.
     *
     * @return bool True if the file is deleted, false otherwise.
     */
    public function deleteFile( string $file_path ) : bool {
        $absolute_path = SUBSITE_UPLOAD_DIR . '/' . $file_path;
        if ( ! file_exists( $absolute_path ) ) {
            return false;
        }

        wp_delete_file( SUBSITE_UPLOAD_DIR . '/' . $file_path );

        return true;
    }

    /**
     * Getter for the static properties.
     *
     * @param string $property The property name to get.
     *
     * @return mixed The property value.
     * @throws RuntimeException If the property does not exist.
     */
    public static function get( string $property ) : mixed {
        if ( property_exists( __CLASS__, $property ) ) {
            return self::$$property;
        }
        throw new RuntimeException( "Property $property does not exist." );
    }

    /**
     * Return first error message if exists.
     *
     * @return string|null
     */
    public function getErrorMessage() : ?string {
        if ( $this->error->hasErrors() ) {
            return $this->error->getErrorMessage();
        }

        return null;
    }

    /**
     * Get the error code if errors exist.
     *
     * @return string|null The error code if errors exist, null otherwise.
     */
    public function getErrorCode() : ?string {
        if ( $this->error->hasErrors() ) {
            return $this->error->getErrorCode();
        }

        return null;
    }

    public function hasErrors() : bool {
        return $this->error->hasErrors();
    }

    public function addError( string $code, string $message ) : void {
        $this->error->add( $code, $message );
    }
}
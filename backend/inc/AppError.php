<?php
/**
 * Application Error API.
 */

namespace WebserverHome;

/**
 * Structured backend error aggregator.
 *
 * Functionality description:
 * - Created once per request flow (usually via `Generic::$error`).
 * - Add errors with `add( code, message[, data] )` using deterministic field/code keys.
 * - Error codes should be field names if the error is regarding a specific field.
 * - Error codes will be used to find the field by name on the frontend side.
 * - Query flow state via `hasErrors()` and read payload via `getErrorCodes()` / `getErrorMessage()`.
 * - Merge/export supported with `mergeFrom()` / `exportTo()` for composed flows.
 *
 * Serialization boundary:
 * - This class stores errors only.
 * - JSON response emission is performed by `Generic::sendErrorResponse()`, which maps codes to messages.
 */
#[AllowDynamicProperties]
class AppError {
    /**
     * Stores the list of errors.
     *
     * @var array
     */
    public $errors = array();

    /**
     * Stores the most recently added data for each error code.
     *
     * @var array
     */
    public $error_data = array();

    /**
     * Stores previously added data added for error codes, oldest-to-newest by code.
     *
     * @var array[]
     */
    protected $additional_data = array();

    /**
     * Initializes the error.
     * If `$code` is empty, the other parameters will be ignored.
     * When `$code` is not empty, `$message` will be used even if
     * it is empty. The `$data` parameter will be used only if it
     * is not empty.
     * Though the class is constructed with a single error code and
     * message, multiple codes can be added using the `add()` method.
     *
     * @param string|int $code    Error code.
     * @param string     $message Error message.
     * @param mixed      $data    Optional. Error data. Default empty string.
     */
    public function __construct( $code = '', $message = '', $data = '' ) {
        if ( empty( $code ) ) {
            return;
        }

        $this->add( $code, $message, $data );
    }

    /**
     * Retrieves all error codes.
     *
     * @return array List of error codes, if available.
     */
    public function getErrorCodes() {
        if ( ! $this->hasErrors() ) {
            return array();
        }

        return array_keys( $this->errors );
    }

    /**
     * Retrieves the first error code available.
     *
     * @return string|int Empty string, if no error codes.
     */
    public function getErrorCode() {
        $codes = $this->getErrorCodes();

        if ( empty( $codes ) ) {
            return '';
        }

        return $codes[0];
    }

    /**
     * Retrieves all error messages, or the error messages for the given error code.
     *
     * @param int|string|null $code Optional. Error code to retrieve the messages for.
     *                              Default empty string.
     *
     * @return string[] Error strings on success, or empty array if there are none.
     */
    public function getErrorMessages( int|string $code = null ) : array {
        // Return all messages if no code specified.
        if ( empty( $code ) ) {
            $all_messages = array();
            foreach ( (array) $this->errors as $_code => $messages ) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $all_messages = array_merge( $all_messages, $messages );
            }

            return $all_messages;
        }

        return $this->errors[ $code ] ?? [];
    }

    /**
     * Gets a single error message.
     * This will get the first message available for the code. If no code is
     * given then the first code available will be used.
     *
     * @param string|int $code Optional. Error code to retrieve the message for.
     *                         Default empty string.
     *
     * @return string The error message.
     */
    public function getErrorMessage( $code = '' ) {
        if ( empty( $code ) ) {
            $code = $this->getErrorCode();
        }
        $messages = $this->getErrorMessages( $code );
        if ( empty( $messages ) ) {
            return '';
        }

        return $messages[0];
    }

    /**
     * Retrieves the most recently added error data for an error code.
     *
     * @param string|int $code Optional. Error code. Default empty string.
     *
     * @return mixed Error data, if it exists.
     */
    public function getErrorData( $code = '' ) {
        if ( empty( $code ) ) {
            $code = $this->getErrorCode();
        }

        if ( isset( $this->error_data[ $code ] ) ) {
            return $this->error_data[ $code ];
        }
    }

    /**
     * Verifies if the instance contains errors.
     *
     * @return bool If the instance contains errors.
     */
    public function hasErrors() {
        if ( ! empty( $this->errors ) ) {
            return true;
        }

        return false;
    }

    /**
     * Adds an error or appends an additional message to an existing error.
     *
     * @param string|int $code    Error code.
     * @param string     $message Error message.
     * @param mixed      $data    Optional. Error data. Default empty string.
     */
    public function add( $code, $message, $data = '' ) {
        $this->errors[ $code ][] = $message;

        if ( ! empty( $data ) ) {
            $this->addData( $data, $code );
        }
    }

    /**
     * Adds data to an error with the given code.
     *
     * @param mixed      $data Error data.
     * @param string|int $code Error code.
     */
    public function addData( $data, $code = '' ) {
        if ( empty( $code ) ) {
            $code = $this->getErrorCode();
        }

        if ( isset( $this->error_data[ $code ] ) ) {
            $this->additional_data[ $code ][] = $this->error_data[ $code ];
        }

        $this->error_data[ $code ] = $data;
    }

    /**
     * Retrieves all error data for an error code in the order in which the data was added.
     *
     * @param string|int $code Error code.
     *
     * @return mixed[] Array of error data, if it exists.
     */
    public function getAllErrorData( $code = '' ) {
        if ( empty( $code ) ) {
            $code = $this->getErrorCode();
        }

        $data = array();

        if ( isset( $this->additional_data[ $code ] ) ) {
            $data = $this->additional_data[ $code ];
        }

        if ( isset( $this->error_data[ $code ] ) ) {
            $data[] = $this->error_data[ $code ];
        }

        return $data;
    }

    /**
     * Removes the specified error.
     * This function removes all error messages associated with the specified
     * error code, along with any error data for that code.
     *
     * @param string|int $code Error code.
     */
    public function remove( $code ) {
        unset( $this->errors[ $code ] );
        unset( $this->error_data[ $code ] );
        unset( $this->additional_data[ $code ] );
    }

    /**
     * Merges the errors in the given error object into this one.
     *
     * @param AppError $error Error object to merge.
     */
    public function mergeFrom( AppError $error ) {
        static::copyErrors( $error, $this );
    }

    /**
     * Exports the errors in this object into the given one.
     *
     * @param AppError $error Error object to export into.
     */
    public function exportTo( AppError $error ) {
        static::copyErrors( $this, $error );
    }

    /**
     * Copies errors from one AppError instance to another.
     *
     * @param AppError $from The AppError to copy from.
     * @param AppError $to   The AppError to copy to.
     */
    protected static function copyErrors( self $from, self $to ) : void {
        foreach ( $from->getErrorCodes() as $code ) {
            foreach ( $from->getErrorMessages( $code ) as $error_message ) {
                $to->add( $code, $error_message );
            }

            foreach ( $from->getAllErrorData( $code ) as $data ) {
                $to->addData( $data, $code );
            }
        }
    }
}
<?php

function load_config(): void
{
    global $app_config;
    $config_file = ABSPATH . '/config/app-config.php';
    if (file_exists($config_file)) {
        $app_config = include $config_file;
    } else {
        throw new RuntimeException("Configuration file not found: " . $config_file);
    }
    $config_file = ABSPATH . '/config/server-config.php';
    if (file_exists($config_file)) {
        $server_config = include $config_file;
        // Merge server config into app config.
        $app_config = array_merge($app_config, $server_config);
    } else {
        throw new RuntimeException("Configuration file not found: " . $config_file);
    }
}

function config($key, $default = null)
{
    global $app_config;
    return $app_config[$key] ?? $default;
}

function user_can($action): true
{
    // Placeholder for future role/permission logic
    // For now, always allow
    return true;
}

function is_logged_in(): bool
{
    // Placeholder for future session management logic
    // For now, always return true
    return true;
}

/**
 * Set HTTP status header.
 *
 * @param $header
 * @return false|void
 */
function status_header($header)
{
    $text = get_status_header_desc($header);

    if (empty($text)) {
        return false;
    }

    $protocol = $_SERVER["SERVER_PROTOCOL"];
    if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol) {
        $protocol = 'HTTP/1.0';
    }
    $status_header = "$protocol $header $text";

    return @header($status_header, true, $header);
}

/**
 * Get the description of an HTTP status code.
 *
 * @param int $code The HTTP status code.
 * @return string The description of the status code.
 */
function get_status_header_desc(int $code): string
{
    $code = abs($code);

    $header_to_desc = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        510 => 'Not Extended',
    );

    return $header_to_desc[$code] ?? '';
}

function get_status_header_message($code)
{
    $text = get_status_header_desc($code);
    $protocol = $_SERVER["SERVER_PROTOCOL"];
    if ('HTTP/1.1' !== $protocol && 'HTTP/1.0' !== $protocol) {
        $protocol = 'HTTP/1.0';
    }
    return "$protocol $code $text";
}


/**
 * Encode a variable into JSON, with some sanity checks.
 *
 * @param mixed $data Variable (usually an array or object) to encode as JSON.
 * @param int $options Optional. Options to be passed to json_encode(). Default 0.
 * @param int $depth Optional. Maximum depth to walk through $data. Must be
 *                       greater than 0. Default 512.
 * @return string|false The JSON encoded string, or false if it cannot be encoded.
 */
function ws_json_encode($data, $options = 0, $depth = 512)
{
    $json = json_encode($data, $options, $depth);

    // If json_encode() was successful, no need to do more sanity checking.
    if (false !== $json) {
        return $json;
    }

    try {
        $data = ws_json_sanity_check($data, $depth);
    } catch (Exception $e) {
        return false;
    }

    return json_encode($data, $options, $depth);
}

/**
 * Perform sanity checks on data that shall be encoded to JSON.
 *
 * @param mixed $data Variable (usually an array or object) to encode as JSON.
 * @param int $depth Maximum depth to walk through $data. Must be greater than 0.
 * @return mixed The sanitized data that shall be encoded to JSON.
 */
function ws_json_sanity_check($data, $depth): mixed
{
    if ($depth < 0) {
        throw new RuntimeException('Reached depth limit');
    }

    if (is_array($data)) {
        $output = array();
        foreach ($data as $id => $el) {
            // Don't forget to sanitize the ID!
            if (is_string($id)) {
                $clean_id = ws_json_convert_string($id);
            } else {
                $clean_id = $id;
            }

            // Check the element type, so that we're only recursing if we really have to.
            if (is_array($el) || is_object($el)) {
                $output[$clean_id] = ws_json_sanity_check($el, $depth - 1);
            } elseif (is_string($el)) {
                $output[$clean_id] = ws_json_convert_string($el);
            } else {
                $output[$clean_id] = $el;
            }
        }
    } elseif (is_object($data)) {
        $output = new stdClass();
        foreach ($data as $id => $el) {
            if (is_string($id)) {
                $clean_id = ws_json_convert_string($id);
            } else {
                $clean_id = $id;
            }

            if (is_array($el) || is_object($el)) {
                $output->$clean_id = ws_json_sanity_check($el, $depth - 1);
            } elseif (is_string($el)) {
                $output->$clean_id = ws_json_convert_string($el);
            } else {
                $output->$clean_id = $el;
            }
        }
    } elseif (is_string($data)) {
        return ws_json_convert_string($data);
    } else {
        return $data;
    }

    return $output;
}

/**
 * Convert a string to UTF-8, so that it can be safely encoded to JSON.
 *
 * @param string $string The string which is to be converted.
 * @return string The checked string.
 */
function ws_json_convert_string(string $string)
{
    static $use_mb = null;
    if (is_null($use_mb)) {
        $use_mb = function_exists('mb_convert_encoding');
    }

    if ($use_mb) {
        $encoding = mb_detect_encoding($string, mb_detect_order(), true);
        if ($encoding) {
            return mb_convert_encoding($string, 'UTF-8', $encoding);
        }

        return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

    return check_invalid_utf8($string, true);
}

/**
 * Send a JSON response back to an Ajax request.
 *
 * @param mixed $response Variable (usually an array or object) to encode as JSON,
 *                           then print and die.
 * @param int|null $status_code The HTTP status code to output.
 */
function send_json(mixed $response, int $status_code = null)
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
        if (null !== $status_code) {
            status_header($status_code);
        }
    }

    if ($status_code !== null) {
        $response['status_code'] = $status_code;
    }

    echo ws_json_encode($response);
    die;
}

/**
 * Send a JSON response back to an Ajax request, indicating success.
 *
 * @param mixed|null $data Data to encode as JSON, then print and die.
 * @param int $status_code The HTTP status code to output.
 *
 */
function send_json_success(mixed $data = null, int $status_code = 200): void
{
    $response = array('success' => true);

    if (isset($data)) {
        if (is_string($data)) {
            $response['message'] = $data;
        } else {
            $response['data'] = $data;
        }
    }

    send_json($response, $status_code);
}

/**
 * Send a JSON response back to an Ajax request, indicating failure.
 *
 * @param mixed $data Data to encode as JSON, then print and die.
 * @param int $status_code The HTTP status code to output.
 */
function send_json_error($data = null, $status_code = 400)
{
    $response = array('success' => false);

    if (isset($data)) {
        if (is_string($data)) {
            $response['message'] = $data;
        } else {
            $response['data'] = $data;
        }
    }

    send_json($response, $status_code);
}


/**
 * Checks for invalid UTF8 in a string.
 *
 * @param string $text The text which is to be checked.
 * @param bool $strip Optional. Whether to attempt to strip out invalid UTF8. Default false.
 * @return string The checked text.
 * @since 2.8.0
 *
 */
function check_invalid_utf8($text, $strip = false)
{
    $text = (string)$text;

    if (0 === strlen($text)) {
        return '';
    }

    // Check for support for utf8 in the installed PCRE library once and store the result in a static.
    static $utf8_pcre = null;
    if (!isset($utf8_pcre)) {
        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
        $utf8_pcre = @preg_match('/^./u', 'a');
    }
    // We can't demand utf8 in the PCRE installation, so just return the string in those cases.
    if (!$utf8_pcre) {
        return $text;
    }

    // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- preg_match fails when it encounters invalid UTF8 in $text.
    if (1 === @preg_match('/^./us', $text)) {
        return $text;
    }

    // Attempt to strip the bad chars if requested (not recommended).
    if ($strip && function_exists('iconv')) {
        return iconv('utf-8', 'utf-8', $text);
    }

    return '';
}

function get_listing_data($dir = false)
{
    $ignore_files = [
        '.htaccess',
        '.',
        '..',
        '.DS_Store',
    ];
    $files = $dirs = [];
    if (!$dir) {
        $current_path = config('home_path');
        $relative_path = null;
    } else {
        $current_path = config('home_path') . '/' . $dir;
        $relative_path = $dir;
    }

    $data = [];
    $directory = dir($current_path);
    while (false !== ($entry = $directory->read())) {
        // Skip ignored files.
        if (in_array($entry, $ignore_files, true)) {
            continue;
        }
        // Full path to file/dir.
        $entry_path = $current_path . '/' . $entry;

        // Add file/dir to array.
        if (is_file($entry_path)) {
            $files[$entry] = array(
                'relative_path' => trim($relative_path . '/' . $entry, '/'),
                'full_path' => $entry_path,
                'name' => $entry,
            );
        } elseif (is_dir($entry_path) && $entry !== '_old-projects_') {
            $dirs[$entry] = array(
                'relative_path' => trim($relative_path . '/' . $entry, '/'),
                'full_path' => $entry_path,
                'name' => $entry,
            );
        }
    }
    $directory->close();

    // Sort files and folders in ASC order
    asort($files);
    asort($dirs);
    $data['files'] = $files;
    $data['folders'] = $dirs;
    return $data;
}
<?php

/**
 * Class AppShared
 *
 * @package WebserverHome
 */
class AppShared {
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Get the singleton instance.
     */
    public static function get_instance() : ?self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
    }

    public static function getPublicBackendConfig() : void {
        send_json_success(
            [
                'projects_root_path' => config( 'path_to_projects_root' ),
            ]
        );
    }
}

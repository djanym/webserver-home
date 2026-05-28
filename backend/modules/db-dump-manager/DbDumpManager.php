<?php

declare(strict_types=1);

namespace WebserverHome;

use JsonException;

class DbDumpManager extends Generic {
    private static ?self $instance = null;

    public static function get_instance() : ?self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create a placeholder DB dump file and register it in the project registry.
     *
     * @param string $project_slug
     *
     * @return array|false
     */
    public function createDbDump( string $project_slug ) : array|false {
        $project = $this->getProjectData( $project_slug );
        if ( ! $project ) {
            return false;
        }

        $registry_path = (string) ( $project['registered_registry_path'] ?? '' );
        $dump_dir      = $this->getProjectDbDumpDirectory( $project );
        $created_at    = gmdate( 'c' );
        $file_name     = $this->buildDbDumpFileName( $project, $created_at );
        $file_path     = normalizePath( $dump_dir . '/' . $file_name );
        $placeholder   = "-- DB dump placeholder\n-- project: {$project_slug}\n-- created_at: {$created_at}\n";

        if ( '' === $registry_path ) {
            $this->error->add( 'project_registry', 'Project registry path is empty.' );

            return false;
        }

        if ( '' === $dump_dir ) {
            $this->error->add( 'db_dump_directory', 'DB dump directory is not configured.' );

            return false;
        }

        if ( ! is_dir( $dump_dir ) && ! createDirectory( $dump_dir ) ) {
            $this->error->add( 'db_dump_directory', 'Failed to create DB dump directory.' );

            return false;
        }

        if ( false === file_put_contents( $file_path, $placeholder, LOCK_EX ) ) {
            $this->error->add( 'db_dump_file', 'Failed to create DB dump file.' );

            return false;
        }

        $size = filesize( $file_path );
        if ( false === $size ) {
            $size = strlen( $placeholder );
        }

        $registry = $this->readProjectRegistryData( $registry_path );
        if ( ! $registry ) {
            $this->error->add( 'project_registry', 'Failed to read project registry file.' );

            return false;
        }

        if ( ! isset( $registry['db_dumps'] ) || ! is_array( $registry['db_dumps'] ) ) {
            $registry['db_dumps'] = [];
        }

        $record = [
            'file_name'  => $file_name,
            'created_at' => $created_at,
            'size'       => (int) $size,
        ];

        array_unshift( $registry['db_dumps'], $record );
        $registry['updated_at'] = $created_at;

        if ( ! $this->writeProjectRegistryData( $registry_path, $registry ) ) {
            $this->error->add( 'project_registry', 'Failed to update project registry file.' );

            return false;
        }

        $project['db_dumps']    = $registry['db_dumps'];
        $project['updated_at']  = $created_at;

        return [
            'project' => $project,
            'dump'    => $record,
        ];
    }

    /**
     * Get all DB dumps for a project.
     *
     * @param string $project_slug
     *
     * @return array|false
     */
    public function getProjectDbDumps( string $project_slug ) : array|false {
        $project = $this->getProjectData( $project_slug );
        if ( ! $project ) {
            return false;
        }

        $dumps = $project['db_dumps'] ?? [];
        if ( ! is_array( $dumps ) ) {
            $dumps = [];
        }

        return [
            'project'  => $project,
            'db_dumps' => array_values( $dumps ),
            'count'    => count( $dumps ),
        ];
    }

    /**
     * Stream a project DB dump file to the browser.
     *
     * @param string $project_slug
     * @param string $file_name
     *
     * @return bool
     */
    public function downloadDbDump( string $project_slug, string $file_name ) : bool {
        $project = $this->getProjectData( $project_slug );
        if ( ! $project ) {
            return false;
        }

        $record = $this->getDbDumpRecord( $project, $file_name );
        if ( ! $record ) {
            $this->error->add( 'db_dump_file', 'DB dump file was not found.' );

            return false;
        }

        $dump_dir  = $this->getProjectDbDumpDirectory( $project );
        $file_path = normalizePath( $dump_dir . '/' . $record['file_name'] );

        if ( ! file_exists( $file_path ) ) {
            $this->error->add( 'db_dump_file', 'DB dump file does not exist.' );

            return false;
        }

        while ( ob_get_level() > 0 ) {
            ob_end_clean();
        }

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' . basename( $record['file_name'] ) . '"' );
        header( 'Content-Length: ' . (string) filesize( $file_path ) );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Pragma: public' );

        readfile( $file_path );

        return true;
    }

    private function getProjectData( string $project_slug ) : ?array {
        $project_slug = trim( $project_slug );
        if ( '' === $project_slug ) {
            $this->error->add( 'project_slug', 'Project slug is empty.' );

            return null;
        }

        $manager = ProjectsManager::get_instance();
        if ( ! $manager ) {
            $this->error->add( 'project', 'Projects manager is not available.' );

            return null;
        }

        $project = $manager->getProject( $project_slug );
        if ( ! $project ) {
            $this->error->add( 'project', 'Project not found.' );

            return null;
        }

        return $project;
    }

    private function getProjectRegistryPath( array $project ) : string {
        return normalizePath( trim( (string) ( $project['registered_registry_path'] ?? '' ) ) );
    }

    private function getProjectDbDumpDirectory( array $project ) : string {
        $project_root = trim( (string) ( $project['registered_root_path'] ?? $project['project_root_path'] ?? '' ) );
        if ( '' === $project_root ) {
            return '';
        }

        $folders_structure = config( 'project_folders_structure', [] );
        $dump_folder       = 'db-dump';

        if ( is_array( $folders_structure ) && ! empty( $folders_structure['db-dump'] ) ) {
            $dump_folder = (string) $folders_structure['db-dump'];
        }

        return normalizePath( $project_root . '/' . trim( $dump_folder, "\/" ) );
    }

    private function getFileNameFormat() : string {
        $format = trim( (string) config( 'db-dump-file-format', '' ) );

        return '' !== $format ? $format : 'db-dump-%project-slug%-%created-at%.sql';
    }

    private function buildDbDumpFileName( array $project, string $created_at ) : string {
        $slug         = trim( (string) ( $project['slug'] ?? '' ) );
        $created_at_ts = gmdate( 'YmdHis', strtotime( $created_at ) ?: time() );
        $file_name    = str_replace(
            [ '%project-slug%', '%created-at%' ],
            [ $slug, $created_at_ts ],
            $this->getFileNameFormat()
        );

        return $this->normalizeFileName( $file_name );
    }

    private function normalizeFileName( string $file_name ) : string {
        $file_name = trim( $file_name );
        $file_name = preg_replace( '/[^A-Za-z0-9._-]+/', '-', $file_name ) ?: $file_name;

        return trim( $file_name, '-.' );
    }

    private function readProjectRegistryData( string $registry_path ) : ?array {
        $json = file_get_contents( $registry_path );
        if ( false === $json || '' === trim( $json ) ) {
            return null;
        }

        try {
            $decoded = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
        } catch ( JsonException ) {
            return null;
        }

        return is_array( $decoded ) ? $decoded : null;
    }

    private function writeProjectRegistryData( string $registry_path, array $registry ) : bool {
        try {
            $json = json_encode( $registry, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        } catch ( JsonException ) {
            return false;
        }

        return false !== file_put_contents( $registry_path, $json, LOCK_EX );
    }

    private function getDbDumpRecord( array $project, string $file_name ) : ?array {
        $dumps = $project['db_dumps'] ?? [];
        if ( ! is_array( $dumps ) ) {
            return null;
        }

        $target_file = trim( $file_name );
        foreach ( $dumps as $dump ) {
            if ( ! is_array( $dump ) ) {
                continue;
            }

            if ( (string) ( $dump['file_name'] ?? '' ) === $target_file ) {
                return $dump;
            }
        }

        return null;
    }
}

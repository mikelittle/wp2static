<?php
/*
    StaticSite

    The resulting output of crawling the WordPress site

    Site URLs are all made absolute for easier rewriting during deployment
*/

namespace WP2Static;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class StaticSite {

    /**
     * Add crawled resource to static site
     *
     */
    public static function add(string $path, string $contents) {
        // simple file save, Crawler holds logic for what/where to save
        // Crawler has already processed links, etc
        $full_path = self::getPath() . "$path";

        $directory = dirname( $full_path );

        // mkdir recursively
        if ( ! is_dir( $directory ) ) {
            mkdir( $directory, 0755, true );
        }

        file_put_contents( $full_path, $contents );
    }

    public static function getPath() {
        return SiteInfo::getPath( 'uploads') . 'wp2static-exported-site';
    }

    /**
     * Delete StaticSite files
     *
     */
    public static function delete() {
        WsLog::l('Deleting static site files');

        if ( is_dir( self::getPath() ) ) {
            FilesHelper::delete_dir_with_files( self::getPath() );
            
            // CrawlCache not useful without StaticSite files
            CrawlCache::truncate();
        }
    }

    /**
     *  Get all paths in StaticSite
     *
     *  @return string[] StaticSite paths
     */
    public static function getPaths() : array {
        global $wpdb;

        if ( ! is_dir( self::getPath() ) ) {
            return [];
        }

        $paths = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                self::getPath(),
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ( $iterator as $filename => $file_object ) {
            $base_name = basename( $filename );
            if ( $base_name != '.' && $base_name != '..' ) {
                $real_filepath = realpath( $filename );

                $paths[] = str_replace( self::getPath(), '', $real_filepath );
            }
        }

        return $paths;
    }
}


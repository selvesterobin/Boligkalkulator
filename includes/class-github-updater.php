<?php
/**
 * Boligkalkulator — GitHub Updater
 *
 * Lightweight, dependency-free updater that lets this plugin receive update
 * notifications and one-click updates inside wp-admin, sourced from GitHub
 * Releases. Public repos work with no configuration; for a PRIVATE repo add a
 * fine-grained token (Contents: read-only) to wp-config.php:
 *
 *   define('NXTWEB_GITHUB_TOKEN', 'github_pat_xxx');
 *
 * The class name is unique per plugin so several NXTweb plugins can each ship
 * their own copy without colliding when active on the same site.
 *
 * @package Boligkalkulator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Boligkalkulator_GitHub_Updater' ) ) {

    class Boligkalkulator_GitHub_Updater {

        /** @var string Plugin basename (folder/file.php). */
        private $slug;

        /** @var string Human readable name shown in the update UI. */
        private $name;

        /** @var string Currently installed version. */
        private $version;

        /** @var string GitHub repository, e.g. "owner/repo". */
        private $repo;

        /** @var string Exact release asset filename to install. */
        private $asset_file;

        /** @var string Control-plane component slug (cloud mode). */
        private $component;

        /** @var string Optional wp-config constant holding a GitHub token. */
        private $token_const;

        /** @var string Filter name to resolve a GitHub token. */
        private $token_filter;

        /** @var string Transient cache key for the latest release payload. */
        private $cache_key;

        /** @var int Cache lifetime for the release lookup. */
        private $cache_ttl;

        /**
         * @param array $args {
         *   @type string $slug         Plugin basename (folder/file.php).
         *   @type string $name         Display name.
         *   @type string $version      Installed version.
         *   @type string $repo         "owner/repo".
         *   @type string $asset_file   Exact release asset filename.
         *   @type string $token_const  Optional constant name for a GitHub token.
         *   @type string $token_filter Optional filter name for a GitHub token.
         * }
         */
        public function __construct( array $args ) {
            $this->slug         = $args['slug'];
            $this->name         = $args['name'];
            $this->version      = $args['version'];
            $this->repo         = $args['repo'];
            $this->asset_file   = $args['asset_file'];
            $this->component    = isset( $args['component'] ) ? $args['component'] : '';
            $this->token_const  = isset( $args['token_const'] ) ? $args['token_const'] : 'NXTWEB_GITHUB_TOKEN';
            $this->token_filter = isset( $args['token_filter'] ) ? $args['token_filter'] : 'nxtweb_github_token';
            // Matches the shorter cache already used by the AI Page Builder/
            // Theme's own updater — keeps "how soon do I see a new update"
            // consistent across every NXTweb product instead of some plugins
            // feeling instant and others taking hours.
            $this->cache_ttl    = isset( $args['cache_ttl'] ) ? (int) $args['cache_ttl'] : 10 * MINUTE_IN_SECONDS;
            $channel            = $this->use_prerelease() ? 'beta' : 'stable';
            $mode               = $this->use_cloud() ? 'cloud' : 'gh';
            $this->cache_key    = 'nxtweb_gh_' . md5( $this->repo . '|' . $this->slug . '|' . $channel . '|' . $mode );

            add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_plugin_update' ] );
            add_filter( 'plugins_api', [ $this, 'plugin_info' ], 20, 3 );
            add_filter( 'http_request_args', [ $this, 'authorize_asset_download' ], 10, 2 );
            add_action( 'upgrader_process_complete', [ $this, 'flush_cache' ], 10, 0 );
            add_filter( 'upgrader_source_selection', [ $this, 'keep_folder_name' ], 10, 4 );
        }

        /**
         * The release zip unpacks to "boligkalkulator/". Sites installed from
         * a GitHub download may use another folder name, so rename the
         * unpacked folder to the installed one to keep the plugin active.
         */
        public function keep_folder_name( $source, $remote_source, $upgrader, $hook_extra = array() ) {
            global $wp_filesystem;
            if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->slug || ! $wp_filesystem ) {
                return $source;
            }
            $want = trailingslashit( $remote_source ) . dirname( $this->slug ) . '/';
            if ( untrailingslashit( $source ) === untrailingslashit( $want ) ) {
                return $source;
            }
            return $wp_filesystem->move( $source, $want ) ? $want : $source;
        }

        /**
         * Resolve the GitHub access token from a constant, the shared NXTweb
         * constant, or a filter. Empty for public repositories.
         *
         * @return string
         */
        private function get_token() {
            if ( $this->token_const && defined( $this->token_const ) && constant( $this->token_const ) ) {
                return (string) constant( $this->token_const );
            }
            if ( defined( 'NXTWEB_GITHUB_TOKEN' ) && NXTWEB_GITHUB_TOKEN ) {
                return (string) NXTWEB_GITHUB_TOKEN;
            }

            return (string) apply_filters( $this->token_filter, '' );
        }

        /**
         * Control-plane license key (nxtweb_live_...). When present, updates are
         * fetched through the NXTweb control plane instead of GitHub directly, so
         * the customer needs no GitHub token in wp-config.php. Sourced from a
         * constant, a shared option set once in wp-admin, or a filter.
         *
         * @return string
         */
        private function cloud_key() {
            if ( defined( 'NXTWEB_CLOUD_TOKEN' ) && NXTWEB_CLOUD_TOKEN ) {
                return (string) NXTWEB_CLOUD_TOKEN;
            }

            // Shared NXTweb license key from the AI theme/plugin. One valid
            // license set there automatically covers all NXTweb plugins.
            $ai = get_option( 'nxtweb_ai_settings', array() );
            if ( is_array( $ai ) ) {
                if ( ! empty( $ai['ai_cloud_key'] ) ) {
                    return trim( (string) $ai['ai_cloud_key'] );
                }
                if ( ! empty( $ai['cloud_token'] ) ) {
                    return trim( (string) $ai['cloud_token'] );
                }
            }

            $opt = get_option( 'nxtweb_cloud_key', '' );
            if ( is_string( $opt ) && $opt !== '' ) {
                return trim( $opt );
            }

            return (string) apply_filters( 'nxtweb_cloud_key', '' );
        }

        /**
         * Base URL of the control plane (no trailing slash).
         *
         * @return string
         */
        private function cloud_url() {
            if ( defined( 'NXTWEB_CLOUD_URL' ) && NXTWEB_CLOUD_URL ) {
                return rtrim( (string) NXTWEB_CLOUD_URL, '/' );
            }

            return rtrim( (string) apply_filters( 'nxtweb_cloud_url', 'https://nxtweb-control.fly.dev' ), '/' );
        }

        /**
         * Whether to update via the control-plane proxy. True when a license key
         * is present and this updater has a control-plane component slug.
         *
         * @return bool
         */
        private function use_cloud() {
            return $this->component !== '' && $this->cloud_key() !== '';
        }

        /**
         * Whether this site is opted in to the pre-release (beta) channel.
         *
         * @return bool
         */
        private function use_prerelease() {
            if ( defined( 'NXTWEB_GITHUB_PRERELEASE' ) && NXTWEB_GITHUB_PRERELEASE ) {
                return true;
            }

            return (bool) apply_filters( 'nxtweb_github_prerelease', false );
        }

        /**
         * Fetch (and cache) the relevant GitHub release payload.
         *
         * @return array
         */
        private function get_release() {
            if ( $this->use_cloud() ) {
                return $this->get_release_cloud();
            }

            $cached = get_transient( $this->cache_key );
            if ( is_array( $cached ) ) {
                return $cached;
            }

            $beta    = $this->use_prerelease();
            $url     = $beta
                ? 'https://api.github.com/repos/' . $this->repo . '/releases?per_page=10'
                : 'https://api.github.com/repos/' . $this->repo . '/releases/latest';
            $headers = [
                'Accept'     => 'application/vnd.github+json',
                'User-Agent' => 'NXTweb-Updater',
            ];
            $token = $this->get_token();
            if ( $token ) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }

            $response = wp_remote_get( $url, [
                'headers' => $headers,
                'timeout' => 20,
            ] );

            if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
                // Short negative cache so a failing/rate-limited API does not hammer GitHub.
                set_transient( $this->cache_key, [], 15 * MINUTE_IN_SECONDS );
                return [];
            }

            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! is_array( $data ) ) {
                $data = [];
            }

            if ( $beta ) {
                // List endpoint returns an array; pick the most recent non-draft
                // release that actually carries our install asset.
                $picked = [];
                foreach ( $data as $release ) {
                    if ( ! is_array( $release ) || ! empty( $release['draft'] ) ) {
                        continue;
                    }
                    if ( $this->asset_url( $release ) !== '' ) {
                        $picked = $release;
                        break;
                    }
                }
                $data = $picked;
            }

            set_transient( $this->cache_key, $data, $this->cache_ttl );

            return $data;
        }

        /**
         * Fetch the release via the NXTweb control plane (cloud mode).
         *
         * The control plane authenticates the customer by their license key,
         * reads the (private) GitHub release on their behalf, and returns a
         * download URL that points back at the control plane.
         *
         * @return array
         */
        private function get_release_cloud() {
            $cached = get_transient( $this->cache_key );
            if ( is_array( $cached ) ) {
                return $cached;
            }

            $channel = $this->use_prerelease() ? 'beta' : 'stable';
            $url     = $this->cloud_url() . '/v1/update?component=' . rawurlencode( $this->component )
                     . '&channel=' . $channel;

            $response = wp_remote_get( $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->cloud_key(),
                    'Accept'        => 'application/json',
                    'User-Agent'    => 'NXTweb-Updater',
                    'X-NXTweb-Site' => home_url( '/' ),
                ],
                'timeout' => 20,
            ] );

            if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
                set_transient( $this->cache_key, [], 15 * MINUTE_IN_SECONDS );
                return [];
            }

            $payload = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! is_array( $payload ) || empty( $payload['version'] ) ) {
                set_transient( $this->cache_key, [], 15 * MINUTE_IN_SECONDS );
                return [];
            }

            $data = [
                'tag_name'       => 'v' . $payload['version'],
                'html_url'       => isset( $payload['url'] ) ? (string) $payload['url'] : '',
                'body'           => isset( $payload['changelog'] ) ? (string) $payload['changelog'] : '',
                '_cloud_package' => isset( $payload['package'] ) ? (string) $payload['package'] : '',
            ];

            set_transient( $this->cache_key, $data, $this->cache_ttl );

            return $data;
        }

        /**
         * Normalised version number from the release tag (strips a leading "v").
         *
         * @param array $release
         * @return string
         */
        private function release_version( array $release ) {
            $tag = isset( $release['tag_name'] ) ? (string) $release['tag_name'] : '';
            return ltrim( $tag, 'vV' );
        }

        /**
         * Locate the GitHub API asset URL for this plugin's release asset.
         *
         * Returns the API asset URL (not browser_download_url) so private repos
         * can be authorised via authorize_asset_download().
         *
         * @param array $release
         * @return string
         */
        private function asset_url( array $release ) {
            if ( ! empty( $release['_cloud_package'] ) ) {
                return (string) $release['_cloud_package'];
            }

            if ( empty( $release['assets'] ) || ! is_array( $release['assets'] ) ) {
                return '';
            }

            foreach ( $release['assets'] as $asset ) {
                if ( isset( $asset['name'] ) && $asset['name'] === $this->asset_file && ! empty( $asset['url'] ) ) {
                    return (string) $asset['url'];
                }
            }

            return '';
        }

        /**
         * Add auth + octet-stream Accept headers when downloading our release asset.
         *
         * @param array  $args
         * @param string $url
         * @return array
         */
        public function authorize_asset_download( $args, $url ) {
            // Cloud mode: authorise the control-plane download with the license key.
            if ( is_string( $url ) && strpos( $url, '/v1/update/download' ) !== false ) {
                if ( ! isset( $args['headers'] ) || ! is_array( $args['headers'] ) ) {
                    $args['headers'] = [];
                }
                $key = $this->cloud_key();
                if ( $key ) {
                    $args['headers']['Authorization'] = 'Bearer ' . $key;
                }
                $args['headers']['Accept']        = 'application/octet-stream';
                $args['headers']['User-Agent']    = 'NXTweb-Updater';
                $args['headers']['X-NXTweb-Site'] = home_url( '/' );
                return $args;
            }

            $needle = 'api.github.com/repos/' . $this->repo . '/releases/assets/';
            if ( is_string( $url ) && strpos( $url, $needle ) !== false ) {
                if ( ! isset( $args['headers'] ) || ! is_array( $args['headers'] ) ) {
                    $args['headers'] = [];
                }
                $token = $this->get_token();
                if ( $token ) {
                    $args['headers']['Authorization'] = 'Bearer ' . $token;
                }
                $args['headers']['Accept']     = 'application/octet-stream';
                $args['headers']['User-Agent'] = 'NXTweb-Updater';
            }

            return $args;
        }

        /**
         * Plugin update check.
         *
         * @param mixed $transient
         * @return mixed
         */
        public function check_plugin_update( $transient ) {
            if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
                return $transient;
            }

            $release = $this->get_release();
            $new     = $this->release_version( $release );
            if ( $new === '' ) {
                return $transient;
            }

            $item = (object) [
                'id'          => $this->repo,
                'slug'        => dirname( $this->slug ),
                'plugin'      => $this->slug,
                'new_version' => $new,
                'url'         => isset( $release['html_url'] ) ? $release['html_url'] : '',
                'package'     => $this->asset_url( $release ),
                'tested'      => '',
                'icons'       => [],
                'banners'     => [],
            ];

            if ( version_compare( $new, $this->version, '>' ) && $item->package ) {
                $transient->response[ $this->slug ] = $item;
            } else {
                unset( $transient->response[ $this->slug ] );
                $transient->no_update[ $this->slug ] = $item;
            }

            return $transient;
        }

        /**
         * Populate the "View details" modal for the plugin.
         *
         * @param mixed  $result
         * @param string $action
         * @param object $args
         * @return mixed
         */
        public function plugin_info( $result, $action, $args ) {
            if ( $action !== 'plugin_information' ) {
                return $result;
            }
            if ( empty( $args->slug ) || $args->slug !== dirname( $this->slug ) ) {
                return $result;
            }

            $release = $this->get_release();
            if ( empty( $release ) ) {
                return $result;
            }

            $changelog = isset( $release['body'] ) ? (string) $release['body'] : '';

            return (object) [
                'name'          => $this->name,
                'slug'          => dirname( $this->slug ),
                'version'       => $this->release_version( $release ),
                'author'        => '<a href="https://nxtweb.no">NXTweb</a>',
                'homepage'      => isset( $release['html_url'] ) ? $release['html_url'] : '',
                'download_link' => $this->asset_url( $release ),
                'sections'      => [
                    'changelog' => $changelog !== '' ? wpautop( esc_html( $changelog ) ) : '',
                ],
            ];
        }

        /**
         * Clear the cached release lookup.
         */
        public function flush_cache() {
            delete_transient( $this->cache_key );
        }
    }
}

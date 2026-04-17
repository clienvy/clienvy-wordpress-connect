<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Clienvy_Updater {

	private const CACHE_KEY      = 'clienvy_github_release';
	private const CACHE_DURATION = 12 * HOUR_IN_SECONDS;

	private string $plugin_file;
	private string $plugin_slug;
	private string $github_repo;

	public function __construct( string $plugin_file, string $github_repo ) {
		$this->plugin_file = $plugin_file;
		$this->plugin_slug = plugin_basename( $plugin_file );
		$this->github_repo = $github_repo;

		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'inject_update' ] );
		add_filter( 'plugins_api', [ $this, 'plugin_details' ], 10, 3 );
		add_filter( 'upgrader_post_install', [ $this, 'fix_folder_name' ], 10, 3 );
	}

	// ── WordPress update transient ────────────────────────────────────────────

	public function inject_update( object $transient ): object {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $transient;
		}

		$remote_version = ltrim( $release->tag_name, 'v' );

		if ( version_compare( CLIENVY_VERSION, $remote_version, '<' ) ) {
			$transient->response[ $this->plugin_slug ] = $this->build_update_object( $release, $remote_version );
		} else {
			// Tell WordPress we are up to date (prevents false "update available" notices)
			$transient->no_update[ $this->plugin_slug ] = $this->build_update_object( $release, $remote_version );
		}

		return $transient;
	}

	// ── Plugin details popup ──────────────────────────────────────────────────

	/**
	 * @param mixed $result
	 * @return mixed
	 */
	public function plugin_details( $result, string $action, object $args ) {
		if ( $action !== 'plugin_information' ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || $args->slug !== dirname( $this->plugin_slug ) ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $result;
		}

		$remote_version = ltrim( $release->tag_name, 'v' );

		$info                = new stdClass();
		$info->name          = 'Clienvy Connect';
		$info->slug          = dirname( $this->plugin_slug );
		$info->version       = $remote_version;
		$info->author        = '<a href="https://clienvy.io">Clienvy</a>';
		$info->homepage      = 'https://github.com/' . $this->github_repo;
		$info->requires      = '6.0';
		$info->tested        = get_bloginfo( 'version' );
		$info->last_updated  = $release->published_at;
		$info->download_link = $this->get_download_url( $release );
		$info->sections      = [
			'description' => 'Verbind WordPress met Clienvy om gebruik te kunnen maken van One-Click Login.',
			'changelog'   => $this->format_changelog( $release->body ?? '' ),
		];

		return $info;
	}

	// ── Post-install: fix folder name ─────────────────────────────────────────

	/**
	 * @param bool|WP_Error $response
	 * @return bool|WP_Error
	 */
	public function fix_folder_name( $response, array $hook_extra, array $result ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ( $hook_extra['plugin'] ?? '' ) !== $this->plugin_slug ) {
			return $response;
		}

		global $wp_filesystem;

		$plugin_dir    = trailingslashit( WP_PLUGIN_DIR . '/' . dirname( $this->plugin_slug ) );
		$extracted_dir = trailingslashit( $result['destination'] );

		if ( $extracted_dir === $plugin_dir ) {
			return $response;
		}

		if ( $wp_filesystem->exists( $plugin_dir ) ) {
			$wp_filesystem->delete( $plugin_dir, true );
		}

		$wp_filesystem->move( $extracted_dir, $plugin_dir );

		$result['destination'] = $plugin_dir;

		// Clear caches so the just-installed version is used on the next check
		delete_transient( self::CACHE_KEY );
		delete_site_transient( 'update_plugins' );

		// Re-activate the plugin after update
		activate_plugin( $this->plugin_slug );

		return $response;
	}

	// ── GitHub API ────────────────────────────────────────────────────────────

	private function get_latest_release(): ?object {
		$cached = get_transient( self::CACHE_KEY );
		if ( $cached !== false ) {
			return $cached ?: null;
		}

		$response = wp_remote_get(
			"https://api.github.com/repos/{$this->github_repo}/releases/latest",
			[
				'timeout' => 10,
				'headers' => [
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
				],
			]
		);

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			// Cache a falsy value briefly so we don't hammer the API on errors
			set_transient( self::CACHE_KEY, 0, 5 * MINUTE_IN_SECONDS );
			return null;
		}

		$release = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $release->tag_name ) ) {
			set_transient( self::CACHE_KEY, 0, 5 * MINUTE_IN_SECONDS );
			return null;
		}

		set_transient( self::CACHE_KEY, $release, self::CACHE_DURATION );

		return $release;
	}

	private function get_download_url( object $release ): string {
		// Prefer a zip asset named "clienvy-connect.zip" attached to the release
		if ( ! empty( $release->assets ) ) {
			foreach ( $release->assets as $asset ) {
				if ( substr( $asset->name, -4 ) === '.zip' ) {
					return $asset->browser_download_url;
				}
			}
		}

		// Fall back to the automatic GitHub source zip
		return $release->zipball_url;
	}

	private function build_update_object( object $release, string $version ): object {
		$obj              = new stdClass();
		$obj->slug        = dirname( $this->plugin_slug );
		$obj->plugin      = $this->plugin_slug;
		$obj->new_version = $version;
		$obj->url         = 'https://github.com/' . $this->github_repo;
		$obj->package     = $this->get_download_url( $release );
		$obj->icons       = [];
		$obj->banners     = [];
		$obj->tested      = get_bloginfo( 'version' );
		$obj->requires_php = '7.4';

		return $obj;
	}

	private function format_changelog( string $markdown ): string {
		if ( empty( $markdown ) ) {
			return '<p>Zie de <a href="https://github.com/' . esc_html( $this->github_repo ) . '/releases" target="_blank">GitHub releases</a> pagina voor de changelog.</p>';
		}

		// Basic markdown → HTML for the WP modal
		$html = esc_html( $markdown );
		$html = preg_replace( '/^### (.+)$/m', '<h3>$1</h3>', $html );
		$html = preg_replace( '/^## (.+)$/m', '<h2>$1</h2>', $html );
		$html = preg_replace( '/^- (.+)$/m', '<li>$1</li>', $html );
		$html = preg_replace( '/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html );
		$html = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html );

		return $html;
	}
}

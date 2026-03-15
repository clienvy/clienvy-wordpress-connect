<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Clienvy_Settings {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_clienvy_reveal_secret', [ $this, 'ajax_reveal_secret' ] );
		add_action( 'wp_ajax_clienvy_reset_secret', [ $this, 'ajax_reset_secret' ] );
	}

	public function add_menu(): void {
		$connected = ! empty( get_option( 'clienvy_settings', [] )['website_id'] );
		$dot_color = $connected ? '#22c55e' : '#ef4444';
		$dot       = ' <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:' . $dot_color . ';margin-left:5px;vertical-align:middle;position:relative;top:-1px;"></span>';

		add_menu_page(
			'Clienvy Connect',
			'Clienvy',
			'manage_options',
			'clienvy-connect',
			[ $this, 'render_page' ],
			'data:image/svg+xml;base64,' . base64_encode( '<svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg" id="fi_11936494"><g clip-rule="evenodd" fill="rgb(0,0,0)" fill-rule="evenodd"><path d="m15.3936 5.21039c-.8902.13488-1.6132.57001-1.8633.82006l-.9696.96968 4.4393 4.43937.9697-.9697c-.0001 0 0 0 0 0 .25-.2501.6851-.97311.82-1.86329.1289-.8509-.0245-1.78052-.82-2.57605-.7956-.79554-1.7252-.94899-2.5761-.82007zm-.2247-1.48307c1.2116-.18358 2.657.038 3.8614 1.24248 1.2045 1.20447 1.4261 2.64982 1.2425 3.86142-.1776 1.17228-.7425 2.19928-1.2425 2.69918l-1.5 1.5001c-.2929.2929-.7677.2929-1.0606 0l-5.5-5.50004c-.1407-.14065-.2197-.33142-.2197-.53033s.079-.38968.2197-.53033l1.5-1.49999c.4999-.49999 1.5269-1.06488 2.6992-1.24249z"></path><path d="m21.5303 2.46967c.2929.29289.2929.76777 0 1.06066l-2.5 2.5c-.2929.29289-.7677.29302-1.0606.00013s-.2929-.7679 0-1.06079l2.5-2.5c.2929-.29289.7677-.29289 1.0606 0z"></path><path d="m6.99962 10.75c.19891 0 .38968.079.53033.2197l5.49995 5.5c.2929.2929.2929.7677 0 1.0606l-1.4999 1.5c-.5.5-1.527 1.0649-2.69928 1.2425-1.2116.1836-2.65696-.038-3.86143-1.2425m0 0c-1.20447-1.2044-1.42603-2.6498-1.24246-3.8614.17762-1.1723.7425-2.1992 1.24244-2.6992l1.50002-1.5c.14065-.1407.33142-.2197.53033-.2197m0 1.8107-.96965.9696c-.00001 0 0 0 0 0-.25006.2501-.68519.9731-.82007 1.8633-.12892.8509.02452 1.7805.82005 2.5761.79553.7955 1.72517.949 2.57606.8201.89021-.1349 1.61319-.5701 1.86329-.8201l.9697-.9697z"></path><path d="m14.0303 13.4697c.2929.2929.2929.7677 0 1.0606l-2 2c-.2929.2929-.7677.2929-1.0606 0s-.2929-.7677 0-1.0606l2-2c.2929-.2929.7677-.2929 1.0606 0z"></path><path d="m6.02995 17.9697c.29289.2929.29327.7677.00038 1.0606l-2.5 2.5c-.29289.2929-.76777.2929-1.06066 0s-.29289-.7677 0-1.0606l2.5-2.5c.29289-.2929.76739-.2929 1.06028 0z"></path><path d="m10.5303 9.96967c.2929.29293.2929.76773 0 1.06063l-1.99997 2c-.29289.2929-.76777.2929-1.06066 0s-.29289-.7677 0-1.0606l2-2.00003c.29289-.29289.76773-.29289 1.06063 0z"></path></g></svg>' ),
			60
		);

		add_submenu_page(
			'clienvy-connect',
			'Clienvy Connect',
			'Clienvy Connect' . $dot,
			'manage_options',
			'clienvy-connect',
			[ $this, 'render_page' ]
		);

	}

	public function enqueue_scripts( string $hook ): void {
		if ( $hook !== 'toplevel_page_clienvy-connect' ) {
			return;
		}
		wp_enqueue_style( 'clienvy-admin', CLIENVY_PLUGIN_URL . 'assets/css/admin.css', [], CLIENVY_VERSION );
		wp_add_inline_style( 'clienvy-admin', "@font-face { font-family: 'Circular Std'; font-weight: 400; src: url('" . esc_url( CLIENVY_PLUGIN_URL . 'assets/fonts/circular-std-400.woff' ) . "') format('woff'); }" );
		wp_enqueue_script( 'clienvy-admin', CLIENVY_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery' ], CLIENVY_VERSION, true );
		wp_localize_script( 'clienvy-admin', 'clienvyAdmin', [
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'clienvy_admin_nonce' ),
			'confirmReset' => 'Weet je zeker dat je de connection secret wilt resetten? Na het resetten is Clienvy niet meer verbonden. Gebruikers kunnen dan tijdelijk niet via One-Click Login inloggen.',
		] );
	}

	// ── AJAX: reveal ─────────────────────────────────────────────────────────

	public function ajax_reveal_secret(): void {
		check_ajax_referer( 'clienvy_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		if ( get_option( 'clienvy_secret_revealed' ) ) {
			wp_send_json_error( 'Secret is al getoond.', 403 );
		}

		$secret = get_option( 'clienvy_connection_secret' );
		if ( ! $secret ) {
			$secret = clienvy_generate_secret();
			update_option( 'clienvy_connection_secret', $secret );
		}

		update_option( 'clienvy_secret_revealed', true );
		wp_send_json_success( [ 'secret' => $secret ] );
	}

	// ── AJAX: reset ───────────────────────────────────────────────────────────

	public function ajax_reset_secret(): void {
		check_ajax_referer( 'clienvy_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$secret = clienvy_generate_secret();
		update_option( 'clienvy_connection_secret', $secret );
		update_option( 'clienvy_secret_revealed', false );
		delete_option( 'clienvy_settings' );
		flush_rewrite_rules();

		wp_send_json_success( [ 'secret' => $secret ] );
	}

	// ── Page ──────────────────────────────────────────────────────────────────

	public function render_page(): void {
		$settings  = get_option( 'clienvy_settings', [] );
		$revealed  = (bool) get_option( 'clienvy_secret_revealed', false );
		$connected = ! empty( $settings['website_id'] );
		$org_name  = $settings['organization_name'] ?? '';

        $title = 'Koppelen met Clienvy';
        $intro = 'Koppelen deze site aan Clienvy om gebruik te maken van One-Click Login en om de styling van de login pagina te personaliseren. Om te verbinden met je de applicatie aanmaken in Clienvy en de onderstaande connection secret opgeven.';
        if($connected) {
            $title = 'Gekoppeld met Clienvy';
            $intro = 'Deze site is gekoppeld aan Clienvy, je kunt daarom gebruik maken van One-Click Login. Hieronder kun je Clienvy ontkoppelen door een nieuwe connection secret te genereren.';
        }
		?>
		<div class="clienvy-wrap">

			<div class="clienvy-page-header">
				<h1>Clienvy Connect</h1>
				<span class="clienvy-version">v<?php echo esc_html( CLIENVY_VERSION ); ?></span>
			</div>

			<div class="clienvy-card">
                <div class="clienvy-card-header">
                    <div class="clienvy-card-header-title">
                        <?php echo $title; ?>
                        <span class="clienvy-status-pill clienvy-status-pill--<?php echo $connected ? 'connected' : 'disconnected'; ?>">
                                <span class="clienvy-status-dot"></span>
                                <?php echo $connected ? ( $org_name ? esc_html( $org_name ) : 'Verbonden' ) : 'Niet verbonden'; ?>
                            </span>
                    </div>
                    <p class="clienvy-card-header-info"><?php echo $intro; ?></p>
                </div>
				<div class="clienvy-card-body">
					<div class="clienvy-secret-area">
						<span class="clienvy-field-label">Connection Secret</span>
						<?php if ( ! $revealed ) : ?>
							<div id="clienvy-state-hidden">
								<div class="clienvy-secret-masked">
									<span class="clienvy-masked-set">
										<span class="clienvy-secret-dots">&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</span>
									</span>
									<button id="clienvy-reveal-btn" class="clienvy-secret-renew clienvy-secret-renew--reveal">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
										Klik om te zien
									</button>
									<button id="clienvy-reset-btn" class="clienvy-secret-renew" title="Reset Connection Secret">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
										Resetten
									</button>
								</div>
							</div>
							<div id="clienvy-state-revealed" style="display:none;">
								<?php $this->render_secret_display(); ?>
							</div>
						<?php else : ?>
							<div id="clienvy-state-masked">
								<div class="clienvy-secret-masked">
									<span class="clienvy-masked-set">
										<span class="clienvy-secret-dots">&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</span>
									</span>
									<button id="clienvy-reset-btn" class="clienvy-secret-renew" title="Reset Connection Secret">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
										Resetten
									</button>
								</div>
							</div>
							<div id="clienvy-state-revealed" style="display:none;">
								<?php $this->render_secret_display(); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>

			</div>

		</div>
		<?php
	}

	private function render_secret_display(): void {
		?>
		<div class="clienvy-secret-display">
			<code id="clienvy-secret-value"></code>
			<button id="clienvy-copy-btn" class="clienvy-btn clienvy-btn-ghost">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
				Kopieer
			</button>
		</div>
		<p class="clienvy-warning">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
			Bewaar deze code veilig. Hij wordt niet opnieuw getoond tenzij je reset.
		</p>
		<?php
	}
}

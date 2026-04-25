<?php

if( !defined('ABSPATH') ) {
    exit;
}


class Clienvy_Login
{

    private array $settings;

    private bool $styling_enabled;


    public function __construct()
    {
        $this->settings = get_option('clienvy_settings', []);
        $this->styling_enabled = !empty($this->settings[ 'custom_login_styling_enabled' ]);

        add_action('login_head', [$this, 'inject_font']);
        add_action('login_enqueue_scripts', [$this, 'enqueue_styles']);

        if( $this->styling_enabled ) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_auth_check']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_auth_check']);
            add_action('login_head', [$this, 'inject_dynamic_styles']);
            add_filter('login_headerurl', [$this, 'logo_url']);
            add_filter('login_headertext', [$this, 'logo_title']);
            add_filter('login_message', [$this, 'render_login_heading']);
        }

        add_action('login_footer', [$this, 'render_sso_buttons']);

        if( !empty($this->settings[ 'custom_login_slug' ]) ) {
            add_action('init', [$this, 'register_login_route'], 1);
            add_action('init', [$this, 'maybe_block_wp_login'], 1);

            add_filter('login_url', [$this, 'override_login_url'], 10, 3);
            add_filter('site_url', [$this, 'rewrite_login_url'], 10, 4);
            add_filter('network_site_url', [$this, 'rewrite_login_url'], 10, 3);
            add_filter('wp_redirect', [$this, 'rewrite_login_url'], 10, 2);
        }
    }


    // ── Custom login slug ─────────────────────────────────────────────────────

    public function register_login_route(): void
    {
        global $pagenow;

        $slug = sanitize_title($this->settings[ 'custom_login_slug' ]);
        $req = parse_url($_SERVER[ 'REQUEST_URI' ] ?? '');
        $path = isset($req[ 'path' ]) ? untrailingslashit($req[ 'path' ]) : '';
        $base = untrailingslashit(parse_url(home_url(), PHP_URL_PATH) ?? '');

        if( $path === $base . '/' . $slug ) {
            $pagenow = 'wp-login.php';
            $user_login = '';
            $error = '';
            require ABSPATH . 'wp-login.php';
            exit;
        }
    }


    public function maybe_block_wp_login(): void
    {
        if( !isset($_SERVER[ 'REQUEST_URI' ]) ) {
            return;
        }
        if( defined('XMLRPC_REQUEST') || defined('DOING_AJAX') ) {
            return;
        }

        $path = parse_url($_SERVER[ 'REQUEST_URI' ], PHP_URL_PATH);
        if( !$path ) {
            return;
        }

        // Block direct wp-login.php access.
        if( preg_match('#/wp-login\.php#', $path) ) {
            $this->serve_404();
        }

        // Block unauthenticated wp-admin access — don't redirect, which would reveal the custom login URL.
        if( preg_match('#/wp-admin(/|$)#', $path) && !is_user_logged_in() ) {
            $this->serve_404();
        }
    }


    private function serve_404(): void
    {
        wp_redirect(home_url('/'), 302);
        exit;
    }


    public function override_login_url(string $login_url, string $redirect, bool $force_reauth): string
    {
        $slug = sanitize_title($this->settings[ 'custom_login_slug' ]);
        $url = home_url('/' . $slug . '/');
        if( $redirect ) {
            $url = add_query_arg('redirect_to', urlencode($redirect), $url);
        }
        if( $force_reauth ) {
            $url = add_query_arg('reauth', '1', $url);
        }
        return $url;
    }


    public function rewrite_login_url(string $url, ...$args): string
    {
        if( strpos($url, 'wp-login.php') !== false ) {
            $slug = sanitize_title($this->settings[ 'custom_login_slug' ]);
            $url = str_replace('wp-login.php', $slug, $url);
        }
        return $url;
    }


    // ── Font ──────────────────────────────────────────────────────────────────

    public function inject_font(): void
    {
        $font_300 = esc_url(CLIENVY_PLUGIN_URL . 'assets/fonts/circular-std-300.woff');
        $font_400 = esc_url(CLIENVY_PLUGIN_URL . 'assets/fonts/circular-std-400.woff');
        echo "<style>";
        echo "@font-face { font-family: 'Circular Std'; font-weight: 300; src: url('{$font_300}') format('woff'); }";
        echo "@font-face { font-family: 'Circular Std'; font-weight: 400; src: url('{$font_400}') format('woff'); }";
        echo "</style>\n";
    }


    // ── Styles (only when custom_login_styling_enabled = true) ────────────────────────

    public function enqueue_styles(): void
    {
        wp_enqueue_style('clienvy-sso', CLIENVY_PLUGIN_URL . 'frontend/styles/sso.css', [], CLIENVY_VERSION);

        if( $this->styling_enabled ) {
            wp_enqueue_style('clienvy-login', CLIENVY_PLUGIN_URL . 'frontend/styles/login.css', ['clienvy-sso'], CLIENVY_VERSION);
        }
    }


    public function enqueue_auth_check(): void
    {
        wp_enqueue_style('clienvy-auth-check', CLIENVY_PLUGIN_URL . 'frontend/styles/auth-check.css', [], CLIENVY_VERSION);
    }


    public function inject_dynamic_styles(): void
    {
        $color = $this->settings[ 'primary_color' ] ?? '#4A90E2';
        $logo = !empty($this->settings[ 'logo_url' ]) ? esc_url($this->settings[ 'logo_url' ]) : '';
        $custom_css = $this->settings[ 'custom_login_css' ] ?? '';

        echo "<style>\n";
        echo ":root { 
		--clienvy-auth-primary-color: {$color}; 
		--clienvy-auth-background-color: #fff;
		--clienvy-auth-button-background-color: {$color};
		--clienvy-auth-flat-button-background-color: #f7f7f7;
		--clienvy-auth-flat-button-color: #333;
		--clienvy-auth-button-color: #fff;
		--clienvy-auth-password-reset-color: #ccc;
		--clienvy-auth-password-reset-hover-color: #bbb;
		--clienvy-auth-separator-color: #ccc;
		--clienvy-auth-separator-border-color: #ddd;
		--clienvy-auth-input-label-color: #aaa;
		--clienvy-auth-input-background-color: #fff;
		--clienvy-auth-input-color: #222;
		--clienvy-auth-input-border-color: #f2f2f9;
		--clienvy-auth-input-box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.1);
		--clienvy-auth-text-color: #bbb;
		--clienvy-auth-strong-text-color: #999;
		--clienvy-auth-error-border-color: #f5f5f5;
		--clienvy-auth-error-border-left-color: #e89b9b;
		--clienvy-auth-error-color: #444;
		}\n";

        if( $logo ) {
            echo "#login h1 a { display: block !important; background-image: url('{$logo}'); }\n";
        }

        if( $custom_css ) {
            echo $custom_css . "\n";
        }

        echo "</style>\n";
    }


    // ── Logo link / title ─────────────────────────────────────────────────────

    public function logo_url(): string
    {
        return home_url('/');
    }


    public function logo_title(): string
    {
        return !empty($this->settings[ 'organization_name' ])
                ? esc_html($this->settings[ 'organization_name' ])
                : get_bloginfo('name');
    }


    public function render_login_heading(string $message): string
    {
        $action = $_REQUEST[ 'action' ] ?? 'login';
        if( in_array($action, ['lostpassword', 'retrievepassword', 'resetpass', 'rp', 'confirm_admin_email'], true)
                || ($_REQUEST[ 'checkemail' ] ?? '') === 'confirm' ) {
            return $message;
        }

        $html = '<div class="message"><p>' . esc_html(Clienvy_I18n::t('_admin._frontend._login.heading')) . '</p></div>';

        return $message . $html;
    }


    // ── SSO buttons ───────────────────────────────────────────────────────────

    public function render_sso_buttons(): void
    {
        $action = $_REQUEST[ 'action' ] ?? 'login';
        if( in_array($action, ['lostpassword', 'retrievepassword', 'resetpass', 'rp', 'confirm_admin_email'], true)
                || ($_REQUEST[ 'checkemail' ] ?? '') === 'confirm' ) {
            return;
        }

        $employee_url = $this->settings[ 'employee_portal_url' ] ?? '';
        $customer_url = $this->settings[ 'customer_portal_url' ] ?? '';
        $app_id = $this->settings[ 'website_application_id' ] ?? '';
        $customer_access = $this->settings[ 'customer_access' ] ?? true;

        // When customer_access is explicitly false, suppress the customer button.
        if( !$customer_access ) {
            $customer_url = '';
        }

        if( !$employee_url && !$customer_url ) {
            return;
        }

        $params = [
                'action' => 'wordpress_sso',
                'application_id' => $app_id,
        ];

        $employee_href = $employee_url ? add_query_arg($params, $employee_url) : '';
        $customer_href = $customer_url ? add_query_arg($params, $customer_url) : '';

        ?>
        <div id="clienvy-sso-mount" style="display:none;">
            <div class="clienvy-sso">
                <div class="clienvy-divider">
                    <span><?php echo esc_html(Clienvy_I18n::t('_admin._frontend._login.or')); ?></span></div>
                <div class="clienvy-sso-buttons">
                    <?php if( $employee_href ) : ?>
                        <a href="<?php echo esc_url($employee_href); ?>"
                           class="clienvy-sso-btn">
                            <?php echo esc_html(Clienvy_I18n::t('_admin._frontend._login.sso_employee')); ?>
                        </a>
                    <?php endif; ?>
                    <?php if( $customer_href ) : ?>
                        <a href="<?php echo esc_url($customer_href); ?>"
                           class="clienvy-sso-btn">
                            <?php echo esc_html(Clienvy_I18n::t('_admin._frontend._login.sso_customer')); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <script>
            (function () {
                var mount = document.getElementById('clienvy-sso-mount');
                var login = document.getElementById('login');
                if (mount && login) {
                    mount.style.display = '';
                    login.appendChild(mount);
                }
            })();
        </script>
        <?php
    }
}

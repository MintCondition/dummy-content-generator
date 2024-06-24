<?php

class GitHub_Updater {
    private $username;
    private $repo;
    private $plugin_file;
    private $plugin_data;
    private $github_response;
    private $access_token;

    public function __construct($username, $repo, $plugin_file, $access_token = '') {
    $this->username = $username;
    $this->repo = $repo;
    $this->plugin_file = $plugin_file;
    $this->access_token = $access_token;

    // Make sure we're in the admin before calling get_plugin_data
    if (is_admin()) {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $this->plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_file);
    }

    add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
    add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
    add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
}

    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $this->github_response = $this->get_repository_info();

        if ($this->github_response === false) {
            error_log('Failed to get GitHub repository info');
            return $transient;
        }

        $current_version = $this->plugin_data['Version'];
        $latest_version = $this->github_response->tag_name;

        if (version_compare($latest_version, $current_version, '>')) {
            $plugin_info = array(
                'slug' => dirname($this->plugin_file),
                'plugin' => $this->plugin_file,
                'new_version' => $latest_version,
                'url' => $this->plugin_data['PluginURI'],
                'package' => $this->github_response->zipball_url,
            );
            $transient->response[$this->plugin_file] = (object) $plugin_info;
        }

        return $transient;
    }

    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return false;
        }

        if (!empty($args->slug)) {
            if ($args->slug == dirname($this->plugin_file)) {
                $this->github_response = $this->get_repository_info();

                $plugin = array(
                    'name'              => $this->plugin_data["Name"],
                    'slug'              => $this->plugin_file,
                    'version'           => $this->github_response->tag_name,
                    'author'            => $this->plugin_data["AuthorName"],
                    'author_profile'    => $this->plugin_data["AuthorURI"],
                    'last_updated'      => $this->github_response->published_at,
                    'homepage'          => $this->plugin_data["PluginURI"],
                    'short_description' => $this->plugin_data["Description"],
                    'sections'          => array(
                        'Description'   => $this->plugin_data["Description"],
                        'Updates'       => $this->github_response->body,
                    ),
                    'download_link'     => $this->github_response->zipball_url
                );

                return (object) $plugin;
            }
        }

        return $result;
    }

    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        $install_directory = plugin_dir_path($this->plugin_file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;

        if ($this->active) {
            activate_plugin($this->plugin_file);
        }

        return $result;
    }

    public function get_repository_info() {
    if (!empty($this->github_response)) {
        return $this->github_response;
    }

    $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repo);
    
    $args = array(
        'headers' => array(
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress/' . get_bloginfo('version')
        )
    );
    
    if (!empty($this->access_token)) {
        $args['headers']['Authorization'] = 'token ' . $this->access_token;
    }

    $response = wp_remote_get($request_uri, $args);

    if (is_wp_error($response)) {
        error_log('GitHub API request failed: ' . $response->get_error_message());
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $response_headers = wp_remote_retrieve_headers($response);

    if ($response_code !== 200) {
        error_log('GitHub API request returned non-200 status code: ' . $response_code);
        error_log('Response body: ' . $response_body);
        error_log('Rate limit remaining: ' . $response_headers['x-ratelimit-remaining']);
        error_log('Rate limit reset: ' . date('Y-m-d H:i:s', $response_headers['x-ratelimit-reset']));
        return false;
    }

    $result = json_decode($response_body);

    if (!is_object($result)) {
        error_log('GitHub API response is not a valid JSON object');
        return false;
    }

    if (!isset($result->tag_name)) {
        error_log('GitHub API response does not contain a tag_name property');
        return false;
    }

    $this->github_response = $result;
    return $result;
}
}
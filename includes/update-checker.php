<?php
if (!class_exists('GitHub_Updater')) {

    class GitHub_Updater {
        private $username;
        private $repo;
        private $accessToken;

        function __construct($gitHubUsername, $gitHubProjectName, $accessToken = '') {
            error_log("GitHub_Updater constructor called");
            error_log("gitHubUsername: $gitHubUsername");
            error_log("gitHubProjectName: $gitHubProjectName");
            error_log("accessToken: $accessToken");

            $this->username = $gitHubUsername;
            $this->repo = $gitHubProjectName;
            $this->accessToken = $accessToken;

            // Add filters
            add_filter("pre_set_site_transient_update_plugins", array($this, "setTransient"));
        }

        public function getRepositoryInfo() {
            $url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases";
            if (!empty($this->accessToken)) {
                $url = add_query_arg(array('access_token' => $this->accessToken), $url);
            }

            error_log("Fetching repository info from URL: $url");
            $response = wp_remote_get($url);
            $githubAPIResult = wp_remote_retrieve_body($response);

            if (is_wp_error($response)) {
                error_log("Error fetching repository info: " . $response->get_error_message());
                return new WP_Error('api_error', $response->get_error_message());
            }

            error_log("Repo info retrieved successfully");
            $githubAPIResult = @json_decode($githubAPIResult);
            if (is_array($githubAPIResult)) {
                $githubAPIResult = $githubAPIResult[0];
            }

            error_log("Decoded GitHub API Result: " . print_r($githubAPIResult, true));
            return $githubAPIResult;
        }

        public function setTransient($transient) {
            error_log('Running setTransient');

            // Ensure transient is an object
            if (!is_object($transient)) {
                $transient = new stdClass();
            }

            // Fetch repository info
            $githubAPIResult = $this->getRepositoryInfo();

            if (is_wp_error($githubAPIResult)) {
                error_log('Error fetching repository info: ' . $githubAPIResult->get_error_message());
                return $transient;
            }

            // Define plugin file path and slug correctly
            $plugin_file_path = dirname(plugin_dir_path(__FILE__)) . '/dummy-content-main.php';
            error_log("Plugin file path: $plugin_file_path");
            $plugin_basename = plugin_basename($plugin_file_path);
            error_log("Plugin basename: $plugin_basename");

            // Get plugin data and log it
            $plugin_data = get_plugin_data($plugin_file_path);
            error_log("Plugin data: " . print_r($plugin_data, true));

            if (empty($plugin_data)) {
                error_log("Failed to retrieve plugin data.");
                return $transient;
            }

            if (empty($plugin_data['Version']) || empty($plugin_data['PluginURI']) || empty($plugin_data['Name'])) {
                error_log("Incomplete plugin data. Version: {$plugin_data['Version']}, PluginURI: {$plugin_data['PluginURI']}, Name: {$plugin_data['Name']}");
                return $transient;
            }

            $installed_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '0.0.0';
            error_log("Installed version: $installed_version");

            if (version_compare($githubAPIResult->tag_name, $installed_version, '>')) {
                $plugin = array(
                    'slug' => dirname($plugin_basename),
                    'plugin' => $plugin_basename,
                    'new_version' => $githubAPIResult->tag_name,
                    'url' => $plugin_data['PluginURI'],
                    'package' => $githubAPIResult->zipball_url,
                );

                if (!empty($this->accessToken)) {
                    $plugin['package'] = add_query_arg(array('access_token' => $this->accessToken), $plugin['package']);
                }

                if (!isset($transient->response)) {
                    $transient->response = array();
                }

                $transient->response[$plugin_basename] = (object)$plugin;

                error_log("Update available: " . print_r($plugin, true));
            } else {
                error_log("No update needed");
            }

            return $transient;
        }
    }
}

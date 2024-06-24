<?php
if (!class_exists('GitHub_Updater')) {
    class GitHub_Updater {
        private $username;
        private $repo;
        private $accessToken;
        private $plugin_file;

        function __construct($gitHubUsername, $gitHubProjectName, $plugin_file, $accessToken = '') {
            $this->username = $gitHubUsername;
            $this->repo = $gitHubProjectName;
            $this->plugin_file = $plugin_file;
            $this->accessToken = $accessToken;

            add_filter("pre_set_site_transient_update_plugins", array($this, "setTransient"));
        }
        public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $this->github_response = $this->get_repository_info();

        if ($this->github_response && version_compare($this->github_response->tag_name, $this->plugin_data['Version'], '>')) {
            $plugin_info = array(
                'slug' => dirname($this->plugin_file),
                'plugin' => $this->plugin_file,
                'new_version' => $this->github_response->tag_name,
                'url' => $this->plugin_data['PluginURI'],
                'package' => $this->github_response->zipball_url,
            );
            $transient->response[$this->plugin_file] = (object) $plugin_info;
        }

        return $transient;
    }

    // Add this new method
    public function after_update($upgrader_object, $options) {
        if ($options['action'] == 'update' && $options['type'] == 'plugin') {
            // Get the current plugin directory name
            $current_plugin_dir = dirname($this->plugin_file);

            // Check if our plugin was updated
            if (isset($options['plugins']) && in_array($this->plugin_file, $options['plugins'])) {
                // Get the temporary update directory
                $temp_dir = $upgrader_object->skin->result['destination'];

                // Check if the temporary directory exists and is different from our plugin directory
                if ($temp_dir && $temp_dir != WP_PLUGIN_DIR . '/' . $current_plugin_dir) {
                    // Move the files from the temporary directory to our plugin directory
                    rename($temp_dir, WP_PLUGIN_DIR . '/' . $current_plugin_dir);
                }
            }
        }
    } 
        public function getRepositoryInfo() {
            $url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases/latest";
            if (!empty($this->accessToken)) {
                $url = add_query_arg(array('access_token' => $this->accessToken), $url);
            }

            $response = wp_remote_get($url);
            if (is_wp_error($response)) {
                error_log('Error fetching repository info: ' . $response->get_error_message());
                return false;
            }

            $responseBody = wp_remote_retrieve_body($response);
            return json_decode($responseBody);
        }

        public function setTransient($transient) {
            if (!is_object($transient)) {
                $transient = new stdClass();
            }

            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_file);
            $installed_version = $plugin_data['Version'];

            $repoInfo = $this->getRepositoryInfo();
            if ($repoInfo === false) {
                error_log('Failed to get repository info');
                return $transient;
            }

            //error_log('Installed version: ' . $installed_version . ', Latest version: ' . $repoInfo->tag_name);

            if (version_compare($repoInfo->tag_name, $installed_version, '>')) {
                $plugin = array(
                    'slug' => dirname($this->plugin_file),
                    'plugin' => $this->plugin_file,
                    'new_version' => $repoInfo->tag_name,
                    'url' => $plugin_data['PluginURI'],
                    'package' => $repoInfo->zipball_url,
                );

                $transient->response[$this->plugin_file] = (object)$plugin;
                //error_log('Update available, modifying transient: ' . print_r($plugin, true));
            } else {
                //error_log('No update available');
            }

            return $transient;
        }
    }
}
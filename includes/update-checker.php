<?php
if (!class_exists('GitHub_Updater')) {
    class GitHub_Updater {
        private $username;
        private $repo;
        private $accessToken;

        function __construct($gitHubUsername, $gitHubProjectName, $accessToken = '') {
            $this->username = $gitHubUsername;
            $this->repo = $gitHubProjectName;
            $this->accessToken = $accessToken;

            add_filter("pre_set_site_transient_update_plugins", array($this, "setTransient"));
        }

        public function getRepositoryInfo() {
            $url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases/latest";
            if (!empty($this->accessToken)) {
                $url = add_query_arg(array('access_token' => $this->accessToken), $url);
            }

            $response = wp_remote_get($url);
            if (is_wp_error($response)) {
                return false;
            }

            $responseBody = wp_remote_retrieve_body($response);
            return json_decode($responseBody);
        }

            public function setTransient($transient) {
        if (!is_object($transient)) {
            $transient = new stdClass();
        }

        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . plugin_basename(__DIR__ . '/../dummy-content-main.php'));
        $plugin_slug = plugin_basename(__DIR__ . '/../dummy-content-main.php');
        $installed_version = $plugin_data['Version'];

        $repoInfo = $this->getRepositoryInfo();
        if ($repoInfo === false) {
            return $transient;
        }

        if (version_compare($repoInfo->tag_name, $installed_version, '>')) {
            $plugin = array(
                'slug' => dirname($plugin_slug),
                'plugin' => $plugin_slug,
                'new_version' => $repoInfo->tag_name,
                'url' => $plugin_data['PluginURI'],
                'package' => $repoInfo->zipball_url,
            );
    
            $transient->response[$plugin_slug] = (object)$plugin;
            error_log('Update available, modifying transient: ' . print_r($plugin, true));
        } else {
            error_log('No update available. Installed version: ' . $installed_version . ', Latest version: ' . $repoInfo->tag_name);
        }

        return $transient;
    }
        }
}
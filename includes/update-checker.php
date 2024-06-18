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

            //error_log("Repository info response: " . print_r($githubAPIResult, true));
            $githubAPIResult = @json_decode($githubAPIResult);
            if (is_array($githubAPIResult)) {
                $githubAPIResult = $githubAPIResult[0];
            }

            error_log("Decoded GitHub API Result: " . print_r($githubAPIResult, true));
            return $githubAPIResult;
        }
    }
}

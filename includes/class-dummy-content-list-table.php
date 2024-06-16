<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Dummy_Content_List_Table extends WP_List_TABLE {
    private $post_type;

    public function __construct($post_type) {
        $this->post_type = $post_type;

        parent::__construct([
            'singular' => __('Dummy Content', 'sp'),
            'plural'   => __('Dummy Contents', 'sp'),
            'ajax'     => false
        ]);
    }

    public function get_columns() {
        $columns = [
            'cb'              => '<input type="checkbox" />',
            'title'           => __('Title'),
            'dcg_create_date' => __('DCG Created Date'),
            'dcg_create_user' => __('DCG Created User'),
            'date'            => __('Post Date')
        ];
        return $columns;
    }

    protected function column_cb($item) {
        return sprintf('<input type="checkbox" name="post[]" value="%s" />', $item->ID);
    }

    protected function column_title($item) {
        $title = '<strong>' . $item->post_title . '</strong>';
        $actions = [
            'edit'      => sprintf('<a href="post.php?post=%s&action=edit">' . __('Edit') . '</a>', $item->ID),
            'delete'    => sprintf('<a href="?page=%s&action=%s&post=%s">' . __('Delete') . '</a>', $_REQUEST['page'], 'delete', $item->ID),
        ];
        return sprintf('%1$s %2$s', $title, $this->row_actions($actions));
    }

    protected function column_dcg_create_date($item) {
        return get_post_meta($item->ID, 'dcg_create_date', true);
    }

    protected function column_dcg_create_user($item) {
        $user_id = get_post_meta($item->ID, 'dcg_create_user', true);
        $user = get_userdata($user_id);
        return $user ? $user->display_name : __('Unknown');
    }

    protected function column_date($item) {
        return get_the_date('Y/m/d', $item->ID);
    }

    public function get_sortable_columns() {
        $sortable_columns = [
            'title'           => ['title', true],
            'dcg_create_date' => ['dcg_create_date', false],
            'dcg_create_user' => ['dcg_create_user', false],
            'date'            => ['date', false]
        ];
        return $sortable_columns;
    }

    protected function get_bulk_actions() {
        $actions = [
            'bulk-trash'  => 'Move to Trash',
            'bulk-delete' => 'Delete'
        ];
        return $actions;
    }

    public function prepare_items() {
        $this->process_bulk_action();

        $status = isset($_GET['post_status']) ? $_GET['post_status'] : 'all';
        $query_args = [
            'post_type'      => $this->post_type,
            'meta_key'       => 'dcg_create',
            'meta_value'     => '1',
            'posts_per_page' => -1,
        ];

        if ($status !== 'all') {
            $query_args['post_status'] = $status;
        }

        $query = new WP_Query($query_args);

        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->items = $query->posts;

        $this->set_pagination_args([
            'total_items' => $query->found_posts,
            'per_page'    => 20,
            'total_pages' => ceil($query->found_posts / 20)
        ]);
    }

    public function get_views() {
        $status_links = [
            'all'       => __('All'),
            'publish'   => __('Published'),
            'trash'     => __('Trash')
        ];

        $status_counts = [
            'all'       => $this->get_dummy_content_count(),
            'publish'   => $this->get_dummy_content_count('publish'),
            'trash'     => $this->get_dummy_content_count('trash')
        ];

        $current_status = isset($_REQUEST['post_status']) ? $_REQUEST['post_status'] : 'all';
        $views = [];

        foreach ($status_links as $status => $label) {
            $class = ($status == $current_status) ? ' class="current"' : '';
            $views[$status] = "<a href='" . esc_url(add_query_arg('post_status', $status, admin_url('admin.php?page=manage-dummy-content'))) . "'$class>$label<span class='count'> ({$status_counts[$status]})</span></a>";
        }

        return $views;
    }

    public function get_dummy_content_count($status = 'all') {
        $query_args = [
            'post_type'   => $this->post_type,
            'meta_key'    => 'dcg_create',
            'meta_value'  => '1',
            'post_status' => $status === 'all' ? ['publish', 'trash'] : $status
        ];

        $query = new WP_Query($query_args);

        return $query->found_posts;
    }

    public function display() {
        $views = $this->get_views();
        echo '<ul class="subsubsub">';
        foreach ($views as $view) {
            echo '<li>' . $view . ' | </li>';
        }
        echo '</ul>';
        parent::display();
    }

    public function process_bulk_action() {
        $action = $this->current_action();

        switch ($action) {
            case 'bulk-trash':
            case 'bulk-delete':
                $post_ids = isset($_POST['post']) ? array_map('absint', $_POST['post']) : [];
                if (!empty($post_ids)) {
                    handle_bulk_action_posts('', $action, $post_ids);
                }
                break;
        }
    }

    protected function extra_tablenav($which) {
        if ($which == "top") {
            echo '<div class="alignleft actions">';
            
            // Date filter
            $date_filter = wp_dropdown_categories([
                'show_option_all' => __('All dates'),
                'orderby'         => 'name',
                'echo'            => 0,
                'name'            => 'date_filter',
                'taxonomy'        => 'category',
                'selected'        => isset($_GET['date_filter']) ? $_GET['date_filter'] : '',
                'hierarchical'    => true,
                'depth'           => 0,
                'show_count'      => true,
                'hide_empty'      => true,
                'value_field'     => 'slug'
            ]);
            echo $date_filter;

            // Category filter
            $category_filter = wp_dropdown_categories([
                'show_option_all' => __('All categories'),
                'orderby'         => 'name',
                'echo'            => 0,
                'name'            => 'category_filter',
                'taxonomy'        => 'category',
                'selected'        => isset($_GET['category_filter']) ? $_GET['category_filter'] : '',
                'hierarchical'    => true,
                'depth'           => 0,
                'show_count'      => true,
                'hide_empty'      => true,
                'value_field'     => 'slug'
            ]);
            echo $category_filter;
            
            submit_button(__('Filter'), '', 'filter_action', false);

            echo '</div>';
        }
    }
}

// Hook into the handle_bulk_actions-{screen} filter
add_filter('handle_bulk_actions-dummy-content_page_manage-dummy-content', 'handle_bulk_action_posts', 10, 3);

function handle_bulk_action_posts($redirect_to, $doaction, $post_ids) {
    if ($doaction === 'bulk-trash') {
        foreach ($post_ids as $post_id) {
            wp_trash_post($post_id);
        }
        $redirect_to = add_query_arg('bulk_trashed_posts', count($post_ids), $redirect_to);
    } elseif ($doaction === 'bulk-delete') {
        foreach ($post_ids as $post_id) {
            wp_delete_post($post_id, true); // Permanently delete
        }
        $redirect_to = add_query_arg('bulk_deleted_posts', count($post_ids), $redirect_to);
    }
    return $redirect_to;
}

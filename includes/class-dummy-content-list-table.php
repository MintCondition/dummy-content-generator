<?php
//TODO: Get the hover actions working
//TODO: Make the list only show dcg_created posts
//TODO: Add Columns for dcg_create_date and dcg_create_user
//TODO: remove unneeded columns
//TODO: make loop and accordion for all post types



if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Dummy_Content_List_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct([
            'singular' => __('Dummy Content', 'sp'),
            'plural'   => __('Dummy Contents', 'sp'),
            'ajax'     => false
        ]);
    }

    public function get_columns() {
        $columns = [
            'cb'        => '<input type="checkbox" />',
            'title'     => __('Title'),
            'author'    => __('Author'),
            'categories'=> __('Categories'),
            'tags'      => __('Tags'),
            'date'      => __('Date')
        ];
        return $columns;
    }

    protected function column_cb($item) {
        return sprintf('<input type="checkbox" name="post[]" value="%s" />', $item->ID);
    }

    protected function column_title($item) {
        $title = '<strong>' . $item->post_title . '</strong>';
        $actions = [
            'edit'      => sprintf('<a href="?page=%s&action=%s&post=%s">' . __('Edit') . '</a>', $_REQUEST['page'], 'edit', $item->ID),
            'delete'    => sprintf('<a href="?page=%s&action=%s&post=%s">' . __('Delete') . '</a>', $_REQUEST['page'], 'delete', $item->ID),
        ];
        return sprintf('%1$s %2$s', $title, $this->row_actions($actions));
    }

    protected function column_author($item) {
        $author = get_userdata($item->post_author);
        return $author->display_name;
    }

    protected function column_categories($item) {
        return get_the_category_list(', ', '', $item->ID);
    }

    protected function column_tags($item) {
        return get_the_tag_list('', ', ', '', $item->ID);
    }

    protected function column_date($item) {
        return get_the_date('Y/m/d', $item->ID);
    }

    public function get_sortable_columns() {
        $sortable_columns = [
            'title'    => ['title', true],
            'author'   => ['author', false],
            'date'     => ['date', false]
        ];
        return $sortable_columns;
    }

    protected function get_bulk_actions() {
        $actions = [
            'bulk-trash' => 'Move to Trash',
            'bulk-delete' => 'Delete'
        ];
        return $actions;
    }

    public function prepare_items() {
        // Process bulk actions
        $this->process_bulk_action();

        $status = isset($_GET['post_status']) ? $_GET['post_status'] : 'all';
        $query_args = [
            'post_type'      => 'post',
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
        $status_links = array(
            'all'       => __('All'),
            'publish'   => __('Published'),
            'trash'     => __('Trash')
        );

        $status_counts = array(
            'all'       => wp_count_posts()->publish + wp_count_posts()->trash,
            'publish'   => wp_count_posts()->publish,
            'trash'     => wp_count_posts()->trash
        );

        $current_status = isset($_REQUEST['post_status']) ? $_REQUEST['post_status'] : 'all';
        $views = array();

        foreach ($status_links as $status => $label) {
            $class = ($status == $current_status) ? ' class="current"' : '';
            $views[$status] = "<a href='" . esc_url(add_query_arg('post_status', $status, admin_url('admin.php?page=manage-dummy-content'))) . "'$class>$label<span class='count'> ({$status_counts[$status]})</span></a>";
        }

        return $views;
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
        // Rely on WP_List_Table for nonce verification
        $action = $this->current_action();
        error_log('Current action: ' . $action);

        switch ($action) {
            case 'bulk-trash':
            case 'bulk-delete':
                $post_ids = isset($_POST['post']) ? array_map('absint', $_POST['post']) : [];
                if (!empty($post_ids)) {
                    error_log('Processing bulk action: ' . $action);
                    error_log('Post IDs: ' . implode(', ', $post_ids));
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
    error_log('Handling bulk action: ' . $doaction);
    error_log('Post IDs: ' . implode(', ', $post_ids));
    error_log('Redirect to: ' . $redirect_to);
    if ($doaction === 'bulk-trash') {
        foreach ($post_ids as $post_id) {
            wp_trash_post($post_id);
            error_log('Trashed post ID: ' . $post_id);
        }
        $redirect_to = add_query_arg('bulk_trashed_posts', count($post_ids), $redirect_to);
    } elseif ($doaction === 'bulk-delete') {
        foreach ($post_ids as $post_id) {
            wp_delete_post($post_id, true); // Permanently delete
            error_log('Deleted post ID: ' . $post_id);
        }
        $redirect_to = add_query_arg('bulk_deleted_posts', count($post_ids), $redirect_to);
    }
    return $redirect_to;
}

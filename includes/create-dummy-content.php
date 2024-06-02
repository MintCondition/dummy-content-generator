<?php
// File: includes/create-dummy-content.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Create_Dummy_Content_Page {
    public function display_page() {
        ?>
        <div id="dummy-content-wizard">
            <h1>Create Dummy Content</h1>
            <div id="step-1">
                <h2>Step 1: Select Post Type</h2>
                <form id="dummy-content-form">
                    <?php wp_nonce_field('create_dummy_content', 'dummy_content_nonce'); ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Post Type</th>
                            <td>
                                <select id="dummy_content_post_type" name="dummy_content_post_type">
                                    <option value="">Select Post Type</option>
                                    <?php
                                    $post_types = get_post_types(array('public' => true), 'objects');
                                    foreach ($post_types as $post_type) {
                                        echo '<option value="' . esc_attr($post_type->name) . '">' . esc_html($post_type->labels->singular_name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <button type="button" id="next-step" class="button button-primary">Next</button>
                </form>
            </div>
            <div id="step-2" style="display: none;">
                <h2>Step 2: Configure Fields</h2>
                <form id="dummy-content-fields-form">
                    <table class="widefat fixed striped" id="dummy-content-fields-table">
                        <thead>
                            <tr>
                                <th class="manage-column column-primary">Field</th>
                                <th class="manage-column">Data Type</th>
                                <th class="manage-column">Parameters</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <button type="button" id="final-step" class="button button-primary">Next</button>
                </form>
            </div>
            <div id="step-3" style="display: none;">
                <h2>Step 3: Create Content</h2>
                <button type="button" id="create-content" class="button button-primary">Create</button>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#next-step').on('click', function() {
                    var postType = $('#dummy_content_post_type').val();
                    if (postType) {
                        $.ajax({
                            url: dummyContent.ajax_url,
                            method: 'POST',
                            data: {
                                action: 'get_post_type_fields',
                                post_type: postType,
                                nonce: dummyContent.nonce
                            },
                            success: function(response) {
                                if (response.success) {
                                    var fields = response.data;
                                    var tbody = $('#dummy-content-fields-table tbody');
                                    tbody.empty();
                                    $.each(fields, function(index, field) {
                                        var dataTypeOptions = '<select class="data-type-select" data-field="' + field.name + '"><option value="">Select Data Type</option>';
                                        $.each(dummyContent.data_types, function(type, label) {
                                            dataTypeOptions += '<option value="' + type + '">' + label + '</option>';
                                        });
                                        dataTypeOptions += '</select>';
                                        tbody.append('<tr><td class="column-primary">' + field.label + '</td><td>' + dataTypeOptions + '</td><td class="parameters-column"></td></tr>');
                                    });
                                    $('#step-1').hide();
                                    $('#step-2').show();
                                }
                            }
                        });
                    }
                });

                $('#dummy-content-fields-table').on('change', '.data-type-select', function() {
                    var dataType = $(this).val();
                    var field = $(this).data('field');
                    var $parametersColumn = $(this).closest('tr').find('.parameters-column');
                    if (dataType) {
                        $.ajax({
                            url: dummyContent.ajax_url,
                            method: 'POST',
                            data: {
                                action: 'get_data_type_parameters',
                                data_type: dataType,
                                field: field,
                                nonce: dummyContent.nonce
                            },
                            success: function(response) {
                                if (response.success) {
                                    $parametersColumn.html(response.data.parameters);
                                }
                            }
                        });
                    } else {
                        $parametersColumn.empty();
                    }
                });

                $('#final-step').on('click', function() {
                    $('#step-2').hide();
                    $('#step-3').show();
                });
            });
        </script>
        <?php
    }
}

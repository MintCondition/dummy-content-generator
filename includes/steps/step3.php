<?php
// File: includes/steps/step3.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
$num_posts = isset($_POST['num_posts']) ? intval($_POST['num_posts']) : 1;
$fields = isset($_POST['fields']) ? $_POST['fields'] : array();
$data_types = load_data_types();
$ajax_nonce = wp_create_nonce('create_dummy_content');
?>

<div class="wrap">
    <h1>Step 3: Configure Generators</h1>
    <p>Creating <?php echo $num_posts . ' ' . esc_html($post_type) . 's'; ?></p>

    <form id="step3-form" method="post" action="">
        <input type="hidden" name="step" value="4">
        <input type="hidden" name="post_type" value="<?php echo esc_attr($post_type); ?>">
        <input type="hidden" name="num_posts" value="<?php echo esc_attr($num_posts); ?>">
        <input type="hidden" name="fields" value="<?php echo esc_attr(implode(',', $fields)); ?>">

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Data Type</th>
                    <th>Generator</th>
                    <th>Parameters</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fields as $field): ?>
                    <tr>
                        <td><?php echo esc_html($field); ?></td>
                        <td>
                            <select name="data_types[<?php echo esc_attr($field); ?>]" class="data-type-select">
                                <option value="">Select Data Type</option>
                                <?php foreach ($data_types as $data_type_key => $data_type): ?>
                                    <option value="<?php echo esc_attr($data_type_key); ?>"><?php echo esc_html($data_type['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="generators[<?php echo esc_attr($field); ?>]" class="generator-select" disabled>
                                <option value="">Select Generator</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </td>
                        <td class="parameters-cell">
                            <!-- Parameters will be populated via JavaScript -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary">Next</button>
            <button type="button" class="button" id="back-button">Back</button>
        </p>
    </form>
</div>
<script>
jQuery(document).ready(function($) {
    $('.data-type-select').on('change', function() {
        var field = $(this).attr('name').match(/\[([^\]]+)\]/)[1];
        var dataType = $(this).val();
        var generatorSelect = $('select[name="generators[' + field + ']"]');
        var parametersCell = generatorSelect.closest('tr').find('.parameters-cell');

        generatorSelect.empty().append('<option value="">Select Generator</option>').prop('disabled', true);
        parametersCell.empty();

        if (dataType) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_generators',
                    data_type: dataType,
                    field: field,
                    _ajax_nonce: '<?php echo $ajax_nonce; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $.each(response.data.generators, function(index, generator) {
                            generatorSelect.append('<option value="' + generator['class'] + '">' + generator['label'] + '</option>');
                        });
                        generatorSelect.prop('disabled', false);
                    } else {
                        console.error('Failed to fetch generators:', response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error on get_generators:', textStatus, errorThrown);
                    console.log('Response text:', jqXHR.responseText);
                }
            });
        }
    });

    $('.generator-select').on('change', function() {
        var field = $(this).attr('name').match(/\[([^\]]+)\]/)[1];
        var generatorClass = $(this).val();
        var parametersCell = $(this).closest('tr').find('.parameters-cell');

        parametersCell.empty();

        if (generatorClass) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_generator_parameters',
                    generator: generatorClass,
                    field: field,
                    _ajax_nonce: '<?php echo $ajax_nonce; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        parametersCell.html(response.data.parameters_html);
                    } else {
                        console.error('Failed to fetch parameters:', response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error on get_generator_parameters:', textStatus, errorThrown);
                    console.log('Response text:', jqXHR.responseText);
                }
            });
        }
    });

    $('#back-button').on('click', function() {
        history.back();
    });
});
</script>

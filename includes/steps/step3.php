<?php
// File: includes/steps/step3.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
$num_posts = isset($_POST['num_posts']) ? intval($_POST['num_posts']) : 1;
$fields = isset($_POST['fields']) ? $_POST['fields'] : array();
$data_types = load_data_types();
?>

<div class="wrap">
    <h1>Step 3: Configure Generators</h1>
    <p>Creating <?php echo $num_posts; ?> <?php echo $post_type; ?></p>

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
            var generators = <?php echo json_encode($data_types); ?>[dataType]['generators'];
            $.each(generators, function(index, generator) {
                generatorSelect.append('<option value="' + generator['class'] + '">' + generator['label'] + '</option>');
            });
            generatorSelect.prop('disabled', false);
        }
    });

    $('.generator-select').on('change', function() {
        var field = $(this).attr('name').match(/\[([^\]]+)\]/)[1];
        var generatorClass = $(this).val();
        var dataType = $('select[name="data_types[' + field + ']"]').val();
        var parametersCell = $(this).closest('tr').find('.parameters-cell');
        
        parametersCell.empty();
        
        if (dataType && generatorClass) {
            var generators = <?php echo json_encode($data_types); ?>[dataType]['generators'];
            var generator = generators.find(gen => gen['class'] === generatorClass);
            if (generator && generator['parameters']) {
                $.each(generator['parameters'], function(paramKey, param) {
                    var input;
                    switch (param['type']) {
                        case 'select':
                            input = $('<select>').attr('name', 'parameters[' + field + '][' + paramKey + ']').addClass(param['class']);
                            $.each(param['options'], function(optionIndex, option) {
                                input.append($('<option>').attr('value', option).text(option));
                            });
                            break;
                        case 'text':
                        case 'number':
                            input = $('<input>').attr('type', param['type']).attr('name', 'parameters[' + field + '][' + paramKey + ']').addClass(param['class']);
                            break;
                    }
                    parametersCell.append($('<div>').addClass('param').append($('<label>').text(param['label'])).append(input).append($('<p>').text(param['instructions'])));
                });
            }
        }
    });
});
</script>

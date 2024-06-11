<?php
// File: includes/testing.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dummy_Content_Testing_Page {
    public function display_page() {
        // Load all generators
        $generators = $this->get_all_generators();
        ?>
        <div class="wrap">
            <h1>Testing Generators</h1>
            <p>Use this page to test all installed generators with different parameters.</p>

            <?php
            // Display generated content at the top if available
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generator_class'])) {
                $params = $_POST['params'];
                $result = $this->test_generator($_POST['generator_class'], $_POST['generator_file'], $params);
                if ($result && isset($result['type']) && isset($result['data_type']) && isset($result['content'])) {
                    echo '<h3>Generated Content</h3>';
                    echo '<div class="dc-generator-output">' . esc_html(print_r($result, true)) . '</div>';
                    echo '<h3>Rendered Output</h3>';
                    echo '<div class="dc-render-container">';
                    switch ($result['data_type']) {
                        case 'plaintext':
                            echo '<div class="dc-payload-plaintext">' . esc_html($result['content']) . '</div>';
                            break;
                        case 'html':
                            echo '<div class="dc-payload-html">' . $result['content'] . '</div>';
                            break;
                        case 'image':
                            echo '<div class="dc-payload-image"><img src="' . esc_url($result['content']['url']) . '" alt="Generated Image"></div>';
                            break;
                        case 'datetime':
                            echo '<div class="dc-payload-date">' . esc_html($result['content']) . '</div>';
                            break;
                        default:
                            echo '<div class="dc-payload-unknown">Unknown data type</div>';
                            break;
                    }
                    echo '</div>';
                } else {
                    echo '<div class="dc-error">Invalid Generator Response format.</div>';
                }
            }
            ?>

            <div class="generator-container" style="display: flex; flex-wrap: wrap;">
                <?php if (empty($generators)): ?>
                    <p>No generators found.</p>
                <?php else: ?>
                    <?php foreach ($generators as $generator): ?>
                        <div class="generator-box" style="flex: 1 0 200px; margin: 10px; padding: 10px; border: 1px solid #ccc; box-shadow: 2px 2px 5px rgba(0,0,0,0.1);">
                            <h2><?php echo esc_html($generator['label']); ?></h2>
                            <form method="post">
                                <input type="hidden" name="generator_class" value="<?php echo esc_attr($generator['class']); ?>">
                                <input type="hidden" name="generator_file" value="<?php echo esc_attr($generator['file']); ?>">

                                <?php foreach ($generator['parameters'] as $param_name => $param): ?>
                                    <p>
                                        <label><?php echo esc_html($param['label']); ?>:</label>
                                        <?php if ($param['type'] == 'select'): ?>
                                            <select name="params[<?php echo esc_attr($param_name); ?>]">
                                                <?php foreach ($param['options'] as $option): ?>
                                                    <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php elseif ($param['type'] == 'text'): ?>
                                            <input type="text" name="params[<?php echo esc_attr($param_name); ?>]">
                                        <?php elseif ($param['type'] == 'number'): ?>
                                            <input type="number" name="params[<?php echo esc_attr($param_name); ?>]">
                                        <?php elseif ($param['type'] == 'date'): ?>
                                            <input type="date" name="params[<?php echo esc_attr($param_name); ?>]">
                                        <?php endif; ?>
                                    </p>
                                <?php endforeach; ?>

                                <p>
                                    <button type="submit" class="button button-primary">Test Generator</button>
                                </p>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function get_all_generators() {
        $generators = [];
        $generator_files = glob(plugin_dir_path(__FILE__) . '../generators/*.php');

        foreach ($generator_files as $file) {
            $file_content = file_get_contents($file);
            preg_match('/Class:\s*(\w+)/', $file_content, $matches);
            if (isset($matches[1])) {
                $class_name = $matches[1];
                require_once $file;
                if (class_exists($class_name) && method_exists($class_name, 'getParameters')) {
                    $generators[] = [
                        'class' => $class_name,
                        'file' => basename($file),
                        'label' => $class_name,
                        'parameters' => call_user_func([$class_name, 'getParameters']),
                    ];
                }
            }
        }

        return $generators;
    }

    private function test_generator($generator_class, $generator_file, $params) {
        require_once plugin_dir_path(__FILE__) . '../generators/' . $generator_file;
        return call_user_func([$generator_class, 'generate'], $params);
    }
}

new Dummy_Content_Testing_Page();
?>

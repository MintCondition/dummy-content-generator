<?php
// File: includes/data-generatorinterface.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

interface DataGeneratorInterface {
    public static function generate($params);
}

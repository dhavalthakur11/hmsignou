<?php
/**
 * Base Model — all models extend this.
 * Provides a $db reference and common query helpers.
 */
class Model {
    protected Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Sanitize a string input (trim + htmlspecialchars).
     * Use for display — NOT as a substitute for bind variables.
     */
    protected function sanitize(string $input): string {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}
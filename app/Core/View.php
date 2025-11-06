<?php
/**
 * View Rendering
 *
 * Handles view rendering with layout support
 */
class View {
    /**
     * Render a view with layout
     *
     * @param string $view View file path (relative to Views directory)
     * @param array $data Data to pass to the view
     * @param string|null $layout Layout file (null for no layout)
     * @return string Rendered HTML
     */
    public static function render($view, $data = [], $layout = 'layout') {
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        $view_path = VIEWS_PATH . '/' . $view . '.php';

        if (!file_exists($view_path)) {
            die('View not found: ' . $view);
        }

        include $view_path;

        // Get view content
        $content = ob_get_clean();

        // If no layout, return content directly
        if ($layout === null) {
            return $content;
        }

        // Render with layout
        $layout_path = VIEWS_PATH . '/layout.php';

        if (!file_exists($layout_path)) {
            die('Layout not found: ' . $layout);
        }

        // Start output buffering again for layout
        ob_start();
        include $layout_path;
        return ob_get_clean();
    }

    /**
     * Render a partial view (without layout)
     *
     * @param string $partial Partial file path
     * @param array $data Data to pass to the partial
     * @return string Rendered HTML
     */
    public static function partial($partial, $data = []) {
        extract($data);

        ob_start();
        $partial_path = VIEWS_PATH . '/' . $partial . '.php';

        if (!file_exists($partial_path)) {
            die('Partial not found: ' . $partial);
        }

        include $partial_path;
        return ob_get_clean();
    }

    /**
     * Include a partial directly (for use within views)
     *
     * @param string $partial Partial file path
     * @param array $data Data to pass to the partial
     */
    public static function include($partial, $data = []) {
        echo self::partial($partial, $data);
    }
}

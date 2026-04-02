<?php

class Controller {

    /**
     * Load and render a view.
     *
     * @param string $view   Path relative to app/views/ (e.g. 'admin/dashboard')
     * @param array  $data   Variables to extract into the view scope
     * @param bool   $layout Wrap in layouts/main.php (header + sidebar + footer)
     */
    protected function view(string $view, array $data = [], bool $layout = true): void {
        // Make $data keys available as variables in the view
        extract($data);

        $viewFile = APP_PATH . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            error_log('[View] Not found: ' . $viewFile);
            $this->view('error/error', ['message' => 'View not found.'], false);
            return;
        }

        if ($layout) {
            // $content_view is used inside main.php to include the actual view
            $content_view = $viewFile;
            require_once APP_PATH . '/views/layouts/main.php';
        } else {
            require_once $viewFile;
        }
    }

    /**
     * Load a model class.
     *
     * @param  string $model  Class name without 'Model' suffix (e.g. 'User')
     * @return object         Instantiated model
     */
    protected function model(string $model): object {
        $class = $model . 'Model';
        $file  = APP_PATH . '/models/' . $class . '.php';
        require_once $file;
        return new $class();
    }

    /** Redirect to a URL */
    protected function redirect(string $url): void {
        header('Location: ' . base_url($url));
        exit;
    }

    /** Set a flash message to display on next page load */
    protected function flash(string $type, string $message): void {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /** Check if POST */
    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
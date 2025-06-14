<?php

class Session
{
    /**
     * Constructor that starts a session if one hasn't been started yet.
     */
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); // Start the session if not already started
        }
    }

    /**
     * Set a session variable.
     * 
     * @param string $key The key for the session data.
     * @param mixed $value The value to be stored in the session.
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable by key.
     * 
     * @param string $key The key for the session data.
     * @return mixed|null The value of the session variable, or null if not set.
     */
    public function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Remove a session variable.
     * 
     * @param string $key The key for the session data to remove.
     */
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy the session.
     */
    public function destroy()
    {
        $_SESSION = [];
        session_destroy(); // Destroy the session data
    }

    /**
     * Check if a session variable is set.
     * 
     * @param string $key The key for the session data.
     * @return bool True if the session variable is set, otherwise false.
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }
}
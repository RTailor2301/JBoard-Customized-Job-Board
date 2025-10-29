<?php
/**
 * Checks if user key is set in session
 */
function is_logged_in() {
    return isset($_SESSION["user"]);
}
/**
 * Returns the current user's username or empty string
 */
function get_username() {
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "username", "", false);
    }
    return "";
}
/**
 * Returns the current user's email or empty string
 */
function get_user_email() {
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "email", "", false);
    }
    return "";
}
/**
 * Returns the current user's id or -1
 */
function get_user_id() {
    if (is_logged_in()) { //we need to check for login first because "user" key may not exist
        return se($_SESSION["user"], "id", -1, false);
    }
    return -1;
}
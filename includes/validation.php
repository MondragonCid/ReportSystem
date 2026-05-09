<?php
/**
 * Validation Functions for CIT University System
 */

/**
 * Generate CIT email from firstname and lastname
 */
function generateCITEmail($firstname, $lastname) {
    return strtolower(trim($firstname)) . '.' . strtolower(trim($lastname)) . '@cit.edu';
}

/**
 * Validate CIT University Email Format
 */
function validateCITEmail($email, $firstname = null, $lastname = null) {
    // Check if email ends with @cit.edu
    if (!str_ends_with($email, '@cit.edu')) {
        return ['valid' => false, 'message' => 'Email must end with @cit.edu'];
    }
    
    // Extract local part
    $local_part = str_replace('@cit.edu', '', $email);
    
    // Check format
    if (strpos($local_part, '.') === false) {
        return ['valid' => false, 'message' => 'Email format: firstname.lastname@cit.edu'];
    }
    
    if (substr_count($local_part, '.') > 1) {
        return ['valid' => false, 'message' => 'Use only one dot: firstname.lastname@cit.edu'];
    }
    
    if (!preg_match('/^[a-zA-Z]+\.[a-zA-Z]+$/', $local_part)) {
        return ['valid' => false, 'message' => 'Use only letters: firstname.lastname@cit.edu'];
    }
    
    // Validate name match
    if ($firstname && $lastname) {
        $expected = strtolower($firstname) . '.' . strtolower($lastname) . '@cit.edu';
        if (strtolower($email) !== $expected) {
            return ['valid' => false, 'message' => "Email must be: {$expected}"];
        }
    }
    
    return ['valid' => true, 'message' => 'Valid CIT email'];
}

/**
 * Check if email exists
 */
function isEmailExists($conn, $email) {
    $email = mysqli_real_escape_string($conn, $email);
    $query = "SELECT UserID FROM user WHERE Email = '$email'";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

/**
 * Check if username exists
 */
function isUsernameExists($conn, $username, $exclude_id = null) {
    $username = mysqli_real_escape_string($conn, $username);
    $query = "SELECT UserID FROM user WHERE Username = '$username'";
    if ($exclude_id) {
        $query .= " AND UserID != '$exclude_id'";
    }
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

/**
 * Sanitize input
 */
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($input)));
}
?>
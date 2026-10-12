<?php
// Role -> permission map. A role is a named set of permissions.
// Edit here to add roles/permissions. Assignment of roles to users lives in
// the user's attributes JSON (users.attributes -> "roles": [...]).
$GLOBALS['ROLE_PERMISSIONS'] = array(
    'admin' => array('manage_locations', 'manage_items', 'manage_users'),
);

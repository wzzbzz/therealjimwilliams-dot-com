<?php
require_once(BASEPATH."/_classes/commander/action.php");
// elevate <username> — grant the "admin" role to another user.
// Gated on the manage_users permission (held by the admin role). This is the
// in-game counterpart to the SQL JSON_SET grant documented in
// .kiro/skills/playfix/functionality/admin-and-roles.md. Multiple users can be
// admin, so this appends "admin" to the target user's attributes.roles array.
class elevate extends action{
    public function __construct(){
    }
    public function _execute($args){
        $user = _user();
        if (!$user->user_has_permission('manage_users')){
            return $this->out("who do you think you are, the real jim williams?");
        }
        if (count($args) == 0 || trim($args[0]) === ""){
            return $this->out("Elevate whom? Usage: elevate <username>");
        }
        $name = trim($args[0]);
        $db = _db();

        // Resolve the target by name. The command parser already rejects any
        // command containing an apostrophe; strip to identifier chars defensively
        // before interpolating into the query (the DB layer does not escape).
        $safe = preg_replace('/[^A-Za-z0-9_.\-]/', '', $name);
        if ($safe === ""){
            return $this->out("That is not a valid username.");
        }
        $rows = $db->query("SELECT id, username FROM users WHERE username='" . $safe . "' LIMIT 1");
        if (count($rows) == 0){
            return $this->out("No such user: " . htmlspecialchars($name));
        }
        $target_id = (int) $rows[0]->id;
        $target_name = $rows[0]->username;

        // Already an admin? Idempotent — report and stop.
        $roles = $user->get_user_roles($target_id);   // stateless public reader
        if (in_array("admin", $roles, true)){
            return $this->out($target_name . " is already an admin.");
        }

        // Append admin and write the full roles array back. Read-modify-write in
        // PHP preserves any pre-existing roles and avoids brittle nested JSON SQL.
        $roles[] = "admin";
        $json = json_encode(array_values($roles));    // e.g. ["admin"] or ["builder","admin"]
        $db->query(
            "UPDATE users SET attributes = JSON_SET(" .
                "COALESCE(NULLIF(attributes, ''), '{}'), " .
                "'$.roles', CAST('" . $json . "' AS JSON)" .
            ") WHERE id=" . $target_id
        );

        // Verify the grant landed before confirming to the admin.
        $roles_after = $user->get_user_roles($target_id);
        if (!in_array("admin", $roles_after, true)){
            return $this->out("Failed to elevate " . $target_name . ".");
        }
        return $this->out("You have elevated " . $target_name . " to admin.");
    }

    private function out($msg){
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = $msg;
        return $result;
    }
}
?>

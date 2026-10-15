<?php
require_once(BASEPATH."/_classes/commander/command.php");
// yeet <item> — (admin) pull any item in the world into your inventory, from
// wherever it is: a room, or another player's pockets. <item> can be an item id
// (yeet 8), its name (yeet key_apt_5B), its alias (yeet key 5B) or its title.
// If more than one item matches, lists them with ids so you can yeet by id.
class yeet extends command{
    public function _execute($args){
        $user = _user();
        $result = array();
        $result['action'] = "OUTPUT";
        if (!$user->user_has_permission('manage_items')){
            $result['data'] = "who do you think you are, the real jim williams?";
            return $result;
        }
        $phrase = trim(implode(" ", $args));
        if ($phrase==""){
            $result['data'] = "Yeet what? Type 'yeet &lt;item id or name&gt;'.";
            return $result;
        }
        $db = _db();
        if (ctype_digit($phrase)){
            $items = $db->query("SELECT items.id, items.name, items.title FROM items WHERE items.id='".intval($phrase)."'");
        }
        else{
            $p = $db->escape(strtolower(preg_replace('/^the /i', '', $phrase)));
            $items = $db->query("SELECT items.id, items.name, items.title FROM items
                WHERE LOWER(items.name)='$p'
                   OR (JSON_VALID(items.attributes) AND LOWER(JSON_UNQUOTE(JSON_EXTRACT(items.attributes, '$.alias')))='$p')
                   OR LOWER(items.title)='$p'
                   OR LOWER(items.title)='the $p'
                   OR LOWER(items.title)='a $p'");
        }
        if (count($items)==0){
            $result['data'] = "Nothing called ".htmlspecialchars($phrase)." exists.";
            return $result;
        }
        if (count($items)>1){
            $list = "<div>Which one? Yeet it by id:</div>";
            foreach ($items as $item){
                $list .= "<div>".intval($item->id)." &nbsp; ".htmlspecialchars($item->title)." (".htmlspecialchars($item->name).")</div>";
            }
            $result['data'] = $list;
            return $result;
        }
        $item = $items[0];
        $item_id = intval($item->id);
        $user_id = intval($user->get_logged_in_user_id());
        $db->query("DELETE FROM item_locations WHERE item_id='$item_id'");
        $db->query("DELETE FROM item_users WHERE item_id='$item_id'");
        $db->query("INSERT INTO item_users (item_id, user_id) VALUES ('$item_id','$user_id')");
        $result['data'] = htmlspecialchars($item->title)." comes flying out of nowhere and lands in your hands.";
        return $result;
    }
}
?>

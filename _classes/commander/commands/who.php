<?php
/*
 *  command - who
 *
 *  gives list of currently logged users
 *  
 */

class who extends command{
    
    public function _execute(){

        $sql = "SELECT * from users JOIN live_sessions ON users.id = live_sessions.user_id";
        $users = $this->db->query($sql);

        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = "LOGGED IN USERS : <br>";

        if (count($users)==0){
            $result['data'].= "NONE";
        }
        
        else{
            foreach($users as $user){
                $result['data'].=" ".$user->username;
            }
        }

        return $result;
    }
}
?>
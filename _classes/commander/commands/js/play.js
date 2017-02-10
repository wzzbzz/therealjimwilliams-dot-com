if (e[\"farter_id\"] == [user_id]){
o = \"You rock out. \";
}
else{
o = e[\"farter_name\"]+ \" rocks out. \";
}

my_console.print(o);    
    
    var snd = new Audio({url});
    snd.play();
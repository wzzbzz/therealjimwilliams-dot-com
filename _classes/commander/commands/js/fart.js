if (e[\"farter_id\"] == [user_id]){
o = \"You fart. \";
}
else{
o = e[\"farter_name\"]+ \" farts \";
}

my_console.print(o);    
    
    var snd = new Audio({url});
    snd.play();
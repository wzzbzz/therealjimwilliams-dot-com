if (e[\"speaker_id\"] == [user_id]){
o = \"You say \\\"\";
}
else{
o = e[\"speaker_name\"]+ \" says \\\"\";
}
o += e[\"message\"];
o += \"\\\"\";
my_console.print(o);
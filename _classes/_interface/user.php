<?php


// MVC of a "creature" -
// body = "view"
// mind = model
// spirit = controller.

class creature{
    
    //interacts with the world
    //body is the input/output device.
    private $body;
    
    //recieves messages from the body, and commands from the spirit.
    private $mind;
    
    //the "presence" of the object in the world.  The tissue between body and mind
    //the mind consults the spirit for decision making, and to decide if it "likes"
    //what is happening here.
    //this is the learning part.  Values learned from interactions.
    //essentially is the being's "state"
    private $spirit;
}
?>
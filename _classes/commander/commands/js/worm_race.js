/* Plays a worm race in the terminal; sent by the watch command. Every player
   gets the same saved tick sequence and the same per-lane thresholds, so
   everyone watching sees the same race. The loop numbers match
   _classes/worm_races.php (from the old wormcraft page): a worm steps when its
   number is under its early threshold, or its late one once past halfway. */
(function(r){
    var BODY = '//\\/\\/\\/\\/\\//\\/\\/', HEAD = '(&#8226;_&#8226;)', BLINK = '(-_-)';
    var STEP_PX = 5, DISTANCE = 600, TICK_MS = 28;
    var lanes = r.lanes.length;
    var id = 'wormrace-' + r.id + '-' + new Date().getTime();
    var html = '<div id="' + id + '"><div class="track">';
    for (var i = 0; i < lanes; i++){
        html += '<div class="lane" style="position:relative;height:20px;white-space:nowrap;">'
             +  '<div class="worm" style="position:absolute;left:0;"><span class="wormbody">' + BODY + '</span><span class="wormhead">' + HEAD + '</span></div></div>';
    }
    html += '</div><div class="messages"></div></div>';
    if (r.intro) my_console.print(r.intro);
    my_console.print(html);

    var root = $('#' + id), worms = root.find('.worm'), msg = root.find('.messages');
    var pos = [], leader = -1, t = 0, timer = null, blinker = null;
    for (var i = 0; i < lanes; i++) pos[i] = 0;

    function step(lane, quiet){
        pos[lane] += STEP_PX;
        if (leader < 0){ leader = lane; return; }
        if (pos[lane] > pos[leader]){
            leader = lane;
            if (!quiet) msg.html(r.lanes[lane].name + ' takes the lead!');
        }
    }
    function tick(quiet){
        var row = r.ticks[t];
        for (var i = 0; i < lanes; i++){
            var threshold = pos[i] > DISTANCE / 2 ? r.lanes[i].late : r.lanes[i].early;
            if (row[i] < threshold) step(i, quiet);
        }
        t++;
    }
    function render(){
        worms.each(function(i){ $(this).css('left', pos[i] + 'px'); });
    }
    function finish(){
        clearInterval(timer);
        clearInterval(blinker);
        worms.eq(leader).css('color', 'red');
        msg.html(r.lanes[leader].name + ' is the winner!!');
    }
    function run(){
        timer = setInterval(function(){
            if (t >= r.ticks.length) return finish();
            tick(false);
            render();
            if (leader >= 0 && pos[leader] > DISTANCE) finish();
        }, TICK_MS);
    }

    blinker = setInterval(function(){
        worms.each(function(){
            var w = $(this);
            if (w.data('blinking') || Math.random() * 1000 <= 995) return;
            w.data('blinking', 1);
            w.find('.wormhead').html(BLINK);
            setTimeout(function(){ w.find('.wormhead').html(HEAD); w.data('blinking', 0); }, 150);
        });
    }, TICK_MS);

    while (t < r.skip && t < r.ticks.length) tick(true);
    render();
    if (r.delay > 0) msg.text('The worms are lining up.');
    else if (r.skip > 0 && leader >= 0) msg.html('The race is already on. ' + r.lanes[leader].name + ' is out in front.');
    setTimeout(run, r.delay);
})({race});

<?php
function _404($template_ = '404.php'){
    global $template;
    $template = locate_template(  $template_ , false);
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_header();
    load_template( $template );
    get_footer();
    exit();
}
function login_only(){
	if (isset($_GET['logged_out'])){
    	wp_logout();
    	?><script>location.reload();</script><?php
	}
	if(! is_user_logged_in()){
		get_header();
        get_footer();
        ?><script>
        document.addEventListener('DOMContentLoaded',function(){
            prompt_login();
            document.querySelector('popup-overlay').style.pointerEvents = 'none';
        });
        </script><?php
		exit();
	}
}
function admin_only(){
	if (isset($_GET['logged_out'])){
    	wp_logout();
    	?><script>location.reload();</script><?php
    }
	if(! current_user_can('administrator')){
        _404();
	}
}
function working_page($coming_soon = false,$part = false,$link = null,$time = null){
	if ($link == null){
		$link = '/author/ffonline';
	}
	if (! current_user_can('administrator')){
		if ($part == false){
			get_header();
		}
		//$time = "Jan 5, 2021 15:37 UTC";
		?>
        <div class="">
            <div class="row section">
                <?php if ($coming_soon == false) { ?>
                    <div class="col s12 center-align">Sorry, this page is under maintainence right now, but we're working to get it active as soon as we can, you can see more on why you're seeing this <a href="<?php echo $link; ?>">here</a>.</div>
                <?php } else { ?>
                    <div class="col s12 center-align">Coming Soon!, more details <a href="<?php echo $link; ?>">here</a>.</div>
                <?php } ?>
            </div>
            <?php
            if ($time != null){
                echo '<p style="display:none;" id="get">' . $time . '</p>';
            ?>
            <div class="row section">
                <div class="col s12">
                    <div class="countdown-cont">
                        <h1 id="head">Time until is available:</h1>
                        <ul>
                            <li><span id="days"></span>days</li>
                            <li><span id="hours"></span>Hours</li>
                            <li><span id="minutes"></span>Minutes</li>
                            <li><span id="seconds"></span>Seconds</li>
                        </ul>
                    </div>

                    <style>
                        * {
                            box-sizing: border-box;
                            margin: 0;
                            padding: 0;
                        }


                        .countdown-cont {
                            color: #333;
                            text-align: center;
                            margin: 0 auto;
                        }

                        h1 {
                            font-weight: normal;
                        }

                        li {
                            display: inline-block;
                            font-size: 1.5em;
                            list-style-type: none;
                            padding: 1em;
                            text-transform: uppercase;
                        }

                        li span {
                            display: block;
                            font-size: 4.5rem;
                        }
                    </style>
                    <script>
                        const second = 1000,
                            minute = second * 60,
                            hour = minute * 60,
                            day = hour * 24;

                        let countDown = new Date(document.getElementById('get').innerHTML).getTime(),
                            x = setInterval(function() {

                                let now = new Date().getTime(),
                                    distance = countDown - now;

                                document.getElementById('days').innerText = Math.floor(distance / (day)),
                                    document.getElementById('hours').innerText = Math.floor((distance % (day)) / (hour)),
                                    document.getElementById('minutes').innerText = Math.floor((distance % (hour)) / (minute)),
                                    document.getElementById('seconds').innerText = Math.floor((distance % (minute)) / second);
                                    //do something later when date is reached
                                    if (distance < 0) {
                                        clearInterval(x);
                                        document.getElementById('days').innerText = 0,
                                            document.getElementById('hours').innerText = 0,
                                            document.getElementById('minutes').innerText = 0,
                                            document.getElementById('seconds').innerText = 0;
                                    }

                            }, second);
                    </script>
                </div>
            </div>
            <?php } ?>
        </div>
		<?php
		if ($part == false){
			get_footer();
		}
		exit();
	}
}

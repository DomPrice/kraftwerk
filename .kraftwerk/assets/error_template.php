<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Kraftwerk Error!</title>
<style type="text/css">
	html {
		height: 100%;
	}	
	body {
		color: #333333;
		font-family: "Courier New", Courier, monospace;	
		text-align: center;
		background-color: #EEEEEE;
		height: 100%;
		font-size: 13px;
	}
	#kw_error_shell {
		width: 900px;
		margin: 0 auto 0 auto;
		text-align: left;
		background-color: #FFFFFF;
		padding: 0;
		height: 100%;
		
	}
	#kw_error_logo {
		float: left;
		width: 150px;
		margin: 20px;
	}
	#kw_error_box {
		float: left;
		width: 650px;
		margin: 20px;
	}
	#kw_top_error {
		padding: 20px;	
	}
	#kw_top_error h2 {
		color: #333;
		margin: 0 0 20px 0;
	}
	#kw_top_error h4 {
		color: #F00;
	}
	#kw_error_env_info {
		padding: 20px;	
	}
	.clear_fix {
		clear: both;
		line-height: 1px;	
	}
	.rule_01 {
		display: block;
		clear: both;
		background: #DDDDDD;
		width: 100%;
		height: 10px;	
	}
</style>
</head>
<body>
	<div id="kw_error_shell">
        <div id="kw_error_logo">
            <img src="<?php print $error_template_logo; ?>" border="0" />
            <div class="clear_fix"></div>
        </div>
        <div id="kw_top_error">
        	<h2>Kraftwerk Error!</h2>
        	<h4><?php print $error_message; ?></h4>
            <div class="clear_fix"></div>
        </div>
        <div class="clear_fix"></div>
        <div class="rule_01"></div>
        <div class="clear_fix"></div>
        <div id="kw_error_env_info">
		APPLICATION:
        <br />
        Kraftwerk framework running at: <a href="http://<?php print $_SERVER['HTTP_HOST']; ?>"><?php print $_SERVER['HTTP_HOST']; ?></a>
        <br /><br />
        ENVIRONMENT: <?php print $_SERVER['SERVER_SIGNATURE']; ?>
        <br />
        GATEWAY_INTERFACE: <?php print $_SERVER['GATEWAY_INTERFACE']; ?>
        <br />
        SERVER_PROTOCOL: <?php print $_SERVER['SERVER_PROTOCOL']; ?>
        <br />
        HTTP_USER_AGENT: <?php print $_SERVER['HTTP_USER_AGENT']; ?>
        <br />
       	HTTP_REFERER: <?php print $_SERVER['HTTP_REFERER']; ?>
        <br /><br />
        </div>
        <?php if($error_stacktrace != "") { ?>
            <div class="clear_fix"></div>
            <div id="kw_error_box">
                <p><strong>Stack Trace</strong></p>
                <pre>
                    <?php print $error_stacktrace; ?>
                </pre>
            </div>
        <?php } ?>
    </div>
</body>
</html>

<?php if (!defined('FARI')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en" />

	<meta name="description" content="ZenChat" />

	<title>ZenChat</title>

	<link rel="stylesheet" type="text/css" media="screen" href="<?php $this->url('/public/tumblr.css') ;?>" />
    <script type="text/javascript" src="<?php $this->url('/public/ajax.js') ;?>"></script>
    <script type="text/javascript">
        // store the ID of the last message retrieved
        var lastMessageId = '';

        function traverseIntoTable(data, elementId) {
            var rows = '';
            for (var key in data) {
                rows += '<p><span class="quote">&#147;</span> ' + data[key].text
                        + ' <span class="quote">&#148;</span></p><div class="author">&mdash;&nbsp;'
                        + data[key].author + ', '
                        + data[key].time + '</div>';
            }
            lastMessageId = data[0].id; // update last message (which comes first!)
            document.getElementById(elementId).innerHTML = '<div class="new" id="'+lastMessageId+'"' + rows
                + '</div>' + document.getElementById(elementId).innerHTML;
        }

        // change to JSON response handler
        var jsonMessageHandler = function(elementId, jsonObject) {
            // we might have nothing in the db yet
            if (jsonObject != '') {
                newMessages = eval('(' + jsonObject + ')');
                // we might have 0 new messages
                if (newMessages.length > 0) traverseIntoTable(newMessages, elementId);
            }
        }

        // check for new messages
        function update() {
            // first 'switch off' highlighting on the past new messages
            if (document.getElementById(lastMessageId) != null)
                document.getElementById(lastMessageId).className = '';

            // switch to JSON
            ajax.responseHand = jsonMessageHandler;
            ajax.responseFormat = 'object';
            ajax.doGet('<?php echo WWW_DIR; ?>/shoutbox/get/'+lastMessageId, 'freshMessages');
            var t = setTimeout("update()", 5000);
        }

        var customToDiv = function(id, str) {
            // if (str.length > 0) document.getElementById(id).innerHTML = str;
            // re-enable
            document.inputForm.shout.disabled = false;
            document.inputForm.shout.value = 'Shout!';
            document.inputForm.shout.className = 'button';
        }

        // POST a message
        var messageText = '';
        function postMessage() {
                // disable to 'prevent' multiple send and request timeouts
                document.inputForm.shout.disabled = true;
                document.inputForm.shout.value = '';
                document.inputForm.shout.className = 'button loading';
                // switch to basic TEXT
                ajax.responseHand = customToDiv;
                ajax.responseFormat = 'text';
                ajax.doPost('<?php echo WWW_DIR; ?>/shoutbox/add','inputForm', 'status');
                messageText = document.inputForm.text.value; // 'save' the message in case of problems
                document.inputForm.text.value = ''; // delete the text from input field
        }
    </script>
</head>

<body onload="update()">
    <div id="content">
        <span id="top">
            Logged in as: <b><?php echo $user ;?></b> <a href="<?php $this->url('secure/logout') ;?>">Logout</a>
        </span>

        <h1><a href="<?php $this->url('/') ;?>">ZenChat</a></h1>

        <?php if (isset($system)) foreach($system as $message): ?>
            <div class="flash <?php echo $message['status'] ;?>"><?php echo $message['message'] ;?></div>
        <?php endforeach ;?>

        <form action="<?php $this->url('/shoutbox/add/') ;?>" name="inputForm" id="inputForm" method="post">
            <span class="text"><input type="text" class="text" name="text" /></span>
            <input onclick="postMessage();return false;" type="submit" class="button" name="shout"
                   id="shout" value="Shout!" />
		</form>
		<br />

        <div class="" id="spinner"></div>

        <div id="freshMessages"></div>
        <?php $lastMessageId = -1; foreach ($messages as $message): ?>
            <?php if ($lastMessageId == -1) $lastMessageId = $message['id'] ;?>
            <p><span class="quote">&#147;</span>
                <?php echo $message['text'] ;?>
                <span class="quote">&#148;</span>
            </p>
            <div class="author">
                &mdash; <?php echo $message['author'] ;?>, <?php echo $message['time'] ;?>
            </div>
        <?php endforeach; ?>
        <script type="text/javascript">
            lastMessageId = <?php echo $lastMessageId ;?>;
        </script>
	</div>
</body>
</html>

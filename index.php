<?php
require('Services/Twilio.php');
require('config.php');

if(isset($_REQUEST['book']) && $_REQUEST['book'] != 'none') {
    $book = $_REQUEST['book'];
}
$phonebook = file($book);

foreach ($phonebook as $line) {
    $line = chop($line);
    list($number,$name,$group) = preg_split('/,/', $line);
    if (preg_match('/ /', $group)) {
        list($group, $group2) = preg_split('/ /', $group);
    }
    $groups[$group][] = $number;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Twilio SMS bulk</title>
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
<div class="starter-template">
<h1>Twilio SMS bulk</h1>

<form method="POST">

Group:
<select name="group">
<option value="none">--</option>
<?php
foreach ($groups as $key => $val) {
    echo "<option value=\"$key\">$key</option>";
}
?>
</select>
<br/>
Individual:<br/>
<select size="6" multiple="multiple" name="nos[]">
<?php
foreach ($phonebook as $line) {
    $line = chop($line);
    list($number,$name,$group) = preg_split('/,/', $line);
    echo "<option value=\"$number\">$group - $name</option>";
}
?>
</select>
<br/>
Message:<br/>
<textarea name="text" cols="40" rows="10">
<?php if (isset($_REQUEST['text'])) { ?>
<?=$_REQUEST['text']?>
<?php } ?>
</textarea>
<br/>
<input type="submit" value="Send SMS" class="btn btn-primary"/>
</form>

<br/>
<br/>
<br/>
<form method="POST">
Phonebook:
<select name="book">
<option>--</option>
<?php
foreach (glob("*.txt") as $filename) {
    echo "<option value=\"$filename\">$filename</option>";
}
?>
</select>
<br/>
<input type="submit" value="Load Phonebook" class="btn btn-default"/>
</form>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</div>
</div><!-- /.container -->
</body>
</html>


<?php

$client = new Services_Twilio($sid, $token);
if ($_REQUEST) {
    $text = $_REQUEST['text'];
    $group = $_REQUEST['group'];
    $nos = $_REQUEST['nos'];

    if($group != 'none') {
        foreach ($groups as $key => $val) {
            if ($key == $group)
                $groupnos[] = $val;
        }
        $nos = array_merge($nos, $groupnos[0]);
    }

    foreach ($nos as $no) {
        print "$no = ";
        $message = $client->account->messages->sendMessage($from,$no,$text);
        print $message->sid;
        print "<br/>\n";
    }
}

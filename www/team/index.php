<?php
/**
 * $Id$
 */

require('init.php');
$title = 'Team overview';
require('../header.php');

putClock();

echo "<h1>Teampage ".htmlentities($name)."</h1>\n\n";

getSubmissions('team', $login, FALSE);

?>
<p><table>
<tr><th colspan="2">Clarifications</th></tr>
<?php
$data = $DB->q('TABLE SELECT * FROM clar_response WHERE cid = %i 
	AND (rcpt = %s OR rcpt IS NULL) ORDER BY submittime DESC',
	getCurCont(), $login );

foreach($data as $row) {
	echo "<tr><td>".
		printtime($row['submittime'])."</td><td>".
		"<a href=\"clarification.php?id=".
		urlencode($row['respid'])."\">".
		htmlentities(substr($row['body'],0,50))."...</a></td></tr>\n";

}

?>
</table>
</p>
<?php

echo "<p><a href=\"../public/\">Scoreboard</a></p>\n\n";

require('../footer.php');

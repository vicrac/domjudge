<?php declare(strict_types=1);
/**
 * Part of the DOMjudge Programming Contest Jury System and licensed
 * under the GNU GPL. See README and COPYING for details.
 */

require_once(LIBVENDORDIR . '/autoload.php');

// TODO: still used in some import scripts. Refactor them and remove this

// The IP need not be set when included from
// import-{REST,XML}feed scripts, so suppress empty value.
$ip = @$_SERVER['REMOTE_ADDR'];

$teamid = null;
$username = null;
$teamdata = null;
$userdata = null;

// Check if current user has given role, or has superset of this role's
// privileges
function checkrole(string $rolename, bool $check_superset = true) : bool
{
    global $G_SYMFONY, $apiFromInternal;
    if (isset($apiFromInternal) && $apiFromInternal === true) {
        return true;
    }
    return $G_SYMFONY->checkrole($rolename, $check_superset);
}

// Returns whether the connected user is logged in, sets $username, $teamdata
function logged_in() : bool
{
    global $DB, $ip, $username, $teamid, $teamdata, $userdata;
    if (!empty($username) && !empty($userdata) && !empty($teamdata)) {
        return true;
    }
    if (isset($_SESSION['username'])) {
        $userdata = $DB->q('MAYBETUPLE SELECT * FROM user
                            WHERE username = %s AND enabled = 1', $_SESSION['username']);
    }

    if (!empty($userdata)) {
        $username = $userdata['username'];
        $teamdata = $DB->q('MAYBETUPLE SELECT * FROM team
                            WHERE teamid = %i AND enabled = 1', $userdata['teamid']);

        // Pull the list of roles that a user has
        $userdata['roles'] = get_user_roles($userdata['userid']);
    }
    if (!empty($teamdata)) {
        $teamid = (int)$teamdata['teamid'];
        // Is this the first visit? Record that in the team table.
        if (empty($teamdata['teampage_first_visited'])) {
            $hostname = empty($ip) ? 'PHPUNIT' : gethostbyaddr($ip);
            $DB->q('UPDATE team SET teampage_first_visited = %f, hostname = %s
                    WHERE teamid = %i',
                   now(), $hostname, $teamid);
        }
    }

    return $username!==null;
}

function get_user_roles(string $userid) : array
{
    global $DB;
    return $DB->q('COLUMN SELECT role.role FROM userrole
                   LEFT JOIN role USING (roleid)
                   WHERE userrole.userid = %s', $userid);
}

<?php
require_once __DIR__.'/vendor/autoload.php';
use JohnRivs\Wunderlist\Wunderlist;

// Enter your Wunderlist APP credentials here
$clientId     = 'xxx';
$clientSecret = 'xxx';
$accessToken  = 'xxx';

// Initialize & Roll it...
$w = new Wunderlist($clientId, $clientSecret, $accessToken);
$user = $w->getCurrentUser();
$counter = 0;
$lists = $w->getLists();
$relevant_tasks = array();
foreach($lists as $list) {
    $tasks = $w->getTasks(['list_id' => $list['id']]);
    foreach($tasks as $task) {
        $counter++;
        $notes = $w->getNotes('task',['task_id' => $task['id']]);
        foreach($notes as $note) {
            if (strpos(strtolower($note['content']), '[planning]') !== false) {
                // Planning set, so nihihihihiiiiiiiiiiiii
                $date_array = preg_match_all("/(\d{4}-\d{2}-\d{2})/", $note['content'], $match);
                $task['planning'] = $match[0];
                array_push($relevant_tasks,$task);
            }
        }
    }
}

// Define scope, visible weeks, dates
$scope_startdate    = new DateTime();
$scope_curdate      = $scope_startdate;
$scope_enddate      = new DateTime();
$scope_enddate->modify("+26 weeks");

// Functions
function is_weekend($date) {
    return $date->format('N') >= 6;
}
function limit_text($string, $length) {
    $string = strip_tags($string);
    $output = '';
    $output .= substr($string, 0, $length);
    if(strlen($string) >= $length) {
        $output .= ' &hellip;';
    }
    return $output;
}


?>
<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php echo $user['name']; ?> - Planning</title>
    <meta name="description" content="The work schedule of <?php echo $user['name']; ?> for the next 26 weeks...">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/vendor/modernizr-2.8.3.min.js"></script>
</head>
<body>
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<?php
echo '<header>';
echo '<h1>Planning of '.$user['name'].'</h1>';
echo '<h3><a href="https://www.wunderlist.com/#/lists/all">'.$counter.' tasks</a>, of which '.count($relevant_tasks).' are scheduled in the next 26 weeks...</h3>';
echo '</header>';
echo '<div id="main">';
echo '<table>';

// Row of weeks
$scope_curdate->modify('Monday');
echo '<tr>';
echo '<th style="background: white;">&nbsp;</th>';
while ($scope_curdate < $scope_enddate) {
    if(!is_weekend($scope_curdate)) {
        echo '<th colspan="5">';
        echo 'Week '.$scope_curdate->format("W, M");
        echo '</th>';
    }
    $scope_curdate->modify("+1 week");
}
echo '</tr>';

// Row of days
$scope_curdate = new DateTime();
echo '<tr>';
echo '<th style="text-align:left; vertical-align: middle;">Projecten</th>';
while ($scope_curdate < $scope_enddate) {
    if(!is_weekend($scope_curdate)) {
        echo '<th>' . $scope_curdate->format("D d/m") . '</th>';
    }
    $scope_curdate->modify("+1 day");

}
echo '</tr>';

// Rows of tasks
foreach ($relevant_tasks as $relevant_task) {
    $scope_curdate = new DateTime();
    echo '<tr>';
    echo '<td style="white-space: nowrap;" title="'.$relevant_task['title'].'"><a href="https://www.wunderlist.com/#/tasks/'.$relevant_task['id'].'">' . limit_text($relevant_task['title'],25) . '</a></td>';
    while ($scope_curdate < $scope_enddate) {
        $project_startdate = new DateTime($relevant_task['planning'][0]);
        $project_enddate = new DateTime($relevant_task['planning'][1]);
        if(!is_weekend($scope_curdate)) {

            if (($project_startdate <= $scope_curdate) && ($project_enddate >= $scope_curdate)) {
                echo '<td class="busy" title="'.$relevant_task['title'].'">&nbsp;</td>';
            } else {
                echo '<td class="free">&nbsp;</td>';
            }
            echo '</td>';
        }
        $scope_curdate->modify("+1 day");
    }
    echo '</tr>';
}
echo '</table>';
echo '</div>';
$updated_date = new DateTime($user['updated_at']);
echo '<footer><a href="https://www.wunderlist.com/#/lists/all/tasks/new" class="button">Create a new task</a><hr />Last changed on '.$updated_date->format('d/m/y').' - Made by <a href="http://chocolata.be">Chocolata</a> with <a href="https://github.com/johnRivs/wunderlist">John Rivs\' Wunderlist API Wrapper for PHP</a>.</footer>';
?>
<!-- jQuery is loaded, but we don't have any scripts, maybe useful in future -->
<script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.12.0.min.js"><\/script>')</script>
<script src="js/plugins.js"></script>
<script src="js/main.js"></script>
<script type="text/javascript">
    WebFontConfig = {
        google: { families: [ 'Source+Sans+Pro:400,600,700:latin' ] }
    };
    (function() {
        var wf = document.createElement('script');
        wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
            '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
        wf.type = 'text/javascript';
        wf.async = 'true';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(wf, s);
    })();
</script>
</body>
</html>

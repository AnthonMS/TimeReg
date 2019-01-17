<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');


include("includes/db_conn.php");
include("includes/response.php");
include("includes/functions.php");

switch($_SERVER['REQUEST_METHOD'])
{
    case 'POST':
        $function = $_REQUEST['function'];
        if ($function == "registerTime") {
            echo registerTime($connect);
        }
        break;
    case 'GET':
        echo "NO GET FUNCTION";
        break;
}

function registerTime($localConn) {
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);
    
    $userId = $data->userId;
    $companyId = $data->companyId;
    $borgerId = $data->borgerId;

    //$now = new \DateTime('now', new DateTimeZone('Europe/Bucharest'));
    //date_default_timezone_set('UTC+1');
    $date = new DateTime($data->date, new DateTimeZone('Europe/Copenhagen'));
    $date->add(new DateInterval('PT1H'));
    $dateFormat = $date->format('Y-m-d H:i:s');

    $tmpTime = $data->tmpTime;

    $attendance = utf8_decode($data->attendance);
    $description = utf8_decode($data->description);

    $response = new Response;
    $response->setSuccess(false);
    $response->setMsg("ERROR");

    //$sql = "SET SESSION time_zone = '+1:00'; ";
    $sql = "INSERT INTO timeregistrations (userId, companyId, borgerId, date, timeInterval, attendance, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $localConn->prepare($sql);
    $stmt->bind_param('iiisiss', $userId, $companyId, $borgerId, $dateFormat, $tmpTime, $attendance, $description);

    if ($stmt->execute()) 
    {
        $response->setSuccess(true);
        $response->setMsg("SUCCESS: saved registration");
    } else {
        $response->setMsg("ERROR: did not save registration");
    }

    //$response->setMsg(phpinfo());

    //$stmt->execute();
    $stmt->close();

    return $response->jsonEnc();
}
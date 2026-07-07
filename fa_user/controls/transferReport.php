<?php
require 'init.php';

$output = '<table>
    <tr>
        <th>Player / Staff</th>
        <th>Number</th>
        <th>Post</th>
        <th>Position</th>
        <th>Team From</th>
        <th>Team To</th>
        <th>Date Of Completion</th>
    </tr>';

$sql = 'SELECT * FROM `transfer` WHERE `status` = 3 ORDER BY `id` DESC';
$statement = $connection->prepare($sql);
$statement->execute();
$transfers = $statement->fetchAll(PDO::FETCH_OBJ);

foreach ($transfers as $member) {
    // Retrieve player/staff information
    $stmt = $connection->prepare("SELECT * FROM team_member WHERE member_id=:member_id");
    $stmt->execute([':member_id' => $member->member_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $number = $data['number'];
        $post = $data['post'];
        $fname = $data['fname'];
        $lname = $data['lname'];
        $position = $data['position'];
        $contract_value = $data['contract_value'];
        $contract_duration = $data['contract_duration'];

        // Define position based on the post
        if ($post == 'HC') {
            $posi = 'Head Coach';
        } elseif ($post == 'AC') {
            $posi = 'Assistant Coach';
        } elseif ($post == 'GC') {
            $posi = 'GoalKeeper Coach';
        } elseif ($post == 'Do') {
            $posi = 'Doctor Coach';
        } elseif ($post == 'Ph') {
            $posi = 'Physiotherapist';
        } else {
            $posi = 'Unknown'; // Handle unknown post
        }
    } else {
        // Handle the case where member data isn't found
        $number = "";
        $post = "";
        $fname = "";
        $lname = "";
        $position = "";
        $posi = "";
    }

    // Get the team names for Team From and Team To
    $stmt = $connection->prepare("SELECT `name`,`team_id` FROM team WHERE team_id=:team_id");
    $stmt->execute([':team_id' => $member->team_from]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $teamFrom = $data ? $data['name'] : "";

    $stmt = $connection->prepare("SELECT `name`,`team_id` FROM team WHERE team_id=:team_id");
    $stmt->execute([':team_id' => $member->team_to]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $teamTo = $data ? $data['name'] : "";

    // Add a row to the table
    $output .= '<tr>
        <td>'. $fname .' '. $lname.'</td>
        <td>'. ($number == "" ? "" : $number) .'</td>
        <td>'. $post .'</td>
        <td>'. ($post == "player" ? $position : $posi) .'</td>
        <td>'. $teamFrom .'</td>
        <td>'. $teamTo .'</td>
        <td>'. $member->completeDate .'</td>
    </tr>';
}

$output .='</table>';

header('Content-Type:application/xls');
header('Content-Disposition: attachment; filename=transfer.xls');
echo $output;

